<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction;
use Paymenter\Extensions\Gateways\MultiBankQR\Services\PaymentApprovalService;

if (!class_exists(\Filament\Resources\Resource::class)) {
    return;
}

class MultiBankTransactionResource extends Resource
{
    protected static ?string $model = MultiBankTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Payments';

    protected static ?string $navigationLabel = 'Bank QR Proofs';

    protected static ?string $modelLabel = 'Bank QR Transaction';

    protected static ?int $navigationSort = 15;

    public static function getNavigationBadge(): ?string
    {
        $count = MultiBankTransaction::where('status', MultiBankTransaction::STATUS_PENDING)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('invoice_id')
                    ->label('Invoice ID')
                    ->disabled(),
                Forms\Components\TextInput::make('amount')
                    ->label('Amount')
                    ->disabled(),
                Forms\Components\TextInput::make('currency')
                    ->label('Currency')
                    ->disabled(),
                Forms\Components\TextInput::make('bank_account_name')
                    ->label('Receiving Account')
                    ->disabled(),
                Forms\Components\TextInput::make('transaction_id')
                    ->label('Transaction ID / UTR')
                    ->disabled(),
                Forms\Components\TextInput::make('sender_name')
                    ->label('Sender Name')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->label('Status')
                    ->disabled(),
                Forms\Components\Textarea::make('admin_notes')
                    ->label('Admin Notes (Visible to Customer if Flagged)')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('decline_reason')
                    ->label('Decline Reason')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_id')
                    ->label('Invoice #')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->url(fn (MultiBankTransaction $record): string => function_exists('route') && \Illuminate\Support\Facades\Route::has('filament.admin.resources.invoices.view')
                        ? route('filament.admin.resources.invoices.view', ['record' => $record->invoice_id])
                        : "#"
                    ),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),
                Tables\Columns\TextColumn::make('bank_account_name')
                    ->label('Bank Account')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('TRX / UTR ID')
                    ->copyable()
                    ->copyMessage('Transaction ID copied')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('sender_name')
                    ->label('Sender Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        MultiBankTransaction::STATUS_APPROVED => 'success',
                        MultiBankTransaction::STATUS_PENDING  => 'warning',
                        MultiBankTransaction::STATUS_DECLINED => 'danger',
                        MultiBankTransaction::STATUS_FLAGGED  => 'info',
                        default                               => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // View Screenshot Proof Action
                Action::make('viewProof')
                    ->label('View Proof')
                    ->icon('heroicon-o-photo')
                    ->color('gray')
                    ->modalHeading(fn (MultiBankTransaction $record) => "Proof for Invoice #{$record->invoice_id} — TRX: {$record->transaction_id}")
                    ->modalSubmitAction(false)
                    ->modalContent(fn (MultiBankTransaction $record) => view('multibankqr::filament.view-proof-modal', ['transaction' => $record]))
                    ->visible(fn (MultiBankTransaction $record) => !empty($record->proof_image)),

                // 1-Click Approve Action
                Action::make('approve')
                    ->label('Approve & Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Payment')
                    ->modalDescription(fn (MultiBankTransaction $record) => "Are you sure you want to approve Invoice #{$record->invoice_id}? This will mark the invoice as PAID and trigger order provisioning.")
                    ->action(function (MultiBankTransaction $record) {
                        $success = PaymentApprovalService::approve($record, auth()->id());
                        if ($success) {
                            Notification::make()
                                ->title('Invoice Marked as Paid')
                                ->body("Invoice #{$record->invoice_id} has been approved and paid.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Approval Failed')
                                ->body("Could not mark Invoice #{$record->invoice_id} as paid.")
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (MultiBankTransaction $record) => $record->status !== MultiBankTransaction::STATUS_APPROVED),

                // Flag / Under Review Action
                Action::make('flag')
                    ->label('Flag Issue')
                    ->icon('heroicon-o-flag')
                    ->color('info')
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('Note for Customer')
                            ->placeholder('e.g. Received partial amount, or TRX ID cannot be found. Please check and re-upload.')
                            ->required(),
                    ])
                    ->action(function (MultiBankTransaction $record, array $data) {
                        PaymentApprovalService::flag($record, $data['note'], auth()->id());
                        Notification::make()
                            ->title('Transaction Flagged')
                            ->body('Customer will see this note on their invoice screen.')
                            ->info()
                            ->send();
                    })
                    ->visible(fn (MultiBankTransaction $record) => $record->status !== MultiBankTransaction::STATUS_APPROVED),

                // Decline Action
                Action::make('decline')
                    ->label('Decline')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Decline Reason')
                            ->placeholder('e.g. Fraudulent transaction / unverified payment.')
                            ->required(),
                    ])
                    ->action(function (MultiBankTransaction $record, array $data) {
                        PaymentApprovalService::decline($record, $data['reason'], auth()->id());
                        Notification::make()
                            ->title('Transaction Declined')
                            ->body("Transaction #{$record->id} declined.")
                            ->danger()
                            ->send();
                    })
                    ->visible(fn (MultiBankTransaction $record) => $record->status !== MultiBankTransaction::STATUS_DECLINED),
            ]);
    }

    public static function getPages(): array
    {
        return [
            // Standard Filament resource page bindings
        ];
    }
}
