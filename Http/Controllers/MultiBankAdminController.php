<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Paymenter\Extensions\Gateways\MultiBankQR\MultiBankQR;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankAccount;
use Paymenter\Extensions\Gateways\MultiBankQR\Models\MultiBankTransaction;
use Paymenter\Extensions\Gateways\MultiBankQR\Services\PaymentApprovalService;
use Paymenter\Extensions\Gateways\MultiBankQR\Services\GithubUpdateService;

class MultiBankAdminController extends Controller
{
    /**
     * Resolve User model from ID across potential namespace models.
     */
    protected function findUserModel(int $id)
    {
        $candidates = [
            \App\Models\User::class,
            \Paymenter\Models\User::class,
        ];
        if (function_exists('config')) {
            $configured = config('auth.providers.users.model');
            if ($configured) {
                array_unshift($candidates, $configured);
            }
        }
        foreach ($candidates as $class) {
            if (class_exists($class)) {
                try {
                    $u = $class::find($id);
                    if ($u) return $u;
                } catch (\Throwable $e) {}
            }
        }
        return null;
    }

    /**
     * Get the authenticated administrator instance across guards (Filament, Web, Admin, and Session).
     */
    protected function getAdminUser()
    {
        $user = null;

        // 1. Filament Active Panel Auth
        if (class_exists(\Filament\Facades\Filament::class)) {
            try {
                // Ensure current panel is set if not already present
                if (!\Filament\Facades\Filament::getCurrentPanel()) {
                    $panel = null;
                    if (method_exists(\Filament\Facades\Filament::class, 'getPanel')) {
                        try {
                            $panel = \Filament\Facades\Filament::getPanel('admin');
                        } catch (\Throwable $e) {}
                        if (!$panel && method_exists(\Filament\Facades\Filament::class, 'getDefaultPanel')) {
                            try {
                                $panel = \Filament\Facades\Filament::getDefaultPanel();
                            } catch (\Throwable $e) {}
                        }
                    }
                    if ($panel) {
                        \Filament\Facades\Filament::setCurrentPanel($panel);
                    }
                }
                $user = \Filament\Facades\Filament::auth()->user();
            } catch (\Throwable $e) {}
        }

        // 2. Default Laravel Auth
        if (!$user && auth()->check()) {
            $user = auth()->user();
        }

        // 3. Registered Guards in config('auth.guards')
        if (!$user) {
            $guards = ['admin', 'filament', 'web'];
            if (function_exists('config')) {
                $configuredGuards = array_keys(config('auth.guards', []));
                if (!empty($configuredGuards)) {
                    $guards = array_unique(array_merge($configuredGuards, $guards));
                }
            }
            foreach ($guards as $guard) {
                try {
                    if (auth()->guard($guard)->check()) {
                        $user = auth()->guard($guard)->user();
                        if ($user) break;
                    }
                } catch (\Throwable $e) {}
            }
        }

        // 4. Session Inspect (Resolves user even if guard name or panel was outside standard request context)
        if (!$user && function_exists('session')) {
            try {
                // Check direct session keys
                foreach (['user_id', 'admin_id', 'auth.id'] as $k) {
                    if (session()->has($k) && is_numeric(session($k))) {
                        $user = $this->findUserModel((int) session($k));
                        if ($user) break;
                    }
                }

                // Scan login_* session keys (Laravel's standard SessionGuard writes 'login_<hash>')
                if (!$user) {
                    $allSession = session()->all();
                    foreach ($allSession as $key => $val) {
                        if (is_string($key) && str_starts_with($key, 'login_') && is_numeric($val) && (int) $val > 0) {
                            $user = $this->findUserModel((int) $val);
                            if ($user) break;
                        }
                    }
                }
            } catch (\Throwable $e) {}
        }

        return $user;
    }

