<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampaignWithdrawalController extends Controller
{
    public function store(Request $request, Campaign $campaign): RedirectResponse
    {
        abort_unless($campaign->organizer_id === $request->user()->id, 403, 'Hanya penyelenggara yang dapat mengajukan pencairan dana.');
        abort_unless($campaign->canWithdraw(), 422, $campaign->withdrawalEligibilityMessage());

        $validated = $request->validate([
            'payout_bank_name' => ['required', 'string', 'max:100'],
            'payout_account_number' => ['required', 'string', 'max:50'],
            'payout_account_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Generate Transaction Hash Kriptografis Blockchain untuk Pencairan
        $txHash = '0x' . hash('sha256', "withdrawal:{$campaign->id}:" . Str::random(16) . ':' . microtime(true));

        $campaign->update([
            'payout_bank_name' => $validated['payout_bank_name'],
            'payout_account_number' => $validated['payout_account_number'],
            'payout_account_name' => $validated['payout_account_name'],
            'withdrawal_transaction_hash' => $txHash,
            'withdrawal_status' => 'pending',
            'withdrawal_notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('status', 'Permintaan pencairan dana berhasil diajukan dengan hash kriptografis ' . substr($txHash, 0, 10) . '... Admin akan mentransfer dana ke rekening yang ditentukan.');
    }
}