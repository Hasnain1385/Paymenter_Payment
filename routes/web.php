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
        // Manual Verification Actions
        Route::post('/transactions/{id}/approve', [MultiBankAdminController::class, 'approve'])
            ->name('approve');

        Route::post('/transactions/{id}/decline', [MultiBankAdminController::class, 'decline'])
            ->name('decline');

        Route::post('/transactions/{id}/flag', [MultiBankAdminController::class, 'flag'])
            ->name('flag');

        // Dynamic Bank Accounts Management (Add, Edit, Remove, Toggle, Import)
        Route::get('/accounts', [MultiBankAdminController::class, 'indexAccounts'])
            ->name('accounts');

        Route::post('/accounts', [MultiBankAdminController::class, 'storeAccount'])
            ->name('accounts.store');

        Route::post('/accounts/{id}/update', [MultiBankAdminController::class, 'updateAccount'])
            ->name('accounts.update');

        Route::post('/accounts/{id}/delete', [MultiBankAdminController::class, 'deleteAccount'])
            ->name('accounts.delete');

        Route::post('/accounts/{id}/toggle', [MultiBankAdminController::class, 'toggleAccount'])
            ->name('accounts.toggle');

        Route::post('/accounts/import-config', [MultiBankAdminController::class, 'importFromConfig'])
            ->name('accounts.import');

        // In-Place GitHub Updates (Check, Apply In-Place, Push In-Place)
        Route::get('/updates', [MultiBankAdminController::class, 'indexUpdates'])
            ->name('updates');

        Route::post('/updates/check', [MultiBankAdminController::class, 'checkUpdates'])
            ->name('updates.check');

        Route::post('/updates/apply', [MultiBankAdminController::class, 'applyUpdate'])
            ->name('updates.apply');

        Route::post('/updates/push', [MultiBankAdminController::class, 'pushUpdates'])
            ->name('updates.push');
    });
});
