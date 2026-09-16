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
        );
    }
}
