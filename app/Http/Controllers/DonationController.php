<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Donation;
use App\Services\DonationAmountEncryptor;
use App\Services\PaillierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends Controller
{
    public function store(Request $request, Campaign $campaign, DonationAmountEncryptor $encryptor): RedirectResponse
    {
        abort_unless($campaign->acceptsDonations(), 422, $campaign->donationAvailabilityMessage());

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1000', 'max:1000000000'],
            'transaction_hash' => ['required', 'string', 'max:255', 'unique:donations,transaction_hash'],
            'block_number' => ['nullable', 'integer', 'min:0'],
        ]);

        $amount = (int) $validated['amount'];

        $encryptedAmount = $encryptor->encrypt($amount);

        Donation::create([
            'campaign_id' => $campaign->id,
            'donor_id' => $request->user()?->id,
            'encrypted_amount' => $encryptedAmount,
            'amount_commitment' => app(PaillierService::class)->commitment($encryptedAmount),
            'transaction_hash' => $validated['transaction_hash'],
            'block_number' => $validated['block_number'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('status', 'Donasi tercatat dan menunggu konfirmasi blockchain.');
    }

    public function history(Request $request): Response
    {
        return Inertia::render('Transactions/Index', [
            'transactions' => Donation::query()
                ->with('campaign:id,title')
                ->where('donor_id', $request->user()->id)
                ->latest()
                ->paginate(20)
                ->through(fn (Donation $donation) => [
                    'id' => $donation->id,
                    'campaign_id' => $donation->campaign_id,
                    'campaign' => $donation->campaign?->title ?? 'Kampanye dihapus',
                    'transaction_hash' => $donation->transaction_hash,
                    'block_number' => $donation->block_number,
                    'status' => $donation->status,
                    'created_at' => $donation->created_at?->toIso8601String(),
                    'confirmed_at' => $donation->confirmed_at?->toIso8601String(),
                ]),
        ]);
    }
}