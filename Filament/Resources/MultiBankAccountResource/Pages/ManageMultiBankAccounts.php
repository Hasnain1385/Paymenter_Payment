<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankAccountResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankAccountResource;

if (!class_exists(\Filament\Resources\Pages\ManageRecords::class)) {
    return;
}

class ManageMultiBankAccounts extends ManageRecords
{
    protected static string $resource = MultiBankAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('➕ Add Bank Account')
                ->modalHeading('Add New Merchant Bank / Wallet Account'),
            Actions\Action::make('pakpayHub')
                ->label('⚡ PakPay Full Hub & Updates')
                ->url('/extensions/multibankqr/admin/pakpay')
                ->openUrlInNewTab()
                ->color('success'),
        ];
    }
}
