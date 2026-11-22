<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Paymenter\Extensions\Gateways\MultiBankQR\Services\QrCodeService;
use Paymenter\Extensions\Gateways\MultiBankQR\Services\BankAccountService;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction;

class MultiBankQRTest
{
    public static function run()
    {
        echo "Running MultiBankQR Test Suite...\n\n";

        // 1. QR Code Generation
        $res = QrCodeService::generate("https://pay.example.com?inv={invoice_id}&amt={amount}", 250.00, 77);
        assert(str_starts_with($res["qr_src"], "data:image/svg+xml;base64,"), "QR src should be base64 SVG data URI");
        assert(str_contains($res["payload"], "inv=77&amt=250"), "Tokens should be replaced");
        echo "✓ QrCodeService token replacement & SVG generation\n";

        // 2. UPI Payload
        $upi = QrCodeService::buildUpiPayload("sales@upi", "Hosting Co", 1500, 88, "INR");
        assert(str_contains($upi, "pa=sales%40upi") && str_contains($upi, "am=1500.00"), "UPI payload valid");
        echo "✓ UPI Payload Builder\n";

        // 3. SEPA EPC Payload
        $sepa = QrCodeService::buildSepaPayload("FR1420041010050500013M02606", "BNPAFRPP", "Hosting Corp", 49.99, 99);
        assert(str_contains($sepa, "FR1420041010050500013M02606") && str_contains($sepa, "EUR49.99"), "SEPA payload valid");
        echo "✓ SEPA EPC Payload Builder\n";

        // 4. Flat Settings Account Resolution & Currency Filtering
        $config = [
            "acc1_enabled" => 1,
            "acc1_name" => "US Wire & Zelle",
            "acc1_badge" => "Recommended",
            "acc1_title" => "Hosting Corp LLC",
            "acc1_number" => "1122334455",
            "acc1_currency" => "USD",
            "acc2_enabled" => 1,
            "acc2_name" => "Wise Global Account",
            "acc2_badge" => "Multi-Currency",
            "acc2_title" => "Hosting Corp LLC",
            "acc2_number" => "GB1122334455",
            "acc2_currency" => "ALL",
        ];

        $usdAccounts = BankAccountService::getAccountsForInvoice($config, 100, "USD", 10);
        assert(count($usdAccounts) === 2, "USD invoice matches both USD and ALL accounts");
        echo "✓ BankAccountService USD matches specific and ALL currency accounts\n";

        $eurAccounts = BankAccountService::getAccountsForInvoice($config, 100, "EUR", 10);
        assert(count($eurAccounts) === 1, "EUR invoice only matches ALL currency account");
        echo "✓ BankAccountService EUR filters out USD-only account\n";

        // 5. JSON Accounts Parsing
        $jsonConfig = [
            "accounts_json" => json_encode([
                [
                    "name" => "USDT (TRC20)",
                    "badge" => "Zero Fee",
                    "account_title" => "USDT Wallet",
                    "account_number" => "TXYZ9876543210",
                    "currency" => "ALL",
                ]
            ])
        ];
        $jsonAccs = BankAccountService::getAccountsForInvoice($jsonConfig, 75, "USD", 11);
        assert(count($jsonAccs) === 1 && $jsonAccs[0]["name"] === "USDT (TRC20)", "JSON accounts parsed successfully");
        echo "✓ JSON Accounts Parsing\n";

        // 6. Transaction Status Helpers
        $tx = new MultiBankTransaction();
        $tx->status = MultiBankTransaction::STATUS_PENDING;
        assert($tx->isPending() && !$tx->isApproved(), "isPending helper works");
        assert(str_contains($tx->getStatusLabel(), 'Verification'), "Status label contains Verification");
        echo "✓ MultiBankTransaction Model Status Helpers\n";

        echo "\n🎉 All tests passed successfully!\n";
    }
}

MultiBankQRTest::run();
