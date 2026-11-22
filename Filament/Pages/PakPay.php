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

    public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null): string
    {
        try {
            $panelObj = $panel ? \Filament\Facades\Filament::getPanel($panel) : (class_exists(\Filament\Facades\Filament::class) ? \Filament\Facades\Filament::getCurrentPanel() : null);
            $panelId = $panelObj ? $panelObj->getId() : 'admin';
            $routeName = "filament.{$panelId}.pages.pakpay";
            if (\Illuminate\Support\Facades\Route::has($routeName)) {
                return route($routeName, $parameters, $isAbsolute);
            }
        } catch (\Throwable $e) {}

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
            'version'           => defined(MultiBankQR::class . '::VERSION') ? MultiBankQR::VERSION : '1.3.6',
            'tab'               => request()->query('tab', 'accounts'),
        ];
    }
}
