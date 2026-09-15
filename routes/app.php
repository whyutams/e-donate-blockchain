<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\VerificationProfileController;
use App\Http\Controllers\AdminVerificationController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\CampaignWithdrawalController;
use App\Http\Middleware\EnsureUserIsAdmin;



Route::middleware('auth')->group(function () {
	Route::get('/dashboard', fn () => \Inertia\Inertia::render('Dashboard'))->name('dashboard');
	Route::get('/profile', function (Request $request) {
		return \Inertia\Inertia::render('Profile', [
			'verificationProfile' => $request->user()->verificationProfile?->only([
				'entity_type', 'legal_name', 'status', 'review_notes', 'verified_at',
			]),
		]);
	})->name('profile');
	Route::post('/verification-profile', [VerificationProfileController::class, 'store'])->name('verification-profile.store');
	Route::resource('campaigns', CampaignController::class)->only(['index', 'show', 'create', 'store', 'update', 'destroy']);
	Route::get('/my-campaigns', [CampaignController::class, 'mine'])->name('campaigns.mine');
	Route::post('/campaigns/{campaign}/donations', [DonationController::class, 'store'])->name('campaigns.donations.store');
	Route::get('/transactions', [DonationController::class, 'history'])->name('transactions.index');
	Route::post('/campaigns/{campaign}/withdraw', [CampaignWithdrawalController::class, 'store'])->name('campaigns.withdraw');
	Route::get('/panduan', fn () => \Inertia\Inertia::render('Guide'))->name('guide');

	Route::middleware(EnsureUserIsAdmin::class)->prefix('admin')->name('admin.')->group(function () {
		Route::get('/verifications', [AdminVerificationController::class, 'index'])->name('verifications.index');
		Route::post('/verifications/{verificationProfile}/approve', [AdminVerificationController::class, 'approve'])->name('verifications.approve');
		Route::post('/verifications/{verificationProfile}/reject', [AdminVerificationController::class, 'reject'])->name('verifications.reject');
		Route::get('/verifications/{verificationProfile}/documents/{document}', [AdminVerificationController::class, 'document'])->name('verifications.document');
	});
	
});