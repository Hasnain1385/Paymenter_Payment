<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction;
use Paymenter\Extensions\Gateways\MultiBankQR\MultiBankQR;

class MultiBankController extends Controller
{
    /**
     * Standalone full-page checkout view for the invoice.
     */
    public function showCheckout(Request $request, $invoiceId)
    {
        $invoiceId = (int) $invoiceId;
        $invoiceClass = '\App\Models\Invoice';
        $invoice = class_exists($invoiceClass) ? $invoiceClass::findOrFail($invoiceId) : null;

        if (!$invoice) {
            abort(404, 'Invoice not found');
        }

        // Authorization check: User must own invoice or be admin if logged in
        if (auth()->check()) {
            $user = auth()->user();
            $isAdmin = ($user->is_admin ?? false) || ($user->role === 'admin') || ($user->is_staff ?? false);
            if (!$isAdmin && $invoice->user_id && (int) $invoice->user_id !== (int) $user->id) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        }

        $gateway = new MultiBankQR();

        return $gateway->pay($invoice, $invoice->remaining ?? $invoice->total, isStandalone: true);
    }

    /**
     * Handle payment proof submission from the customer.
     * Transitions status from Pending Payment to Pending Verification.
     * Hardened against SQLi, XSS, and malicious file uploads.
     */
    public function submitProof(Request $request, $invoiceId)
    {
        $invoiceId = (int) $invoiceId;
        $invoiceClass = '\App\Models\Invoice';
        $invoice = class_exists($invoiceClass) ? $invoiceClass::findOrFail($invoiceId) : null;

        if (!$invoice) {
            abort(404, 'Invoice not found');
        }

        // Authorization check
        if (auth()->check()) {
            $user = auth()->user();
            $isAdmin = ($user->is_admin ?? false) || ($user->role === 'admin') || ($user->is_staff ?? false);
            if (!$isAdmin && $invoice->user_id && (int) $invoice->user_id !== (int) $user->id) {
                abort(403, 'Unauthorized submission for this invoice.');
            }
        }

        // Validate submission inputs
        $request->validate([
            'transaction_id'    => ['required', 'string', 'max:100'],
            'sender_name'       => ['required', 'string', 'max:150'],
            'bank_account_name' => ['nullable', 'string', 'max:150'],
            'sender_account'    => ['nullable', 'string', 'max:150'],
            'proof_image'       => ['nullable', 'file', 'max:5120'], // Max 5MB
            'customer_notes'    => ['nullable', 'string', 'max:1000'],
        ], [
            'transaction_id.required' => 'Please provide the Transaction ID (TRX / UTR / Reference Number) from your payment receipt.',
            'sender_name.required'    => 'Please enter the Account Name / Sender Title or mobile number used to pay.',
            'proof_image.max'         => 'The receipt file size must not exceed 5MB.',
        ]);

        // Strict Server-Side MIME Type and File Content Verification
        $storedPath = null;
        if ($request->hasFile('proof_image')) {
            $file = $request->file('proof_image');

            if (!$file->isValid()) {
                return $this->errorResponse($request, 'Uploaded receipt file is invalid or corrupted.');
            }

            // Real MIME type inspection via fileinfo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $realMime = finfo_file($finfo, $file->getRealPath());
            finfo_close($finfo);

            $allowedMimes = [
                'image/jpeg'      => 'jpg',
                'image/png'       => 'png',
                'image/webp'      => 'webp',
                'application/pdf' => 'pdf',
            ];

            if (!array_key_exists($realMime, $allowedMimes)) {
                return $this->errorResponse($request, 'Invalid receipt format. Only JPG, PNG, WEBP images or PDF files are allowed.');
            }

            // Generate cryptographically random filename to completely prevent directory traversal & PHP execution
            $extension = $allowedMimes[$realMime];
            $safeFilename = 'proof_' . $invoice->id . '_' . Str::random(32) . '.' . $extension;

            // Store securely in public disk
            $storedPath = $file->storeAs('multibank_proofs', $safeFilename, 'public');
        }

        // Sanitize string inputs to eliminate XSS
        $sanitizedTrxId     = htmlspecialchars(strip_tags(trim($request->transaction_id)), ENT_QUOTES, 'UTF-8');
        $sanitizedSender    = htmlspecialchars(strip_tags(trim($request->sender_name)), ENT_QUOTES, 'UTF-8');
        $sanitizedBankName  = $request->bank_account_name ? htmlspecialchars(strip_tags(trim($request->bank_account_name)), ENT_QUOTES, 'UTF-8') : 'Bank Transfer / Raast';
        $sanitizedSenderAcc = $request->sender_account ? htmlspecialchars(strip_tags(trim($request->sender_account)), ENT_QUOTES, 'UTF-8') : null;
        $sanitizedNotes     = $request->customer_notes ? htmlspecialchars(strip_tags(trim($request->customer_notes)), ENT_QUOTES, 'UTF-8') : null;

        // Atomic Database Transaction: transitions to Pending Verification
        $transaction = DB::transaction(function () use (
            $invoice,
            $sanitizedTrxId,
            $sanitizedSender,
            $sanitizedBankName,
            $sanitizedSenderAcc,
            $sanitizedNotes,
            $storedPath
        ) {
            $existing = MultiBankTransaction::where('invoice_id', $invoice->id)->lockForUpdate()->first();

            $updateData = [
                'user_id'           => auth()->id() ?? ($invoice->user_id ?? null),
                'bank_account_name' => $sanitizedBankName,
                'amount'            => $invoice->remaining ?? $invoice->total,
                'currency'          => $invoice->currency_code ?? ($invoice->currency ?? 'PKR'),
                'transaction_id'    => $sanitizedTrxId,
                'sender_name'       => $sanitizedSender,
                'sender_account'    => $sanitizedSenderAcc,
                'customer_notes'    => $sanitizedNotes,
                'status'            => MultiBankTransaction::STATUS_PENDING_VERIFICATION,
                'admin_notes'       => null,
                'decline_reason'    => null,
            ];

            if ($storedPath) {
                $updateData['proof_image'] = $storedPath;
            }

            return MultiBankTransaction::updateOrCreate(
                ['invoice_id' => $invoice->id],
                $updateData
            );
        });

        // Register processing transaction in Paymenter
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
            Log::info('[MultiBankQR] ExtensionHelper::addPayment notice: ' . $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Payment proof submitted successfully! Status updated to Pending Verification.',
                'transaction'  => $transaction,
                'status'       => $transaction->status,
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
        $invoiceId = (int) $invoiceId;
        $invoiceClass = '\App\Models\Invoice';
        $invoice = class_exists($invoiceClass) ? $invoiceClass::find($invoiceId) : null;

        $transaction = MultiBankTransaction::where('invoice_id', $invoiceId)->first();

        $isPaid = $invoice && in_array(strtolower($invoice->status), ['paid', 'completed', 'active']);
        $isVerified = ($transaction && $transaction->isVerified()) || $isPaid;

        $redirectUrl = function_exists('route') && \Illuminate\Support\Facades\Route::has('invoices.show')
            ? route('invoices.show', $invoiceId)
            : "/invoices/{$invoiceId}";

        return response()->json([
            'is_paid'            => $isVerified,
            'has_transaction'    => $transaction !== null,
            'status'             => $transaction ? $transaction->status : 'pending_payment',
            'status_label'       => $transaction ? $transaction->getStatusLabel() : 'Pending Payment',
            'status_badge_class' => $transaction ? $transaction->getStatusBadgeClass() : 'bg-slate-100 text-slate-800',
            'admin_notes'        => $transaction ? $transaction->admin_notes : null,
            'decline_reason'     => $transaction ? $transaction->decline_reason : null,
            'redirect_url'       => $redirectUrl,
        ]);
    }

    /**
     * Helper for returning standardized error response.
     */
    protected function errorResponse(Request $request, string $message)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return redirect()->back()->withErrors(['proof_image' => $message])->withInput();
    }
}
