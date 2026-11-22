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

// Backward-compatibility alias for older Paymenter versions or standalone test mock
if (!class_exists('App\Classes\Extension\Gateway')) {
    if (class_exists('App\Classes\Extensions\Gateway')) {
        class_alias('App\Classes\Extensions\Gateway', 'App\Classes\Extension\Gateway');
    } else {
        eval('namespace App\Classes\Extension { class Gateway {} }');
    }
}
if (!class_exists('App\Attributes\ExtensionMeta')) {
    eval('namespace App\Attributes { #[\Attribute] class ExtensionMeta { public function __construct(...$args) {} } }');
}

#[ExtensionMeta(
    name: 'PakBank QR & Manual Verification',
    description: 'Accept Pakistani bank & mobile wallet payments (Raast, Meezan, HBL, Bank Alfalah, Easypaisa, JazzCash, SadaPay, NayaPay) with scanned static QR codes, WhatsApp & Discord direct contact buttons, and manual admin verification workflow.',
    version: '1.2.7',
    author: 'Mirza Hasnain Baig',
    url: 'https://github.com/Hasnain1385/Paymenter_Payment',
    icon: 'https://raw.githubusercontent.com/Hasnain1385/Paymenter_Payment/main/logo.png'
)]
class MultiBankQR extends Gateway
{
    public const VERSION = '1.2.7';
    /**
     * Boot the extension: register routes, views, and auto-migrate tables if needed.
     */
    public function boot()
    {
        // Load composer autoload if vendor exists inside extension folder
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require_once __DIR__ . '/vendor/autoload.php';
        }

        // Register Web routes
        if (file_exists(__DIR__ . '/routes/web.php')) {
            require __DIR__ . '/routes/web.php';
        }

        // Register Views namespace
        if (file_exists(__DIR__ . '/resources/views')) {
            View::addNamespace('multibankqr', __DIR__ . '/resources/views');
        }

