<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Donation;
use App\Services\DonationAmountEncryptor;
use App\Services\MidtransService;
use App\Services\PaillierService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MidtransController extends Controller
{
    /**
     * Membuat sesi donasi dan menghasilkan Snap Token untuk Midtrans.
     */
    public function createSnap(
        Request $request,
        Campaign $campaign,
        DonationAmountEncryptor $encryptor,
        MidtransService $midtrans
    ): JsonResponse {
        $remainingAmount = $this->remainingAmount($campaign);
        $isWithinSchedule = $campaign->starts_at && $campaign->ends_at
            && now()->betweenIncluded($campaign->starts_at, $campaign->ends_at);
        abort_unless(
            $isWithinSchedule && ! in_array($campaign->status, ['withdrawn', 'expired'], true),
            422,
            $campaign->donationAvailabilityMessage()
        );

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'donor_name' => ['nullable', 'string', 'max:100'],
            'is_anonymous' => ['nullable', 'boolean'],
            'donor_note' => ['nullable', 'string', 'max:500'],
        ]);

        $amount = (int) $validated['amount'];
        if ($remainingAmount === 0) {
            throw ValidationException::withMessages(['amount' => 'Target donasi sudah tercapai.']);
        }

        if ($amount > $remainingAmount) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal donasi maksimal '.number_format($remainingAmount, 0, ',', '.').' sesuai sisa target.',
            ]);
        }

        // Enkripsi nominal donasi dengan Paillier Cryptosystem
        $encryptedAmount = $encryptor->encrypt($amount);
        $commitment = app(PaillierService::class)->commitment($encryptedAmount);

        // Enkripsi nama donatur jika memilih sembunyikan nama (Paillier Cryptosystem)
        $isAnonymous = $request->boolean('is_anonymous');
        $rawDonorName = ! empty($validated['donor_name']) ? $validated['donor_name'] : ($request->user()?->name ?? 'Donatur Anonim');
        $encryptedDonorName = null;
        $displayDonorName = $rawDonorName;

        if ($isAnonymous) {
            $encryptedDonorName = app(PaillierService::class)->encryptString($rawDonorName);
            $displayDonorName = 'Anonim (Terenkripsi)';
        }

        // Transaction Hash Kriptografis Blockchain
        $txHash = '0x' . hash('sha256', "donation:midtrans:{$campaign->id}:" . Str::random(16) . ":{$encryptedAmount}:" . microtime(true));

        // Nomor Blok Sekuensial
        $latestBlock = Donation::max('block_number') ?? 19842100;
        $blockNumber = $latestBlock + 1;

        // Kode Referensi Unik Order ID Midtrans (Maksimal 50 karakter)
        $referenceCode = 'SG-' . date('ymd') . '-' . strtoupper(Str::random(6));

        $donation = Donation::create([
            'campaign_id' => $campaign->id,
            'donor_id' => $request->user()?->id,
            'is_anonymous' => $isAnonymous,
            'donor_name' => $displayDonorName,
            'encrypted_donor_name' => $encryptedDonorName,
            'payment_method' => 'midtrans',
            'reference_code' => $referenceCode,
            'donor_note' => $validated['donor_note'] ?? null,
            'encrypted_amount' => $encryptedAmount,
            'amount_commitment' => $commitment,
            'transaction_hash' => $txHash,
            'block_number' => $blockNumber,
            'status' => 'pending',
        ]);

        try {
            $customer = [
                'name' => $displayDonorName,
                'email' => $request->user()?->email ?? 'donor@example.com',
            ];

            $snap = $midtrans->createSnapTransaction($donation, $campaign, $amount, $customer);

            $donation->update([
                'snap_token' => $snap['token'],
                'snap_redirect_url' => $snap['redirect_url'],
            ]);

            return response()->json([
                'success' => true,
                'snap_token' => $snap['token'],
                'snap_redirect_url' => $snap['redirect_url'],
                'donation_id' => $donation->id,
                'reference_code' => $donation->reference_code,
                'transaction_hash' => $donation->transaction_hash,
            ]);
        } catch (Exception $e) {
            $donation->update([
                'status' => 'failed',
                'admin_notes' => 'Gagal membuat transaksi Midtrans: ' . $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke Midtrans. Pastikan Server Key Midtrans Sandbox sudah benar di Pengaturan Admin atau .env. Error: ' . $e->getMessage(),
            ], 422);
        }
    }

    private function remainingAmount(Campaign $campaign): int
    {
        $paillier = app(PaillierService::class);
        $ciphertexts = $campaign->donations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('encrypted_amount')
            ->all();
        $collected = $ciphertexts === [] ? 0 : $paillier->decrypt($paillier->add(...$ciphertexts));

        return max(0, (int) $campaign->target_amount - $collected);
    }

    /**
     * Webhook notifikasi dari Midtrans (server-to-server).
     */
    public function handleNotification(
        Request $request,
        MidtransService $midtrans,
        PaillierService $paillier
    ): JsonResponse {
        $payload = $request->all();
        Log::info('Midtrans Notification Callback:', $payload);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? 'accept';

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            return response()->json(['status' => 'error', 'message' => 'Payload tidak lengkap.'], 400);
        }

        // Verifikasi Signature Key Midtrans
        if (!$midtrans->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning("Midtrans notification signature invalid for order {$orderId}");
            return response()->json(['status' => 'error', 'message' => 'Signature key tidak valid.'], 403);
        }

        $donation = Donation::where('reference_code', $orderId)->first();
        if (!$donation) {
            Log::warning("Donation not found for order {$orderId}");
            return response()->json(['status' => 'error', 'message' => 'Donasi tidak ditemukan.'], 404);
        }

        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            if ($fraudStatus === 'accept') {
                $midtrans->processPaymentSuccess($donation, $payload, $paillier);
            }
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $donation->update([
                'status' => 'failed',
                'midtrans_response' => $payload,
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Notifikasi berhasil diproses.']);
    }

    /**
     * Sinkronisasi status donasi langsung ke Midtrans Status API.
     */
    public function syncStatus(
        Request $request,
        Donation $donation,
        MidtransService $midtrans,
        PaillierService $paillier
    ): JsonResponse|RedirectResponse {
        if (!$donation->reference_code) {
            $msg = 'Donasi ini tidak memiliki kode referensi transaksi.';
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        try {
            $status = $midtrans->checkTransactionStatus($donation->reference_code);
            $txStatus = $status['transaction_status'] ?? null;
            $fraudStatus = $status['fraud_status'] ?? 'accept';

            if (in_array($txStatus, ['capture', 'settlement']) && $fraudStatus === 'accept') {
                $midtrans->processPaymentSuccess($donation, $status, $paillier);
                $msg = "Pembayaran Midtrans berhasil terverifikasi! Status donasi kini: confirmed.";
            } elseif (in_array($txStatus, ['cancel', 'deny', 'expire'])) {
                $donation->update([
                    'status' => 'failed',
                    'midtrans_response' => $status,
                ]);
                $msg = "Status transaksi Midtrans: {$txStatus}.";
            } else {
                $msg = "Status pembayaran Midtrans saat ini: " . ($txStatus ?? 'pending');
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'status' => $donation->fresh()->status,
                    'transaction_status' => $txStatus,
                    'message' => $msg,
                ]);
            }

            return back()->with('status', $msg);
        } catch (Exception $e) {
            $errorMsg = 'Gagal memeriksa status ke Midtrans: ' . $e->getMessage();
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $errorMsg], 500)
                : back()->with('error', $errorMsg);
        }
    }
}
