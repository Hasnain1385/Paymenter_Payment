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

// Standalone PSR-4 autoloader: ensures extension classes load reliably across any Paymenter core updates
spl_autoload_register(function ($class) {
    $prefix = 'Paymenter\\Extensions\\Gateways\\MultiBankQR\\';
    if (str_starts_with($class, $prefix)) {
        $relativeClass = substr($class, strlen($prefix));
        $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

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
    name: 'PakPay - Bank Transfer & QR Gateway',
    description: 'Accept Pakistani bank & mobile wallet payments (Raast, Meezan, Easypaisa, JazzCash, SadaPay, NayaPay) with scanned static QR codes, WhatsApp & Discord support buttons, and instant admin verification.',
    version: '1.3.6',
    author: 'Mirza Hasnain Baig',
    url: 'https://github.com/Hasnain1385/Paymenter_Payment',
    icon: 'https://raw.githubusercontent.com/Hasnain1385/Paymenter_Payment/main/logo.png'
)]
class MultiBankQR extends Gateway
{
    public const VERSION = '1.3.6';

    /**
     * Instantiate the gateway and ensure routes and services are immediately booted.
     */
    public function __construct()
    {
        $this->boot();
    }

    /**
     * Boot the extension: register routes, views, service provider, and auto-migrate tables if needed.
     */
    public function boot($extension = null)
    {
        // Load composer autoload if vendor exists inside extension folder
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require_once __DIR__ . '/vendor/autoload.php';
        }

        // Register Web routes
        if (class_exists(\Illuminate\Support\Facades\Route::class)) {
            if (file_exists(__DIR__ . '/routes/web.php')) {
                require_once __DIR__ . '/routes/web.php';
            } elseif (file_exists(__DIR__ . '/routes.php')) {
                require_once __DIR__ . '/routes.php';
            }
        }

        // Register Views namespace
        if (file_exists(__DIR__ . '/resources/views') && class_exists(\Illuminate\Support\Facades\View::class)) {
            try {
                \Illuminate\Support\Facades\View::addNamespace('multibankqr', __DIR__ . '/resources/views');
            } catch (\Throwable $e) {}
        }

        // Register ServiceProvider if not yet registered
        if (function_exists('app') && class_exists(\Paymenter\Extensions\Gateways\MultiBankQR\MultiBankQRServiceProvider::class)) {
            try {
                app()->register(\Paymenter\Extensions\Gateways\MultiBankQR\MultiBankQRServiceProvider::class);
            } catch (\Throwable $e) {}
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
            [
                'name' => 'gateway_title',
                'label' => 'Gateway Display Name',
                'type' => 'text',
                'default' => 'Bank Transfer / Mobile Wallet & QR',
                'description' => 'Title shown to customers during checkout.',
                'required' => false,
            ],
            [
                'name' => 'whatsapp_number',
                'label' => 'Support WhatsApp Number',
                'type' => 'text',
                'default' => '',
                'placeholder' => '+923001234567',
                'description' => 'Official WhatsApp number for support (e.g. +923001234567). Leaves button hidden if empty.',
                'required' => false,
            ],
            [
                'name' => 'discord_invite',
                'label' => 'Discord Server Invite Link',
                'type' => 'text',
                'default' => '',
                'placeholder' => 'https://discord.gg/yourserver',
                'description' => 'Invite link for customer support. Leaves button hidden if empty.',
                'required' => false,
            ],
            [
                'name' => 'custom_notice',
                'label' => 'Checkout Payment Notice',
                'type' => 'textarea',
                'default' => 'Please transfer the exact amount and enter your Transaction ID (TRX/UTR) below.',
                'description' => 'Guidance note displayed to customers above the payment methods.',
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
        if (!class_exists(\Illuminate\Support\Facades\Schema::class)) {
            return;
        }

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

            // Ensure gateway is registered and retained in Paymenter's extensions table across core updates
            if (Schema::hasTable('extensions') && class_exists(\Illuminate\Support\Facades\DB::class)) {
                try {
                    $ext = \Illuminate\Support\Facades\DB::table('extensions')->where('name', 'MultiBankQR')->first();
                    if (!$ext) {
                        \Illuminate\Support\Facades\DB::table('extensions')->insert([
                            'name'       => 'MultiBankQR',
                            'type'       => 'gateway',
                            'enabled'    => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {
            if (class_exists(\Illuminate\Support\Facades\Log::class)) {
                Log::warning('[MultiBankQR] Unable to check or run migrations: ' . $e->getMessage());
            }
        }
    }
}

// Global auto-boot: ensure routes and services are immediately registered when Paymenter loads this file
if (class_exists(\Illuminate\Support\Facades\Route::class)) {
    if (file_exists(__DIR__ . '/routes/web.php')) {
        require_once __DIR__ . '/routes/web.php';
    } elseif (file_exists(__DIR__ . '/routes.php')) {
        require_once __DIR__ . '/routes.php';
    }
    if (file_exists(__DIR__ . '/resources/views') && class_exists(\Illuminate\Support\Facades\View::class)) {
        try {
            \Illuminate\Support\Facades\View::addNamespace('multibankqr', __DIR__ . '/resources/views');
        } catch (\Throwable $e) {}
    }
    if (function_exists('app') && class_exists(\Paymenter\Extensions\Gateways\MultiBankQR\MultiBankQRServiceProvider::class)) {
        try {
            app()->register(\Paymenter\Extensions\Gateways\MultiBankQR\MultiBankQRServiceProvider::class);
        } catch (\Throwable $e) {}
    }
}
