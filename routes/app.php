<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\VerificationProfileController;
use App\Http\Controllers\AdminVerificationController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\AdminBankSettingController;
use App\Http\Controllers\AdminDonationController;
use App\Http\Controllers\AdminWithdrawalController;
use App\Http\Controllers\CampaignWithdrawalController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\EnsureUserIsAdmin;

// Public campaign list
Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');

Route::middleware('auth')->group(function () {
	Route::get('/dashboard', DashboardController::class)->name('dashboard');
	Route::get('/profile', function (Request $request) {
		return \Inertia\Inertia::render('Profile', [
			'verificationProfile' => $request->user()->verificationProfile?->only([
				'entity_type', 'legal_name', 'status', 'review_notes', 'verified_at',
			]),
		]);
	})->name('profile');
	Route::post('/verification-profile', [VerificationProfileController::class, 'store'])->name('verification-profile.store');
	Route::resource('campaigns', CampaignController::class)->only(['create', 'store', 'update', 'destroy']);
	Route::patch('/campaigns/{campaign}/video-url', [CampaignController::class, 'updateVideoUrl'])->name('campaigns.video-url.update');
	Route::get('/my-campaigns', [CampaignController::class, 'mine'])->name('campaigns.mine');
	Route::post('/campaigns/{campaign}/donations', [DonationController::class, 'store'])->name('campaigns.donations.store');
	Route::post('/campaigns/{campaign}/donations/snap', [MidtransController::class, 'createSnap'])->name('campaigns.donations.snap');
	Route::post('/donations/{donation}/sync-midtrans', [MidtransController::class, 'syncStatus'])->name('donations.sync-midtrans');
	Route::get('/transactions', [DonationController::class, 'history'])->name('transactions.index');
	Route::post('/campaigns/{campaign}/withdraw', [CampaignWithdrawalController::class, 'store'])->name('campaigns.withdraw');
	Route::get('/panduan', fn () => \Inertia\Inertia::render('Guide'))->name('guide');

	Route::middleware(EnsureUserIsAdmin::class)->prefix('admin')->name('admin.')->group(function () {
		Route::get('/verifications', [AdminVerificationController::class, 'index'])->name('verifications.index');
		Route::post('/verifications/{verificationProfile}/approve', [AdminVerificationController::class, 'approve'])->name('verifications.approve');
		Route::post('/verifications/{verificationProfile}/reject', [AdminVerificationController::class, 'reject'])->name('verifications.reject');
		Route::get('/verifications/{verificationProfile}/documents/{document}', [AdminVerificationController::class, 'document'])->name('verifications.document');

		Route::get('/bank-settings', [AdminBankSettingController::class, 'index'])->name('bank-settings.index');
		Route::post('/bank-settings', [AdminBankSettingController::class, 'update'])->name('bank-settings.update');

		Route::get('/donations', [AdminDonationController::class, 'index'])->name('donations.index');
		Route::post('/donations/{donation}/confirm', [AdminDonationController::class, 'confirm'])->name('donations.confirm');
		Route::post('/donations/{donation}/reject', [AdminDonationController::class, 'reject'])->name('donations.reject');

		Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
		Route::post('/withdrawals/{campaign}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
		Route::post('/withdrawals/{campaign}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');
	});
});

// Publicly viewable campaign detail route (must be defined AFTER /campaigns/create)
Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');