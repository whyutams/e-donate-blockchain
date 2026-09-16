<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminWithdrawalController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();

        $campaigns = Campaign::query()
            ->with('organizer:id,name,email')
            ->whereIn('withdrawal_status', ['pending', 'confirmed', 'failed'])
            ->when(in_array($status, ['pending', 'confirmed', 'failed'], true), fn ($query) => $query->where('withdrawal_status', $status))
            ->latest('updated_at')
            ->paginate(15)
            ->through(fn (Campaign $campaign) => [
                'id' => $campaign->id,
                'title' => $campaign->title,
                'organizer_name' => $campaign->organizer?->name ?? 'Penyelenggara',
                'organizer_email' => $campaign->organizer?->email,
                'target_amount' => $campaign->target_amount,
                'progress_percentage' => (float) $campaign->progress_percentage,
                'payout_bank_name' => $campaign->payout_bank_name,
                'payout_account_number' => $campaign->payout_account_number,
                'payout_account_name' => $campaign->payout_account_name,
                'withdrawal_transaction_hash' => $campaign->withdrawal_transaction_hash,
                'withdrawal_status' => $campaign->withdrawal_status,
                'withdrawal_proof_url' => $campaign->withdrawal_proof_path ? asset('storage/' . $campaign->withdrawal_proof_path) : null,
                'withdrawal_notes' => $campaign->withdrawal_notes,
                'withdrawn_at' => $campaign->withdrawn_at?->translatedFormat('d M Y, H:i'),
                'ends_at' => $campaign->ends_at?->translatedFormat('d M Y, H:i'),
            ]);

        return Inertia::render('Admin/Withdrawals/Index', [
            'campaigns' => $campaigns,
            'activeStatus' => $status ?: 'all',
        ]);
    }

    public function approve(Request $request, Campaign $campaign): RedirectResponse
    {
        abort_unless($campaign->withdrawal_status === 'pending', 422, 'Pengajuan pencairan tidak dalam status pending.');

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('withdrawal_proofs', 'public');
        }

        $campaign->update([
            'withdrawal_status' => 'confirmed',
            'status' => 'withdrawn',
            'withdrawn_at' => now(),
            'withdrawal_proof_path' => $proofPath ?? $campaign->withdrawal_proof_path,
            'withdrawal_notes' => $request->input('notes') ?? $campaign->withdrawal_notes,
        ]);

        return back()->with('status', "Pencairan dana untuk kampanye '{$campaign->title}' berhasil disetujui.");
    }

    public function reject(Request $request, Campaign $campaign): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:500'],
        ]);

        $campaign->update([
            'withdrawal_status' => 'not_ready',
            'withdrawal_notes' => $validated['notes'],
        ]);

        return back()->with('status', "Pengajuan pencairan dana kampanye '{$campaign->title}' ditolak.");
    }
}
