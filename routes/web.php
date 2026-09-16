<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['throttle:global'])->group(function () {
    Route::get('/', \App\Http\Controllers\LandingController::class)->name('home');
});

Route::middleware(['throttle:donations'])->prefix('api')->group(function () {
    Route::get('/donations/health', function () {
        return response()->json([
            'status' => 'active',
            'limiter' => 'donations (10 requests / min)',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    Route::post('/midtrans/notification', [\App\Http\Controllers\MidtransController::class, 'handleNotification'])->name('midtrans.notification');
});

Route::post('/midtrans/notification', [\App\Http\Controllers\MidtransController::class, 'handleNotification']);

require __DIR__.'/auth.php';
require __DIR__.'/app.php';