<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

if (!class_exists(\Illuminate\Support\ServiceProvider::class)) {
    eval('namespace Illuminate\Support { class ServiceProvider { public function __construct($app = null) {} public function loadRoutesFrom($path) {} public function loadMigrationsFrom($path) {} public function loadViewsFrom($path, $namespace) {} } }');
}

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

        // Register Filament resources & pages into Filament panels
        if (class_exists(\Filament\Facades\Filament::class)) {
            try {
                $registerWithPanel = function ($panel) {
                    if (!$panel) return;
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
                };

                // 1. ServingFilament event hook
                if (class_exists(\Filament\Events\ServingFilament::class)) {
                    \Illuminate\Support\Facades\Event::listen(\Filament\Events\ServingFilament::class, function () use ($registerWithPanel) {
                        try {
                            $panel = \Filament\Facades\Filament::getCurrentPanel();
                            $registerWithPanel($panel);
                        } catch (\Throwable $e) {}
                    });
                }

                // 2. Direct panel registration if panels already booted
                if (method_exists(\Filament\Facades\Filament::class, 'getPanels')) {
                    foreach (\Filament\Facades\Filament::getPanels() as $panel) {
                        try {
                            $registerWithPanel($panel);
                        } catch (\Throwable $e) {}
                    }
                }

                // 3. Filament v2 fallback
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
