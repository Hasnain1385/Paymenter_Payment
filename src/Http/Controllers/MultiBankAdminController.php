<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction;
use Paymenter\Extensions\Gateways\MultiBankQR\Services\PaymentApprovalService;

class MultiBankAdminController extends Controller
{
    /**
     * Approve transaction and mark the invoice as PAID.
     */
    public function approve(Request $request, $id)
    {
        $transaction = MultiBankTransaction::findOrFail($id);
        $adminId = auth()->id();

        $success = PaymentApprovalService::approve($transaction, $adminId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $success ? "Invoice #{$transaction->invoice_id} approved and marked as paid!" : 'Failed to approve invoice.',
            ]);
        }

        return redirect()->back()->with($success ? 'success' : 'error', $success ? "Invoice #{$transaction->invoice_id} approved!" : 'Failed to approve.');
    }

    /**
     * Decline transaction with reason.
     */
    public function decline(Request $request, $id)
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $transaction = MultiBankTransaction::findOrFail($id);
        $adminId = auth()->id();

        $success = PaymentApprovalService::decline($transaction, $request->reason, $adminId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $success,
                'message' => "Transaction #{$transaction->id} has been declined.",
            ]);
        }

        return redirect()->back()->with('success', "Transaction #{$transaction->id} declined.");
    }

    /**
     * Flag transaction with an admin note (Under Review / Action Required).
     */
    public function flag(Request $request, $id)
    {
        $request->validate([
            'note' => ['required', 'string', 'max:500'],
        ]);

        $transaction = MultiBankTransaction::findOrFail($id);
        $adminId = auth()->id();

        $success = PaymentApprovalService::flag($transaction, $request->note, $adminId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $success,
                'message' => "Transaction #{$transaction->id} flagged with note: {$request->note}",
            ]);
        }

        return redirect()->back()->with('success', "Transaction #{$transaction->id} updated with note.");
    }
}
