<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Donation;
use App\Services\PaillierService;
use Illuminate\Support\Facades\App;

Artisan::command('paillier:keys {--force}', function (bool $force = false) {
    if (! $force && \Illuminate\Support\Facades\Storage::disk('private')->exists('paillier/keys.json')) {
        $this->info('Paillier keypair already exists.');

        return;
    }

    app(\App\Services\PaillierService::class)->generateKeys();
    $this->info('Paillier keypair generated in private storage.');
})->purpose('Generate the Paillier keypair used for donation encryption');

Artisan::command('donation:confirm {donation}', function (int $donation) {
    if (! App::environment('local')) {
        $this->error('Perintah testing ini hanya boleh dijalankan pada APP_ENV=local.');

        return 1;
    }

    $record = Donation::query()->with('campaign')->find($donation);

    if (! $record) {
        $this->error("Donasi #{$donation} tidak ditemukan.");
        $this->line('Jalankan "php artisan donation:list" untuk melihat ID donasi yang tersedia.');

        return 1;
    }

    if ($record->status === 'confirmed') {
        $this->warn("Donasi #{$record->id} sudah berstatus confirmed.");

        return 0;
    }

    if (! $record->campaign) {
        $this->error("Kampanye untuk donasi #{$record->id} tidak ditemukan.");

        return 1;
    }

    $record->update([
        'status' => 'confirmed',
        'confirmed_at' => now(),
    ]);

    $paillier = app(PaillierService::class);
    $campaign = $record->campaign;
    $ciphertexts = $campaign->donations()->where('status', 'confirmed')->pluck('encrypted_amount')->all();
    $aggregate = $paillier->add(...$ciphertexts);
    $total = $paillier->decrypt($aggregate);
    $campaign->update([
        'encrypted_collected_amount' => $aggregate,
        'progress_percentage' => min(round(($total / $campaign->target_amount) * 100, 2), 100),
        'donors_count' => count($ciphertexts),
        'status' => $total >= $campaign->target_amount ? 'goal_reached' : 'active',
    ]);

    $this->info("Donasi #{$record->id} dikonfirmasi untuk testing lokal.");
    $this->line("Progres hasil agregasi Paillier: {$campaign->progress_percentage}%");

    return 0;
})->purpose('Confirm a donation locally for testing without a blockchain listener');

Artisan::command('donation:list', function () {
    $donations = Donation::query()
        ->with('campaign:id,title')
        ->latest('id')
        ->get(['id', 'campaign_id', 'transaction_hash', 'status', 'created_at']);

    if ($donations->isEmpty()) {
        $this->warn('Belum ada donasi. Buat donasi melalui halaman detail kampanye terlebih dahulu.');

        return 0;
    }

    $this->table(
        ['ID', 'Kampanye', 'Status', 'Transaction hash', 'Dibuat'],
        $donations->map(fn (Donation $donation) => [
            $donation->id,
            $donation->campaign?->title ?? 'Kampanye dihapus',
            $donation->status,
            $donation->transaction_hash,
            $donation->created_at?->format('Y-m-d H:i:s'),
        ])->all(),
    );
})->purpose('List donation IDs for local testing');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
