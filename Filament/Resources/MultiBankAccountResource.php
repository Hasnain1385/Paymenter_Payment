<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankAccount;
use Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankAccountResource\Pages;

if (!class_exists(\Filament\Resources\Resource::class)) {
    return;
}

class MultiBankAccountResource extends Resource
{
    protected static ?string $model = MultiBankAccount::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Payments';

    protected static ?string $navigationLabel = 'Bank Accounts & QRs';

    protected static ?string $modelLabel = 'Bank Account';

    protected static ?int $navigationSort = 16;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Bank & Method Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Bank / Method Name')
                            ->placeholder('e.g. Chase Bank / Zelle, Revolut EUR, Wise USD, HDFC UPI')
                            ->required(),
                        Forms\Components\TextInput::make('badge')
                            ->label('Badge / Tag')
                            ->placeholder('e.g. Recommended, Instant, Zero Fee, Domestic')
                            ->default('Direct'),
                        Forms\Components\TextInput::make('account_title')
                            ->label('Account Holder Title / Name')
                            ->placeholder('e.g. John Doe LLC')
                            ->required(),
                        Forms\Components\TextInput::make('account_number')
                            ->label('Account Number / IBAN / UPI ID / Wallet')
                            ->placeholder('e.g. 1234567890 or user@upi or 0x...')
                            ->required(),
                        Forms\Components\TextInput::make('routing_code')
                            ->label('Routing / SWIFT / BIC / IFSC / Branch')
                            ->placeholder('e.g. ROUTING123 or SWIFTCODE'),
                        Forms\Components\TextInput::make('currency')
                            ->label('Supported Currency')
                            ->placeholder('ALL or USD, EUR, PKR, INR, GBP')
                            ->default('ALL')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('QR Code & Instructions')
                    ->schema([
                        Forms\Components\TextInput::make('qr_payload')
                            ->label('Dynamic QR Code Payload (Optional)')
                            ->placeholder('upi://pay?pa=acc@upi&pn=Business&am={amount} or payment link')
                            ->helperText('Supports tokens: {amount}, {invoice_id}. Leave empty to use account number.'),
                        Forms\Components\FileUpload::make('qr_image_url')
                            ->label('Static QR Code Image (Optional)')
                            ->disk('public')
                            ->directory('multibank_qrcodes')
                            ->image()
                            ->imagePreviewHeight('150')
                            ->helperText('Upload a pre-generated QR code image if you prefer not to generate it dynamically.'),
                        Forms\Components\Textarea::make('instructions')
                            ->label('Custom Account Remarks / Instructions')
                            ->placeholder('e.g. Please put Invoice ID in remarks. Contact us on WhatsApp after sending.')
                            ->rows(3),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active / Visible to Customers')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Bank Name')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('badge')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('account_title')
                    ->label('Account Title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_number')
                    ->label('Account # / IBAN')
                    ->copyable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency')
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMultiBankAccounts::route('/'),
        ];
    }
}
