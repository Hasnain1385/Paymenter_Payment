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

        // Register Filament sidebar navigation safely if Filament is available
        if (class_exists(\Filament\Navigation\NavigationItem::class)) {
            try {
                // Hook into ServingFilament event to attach navigation items to the panel
                if (class_exists(\Filament\Events\ServingFilament::class)) {
                    \Illuminate\Support\Facades\Event::listen(\Filament\Events\ServingFilament::class, function () {
                        try {
                            $panel = class_exists(\Filament\Facades\Filament::class) ? \Filament\Facades\Filament::getCurrentPanel() : null;
                            if ($panel) {
                                self::registerFilamentNavigation($panel);
                            }
                        } catch (\Throwable $e) {}
                    });
                }

                // Also register on available panels directly if already initialized
                if (class_exists(\Filament\Facades\Filament::class) && method_exists(\Filament\Facades\Filament::class, 'getPanels')) {
                    foreach (\Filament\Facades\Filament::getPanels() as $panel) {
                        try {
                            self::registerFilamentNavigation($panel);
                        } catch (\Throwable $e) {}
                    }
                }
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Safely register PakPay sidebar navigation items without touching panel resources
     * to prevent RouteNotFoundException errors from crashing the Filament admin panel.
     */
    public static function registerFilamentNavigation($panel = null): void
    {
        if (!class_exists(\Filament\Navigation\NavigationItem::class)) {
            return;
        }

        try {
            $items = [
                \Filament\Navigation\NavigationItem::make('PakPay')
                    ->url(url('/admin/multibankqr/pakpay'))
                    ->icon('heroicon-o-credit-card')
                    ->group('Payments')
                    ->isActiveWhen(fn (): bool => request()->is('admin/multibankqr/pakpay*') || request()->is('admin/pakpay*'))
                    ->sort(1),
                \Filament\Navigation\NavigationItem::make('PakPay Accounts')
                    ->url(url('/admin/multibankqr/accounts'))
                    ->icon('heroicon-o-building-library')
                    ->group('Payments')
                    ->isActiveWhen(fn (): bool => request()->is('admin/multibankqr/accounts*'))
                    ->sort(2),
                \Filament\Navigation\NavigationItem::make('PakPay Verifications')
                    ->url(url('/admin/multibankqr/transactions'))
                    ->icon('heroicon-o-check-badge')
                    ->badge(function () {
                        try {
                            if (class_exists(\Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction::class)) {
                                return \Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction::whereIn('status', ['pending_verification', 'pending'])->count() ?: null;
                            }
                        } catch (\Throwable $e) {}
                        return null;
                    })
                    ->badgeColor('warning')
                    ->isActiveWhen(fn (): bool => request()->is('admin/multibankqr/transactions*'))
                    ->sort(3),
            ];

            // Filament v3 panel navigation
            if ($panel && method_exists($panel, 'navigationItems')) {
                $panel->navigationItems($items);
                return;
            }

            // Filament v2 fallback
            if (class_exists(\Filament\Facades\Filament::class) && method_exists(\Filament\Facades\Filament::class, 'registerNavigationItems')) {
                \Filament\Facades\Filament::registerNavigationItems($items);
            }
        } catch (\Throwable $e) {}
    }
}
