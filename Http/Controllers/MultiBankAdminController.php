<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction;
use Paymenter\Extensions\Gateways\MultiBankQR\Services\PaymentApprovalService;

class MultiBankAdminController extends Controller
{
    /**
     * Verify that the current user is an authenticated administrator.
     */
    protected function authorizeAdmin(): void
    {
        if (!auth()->check()) {
            abort(401, 'Unauthenticated');
        }

        $user = auth()->user();
        $isAdmin = ($user->is_admin ?? false)
            || ($user->role === 'admin')
            || ($user->is_staff ?? false)
            || (method_exists($user, 'can') && $user->can('manage-invoices'));

        if (!$isAdmin) {
            abort(403, 'Forbidden: Administrative privilege required.');
        }
    }

    /**
     * Approve transaction manually and mark the invoice as PAID.
     * Status transition: Pending Verification -> Verified.
     */
    public function approve(Request $request, $id)
    {
        $this->authorizeAdmin();

        $id = (int) $id;
        $transaction = MultiBankTransaction::findOrFail($id);
        $adminId = auth()->id();

        $success = PaymentApprovalService::approve($transaction, $adminId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $success ? "Invoice #{$transaction->invoice_id} manually verified and marked as PAID!" : 'Failed to approve invoice.',
            ]);
        }

        return redirect()->back()->with($success ? 'success' : 'error', $success ? "Invoice #{$transaction->invoice_id} verified!" : 'Failed to approve.');
    }

    /**
     * Decline transaction with reason.
     */
    public function decline(Request $request, $id)
    {
        $this->authorizeAdmin();

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $id = (int) $id;
        $transaction = MultiBankTransaction::findOrFail($id);
        $adminId = auth()->id();

        $sanitizedReason = $request->reason ? htmlspecialchars(strip_tags(trim($request->reason)), ENT_QUOTES, 'UTF-8') : null;

        $success = PaymentApprovalService::decline($transaction, $sanitizedReason, $adminId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $success,
                'message' => "Transaction #{$transaction->id} has been declined.",
            ]);
        }

        return redirect()->back()->with('success', "Transaction #{$transaction->id} declined.");
    }

    /**
     * Flag transaction with an admin note (Action Required / Under Review).
     */
    public function flag(Request $request, $id)
    {
        $this->authorizeAdmin();

        $request->validate([
            'note' => ['required', 'string', 'max:500'],
        ]);

        $id = (int) $id;
        $transaction = MultiBankTransaction::findOrFail($id);
        $adminId = auth()->id();

        $sanitizedNote = htmlspecialchars(strip_tags(trim($request->note)), ENT_QUOTES, 'UTF-8');

        $success = PaymentApprovalService::flag($transaction, $sanitizedNote, $adminId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $success,
                'message' => "Transaction #{$transaction->id} flagged: {$sanitizedNote}",
            ]);
        }

        return redirect()->back()->with('success', "Transaction #{$transaction->id} updated with note.");
    }
}