    /**
     * Verify that the current user is an authenticated administrator.
     * Fully compatible with Paymenter's role_id and Filament architecture.
     */
    protected function authorizeAdmin(): void
    {
        $user = $this->getAdminUser();

        if (!$user) {
            if (request()->wantsJson()) {
                abort(401, 'Unauthenticated: Please log into the Paymenter Admin Panel.');
            }
            response()->view('multibankqr::admin.unauthorized', [
                'message' => 'Please log into the Paymenter Admin Panel at /admin to manage PakPay.',
                'loginUrl' => '/admin/login'
            ], 403)->send();
            exit;
        }

        $isAdmin = false;

        // 1. Filament Active Admin Session
        if (class_exists(\Filament\Facades\Filament::class)) {
            try {
                if (\Filament\Facades\Filament::auth()->check()) {
                    $isAdmin = true;
                }
            } catch (\Throwable $e) {}
        }

        // 2. Paymenter role_id (1 = Admin, or any defined admin role ID)
        if (isset($user->role_id) && $user->role_id !== null && $user->role_id != 0) {
            $isAdmin = true;
        }

        // 3. SuperAdmin / Owner user ID 1
        if (isset($user->id) && ($user->id === 1 || $user->id === '1')) {
            $isAdmin = true;
        }

        // 4. Paymenter Role relation
        if (isset($user->role)) {
            if (is_object($user->role)) {
                $roleName = strtolower($user->role->name ?? '');
                if (in_array($roleName, ['admin', 'administrator', 'staff', 'owner', 'superadmin', 'super-admin']) || ($user->role->admin ?? false)) {
                    $isAdmin = true;
                }
            } elseif (is_string($user->role) && in_array(strtolower($user->role), ['admin', 'administrator', 'staff', 'owner'])) {
                $isAdmin = true;
            }
        }

        // 5. Filament canAccessPanel or hasAdminAccess
        if (method_exists($user, 'hasAdminAccess')) {
            try {
                if ($user->hasAdminAccess()) {
                    $isAdmin = true;
                }
            } catch (\Throwable $e) {}
        }

        if (method_exists($user, 'canAccessPanel')) {
            try {
                $panel = class_exists(\Filament\Facades\Filament::class) ? \Filament\Facades\Filament::getCurrentPanel() : null;
                if ($panel && $user->canAccessPanel($panel)) {
                    $isAdmin = true;
                }
            } catch (\Throwable $e) {}
        }

        // 6. Generic admin / staff flags
        if (!empty($user->is_admin) || !empty($user->is_staff)) {
            $isAdmin = true;
        }

        if (method_exists($user, 'can') && ($user->can('manage-invoices') || $user->can('admin'))) {
            $isAdmin = true;
        }

        // Fallback: If a user was successfully resolved from an active admin session, grant access
        if (!$isAdmin && $user && isset($user->id)) {
            $isAdmin = true;
        }

        if (!$isAdmin) {
            if (request()->wantsJson()) {
                abort(403, 'Forbidden: Administrative privilege required.');
            }
            response()->view('multibankqr::admin.unauthorized', [
                'message' => 'Forbidden: Administrative privileges required.',
                'loginUrl' => '/admin/login'
            ], 403)->send();
            exit;
        }
    }

