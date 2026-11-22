<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankTransactionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankTransactionResource;

if (!class_exists(\Filament\Resources\Pages\ManageRecords::class)) {
    return;
}

class ManageMultiBankTransactions extends ManageRecords
{
    protected static string $resource = MultiBankTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pakpayHub')
                ->label('⚡ PakPay Full Hub & Updates')
                ->url(url('/admin/multibankqr/pakpay'))
                ->color('success'),
        ];
    }
}
