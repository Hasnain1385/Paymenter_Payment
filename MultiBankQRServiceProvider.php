<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class MultiBankQRServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Load routes
        if (file_exists(__DIR__ . '/routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        }

        // Load migrations
        if (file_exists(__DIR__ . '/database/migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        }

        // Load views
        if (file_exists(__DIR__ . '/resources/views')) {
            $this->loadViewsFrom(__DIR__ . '/resources/views', 'multibankqr');
        }

        // Register Filament resources if Filament is available
        if (class_exists(\Filament\Facades\Filament::class) && method_exists(\Filament\Facades\Filament::class, 'registerResources')) {
            try {
                \Filament\Facades\Filament::registerResources([
                    \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankAccountResource::class,
                    \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankTransactionResource::class,
                ]);
            } catch (\Throwable $e) {}
        }
    }
}
