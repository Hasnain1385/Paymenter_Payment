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
        // Standalone PSR-4 autoloader: ensures extension classes load reliably across any Paymenter core updates
        spl_autoload_register(function ($class) {
            $prefix = 'Paymenter\\Extensions\\Gateways\\MultiBankQR\\';
            if (str_starts_with($class, $prefix)) {
                $relativeClass = substr($class, strlen($prefix));
                $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        });

        // Load routes
        if (file_exists(__DIR__ . '/routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        } elseif (file_exists(__DIR__ . '/routes.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes.php');
        }

        // Load migrations
        if (file_exists(__DIR__ . '/database/migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        }

        // Load views
        if (file_exists(__DIR__ . '/resources/views')) {
            $this->loadViewsFrom(__DIR__ . '/resources/views', 'multibankqr');
        }

        // Register Filament resources & pages into Filament panels safely
        if (class_exists(\Filament\Facades\Filament::class)) {
            try {
                $registerWithPanel = function ($panel) {
                    if (!$panel) return;

                    // 1. Resources (MultiBankAccountResource, MultiBankTransactionResource)
                    if (method_exists($panel, 'resources')) {
                        $existing = method_exists($panel, 'getResources') ? $panel->getResources() : [];
                        $toRegister = [];
                        if (!in_array(\Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankAccountResource::class, $existing)) {
                            $toRegister[] = \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankAccountResource::class;
                        }
                        if (!in_array(\Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankTransactionResource::class, $existing)) {
                            $toRegister[] = \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankTransactionResource::class;
                        }
                        if (!empty($toRegister)) {
                            $panel->resources($toRegister);
                        }
                    }

                    // 2. Custom Pages (PakPay)
                    if (method_exists($panel, 'pages')) {
                        $existingPages = method_exists($panel, 'getPages') ? $panel->getPages() : [];
                        if (!in_array(\Paymenter\Extensions\Gateways\MultiBankQR\Filament\Pages\PakPay::class, $existingPages)) {
                            $panel->pages([
                                \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Pages\PakPay::class,
                            ]);
                        }
                    }
                };

                // Hook: Filament::serving callback
                if (method_exists(\Filament\Facades\Filament::class, 'serving')) {
                    \Filament\Facades\Filament::serving(function () use ($registerWithPanel) {
                        try {
                            $panel = \Filament\Facades\Filament::getCurrentPanel();
                            $registerWithPanel($panel);
                        } catch (\Throwable $e) {}
                    });
                }

                // Hook: ServingFilament event fallback
                if (class_exists(\Filament\Events\ServingFilament::class)) {
                    \Illuminate\Support\Facades\Event::listen(\Filament\Events\ServingFilament::class, function () use ($registerWithPanel) {
                        try {
                            $panel = \Filament\Facades\Filament::getCurrentPanel();
                            $registerWithPanel($panel);
                        } catch (\Throwable $e) {}
                    });
                }

                // Direct panel registration if panels already booted
                if (method_exists(\Filament\Facades\Filament::class, 'getPanels')) {
                    foreach (\Filament\Facades\Filament::getPanels() as $panel) {
                        try {
                            $registerWithPanel($panel);
                        } catch (\Throwable $e) {}
                    }
                }

                // Filament v2 fallback
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
