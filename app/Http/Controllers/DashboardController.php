<?php

namespace App\Http\Controllers;

use App\Models\AdminBankSetting;
use App\Models\Campaign;
use App\Models\Donation;
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
