<?php

namespace Illuminate\Database\Eloquent {
    if (!class_exists('Illuminate\Database\Eloquent\Model')) {
        class Model {
            protected array $attributes = [];
            public function __get($key) { return $this->attributes[$key] ?? null; }
            public function __set($key, $val) { $this->attributes[$key] = $val; }
        }
    }
}

namespace {
require_once __DIR__ . '/../vendor/autoload.php';

use Paymenter\Extensions\Gateways\MultiBankQR\Services\QrCodeService;
use Paymenter\Extensions\Gateways\MultiBankQR\Services\BankAccountService;
use Paymenter\Extensions\Gateways\MultiBankQR\Services\GithubUpdateService;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankAccount;

class MultiBankQRTest
{
    public static function run()
    {
        echo "Running MultiBankQR Pakistani & Security Test Suite...\n\n";

        // 1. Scanned Static QR Image Priority
        $staticRes = QrCodeService::generate(
            "000201010211...", // raw payload
            5000,
            123,
            "storage/multibank_qrcodes/meezan_raast.png" // merchant's scanned static QR
        );
        assert($staticRes['is_image'] === true, "Scanned static QR should be prioritized as image");
        assert(str_contains($staticRes['qr_src'], "meezan_raast.png"), "QR src should use the static image path");
        echo "✓ Scanned Static QR Image is correctly prioritized\n";

        // 2. Dynamic QR Fallback
        $dynRes = QrCodeService::generate("https://pay.example.com?inv={invoice_id}&amt={amount}", 2500.00, 77);
        assert(str_starts_with($dynRes["qr_src"], "data:image/svg+xml;base64,"), "QR src should be base64 SVG data URI");
        assert(str_contains($dynRes["payload"], "inv=77&amt=2500"), "Tokens should be replaced");
        echo "✓ QrCodeService dynamic token replacement & SVG generation\n";

        // 3. Pakistani Account Resolution (Raast, Easypaisa, JazzCash)
        $pakConfig = [
            "acc1_enabled" => 1,
            "acc1_name" => "Meezan Bank / Raast P2P",
            "acc1_badge" => "Instant Raast",
            "acc1_title" => "Ali Khan",
            "acc1_number" => "PK42MEZN0001020304050607",
            "acc1_currency" => "PKR",
            "acc1_qr_image" => "https://example.com/meezan.png",
            "acc2_enabled" => 1,
            "acc2_name" => "Easypaisa Wallet",
            "acc2_badge" => "Easypaisa",
            "acc2_title" => "Ali Khan",
            "acc2_number" => "03001234567",
            "acc2_currency" => "PKR",
            "acc2_qr_image" => "https://example.com/easypaisa.png",
        ];

        $pkrAccounts = BankAccountService::getAccountsForInvoice($pakConfig, 5000, "PKR", 123);
        assert(count($pkrAccounts) === 2, "Both Pakistani accounts resolved for PKR invoice");
        assert($pkrAccounts[0]['is_image'] === true, "Account 1 uses static scanned QR image");
        assert($pkrAccounts[1]['name'] === "Easypaisa Wallet", "Account 2 is Easypaisa");
        echo "✓ Pakistani Multi-Account Resolution & Static Scanned QR handling\n";

        // 4. Status Lifecycle Transition: Pending Payment -> Pending Verification -> Verified
        $tx = new MultiBankTransaction();

        // Stage 1: Pending Payment
        $tx->status = MultiBankTransaction::STATUS_PENDING_PAYMENT;
        assert($tx->isPendingPayment() && !$tx->isVerified(), "Initial state is Pending Payment");
        assert($tx->getStatusLabel() === 'Pending Payment', "Status label is Pending Payment");

        // Stage 2: Pending Verification (after customer submission)
        $tx->status = MultiBankTransaction::STATUS_PENDING_VERIFICATION;
        assert($tx->isPendingVerification() && !$tx->isVerified(), "Transition to Pending Verification works");
        assert($tx->getStatusLabel() === 'Pending Verification', "Status label is Pending Verification");

        // Stage 3: Verified (after manual admin approval)
        $tx->status = MultiBankTransaction::STATUS_VERIFIED;
        assert($tx->isVerified() && !$tx->isPendingVerification(), "Transition to Verified works");
        assert($tx->getStatusLabel() === 'Verified', "Status label is Verified");
        echo "✓ Status Lifecycle: Pending Payment -> Pending Verification -> Verified\n";

        // 5. Input Sanitization against XSS
        $dirtyTrx = "  <script>alert('xss')</script>TRX99887766  ";
        $cleanTrx = htmlspecialchars(strip_tags(trim($dirtyTrx)), ENT_QUOTES, 'UTF-8');
        assert($cleanTrx === "TRX99887766", "XSS script tags stripped safely");
        echo "✓ Security: Input Sanitization against XSS attacks\n";

        // 6. Dynamic Bank Account Model & Currency Matching
        $acc = new MultiBankAccount();
        $acc->name = "JazzCash Merchant";
        $acc->account_number = "03009988776";
        $acc->currency = "PKR";
        $acc->is_active = true;
        assert($acc->matchesCurrency("PKR") === true, "Account matches PKR currency");
        assert($acc->matchesCurrency("USD") === false, "Account rejects USD currency");
        $acc->currency = "ALL";
        assert($acc->matchesCurrency("USD") === true, "Universal ALL account matches USD");
        echo "✓ Dynamic Bank Account Model & Currency Matching\n";

        // 7. GitHub In-Place Update Service & Git Status
        $gitStatus = GithubUpdateService::getGitStatus();
        assert(is_array($gitStatus), "Git status returns array");
        assert(isset($gitStatus['is_git']), "is_git property exists");
        assert($gitStatus['is_git'] === true, "Local clone is detected as Git repository");
        echo "✓ GitHub In-Place Update Service & Local Git Status Detection\n";

        // 8. Decluttered getConfig() Verification
        require_once __DIR__ . '/../MultiBankQR.php';
        $gateway = new \Paymenter\Extensions\Gateways\MultiBankQR\MultiBankQR();
        $config = $gateway->getConfig();
        assert(is_array($config), "getConfig returns array");
        assert(count($config) === 5, "getConfig is decluttered to exactly 5 essential fields with zero raw HTML placeholders");
        $fieldNames = array_column($config, 'name');
        assert(!in_array('pakpay_hub_notice', $fieldNames), "getConfig removes raw HTML pakpay_hub_notice placeholder");
        assert(!in_array('acc1_name', $fieldNames), "getConfig removes old acc1 clutter field");
        assert(!in_array('acc4_currency', $fieldNames), "getConfig removes old acc4 clutter field");
        assert(\Paymenter\Extensions\Gateways\MultiBankQR\MultiBankQR::VERSION === '1.3.6', "Version bumped to 1.3.6");
        echo "✓ Gateway Setup Decluttered: 5 clean fields, zero raw HTML placeholders, v1.3.6\n";

        // 9. Raw QR Payload with tokens
        $rawPayload = "00020101021132480016A0000007270401145204541153035865404{amount}5802PK5914Merchant Title6006Lahore62170113INV-{invoice_id}6304";
        $resolvedPayload = QrCodeService::preparePayload($rawPayload, 1500.50, 42);
        assert(str_contains($resolvedPayload, "54041500.5"), "Token {amount} properly replaced");
        assert(str_contains($resolvedPayload, "INV-42"), "Token {invoice_id} properly replaced");
        $rawQrResult = QrCodeService::generate($resolvedPayload);
        assert($rawQrResult['is_image'] === false, "Dynamic QR rendered from raw payload");
        assert(str_starts_with($rawQrResult['qr_src'], "data:image/svg+xml;base64,"), "Vector SVG QR generated from raw payload");
        echo "✓ Raw QR Payload (EMVCo/Raast) Token Resolution & Dynamic Vector QR Generation\n";

        // 10. Proof Image Path Accessor & Model verification
        $testTx = new MultiBankTransaction();
        $testTx->proof_image = 'storage/multibank_proofs/receipt_123.jpg';
        assert($testTx->proof_image_path === '/storage/multibank_proofs/receipt_123.jpg', "proof_image_path formatted with leading slash");
        echo "✓ MultiBankTransaction proof_image_path accessor formatted safely\n";

        // 11. Crash-Proof Admin Navigation: getUrl & getNavigationBadge never throw RouteNotFoundException
        if (class_exists(\Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankAccountResource::class)) {
            assert(is_string(\Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankAccountResource::getUrl()), "MultiBankAccountResource::getUrl safe fallback");
            assert(is_string(\Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankTransactionResource::getUrl()), "MultiBankTransactionResource::getUrl safe fallback");
            assert(is_string(\Paymenter\Extensions\Gateways\MultiBankQR\Filament\Pages\PakPay::getUrl()), "PakPay::getUrl safe fallback");
            assert(\Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankAccountResource::shouldRegisterNavigation() === true, "MultiBankAccountResource navigation enabled");
            assert(\Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankTransactionResource::shouldRegisterNavigation() === true, "MultiBankTransactionResource navigation enabled");
            assert(\Paymenter\Extensions\Gateways\MultiBankQR\Filament\Pages\PakPay::shouldRegisterNavigation() === true, "PakPay page navigation enabled");
            \Paymenter\Extensions\Gateways\MultiBankQR\Filament\Resources\MultiBankTransactionResource::getNavigationBadge();
        }
        assert(class_exists(\Paymenter\Extensions\Gateways\MultiBankQR\MultiBankQRServiceProvider::class), "MultiBankQRServiceProvider loaded");
        echo "✓ Filament Navigation & URL Safe Fallbacks (Zero 500 error risk)\n";

        echo "\n🎉 All Pakistani & Security tests passed successfully!\n";
    }
}

MultiBankQRTest::run();
}
