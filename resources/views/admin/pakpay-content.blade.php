<!-- PakPay Unified Management Hub Partial -->
<div class="space-y-6" id="pakpay-hub-container">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Top Brand & Stat Banner -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-800/80">
            <div class="flex items-center gap-3.5">
                <img src="https://raw.githubusercontent.com/Hasnain1385/Paymenter_Payment/main/logo.png" alt="PakPay" class="w-12 h-12 rounded-xl shadow-lg shrink-0">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-white tracking-tight">PakPay Gateway Management</h1>
                        <span class="px-2.5 py-0.5 text-xs font-semibold bg-emerald-950/80 text-emerald-400 border border-emerald-700/60 rounded-full">v{{ $version }}</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Manage bank accounts & scanned QRs, verify customer payments, and check GitHub updates</p>
                </div>
            </div>

            <!-- Global Action Links -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="/admin/extensions" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Gateway Settings</span>
                </a>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 pt-5">
            <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-3.5">
                <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Total Accounts</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold text-white">{{ count($accounts) }}</span>
                    <span class="text-xs text-emerald-400 font-medium">({{ $accounts->where('is_active', true)->count() }} active)</span>
                </div>
            </div>

            <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-3.5">
                <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Pending Verifications</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold {{ $pendingCount > 0 ? 'text-amber-400 animate-pulse' : 'text-white' }}">{{ $pendingCount }}</span>
                    @if($pendingCount > 0)
                        <span class="text-[11px] px-1.5 py-0.5 rounded bg-amber-950/80 text-amber-300 border border-amber-700/50 font-bold">Action Needed</span>
                    @endif
                </div>
            </div>

            <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-3.5">
                <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Verified Volume</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold text-emerald-400">{{ number_format($verifiedTotal, 0) }}</span>
                    <span class="text-xs text-slate-400 font-medium">PKR</span>
                </div>
            </div>

            <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-3.5">
                <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Verified Count</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold text-indigo-400">{{ $verifiedCount }}</span>
                    <span class="text-xs text-slate-400 font-medium">payments</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert / Toast Banner -->
    <div id="pakpayAlertBox" class="hidden p-4 rounded-xl text-sm font-medium transition-all duration-300"></div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
        <button type="button" onclick="switchPakPayTab('accounts')" id="tab-btn-accounts" class="tab-btn inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg transition bg-emerald-600 text-white shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <span>Bank Accounts & QRs</span>
            <span class="px-1.5 py-0.2 rounded bg-black/30 text-[11px] font-bold">{{ count($accounts) }}</span>
        </button>

        <button type="button" onclick="switchPakPayTab('verifications')" id="tab-btn-verifications" class="tab-btn inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg transition text-slate-400 hover:text-white hover:bg-slate-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            <span>Payment Verifications</span>
            @if($pendingCount > 0)
                <span class="px-1.5 py-0.2 rounded bg-amber-500 text-black text-[11px] font-bold">{{ $pendingCount }}</span>
            @endif
        </button>

        <button type="button" onclick="switchPakPayTab('updates')" id="tab-btn-updates" class="tab-btn inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg transition text-slate-400 hover:text-white hover:bg-slate-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>GitHub Updates & Sync</span>
        </button>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 1: BANK ACCOUNTS MANAGEMENT -->
    <!-- ===================================================================== -->
    <div id="tab-content-accounts" class="space-y-4">
        <!-- Section Header Bar with Prominent "Add Bank Account" Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-900/60 border border-slate-800/80 p-4 rounded-xl">
            <div>
                <h2 class="text-sm font-bold text-white">Merchant Bank Accounts & Mobile Wallets</h2>
                <p class="text-xs text-slate-400">Configure Pakistani bank accounts, Raast ID, Easypaisa, or JazzCash with scanned static QR images</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="document.getElementById('importSettingsModal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700 transition">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import from Settings</span>
                </button>

                <!-- REQUESTED BUTTON 1: Add a Bank Account -->
                <button type="button" onclick="openAddAccountModal()" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg shadow-lg shadow-emerald-950 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>➕ Add Bank Account</span>
                </button>
            </div>
        </div>

        <!-- Accounts Grid -->
        @if(count($accounts) === 0)
            <div class="bg-slate-900/60 border border-dashed border-slate-800 rounded-2xl p-12 text-center space-y-4">
                <div class="mx-auto w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center text-slate-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">No Bank Accounts Configured</h3>
                    <p class="text-xs text-slate-400 max-w-md mx-auto mt-1">Add your first bank account or mobile wallet so customers can transfer funds and view your scanned QR code.</p>
                </div>
                <div class="flex items-center justify-center gap-2 pt-2">
                    <button type="button" onclick="openAddAccountModal()" class="px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg shadow transition">
                        ➕ Add Bank Account
                    </button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($accounts as $acc)
                    <div id="account-card-{{ $acc->id }}" class="bg-slate-900 border {{ $acc->is_active ? 'border-slate-800' : 'border-slate-800/60 opacity-60' }} rounded-xl p-5 flex flex-col justify-between space-y-4 hover:border-slate-700 transition">
                        <div>
                            <!-- Header with Badge and Toggle -->
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div>
                                    <span class="inline-block px-2 py-0.5 text-[11px] font-bold rounded-md bg-emerald-950 text-emerald-400 border border-emerald-800/50 mb-1">
                                        {{ $acc->badge ?: 'Active' }}
                                    </span>
                                    <h3 class="text-base font-bold text-white leading-snug">{{ $acc->name }}</h3>
                                </div>
                                <button type="button" onclick="togglePakPayAccount({{ $acc->id }})" title="Toggle Active Status" class="p-1.5 rounded-lg border transition {{ $acc->is_active ? 'bg-emerald-950/80 border-emerald-700/60 text-emerald-400' : 'bg-slate-800 border-slate-700 text-slate-500' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </div>

                            <!-- Account Details Box -->
                            <div class="bg-slate-950/60 rounded-lg p-3 space-y-2 border border-slate-800/80 text-xs">
                                <div>
                                    <span class="text-slate-500 block text-[10px] uppercase font-semibold">Account Title</span>
                                    <span class="font-medium text-slate-200">{{ $acc->account_title }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block text-[10px] uppercase font-semibold">Account Number / IBAN / Raast</span>
                                    <span class="font-mono font-semibold text-emerald-400 select-all">{{ $acc->account_number }}</span>
                                </div>
                                @if($acc->routing_code)
                                    <div>
                                        <span class="text-slate-500 block text-[10px] uppercase font-semibold">Branch / Bank Code</span>
                                        <span class="text-slate-300">{{ $acc->routing_code }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between pt-1 border-t border-slate-800/80 text-[11px]">
                                    <span class="text-slate-500">Currency: <strong class="text-slate-300">{{ $acc->currency }}</strong></span>
                                    <span class="text-slate-500">Sort: <strong class="text-slate-300">{{ $acc->sort_order }}</strong></span>
                                </div>
                            </div>

                            <!-- Scanned QR Preview -->
                            @if($acc->qr_image_url)
                                <div class="mt-3 flex items-center gap-2 p-2 rounded-lg bg-slate-950/40 border border-slate-800/60">
                                    <img src="{{ $acc->qr_image_url }}" alt="QR" class="w-10 h-10 object-contain rounded bg-white p-0.5" onerror="this.src='https://placehold.co/40x40?text=QR'">
                                    <div class="overflow-hidden">
                                        <span class="text-[10px] uppercase text-slate-500 font-bold block">Scanned QR Attached</span>
                                        <span class="text-[11px] text-slate-400 truncate block">{{ basename($acc->qr_image_url) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Card Action Buttons (Edit / Delete) -->
                        <div class="flex items-center justify-between gap-2 pt-3 border-t border-slate-800/80">
                            <button type="button" onclick='openEditAccountModal(@json($acc))' class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg border border-slate-700 transition">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit</span>
                            </button>

                            <!-- REQUESTED BUTTON 2: Delete on each added account -->
                            <button type="button" onclick="deletePakPayAccount({{ $acc->id }}, '{{ addslashes($acc->name) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-bold bg-red-950/70 hover:bg-red-900 border border-red-700/60 text-red-300 rounded-lg transition" title="Delete Account">
                                <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Delete</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 2: PAYMENT VERIFICATIONS & HISTORY -->
    <!-- ===================================================================== -->
    <div id="tab-content-verifications" class="hidden space-y-4">
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-xl">
            <div class="p-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-white">Submitted Transactions & Receipts</h2>
                    <p class="text-xs text-slate-400">Review customer proof submissions and approve invoices in one click</p>
                </div>
            </div>

            <!-- Table of Transactions -->
            @if(count($transactions) === 0)
                <div class="p-12 text-center space-y-3">
                    <p class="text-sm text-slate-400">No transactions recorded yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-950/80 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                                <th class="p-3">Invoice</th>
                                <th class="p-3">Customer / Sender</th>
                                <th class="p-3">Amount</th>
                                <th class="p-3">Bank Account</th>
                                <th class="p-3">Transaction ID</th>
                                <th class="p-3">Receipt</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Submitted</th>
                                <th class="p-3 text-right">Verification Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            @foreach($transactions as $trx)
                                <tr id="trx-row-{{ $trx->id }}" class="hover:bg-slate-800/40 transition">
                                    <td class="p-3 font-semibold text-white">#{{ $trx->invoice_id }}</td>
                                    <td class="p-3 text-slate-300">{{ $trx->sender_name ?: 'Customer' }}</td>
                                    <td class="p-3 font-bold text-emerald-400">{{ $trx->currency }} {{ number_format($trx->amount, 2) }}</td>
                                    <td class="p-3 text-slate-300">{{ $trx->bank_account_name ?: 'Standard' }}</td>
                                    <td class="p-3 font-mono text-slate-200 select-all">{{ $trx->transaction_id }}</td>
                                    <td class="p-3">
                                        @if($trx->proof_image_path)
                                            <button type="button" onclick="previewReceiptImage('{{ $trx->proof_image_path }}', 'Invoice #{{ $trx->invoice_id }} ({{ $trx->transaction_id }})')" class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-400 hover:text-indigo-300 bg-indigo-950/60 px-2 py-1 rounded border border-indigo-800/40">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>View</span>
                                            </button>
                                        @else
                                            <span class="text-slate-600 text-[11px]">No Receipt</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <span id="trx-status-{{ $trx->id }}" class="inline-block px-2 py-0.5 text-[10px] font-bold rounded-md
                                            {{ in_array($trx->status, ['verified', 'approved', 'paid']) ? 'bg-emerald-950 text-emerald-400 border border-emerald-800/60' : (in_array($trx->status, ['declined', 'rejected']) ? 'bg-red-950 text-red-400 border border-red-800/60' : 'bg-amber-950 text-amber-300 border border-amber-800/60') }}">
                                            {{ ucfirst(str_replace('_', ' ', $trx->status)) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-slate-400 text-[11px] whitespace-nowrap">{{ $trx->created_at ? $trx->created_at->format('M d, H:i') : 'Recently' }}</td>
                                    <td class="p-3 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            @if(!in_array($trx->status, ['verified', 'approved', 'paid']))
                                                <button type="button" onclick="verifyTransaction({{ $trx->id }}, 'approve')" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded transition shadow">
                                                    <span>✅ Approve</span>
                                                </button>
                                            @endif
                                            @if(!in_array($trx->status, ['declined', 'rejected']))
                                                <button type="button" onclick="verifyTransaction({{ $trx->id }}, 'decline')" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold bg-red-950/70 hover:bg-red-900 border border-red-700/60 text-red-300 rounded transition">
                                                    <span>❌ Decline</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 3: GITHUB UPDATES & SYNC -->
    <!-- ===================================================================== -->
    <div id="tab-content-updates" class="hidden space-y-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
                <div>
                    <h2 class="text-base font-bold text-white">In-Place GitHub Repository Updates</h2>
                    <p class="text-xs text-slate-400">Hardcoded repository: <code class="text-indigo-400 font-mono">https://github.com/Hasnain1385/Paymenter_Payment</code></p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="checkPakPayUpdates()" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Check for Updates</span>
                    </button>
                    <button type="button" onclick="applyPakPayUpdate()" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                        <span>⚡ Update In-Place</span>
                    </button>
                </div>
            </div>

            <!-- Version Status Card -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-4">
                    <span class="text-xs uppercase tracking-wider text-slate-500 font-semibold block">Installed Gateway Version</span>
                    <p class="text-xl font-bold text-white mt-1">v{{ $version }}</p>
                    <span class="text-xs text-slate-400 block mt-1">Production Release</span>
                </div>
                <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-4">
                    <span class="text-xs uppercase tracking-wider text-slate-500 font-semibold block">GitHub Remote Release</span>
                    <p id="remoteVersionLabel" class="text-xl font-bold text-indigo-400 mt-1">v{{ $updateInfo['latest_version'] ?? $version }}</p>
                    <span id="remoteStatusLabel" class="text-xs text-slate-400 block mt-1">
                        {{ !empty($updateInfo['has_update']) ? '🔔 New update available!' : '✅ Gateway is up to date' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODALS: ADD, EDIT, RECEIPT, IMPORT -->
    <!-- ===================================================================== -->

    <!-- ADD ACCOUNT MODAL -->
    <div id="addAccountModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="text-emerald-400">➕</span> Add New Bank Account
                </h3>
                <button type="button" onclick="document.getElementById('addAccountModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-lg">&times;</button>
            </div>

            <form action="{{ route('multibankqr.admin.accounts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Bank / Wallet Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Meezan Bank / Raast, Easypaisa, JazzCash" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Badge / Tag</label>
                        <input type="text" name="badge" placeholder="e.g. Instant Raast, 0% Fee, Recommended" value="Instant" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Account Holder Title *</label>
                        <input type="text" name="account_title" required placeholder="e.g. Mirza Hasnain Baig" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Account Number / IBAN / Raast *</label>
                        <input type="text" name="account_number" required placeholder="e.g. PK42MEZN... or 03001234567" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white font-mono focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Branch / Bank Code</label>
                        <input type="text" name="routing_code" placeholder="e.g. Meezan Bank Gulberg" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Currency</label>
                        <input type="text" name="currency" value="PKR" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" value="1" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Scanned Static QR Code (Image URL or File)</label>
                    <div class="space-y-2">
                        <input type="text" name="qr_image_url" placeholder="https://example.com/qr.png or /storage/..." class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                        <input type="file" name="qr_image_file" accept="image/*" class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Payment Instructions / Notes</label>
                    <input type="text" name="instructions" placeholder="e.g. Please put Invoice ID in remarks and message on WhatsApp" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" id="add_is_active_hub" value="1" checked class="w-4 h-4 rounded text-emerald-600 bg-slate-950 border-slate-800">
                    <label for="add_is_active_hub" class="text-xs text-slate-300 font-medium">Account is Active & Visible to Customers</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('addAccountModal').classList.add('hidden')" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-slate-800 rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg shadow-lg">Save Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT ACCOUNT MODAL -->
    <div id="editAccountModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="text-indigo-400">✏️</span> Edit Bank Account
                </h3>
                <button type="button" onclick="document.getElementById('editAccountModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-lg">&times;</button>
            </div>

            <form id="editAccountForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Bank / Wallet Name *</label>
                        <input type="text" id="hub_edit_name" name="name" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Badge / Tag</label>
                        <input type="text" id="hub_edit_badge" name="badge" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Account Holder Title *</label>
                        <input type="text" id="hub_edit_account_title" name="account_title" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Account Number / IBAN / Raast *</label>
                        <input type="text" id="hub_edit_account_number" name="account_number" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Branch / Bank Code</label>
                        <input type="text" id="hub_edit_routing_code" name="routing_code" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Currency</label>
                        <input type="text" id="hub_edit_currency" name="currency" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Sort Order</label>
                        <input type="number" id="hub_edit_sort_order" name="sort_order" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Scanned Static QR Code (Image URL or File)</label>
                    <div class="space-y-2">
                        <input type="text" id="hub_edit_qr_image_url" name="qr_image_url" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                        <input type="file" name="qr_image_file" accept="image/*" class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Payment Instructions / Notes</label>
                    <input type="text" id="hub_edit_instructions" name="instructions" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" id="hub_edit_is_active" value="1" class="w-4 h-4 rounded text-indigo-600 bg-slate-950 border-slate-800">
                    <label for="hub_edit_is_active" class="text-xs text-slate-300 font-medium">Account is Active & Visible</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('editAccountModal').classList.add('hidden')" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-slate-800 rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg shadow-lg">Update Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- RECEIPT PREVIEW MODAL -->
    <div id="receiptPreviewModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/85 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-5 shadow-2xl space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                <h3 id="receiptPreviewTitle" class="text-sm font-bold text-white">Payment Receipt</h3>
                <button type="button" onclick="document.getElementById('receiptPreviewModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-lg">&times;</button>
            </div>
            <div class="bg-black/60 rounded-xl p-2 flex items-center justify-center max-h-[70vh] overflow-hidden">
                <img id="receiptPreviewImage" src="" alt="Proof" class="max-h-[65vh] w-auto object-contain rounded-lg">
            </div>
            <div class="flex items-center justify-end pt-2">
                <a id="receiptDownloadLink" href="" target="_blank" download class="px-4 py-1.5 text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition">Open Full Image</a>
            </div>
        </div>
    </div>

    <!-- IMPORT FROM SETTINGS MODAL -->
    <div id="importSettingsModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white">🔄 Import from Settings</h3>
                <button type="button" onclick="document.getElementById('importSettingsModal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed">
                Import any bank accounts previously configured in Paymenter Gateway settings into the database so you can add, edit, or remove them freely.
            </p>
            <form action="{{ route('multibankqr.admin.accounts.import') }}" method="POST" class="pt-2">
                @csrf
                <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="document.getElementById('importSettingsModal').classList.add('hidden')" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-slate-800 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg">Start Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const pakPayCsrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    function switchPakPayTab(tab) {
        document.getElementById('tab-content-accounts').classList.add('hidden');
        document.getElementById('tab-content-verifications').classList.add('hidden');
        document.getElementById('tab-content-updates').classList.add('hidden');

        document.querySelectorAll('.tab-btn').forEach(b => {
            b.className = 'tab-btn inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg transition text-slate-400 hover:text-white hover:bg-slate-800';
        });

        const activeContent = document.getElementById(`tab-content-${tab}`);
        const activeBtn = document.getElementById(`tab-btn-${tab}`);

        if (activeContent) activeContent.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.className = 'tab-btn inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg transition bg-emerald-600 text-white shadow-md';
        }
    }

    function showPakPayAlert(msg, isSuccess = true) {
        const el = document.getElementById('pakPayAlertBox');
        if (!el) return;
        el.className = `p-4 rounded-xl text-sm font-medium ${isSuccess ? 'bg-emerald-950/80 border border-emerald-500/40 text-emerald-300' : 'bg-red-950/80 border border-red-500/40 text-red-300'}`;
        el.textContent = msg;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 5000);
    }

    function openAddAccountModal() {
        document.getElementById('addAccountModal').classList.remove('hidden');
    }

    function openEditAccountModal(acc) {
        document.getElementById('editAccountForm').action = `/admin/multibankqr/accounts/${acc.id}/update`;
        document.getElementById('hub_edit_name').value = acc.name || '';
        document.getElementById('hub_edit_badge').value = acc.badge || '';
        document.getElementById('hub_edit_account_title').value = acc.account_title || '';
        document.getElementById('hub_edit_account_number').value = acc.account_number || '';
        document.getElementById('hub_edit_routing_code').value = acc.routing_code || '';
        document.getElementById('hub_edit_currency').value = acc.currency || 'PKR';
        document.getElementById('hub_edit_sort_order').value = acc.sort_order || 0;
        document.getElementById('hub_edit_qr_image_url').value = acc.qr_image_url || '';
        document.getElementById('hub_edit_instructions').value = acc.instructions || '';
        document.getElementById('hub_edit_is_active').checked = !!acc.is_active;

        document.getElementById('editAccountModal').classList.remove('hidden');
    }

    async function togglePakPayAccount(id) {
        try {
            const res = await fetch(`/admin/multibankqr/accounts/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': pakPayCsrf,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                showPakPayAlert(data.message, true);
                const card = document.getElementById(`account-card-${id}`);
                if (card) {
                    if (data.is_active) {
                        card.classList.remove('opacity-60', 'border-slate-800/60');
                        card.classList.add('border-slate-800');
                    } else {
                        card.classList.add('opacity-60', 'border-slate-800/60');
                        card.classList.remove('border-slate-800');
                    }
                }
            }
        } catch (e) {
            showPakPayAlert('Failed to toggle status: ' + e.message, false);
        }
    }

    async function deletePakPayAccount(id, name) {
        if (!confirm(`Are you sure you want to remove the bank account "${name}"?`)) {
            return;
        }

        try {
            const res = await fetch(`/admin/multibankqr/accounts/${id}/delete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': pakPayCsrf,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                showPakPayAlert(data.message, true);
                const card = document.getElementById(`account-card-${id}`);
                if (card) card.remove();
            } else {
                showPakPayAlert(data.message || 'Failed to remove account', false);
            }
        } catch (e) {
            showPakPayAlert('Failed to remove account: ' + e.message, false);
        }
    }

    function previewReceiptImage(url, title) {
        document.getElementById('receiptPreviewImage').src = url;
        document.getElementById('receiptDownloadLink').href = url;
        document.getElementById('receiptPreviewTitle').textContent = title || 'Payment Receipt';
        document.getElementById('receiptPreviewModal').classList.remove('hidden');
    }

    async function verifyTransaction(id, action) {
        const url = `/admin/multibankqr/transactions/${id}/${action}`;
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': pakPayCsrf,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                showPakPayAlert(data.message, true);
                const statusBadge = document.getElementById(`trx-status-${id}`);
                if (statusBadge) {
                    if (action === 'approve') {
                        statusBadge.className = 'inline-block px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-950 text-emerald-400 border border-emerald-800/60';
                        statusBadge.textContent = 'Verified (Paid)';
                    } else {
                        statusBadge.className = 'inline-block px-2 py-0.5 text-[10px] font-bold rounded-md bg-red-950 text-red-400 border border-red-800/60';
                        statusBadge.textContent = 'Declined';
                    }
                }
            } else {
                showPakPayAlert(data.message || 'Operation failed', false);
            }
        } catch (e) {
            showPakPayAlert('Verification error: ' + e.message, false);
        }
    }

    async function checkPakPayUpdates() {
        showPakPayAlert('Checking GitHub for new releases...', true);
        try {
            const res = await fetch('/admin/multibankqr/updates/check', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': pakPayCsrf,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('remoteVersionLabel').textContent = 'v' + (data.update_info?.latest_version || '1.2.8');
                document.getElementById('remoteStatusLabel').textContent = data.update_info?.has_update ? '🔔 New update available!' : '✅ Gateway is up to date';
                showPakPayAlert(data.message, true);
            } else {
                showPakPayAlert(data.message || 'Could not fetch GitHub updates', false);
            }
        } catch (e) {
            showPakPayAlert('Update check failed: ' + e.message, false);
        }
    }

    async function applyPakPayUpdate() {
        if (!confirm('Apply in-place update from GitHub repository? This will refresh extension files to the latest release.')) {
            return;
        }

        showPakPayAlert('Downloading and applying update in-place...', true);
        try {
            const res = await fetch('/admin/multibankqr/updates/apply', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': pakPayCsrf,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                showPakPayAlert(data.message + ' Reloading...', true);
                setTimeout(() => window.location.reload(), 2000);
            } else {
                showPakPayAlert(data.message || 'Update failed', false);
            }
        } catch (e) {
            showPakPayAlert('Update application error: ' + e.message, false);
        }
    }
</script>