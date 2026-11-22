<?php

use Illuminate\Support\Facades\Route;
use Paymenter\Extensions\Gateways\MultiBankQR\Http\Controllers\MultiBankController;
use Paymenter\Extensions\Gateways\MultiBankQR\Http\Controllers\MultiBankAdminController;

if (!class_exists(\Illuminate\Support\Facades\Route::class)) {
    return;
}

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
        // Dedicated PakPay Management Hub
        Route::get('/pakpay', [MultiBankAdminController::class, 'indexDashboard'])
            ->name('pakpay');
        Route::get('/', [MultiBankAdminController::class, 'indexDashboard'])
            ->name('dashboard');

        // Payment History & Manual Verification Dashboard
        Route::get('/transactions', [MultiBankAdminController::class, 'indexTransactions'])
            ->name('transactions');

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

    // Direct Admin Sidebar Route for PakPay
    Route::get('/admin/pakpay', [MultiBankAdminController::class, 'indexDashboard'])
        ->name('multibankqr.admin.pakpay.direct');

    // Extension Admin Route Group (Bypasses any Filament /admin panel route interception)
    Route::prefix('extensions/multibankqr/admin')->group(function () {
        Route::get('/pakpay', [MultiBankAdminController::class, 'indexDashboard']);
        Route::get('/', [MultiBankAdminController::class, 'indexDashboard']);
        Route::get('/accounts', [MultiBankAdminController::class, 'indexAccounts']);
        Route::get('/transactions', [MultiBankAdminController::class, 'indexTransactions']);
        Route::get('/updates', [MultiBankAdminController::class, 'indexUpdates']);
        Route::post('/transactions/{id}/approve', [MultiBankAdminController::class, 'approve']);
        Route::post('/transactions/{id}/decline', [MultiBankAdminController::class, 'decline']);
        Route::post('/transactions/{id}/flag', [MultiBankAdminController::class, 'flag']);
        Route::post('/accounts', [MultiBankAdminController::class, 'storeAccount']);
        Route::post('/accounts/{id}/update', [MultiBankAdminController::class, 'updateAccount']);
        Route::post('/accounts/{id}/delete', [MultiBankAdminController::class, 'deleteAccount']);
        Route::post('/accounts/{id}/toggle', [MultiBankAdminController::class, 'toggleAccount']);
        Route::post('/accounts/import-config', [MultiBankAdminController::class, 'importFromConfig']);
        Route::post('/updates/check', [MultiBankAdminController::class, 'checkUpdates']);
        Route::post('/updates/apply', [MultiBankAdminController::class, 'applyUpdate']);
        Route::post('/updates/push', [MultiBankAdminController::class, 'pushUpdates']);
    });
});
