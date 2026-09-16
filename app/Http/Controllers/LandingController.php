<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Donation;
use App\Services\PaillierService;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function __invoke(PaillierService $paillier): Response
    {
        $campaigns = Campaign::query()
            ->with('organizer:id,name')
            ->whereIn('status', ['active', 'goal_reached'])
            ->latest()
            ->get()
            ->map(fn (Campaign $campaign) => [
                'id' => $campaign->id,
                'title' => $campaign->title,
                'slug' => $campaign->slug,
                'description' => $campaign->description,
                'category' => $campaign->category,
                'image_url' => $campaign->image_path ? asset('storage/'.$campaign->image_path) : null,
                'target_amount' => $campaign->target_amount,
                'progress_percentage' => (float) $campaign->progress_percentage,
                'donors_count' => $campaign->donors_count,
                'starts_at' => $campaign->starts_at?->toIso8601String(),
                'ends_at' => $campaign->ends_at?->toIso8601String(),
                'status' => $campaign->status,
                'organizer' => $campaign->organizer ? ['name' => $campaign->organizer->name] : null,
            ])
            ->values();

        // Homomorphic sum of confirmed donations for verified ledger statistics
        $confirmedCiphertexts = Donation::where('status', 'confirmed')
            ->pluck('encrypted_amount')
            ->all();

        $totalCollected = $confirmedCiphertexts === []
            ? 0
            : $paillier->decrypt($paillier->add(...$confirmedCiphertexts));

        $totalDonationsCount = Donation::where('status', 'confirmed')->count();
        $totalTransactionsCount = Donation::count();
        $activeCampaignsCount = Campaign::where('status', 'active')->count();

        $categories = Campaign::query()
            ->whereIn('status', ['active', 'goal_reached'])
            ->pluck('category')
            ->unique()
            ->values()
            ->all();

        return Inertia::render('Landing', [
            'campaigns' => $campaigns,
            'statistics' => [
                'total_collected' => $totalCollected,
                'total_donations' => $totalDonationsCount,
                'total_transactions' => $totalTransactionsCount,
                'active_campaigns' => $activeCampaignsCount,
                'ledger_node_status' => 'online',
            ],
            'categories' => $categories,
        ]);
    }
}
