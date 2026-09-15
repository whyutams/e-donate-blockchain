<?php

namespace App\Services;

use App\Models\AdminBankSetting;
use App\Models\Campaign;
use App\Models\Donation;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap as MidtransSnap;
use Midtrans\Transaction as MidtransTransaction;

class MidtransService
{
    public function __construct()
    {
        $this->configure();
    }

    public function configure(): void
    {
        MidtransConfig::$serverKey = $this->getServerKey();
        MidtransConfig::$isProduction = $this->isProduction();
        MidtransConfig::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        MidtransConfig::$is3ds = (bool) config('services.midtrans.is_3ds', true);
    }

    public function getServerKey(): string
    {
        $setting = AdminBankSetting::current();
        return (string) ($setting->getMidtransServerKey() ?: config('services.midtrans.server_key', ''));
    }

    public function getClientKey(): string
    {
        $setting = AdminBankSetting::current();
        return (string) ($setting->getMidtransClientKey() ?: config('services.midtrans.client_key', ''));
    }

    public function isProduction(): bool
    {
        $setting = AdminBankSetting::current();
        return (bool) $setting->isMidtransProduction();
    }

    public function isConfigured(): bool
    {
        $serverKey = $this->getServerKey();
        $clientKey = $this->getClientKey();
        return !empty($serverKey) && !empty($clientKey) && !str_contains($serverKey, 'your-sandbox');
    }

    public function getSnapJsUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    public function getApiBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    /**
     * Membuat sesi Snap Token untuk donasi.
     */
    public function createSnapTransaction(Donation $donation, Campaign $campaign, int $amount, array $customer): array
    {
        $this->configure();

        $orderId = $donation->reference_code;
        $campaignTitle = mb_substr('Donasi: ' . $campaign->title, 0, 45);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $customer['name'] ?? 'Donatur SafeGive',
                'email' => $customer['email'] ?? 'donor@example.com',
                'phone' => $customer['phone'] ?? '081234567890',
            ],
            'item_details' => [
                [
                    'id' => 'CAMP-' . $campaign->id,
                    'price' => $amount,
                    'quantity' => 1,
                    'name' => $campaignTitle,
                ],
            ],
            'callbacks' => [
                'finish' => url("/campaigns/{$campaign->id}?finish_donation={$donation->id}"),
            ],
        ];

        try {
            $snapResponse = MidtransSnap::createTransaction($params);

            $token = is_object($snapResponse) ? $snapResponse->token : ($snapResponse['token'] ?? null);
            $redirectUrl = is_object($snapResponse) ? $snapResponse->redirect_url : ($snapResponse['redirect_url'] ?? null);

            if (!$token) {
                throw new Exception('Gagal mendapatkan Snap Token dari Midtrans.');
            }

            return [
                'token' => $token,
                'redirect_url' => $redirectUrl,
            ];
        } catch (Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage(), ['params' => $params]);
            throw $e;
        }
    }

    /**
     * Memvalidasi Signature Key webhook notifikasi dari Midtrans.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $serverKey = $this->getServerKey();
        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        return hash_equals($expected, $signatureKey);
    }

    /**
     * Memeriksa status transaksi secara real-time ke Midtrans API.
     */
    public function checkTransactionStatus(string $orderId): array
    {
        $this->configure();

        try {
            $status = MidtransTransaction::status($orderId);
            return (array) $status;
        } catch (Exception $e) {
            // Fallback ke HTTP Client jika SDK throws
            $serverKey = $this->getServerKey();
            $url = "{$this->getApiBaseUrl()}/{$orderId}/status";

            $response = Http::withBasicAuth($serverKey, '')
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("Midtrans status check failed for {$orderId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Memproses status pembayaran berhasil: update donasi & hitung ulang agregasi Paillier.
     */
    public function processPaymentSuccess(Donation $donation, array $payload, PaillierService $paillier): bool
    {
        if ($donation->status === 'confirmed') {
            return true;
        }

        $paymentType = $payload['payment_type'] ?? $donation->payment_method ?? 'midtrans';
        $transactionId = $payload['transaction_id'] ?? null;

        $donation->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'payment_method' => 'midtrans_' . $paymentType,
            'midtrans_transaction_id' => $transactionId,
            'midtrans_payment_type' => $paymentType,
            'midtrans_response' => $payload,
        ]);

        // Agregasi Paillier Homomorfik pada kampanye
        $campaign = $donation->campaign;
        if ($campaign) {
            $ciphertexts = $campaign->donations()->where('status', 'confirmed')->pluck('encrypted_amount')->all();
            if (!empty($ciphertexts)) {
                $aggregate = $paillier->add(...$ciphertexts);
                $total = $paillier->decrypt($aggregate);

                $campaign->update([
                    'encrypted_total_amount' => $aggregate,
                    'progress_percentage' => $campaign->target_amount > 0 ? min(100, (int) round(($total / $campaign->target_amount) * 100)) : 100,
                    'donors_count' => $campaign->donations()->where('status', 'confirmed')->distinct('donor_id')->count('donor_id'),
                    'status' => ($total >= $campaign->target_amount) ? 'goal_reached' : $campaign->status,
                ]);
            }
        }

        Log::info("Donation {$donation->id} ({$donation->reference_code}) successfully confirmed via Midtrans.");
        return true;
    }
}
