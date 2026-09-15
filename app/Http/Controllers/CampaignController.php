<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Services\PaillierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function create(Request $request): Response
    {
        abort_unless($request->user()->verificationProfile?->status === 'verified', 403, 'Profil harus terverifikasi untuk membuat kampanye.');

        return Inertia::render('Campaigns/Create');
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Campaigns/Index', [
            'campaigns' => Campaign::query()
                ->with('organizer:id,name')
                ->whereIn('status', ['active', 'goal_reached'])
                ->latest()
                ->paginate(12)
                ->through(fn (Campaign $campaign) => $this->present($campaign)),
        ]);
    }

    public function mine(Request $request): Response
    {
        return Inertia::render('Campaigns/Mine', [
            'campaigns' => $request->user()->campaigns()
                ->latest()
                ->get()
                ->map(fn (Campaign $campaign) => $this->present($campaign))
                ->values(),
            'canCreate' => $request->user()->verificationProfile?->status === 'verified',
        ]);
    }

    public function show(Campaign $campaign, PaillierService $paillier): Response
    {
        $campaign->load(['organizer:id,name', 'donations' => fn ($query) => $query->latest()]);
        $collectedAmount = $this->collectedAmount($campaign, $paillier);
        $remainingAmount = max(0, (int) $campaign->target_amount - $collectedAmount);
        $progressPercentage = $campaign->target_amount > 0
            ? min(100, round(($collectedAmount / $campaign->target_amount) * 100, 2))
            : 0;
        $effectiveStatus = $campaign->status;
        if (in_array($campaign->status, ['active', 'goal_reached'], true)) {
            $effectiveStatus = $remainingAmount === 0 ? 'goal_reached' : 'active';
        }
        $isWithinSchedule = $campaign->starts_at && $campaign->ends_at
            && now()->betweenIncluded($campaign->starts_at, $campaign->ends_at);
        $donationOpen = $effectiveStatus !== 'withdrawn'
            && $effectiveStatus !== 'expired'
            && $isWithinSchedule
            && $remainingAmount > 0;

        return Inertia::render('Campaigns/Show', [
            'campaign' => [
                ...$this->present($campaign),
                'wallet_address' => $campaign->wallet_address,
                'payout_bank_name' => $campaign->payout_bank_name,
                'payout_account_number' => $campaign->payout_account_number,
                'payout_account_name' => $campaign->payout_account_name,
                'collected_amount' => $collectedAmount,
                'remaining_amount' => $remainingAmount,
                'progress_percentage' => $progressPercentage,
                'status' => $effectiveStatus,
                'donation_open' => $donationOpen,
                'donation_message' => $remainingAmount === 0 ? 'Target donasi sudah tercapai.' : ($donationOpen ? 'Donasi sedang dibuka.' : ($isWithinSchedule ? 'Donasi belum tersedia.' : $campaign->donationAvailabilityMessage())),
                'donations' => $campaign->donations->map(fn ($donation) => [
                    'id' => $donation->id,
                    'donor_name' => $donation->donor_name ?? 'Dermawan Baik',
                    'payment_method' => $donation->payment_method,
                    'reference_code' => $donation->reference_code,
                    'payment_proof_url' => $donation->payment_proof_path ? asset('storage/' . $donation->payment_proof_path) : null,
                    'transaction_hash' => $donation->transaction_hash,
                    'block_number' => $donation->block_number,
                    'status' => $donation->status,
                    'confirmed_at' => $donation->confirmed_at?->toIso8601String(),
                    'created_at' => $donation->created_at?->toIso8601String(),
                ])->values(),
            ],
        ]);
    }

    private function collectedAmount(Campaign $campaign, PaillierService $paillier): int
    {
        $ciphertexts = $campaign->donations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('encrypted_amount')
            ->all();

        return $ciphertexts === [] ? 0 : $paillier->decrypt($paillier->add(...$ciphertexts));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->verificationProfile?->status === 'verified', 403, 'Profil harus terverifikasi untuk membuat kampanye.');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'max:5120'],
            'target_amount' => ['required', 'integer', 'min:10000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'payout_bank_name' => ['required', 'string', 'max:100'],
            'payout_account_number' => ['required', 'string', 'max:50'],
            'payout_account_name' => ['required', 'string', 'max:100'],
            'wallet_address' => ['nullable', 'string', 'max:255'],
            'blockchain_campaign_id' => ['nullable', 'integer', 'min:0'],
            'video_url' => ['nullable', 'string', 'max:1000', 'regex:/^https?:\/\/(www\.)?(instagram\.com|instagr\.am|facebook\.com|fb\.watch|fb\.com|tiktok\.com|vt\.tiktok\.com|vm\.tiktok\.com)\/.+$/i'],
        ], [
            'video_url.regex' => 'Link video harus berupa URL valid dari Instagram, Facebook, atau TikTok.',
        ]);

        Campaign::create([
            'organizer_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(8)),
            'target_amount' => $validated['target_amount'],
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'image_path' => $request->file('image')?->store('campaigns', 'public'),
            'payout_bank_name' => $validated['payout_bank_name'],
            'payout_account_number' => $validated['payout_account_number'],
            'payout_account_name' => $validated['payout_account_name'],
            'wallet_address' => $validated['wallet_address'] ?? null,
            'blockchain_campaign_id' => $validated['blockchain_campaign_id'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'status' => 'active',
        ]);

        return redirect('/campaigns')->with('status', 'Kampanye berhasil dibuat.');
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        abort_unless($campaign->organizer_id === $request->user()->id, 403);
        abort_if(in_array($campaign->status, ['goal_reached', 'withdrawn'], true), 422, 'Kampanye sudah tidak dapat diubah.');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'max:5120'],
            'target_amount' => ['required', 'integer', 'min:10000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'payout_bank_name' => ['nullable', 'string', 'max:100'],
            'payout_account_number' => ['nullable', 'string', 'max:50'],
            'payout_account_name' => ['nullable', 'string', 'max:100'],
            'video_url' => ['nullable', 'string', 'max:1000', 'regex:/^https?:\/\/(www\.)?(instagram\.com|instagr\.am|facebook\.com|fb\.watch|fb\.com|tiktok\.com|vt\.tiktok\.com|vm\.tiktok\.com)\/.+$/i'],
        ], [
            'video_url.regex' => 'Link video harus berupa URL valid dari Instagram, Facebook, atau TikTok.',
        ]);

        $campaign->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'target_amount' => $validated['target_amount'],
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'payout_bank_name' => $validated['payout_bank_name'] ?? $campaign->payout_bank_name,
            'payout_account_number' => $validated['payout_account_number'] ?? $campaign->payout_account_number,
            'payout_account_name' => $validated['payout_account_name'] ?? $campaign->payout_account_name,
            'video_url' => array_key_exists('video_url', $validated) ? $validated['video_url'] : $campaign->video_url,
            'image_path' => $request->file('image')?->store('campaigns', 'public') ?? $campaign->image_path,
        ]);

        return back()->with('status', 'Kampanye berhasil diperbarui.');
    }

    public function updateVideoUrl(Request $request, Campaign $campaign): RedirectResponse
    {
        abort_unless($campaign->organizer_id === $request->user()->id, 403, 'Hanya penyelenggara yang dapat mengubah link video media sosial.');
        abort_unless($campaign->status === 'withdrawn' || $campaign->withdrawal_status === 'confirmed', 422, 'Link video media sosial hanya dapat disematkan atau diubah setelah status kampanye telah dicairkan (withdrawn).');

        $validated = $request->validate([
            'video_url' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^https?:\/\/(www\.)?(instagram\.com|instagr\.am|facebook\.com|fb\.watch|fb\.com|tiktok\.com|vt\.tiktok\.com|vm\.tiktok\.com)\/.+$/i',
            ],
        ], [
            'video_url.regex' => 'Link video harus berupa URL valid dari Instagram, Facebook, atau TikTok.',
        ]);

        $campaign->update([
            'video_url' => $validated['video_url'] ? trim($validated['video_url']) : null,
        ]);

        return back()->with('status', 'Link video media sosial kampanye berhasil disimpan.');
    }

    public function destroy(Request $request, Campaign $campaign): RedirectResponse
    {
        abort_unless($campaign->organizer_id === $request->user()->id, 403);
        abort_if($campaign->donations()->where('status', 'confirmed')->exists(), 422, 'Kampanye dengan donasi terkonfirmasi tidak dapat dihapus.');

        $campaign->delete();

        return back()->with('status', 'Kampanye berhasil dihapus.');
    }

    private function present(Campaign $campaign): array
    {
        return [
            'id' => $campaign->id,
            'title' => $campaign->title,
            'slug' => $campaign->slug,
            'description' => $campaign->description,
            'category' => $campaign->category,
            'image_url' => $campaign->image_path ? asset('storage/'.$campaign->image_path) : null,
            'target_amount' => $campaign->target_amount,
            'encrypted_collected_amount' => $campaign->encrypted_collected_amount,
            'progress_percentage' => (float) $campaign->progress_percentage,
            'donors_count' => $campaign->donors_count,
            'starts_at' => $campaign->starts_at?->toIso8601String(),
            'ends_at' => $campaign->ends_at?->toIso8601String(),
            'status' => $campaign->status,
            'payout_bank_name' => $campaign->payout_bank_name,
            'payout_account_number' => $campaign->payout_account_number,
            'payout_account_name' => $campaign->payout_account_name,
            'blockchain_campaign_id' => $campaign->blockchain_campaign_id,
            'video_url' => $campaign->video_url,
            'withdrawal_status' => $campaign->withdrawal_status ?? 'not_ready',
            'withdrawal_transaction_hash' => $campaign->withdrawal_transaction_hash,
            'can_withdraw' => $campaign->canWithdraw(),
            'withdrawal_message' => $campaign->withdrawalEligibilityMessage(),
            'donation_open' => $campaign->acceptsDonations(),
            'donation_message' => $campaign->acceptsDonations() ? 'Donasi sedang dibuka.' : $campaign->donationAvailabilityMessage(),
            'organizer' => $campaign->organizer,
        ];
    }
}