<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
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

    public function show(Campaign $campaign): Response
    {
        $campaign->load(['organizer:id,name', 'donations' => fn ($query) => $query->latest()]);

        return Inertia::render('Campaigns/Show', [
            'campaign' => [
                ...$this->present($campaign),
                'wallet_address' => $campaign->wallet_address,
                'donation_open' => $campaign->acceptsDonations(),
                'donation_message' => $campaign->acceptsDonations() ? 'Donasi sedang dibuka.' : $campaign->donationAvailabilityMessage(),
                'donations' => $campaign->donations->map(fn ($donation) => [
                    'id' => $donation->id,
                    'transaction_hash' => $donation->transaction_hash,
                    'block_number' => $donation->block_number,
                    'status' => $donation->status,
                    'confirmed_at' => $donation->confirmed_at?->toIso8601String(),
                    'created_at' => $donation->created_at?->toIso8601String(),
                ])->values(),
            ],
        ]);
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
            'wallet_address' => ['required', 'string', 'max:255'],
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
            'wallet_address' => $validated['wallet_address'],
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
        ]);

        $campaign->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'target_amount' => $validated['target_amount'],
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'image_path' => $request->file('image')?->store('campaigns', 'public') ?? $campaign->image_path,
        ]);

        return back()->with('status', 'Kampanye berhasil diperbarui.');
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
            'donation_open' => $campaign->acceptsDonations(),
            'donation_message' => $campaign->acceptsDonations() ? 'Donasi sedang dibuka.' : $campaign->donationAvailabilityMessage(),
            'organizer' => $campaign->organizer,
        ];
    }
}