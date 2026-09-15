<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('paillier:keys {--force}', function (bool $force = false) {
    if (! $force && \Illuminate\Support\Facades\Storage::disk('private')->exists('paillier/keys.json')) {
        $this->info('Paillier keypair already exists.');

        return;
    }

    app(\App\Services\PaillierService::class)->generateKeys();
    $this->info('Paillier keypair generated in private storage.');
})->purpose('Generate the Paillier keypair used for donation encryption');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
