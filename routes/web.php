<?php

use Illuminate\Support\Facades\Route;
use Paymenter\Extensions\Gateways\MultiBankQR\Http\Controllers\MultiBankController;
use Paymenter\Extensions\Gateways\MultiBankQR\Http\Controllers\MultiBankAdminController;

Route::middleware(['web'])->group(function () {
    // Customer Checkout View
    Route::get('/extensions/multibankqr/pay/{invoice}', [MultiBankController::class, 'showCheckout'])
        ->name('multibankqr.checkout');

    // Customer Proof Submission (Throttled: 15 per min)
    Route::post('/extensions/multibankqr/submit-proof/{invoice}', [MultiBankController::class, 'submitProof'])
        ->middleware('throttle:15,1')
        ->name('multibankqr.submit-proof');

    // Real-Time Status Polling (Throttled: 120 per min)
    Route::get('/extensions/multibankqr/status/{invoice}', [MultiBankController::class, 'checkStatus'])
        ->middleware('throttle:120,1')
        ->name('multibankqr.status');

    // Admin Verification Endpoints (Protected by Admin Auth inside Controller)
    Route::prefix('admin/multibankqr')->name('multibankqr.admin.')->group(function () {
        Route::post('/transactions/{id}/approve', [MultiBankAdminController::class, 'approve'])
            ->name('approve');

        Route::post('/transactions/{id}/decline', [MultiBankAdminController::class, 'decline'])
            ->name('decline');

        Route::post('/transactions/{id}/flag', [MultiBankAdminController::class, 'flag'])
            ->name('flag');
    });
});