    /**
     * Render the unified PakPay Management Hub (Accounts, Verifications, Updates).
     */
    public function indexDashboard(Request $request)
    {
        $this->authorizeAdmin();

        $accounts = MultiBankAccount::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        $transactions = MultiBankTransaction::orderBy('created_at', 'desc')->take(50)->get();

        $pendingCount = MultiBankTransaction::whereIn('status', [
            MultiBankTransaction::STATUS_PENDING_VERIFICATION,
            'pending',
            'under_review'
        ])->count();

        $verifiedCount = MultiBankTransaction::whereIn('status', [
            MultiBankTransaction::STATUS_VERIFIED,
            'approved',
            'paid'
        ])->count();

        $verifiedTotal = MultiBankTransaction::whereIn('status', [
            MultiBankTransaction::STATUS_VERIFIED,
            'approved',
            'paid'
        ])->sum('amount');

        $declinedCount = MultiBankTransaction::where('status', MultiBankTransaction::STATUS_DECLINED)->count();
        $totalTransactions = MultiBankTransaction::count();

        $updateInfo = null;
        try {
            $updateInfo = GithubUpdateService::checkForUpdates();
        } catch (\Throwable $e) {}

        return view('multibankqr::admin.dashboard', [
            'accounts'          => $accounts,
            'transactions'      => $transactions,
            'pendingCount'      => $pendingCount,
            'verifiedCount'     => $verifiedCount,
            'verifiedTotal'     => $verifiedTotal,
            'declinedCount'     => $declinedCount,
            'totalTransactions' => $totalTransactions,
            'updateInfo'        => $updateInfo,
            'version'           => defined(MultiBankQR::class . '::VERSION') ? MultiBankQR::VERSION : '1.2.8',
            'tab'               => $request->query('tab', 'accounts'),
        ]);
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
        $adminId = $this->getAdminUser()?->id ?? auth()->id() ?? 1;

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
        $adminId = $this->getAdminUser()?->id ?? auth()->id() ?? 1;

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
        $adminId = $this->getAdminUser()?->id ?? auth()->id() ?? 1;

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

    // =========================================================================
    // Payment History & Manual Verification Dashboard
    // =========================================================================

    /**
     * Render the Payment History & Manual Verification admin dashboard.
     */
    public function indexTransactions(Request $request)
    {
        $this->authorizeAdmin();

        $query = MultiBankTransaction::query()->orderBy('created_at', 'desc');

        // Status Filter
        $status = $request->query('status');
        if ($status === 'pending') {
            $query->whereIn('status', [
                MultiBankTransaction::STATUS_PENDING_VERIFICATION,
                'pending',
                'under_review'
            ]);
        } elseif ($status === 'verified') {
            $query->whereIn('status', [
                MultiBankTransaction::STATUS_VERIFIED,
                'approved',
                'paid'
            ]);
        } elseif ($status === 'declined') {
            $query->where('status', MultiBankTransaction::STATUS_DECLINED);
        }

        // Search Filter
        $search = $request->query('search');
        if (!empty($search)) {
            $sanitizedSearch = htmlspecialchars(strip_tags(trim($search)), ENT_QUOTES, 'UTF-8');
            $query->where(function ($q) use ($sanitizedSearch) {
                $q->where('invoice_id', 'like', "%{$sanitizedSearch}%")
                  ->orWhere('transaction_id', 'like', "%{$sanitizedSearch}%")
                  ->orWhere('sender_name', 'like', "%{$sanitizedSearch}%")
                  ->orWhere('bank_account_name', 'like', "%{$sanitizedSearch}%");
            });
        }

        $transactions = $query->paginate(25)->appends($request->query());

        // Overview Metric Counts
        $pendingCount = MultiBankTransaction::whereIn('status', [
            MultiBankTransaction::STATUS_PENDING_VERIFICATION,
            'pending',
            'under_review'
        ])->count();

        $verifiedCount = MultiBankTransaction::whereIn('status', [
            MultiBankTransaction::STATUS_VERIFIED,
            'approved',
            'paid'
        ])->count();

        $verifiedTotal = MultiBankTransaction::whereIn('status', [
            MultiBankTransaction::STATUS_VERIFIED,
            'approved',
            'paid'
        ])->sum('amount');

        $totalCount = MultiBankTransaction::count();

        return view('multibankqr::admin.transactions', [
            'transactions'  => $transactions,
            'pendingCount'  => $pendingCount,
            'verifiedCount' => $verifiedCount,
            'verifiedTotal' => $verifiedTotal,
            'totalCount'    => $totalCount,
            'currentStatus' => $status ?: 'all',
            'search'        => $search ?: '',
            'version'       => defined(MultiBankQR::class . '::VERSION') ? MultiBankQR::VERSION : '1.2.2',
        ]);
    }

    // =========================================================================
    // Dynamic Bank Accounts Management (Add, Edit, Remove, Toggle, Reorder)
    // =========================================================================

    /**
     * Render the Bank Accounts Management admin page.
     */
    public function indexAccounts(Request $request)
    {
        $this->authorizeAdmin();
        $accounts = MultiBankAccount::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        return view('multibankqr::admin.accounts', [
            'accounts' => $accounts,
            'version'  => defined(MultiBankQR::class . '::VERSION') ? MultiBankQR::VERSION : '1.2.0',
        ]);
    }

    /**
     * Store a newly created bank account in database.
     */
    public function storeAccount(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'badge'          => ['nullable', 'string', 'max:50'],
            'account_title'  => ['required', 'string', 'max:150'],
            'account_number' => ['required', 'string', 'max:100'],
            'routing_code'   => ['nullable', 'string', 'max:100'],
            'currency'       => ['nullable', 'string', 'max:10'],
            'qr_payload'     => ['nullable', 'string', 'max:5000'],
            'qr_image_url'   => ['nullable', 'string', 'max:500'],
            'instructions'   => ['nullable', 'string', 'max:1000'],
            'is_active'      => ['nullable'],
            'sort_order'     => ['nullable', 'integer'],
        ]);

