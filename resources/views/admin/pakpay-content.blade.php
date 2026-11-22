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
                <a href="{{ url('/admin/extensions') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700 transition">
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

                            <!-- Raw QR Payload Preview -->
                            @if($acc->qr_payload)
                                <div class="mt-2.5 flex items-center gap-2 p-2 rounded-lg bg-emerald-950/30 border border-emerald-800/40 text-xs">
                                    <div class="p-1 rounded bg-emerald-900/60 text-emerald-400 shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    </div>
                                    <div class="overflow-hidden min-w-0 flex-1">
                                        <span class="text-[10px] uppercase text-emerald-400 font-bold block">Raw QR Payload</span>
                                        <span class="text-[11px] font-mono text-slate-300 truncate block select-all" title="{{ $acc->qr_payload }}">{{ $acc->qr_payload }}</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Scanned QR Preview -->
                            @if($acc->qr_image_url)
                                <div class="mt-2.5 flex items-center gap-2 p-2 rounded-lg bg-slate-950/40 border border-slate-800/60">
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
                    <h2 class="text-sm font-bold text-white flex items-center gap-2">
                        <span>Submitted Transactions & Verification Logs</span>
                        <span id="verif_count_badge" class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-800 text-slate-300">{{ count($transactions) }}</span>
                    </h2>
                    <p class="text-xs text-slate-400">Review customer proof submissions, open full verification logs, and approve invoices</p>
                </div>

                <!-- Controls: Refresh Logs Button & Live Auto-Update Rule -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Auto-Update Toggle Rule -->
                    <label class="inline-flex items-center gap-2 cursor-pointer bg-slate-950/80 px-3 py-1.5 rounded-lg border border-slate-800 text-xs hover:border-slate-700 transition" title="Automatically poll for new customer payments and paid invoices">
                        <input type="checkbox" id="pakpay_auto_refresh_toggle" checked onchange="toggleAutoUpdate(this.checked)" class="w-3.5 h-3.5 text-emerald-600 rounded bg-slate-900 border-slate-700 focus:ring-0">
                        <span class="flex items-center gap-1.5 font-medium text-slate-300">
                            <span id="pakpay_live_indicator" class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span id="pakpay_auto_label">Auto-Update (10s)</span>
                        </span>
                    </label>

                    <!-- Refresh Button -->
                    <button type="button" onclick="refreshPakPayTransactions(true)" id="pakpay_refresh_btn" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg border border-slate-700 transition" title="Manually refresh payment verification logs">
                        <svg id="pakpay_refresh_icon" class="w-3.5 h-3.5 text-emerald-400 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Refresh Logs</span>
                    </button>

                    <span id="pakpay_last_synced" class="text-[11px] text-slate-500 hidden sm:inline-block">Synced just now</span>
                </div>
            </div>

            <!-- Table of Transactions -->
            <div id="pakpay_empty_state" class="{{ count($transactions) === 0 ? '' : 'hidden' }} p-12 text-center space-y-3">
                <p class="text-sm text-slate-400">No transactions recorded yet.</p>
            </div>

            <div id="pakpay_table_wrapper" class="{{ count($transactions) === 0 ? 'hidden' : '' }} overflow-x-auto">
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
                            <th class="p-3 text-right">Actions & Details</th>
                        </tr>
                    </thead>
                    <tbody id="pakpay_trx_tbody" class="divide-y divide-slate-800/80">
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
                                        <!-- Log Details Modal Trigger Button -->
                                        <button type="button" onclick='openLogDetailsModal(@json($trx))' class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold bg-slate-800 hover:bg-slate-700 text-indigo-300 rounded border border-slate-700 transition" title="Inspect Full Payment Verification Log">
                                            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Log Details</span>
                                        </button>

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
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Raw QR Code Payload (Dynamic Raast / UPI / Payment URI)</label>
                    <textarea name="qr_payload" rows="2" placeholder="e.g. 000201010211... or upi://pay?pa=acc@bank&pn=Merchant&am={amount} (supports {amount} and {invoice_id} tokens)" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white font-mono focus:outline-none focus:border-emerald-500"></textarea>
                    <p class="text-[11px] text-slate-500 mt-1">Provide raw QR payload text to generate dynamic vector QR code. Tokens supported: <code class="text-emerald-400 font-mono">{amount}</code> and <code class="text-emerald-400 font-mono">{invoice_id}</code>.</p>
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
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Raw QR Code Payload (Dynamic Raast / UPI / Payment URI)</label>
                    <textarea id="hub_edit_qr_payload" name="qr_payload" rows="2" placeholder="e.g. 000201010211... or upi://pay?pa=acc@bank&pn=Merchant&am={amount} (supports {amount} and {invoice_id} tokens)" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white font-mono focus:outline-none focus:border-indigo-500"></textarea>
                    <p class="text-[11px] text-slate-500 mt-1">Provide raw QR payload text to generate dynamic vector QR code. Tokens supported: <code class="text-indigo-400 font-mono">{amount}</code> and <code class="text-indigo-400 font-mono">{invoice_id}</code>.</p>
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

    <!-- TRANSACTION LOG DETAILS MODAL -->
    <div id="logDetailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/85 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeLogDetailsModal()">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-2xl w-full p-6 space-y-5 shadow-2xl" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-950/80 border border-indigo-800/60 flex items-center justify-center text-indigo-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <span>Payment Verification Log</span>
                            <span id="log_modal_id" class="text-xs text-slate-400 font-mono">#--</span>
                        </h3>
                        <p class="text-[11px] text-slate-400">Complete audit trail & customer submission details</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span id="log_modal_status_badge" class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-800 text-slate-300">--</span>
                    <button type="button" onclick="closeLogDetailsModal()" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <!-- Invoice Details Tile -->
                <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/80 space-y-1.5">
                    <span class="text-[10px] uppercase font-bold text-slate-500 block tracking-wider">Invoice Details</span>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Invoice:</span>
                        <a id="log_modal_invoice_link" href="#" target="_blank" class="font-bold text-indigo-400 hover:underline inline-flex items-center gap-1">
                            <span id="log_modal_invoice_id">#--</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Amount Due:</span>
                        <span id="log_modal_amount" class="font-bold text-emerald-400 font-mono">PKR --</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Payment Gateway:</span>
                        <span id="log_modal_bank_name" class="text-slate-200 font-medium truncate max-w-[150px]">--</span>
                    </div>
                </div>

                <!-- Customer & Sender Tile -->
                <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/80 space-y-1.5">
                    <span class="text-[10px] uppercase font-bold text-slate-500 block tracking-wider">Customer / Sender</span>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Account Title:</span>
                        <span id="log_modal_sender_name" class="font-semibold text-slate-200 truncate max-w-[150px]">--</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Sender Account:</span>
                        <span id="log_modal_sender_account" class="font-mono text-slate-300 select-all truncate max-w-[150px]">--</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Submitted:</span>
                        <span id="log_modal_submitted_at" class="text-slate-400 truncate max-w-[150px]">--</span>
                    </div>
                </div>
            </div>

            <!-- Transaction Reference Tile (Full Width) -->
            <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/80 flex items-center justify-between gap-3 text-xs">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-500 block tracking-wider">Transaction ID / UTR / Reference</span>
                    <span id="log_modal_trx_id" class="font-mono font-bold text-emerald-400 text-sm select-all">--</span>
                </div>
                <button type="button" onclick="copyLogTrx()" class="px-2.5 py-1 text-[11px] font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 rounded border border-slate-700 transition">
                    <span id="log_modal_copy_text">Copy TRX</span>
                </button>
            </div>

            <!-- Customer Notes if any -->
            <div id="log_modal_notes_box" class="hidden bg-slate-950/40 p-3 rounded-xl border border-slate-800/60 text-xs">
                <span class="text-[10px] uppercase font-bold text-slate-500 block tracking-wider mb-1">Customer Remarks / Notes</span>
                <p id="log_modal_notes" class="text-slate-300 italic"></p>
            </div>

            <!-- Receipt Proof Section -->
            <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/80 text-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Payment Receipt Screenshot</span>
                    <a id="log_modal_receipt_download" href="#" target="_blank" download class="text-[11px] font-semibold text-indigo-400 hover:text-indigo-300 inline-flex items-center gap-1">
                        <span>Open Original Image</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
                <div id="log_modal_receipt_container" class="bg-black/60 rounded-lg p-2 flex items-center justify-center max-h-56 overflow-hidden">
                    <img id="log_modal_receipt_img" src="" alt="Proof Screenshot" class="max-h-52 w-auto object-contain rounded cursor-pointer transition hover:opacity-95" onclick="previewReceiptImage(this.src, 'Proof')">
                </div>
                <div id="log_modal_no_receipt" class="hidden text-center py-4 text-slate-500 text-xs">
                    No screenshot receipt was uploaded for this transaction.
                </div>
            </div>

            <!-- Admin Audit Trail -->
            <div id="log_modal_audit_box" class="bg-slate-950/40 p-3 rounded-xl border border-slate-800/60 text-xs space-y-1 text-slate-400">
                <span class="text-[10px] uppercase font-bold text-slate-500 block tracking-wider mb-1">Verification Audit Trail</span>
                <div class="flex items-center justify-between">
                    <span>Reviewed By:</span>
                    <span id="log_modal_reviewer" class="text-slate-300 font-medium">--</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Reviewed At:</span>
                    <span id="log_modal_reviewed_at" class="text-slate-300">--</span>
                </div>
                <div id="log_modal_admin_notes_row" class="hidden pt-1 border-t border-slate-800">
                    <span class="text-slate-400 block mb-0.5">Admin Note:</span>
                    <span id="log_modal_admin_notes" class="text-amber-300 font-medium"></span>
                </div>
                <div id="log_modal_decline_row" class="hidden pt-1 border-t border-slate-800">
                    <span class="text-slate-400 block mb-0.5">Decline Reason:</span>
                    <span id="log_modal_decline_reason" class="text-red-400 font-medium"></span>
                </div>
            </div>

            <!-- Modal Footer Actions -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                <button type="button" onclick="closeLogDetailsModal()" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition">Close</button>
                <div id="log_modal_actions" class="flex items-center gap-2">
                    <button type="button" id="log_modal_approve_btn" onclick="approveFromModal()" class="px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg shadow-md transition">✅ Verify & Mark Invoice Paid</button>
                    <button type="button" id="log_modal_decline_btn" onclick="declineFromModal()" class="px-3 py-2 text-xs font-bold bg-red-950/80 hover:bg-red-900 border border-red-700/60 text-red-300 rounded-lg transition">❌ Decline</button>
                </div>
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
    const pakPayBase = '{{ url("") }}';

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
        document.getElementById('editAccountForm').action = `${pakPayBase}/admin/multibankqr/accounts/${acc.id}/update`;
        document.getElementById('hub_edit_name').value = acc.name || '';
        document.getElementById('hub_edit_badge').value = acc.badge || '';
        document.getElementById('hub_edit_account_title').value = acc.account_title || '';
        document.getElementById('hub_edit_account_number').value = acc.account_number || '';
        document.getElementById('hub_edit_routing_code').value = acc.routing_code || '';
        document.getElementById('hub_edit_currency').value = acc.currency || 'PKR';
        document.getElementById('hub_edit_sort_order').value = acc.sort_order || 0;
        document.getElementById('hub_edit_qr_payload').value = acc.qr_payload || '';
        document.getElementById('hub_edit_qr_image_url').value = acc.qr_image_url || '';
        document.getElementById('hub_edit_instructions').value = acc.instructions || '';
        document.getElementById('hub_edit_is_active').checked = !!acc.is_active;

        document.getElementById('editAccountModal').classList.remove('hidden');
    }

    async function togglePakPayAccount(id) {
        try {
            const res = await fetch(`${pakPayBase}/admin/multibankqr/accounts/${id}/toggle`, {
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
            const res = await fetch(`${pakPayBase}/admin/multibankqr/accounts/${id}/delete`, {
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

    let currentLogTrx = null;
    let autoUpdateInterval = null;
    let knownTrxIds = new Set(@json($transactions->pluck('id')));

    function openLogDetailsModal(trx) {
        if (!trx) return;
        currentLogTrx = trx;

        document.getElementById('log_modal_id').textContent = '#' + (trx.id || '');
        document.getElementById('log_modal_invoice_id').textContent = '#' + (trx.invoice_id || '');
        document.getElementById('log_modal_invoice_link').href = `${pakPayBase}/admin/invoices/${trx.invoice_id || ''}`;
        document.getElementById('log_modal_amount').textContent = (trx.currency || 'PKR') + ' ' + Number(trx.amount || 0).toFixed(2);
        document.getElementById('log_modal_bank_name').textContent = trx.bank_account_name || 'Standard Gateway';
        document.getElementById('log_modal_sender_name').textContent = trx.sender_name || 'Customer';
        document.getElementById('log_modal_sender_account').textContent = trx.sender_account || 'Not specified';
        document.getElementById('log_modal_submitted_at').textContent = trx.created_at_human ? (trx.created_at_human + ' (' + trx.created_at + ')') : (trx.created_at || 'Recently');
        document.getElementById('log_modal_trx_id').textContent = trx.transaction_id || 'N/A';

        // Status badge
        const badge = document.getElementById('log_modal_status_badge');
        const isVerif = ['verified', 'approved', 'paid'].includes(trx.status);
        const isDecl = ['declined', 'rejected'].includes(trx.status);

        if (isVerif) {
            badge.className = 'px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-950 text-emerald-400 border border-emerald-800';
            badge.textContent = 'Verified / Paid';
        } else if (isDecl) {
            badge.className = 'px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-red-950 text-red-400 border border-red-800';
            badge.textContent = 'Declined';
        } else {
            badge.className = 'px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-amber-950 text-amber-300 border border-amber-800';
            badge.textContent = 'Pending Verification';
        }

        // Customer notes
        const notesBox = document.getElementById('log_modal_notes_box');
        const notesText = document.getElementById('log_modal_notes');
        if (trx.customer_notes && trx.customer_notes.trim()) {
            notesText.textContent = '"' + trx.customer_notes.trim() + '"';
            notesBox.classList.remove('hidden');
        } else {
            notesBox.classList.add('hidden');
        }

        // Receipt Proof
        const receiptImg = document.getElementById('log_modal_receipt_img');
        const receiptContainer = document.getElementById('log_modal_receipt_container');
        const noReceipt = document.getElementById('log_modal_no_receipt');
        const downloadLink = document.getElementById('log_modal_receipt_download');
        const proofPath = trx.proof_image_path || (trx.proof_image ? '/' + trx.proof_image.replace(/^\//, '') : null);

        if (proofPath) {
            receiptImg.src = proofPath;
            downloadLink.href = proofPath;
            downloadLink.classList.remove('hidden');
            receiptContainer.classList.remove('hidden');
            noReceipt.classList.add('hidden');
        } else {
            downloadLink.classList.add('hidden');
            receiptContainer.classList.add('hidden');
            noReceipt.classList.remove('hidden');
        }

        // Audit Trail
        document.getElementById('log_modal_reviewer').textContent = trx.reviewed_by ? ('Admin #' + trx.reviewed_by) : 'Not yet reviewed';
        document.getElementById('log_modal_reviewed_at').textContent = trx.reviewed_at || 'Pending';

        const adminNotesRow = document.getElementById('log_modal_admin_notes_row');
        if (trx.admin_notes) {
            document.getElementById('log_modal_admin_notes').textContent = trx.admin_notes;
            adminNotesRow.classList.remove('hidden');
        } else {
            adminNotesRow.classList.add('hidden');
        }

        const declineRow = document.getElementById('log_modal_decline_row');
        if (trx.decline_reason) {
            document.getElementById('log_modal_decline_reason').textContent = trx.decline_reason;
            declineRow.classList.remove('hidden');
        } else {
            declineRow.classList.add('hidden');
        }

        // Action Buttons inside Modal
        const approveBtn = document.getElementById('log_modal_approve_btn');
        const declineBtn = document.getElementById('log_modal_decline_btn');

        if (isVerif) {
            approveBtn.classList.add('hidden');
            declineBtn.classList.remove('hidden');
        } else if (isDecl) {
            approveBtn.classList.remove('hidden');
            declineBtn.classList.add('hidden');
        } else {
            approveBtn.classList.remove('hidden');
            declineBtn.classList.remove('hidden');
        }

        document.getElementById('logDetailsModal').classList.remove('hidden');
    }

    function closeLogDetailsModal() {
        document.getElementById('logDetailsModal').classList.add('hidden');
        currentLogTrx = null;
    }

    function copyLogTrx() {
        if (!currentLogTrx || !currentLogTrx.transaction_id) return;
        const text = currentLogTrx.transaction_id;
        const success = () => {
            const btn = document.getElementById('log_modal_copy_text');
            if (btn) {
                btn.textContent = 'Copied!';
                setTimeout(() => { btn.textContent = 'Copy TRX'; }, 1500);
            }
        };
        const fallback = () => {
            try {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.setAttribute('readonly', '');
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                success();
            } catch (e) {
                prompt('Copy TRX:', text);
            }
        };
        if (navigator.clipboard && window.isSecureContext && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(success).catch(fallback);
        } else {
            fallback();
        }
    }

    async function approveFromModal() {
        if (!currentLogTrx) return;
        await verifyTransaction(currentLogTrx.id, 'approve');
        currentLogTrx.status = 'verified';
        openLogDetailsModal(currentLogTrx);
    }

    async function declineFromModal() {
        if (!currentLogTrx) return;
        await verifyTransaction(currentLogTrx.id, 'decline');
        currentLogTrx.status = 'declined';
        openLogDetailsModal(currentLogTrx);
    }

    // Refresh transactions live via JSON API without reloading page
    async function refreshPakPayTransactions(isManual = false) {
        const icon = document.getElementById('pakpay_refresh_icon');
        const lastSynced = document.getElementById('pakpay_last_synced');
        if (icon) icon.classList.add('animate-spin');

        try {
            // Try admin route first, then fallback to extensions route (dynamic base URL)
            let url = `${pakPayBase}/admin/multibankqr/transactions/api`;
            let res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) {
                res = await fetch(`${pakPayBase}/extensions/multibankqr/admin/transactions/api`, { headers: { 'Accept': 'application/json' } });
            }

            if (res.ok) {
                const data = await res.json();
                if (data.success && Array.isArray(data.transactions)) {
                    renderTransactionsTable(data.transactions);

                    const badge = document.getElementById('verif_count_badge');
                    if (badge) badge.textContent = data.transactions.length;

                    if (lastSynced) {
                        lastSynced.textContent = 'Synced ' + (data.synced_at || new Date().toLocaleTimeString());
                    }

                    if (isManual) {
                        showPakPayAlert('Payment verification logs refreshed!', true);
                    }
                }
            }
        } catch (e) {
            if (isManual) showPakPayAlert('Could not refresh logs: ' + e.message, false);
        } finally {
            if (icon) icon.classList.remove('animate-spin');
        }
    }

    function renderTransactionsTable(transactions) {
        const tbody = document.getElementById('pakpay_trx_tbody');
        const emptyState = document.getElementById('pakpay_empty_state');
        const wrapper = document.getElementById('pakpay_table_wrapper');
        if (!tbody) return;

        if (!transactions || transactions.length === 0) {
            if (emptyState) emptyState.classList.remove('hidden');
            if (wrapper) wrapper.classList.add('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');
        if (wrapper) wrapper.classList.remove('hidden');

        let rowsHtml = '';
        let hasNewPayment = false;

        transactions.forEach(trx => {
            const isNew = !knownTrxIds.has(trx.id);
            if (isNew) {
                knownTrxIds.add(trx.id);
                hasNewPayment = true;
            }

            const isVerif = ['verified', 'approved', 'paid'].includes(trx.status);
            const isDecl = ['declined', 'rejected'].includes(trx.status);

            const badgeClass = isVerif
                ? 'bg-emerald-950 text-emerald-400 border border-emerald-800/60'
                : (isDecl ? 'bg-red-950 text-red-400 border border-red-800/60' : 'bg-amber-950 text-amber-300 border border-amber-800/60');

            const statusLabel = isVerif ? 'Verified (Paid)' : (isDecl ? 'Declined' : 'Pending Verification');
            const proofPath = trx.proof_image_path || (trx.proof_image ? '/' + trx.proof_image.replace(/^\//, '') : null);

            rowsHtml += `
                <tr id="trx-row-${trx.id}" class="hover:bg-slate-800/40 transition ${isNew ? 'bg-emerald-950/40 border border-emerald-500/50' : ''}">
                    <td class="p-3 font-semibold text-white">#${trx.invoice_id}</td>
                    <td class="p-3 text-slate-300">${escapeHtml(trx.sender_name || 'Customer')}</td>
                    <td class="p-3 font-bold text-emerald-400">${escapeHtml(trx.currency)} ${trx.amount_formatted || Number(trx.amount).toFixed(2)}</td>
                    <td class="p-3 text-slate-300">${escapeHtml(trx.bank_account_name || 'Standard')}</td>
                    <td class="p-3 font-mono text-slate-200 select-all">${escapeHtml(trx.transaction_id)}</td>
                    <td class="p-3">
                        ${proofPath ? `
                            <button type="button" onclick="previewReceiptImage('${escapeHtml(proofPath)}', 'Invoice #${trx.invoice_id} (${escapeHtml(trx.transaction_id)}')')" class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-400 hover:text-indigo-300 bg-indigo-950/60 px-2 py-1 rounded border border-indigo-800/40">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>View</span>
                            </button>
                        ` : `<span class="text-slate-600 text-[11px]">No Receipt</span>`}
                    </td>
                    <td class="p-3">
                        <span id="trx-status-${trx.id}" class="inline-block px-2 py-0.5 text-[10px] font-bold rounded-md ${badgeClass}">
                            ${statusLabel}
                        </span>
                    </td>
                    <td class="p-3 text-slate-400 text-[11px] whitespace-nowrap">${trx.created_at || 'Recently'}</td>
                    <td class="p-3 text-right whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5">
                            <button type="button" onclick='openLogDetailsModal(${JSON.stringify(trx)})' class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold bg-slate-800 hover:bg-slate-700 text-indigo-300 rounded border border-slate-700 transition" title="Inspect Full Payment Verification Log">
                                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Log Details</span>
                            </button>
                            ${!isVerif ? `
                                <button type="button" onclick="verifyTransaction(${trx.id}, 'approve')" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded transition shadow">
                                    <span>✅ Approve</span>
                                </button>
                            ` : ''}
                            ${!isDecl ? `
                                <button type="button" onclick="verifyTransaction(${trx.id}, 'decline')" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold bg-red-950/70 hover:bg-red-900 border border-red-700/60 text-red-300 rounded transition">
                                    <span>❌ Decline</span>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = rowsHtml;

        if (hasNewPayment) {
            showPakPayAlert('🔔 New customer payment submission received! Table updated.', true);
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function toggleAutoUpdate(enable) {
        const ind = document.getElementById('pakpay_live_indicator');
        const lbl = document.getElementById('pakpay_auto_label');

        if (enable) {
            if (ind) ind.className = 'w-2 h-2 rounded-full bg-emerald-400 animate-pulse';
            if (lbl) lbl.textContent = 'Auto-Update (10s)';
            startAutoUpdate();
            refreshPakPayTransactions(false);
        } else {
            if (ind) ind.className = 'w-2 h-2 rounded-full bg-slate-600';
            if (lbl) lbl.textContent = 'Auto-Update (Paused)';
            stopAutoUpdate();
        }
    }

    function startAutoUpdate() {
        stopAutoUpdate();
        autoUpdateInterval = setInterval(() => {
            const toggle = document.getElementById('pakpay_auto_refresh_toggle');
            if (toggle && toggle.checked && !document.hidden) {
                refreshPakPayTransactions(false);
            }
        }, 10000);
    }

    function stopAutoUpdate() {
        if (autoUpdateInterval) {
            clearInterval(autoUpdateInterval);
            autoUpdateInterval = null;
        }
    }

    // Auto-pause polling when tab is hidden, auto-resume when visible
    document.addEventListener('visibilitychange', () => {
        const toggle = document.getElementById('pakpay_auto_refresh_toggle');
        if (!document.hidden && toggle && toggle.checked) {
            refreshPakPayTransactions(false);
        }
    });

    // Initialize auto-update rule on load
    startAutoUpdate();

    function previewReceiptImage(url, title) {
        document.getElementById('receiptPreviewImage').src = url;
        document.getElementById('receiptDownloadLink').href = url;
        document.getElementById('receiptPreviewTitle').textContent = title || 'Payment Receipt';
        document.getElementById('receiptPreviewModal').classList.remove('hidden');
    }

    async function verifyTransaction(id, action) {
        let url = `${pakPayBase}/admin/multibankqr/transactions/${id}/${action}`;
        try {
            let res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': pakPayCsrf,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) {
                res = await fetch(`${pakPayBase}/extensions/multibankqr/admin/transactions/${id}/${action}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': pakPayCsrf,
                        'Accept': 'application/json'
                    }
                });
            }
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
            let res = await fetch(`${pakPayBase}/admin/multibankqr/updates/check`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': pakPayCsrf,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) {
                res = await fetch(`${pakPayBase}/extensions/multibankqr/admin/updates/check`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': pakPayCsrf,
                        'Accept': 'application/json'
                    }
                });
            }
            const data = await res.json();
            if (data.success) {
                document.getElementById('remoteVersionLabel').textContent = 'v' + (data.update_info?.latest_version || '1.3.2');
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
            let res = await fetch(`${pakPayBase}/admin/multibankqr/updates/apply`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': pakPayCsrf,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) {
                res = await fetch(`${pakPayBase}/extensions/multibankqr/admin/updates/apply`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': pakPayCsrf,
                        'Accept': 'application/json'
                    }
                });
            }
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