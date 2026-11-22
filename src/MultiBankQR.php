<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR;

use App\Attributes\ExtensionMeta;
use App\Classes\Extension\Gateway;
use App\Models\Invoice;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction;
use Paymenter\Extensions\Gateways\MultiBankQR\Services\BankAccountService;

// Backward-compatibility alias for older Paymenter versions
if (!class_exists('App\Classes\Extension\Gateway') && class_exists('App\Classes\Extensions\Gateway')) {
    class_alias('App\Classes\Extensions\Gateway', 'App\Classes\Extension\Gateway');
}

#[ExtensionMeta(
    name: 'MultiBank & Direct QR Pay',
    description: 'Accept payments via multiple bank accounts, UPI, Wire, IBAN, Zelle, Wise, and mobile wallets with interactive QR codes, direct WhatsApp and Discord deal-closing contact links, and streamlined payment proof verification.',
    version: '1.0.0',
    author: 'Nain',
    url: 'https://github.com/nain/Paymenter_Payment',
    icon: 'https://raw.githubusercontent.com/FortAwesome/Font-Awesome/6.x/svgs/solid/qrcode.svg'
)]
class MultiBankQR extends Gateway
{
    /**
     * Boot the extension: register routes, views, and auto-migrate tables if needed.
     */
    public function boot()
    {
        // Load composer autoload if vendor exists inside extension folder
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
        }

        // Register Web routes
        if (file_exists(__DIR__ . '/../routes/web.php')) {
            require __DIR__ . '/../routes/web.php';
        }

        // Register Views namespace
        if (file_exists(__DIR__ . '/../resources/views')) {
            View::addNamespace('multibankqr', __DIR__ . '/../resources/views');
        }

