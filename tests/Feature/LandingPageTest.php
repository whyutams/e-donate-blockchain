<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_successfully_with_campaigns_and_statistics(): void
    {
        $organizer = User::factory()->create();

        $campaign = Campaign::create([
            'organizer_id' => $organizer->id,
            'title' => 'Bantu Korban Banjir',
            'description' => 'Bantuan sembako dan logistik darurat untuk korban bencana.',
            'category' => 'Bencana Alam',
            'slug' => 'bantu-korban-banjir-xyz',
            'target_amount' => 50000000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'payout_bank_name' => 'BCA',
            'payout_account_number' => '1234567890',
            'payout_account_name' => 'Yayasan Peduli',
            'status' => 'active',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Landing')
            ->has('campaigns')
            ->has('statistics')
            ->has('categories')
            ->where('campaigns.0.title', 'Bantu Korban Banjir')
            ->where('campaigns.0.category', 'Bencana Alam')
            ->where('campaigns.0.slug', 'bantu-korban-banjir-xyz')
        );
    }

    public function test_campaign_can_be_accessed_via_slug(): void
    {
        $organizer = User::factory()->create();

        $campaign = Campaign::create([
            'organizer_id' => $organizer->id,
            'title' => 'Beasiswa Yatim Dhuafa',
            'description' => 'Program beasiswa pendidikan.',
            'category' => 'Pendidikan',
            'slug' => 'beasiswa-yatim-dhuafa-12345678',
            'target_amount' => 10000000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'payout_bank_name' => 'BCA',
            'payout_account_number' => '1234567890',
            'payout_account_name' => 'Yayasan Beasiswa',
            'status' => 'active',
        ]);

        // Guest access by slug
        $guestResponse = $this->get("/campaigns/{$campaign->slug}");
        $guestResponse->assertStatus(200);
        $guestResponse->assertInertia(fn (Assert $page) => $page
            ->component('Campaigns/PublicShow')
            ->where('campaign.title', 'Beasiswa Yatim Dhuafa')
            ->where('campaign.slug', 'beasiswa-yatim-dhuafa-12345678')
        );

        // Authenticated access by slug
        $user = User::factory()->create();
        $authResponse = $this->actingAs($user)->get("/campaigns/{$campaign->slug}");
        $authResponse->assertStatus(200);
        $authResponse->assertInertia(fn (Assert $page) => $page
            ->component('Campaigns/Show')
            ->where('campaign.title', 'Beasiswa Yatim Dhuafa')
            ->where('campaign.slug', 'beasiswa-yatim-dhuafa-12345678')
        );
    }

    public function test_create_campaign_route_is_accessible_and_not_matched_as_slug(): void
    {
        // Unauthenticated access redirects to login
        $guestResponse = $this->get('/campaigns/create');
        $guestResponse->assertRedirect('/login');

        // Verified user can access create campaign page
        $user = User::factory()->create();
        $user->verificationProfile()->create([
            'entity_type' => 'individual',
            'legal_name' => 'John Doe',
            'status' => 'verified',
        ]);

        $response = $this->actingAs($user)->get('/campaigns/create');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Campaigns/Create'));
    }
}
