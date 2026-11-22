<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Filament\Pages;

use Filament\Pages\Page;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankAccount;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction;
use Paymenter\Extensions\Gateways\MultiBankQR\Services\GithubUpdateService;
use Paymenter\Extensions\Gateways\MultiBankQR\MultiBankQR;

if (!class_exists(\Filament\Pages\Page::class)) {
    return;
}

class PakPay extends Page
{
    protected static bool $shouldRegisterNavigation = true;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public static function canView(): bool
    {
        return true;
    }

    public static function can(string $action, ?\Illuminate\Database\Eloquent\Model $record = null): bool
    {
        return true;
    }

    public static function shouldSkipAuthorization(): bool
    {
        return true;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Payments';
    }

    public static function getNavigationLabel(): string
    {
        return 'PakPay';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-credit-card';
    }

    public static function getNavigationUrl(): string
    {
        return url('/admin/multibankqr/pakpay');
    }

    public static function getNavigationItems(): array
    {
        if (!static::shouldRegisterNavigation()) {
            return [];
        }

        if (class_exists(\Filament\Navigation\NavigationItem::class)) {
            return [
                \Filament\Navigation\NavigationItem::make(static::getNavigationLabel())
                    ->group(static::getNavigationGroup())
                    ->icon(static::getNavigationIcon())
                    ->sort(static::getNavigationSort())
                    ->url(static::getNavigationUrl())
                    ->isActiveWhen(fn (): bool => request()->is('admin/multibankqr/pakpay*') || request()->is('extensions/multibankqr/admin/pakpay*') || request()->is('admin/pakpay*')),
            ];
        }

        return [];
    }

    public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null): string
    {
        return url('/admin/multibankqr/pakpay');
    }

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'PakPay';

    protected static ?string $title = 'PakPay Gateway Management';

    protected static ?string $slug = 'pakpay';

    protected static ?string $navigationGroup = 'Payments';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'multibankqr::filament.pages.pakpay';

    public function getViewData(): array
    {
        $accounts = MultiBankAccount::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        $transactions = MultiBankTransaction::orderBy('created_at', 'desc')->take(50)->get();

        $pendingCount = MultiBankTransaction::whereIn('status', [
            MultiBankTransaction::STATUS_PENDING_VERIFICATION,
            'pending',
            'under_review'
        ])->count();

        $verifiedCount = MultiBankTransaction::whereIn('status', [
            MultiBankTransaction::STATUS_VERIFIED,
            'approved',
            'paid'
        ])->count();

        $verifiedTotal = MultiBankTransaction::whereIn('status', [
            MultiBankTransaction::STATUS_VERIFIED,
            'approved',
            'paid'
        ])->sum('amount');

        $declinedCount = MultiBankTransaction::where('status', MultiBankTransaction::STATUS_DECLINED)->count();
        $totalTransactions = MultiBankTransaction::count();
        $updateInfo = null;
        try {
            $updateInfo = GithubUpdateService::checkForUpdates();
        } catch (\Throwable $e) {}

        return [
            'accounts'          => $accounts,
            'transactions'      => $transactions,
            'pendingCount'      => $pendingCount,
            'verifiedCount'     => $verifiedCount,
            'verifiedTotal'     => $verifiedTotal,
            'declinedCount'     => $declinedCount,
            'totalTransactions' => $totalTransactions,
            'updateInfo'        => $updateInfo,
            'version'           => defined(MultiBankQR::class . '::VERSION') ? MultiBankQR::VERSION : '1.3.8',
            'tab'               => request()->query('tab', 'accounts'),
        ];
    }
}
