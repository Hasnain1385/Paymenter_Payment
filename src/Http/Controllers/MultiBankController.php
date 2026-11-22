<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction;
use Paymenter\Extensions\Gateways\MultiBankQR\Services\BankAccountService;
use Paymenter\Extensions\Gateways\MultiBankQR\MultiBankQR;

class MultiBankController extends Controller
{
    /**
     * Standalone full-page checkout view for the invoice.
     */
    public function showCheckout(Request $request, $invoiceId)
    {
        $invoiceClass = '\App\Models\Invoice';
        $invoice = class_exists($invoiceClass) ? $invoiceClass::findOrFail($invoiceId) : null;

        if (!$invoice) {
            abort(404, 'Invoice not found');
        }

        $gateway = new MultiBankQR();

        return $gateway->pay($invoice, $invoice->remaining ?? $invoice->total, isStandalone: true);
    }

    /**
     * Handle payment proof submission from the customer.
     */
    public function submitProof(Request $request, $invoiceId)
    {
        $invoiceClass = '\App\Models\Invoice';
        $invoice = class_exists($invoiceClass) ? $invoiceClass::findOrFail($invoiceId) : null;

        if (!$invoice) {
            abort(404, 'Invoice not found');
        }

        // Validate submission
        $request->validate([
            'transaction_id' => ['required', 'string', 'max:150'],
            'sender_name' => ['required', 'string', 'max:150'],
            'bank_account_name' => ['nullable', 'string', 'max:150'],
            'sender_account' => ['nullable', 'string', 'max:150'],
            'proof_image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:10240'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'transaction_id.required' => 'Please provide the Transaction ID (TRX / UTR / Reference Number) from your payment receipt.',
            'sender_name.required' => 'Please enter the Account Name / Sender Name used to make the payment.',
            'proof_image.mimes' => 'The receipt must be an image (JPEG, PNG, WEBP) or PDF file.',
            'proof_image.max' => 'The receipt file size must not exceed 10MB.',
        ]);

        $storedPath = null;
        if ($request->hasFile('proof_image')) {
            $file = $request->file('proof_image');
            $storedPath = $file->store('multibank_proofs', 'public');
        }

        // Create or update transaction record for this invoice
        $transaction = MultiBankTransaction::updateOrCreate(
            ['invoice_id' => $invoice->id],
            [
                'user_id' => auth()->id() ?? ($invoice->user_id ?? null),
                'bank_account_name' => $request->bank_account_name ?: 'Bank Transfer',
                'amount' => $invoice->remaining ?? $invoice->total,
                'currency' => $invoice->currency_code ?? ($invoice->currency ?? 'USD'),
                'transaction_id' => trim($request->transaction_id),
                'sender_name' => trim($request->sender_name),
                'sender_account' => $request->sender_account ? trim($request->sender_account) : null,
                'proof_image' => $storedPath ?: MultiBankTransaction::where('invoice_id', $invoice->id)->value('proof_image'),
                'customer_notes' => $request->customer_notes,
                'status' => MultiBankTransaction::STATUS_PENDING,
                'admin_notes' => null,
                'decline_reason' => null,
            ]
        );

        // Register a processing transaction in Paymenter if ExtensionHelper is available
        try {
            if (class_exists(\App\Helpers\ExtensionHelper::class) && method_exists(\App\Helpers\ExtensionHelper::class, 'addPayment')) {
                $statusEnum = class_exists(\App\Enums\InvoiceTransactionStatus::class)
                    ? \App\Enums\InvoiceTransactionStatus::Processing
                    : 'processing';

                \App\Helpers\ExtensionHelper::addPayment(
                    $invoice,
                    'MultiBankQR',
                    $transaction->amount,
                    fee: null,
                    transactionId: $transaction->transaction_id,
                    status: $statusEnum
                );
            }
        } catch (\Throwable $e) {
            // Non-critical, continue
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment proof submitted successfully! Awaiting verification.',
                'transaction' => $transaction,
                'status_label' => $transaction->getStatusLabel(),
            ]);
        }

        return redirect()->back()->with('success', 'Your payment proof has been submitted! Our team is verifying your payment.');
    }

    /**
     * Poll transaction status for real-time live UI updates without reloading.
     */
    public function checkStatus(Request $request, $invoiceId)
    {
        $invoiceClass = '\App\Models\Invoice';
        $invoice = class_exists($invoiceClass) ? $invoiceClass::find($invoiceId) : null;

        $transaction = MultiBankTransaction::where('invoice_id', $invoiceId)->first();

        $isPaid = $invoice && in_array(strtolower($invoice->status), ['paid', 'completed', 'active']);

        $redirectUrl = function_exists('route') && \Illuminate\Support\Facades\Route::has('invoices.show')
            ? route('invoices.show', $invoiceId)
            : "/invoices/{$invoiceId}";

        return response()->json([
            'is_paid' => $isPaid,
            'has_transaction' => $transaction !== null,
            'status' => $transaction ? $transaction->status : null,
            'status_label' => $transaction ? $transaction->getStatusLabel() : 'No submission',
            'status_badge_class' => $transaction ? $transaction->getStatusBadgeClass() : '',
            'admin_notes' => $transaction ? $transaction->admin_notes : null,
            'decline_reason' => $transaction ? $transaction->decline_reason : null,
            'redirect_url' => $redirectUrl,
        ]);
    }
}
