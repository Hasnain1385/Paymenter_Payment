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
    name: 'PakBank QR & Manual Verification',
    description: 'Accept Pakistani bank & mobile wallet payments (Raast, Meezan, HBL, Bank Alfalah, Easypaisa, JazzCash, SadaPay, NayaPay) with scanned static QR codes, WhatsApp & Discord direct contact buttons, and manual admin verification workflow.',
    version: '1.1.0',
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
            'display_name' => 'PakBank QR & Manual Verification',
            'version' => '1.1.0',
            'author' => 'Nain',
            'website' => 'https://github.com/nain/Paymenter_Payment',
        ];
    }

    /**
     * Define extension settings in Paymenter Admin Panel.
     * Structured specifically for Pakistani payments with static scanned QR images.
     */
    public function getConfig($values = [])
    {
        return [
            // --- Contact & Deal Closing (WhatsApp & Discord) ---
            [
                'name' => 'whatsapp_number',
                'label' => 'Support / Sales WhatsApp Number',
                'type' => 'text',
                'default' => '+923001234567',
                'placeholder' => '+923001234567',
                'description' => 'Your official WhatsApp number (e.g. +923001234567). Enables 1-click WhatsApp buttons for pre-payment queries and post-payment proof delivery.',
                'required' => false,
            ],
            [
                'name' => 'whatsapp_prepay_message',
                'label' => 'WhatsApp Pre-Payment Message Template',
                'type' => 'textarea',
                'default' => "Assalam-o-Alaikum! I have a question regarding Invoice #{invoice_id} ({amount} {currency}) before making payment. Can you please assist me?",
                'description' => 'Message sent when customer clicks "Contact on WhatsApp Before Paying". Tokens: {invoice_id}, {amount}, {currency}, {client_name}.',
                'required' => false,
            ],
            [
                'name' => 'whatsapp_postpay_message',
                'label' => 'WhatsApp Post-Payment Message Template',
                'type' => 'textarea',
                'default' => "Assalam-o-Alaikum! I have transferred {amount} {currency} for Invoice #{invoice_id}.\n\nTransaction ID / TRX: {trx_id}\nSender Name: {sender_name}\n\nPlease manually verify and activate my order.",
                'description' => 'Message sent when customer clicks "Send Proof on WhatsApp". Tokens: {invoice_id}, {amount}, {currency}, {trx_id}, {sender_name}, {client_name}.',
                'required' => false,
            ],
            [
                'name' => 'discord_invite',
                'label' => 'Discord Server Invite or Profile Link',
                'type' => 'text',
                'default' => '',
                'placeholder' => 'https://discord.gg/yourcommunity or https://discord.com/users/yourid',
                'description' => 'Direct link for customers to join your Discord server or message support reps for order assistance.',
                'required' => false,
            ],
            [
                'name' => 'discord_instructions',
                'label' => 'Discord Channel Instructions',
                'type' => 'text',
                'default' => 'Join our Discord server and open a ticket or post in #payment-proofs for fast order verification.',
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

            // --- General Branding & Manual Verification Notice ---
            [
                'name' => 'gateway_title',
                'label' => 'Gateway Display Title',
                'type' => 'text',
                'default' => 'Pakistani Banks, Raast & Wallets (Manual Verification)',
                'description' => 'Title shown to customers on checkout.',
                'required' => false,
            ],
            [
                'name' => 'custom_notice',
                'label' => 'Manual Verification Notice / Instructions',
                'type' => 'textarea',
                'default' => '⚠️ Manual Verification Required: Please scan the static QR code or transfer the exact amount using your banking app (Meezan, HBL, Alfalah, Raast) or wallet (Easypaisa, JazzCash, SadaPay). Enter your Transaction ID (TRX/UTR) below after paying. Our team manually verifies payments in real time.',
                'description' => 'Guidance banner displayed at the top of the payment screen.',
                'required' => false,
            ],
            [
                'name' => 'require_proof_image',
                'label' => 'Require Screenshot Receipt Upload',
                'type' => 'checkbox',
                'default' => true,
                'description' => 'If enabled, customers must attach a payment screenshot or receipt alongside their Transaction ID.',
                'required' => false,
            ],

            // --- Bank Account 1: Raast P2P / Commercial Bank (e.g. Meezan Bank) ---
            [
                'name' => 'acc1_enabled',
                'label' => 'Account 1: Enabled',
                'type' => 'checkbox',
                'default' => true,
                'required' => false,
            ],
            [
                'name' => 'acc1_name',
                'label' => 'Account 1: Bank / Wallet Name',
                'type' => 'text',
                'default' => 'Meezan Bank / Raast P2P',
                'placeholder' => 'e.g. Meezan Bank / Raast, HBL, Bank Alfalah',
                'required' => false,
            ],
            [
                'name' => 'acc1_badge',
                'label' => 'Account 1: Badge / Tag',
                'type' => 'text',
                'default' => 'Instant Raast / 0 Fee',
                'required' => false,
            ],
            [
                'name' => 'acc1_title',
                'label' => 'Account 1: Account Holder Title / Name',
                'type' => 'text',
                'default' => 'Your Business Account Title',
                'required' => false,
            ],
            [
                'name' => 'acc1_number',
                'label' => 'Account 1: Account Number / IBAN / Raast ID',
                'type' => 'text',
                'default' => 'PK00MEZN0000000000000000',
                'required' => false,
            ],
            [
                'name' => 'acc1_routing',
                'label' => 'Account 1: Bank Name / Branch / Code',
                'type' => 'text',
                'default' => 'Meezan Bank Limited',
                'required' => false,
            ],
            [
                'name' => 'acc1_qr_image',
                'label' => 'Account 1: Scanned Static QR Image (URL or Storage Path)',
                'type' => 'text',
                'default' => '',
                'placeholder' => 'https://yourdomain.com/qrcodes/meezan_raast.png or storage/multibank_qrcodes/...',
                'description' => 'Direct URL or path to your scanned static Meezan / Raast QR code image.',
                'required' => false,
            ],
            [
                'name' => 'acc1_qr_payload',
                'label' => 'Account 1: Dynamic QR / UPI / Raw String (Optional)',
                'type' => 'text',
                'default' => '',
                'description' => 'Optional raw payload or URL for dynamic QR fallback.',
                'required' => false,
            ],
            [
                'name' => 'acc1_currency',
                'label' => 'Account 1: Currency Code',
                'type' => 'text',
                'default' => 'PKR',
                'required' => false,
            ],
            [
                'name' => 'acc1_instructions',
                'label' => 'Account 1: Instructions',
                'type' => 'text',
                'default' => 'Send via Raast P2P or Meezan Bank. Please put Invoice ID in remarks.',
                'required' => false,
            ],

            // --- Bank Account 2: Easypaisa Mobile Wallet ---
            [
                'name' => 'acc2_enabled',
                'label' => 'Account 2: Enabled',
                'type' => 'checkbox',
                'default' => true,
                'required' => false,
            ],
            [
                'name' => 'acc2_name',
                'label' => 'Account 2: Bank / Wallet Name',
                'type' => 'text',
                'default' => 'Easypaisa Mobile Wallet',
                'required' => false,
            ],
            [
                'name' => 'acc2_badge',
                'label' => 'Account 2: Badge / Tag',
                'type' => 'text',
                'default' => 'Easypaisa Mobile',
                'required' => false,
            ],
            [
                'name' => 'acc2_title',
                'label' => 'Account 2: Account Holder Title / Name',
                'type' => 'text',
                'default' => 'Your Account Title',
                'required' => false,
            ],
            [
                'name' => 'acc2_number',
                'label' => 'Account 2: Mobile Number / Account Number',
                'type' => 'text',
                'default' => '03001234567',
                'required' => false,
            ],
            [
                'name' => 'acc2_routing',
                'label' => 'Account 2: Bank / Wallet Provider',
                'type' => 'text',
                'default' => 'Telenor Microfinance Bank',
                'required' => false,
            ],
            [
                'name' => 'acc2_qr_image',
                'label' => 'Account 2: Scanned Static QR Image (URL or Storage Path)',
                'type' => 'text',
                'default' => '',
                'placeholder' => 'https://yourdomain.com/qrcodes/easypaisa.png',
                'description' => 'Direct link to your scanned Easypaisa account QR screenshot.',
                'required' => false,
            ],
            [
                'name' => 'acc2_qr_payload',
                'label' => 'Account 2: Dynamic QR / Payload (Optional)',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc2_currency',
                'label' => 'Account 2: Currency Code',
                'type' => 'text',
                'default' => 'PKR',
                'required' => false,
            ],
            [
                'name' => 'acc2_instructions',
                'label' => 'Account 2: Instructions',
                'type' => 'text',
                'default' => 'Open Easypaisa app, tap Scan QR or transfer to mobile number.',
                'required' => false,
            ],

            // --- Bank Account 3: JazzCash Mobile Wallet ---
            [
                'name' => 'acc3_enabled',
                'label' => 'Account 3: Enabled',
                'type' => 'checkbox',
                'default' => true,
                'required' => false,
            ],
            [
                'name' => 'acc3_name',
                'label' => 'Account 3: Bank / Wallet Name',
                'type' => 'text',
                'default' => 'JazzCash Mobile Wallet',
                'required' => false,
            ],
            [
                'name' => 'acc3_badge',
                'label' => 'Account 3: Badge / Tag',
                'type' => 'text',
                'default' => 'JazzCash App',
                'required' => false,
            ],
            [
                'name' => 'acc3_title',
                'label' => 'Account 3: Account Holder Title / Name',
                'type' => 'text',
                'default' => 'Your Account Title',
                'required' => false,
            ],
            [
                'name' => 'acc3_number',
                'label' => 'Account 3: Mobile Number / Account Number',
                'type' => 'text',
                'default' => '03001234567',
                'required' => false,
            ],
            [
                'name' => 'acc3_routing',
                'label' => 'Account 3: Bank / Wallet Provider',
                'type' => 'text',
                'default' => 'Mobilink Microfinance Bank',
                'required' => false,
            ],
            [
                'name' => 'acc3_qr_image',
                'label' => 'Account 3: Scanned Static QR Image (URL or Storage Path)',
                'type' => 'text',
                'default' => '',
                'placeholder' => 'https://yourdomain.com/qrcodes/jazzcash.png',
                'description' => 'Direct link to your scanned JazzCash account QR screenshot.',
                'required' => false,
            ],
            [
                'name' => 'acc3_qr_payload',
                'label' => 'Account 3: Dynamic QR / Payload (Optional)',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc3_currency',
                'label' => 'Account 3: Currency Code',
                'type' => 'text',
                'default' => 'PKR',
                'required' => false,
            ],
            [
                'name' => 'acc3_instructions',
                'label' => 'Account 3: Instructions',
                'type' => 'text',
                'default' => 'Open JazzCash app, tap Scan QR or send to mobile number.',
                'required' => false,
            ],

            // --- Bank Account 4: SadaPay / NayaPay / Bank Alfalah ---
            [
                'name' => 'acc4_enabled',
                'label' => 'Account 4: Enabled',
                'type' => 'checkbox',
                'default' => false,
                'required' => false,
            ],
            [
                'name' => 'acc4_name',
                'label' => 'Account 4: Bank / Wallet Name',
                'type' => 'text',
                'default' => 'SadaPay / NayaPay / Bank Alfalah',
                'required' => false,
            ],
            [
                'name' => 'acc4_badge',
                'label' => 'Account 4: Badge / Tag',
                'type' => 'text',
                'default' => 'SadaPay / IBAN',
                'required' => false,
            ],
            [
                'name' => 'acc4_title',
                'label' => 'Account 4: Account Holder Title / Name',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc4_number',
                'label' => 'Account 4: Account Number / IBAN',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc4_routing',
                'label' => 'Account 4: Bank / Provider Name',
                'type' => 'text',
                'default' => 'SadaPay / Bank Alfalah',
                'required' => false,
            ],
            [
                'name' => 'acc4_qr_image',
                'label' => 'Account 4: Scanned Static QR Image (URL or Storage Path)',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc4_qr_payload',
                'label' => 'Account 4: Dynamic QR / Payload (Optional)',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc4_currency',
                'label' => 'Account 4: Currency Code',
                'type' => 'text',
                'default' => 'PKR',
                'required' => false,
            ],
            [
                'name' => 'acc4_instructions',
                'label' => 'Account 4: Instructions',
                'type' => 'text',
                'default' => 'Transfer via SadaPay app or IBAN transfer.',
                'required' => false,
            ],

            // --- Advanced JSON Configuration ---
            [
                'name' => 'accounts_json',
                'label' => 'Advanced: Custom Accounts JSON (Optional)',
                'type' => 'textarea',
                'default' => '',
                'placeholder' => '[{"name":"Faysal Bank","account_title":"Corp","account_number":"PK00...","routing_code":"Faysal","badge":"0 Fee"}]',
                'description' => 'Optional JSON array for adding unlimited Pakistani bank accounts or wallets dynamically.',
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

        // Fetch bank accounts matching currency (defaults to PKR if not set)
        $currency = $invoice->currency_code ?? ($invoice->currency ?? 'PKR');
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

        // WhatsApp direct links: Both Pre-Payment (questions before paying) & Post-Payment (sending proof)
        $rawPhone = $this->config('whatsapp_number');
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone ?? '');
        $whatsappPrePayUrl = null;
        $whatsappPostPayUrl = null;

        if (!empty($cleanPhone)) {
            $clientName = $invoice->user ? ($invoice->user->name ?? 'Customer') : 'Customer';
            $formattedTotal = number_format((float) $total, 2);

            // 1. Pre-Payment WhatsApp URL: Questions before paying
            $preMsgTemplate = $this->config('whatsapp_prepay_message')
                ?: "Assalam-o-Alaikum! I have a question regarding Invoice #{invoice_id} ({amount} {currency}) before making payment. Can you please assist me?";
            $preMsg = str_replace(
                ['{invoice_id}', '{amount}', '{currency}', '{client_name}'],
                [$invoice->id, $formattedTotal, $currency, $clientName],
                $preMsgTemplate
            );
            $whatsappPrePayUrl = 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($preMsg);

            // 2. Post-Payment WhatsApp URL: Sending proof of payment
            $postMsgTemplate = $this->config('whatsapp_postpay_message')
                ?: "Assalam-o-Alaikum! I have transferred {amount} {currency} for Invoice #{invoice_id}.\n\nTransaction ID / TRX: {trx_id}\nSender Name: {sender_name}\n\nPlease manually verify and activate my order.";
            $postMsg = str_replace(
                ['{invoice_id}', '{amount}', '{currency}', '{trx_id}', '{sender_name}', '{client_name}'],
                [
                    $invoice->id,
                    $formattedTotal,
                    $currency,
                    $transaction ? $transaction->transaction_id : '[Enter TRX]',
                    $transaction ? $transaction->sender_name : $clientName,
                    $clientName,
                ],
                $postMsgTemplate
            );
            $whatsappPostPayUrl = 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($postMsg);
        }

        // Discord direct link
        $discordInvite = $this->config('discord_invite');
        $discordInstructions = $this->config('discord_instructions') ?: 'Join our Discord server and open a ticket or post in #payment-proofs for fast order verification.';

        // Telegram link
        $telegramLink = $this->config('telegram_link');

        // General settings
        $gatewayTitle = $this->config('gateway_title') ?: 'Pakistani Banks, Raast & Wallets (Manual Verification)';
        $customNotice = $this->config('custom_notice') ?: '⚠️ Manual Verification: Please scan the static QR code or transfer the exact amount using your banking app (Meezan, HBL, Alfalah, Raast) or wallet (Easypaisa, JazzCash, SadaPay). Enter your Transaction ID (TRX/UTR) below after paying. Our team manually verifies payments in real time.';
        $requireProofImage = $this->config('require_proof_image');
        if ($requireProofImage === null) {
            $requireProofImage = true;
        }

        $viewName = $isStandalone ? 'multibankqr::standalone' : 'multibankqr::pay';

        return view($viewName, [
            'invoice'            => $invoice,
            'total'              => $total,
            'currency'           => $currency,
            'accounts'           => $accounts,
            'transaction'        => $transaction,
            'whatsappPrePayUrl'  => $whatsappPrePayUrl,
            'whatsappPostPayUrl' => $whatsappPostPayUrl,
            'whatsappPhone'      => $rawPhone,
            'discordInvite'      => $discordInvite,
            'discordInstructions' => $discordInstructions,
            'telegramLink'       => $telegramLink,
            'gatewayTitle'       => $gatewayTitle,
            'customNotice'       => $customNotice,
            'requireProofImage'  => (bool) $requireProofImage,
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
