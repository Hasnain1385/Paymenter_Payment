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

        // Register Filament pages, resources & sidebar navigation if Filament is available
        if (class_exists(\Filament\Facades\Filament::class)) {
            try {
                // Filament v3 Event hook to register resources and pages when panel is served
                if (class_exists(\Filament\Events\ServingFilament::class)) {
                    \Illuminate\Support\Facades\Event::listen(\Filament\Events\ServingFilament::class, function () {
                        try {
                            $panel = \Filament\Facades\Filament::getCurrentPanel();
                            if ($panel) {
                                if (method_exists($panel, 'resources')) {
                                    $panel->resources([
                                        \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankAccountResource::class,
                                        \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankTransactionResource::class,
                                    ]);
                                }
                                if (method_exists($panel, 'pages')) {
                                    $panel->pages([
                                        \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Pages\PakPay::class,
                                    ]);
                                }
                            }
                        } catch (\Throwable $e) {}
                    });
                }

                // Panel-level resource and page registration for Filament v3
                if (method_exists(\Filament\Facades\Filament::class, 'getPanels')) {
                    foreach (\Filament\Facades\Filament::getPanels() as $panel) {
                        if (method_exists($panel, 'resources')) {
                            $panel->resources([
                                \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankAccountResource::class,
                                \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankTransactionResource::class,
                            ]);
                        }
                        if (method_exists($panel, 'pages')) {
                            $panel->pages([
                                \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Pages\PakPay::class,
                            ]);
                        }
                    }
                }

                // Legacy Filament v2 fallback
                if (method_exists(\Filament\Facades\Filament::class, 'registerPages')) {
                    \Filament\Facades\Filament::registerPages([
                        \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Pages\PakPay::class,
                    ]);
                }
                if (method_exists(\Filament\Facades\Filament::class, 'registerResources')) {
                    \Filament\Facades\Filament::registerResources([
                        \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankAccountResource::class,
                        \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankTransactionResource::class,
                    ]);
                }
            } catch (\Throwable $e) {}
        }
    }
}
