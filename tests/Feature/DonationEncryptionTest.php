<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use App\Services\PaillierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationEncryptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_encrypts_donor_name_when_is_anonymous_is_checked(): void
    {
        $donor = User::factory()->create(['name' => 'Budi Santoso']);
        $organizer = User::factory()->create();

        $campaign = Campaign::create([
            'organizer_id' => $organizer->id,
            'title' => 'Bantuan Pendidikan Anak Yatim',
            'description' => 'Program beasiswa untuk anak-anak berprestasi.',
            'category' => 'Pendidikan',
            'slug' => 'bantuan-pendidikan-xyz',
            'target_amount' => 10000000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'payout_bank_name' => 'BCA',
            'payout_account_number' => '1234567890',
            'payout_account_name' => 'Yayasan Pendidikan',
            'status' => 'active',
        ]);

        $response = $this->actingAs($donor)->post("/campaigns/{$campaign->id}/donations", [
            'amount' => 150000,
            'payment_method' => 'BCA',
            'donor_name' => 'Budi Santoso Rahasia',
            'is_anonymous' => true,
            'donor_email' => 'budi@example.com',
            'donor_phone' => '081234567890',
            'donor_note' => 'Semoga berkah selalu.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $donation = Donation::where('campaign_id', $campaign->id)->latest()->first();

        $this->assertNotNull($donation);
        $this->assertTrue($donation->is_anonymous);
        $this->assertSame('Hamba Allah', $donation->donor_name);
        $this->assertNotNull($donation->encrypted_donor_name);

        // Verify that Paillier decryptString correctly recovers the original donor name
        $paillier = app(PaillierService::class);
        $decryptedName = $paillier->decryptString($donation->encrypted_donor_name);
        $this->assertSame('Budi Santoso Rahasia', $decryptedName);
    }

    public function test_it_keeps_plain_donor_name_when_is_anonymous_is_false(): void
    {
        $donor = User::factory()->create(['name' => 'Siti Nurhaliza']);
        $organizer = User::factory()->create();

        $campaign = Campaign::create([
            'organizer_id' => $organizer->id,
            'title' => 'Renovasi Masjid',
            'description' => 'Perbaikan kubah dan sarana wudhu.',
            'category' => 'Sarana Ibadah',
            'slug' => 'renovasi-masjid-xyz',
            'target_amount' => 20000000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'payout_bank_name' => 'BSI',
            'payout_account_number' => '7778889990',
            'payout_account_name' => 'DKM Masjid',
            'status' => 'active',
        ]);

        $response = $this->actingAs($donor)->post("/campaigns/{$campaign->id}/donations", [
            'amount' => 250000,
            'payment_method' => 'BSI',
            'donor_name' => 'Siti Nurhaliza',
            'is_anonymous' => false,
            'donor_email' => 'siti@example.com',
            'donor_phone' => '081298765432',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $donation = Donation::where('campaign_id', $campaign->id)->latest()->first();

        $this->assertNotNull($donation);
        $this->assertFalse($donation->is_anonymous);
        $this->assertSame('Siti Nurhaliza', $donation->donor_name);
        $this->assertNull($donation->encrypted_donor_name);
    }

    public function test_campaign_detail_can_be_viewed_without_login(): void
    {
        $organizer = User::factory()->create();
        $campaign = Campaign::create([
            'organizer_id' => $organizer->id,
            'title' => 'Kampanye Uji Publik',
            'description' => 'Testing public viewing of campaign detail.',
            'category' => 'Kesehatan',
            'slug' => 'kampanye-uji-publik',
            'target_amount' => 5000000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'payout_bank_name' => 'BCA',
            'payout_account_number' => '1122334455',
            'payout_account_name' => 'Yayasan Uji',
            'status' => 'active',
        ]);

        // Unauthenticated access must render Campaigns/PublicShow (200 OK)
        $response = $this->get("/campaigns/{$campaign->id}");
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Campaigns/PublicShow'));

        // Authenticated access must render Campaigns/Show (200 OK)
        $user = User::factory()->create();
        $authResponse = $this->actingAs($user)->get("/campaigns/{$campaign->id}");
        $authResponse->assertStatus(200);
        $authResponse->assertInertia(fn ($page) => $page->component('Campaigns/Show'));
    }

    public function test_making_donation_requires_authentication(): void
    {
        $organizer = User::factory()->create();
        $campaign = Campaign::create([
            'organizer_id' => $organizer->id,
            'title' => 'Kampanye Uji Donasi Auth',
            'description' => 'Testing donation auth guard.',
            'category' => 'Pendidikan',
            'slug' => 'kampanye-uji-donasi-auth',
            'target_amount' => 5000000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'payout_bank_name' => 'BCA',
            'payout_account_number' => '1122334455',
            'payout_account_name' => 'Yayasan Uji',
            'status' => 'active',
        ]);

        // Unauthenticated donation submission must redirect to login
        $guestResponse = $this->post("/campaigns/{$campaign->id}/donations", [
            'amount' => 50000,
            'payment_method' => 'BCA',
            'donor_name' => 'Guest Donator',
        ]);
        $guestResponse->assertRedirect('/login');

        // Unauthenticated midtrans snap creation must also redirect to login
        $guestSnapResponse = $this->postJson("/campaigns/{$campaign->id}/donations/snap", [
            'amount' => 50000,
            'donor_name' => 'Guest Donator',
        ]);
        $guestSnapResponse->assertStatus(401);
    }
}