        // Auto-migrate tables if not yet created
        self::ensureTablesExist();
    }

    /**
     * Metadata for backward compatibility.
     */
    public function getMetadata()
    {
        return [
            'display_name' => 'PakBank QR & Manual Verification',
            'version' => '1.1.0',
            'author' => 'Mirza Hasnain Baig',
            'website' => 'https://github.com/Hasnain1385/Paymenter_Payment',
        ];
    }

    /**
     * Define extension settings in Paymenter Admin Panel.
     * Structured specifically for Pakistani payments with static scanned QR images.
     */
    public function getConfig($values = [])
    {
        return [
            // =================================================================
            // 1. Direct Management Dashboards & Navigation Links
            // =================================================================
            [
                'name' => 'admin_navigation_portal',
                'label' => '⚡ Gateway Management Portals',
                'type' => 'placeholder',
                'description' => '👉 <a href="/admin/multibankqr/accounts" target="_blank" style="color:#10b981;font-weight:700;text-decoration:underline;margin-right:16px;">🏦 Manage Bank Accounts (Add / Edit / Remove)</a> | <a href="/admin/multibankqr/transactions" target="_blank" style="color:#3b82f6;font-weight:700;text-decoration:underline;margin-right:16px;">💳 Payment History & Verification</a> | <a href="/admin/multibankqr/updates" target="_blank" style="color:#6366f1;font-weight:700;text-decoration:underline;">🔄 GitHub Updates & Sync</a>',
            ],

            // =================================================================
            // 2. Customer Support & Gateway Settings
            // =================================================================
            [
                'name' => 'whatsapp_number',
                'label' => 'Support WhatsApp Number',
                'type' => 'text',
                'default' => '+923001234567',
                'placeholder' => '+923001234567',
                'description' => 'Your official WhatsApp number (e.g. +923001234567). Enables the "Chat on WhatsApp" button.',
                'required' => false,
            ],
            [
                'name' => 'discord_invite',
                'label' => 'Discord Server Invite Link',
                'type' => 'text',
                'default' => '',
                'placeholder' => 'https://discord.gg/yourserver',
                'description' => 'Invite link for the "Join Discord" button.',
                'required' => false,
            ],
            [
                'name' => 'gateway_title',
                'label' => 'Gateway Title',
                'type' => 'text',
                'default' => 'Bank Transfer & QR Pay',
                'description' => 'Title shown to customers during checkout.',
                'required' => false,
            ],
            [
                'name' => 'custom_notice',
                'label' => 'Payment Notice (Optional)',
                'type' => 'textarea',
                'default' => 'Please transfer the exact amount and enter your Transaction ID (TRX/UTR) below.',
                'description' => 'Optional guidance note displayed to customers above the payment methods.',
                'required' => false,
            ],
            [
                'name' => 'require_proof_image',
                'label' => 'Require Screenshot Receipt Upload',
                'type' => 'checkbox',
                'default' => true,
                'description' => 'If enabled, customers must upload a receipt screenshot alongside their Transaction ID.',
                'required' => false,
            ],

            // =================================================================
            // 3. Bank Account 1 (Primary / Raast)
            // =================================================================
            [
                'name' => 'acc1_enabled',
                'label' => 'Enable Bank Account 1',
                'type' => 'checkbox',
                'default' => true,
                'required' => false,
            ],
            [
                'name' => 'acc1_name',
                'label' => 'Account 1: Bank / Wallet Name',
                'type' => 'text',
                'default' => 'Meezan Bank / Raast P2P',
                'placeholder' => 'e.g. Meezan Bank, HBL, Bank Alfalah',
                'required' => false,
            ],
            [
                'name' => 'acc1_badge',
                'label' => 'Account 1: Badge Tag',
                'type' => 'text',
                'default' => 'Instant Raast',
                'placeholder' => 'e.g. Instant Raast, 0% Fee',
                'required' => false,
            ],
            [
                'name' => 'acc1_title',
                'label' => 'Account 1: Account Title / Beneficiary',
                'type' => 'text',
                'default' => 'Merchant Account Title',
                'required' => false,
            ],
            [
                'name' => 'acc1_number',
                'label' => 'Account 1: Account Number / IBAN / Raast ID',
                'type' => 'text',
                'default' => 'PK42MEZN0001020304050607',
                'required' => false,
            ],
            [
                'name' => 'acc1_routing',
                'label' => 'Account 1: Bank / Branch Name',
                'type' => 'text',
                'default' => 'Meezan Bank Limited',
                'required' => false,
            ],
            [
                'name' => 'acc1_qr_image',
                'label' => 'Account 1: Scanned Static QR Image URL',
                'type' => 'text',
                'default' => '',
                'placeholder' => 'https://example.com/meezan-qr.png',
                'description' => 'Direct URL to merchant static QR code image.',
                'required' => false,
            ],
            [
                'name' => 'acc1_instructions',
                'label' => 'Account 1: Payment Instructions',
                'type' => 'text',
                'default' => 'Transfer via Raast P2P or IBAN with Invoice ID in remarks.',
                'required' => false,
            ],
            [
                'name' => 'acc1_currency',
                'label' => 'Account 1: Supported Currency',
                'type' => 'text',
                'default' => 'PKR',
                'required' => false,
            ],

            // =================================================================
            // 4. Bank Account 2 (Easypaisa Mobile Wallet)
            // =================================================================
            [
                'name' => 'acc2_enabled',
                'label' => 'Enable Bank Account 2',
                'type' => 'checkbox',
                'default' => true,
                'required' => false,
            ],
            [
                'name' => 'acc2_name',
                'label' => 'Account 2: Bank / Wallet Name',
                'type' => 'text',
                'default' => 'Easypaisa Mobile Wallet',
                'placeholder' => 'e.g. Easypaisa Wallet',
                'required' => false,
            ],
            [
                'name' => 'acc2_badge',
                'label' => 'Account 2: Badge Tag',
                'type' => 'text',
                'default' => 'Easypaisa',
                'required' => false,
            ],
            [
                'name' => 'acc2_title',
                'label' => 'Account 2: Account Title / Beneficiary',
                'type' => 'text',
                'default' => 'Merchant Account Title',
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
                'name' => 'acc2_qr_image',
                'label' => 'Account 2: Scanned Static QR Image URL',
                'type' => 'text',
                'default' => '',
                'placeholder' => 'https://example.com/easypaisa-qr.png',
                'required' => false,
            ],
            [
                'name' => 'acc2_instructions',
                'label' => 'Account 2: Payment Instructions',
                'type' => 'text',
                'default' => 'Send payment to Easypaisa number and enter TRX ID below.',
                'required' => false,
            ],
            [
                'name' => 'acc2_currency',
                'label' => 'Account 2: Supported Currency',
                'type' => 'text',
                'default' => 'PKR',
                'required' => false,
            ],

            // =================================================================
            // 5. Bank Account 3 (JazzCash Mobile Wallet)
            // =================================================================
            [
                'name' => 'acc3_enabled',
                'label' => 'Enable Bank Account 3',
                'type' => 'checkbox',
                'default' => true,
                'required' => false,
            ],
            [
                'name' => 'acc3_name',
                'label' => 'Account 3: Bank / Wallet Name',
                'type' => 'text',
                'default' => 'JazzCash Mobile Wallet',
                'placeholder' => 'e.g. JazzCash Wallet',
                'required' => false,
            ],
            [
                'name' => 'acc3_badge',
                'label' => 'Account 3: Badge Tag',
                'type' => 'text',
                'default' => 'JazzCash',
                'required' => false,
            ],
            [
                'name' => 'acc3_title',
                'label' => 'Account 3: Account Title / Beneficiary',
                'type' => 'text',
                'default' => 'Merchant Account Title',
                'required' => false,
            ],
            [
                'name' => 'acc3_number',
                'label' => 'Account 3: Mobile Number / Account Number',
                'type' => 'text',
                'default' => '03007654321',
                'required' => false,
            ],
            [
                'name' => 'acc3_qr_image',
                'label' => 'Account 3: Scanned Static QR Image URL',
                'type' => 'text',
                'default' => '',
                'placeholder' => 'https://example.com/jazzcash-qr.png',
                'required' => false,
            ],
            [
                'name' => 'acc3_instructions',
                'label' => 'Account 3: Payment Instructions',
                'type' => 'text',
                'default' => 'Send payment to JazzCash number and enter TRX ID below.',
                'required' => false,
            ],
            [
                'name' => 'acc3_currency',
                'label' => 'Account 3: Supported Currency',
                'type' => 'text',
                'default' => 'PKR',
                'required' => false,
            ],

            // =================================================================
            // 6. Bank Account 4 (SadaPay / NayaPay / Digital Wallet)
            // =================================================================
            [
                'name' => 'acc4_enabled',
                'label' => 'Enable Bank Account 4',
                'type' => 'checkbox',
                'default' => false,
                'required' => false,
            ],
            [
                'name' => 'acc4_name',
                'label' => 'Account 4: Bank / Wallet Name',
                'type' => 'text',
                'default' => 'SadaPay / NayaPay',
                'placeholder' => 'e.g. SadaPay, NayaPay, Standard Chartered',
                'required' => false,
            ],
            [
                'name' => 'acc4_badge',
                'label' => 'Account 4: Badge Tag',
                'type' => 'text',
                'default' => 'Digital Wallet',
                'required' => false,
            ],
            [
                'name' => 'acc4_title',
                'label' => 'Account 4: Account Title / Beneficiary',
                'type' => 'text',
                'default' => 'Merchant Account Title',
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
                'name' => 'acc4_qr_image',
                'label' => 'Account 4: Scanned Static QR Image URL',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc4_instructions',
                'label' => 'Account 4: Payment Instructions',
                'type' => 'text',
                'default' => '',
                'required' => false,
            ],
            [
                'name' => 'acc4_currency',
                'label' => 'Account 4: Supported Currency',
                'type' => 'text',
                'default' => 'PKR',
                'required' => false,
            ],
        ];
    }

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
                ?: "Assalam-o-Alaikum! I have a query regarding Invoice #{invoice_id} ({amount} {currency}).";
            $preMsg = str_replace(
                ['{invoice_id}', '{amount}', '{currency}', '{client_name}'],
                [$invoice->id, $formattedTotal, $currency, $clientName],
                $preMsgTemplate
            );
            $whatsappPrePayUrl = 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($preMsg);

            // 2. Post-Payment WhatsApp URL: Sending proof of payment
            $postMsgTemplate = $this->config('whatsapp_postpay_message')
                ?: "Assalam-o-Alaikum! I have sent payment for Invoice #{invoice_id} ({amount} {currency}).\nTRX ID: {trx_id}\nSender: {sender_name}";
            $postMsg = str_replace(
                ['{invoice_id}', '{amount}', '{currency}', '{trx_id}', '{sender_name}', '{client_name}'],
                [
                    $invoice->id,
                    $formattedTotal,
                    $currency,
                    $transaction ? $transaction->transaction_id : '[TRX]',
                    $transaction ? $transaction->sender_name : $clientName,
                    $clientName,
                ],
                $postMsgTemplate
            );
            $whatsappPostPayUrl = 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($postMsg);
        }

        // Discord direct link
        $discordInvite = $this->config('discord_invite');

        // General settings
        $gatewayTitle = $this->config('gateway_title') ?: 'Bank Transfer & QR Pay';
        $customNotice = $this->config('custom_notice') ?: '';
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
            'gatewayTitle'       => $gatewayTitle,
            'customNotice'       => $customNotice,
            'requireProofImage'  => (bool) $requireProofImage,
        ]);
    }

    /**
     * Auto-run migrations if tables do not exist yet.
     */
    public static function ensureTablesExist(): void
    {
        try {
            if (!Schema::hasTable('multibank_accounts') || !Schema::hasTable('multibank_transactions')) {
                $migrationPath = realpath(__DIR__ . '/database/migrations');
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

            // Seed initial starter Pakistani accounts if none exist
            if (Schema::hasTable('multibank_accounts') && class_exists(MultiBankAccount::class)) {
                if (MultiBankAccount::count() === 0) {
                    MultiBankAccount::create([
                        'name'           => 'Meezan Bank / Raast P2P',
                        'badge'          => 'Instant Raast',
                        'account_title'  => 'Merchant Account Title',
                        'account_number' => 'PK42MEZN0001020304050607',
                        'routing_code'   => 'Meezan Bank Limited',
                        'currency'       => 'PKR',
                        'is_active'      => true,
                        'sort_order'     => 1,
                    ]);
                    MultiBankAccount::create([
                        'name'           => 'Easypaisa Mobile Wallet',
                        'badge'          => 'Easypaisa',
                        'account_title'  => 'Merchant Account Title',
                        'account_number' => '03001234567',
                        'currency'       => 'PKR',
                        'is_active'      => true,
                        'sort_order'     => 2,
                    ]);
                    MultiBankAccount::create([
                        'name'           => 'JazzCash Mobile Wallet',
                        'badge'          => 'JazzCash',
                        'account_title'  => 'Merchant Account Title',
                        'account_number' => '03007654321',
                        'currency'       => 'PKR',
                        'is_active'      => true,
                        'sort_order'     => 3,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[MultiBankQR] Unable to check or run migrations: ' . $e->getMessage());
        }
    }
}
