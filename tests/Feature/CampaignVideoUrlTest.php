<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignVideoUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_can_update_social_video_url_when_campaign_is_withdrawn(): void
    {
        $role = Role::where('slug', 'user')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $campaign = Campaign::create([
            'organizer_id' => $user->id,
            'title' => 'Kampanye Peduli Bersama',
            'slug' => 'kampanye-peduli-bersama',
            'description' => 'Deskripsi kampanye',
            'category' => 'Bencana Alam',
            'target_amount' => 50000000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'status' => 'withdrawn',
            'withdrawal_status' => 'confirmed',
            'payout_bank_name' => 'BCA',
            'payout_account_number' => '12345678',
            'payout_account_name' => $user->name,
        ]);

        $response = $this->actingAs($user)->patch("/campaigns/{$campaign->id}/video-url", [
            'video_url' => 'https://www.instagram.com/reel/DC_12345678/',
        ]);

        $response->assertRedirect();
        $this->assertEquals('https://www.instagram.com/reel/DC_12345678/', $campaign->fresh()->video_url);
    }

    public function test_cannot_update_video_url_if_campaign_is_not_withdrawn(): void
    {
        $role = Role::where('slug', 'user')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $campaign = Campaign::create([
            'organizer_id' => $user->id,
            'title' => 'Kampanye Aktif Belum Withdrawn',
            'slug' => 'kampanye-aktif-belum-withdrawn',
            'description' => 'Deskripsi kampanye',
            'category' => 'Pendidikan',
            'target_amount' => 20000000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'status' => 'active',
            'withdrawal_status' => 'not_ready',
            'payout_bank_name' => 'BCA',
            'payout_account_number' => '12345678',
            'payout_account_name' => $user->name,
        ]);

        $response = $this->actingAs($user)->patch("/campaigns/{$campaign->id}/video-url", [
            'video_url' => 'https://www.instagram.com/reel/DC_12345678/',
        ]);

        $response->assertStatus(422);
        $this->assertNull($campaign->fresh()->video_url);
    }

    public function test_organizer_can_update_social_video_url_with_tiktok_and_facebook(): void
    {
        $role = Role::where('slug', 'user')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $campaign = Campaign::create([
            'organizer_id' => $user->id,
            'title' => 'Kampanye Berkah',
            'slug' => 'kampanye-berkah',
            'description' => 'Deskripsi kampanye',
            'category' => 'Pendidikan',
            'target_amount' => 20000000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'status' => 'withdrawn',
            'withdrawal_status' => 'confirmed',
            'payout_bank_name' => 'BCA',
            'payout_account_number' => '12345678',
            'payout_account_name' => $user->name,
        ]);

        // TikTok
        $resTiktok = $this->actingAs($user)->patch("/campaigns/{$campaign->id}/video-url", [
            'video_url' => 'https://vt.tiktok.com/ZSjX12345/',
        ]);
        $resTiktok->assertRedirect();
        $this->assertEquals('https://vt.tiktok.com/ZSjX12345/', $campaign->fresh()->video_url);

        // Facebook
        $resFb = $this->actingAs($user)->patch("/campaigns/{$campaign->id}/video-url", [
            'video_url' => 'https://fb.watch/xyz123/',
        ]);
        $resFb->assertRedirect();
        $this->assertEquals('https://fb.watch/xyz123/', $campaign->fresh()->video_url);
    }

    public function test_organizer_can_clear_video_url_when_withdrawn(): void
    {
        $role = Role::where('slug', 'user')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $campaign = Campaign::create([
            'organizer_id' => $user->id,
            'title' => 'Kampanye Bersih',
            'slug' => 'kampanye-bersih',
            'description' => 'Deskripsi kampanye',
            'category' => 'Pendidikan',
            'target_amount' => 20000000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'status' => 'withdrawn',
            'withdrawal_status' => 'confirmed',
            'video_url' => 'https://www.instagram.com/reel/123/',
            'payout_bank_name' => 'BCA',
            'payout_account_number' => '12345678',
            'payout_account_name' => $user->name,
        ]);

        $response = $this->actingAs($user)->patch("/campaigns/{$campaign->id}/video-url", [
            'video_url' => '',
        ]);

        $response->assertRedirect();
        $this->assertNull($campaign->fresh()->video_url);
    }

    public function test_non_supported_urls_are_rejected(): void
    {
        $role = Role::where('slug', 'user')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $campaign = Campaign::create([
            'organizer_id' => $user->id,
            'title' => 'Kampanye Alam',
            'slug' => 'kampanye-alam',
            'description' => 'Deskripsi kampanye',
            'category' => 'Lingkungan',
            'target_amount' => 10000000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'status' => 'withdrawn',
            'withdrawal_status' => 'confirmed',
            'payout_bank_name' => 'BCA',
            'payout_account_number' => '12345678',
            'payout_account_name' => $user->name,
        ]);

        // YouTube should fail
        $response = $this->actingAs($user)->patch("/campaigns/{$campaign->id}/video-url", [
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
        $response->assertSessionHasErrors('video_url');

        // Random website should fail
        $res2 = $this->actingAs($user)->patch("/campaigns/{$campaign->id}/video-url", [
            'video_url' => 'https://myblog.com/video',
        ]);
        $res2->assertSessionHasErrors('video_url');
    }

    public function test_non_organizer_cannot_update_video_url(): void
    {
        $role = Role::where('slug', 'user')->first();
        $organizer = User::factory()->create(['role_id' => $role->id]);
        $otherUser = User::factory()->create(['role_id' => $role->id]);

        $campaign = Campaign::create([
            'organizer_id' => $organizer->id,
            'title' => 'Kampanye Bantuan',
            'slug' => 'kampanye-bantuan',
            'description' => 'Deskripsi',
            'category' => 'Kesehatan',
            'target_amount' => 10000000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'status' => 'withdrawn',
            'withdrawal_status' => 'confirmed',
            'payout_bank_name' => 'BCA',
            'payout_account_number' => '12345678',
            'payout_account_name' => $organizer->name,
        ]);

        $response = $this->actingAs($otherUser)->patch("/campaigns/{$campaign->id}/video-url", [
            'video_url' => 'https://www.instagram.com/reel/123456/',
        ]);

        $response->assertStatus(403);
    }
}
