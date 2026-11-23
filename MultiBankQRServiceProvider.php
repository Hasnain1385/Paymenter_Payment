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

        // Register Filament navigation items into Filament panels safely (Zero 500 error risk)
        if (class_exists(\Filament\Facades\Filament::class)) {
            try {
                $registerNav = function ($panel = null) {
                    try {
                        $panel = $panel ?: (\Filament\Facades\Filament::getCurrentPanel() ?? null);
                        if (!$panel) return;

                        if (method_exists($panel, 'navigationItems')) {
                            static $registered = [];
                            $panelId = method_exists($panel, 'getId') ? $panel->getId() : 'default';
                            if (!empty($registered[$panelId])) return;
                            $registered[$panelId] = true;

                            $items = self::getPakPayNavigationItems();
                            if (!empty($items)) {
                                $panel->navigationItems($items);
                            }
                        }
                    } catch (\Throwable $e) {}
                };

                // Hook: Filament::serving callback
                if (method_exists(\Filament\Facades\Filament::class, 'serving')) {
                    \Filament\Facades\Filament::serving(function () use ($registerNav) {
                        $registerNav();
                    });
                }

                // Hook: ServingFilament event fallback
                if (class_exists(\Filament\Events\ServingFilament::class)) {
                    \Illuminate\Support\Facades\Event::listen(\Filament\Events\ServingFilament::class, function () use ($registerNav) {
                        $registerNav();
                    });
                }

                // Filament v2 fallback
                if (method_exists(\Filament\Facades\Filament::class, 'registerNavigationItems')) {
                    static $v2Done = false;
                    if (!$v2Done) {
                        $v2Done = true;
                        $items = self::getPakPayNavigationItems();
                        if (!empty($items)) {
                            \Filament\Facades\Filament::registerNavigationItems($items);
                        }
                    }
                }
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Build crash-proof Filament navigation items for PakPay under the 'Payments' group.
     */
    public static function getPakPayNavigationItems(): array
    {
        if (!class_exists(\Filament\Navigation\NavigationItem::class)) {
            return [];
        }

        try {
            $hubUrl = url('/admin/multibankqr/pakpay');
            $accountsUrl = url('/admin/multibankqr/accounts');
            $verifUrl = url('/admin/multibankqr/transactions');

            $items = [
                \Filament\Navigation\NavigationItem::make('PakPay')
                    ->group('Payments')
                    ->icon('heroicon-o-credit-card')
                    ->sort(1)
                    ->url($hubUrl)
                    ->isActiveWhen(fn (): bool => request()->is('admin/multibankqr/pakpay*') || request()->is('extensions/multibankqr/admin/pakpay*') || request()->is('admin/pakpay*')),

                \Filament\Navigation\NavigationItem::make('PakPay Accounts & QRs')
                    ->group('Payments')
                    ->icon('heroicon-o-building-library')
                    ->sort(2)
                    ->url($accountsUrl)
                    ->isActiveWhen(fn (): bool => request()->is('admin/multibankqr/accounts*') || request()->is('extensions/multibankqr/admin/accounts*')),

                \Filament\Navigation\NavigationItem::make('PakPay Verifications')
                    ->group('Payments')
                    ->icon('heroicon-o-check-badge')
                    ->sort(3)
                    ->url($verifUrl)
                    ->isActiveWhen(fn (): bool => request()->is('admin/multibankqr/transactions*') || request()->is('extensions/multibankqr/admin/transactions*')),
            ];

            // Optional pending count badge (safely wrapped so it never fails)
            try {
                if (class_exists(\Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction::class)) {
                    $pending = \Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction::whereIn('status', [
                        'pending_verification',
                        'pending',
                        'under_review'
                    ])->count();
                    if ($pending > 0 && isset($items[2])) {
                        $items[2]->badge((string) $pending);
                    }
                }
            } catch (\Throwable $e) {}

            return $items;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
