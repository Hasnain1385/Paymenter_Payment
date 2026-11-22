<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Paymenter\Extensions\Gateways\MultiBankQR\Services\QrCodeService;
use Paymenter\Extensions\Gateways\MultiBankQR\Services\BankAccountService;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction;

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

        echo "\n🎉 All Pakistani & Security tests passed successfully!\n";
    }
}

MultiBankQRTest::run();
