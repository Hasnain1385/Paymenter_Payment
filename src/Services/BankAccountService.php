<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Services;

use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankAccount;
use Illuminate\Support\Facades\Schema;

class BankAccountService
{
    /**
     * Resolve all available bank accounts for an invoice.
     * Checks database first; if empty, falls back to extension configuration settings.
     *
     * @param array $config Extension configuration array from Paymenter
     * @param float|int|string|null $amount Invoice total
     * @param string|null $currency Invoice currency code
     * @param string|int|null $invoiceId Invoice number/ID
     * @return array Array of formatted account objects/arrays
     */
    public static function getAccountsForInvoice(
        array $config,
        $amount = null,
        ?string $currency = null,
        $invoiceId = null
    ): array {
        $accounts = [];

        // 1. Try resolving from database table if table exists
        try {
            if (Schema::hasTable('multibank_accounts')) {
                $dbAccounts = MultiBankAccount::active()->get();
                foreach ($dbAccounts as $acc) {
                    if ($acc->matchesCurrency($currency)) {
                        $qr = QrCodeService::generate(
                            $acc->qr_payload ?: $acc->account_number,
                            $amount,
                            $invoiceId,
                            $acc->qr_image_url
                        );

                        $accounts[] = [
                            'id' => 'db_' . $acc->id,
                            'name' => $acc->name,
                            'badge' => $acc->badge ?: 'Active',
                            'account_title' => $acc->account_title,
                            'account_number' => $acc->account_number,
                            'routing_code' => $acc->routing_code,
                            'instructions' => $acc->instructions,
                            'currency' => $acc->currency,
                            'qr_src' => $qr['qr_src'],
                            'is_image' => $qr['is_image'],
                            'qr_payload' => $qr['payload'],
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // DB not ready or error; continue to config fallback
        }

        // 2. If database accounts are empty, check custom JSON in config
        if (empty($accounts) && !empty($config['accounts_json'])) {
            try {
                $decoded = json_decode($config['accounts_json'], true);
                if (is_array($decoded)) {
                    foreach ($decoded as $index => $acc) {
                        $accCurrency = $acc['currency'] ?? 'ALL';
                        if (empty($accCurrency) || strtoupper($accCurrency) === 'ALL' || strtoupper($accCurrency) === strtoupper($currency ?? '')) {
                            $payload = $acc['qr_payload'] ?? ($acc['account_number'] ?? '');
                            $qr = QrCodeService::generate(
                                $payload,
                                $amount,
                                $invoiceId,
                                $acc['qr_image_url'] ?? null
                            );

                            $accounts[] = [
                                'id' => 'json_' . $index,
                                'name' => $acc['name'] ?? ('Account ' . ($index + 1)),
                                'badge' => $acc['badge'] ?? 'Direct Transfer',
                                'account_title' => $acc['account_title'] ?? '',
                                'account_number' => $acc['account_number'] ?? '',
                                'routing_code' => $acc['routing_code'] ?? null,
                                'instructions' => $acc['instructions'] ?? null,
                                'currency' => $accCurrency,
                                'qr_src' => $qr['qr_src'],
                                'is_image' => $qr['is_image'],
                                'qr_payload' => $qr['payload'],
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Invalid JSON, continue to flat settings
            }
        }

        // 3. Fallback to flat structured extension settings (up to 4 accounts)
        if (empty($accounts)) {
            for ($i = 1; $i <= 4; $i++) {
                $enabled = !empty($config["acc{$i}_enabled"]) ? (bool) $config["acc{$i}_enabled"] : false;
                $name = $config["acc{$i}_name"] ?? null;
                $number = $config["acc{$i}_number"] ?? null;

                // Account 1 defaults to enabled if name/number is set, or if it's the only one
                if ($i === 1 && empty($name) && empty($number) && !isset($config['acc1_enabled'])) {
                    // Pre-fill a demo account so the user sees a working setup on first activation
                    $name = 'Default Bank / Wire Transfer';
                    $number = '123456789012';
                    $enabled = true;
                }

                if ($enabled && !empty($name)) {
                    $accCurrency = $config["acc{$i}_currency"] ?? 'ALL';
                    if (empty($accCurrency) || strtoupper($accCurrency) === 'ALL' || strtoupper($accCurrency) === strtoupper($currency ?? '')) {
                        $payload = $config["acc{$i}_qr_payload"] ?? $number;
                        $qr = QrCodeService::generate(
                            $payload,
                            $amount,
                            $invoiceId,
                            $config["acc{$i}_qr_image"] ?? null
                        );

                        $accounts[] = [
                            'id' => 'cfg_' . $i,
                            'name' => $name,
                            'badge' => $config["acc{$i}_badge"] ?? ($i === 1 ? 'Recommended' : 'Bank Transfer'),
                            'account_title' => $config["acc{$i}_title"] ?? 'Merchant Account',
                            'account_number' => $number ?? '',
                            'routing_code' => $config["acc{$i}_routing"] ?? null,
                            'instructions' => $config["acc{$i}_instructions"] ?? null,
                            'currency' => $accCurrency,
                            'qr_src' => $qr['qr_src'],
                            'is_image' => $qr['is_image'],
                            'qr_payload' => $qr['payload'],
                        ];
                    }
                }
            }
        }

        return $accounts;
    }
}
