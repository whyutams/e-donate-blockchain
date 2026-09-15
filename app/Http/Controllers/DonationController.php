<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Donation;
use App\Services\DonationAmountEncryptor;
use App\Services\PaillierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends Controller
{
    public function store(Request $request, Campaign $campaign, DonationAmountEncryptor $encryptor): RedirectResponse
    {
        abort_unless($campaign->acceptsDonations(), 422, $campaign->donationAvailabilityMessage());

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1000', 'max:1000000000'],
            'payment_method' => ['required', 'string', 'max:50'],
            'donor_name' => ['nullable', 'string', 'max:100'],
            'donor_email' => ['nullable', 'email', 'max:100'],
            'donor_phone' => ['nullable', 'string', 'max:30'],
            'donor_note' => ['nullable', 'string', 'max:500'],
            'payment_proof' => ['nullable', 'image', 'max:5120'],
        ]);

        $amount = (int) $validated['amount'];

        // Enkripsi nominal donasi dengan homomorphic Paillier cryptosystem
        $encryptedAmount = $encryptor->encrypt($amount);
        $commitment = app(PaillierService::class)->commitment($encryptedAmount);

        // Generate Transaction Hash Kriptografis Blockchain (Format 0x + 64 hex SHA-256)
        $txHash = '0x' . hash('sha256', "donation:{$campaign->id}:" . Str::random(16) . ":{$encryptedAmount}:" . microtime(true));

        // Generate Nomor Blok Sekuensial
        $latestBlock = Donation::max('block_number') ?? 19842100;
        $blockNumber = $latestBlock + 1;

        // Kode Referensi Unik Transfer
        $referenceCode = 'SG-' . date('ymd') . '-' . strtoupper(Str::random(5));

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        Donation::create([
            'campaign_id' => $campaign->id,
            'donor_id' => $request->user()?->id,
            'donor_name' => $validated['donor_name'] ?? $request->user()?->name ?? 'Anonim',
            'donor_email' => $validated['donor_email'] ?? $request->user()?->email,
            'donor_phone' => $validated['donor_phone'] ?? null,
            'payment_method' => $validated['payment_method'],
            'reference_code' => $referenceCode,
            'payment_proof_path' => $proofPath,
            'donor_note' => $validated['donor_note'] ?? null,
            'encrypted_amount' => $encryptedAmount,
            'amount_commitment' => $commitment,
            'transaction_hash' => $txHash,
            'block_number' => $blockNumber,
            'status' => 'pending',
        ]);

        return back()->with('status', 'Donasi berhasil diajukan dengan hash blockchain ' . substr($txHash, 0, 10) . '... Silakan transfer ke rekening resmi SafeGive.');
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
                    'donor_name' => $donation->donor_name,
                    'payment_method' => $donation->payment_method,
                    'reference_code' => $donation->reference_code,
                    'payment_proof_url' => $donation->payment_proof_path ? asset('storage/' . $donation->payment_proof_path) : null,
                    'transaction_hash' => $donation->transaction_hash,
                    'block_number' => $donation->block_number,
                    'status' => $donation->status,
                    'created_at' => $donation->created_at?->toIso8601String(),
                    'confirmed_at' => $donation->confirmed_at?->toIso8601String(),
                ]),
        ]);
    }
}