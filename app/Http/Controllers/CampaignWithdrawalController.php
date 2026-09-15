<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignWithdrawalController extends Controller
{
    public function store(Request $request, Campaign $campaign): JsonResponse
    {
        abort_unless($campaign->organizer_id === $request->user()->id, 403);
        abort_unless($campaign->canWithdraw(), 422, 'Kampanye belum memenuhi syarat pencairan atau sudah diproses.');

        $validated = $request->validate([
            'transaction_hash' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{64}$/', 'unique:campaigns,withdrawal_transaction_hash'],
        ]);

        $campaign->update([
            'withdrawal_transaction_hash' => $validated['transaction_hash'],
            'withdrawal_status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Transaksi pencairan dikirim dan menunggu konfirmasi blockchain.',
            'status' => 'pending',
        ]);
    }
}