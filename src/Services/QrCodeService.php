<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeService
{
    /**
     * Generate QR Code as Data URI or return direct scanned static image URL.
     * Prioritizes scanned static QR images when provided by the merchant.
     *
     * @param string|null $payload Scanned string or payment payload
     * @param float|int|string|null $amount
     * @param string|int|null $invoiceId
     * @param string|null $staticImageUrl Scanned static QR image path or URL
     * @return array [
     *     'qr_src' => string, // data URI or URL
     *     'payload' => string,
     *     'is_image' => bool
     * ]
     */
    public static function generate(
        ?string $payload,
        float|int|string|null $amount = null,
        string|int|null $invoiceId = null,
        ?string $staticImageUrl = null
    ): array {
        // 1. Merchant's Scanned Static QR Image has top priority
        if (!empty($staticImageUrl)) {
            return [
                'qr_src'   => self::formatImageUrl($staticImageUrl),
                'payload'  => $payload ?? '',
                'is_image' => true,
            ];
        }

        // 2. Generate dynamic vector QR if custom payload exists
        if (!empty($payload)) {
            $finalPayload = self::preparePayload($payload, $amount, $invoiceId);

            try {
                // Primary: Chillerlan QRCode
                if (class_exists(QRCode::class)) {
                    $options = new QROptions([
                        'outputBase64' => true,
                        'scale'        => 6,
                    ]);
                    $dataUri = (new QRCode($options))->render($finalPayload);

                    return [
                        'qr_src'   => $dataUri,
                        'payload'  => $finalPayload,
                        'is_image' => false,
                    ];
                }

                // Secondary: Endroid QrCode (bundled in Paymenter)
                if (class_exists(\Endroid\QrCode\QrCode::class)) {
                    $writer = new \Endroid\QrCode\Writer\SvgWriter();
                    $qrCode = \Endroid\QrCode\QrCode::create($finalPayload);
                    $result = $writer->write($qrCode);

                    return [
                        'qr_src'   => $result->getDataUri(),
                        'payload'  => $finalPayload,
                        'is_image' => false,
                    ];
                }
            } catch (\Throwable $e) {
                // If rendering failed, return empty
            }
        }

        // 3. Fallback: No QR available
        return [
            'qr_src'   => '',
            'payload'  => '',
            'is_image' => false,
        ];
    }

    /**
     * Replace dynamic tokens like {amount}, {invoice_id}, {invoice_number} in custom payloads.
     */
    public static function preparePayload(string $raw, $amount = null, $invoiceId = null): string
    {
        $payload = str_replace(
            ['{amount}', '{invoice_id}', '{invoice_number}'],
            [$amount ?? '0', $invoiceId ?? '', $invoiceId ?? ''],
            $raw
        );

        return trim($payload);
    }

    /**
     * Format image URL to ensure public storage / asset path is correct.
     * Sanitizes output to prevent XSS.
     */
    public static function formatImageUrl(string $path): string
    {
        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return filter_var($path, FILTER_SANITIZE_URL) ?: '';
        }

        // Check if it's a public storage path
        if (function_exists('asset')) {
            return asset('storage/' . ltrim($path, '/'));
        }

        return '/' . ltrim($path, '/');
    }

    /**
     * Build UPI payment URI (standard for UPI apps).
     */
    public static function buildUpiPayload(string $upiId, string $name, $amount = null, $invoiceId = null, string $currency = 'PKR'): string
    {
        $params = [
            'pa' => $upiId,
            'pn' => $name,
            'cu' => $currency,
        ];

        if ($amount !== null && (float) $amount > 0) {
            $params['am'] = number_format((float) $amount, 2, '.', '');
        }

        if ($invoiceId !== null) {
            $params['tn'] = 'Invoice ' . $invoiceId;
        }

        return 'upi://pay?' . http_build_query($params);
    }

    /**
     * Build standard European EPC QR code for SEPA credit transfers.
     */
    public static function buildSepaPayload(string $iban, string $bic, string $name, $amount = null, $invoiceId = null): string
    {
        $cleanIban = preg_replace('/\s+/', '', strtoupper($iban));
        $cleanBic = preg_replace('/\s+/', '', strtoupper($bic));
        $cleanAmount = ($amount !== null && (float) $amount > 0) ? 'EUR' . number_format((float) $amount, 2, '.', '') : '';
        $ref = $invoiceId ? 'INV-' . $invoiceId : 'INVOICE';

        return "BCD\n002\n1\nSCT\n{$cleanBic}\n{$name}\n{$cleanIban}\n{$cleanAmount}\n\n{$ref}";
    }
}
