@php
    $invoiceId = (int) ($invoice->id ?? 0);
    $invoiceTotal = number_format((float) ($total ?? ($invoice->remaining ?? $invoice->total)), 2);
    $invoiceCurrency = htmlspecialchars($currency ?? ($invoice->currency_code ?? ($invoice->currency ?? 'PKR')), ENT_QUOTES, 'UTF-8');
    $hasTransaction = !empty($transaction);
    $isPendingVerif = $hasTransaction && $transaction->isPendingVerification();
    $isVerified = $hasTransaction && $transaction->isVerified();
    $isFlagged = $hasTransaction && $transaction->isFlagged();
    $isDeclined = $hasTransaction && $transaction->isDeclined();
@endphp

<div class="multibankqr-container max-w-4xl mx-auto text-gray-800 dark:text-gray-100 font-sans my-4 px-2 sm:px-4">
    <!-- Inline Styling -->
    <style>
        .multibankqr-tab-btn.active {
            border-color: #059669;
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.08), rgba(5, 150, 105, 0.02));
            color: #059669;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.12);
        }
        @keyframes pulse-ring {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.85; }
        }
        .animate-pulse-ring {
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>

    <!-- Top Header: Trust, Invoice Summary & Order Total -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white rounded-2xl shadow-xl p-5 sm:p-6 mb-5 border border-slate-700/60">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Secured Checkout
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        Manual Verification
                    </span>
                    <span class="text-xs text-slate-400">Order #{{ $invoiceId }}</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white">
                    {{ $gatewayTitle ?? 'Pakistani Banks, Raast & Wallets' }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">
                    Pay using Raast P2P, Meezan, Easypaisa, JazzCash, or SadaPay. Scan the static QR or transfer to the account details below.
                </p>
            </div>

            <!-- Amount Due Card -->
            <div class="bg-white/10 backdrop-blur-md rounded-xl p-3.5 sm:px-5 sm:py-3 border border-white/15 text-left sm:text-right w-full sm:w-auto shadow-inner">
                <span class="text-[11px] uppercase tracking-wider text-slate-300 font-semibold block">Total Payable</span>
                <div class="flex items-baseline sm:justify-end gap-1.5 mt-0.5">
                    <span class="text-xs font-bold text-slate-300">{{ $invoiceCurrency }}</span>
                    <span class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ $invoiceTotal }}</span>
                </div>
            </div>
        </div>

        @if(!empty($customNotice))
        <div class="mt-4 pt-3.5 border-t border-slate-700/60 flex items-start gap-2.5 text-xs text-slate-300 bg-slate-800/40 rounded-xl p-3">
            <svg class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ $customNotice }}</span>
        </div>
        @endif
    </div>

    <!-- PRE-PAYMENT DEAL CLOSER: Contact on WhatsApp Before Making Payment -->
    @if(!empty($whatsappPrePayUrl))
    <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl p-4 sm:p-5 mb-5 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="p-2.5 rounded-xl bg-emerald-600 text-white shrink-0 mt-0.5">
                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.153.57 4.175 1.564 5.921l-1.636 5.981 6.136-1.609c1.706.929 3.659 1.457 5.736 1.457 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"/></svg>
            </div>
            <div>
                <h4 class="text-sm sm:text-base font-bold text-emerald-950 dark:text-emerald-100">
                    Have questions before transferring payment?
                </h4>
                <p class="text-xs text-emerald-800 dark:text-emerald-300 mt-0.5">
                    Confirm bank details, get help with Raast/Easypaisa, or request instant assistance directly on WhatsApp.
                </p>
            </div>
        </div>

        <a href="{{ $whatsappPrePayUrl }}" target="_blank" rel="noopener noreferrer"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shadow-sm transition shrink-0 transform active:scale-95">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.153.57 4.175 1.564 5.921l-1.636 5.981 6.136-1.609c1.706.929 3.659 1.457 5.736 1.457 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"/></svg>
            <span>Ask on WhatsApp Before Paying</span>
        </a>
    </div>
    @endif

    <!-- Live Status Tracker Card: Pending Payment -> Pending Verification -> Verified -->
    <div id="live-status-card" class="mb-5 rounded-2xl p-5 border shadow-sm transition-all duration-300
        @if($isVerified) bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-700
        @elseif($isPendingVerif) bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-700
        @elseif($isFlagged) bg-sky-50 dark:bg-sky-950/40 border-sky-300 dark:border-sky-700
        @elseif($isDeclined) bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-700
        @else bg-slate-50 dark:bg-slate-900/40 border-slate-200 dark:border-slate-700
        @endif">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-start gap-3.5">
                <div id="status-icon" class="text-3xl shrink-0 mt-0.5">
                    @if($isVerified)
                        <span class="text-emerald-500">✅</span>
                    @elseif($isPendingVerif)
                        <div class="relative w-7 h-7 flex items-center justify-center">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-4 w-4 bg-amber-500"></span>
                        </div>
                    @elseif($isFlagged)
                        <span class="text-sky-500">⚠️</span>
                    @elseif($isDeclined)
                        <span class="text-rose-500">❌</span>
                    @else
                        <span class="text-slate-400">⏳</span>
                    @endif
                </div>

                <div>
                    <div class="flex items-center gap-2">
                        <span id="status-label" class="font-bold text-base text-gray-900 dark:text-white">
                            Status: {{ $hasTransaction ? $transaction->getStatusLabel() : 'Pending Payment' }}
                        </span>
                        <span id="status-badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border
                            @if($isVerified) bg-emerald-100 text-emerald-800 border-emerald-300
                            @elseif($isPendingVerif) bg-amber-100 text-amber-800 border-amber-300 animate-pulse
                            @elseif($isFlagged) bg-sky-100 text-sky-800 border-sky-300
                            @elseif($isDeclined) bg-rose-100 text-rose-800 border-rose-300
                            @else bg-slate-100 text-slate-700 border-slate-300
                            @endif">
                            @if($isVerified) Verified & Active
                            @elseif($isPendingVerif) Pending Manual Verification
                            @elseif($isFlagged) Action Required
                            @elseif($isDeclined) Declined
                            @else Awaiting Your Transfer
                            @endif
                        </span>
                    </div>

                    <p id="status-desc" class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                        @if($isVerified)
                            Your payment has been manually verified by our team! Your services are active.
                        @elseif($isPendingVerif)
                            Proof received (TRX: <strong class="font-mono text-gray-900 dark:text-white">{{ $transaction->transaction_id }}</strong>). Our admin team is manually verifying the funds against our bank statement.
                        @elseif($isFlagged)
                            Admin Note: <strong>{{ $transaction->admin_notes }}</strong>. Please re-submit corrected details below.
                        @elseif($isDeclined)
                            Declined: {{ $transaction->decline_reason ?? 'Payment could not be verified.' }}. Please contact WhatsApp/Discord support.
                        @else
                            Transfer funds using the static QR code or account details below, then enter your Transaction ID (TRX) to trigger manual verification.
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                @if($isPendingVerif)
                    <button type="button" onclick="checkStatusNow()" class="w-full sm:w-auto px-3.5 py-1.5 bg-white dark:bg-gray-800 hover:bg-gray-100 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg border border-gray-300 dark:border-gray-700 transition">
                        Refresh Status
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Payment Details & Scanned Static QR Section -->
    @if(!empty($accounts))
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 sm:p-6 mb-5">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/60 mb-5">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Select Receiving Bank / Wallet Account
                </h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Choose your preferred Pakistani bank or mobile wallet
                </span>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                0% Extra Processing Fee
            </span>
        </div>

        <!-- Account Tabs -->
        <div class="flex flex-wrap gap-2 pb-2 border-b border-gray-100 dark:border-gray-700/60 mb-6">
            @foreach($accounts as $index => $acc)
                <button type="button"
                    onclick="selectBankAccount('{{ $acc['id'] }}')"
                    id="tab-btn-{{ $acc['id'] }}"
                    class="multibankqr-tab-btn {{ $index === 0 ? 'active' : '' }} flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs sm:text-sm font-semibold transition bg-gray-50/50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60">
                    <span>{{ $acc['name'] }}</span>
                    @if(!empty($acc['badge']))
                        <span class="px-1.5 py-0.5 text-[10px] rounded-md font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                            {{ $acc['badge'] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        <!-- Account Panels -->
        @foreach($accounts as $index => $acc)
            <div id="panel-{{ $acc['id'] }}" class="bank-panel {{ $index === 0 ? '' : 'hidden' }} transition-opacity duration-200">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                    <!-- Left: Bank & Account Details with 1-Click Copy -->
                    <div class="lg:col-span-7 space-y-3.5">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700/50">
                            <div>
                                <h4 class="text-base font-extrabold text-gray-900 dark:text-white">{{ $acc['name'] }}</h4>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Transfer exact amount to the account below</span>
                            </div>
                            @if(!empty($acc['badge']))
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    {{ $acc['badge'] }}
                                </span>
                            @endif
                        </div>

                        <!-- Account Title -->
                        <div class="bg-gray-50 dark:bg-gray-900/50 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Account Title</span>
                                <span class="text-sm sm:text-base font-bold text-gray-900 dark:text-white break-all">{{ $acc['account_title'] }}</span>
                            </div>
                            <button type="button" onclick="copyToClipboard('{{ addslashes($acc['account_title']) }}', this)" class="shrink-0 p-2 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-gray-800 rounded-lg transition" title="Copy Account Title">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>

                        <!-- Account Number / IBAN / Raast ID -->
                        <div class="bg-gray-50 dark:bg-gray-900/50 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Account # / IBAN / Raast ID</span>
                                <span class="text-sm sm:text-base font-mono font-extrabold text-gray-900 dark:text-white break-all">{{ $acc['account_number'] }}</span>
                            </div>
                            <button type="button" onclick="copyToClipboard('{{ addslashes($acc['account_number']) }}', this)" class="shrink-0 p-2 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-gray-800 rounded-lg transition" title="Copy Account Number">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>

                        <!-- Bank / Provider Name -->
                        @if(!empty($acc['routing_code']))
                        <div class="bg-gray-50 dark:bg-gray-900/50 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Bank / Wallet Provider</span>
                                <span class="text-sm sm:text-base font-medium text-gray-900 dark:text-white break-all">{{ $acc['routing_code'] }}</span>
                            </div>
                            <button type="button" onclick="copyToClipboard('{{ addslashes($acc['routing_code']) }}', this)" class="shrink-0 p-2 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-gray-800 rounded-lg transition" title="Copy Bank Name">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                        @endif

                        <!-- Transfer Reference Note -->
                        <div class="bg-amber-50/70 dark:bg-amber-950/30 p-3.5 rounded-xl border border-amber-200 dark:border-amber-800/50 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider block">Transfer Remarks / Reference Note</span>
                                <span class="text-sm sm:text-base font-mono font-extrabold text-amber-900 dark:text-amber-200">INV-{{ $invoiceId }}</span>
                            </div>
                            <button type="button" onclick="copyToClipboard('INV-{{ $invoiceId }}', this)" class="shrink-0 px-2.5 py-1.5 text-xs font-semibold bg-amber-200/80 dark:bg-amber-800/80 text-amber-900 dark:text-amber-100 hover:bg-amber-300 rounded-lg transition flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span>Copy Reference</span>
                            </button>
                        </div>

                        @if(!empty($acc['instructions']))
                        <div class="text-xs text-emerald-800 dark:text-emerald-300 bg-emerald-50/60 dark:bg-emerald-950/30 p-3 rounded-lg border border-emerald-200 dark:border-emerald-800/40">
                            <strong>Instructions:</strong> {{ $acc['instructions'] }}
                        </div>
                        @endif
                    </div>

                    <!-- Right: Scanned Static QR Code with Zoom Modal -->
                    <div class="lg:col-span-5 flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 text-center">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            Scanned Account QR Code
                        </span>

                        <div class="relative group bg-white p-3 rounded-2xl shadow-sm border border-gray-200 max-w-[240px]">
                            @if(!empty($acc['qr_src']))
                                <img src="{{ $acc['qr_src'] }}"
                                     alt="Payment QR Code for {{ $acc['name'] }}"
                                     class="w-48 h-48 object-contain rounded-xl cursor-pointer hover:opacity-95 transition"
                                     onclick="openQrZoomModal('{{ $acc['qr_src'] }}', '{{ addslashes($acc['name']) }}')">
                                <button type="button"
                                        onclick="openQrZoomModal('{{ $acc['qr_src'] }}', '{{ addslashes($acc['name']) }}')"
                                        class="mt-2 text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 flex items-center justify-center gap-1 mx-auto">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                    <span>Click to Enlarge</span>
                                </button>
                            @else
                                <div class="w-48 h-48 flex flex-col items-center justify-center text-gray-400 text-xs p-4 bg-gray-50 rounded-xl">
                                    <svg class="w-12 h-12 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    <span>Transfer to Account Details</span>
                                </div>
                            @endif
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2.5 max-w-[220px]">
                            Scan with Meezan, Easypaisa, JazzCash, SadaPay or any mobile banking app.
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <!-- POST-PAYMENT DEAL CLOSER: Submit Proof & WhatsApp / Discord Buttons -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 sm:p-6 mb-5">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/60 mb-5">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Submit Payment Proof for Manual Verification
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    After transferring, enter your Transaction ID (TRX/UTR/TID) below. Our team manually verifies the transfer and activates your service.
                </p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                Manual Verification
            </span>
        </div>

        <!-- Submission Form (AJAX enabled with strict validation) -->
        <form id="proof-submission-form" onsubmit="handleProofSubmit(event)" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="bank_account_name" id="selected-bank-name" value="{{ !empty($accounts) ? $accounts[0]['name'] : 'Meezan Bank / Raast' }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Transaction ID (TRX / UTR / Reference) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                        Transaction ID / TRX / TID / Reference <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                        name="transaction_id"
                        id="form-trx-id"
                        required
                        maxlength="100"
                        value="{{ $hasTransaction ? $transaction->transaction_id : '' }}"
                        placeholder="e.g. TRX1928374829, 3948271039, or TID"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm font-mono font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <span class="text-[11px] text-gray-500 mt-1 block">Visible in your SMS receipt or app confirmation screen.</span>
                </div>

                <!-- Sender Account Name / Mobile Number -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                        Sender Account Name / Mobile Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                        name="sender_name"
                        id="form-sender-name"
                        required
                        maxlength="150"
                        value="{{ $hasTransaction ? $transaction->sender_name : ($invoice->user->name ?? '') }}"
                        placeholder="e.g. Muhammad Ali / 03001234567"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <span class="text-[11px] text-gray-500 mt-1 block">Account title or Easypaisa/JazzCash number you sent from.</span>
                </div>
            </div>

            <!-- Receipt Screenshot Upload -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                    Payment Screenshot / Receipt Upload {{ $requireProofImage ? '(Required)' : '(Recommended)' }}
                </label>
                <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-emerald-500 rounded-2xl p-4 text-center transition bg-gray-50/50 dark:bg-gray-900/30">
                    <input type="file"
                        name="proof_image"
                        id="proof-file-input"
                        accept="image/jpeg,image/png,image/webp,application/pdf"
                        {{ $requireProofImage && empty($transaction?->proof_image) ? 'required' : '' }}
                        onchange="previewReceiptFile(this)"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

                    <div id="upload-placeholder" class="space-y-1">
                        <svg class="w-8 h-8 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <div class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                            <span class="text-emerald-600 dark:text-emerald-400 underline">Upload screenshot of payment receipt</span> or drag & drop
                        </div>
                        <p class="text-[11px] text-gray-500">JPG, PNG, WEBP, or PDF up to 5MB</p>
                    </div>

                    <!-- File Preview Card -->
                    <div id="file-preview-card" class="hidden items-center justify-center gap-3">
                        <img id="image-preview" src="#" alt="Receipt Preview" class="w-14 h-14 object-cover rounded-lg shadow-sm border border-gray-200">
                        <div class="text-left">
                            <span id="preview-filename" class="text-xs font-bold text-gray-800 dark:text-gray-100 block truncate max-w-xs"></span>
                            <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Receipt attached
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Optional Customer Note -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                    Customer Remarks / Notes (Optional)
                </label>
                <textarea name="customer_notes"
                    rows="2"
                    maxlength="1000"
                    placeholder="e.g. Sent via Meezan app, reference INV-{{ $invoiceId }}."
                    class="w-full px-3.5 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">{{ $hasTransaction ? $transaction->customer_notes : '' }}</textarea>
            </div>

            <!-- Submit Button & Post-Payment Contact Buttons -->
            <div class="pt-2 flex flex-col sm:flex-row items-center gap-3">
                <button type="submit"
                    id="submit-proof-btn"
                    class="w-full sm:w-auto flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-bold text-sm rounded-xl shadow-md transition transform active:scale-95 disabled:opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span id="btn-text">{{ $hasTransaction ? 'Update Submitted Details' : 'I Have Transferred — Submit for Verification' }}</span>
                </button>

                <!-- Send on WhatsApp Button (Deal Closer) -->
                @if(!empty($whatsappPostPayUrl))
                <a href="{{ $whatsappPostPayUrl }}" target="_blank" rel="noopener noreferrer"
                    id="whatsapp-postpay-btn"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl text-white font-bold text-xs sm:text-sm shadow-md transition transform active:scale-95 shrink-0"
                    style="background-color: #25D366;">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.153.57 4.175 1.564 5.921l-1.636 5.981 6.136-1.609c1.706.929 3.659 1.457 5.736 1.457 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"/></svg>
                    <span>Send Proof on WhatsApp</span>
                </a>
                @endif

                <!-- Discord Support Button -->
                @if(!empty($discordInvite))
                <a href="{{ $discordInvite }}" target="_blank" rel="noopener noreferrer"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl text-white font-bold text-xs sm:text-sm shadow-md transition transform active:scale-95 shrink-0"
                    style="background-color: #5865F2;">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.929 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.894.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>
                    <span>Discord Support</span>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Scanned QR Zoom Lightbox Modal -->
    <div id="qr-zoom-modal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeQrZoomModal()">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl max-w-sm w-full text-center shadow-2xl border border-gray-200 dark:border-gray-700" onclick="event.stopPropagation()">
            <h4 id="qr-modal-title" class="font-bold text-gray-900 dark:text-white text-base mb-3">Scanned Static QR</h4>
            <div class="bg-white p-2 rounded-xl border border-gray-200 inline-block mb-3">
                <img id="qr-modal-img" src="#" alt="Enlarged QR" class="max-w-[260px] max-h-[260px] mx-auto object-contain rounded-lg">
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                Scan using Meezan, Easypaisa, JazzCash, SadaPay or any mobile banking app.
            </p>
            <button type="button" onclick="closeQrZoomModal()" class="w-full py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-xs font-bold rounded-xl transition">
                Close
            </button>
        </div>
    </div>

    <!-- JavaScript: Tabs, Copying, Lightbox, AJAX & Live Status Polling -->
    <script>
        const INVOICE_ID = {{ $invoiceId }};
        const STATUS_URL = "{{ function_exists('route') && \Illuminate\Support\Facades\Route::has('multibankqr.status') ? route('multibankqr.status', $invoiceId) : '/extensions/multibankqr/status/' . $invoiceId }}";
        const SUBMIT_URL = "{{ function_exists('route') && \Illuminate\Support\Facades\Route::has('multibankqr.submit-proof') ? route('multibankqr.submit-proof', $invoiceId) : '/extensions/multibankqr/submit-proof/' . $invoiceId }}";
        let pollInterval = null;

        // Switch Bank Account Tabs
        function selectBankAccount(accId) {
            document.querySelectorAll('.bank-panel').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.multibankqr-tab-btn').forEach(b => b.classList.remove('active'));

            const panel = document.getElementById('panel-' + accId);
            const btn = document.getElementById('tab-btn-' + accId);
            if (panel) panel.classList.remove('hidden');
            if (btn) {
                btn.classList.add('active');
                const nameSpan = btn.querySelector('span');
                if (nameSpan) {
                    document.getElementById('selected-bank-name').value = nameSpan.textContent.trim();
                }
            }
        }

        // 1-Click Clipboard Copy with Feedback (Safe DOM handling)
        function copyToClipboard(text, btnElement) {
            navigator.clipboard.writeText(text).then(() => {
                const originalHtml = btnElement.innerHTML;
                btnElement.innerHTML = `
                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-bold text-xs animate-bounce">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Copied!
                    </span>
                `;
                setTimeout(() => {
                    btnElement.innerHTML = originalHtml;
                }, 2000);
            }).catch(err => {
                console.error('Could not copy text: ', err);
            });
        }

        // QR Zoom Lightbox
        function openQrZoomModal(src, title) {
            document.getElementById('qr-modal-img').src = src;
            document.getElementById('qr-modal-title').textContent = title;
            document.getElementById('qr-zoom-modal').classList.remove('hidden');
        }

        function closeQrZoomModal() {
            document.getElementById('qr-zoom-modal').classList.add('hidden');
        }

        // Receipt File Preview
        function previewReceiptFile(input) {
            const file = input.files[0];
            if (!file) return;

            document.getElementById('upload-placeholder').classList.add('hidden');
            const previewCard = document.getElementById('file-preview-card');
            previewCard.classList.remove('hidden');
            previewCard.classList.add('flex');
            document.getElementById('preview-filename').textContent = file.name;

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('image-preview').src = e.target.result;
                    document.getElementById('image-preview').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                document.getElementById('image-preview').src = "https://raw.githubusercontent.com/FortAwesome/Font-Awesome/6.x/svgs/solid/file-pdf.svg";
            }
        }

        // AJAX Proof Submission
        function handleProofSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('proof-submission-form');
            const submitBtn = document.getElementById('submit-proof-btn');
            const btnText = document.getElementById('btn-text');
            const formData = new FormData(form);

            submitBtn.disabled = true;
            btnText.textContent = "Submitting for Verification...";

            fetch(SUBMIT_URL, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.disabled = false;
                btnText.textContent = 'Update Submitted Details';

                if (data.success) {
                    // Update Status to Pending Verification
                    const statusCard = document.getElementById('live-status-card');
                    statusCard.className = 'mb-5 rounded-2xl p-5 border shadow-sm transition-all duration-300 bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-700';

                    document.getElementById('status-icon').innerHTML = `
                        <div class="relative w-7 h-7 flex items-center justify-center">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-4 w-4 bg-amber-500"></span>
                        </div>
                    `;
                    document.getElementById('status-label').textContent = 'Status: Pending Verification';
                    document.getElementById('status-badge').className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-amber-100 text-amber-800 border-amber-300 animate-pulse';
                    document.getElementById('status-badge').textContent = 'Pending Manual Verification';
                    document.getElementById('status-desc').textContent = 'Payment details received! Our admin team is manually verifying the funds against our bank statement. Order will auto-activate upon confirmation.';

                    // Start background polling
                    startPolling();
                } else {
                    alert(data.message || 'An error occurred while submitting proof.');
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                btnText.textContent = 'I Have Transferred — Submit for Verification';
                console.error(err);
                alert('Submission error. Please check your file or message our WhatsApp support.');
            });
        }

        // Live Status Background Poller (every 5 seconds)
        function checkStatusNow() {
            fetch(STATUS_URL, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.is_paid || data.status === 'verified' || data.status === 'approved') {
                    // Stop polling
                    if (pollInterval) clearInterval(pollInterval);

                    const statusCard = document.getElementById('live-status-card');
                    statusCard.className = 'mb-5 rounded-2xl p-6 border shadow-lg transition-all duration-300 bg-emerald-50 dark:bg-emerald-950/50 border-emerald-400 dark:border-emerald-700';
                    statusCard.innerHTML = `
                        <div class="text-center py-4 space-y-2">
                            <span class="text-4xl">🎉</span>
                            <h3 class="text-xl font-black text-emerald-900 dark:text-emerald-100">Payment Verified & Approved!</h3>
                            <p class="text-xs sm:text-sm text-emerald-800 dark:text-emerald-200">
                                Your payment has been manually confirmed by admin. Your services are now <strong>ACTIVE</strong>.
                            </p>
                            <p class="text-xs text-emerald-700 dark:text-emerald-400 font-semibold pt-1">
                                Redirecting to your active service dashboard...
                            </p>
                        </div>
                    `;

                    setTimeout(() => {
                        window.location.href = data.redirect_url || window.location.href;
                    }, 2500);
                } else if (data.status === 'flagged') {
                    const statusCard = document.getElementById('live-status-card');
                    statusCard.className = 'mb-5 rounded-2xl p-5 border shadow-sm transition-all duration-300 bg-sky-50 dark:bg-sky-950/40 border-sky-300 dark:border-sky-700';
                    document.getElementById('status-icon').textContent = '⚠️';
                    document.getElementById('status-label').textContent = 'Status: Action Required';
                    document.getElementById('status-badge').textContent = 'Under Review';
                    document.getElementById('status-desc').textContent = 'Admin Note: ' + (data.admin_notes || 'Please verify your transaction reference.');
                }
            })
            .catch(err => console.error('Status check error:', err));
        }

        function startPolling() {
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(checkStatusNow, 5000);
        }

        // Auto-start polling if already in pending verification state
        @if($isPendingVerif)
            startPolling();
        @endif
    </script>
</div>
