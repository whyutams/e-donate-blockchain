<?php

namespace App\Http\Controllers;

use App\Models\AdminBankSetting;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use App\Models\VerificationProfile;
use App\Services\PaillierService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, PaillierService $paillier): Response
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $campaigns = Campaign::query()
            ->with('organizer:id,name')
            ->whereIn('status', ['active', 'goal_reached'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Campaign $campaign) => [
                'id' => $campaign->id,
                'slug' => $campaign->slug,
                'title' => $campaign->title,
                'category' => $campaign->category,
                'target' => (int) $campaign->target_amount,
                'collected' => $this->decryptAmount($campaign->encrypted_collected_amount, $paillier),
                'donorsCount' => (int) $campaign->donors_count,
                'daysLeft' => $campaign->ends_at ? max(0, now()->diffInDays($campaign->ends_at, false)) : 0,
                'urgency' => $campaign->ends_at && now()->diffInDays($campaign->ends_at, false) <= 7 ? 'high' : 'medium',
                'organizer' => $campaign->organizer?->name,
            ])
            ->values();

        $donations = Donation::query()
            ->with('campaign:id,title,category')
            ->when(! $isAdmin, fn ($query) => $query->where('donor_id', $user->id))
            ->latest()
            ->limit(50)
            ->get();

        $transactions = $donations->map(fn (Donation $donation) => [
            'id' => (string) $donation->id,
            'hash' => $donation->transaction_hash,
            'campaign' => $donation->campaign?->title ?? 'Kampanye dihapus',
            'category' => $donation->campaign?->category ?? 'Lainnya',
            'amount' => $this->decryptAmount($donation->encrypted_amount, $paillier),
            'paymentMethod' => strtoupper($donation->payment_method ?? '-'),
            'referenceCode' => $donation->reference_code ?? '-',
            'date' => $donation->created_at?->translatedFormat('d M Y, H:i') ?? '-',
            'blockNumber' => (int) ($donation->block_number ?? 0),
            'status' => $donation->status === 'confirmed' ? 'verified' : 'pending',
        ])->values();

        if ($isAdmin) {
            return Inertia::render('AdminDashboard', [
                'stats' => [
                    'users' => User::query()->count(),
                    'campaigns' => Campaign::query()->count(),
                    'donations' => Donation::query()->count(),
                    'confirmedDonations' => Donation::query()->where('status', 'confirmed')->count(),
                    'pendingDonations' => Donation::query()->where('status', 'pending')->count(),
                    'pendingVerifications' => VerificationProfile::query()->where('status', 'pending')->count(),
                    'pendingWithdrawals' => Campaign::query()->where('withdrawal_status', 'pending')->count(),
                ],
                'recentDonations' => Donation::query()
                    ->with(['campaign:id,title', 'donor:id,name,email'])
                    ->latest()
                    ->limit(8)
                    ->get()
                    ->map(fn (Donation $donation) => [
                        'id' => $donation->id,
                        'campaign' => $donation->campaign?->title ?? 'Kampanye dihapus',
                        'donor' => $donation->donor?->name ?? $donation->donor_name ?? 'Anonim',
                        'amount' => $this->decryptAmount($donation->encrypted_amount, $paillier),
                        'status' => $donation->status,
                        'createdAt' => $donation->created_at?->translatedFormat('d M Y, H:i'),
                    ])
                    ->values(),
                'recentCampaigns' => Campaign::query()
                    ->with('organizer:id,name')
                    ->latest()
                    ->limit(6)
                    ->get()
                    ->map(fn (Campaign $campaign) => [
                        'id' => $campaign->id,
                        'slug' => $campaign->slug,
                        'title' => $campaign->title,
                        'organizer' => $campaign->organizer?->name ?? 'Penyelenggara',
                        'status' => $campaign->status,
                        'progress' => (float) $campaign->progress_percentage,
                        'target' => (int) $campaign->target_amount,
                    ])
                    ->values(),
            ]);
        }

        return Inertia::render('Dashboard', [
            'admin_bank' => AdminBankSetting::current()->only([
                'bank_name', 'bank_code', 'account_number', 'account_name', 'qris_image_path', 'instructions',
            ]),
            'campaigns' => $campaigns,
            'transactions' => $transactions,
        ]);
    }

    private function decryptAmount(?string $ciphertext, PaillierService $paillier): int
    {
        if (! $ciphertext) {
            return 0;
        }

        return $paillier->decrypt($ciphertext);
    }
}
