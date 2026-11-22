<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Services;

use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction;
use Illuminate\Support\Facades\Log;

class PaymentApprovalService
{
    /**
     * Manually verify and approve the transaction, marking the invoice as PAID in Paymenter.
     * Lifecycle: Pending Verification -> Verified.
     *
     * @param MultiBankTransaction $transaction
     * @param int|null $adminId
     * @return bool
     */
    public static function approve(MultiBankTransaction $transaction, ?int $adminId = null): bool
    {
        $transaction->status = MultiBankTransaction::STATUS_VERIFIED;
        $transaction->reviewed_by = $adminId;
        $transaction->reviewed_at = now();
        $transaction->save();

        $invoiceId = $transaction->invoice_id;

        try {
            // Attempt 1: Paymenter ExtensionHelper::addPayment with Succeeded status
            if (class_exists(\App\Helpers\ExtensionHelper::class) && method_exists(\App\Helpers\ExtensionHelper::class, 'addPayment')) {
                $invoiceClass = '\App\Models\Invoice';
                $invoice = class_exists($invoiceClass) ? $invoiceClass::find($invoiceId) : null;
                $statusEnum = class_exists(\App\Enums\InvoiceTransactionStatus::class)
                    ? \App\Enums\InvoiceTransactionStatus::Succeeded
                    : 'succeeded';

                if ($invoice) {
                    \App\Helpers\ExtensionHelper::addPayment(
                        $invoice,
                        'MultiBankQR',
                        $transaction->amount,
                        fee: null,
                        transactionId: $transaction->transaction_id,
                        status: $statusEnum
                    );
                    Log::info("[MultiBankQR] Invoice #{$invoiceId} marked paid via ExtensionHelper::addPayment");
                    return true;
                }
            }

            // Attempt 2: Paymenter ExtensionHelper::paymentDone
            if (class_exists(\App\Helpers\ExtensionHelper::class) && method_exists(\App\Helpers\ExtensionHelper::class, 'paymentDone')) {
                \App\Helpers\ExtensionHelper::paymentDone($invoiceId);
                Log::info("[MultiBankQR] Invoice #{$invoiceId} marked paid via ExtensionHelper::paymentDone");
                return true;
            }

            // Attempt 3: Direct Eloquent Invoice update & event dispatch
            $invoiceClass = '\App\Models\Invoice';
            if (class_exists($invoiceClass)) {
                $invoice = $invoiceClass::find($invoiceId);
                if ($invoice) {
                    $invoice->status = 'paid';
                    if (isset($invoice->paid_at)) {
                        $invoice->paid_at = now();
                    }
                    $invoice->save();

                    // Provision services if Paymenter method exists
                    if (class_exists(\App\Helpers\ExtensionHelper::class) && method_exists(\App\Helpers\ExtensionHelper::class, 'processOrder')) {
                        \App\Helpers\ExtensionHelper::processOrder($invoice);
                    }

                    // Dispatch Invoice events
                    if (class_exists(\App\Events\Invoice\Paid::class)) {
                        event(new \App\Events\Invoice\Paid($invoice));
                    } elseif (class_exists(\App\Events\Invoice\Updated::class)) {
                        event(new \App\Events\Invoice\Updated($invoice));
                    }

                    Log::info("[MultiBankQR] Invoice #{$invoiceId} status updated to paid directly.");
                    return true;
                }
            }
        } catch (\Throwable $e) {
            Log::error("[MultiBankQR] Failed to approve invoice #{$invoiceId}: " . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Decline the transaction with an optional reason.
     */
    public static function decline(MultiBankTransaction $transaction, ?string $reason = null, ?int $adminId = null): bool
    {
        $transaction->status = MultiBankTransaction::STATUS_DECLINED;
        $transaction->decline_reason = $reason ?: 'Transaction details could not be verified.';
        $transaction->reviewed_by = $adminId;
        $transaction->reviewed_at = now();
        $transaction->save();

        Log::info("[MultiBankQR] Transaction #{$transaction->id} for Invoice #{$transaction->invoice_id} declined. Reason: {$reason}");

        return true;
    }

    /**
     * Flag transaction with a custom admin note (Action Required / Under Review).
     */
    public static function flag(MultiBankTransaction $transaction, string $note, ?int $adminId = null): bool
    {
        $transaction->status = MultiBankTransaction::STATUS_FLAGGED;
        $transaction->admin_notes = $note;
        $transaction->reviewed_by = $adminId;
        $transaction->reviewed_at = now();
        $transaction->save();

        Log::info("[MultiBankQR] Transaction #{$transaction->id} for Invoice #{$transaction->invoice_id} flagged: {$note}");

        return true;
    }
}