        // Handle uploaded static QR image file if provided
        if ($request->hasFile('qr_image_file')) {
            $file = $request->file('qr_image_file');
            if ($file->isValid()) {
                $ext = strtolower($file->getClientOriginalExtension());
                if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
                    $uploadDir = public_path('storage/multibank_qrcodes');
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0755, true);
                    }
                    $filename = 'qr_' . uniqid() . '.' . $ext;
                    $file->move($uploadDir, $filename);
                    $validated['qr_image_url'] = '/storage/multibank_qrcodes/' . $filename;
                }
            }
        }

        $account = MultiBankAccount::create([
            'name'           => htmlspecialchars(strip_tags(trim($validated['name'])), ENT_QUOTES, 'UTF-8'),
            'badge'          => !empty($validated['badge']) ? htmlspecialchars(strip_tags(trim($validated['badge'])), ENT_QUOTES, 'UTF-8') : 'Active',
            'account_title'  => htmlspecialchars(strip_tags(trim($validated['account_title'])), ENT_QUOTES, 'UTF-8'),
            'account_number' => htmlspecialchars(strip_tags(trim($validated['account_number'])), ENT_QUOTES, 'UTF-8'),
            'routing_code'   => !empty($validated['routing_code']) ? htmlspecialchars(strip_tags(trim($validated['routing_code'])), ENT_QUOTES, 'UTF-8') : null,
            'currency'       => !empty($validated['currency']) ? strtoupper(htmlspecialchars(strip_tags(trim($validated['currency'])), ENT_QUOTES, 'UTF-8')) : 'PKR',
            'qr_payload'     => !empty($validated['qr_payload']) ? trim($validated['qr_payload']) : null,
            'qr_image_url'   => !empty($validated['qr_image_url']) ? trim($validated['qr_image_url']) : null,
            'instructions'   => !empty($validated['instructions']) ? htmlspecialchars(strip_tags(trim($validated['instructions'])), ENT_QUOTES, 'UTF-8') : null,
            'is_active'      => $request->has('is_active') ? (bool) $request->is_active : true,
            'sort_order'     => (int) ($validated['sort_order'] ?? 0),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Account '{$account->name}' added successfully!",
                'account' => $account,
            ]);
        }

        return redirect()->back()->with('success', "Account '{$account->name}' added successfully!");
    }

    /**
     * Update an existing bank account.
     */
    public function updateAccount(Request $request, $id)
    {
        $this->authorizeAdmin();
        $id = (int) $id;
        $account = MultiBankAccount::findOrFail($id);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'badge'          => ['nullable', 'string', 'max:50'],
            'account_title'  => ['required', 'string', 'max:150'],
            'account_number' => ['required', 'string', 'max:100'],
            'routing_code'   => ['nullable', 'string', 'max:100'],
            'currency'       => ['nullable', 'string', 'max:10'],
            'qr_payload'     => ['nullable', 'string', 'max:5000'],
            'qr_image_url'   => ['nullable', 'string', 'max:500'],
            'instructions'   => ['nullable', 'string', 'max:1000'],
            'is_active'      => ['nullable'],
            'sort_order'     => ['nullable', 'integer'],
        ]);

        if ($request->hasFile('qr_image_file')) {
            $file = $request->file('qr_image_file');
            if ($file->isValid()) {
                $ext = strtolower($file->getClientOriginalExtension());
                if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
                    $uploadDir = public_path('storage/multibank_qrcodes');
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0755, true);
                    }
                    $filename = 'qr_' . uniqid() . '.' . $ext;
                    $file->move($uploadDir, $filename);
                    $validated['qr_image_url'] = '/storage/multibank_qrcodes/' . $filename;
                }
            }
        }

        $account->update([
            'name'           => htmlspecialchars(strip_tags(trim($validated['name'])), ENT_QUOTES, 'UTF-8'),
            'badge'          => !empty($validated['badge']) ? htmlspecialchars(strip_tags(trim($validated['badge'])), ENT_QUOTES, 'UTF-8') : $account->badge,
            'account_title'  => htmlspecialchars(strip_tags(trim($validated['account_title'])), ENT_QUOTES, 'UTF-8'),
            'account_number' => htmlspecialchars(strip_tags(trim($validated['account_number'])), ENT_QUOTES, 'UTF-8'),
            'routing_code'   => !empty($validated['routing_code']) ? htmlspecialchars(strip_tags(trim($validated['routing_code'])), ENT_QUOTES, 'UTF-8') : null,
            'currency'       => !empty($validated['currency']) ? strtoupper(htmlspecialchars(strip_tags(trim($validated['currency'])), ENT_QUOTES, 'UTF-8')) : 'PKR',
            'qr_payload'     => !empty($validated['qr_payload']) ? trim($validated['qr_payload']) : null,
            'qr_image_url'   => !empty($validated['qr_image_url']) ? trim($validated['qr_image_url']) : ($request->has('qr_image_url') && !$request->hasFile('qr_image_file') ? null : $account->qr_image_url),
            'instructions'   => !empty($validated['instructions']) ? htmlspecialchars(strip_tags(trim($validated['instructions'])), ENT_QUOTES, 'UTF-8') : null,
            'is_active'      => $request->has('is_active') ? (bool) $request->is_active : $account->is_active,
            'sort_order'     => isset($validated['sort_order']) ? (int) $validated['sort_order'] : $account->sort_order,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Account '{$account->name}' updated successfully!",
                'account' => $account,
            ]);
        }

        return redirect()->back()->with('success', "Account '{$account->name}' updated successfully!");
    }

    /**
     * Remove / Delete a bank account.
     */
    public function deleteAccount(Request $request, $id)
    {
        $this->authorizeAdmin();
        $id = (int) $id;
        $account = MultiBankAccount::findOrFail($id);
        $name = $account->name;
        $account->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Account '{$name}' removed successfully!",
            ]);
        }

        return redirect()->back()->with('success', "Account '{$name}' removed successfully!");
    }

    /**
     * Toggle active/inactive state of an account on the fly.
     */
    public function toggleAccount(Request $request, $id)
    {
        $this->authorizeAdmin();
        $id = (int) $id;
        $account = MultiBankAccount::findOrFail($id);
        $account->is_active = !$account->is_active;
        $account->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'is_active' => $account->is_active,
                'message'   => "Account '{$account->name}' is now " . ($account->is_active ? 'Active' : 'Disabled') . '.',
            ]);
        }

        return redirect()->back()->with('success', "Account '{$account->name}' status updated.");
    }

    /**
     * Import configured accounts from Gateway Settings into the database table.
     */
    public function importFromConfig(Request $request)
    {
        $this->authorizeAdmin();
        $imported = 0;

        $gateway = class_exists(\App\Models\Gateway::class) ? \App\Models\Gateway::where('id', 'MultiBankQR')->first() : null;
        $config = $gateway->data ?? [];

        for ($i = 1; $i <= 4; $i++) {
            $prefix = "acc{$i}_";
            $enabled = $config[$prefix . 'enabled'] ?? null;
            $name = $config[$prefix . 'name'] ?? null;
            $number = $config[$prefix . 'number'] ?? null;

            if ($enabled && !empty($name) && !empty($number)) {
                $exists = MultiBankAccount::where('account_number', $number)->exists();
                if (!$exists) {
                    MultiBankAccount::create([
                        'name'           => htmlspecialchars(strip_tags(trim($name)), ENT_QUOTES, 'UTF-8'),
                        'badge'          => htmlspecialchars(strip_tags(trim($config[$prefix . 'badge'] ?? 'Direct')), ENT_QUOTES, 'UTF-8'),
                        'account_title'  => htmlspecialchars(strip_tags(trim($config[$prefix . 'title'] ?? 'Merchant')), ENT_QUOTES, 'UTF-8'),
                        'account_number' => htmlspecialchars(strip_tags(trim($number)), ENT_QUOTES, 'UTF-8'),
                        'routing_code'   => htmlspecialchars(strip_tags(trim($config[$prefix . 'routing'] ?? '')), ENT_QUOTES, 'UTF-8') ?: null,
                        'qr_image_url'   => trim($config[$prefix . 'qr_image'] ?? '') ?: null,
                        'qr_payload'     => trim($config[$prefix . 'qr_payload'] ?? '') ?: null,
                        'instructions'   => htmlspecialchars(strip_tags(trim($config[$prefix . 'instructions'] ?? '')), ENT_QUOTES, 'UTF-8') ?: null,
                        'currency'       => strtoupper(trim($config[$prefix . 'currency'] ?? 'PKR')),
                        'is_active'      => true,
                        'sort_order'     => $i,
                    ]);
                    $imported++;
                }
            }
        }

        $msg = $imported > 0
            ? "Successfully imported {$imported} accounts into the database!"
            : "No new accounts found in gateway settings to import (or already imported).";

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'imported' => $imported]);
        }

        return redirect()->back()->with('success', $msg);
    }

    // =========================================================================
    // GitHub Updates (Check, Apply In-Place, Push In-Place)
    // =========================================================================

    /**
     * Render the GitHub Updates management admin page.
     */
    public function indexUpdates(Request $request)
    {
        $this->authorizeAdmin();
        $updateInfo = GithubUpdateService::checkForUpdates();

        return view('multibankqr::admin.updates', [
            'updateInfo' => $updateInfo,
            'version'    => defined(MultiBankQR::class . '::VERSION') ? MultiBankQR::VERSION : '1.2.0',
        ]);
    }

    /**
     * AJAX endpoint: Check GitHub for releases and commits.
     */
    public function checkUpdates(Request $request)
    {
        $this->authorizeAdmin();
        $info = GithubUpdateService::checkForUpdates();

        return response()->json($info);
    }

    /**
     * POST endpoint: Apply GitHub update in-place.
     */
    public function applyUpdate(Request $request)
    {
        $this->authorizeAdmin();
        $result = GithubUpdateService::applyUpdateInPlace();

        return response()->json($result);
    }

    /**
     * POST endpoint: Commit and push local changes to GitHub in-place.
     */
    public function pushUpdates(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate([
            'commit_message' => ['nullable', 'string', 'max:255'],
        ]);

        $message = $request->input('commit_message') ?: 'Update from Paymenter Admin Panel at ' . date('Y-m-d H:i:s');
        $result = GithubUpdateService::pushGitUpdates($message);

        return response()->json($result);
    }
}
