<?php

use Illuminate\Support\Facades\Route;
use Paymenter\Extensions\Gateways\MultiBankQR\Http\Controllers\MultiBankController;
use Paymenter\Extensions\Gateways\MultiBankQR\Http\Controllers\MultiBankAdminController;

Route::middleware(['web'])->group(function () {
    // Customer Checkout & Proof Submission
    Route::get('/extensions/multibankqr/pay/{invoice}', [MultiBankController::class, 'showCheckout'])
        ->name('multibankqr.checkout');

    Route::post('/extensions/multibankqr/submit-proof/{invoice}', [MultiBankController::class, 'submitProof'])
        ->name('multibankqr.submit-proof');

    Route::get('/extensions/multibankqr/status/{invoice}', [MultiBankController::class, 'checkStatus'])
        ->name('multibankqr.status');

    // Admin Verification Endpoints
    Route::prefix('admin/multibankqr')->name('multibankqr.admin.')->group(function () {
        Route::post('/transactions/{id}/approve', [MultiBankAdminController::class, 'approve'])
            ->name('approve');

        Route::post('/transactions/{id}/decline', [MultiBankAdminController::class, 'decline'])
            ->name('decline');

        Route::post('/transactions/{id}/flag', [MultiBankAdminController::class, 'flag'])
            ->name('flag');
    });
});
