<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\PaillierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDonationController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();

        $donations = Donation::query()
            ->with(['campaign:id,title,target_amount', 'donor:id,name,email'])
            ->when(in_array($status, ['pending', 'confirmed', 'failed'], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->through(fn (Donation $donation) => [
                'id' => $donation->id,
                'campaign_id' => $donation->campaign_id,
                'campaign_title' => $donation->campaign?->title ?? 'Kampanye dihapus',
                'donor_name' => $donation->donor_name ?? $donation->donor?->name ?? 'Anonim',
                'payment_method' => $donation->payment_method,
                'reference_code' => $donation->reference_code,
                'payment_proof_url' => $donation->payment_proof_path ? asset('storage/' . $donation->payment_proof_path) : null,
                'donor_note' => $donation->donor_note,
                'admin_notes' => $donation->admin_notes,
                'transaction_hash' => $donation->transaction_hash,
                'block_number' => $donation->block_number,
                'status' => $donation->status,
                'created_at' => $donation->created_at?->translatedFormat('d M Y, H:i'),
                'confirmed_at' => $donation->confirmed_at?->translatedFormat('d M Y, H:i'),
            ]);

        return Inertia::render('Admin/Donations/Index', [
            'donations' => $donations,
            'activeStatus' => $status ?: 'all',
        ]);
    }

    public function confirm(Donation $donation, PaillierService $paillier): RedirectResponse
    {
        if ($donation->status === 'confirmed') {
            return back()->with('status', 'Donasi ini sudah berstatus terkonfirmasi.');
        }

        $donation->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $campaign = $donation->campaign;
        if ($campaign) {
            $ciphertexts = $campaign->donations()->where('status', 'confirmed')->pluck('encrypted_amount')->all();
            $aggregate = $paillier->add(...$ciphertexts);
            $total = $paillier->decrypt($aggregate);

            $campaign->update([
                'encrypted_collected_amount' => $aggregate,
                'progress_percentage' => min(round(($total / $campaign->target_amount) * 100, 2), 100),
                'donors_count' => count($ciphertexts),
                'status' => $total >= $campaign->target_amount ? 'goal_reached' : $campaign->status,
            ]);
        }

        return back()->with('status', "Donasi #{$donation->id} berhasil dikonfirmasi dan dicatat ke agregasi enkripsi Paillier blockchain.");
    }

    public function reject(Request $request, Donation $donation): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ]);

        $donation->update([
            'status' => 'failed',
            'admin_notes' => $validated['admin_notes'],
        ]);

        return back()->with('status', "Donasi #{$donation->id} telah ditolak.");
    }
}