        // Auto-migrate tables if not yet created
        $this->ensureTablesExist();
    }

    /**
     * Metadata for backward compatibility.
     */
    public function getMetadata()
    {
        return [
            'display_name' => 'MultiBank & Direct QR Pay',
            'version' => '1.0.0',
            'author' => 'Nain',
            'website' => 'https://github.com/nain/Paymenter_Payment',
        ];
    }

    /**
     * Define extension settings in Paymenter Admin Panel.
     */
    public function getConfig($values = [])
    {
        return [
            // --- Contact & Deal Closing (WhatsApp & Discord) ---
            [
                'name' => 'whatsapp_number',
                'label' => 'WhatsApp Phone Number',
                'type' => 'text',
                'default' => '',
                'placeholder' => '+1234567890 or +923001234567',
                'description' => 'Your official WhatsApp number in international format. Powers the 1-click WhatsApp proof & sales chat button.',
                'required' => false,
            ],
            [
                'name' => 'whatsapp_message',
                'label' => 'WhatsApp Pre-filled Message Template',
                'type' => 'textarea',
                'default' => "Hello! I have sent payment for Invoice #{invoice_id} ({amount} {currency}).\n\nTransaction ID: {trx_id}\nSender Name: {sender_name}\n\nPlease verify and activate my order.",
                'description' => 'Dynamic tokens supported: {invoice_id}, {amount}, {currency}, {trx_id}, {sender_name}, {client_name}.',
                'required' => false,
            ],
            [
                'name' => 'discord_invite',
                'label' => 'Discord Server Invite or Profile Link',
                'type' => 'text',
                'default' => '',
                'placeholder' => 'https://discord.gg/yourcommunity or https://discord.com/users/yourid',
                'description' => 'Direct link for customers to join your Discord server or message your sales/support reps.',
                'required' => false,
            ],
            [
                'name' => 'discord_instructions',
                'label' => 'Discord Proof Channel Instructions',
                'type' => 'text',
                'default' => 'Join our Discord server and open a ticket or post in #payment-proofs for 24/7 activation.',
                'description' => 'Helper note displayed under the Discord contact button.',
                'required' => false,
            ],
            [
                'name' => 'telegram_link',
                'label' => 'Telegram Support Link (Optional)',
                'type' => 'text',
                'default' => '',
                'placeholder' => 'https://t.me/yourusername',
                'description' => 'Optional Telegram channel or user link for instant messaging.',
                'required' => false,
            ],

            // --- General Branding & Notice ---
            [
                'name' => 'gateway_title',
                'label' => 'Gateway Display Title',
                'type' => 'text',
                'default' => 'Direct Bank Transfer & QR Pay',
                'description' => 'Title shown to customers on checkout.',
                'required' => false,
            ],
            [
                'name' => 'custom_notice',
                'label' => 'Checkout Trust Notice / Instructions',
                'type' => 'textarea',
                'default' => 'Please include your Invoice # in your transfer remarks/reference for instant verification. Our team verifies transfers in real time.',
                'description' => 'Guidance banner displayed at the top of the payment screen.',
                'required' => false,
            ],
            [
                'name' => 'require_proof_image',
                'label' => 'Require Screenshot Receipt Upload',
                'type' => 'checkbox',
                'default' => true,
                'description' => 'If enabled, customers must upload a payment receipt/screenshot alongside their Transaction ID.',
                'required' => false,
            ],

            // --- Bank Account 1 (Primary / Default) ---
            [
                'name' => 'acc1_enabled',
                'label' => 'Account 1: Enabled',
                'type' => 'checkbox',
                'default' => true,
                'required' => false,
            ],
            [
                'name' => 'acc1_name',
                'label' => 'Account 1: Bank / Method Name',
                'type' => 'text',
                'default' => 'Primary Bank / Zelle / Wire',
                'placeholder' => 'e.g. Chase Bank, Bank of America, Meezan Bank, HDFC UPI',
                'required' => false,
            ],
            [
                'name' => 'acc1_badge',
                'label' => 'Account 1: Badge / Tag',
                'type' => 'text',
                'default' => 'Recommended',
                'placeholder' => 'e.g. Recommended, Instant, Zero Fee',
                'required' => false,
            ],
            [
                'name' => 'acc1_title',
                'label' => 'Account 1: Account Holder Name / Title',
                'type' => 'text',
                'default' => 'Your Business Name LLC',
                'required' => false,
            ],
            [
                'name' => 'acc1_number',
                'label' => 'Account 1: Account Number / IBAN / UPI ID / Address',
                'type' => 'text',
                'default' => '123456789012',
                'required' => false,
            ],
            [
                'name' => 'acc1_routing',
                'label' => 'Account 1: Routing / SWIFT / BIC / IFSC / Branch',
                'type' => 'text',
                'default' => 'ROUTING12345',
                'required' => false,
            ],
            [
                'name' => 'acc1_qr_payload',
                'label' => 'Account 1: Dynamic QR Payload (Optional)',
                'type' => 'text',
                'default' => '',
                'placeholder' => 'upi://pay?pa=name@upi&pn=Business&am={amount} or payment URL',
                'description' => 'If provided, auto-generates a dynamic QR code. Tokens: {amount}, {invoice_id}.',
                'required' => false,
            ],
            [
                'name' => 'acc1_qr_image',
                'label' => 'Account 1: Static QR Code Image URL (Optional)',
                'type' => 'text',
                'default' => '',
                'placeholder' => 'https://yourdomain.com/qrcodes/bank1.png',
                'description' => 'Direct link or storage path to a pre-generated QR code image.',
                'required' => false,
            ],
            [
                'name' => 'acc1_currency',
                'label' => 'Account 1: Currency Code',
                'type' => 'text',
                'default' => 'ALL',
                'description' => 'Specify currency (e.g. USD, EUR, PKR, INR) or "ALL" for universal.',
                'required' => false,
            ],
            [
                'name' => 'acc1_instructions',
                'label' => 'Account 1: Custom Remarks / Instructions',
                'type' => 'text',
                'default' => 'Include your Invoice ID in the transfer remarks.',
                'required' => false,
            ],

            // --- Bank Account 2 ---
            [
                'name' => 'acc2_enabled',
                'label' => 'Account 2: Enabled',
                'type' => 'checkbox',
                'default' => false,
                'required' => false,
            ],
            [
                'name' => 'acc2_name',
                'label' => 'Account 2: Bank / Method Name',
                'type' => 'text',
                'default' => 'Wise / Revolut / Secondary Bank',
                'required' => false,
            ],
            [
                'name' => 'acc2_badge',
                'label' => 'Account 2: Badge / Tag',
                'type' => 'text',
                'default' => 'International Transfer',
                'required' => false,
            ],
            [
                'name' => 'acc2_title',
                'label' => 'Account 2: Account Holder Name / Title',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc2_number',
                'label' => 'Account 2: Account Number / IBAN / Address',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc2_routing',
                'label' => 'Account 2: Routing / SWIFT / BIC / IFSC',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc2_qr_payload',
                'label' => 'Account 2: Dynamic QR Payload (Optional)',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc2_qr_image',
                'label' => 'Account 2: Static QR Image URL (Optional)',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc2_currency',
                'label' => 'Account 2: Currency Code',
                'type' => 'text',
                'default' => 'ALL',
                'required' => false,
            ],
            [
                'name' => 'acc2_instructions',
                'label' => 'Account 2: Custom Remarks / Instructions',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],

            // --- Bank Account 3 ---
            [
                'name' => 'acc3_enabled',
                'label' => 'Account 3: Enabled',
                'type' => 'checkbox',
                'default' => false,
                'required' => false,
            ],
            [
                'name' => 'acc3_name',
                'label' => 'Account 3: Bank / Method Name',
                'type' => 'text',
                'default' => '',
                'placeholder' => 'e.g. Binance Pay / Crypto USDT',
                'required' => false,
            ],
            [
                'name' => 'acc3_badge',
                'label' => 'Account 3: Badge / Tag',
                'type' => 'text',
                'default' => 'Instant',
                'required' => false,
            ],
            [
                'name' => 'acc3_title',
                'label' => 'Account 3: Account Holder / Wallet',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc3_number',
                'label' => 'Account 3: Account Number / Wallet Address',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc3_routing',
                'label' => 'Account 3: Routing / Network (e.g. TRC20)',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc3_qr_payload',
                'label' => 'Account 3: Dynamic QR Payload (Optional)',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc3_qr_image',
                'label' => 'Account 3: Static QR Image URL (Optional)',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc3_currency',
                'label' => 'Account 3: Currency Code',
                'type' => 'text',
                'default' => 'ALL',
                'required' => false,
            ],
            [
                'name' => 'acc3_instructions',
                'label' => 'Account 3: Custom Remarks / Instructions',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],

            // --- Bank Account 4 ---
            [
                'name' => 'acc4_enabled',
                'label' => 'Account 4: Enabled',
                'type' => 'checkbox',
                'default' => false,
                'required' => false,
            ],
            [
                'name' => 'acc4_name',
                'label' => 'Account 4: Bank / Method Name',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc4_badge',
                'label' => 'Account 4: Badge / Tag',
                'type' => 'text',
                'default' => 'Zero Fee',
                'required' => false,
            ],
            [
                'name' => 'acc4_title',
                'label' => 'Account 4: Account Holder Name / Title',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc4_number',
                'label' => 'Account 4: Account Number / IBAN / Address',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc4_routing',
                'label' => 'Account 4: Routing / SWIFT / IFSC',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc4_qr_payload',
                'label' => 'Account 4: Dynamic QR Payload (Optional)',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc4_qr_image',
                'label' => 'Account 4: Static QR Image URL (Optional)',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc4_currency',
                'label' => 'Account 4: Currency Code',
                'type' => 'text',
                'default' => 'ALL',
                'required' => false,
            ],
            [
                'name' => 'acc4_instructions',
                'label' => 'Account 4: Custom Remarks / Instructions',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],

            // --- Advanced JSON Configuration ---
            [
                'name' => 'accounts_json',
                'label' => 'Advanced: Custom Accounts JSON (Optional)',
                'type' => 'textarea',
                'default' => '',
                'placeholder' => '[{"name":"Custom Bank","account_title":"Corp","account_number":"123","routing_code":"XYZ","badge":"Instant"}]',
                'description' => 'Optional JSON array for adding unlimited bank accounts or wallets dynamically.',
                'required' => false,
            ],
        ];
    }

    /**
     * Determine if gateway is available for this invoice.
     */
    public function canUseGateway($total, $currency, $type, $items = [])
    {
        return true;
    }

    /**
     * Process checkout for the given invoice.
     * Returns the interactive view.
     */
    public function pay(Invoice $invoice, $total, bool $isStandalone = false)
    {
        $this->boot();

        // Extract settings
        $config = is_array($this->config) ? $this->config : [];

        // Fetch bank accounts matching currency
        $currency = $invoice->currency_code ?? ($invoice->currency ?? 'USD');
        $accounts = BankAccountService::getAccountsForInvoice($config, $total, $currency, $invoice->id);

        // Fetch existing submitted transaction if any
        $transaction = null;
        try {
            if (Schema::hasTable('multibank_transactions')) {
                $transaction = MultiBankTransaction::where('invoice_id', $invoice->id)->first();
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        // WhatsApp direct click-to-chat URL with pre-filled message
        $rawPhone = $this->config('whatsapp_number');
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone ?? '');
        $whatsappUrl = null;
        if (!empty($cleanPhone)) {
            $clientName = $invoice->user ? ($invoice->user->name ?? 'Customer') : 'Customer';
            $msgTemplate = $this->config('whatsapp_message') ?: "Hello! I am sending payment for Invoice #{invoice_id} ({amount} {currency}).\n\nTransaction ID: {trx_id}\nSender Name: {sender_name}\n\nPlease confirm my order.";
            $msg = str_replace(
                ['{invoice_id}', '{amount}', '{currency}', '{trx_id}', '{sender_name}', '{client_name}'],
                [
                    $invoice->id,
                    number_format((float) $total, 2),
                    $currency,
                    $transaction ? $transaction->transaction_id : '[Pending Input]',
                    $transaction ? $transaction->sender_name : $clientName,
                    $clientName,
                ],
                $msgTemplate
            );
            $whatsappUrl = 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($msg);
        }

        // Discord direct link
        $discordInvite = $this->config('discord_invite');
        $discordInstructions = $this->config('discord_instructions') ?: 'Join our Discord server and open a ticket or post in #payment-proofs.';

        // Telegram link
        $telegramLink = $this->config('telegram_link');

        // General settings
        $gatewayTitle = $this->config('gateway_title') ?: 'Direct Bank Transfer & QR Pay';
        $customNotice = $this->config('custom_notice') ?: 'Please include your Invoice # in your transfer remarks/reference for instant verification.';
        $requireProofImage = $this->config('require_proof_image');
        if ($requireProofImage === null) {
            $requireProofImage = true;
        }

        $viewName = $isStandalone ? 'multibankqr::standalone' : 'multibankqr::pay';

        return view($viewName, [
            'invoice' => $invoice,
            'total' => $total,
            'currency' => $currency,
            'accounts' => $accounts,
            'transaction' => $transaction,
            'whatsappUrl' => $whatsappUrl,
            'whatsappPhone' => $rawPhone,
            'discordInvite' => $discordInvite,
            'discordInstructions' => $discordInstructions,
            'telegramLink' => $telegramLink,
            'gatewayTitle' => $gatewayTitle,
            'customNotice' => $customNotice,
            'requireProofImage' => (bool) $requireProofImage,
        ]);
    }

    /**
     * Auto-run migrations if tables do not exist yet.
     */
    protected function ensureTablesExist(): void
    {
        try {
            if (!Schema::hasTable('multibank_accounts') || !Schema::hasTable('multibank_transactions')) {
                $migrationPath = realpath(__DIR__ . '/../database/migrations');
                if ($migrationPath) {
                    $files = glob($migrationPath . '/*.php');
                    sort($files);
                    foreach ($files as $file) {
                        try {
                            $migration = require_once $file;
                            if (is_object($migration) && method_exists($migration, 'up')) {
                                $migration->up();
                            }
                        } catch (\Throwable $e) {
                            Log::warning('[MultiBankQR] Auto-migration error on file ' . basename($file) . ': ' . $e->getMessage());
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[MultiBankQR] Unable to check or run migrations: ' . $e->getMessage());
        }
    }
}
