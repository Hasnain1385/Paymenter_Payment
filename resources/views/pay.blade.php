@php
    $invoiceId = $invoice->id ?? '';
    $invoiceTotal = number_format((float) ($total ?? ($invoice->remaining ?? $invoice->total)), 2);
    $invoiceCurrency = $currency ?? ($invoice->currency_code ?? ($invoice->currency ?? 'USD'));
    $hasTransaction = !empty($transaction);
@endphp

<div class="multibankqr-container max-w-4xl mx-auto text-gray-800 dark:text-gray-100 font-sans my-4 px-2 sm:px-4">
    <!-- Inline Tailwind & Icon Styles -->
    <style>
        .multibankqr-container {
            --primary-accent: #0284c7;
            --whatsapp-green: #25D366;
            --discord-blurple: #5865F2;
            --telegram-blue: #24A1DE;
        }
        .multibank-tab-btn.active {
            border-color: #0284c7;
            background: linear-gradient(135deg, rgba(2, 132, 199, 0.08), rgba(2, 132, 199, 0.02));
            color: #0284c7;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.12);
        }
        .copy-tooltip {
            transition: opacity 0.2s, transform 0.2s;
        }
        @keyframes pulse-subtle {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.85; transform: scale(1.02); }
        }
        .animate-pulse-subtle {
            animation: pulse-subtle 2s infinite ease-in-out;
        }
    </style>

    <!-- Top Security & Trust Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white rounded-2xl shadow-xl p-5 sm:p-6 mb-6 border border-slate-700/50">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        256-Bit SSL Secured Checkout
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        Verified Merchant
                    </span>
                    <span class="text-xs text-slate-400">Invoice #{{ $invoiceId }}</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white">
                    {{ $gatewayTitle ?? 'Direct Bank Transfer & QR Pay' }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-0.5">
                    Scan the QR code or transfer directly to any receiving account below for instant order settlement.
                </p>
            </div>

            <div class="bg-white/10 backdrop-blur-md rounded-xl p-3.5 sm:px-5 sm:py-3 border border-white/15 text-left sm:text-right w-full sm:w-auto shadow-inner">
                <span class="text-[11px] uppercase tracking-wider text-slate-300 font-semibold block">Total Amount Due</span>
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

    <!-- Live Status Tracker Banner (if transaction exists or is polled) -->
    <div id="live-status-card" class="{{ $hasTransaction ? '' : 'hidden' }} mb-6 rounded-2xl p-5 border shadow-sm transition-all duration-300">
        @if($hasTransaction)
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-start gap-3.5">
                    <div id="status-icon" class="text-3xl shrink-0 mt-0.5">
                        @if($transaction->isApproved())
                            <span class="text-emerald-500">🎉</span>
                        @elseif($transaction->isDeclined())
                            <span class="text-rose-500">❌</span>
                        @elseif($transaction->isFlagged())
                            <span class="text-sky-500">⚠️</span>
                        @else
                            <div class="relative w-7 h-7 flex items-center justify-center">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-amber-500"></span>
                            </div>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span id="status-label" class="font-bold text-base text-gray-900 dark:text-white">
                                Status: {{ $transaction->getStatusLabel() }}
                            </span>
                            @if($transaction->isPending())
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-300 dark:border-amber-700 animate-pulse">
                                    Live Checking
                                </span>
                            @endif
                        </div>
                        <p id="status-desc" class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                            @if($transaction->isApproved())
                                Your payment has been verified and confirmed! Your services are active.
                            @elseif($transaction->isDeclined())
                                Payment declined: {{ $transaction->decline_reason ?? 'Could not verify transaction reference.' }}
                            @elseif($transaction->isFlagged())
                                Action needed: {{ $transaction->admin_notes ?? 'Please review your payment reference.' }}
                            @else
                                Reference: <strong class="font-mono text-gray-800 dark:text-gray-200">{{ $transaction->transaction_id }}</strong> &bull; Submitted by <span class="font-medium">{{ $transaction->sender_name }}</span>. Verifying in real-time...
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    @if($transaction->isPending())
                        <button type="button" onclick="checkStatusNow()" class="w-full sm:w-auto px-3.5 py-1.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg border border-gray-300 dark:border-gray-700 transition">
                            Refresh Status
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- 3-Step Visual Roadmap -->
    <div class="grid grid-cols-3 gap-2 sm:gap-4 mb-6 text-center">
        <div class="bg-white dark:bg-gray-800/80 p-3 sm:p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="w-7 h-7 mx-auto mb-1.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold text-xs">1</div>
            <span class="block text-xs font-bold text-gray-900 dark:text-white">Choose & Scan</span>
            <span class="hidden sm:block text-[11px] text-gray-500 mt-0.5">Select bank & scan QR code</span>
        </div>
        <div class="bg-white dark:bg-gray-800/80 p-3 sm:p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="w-7 h-7 mx-auto mb-1.5 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-bold text-xs">2</div>
            <span class="block text-xs font-bold text-gray-900 dark:text-white">Submit Reference</span>
            <span class="hidden sm:block text-[11px] text-gray-500 mt-0.5">Enter TRX ID or screenshot</span>
        </div>
        <div class="bg-white dark:bg-gray-800/80 p-3 sm:p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="w-7 h-7 mx-auto mb-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300 flex items-center justify-center font-bold text-xs">3</div>
            <span class="block text-xs font-bold text-gray-900 dark:text-white">Instant Activation</span>
            <span class="hidden sm:block text-[11px] text-gray-500 mt-0.5">Auto-activated upon confirmation</span>
        </div>
    </div>

    <!-- Main Payment Section: Bank Selector & Details -->
    @if(!empty($accounts))
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 sm:p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Select Receiving Bank / Account
            </h3>
            <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ count($accounts) }} method(s) available
            </span>
        </div>

        <!-- Bank Tabs -->
        <div class="flex flex-wrap gap-2 pb-2 border-b border-gray-100 dark:border-gray-700/60 mb-6">
            @foreach($accounts as $index => $acc)
                <button type="button"
                    onclick="selectBankAccount('{{ $acc['id'] }}')"
                    id="tab-btn-{{ $acc['id'] }}"
                    class="multibank-tab-btn {{ $index === 0 ? 'active' : '' }} flex items-center gap-2 px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-xs sm:text-sm font-semibold transition bg-gray-50/50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60">
                    <span>{{ $acc['name'] }}</span>
                    @if(!empty($acc['badge']))
                        <span class="px-1.5 py-0.5 text-[10px] rounded-md font-bold bg-sky-100 text-sky-800 dark:bg-sky-900/60 dark:text-sky-200">
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
                    
                    <!-- Left Column: Bank Details & 1-Click Copy -->
                    <div class="lg:col-span-7 space-y-4">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700/50">
                            <div>
                                <h4 class="text-base font-extrabold text-gray-900 dark:text-white">{{ $acc['name'] }}</h4>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Transfer exact amount to the account below</span>
                            </div>
                            @if(!empty($acc['badge']))
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    {{ $acc['badge'] }}
                                </span>
                            @endif
                        </div>

                        <!-- Account Title -->
                        <div class="bg-gray-50 dark:bg-gray-900/50 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Account Title / Holder</span>
                                <span class="text-sm sm:text-base font-bold text-gray-900 dark:text-white break-all" id="title-val-{{ $acc['id'] }}">{{ $acc['account_title'] }}</span>
                            </div>
                            <button type="button" onclick="copyToClipboard('{{ addslashes($acc['account_title']) }}', this)" class="shrink-0 p-2 text-gray-500 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-gray-800 rounded-lg transition" title="Copy Account Title">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>

                        <!-- Account Number / IBAN -->
                        <div class="bg-gray-50 dark:bg-gray-900/50 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Account # / IBAN / Wallet Address</span>
                                <span class="text-sm sm:text-base font-mono font-bold text-gray-900 dark:text-white break-all" id="num-val-{{ $acc['id'] }}">{{ $acc['account_number'] }}</span>
                            </div>
                            <button type="button" onclick="copyToClipboard('{{ addslashes($acc['account_number']) }}', this)" class="shrink-0 p-2 text-gray-500 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-gray-800 rounded-lg transition" title="Copy Account Number">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>

                        <!-- Routing / SWIFT / IFSC (if present) -->
                        @if(!empty($acc['routing_code']))
                        <div class="bg-gray-50 dark:bg-gray-900/50 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Routing / SWIFT / BIC / IFSC / Branch</span>
                                <span class="text-sm sm:text-base font-mono font-bold text-gray-900 dark:text-white break-all">{{ $acc['routing_code'] }}</span>
                            </div>
                            <button type="button" onclick="copyToClipboard('{{ addslashes($acc['routing_code']) }}', this)" class="shrink-0 p-2 text-gray-500 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-gray-800 rounded-lg transition" title="Copy Routing Code">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                        @endif

                        <!-- Reference Note (Important) -->
                        <div class="bg-amber-50/60 dark:bg-amber-950/30 p-3.5 rounded-xl border border-amber-200 dark:border-amber-800/50 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider block">Transfer Reference / Remarks (Required)</span>
                                <span class="text-sm sm:text-base font-mono font-extrabold text-amber-900 dark:text-amber-200">INV-{{ $invoiceId }}</span>
                            </div>
                            <button type="button" onclick="copyToClipboard('INV-{{ $invoiceId }}', this)" class="shrink-0 px-2.5 py-1.5 text-xs font-semibold bg-amber-200/80 dark:bg-amber-800/80 text-amber-900 dark:text-amber-100 hover:bg-amber-300 rounded-lg transition flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span>Copy Reference</span>
                            </button>
                        </div>

                        @if(!empty($acc['instructions']))
                        <div class="text-xs text-gray-600 dark:text-gray-400 italic bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg border border-gray-100 dark:border-gray-700/40">
                            <strong>Note:</strong> {{ $acc['instructions'] }}
                        </div>
                        @endif
                    </div>

                    <!-- Right Column: Interactive QR Code -->
                    <div class="lg:col-span-5 flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 text-center">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            Scan to Pay
                        </span>

                        <div class="relative group bg-white p-3 rounded-2xl shadow-md border border-gray-200">
                            @if(!empty($acc['qr_src']))
                                <img src="{{ $acc['qr_src'] }}" alt="Payment QR Code for {{ $acc['name'] }}" class="w-48 h-48 sm:w-56 sm:h-56 object-contain rounded-xl">
                            @else
                                <div class="w-48 h-48 sm:w-56 sm:h-56 flex flex-col items-center justify-center text-gray-400 text-xs p-4 bg-gray-50 rounded-xl">
                                    <svg class="w-12 h-12 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    <span>Use Account Number</span>
                                </div>
                            @endif
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 max-w-[220px]">
                            Open your mobile banking app or camera to scan and transfer {{ $invoiceCurrency }} {{ $invoiceTotal }}.
                        </p>

                        @if(!empty($acc['qr_payload']) && str_starts_with($acc['qr_payload'], 'upi://'))
                            <a href="{{ $acc['qr_payload'] }}" class="mt-3.5 inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>Pay in UPI App</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <!-- DEAL CLOSER: Prominent WhatsApp & Discord Contact Links -->
    @if(!empty($whatsappUrl) || !empty($discordInvite) || !empty($telegramLink))
    <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-2xl p-6 mb-6 shadow-lg border border-slate-700/60 relative overflow-hidden">
        <!-- Background Glow Accent -->
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
            <div class="max-w-xl">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                        ⚡ Instant Sales & Verification
                    </span>
                    <span class="text-xs text-slate-400">Average response &lt; 5 minutes</span>
                </div>
                <h3 class="text-lg sm:text-xl font-bold tracking-tight text-white">
                    Need Instant Help or Sending Proof of Payment?
                </h3>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">
                    Connect directly with our team on WhatsApp or Discord with your <strong>Invoice #{{ $invoiceId }}</strong>. We verify payments on the spot so you don't have to wait!
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap sm:flex-nowrap gap-3 w-full md:w-auto shrink-0">
                @if(!empty($whatsappUrl))
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2.5 px-5 py-3 rounded-xl font-bold text-sm text-white shadow-md transition transform hover:-translate-y-0.5"
                    style="background-color: #25D366;">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.153.57 4.175 1.564 5.921l-1.636 5.981 6.136-1.609c1.706.929 3.659 1.457 5.736 1.457 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"/></svg>
                    <span>Chat on WhatsApp</span>
                </a>
                @endif

                @if(!empty($discordInvite))
                <a href="{{ $discordInvite }}" target="_blank" rel="noopener noreferrer"
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2.5 px-5 py-3 rounded-xl font-bold text-sm text-white shadow-md transition transform hover:-translate-y-0.5"
                    style="background-color: #5865F2;">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.929 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.894.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>
                    <span>Discord Support</span>
                </a>
                @endif

                @if(!empty($telegramLink))
                <a href="{{ $telegramLink }}" target="_blank" rel="noopener noreferrer"
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-bold text-sm text-white shadow-md transition transform hover:-translate-y-0.5"
                    style="background-color: #24A1DE;">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    <span>Telegram</span>
                </a>
                @endif
            </div>
        </div>

        @if(!empty($discordInstructions) && !empty($discordInvite))
        <div class="mt-3 text-xs text-indigo-200/80">
            <strong>Discord note:</strong> {{ $discordInstructions }}
        </div>
        @endif
    </div>
    @endif

    <!-- Payment Proof Submission Form -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 sm:p-6 mb-6">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/60 mb-5">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Submit Proof of Payment
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Enter the transaction reference from your payment confirmation so we can match and activate your order.
                </p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                Step 2 of 3
            </span>
        </div>

        <!-- Submission Form (AJAX enabled) -->
        <form id="proof-submission-form" onsubmit="handleProofSubmit(event)" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="bank_account_name" id="selected-bank-name" value="{{ !empty($accounts) ? $accounts[0]['name'] : 'Bank Transfer' }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Transaction ID / UTR -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                        Transaction ID / UTR / Reference Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                        name="transaction_id"
                        id="form-trx-id"
                        required
                        value="{{ $transaction ? $transaction->transaction_id : '' }}"
                        placeholder="e.g. TRX98274192, 40182937402, or Reference #"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm font-mono font-medium focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition">
                    <span class="text-[11px] text-gray-500 mt-1 block">Found in your bank receipt or SMS notification.</span>
                </div>

                <!-- Sender Name / Phone -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                        Sender Account Name / Sender Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                        name="sender_name"
                        id="form-sender-name"
                        required
                        value="{{ $transaction ? $transaction->sender_name : ($invoice->user->name ?? '') }}"
                        placeholder="Name of person or company who transferred funds"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition">
                    <span class="text-[11px] text-gray-500 mt-1 block">Matches the name on the sending bank account.</span>
                </div>
            </div>

            <!-- Screenshot / Receipt Upload Dropzone -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                    Payment Receipt / Screenshot Upload {{ $requireProofImage ? '(Required)' : '(Recommended)' }}
                </label>
                <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-sky-500 dark:hover:border-sky-400 rounded-2xl p-4 sm:p-5 text-center transition bg-gray-50/50 dark:bg-gray-900/30">
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
                            <span class="text-sky-600 dark:text-sky-400 underline">Click to upload screenshot</span> or drag & drop
                        </div>
                        <p class="text-[11px] text-gray-500">PNG, JPG, WEBP, or PDF up to 10MB</p>
                    </div>

                    <!-- File Preview Card -->
                    <div id="file-preview-card" class="hidden items-center justify-center gap-3">
                        <img id="image-preview" src="#" alt="Receipt Preview" class="w-14 h-14 object-cover rounded-lg shadow-sm border border-gray-200">
                        <div class="text-left">
                            <span id="preview-filename" class="text-xs font-bold text-gray-800 dark:text-gray-100 block truncate max-w-xs"></span>
                            <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                File attached & ready
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Optional Customer Note -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                    Additional Remarks / Notes (Optional)
                </label>
                <textarea name="customer_notes"
                    rows="2"
                    placeholder="e.g. Sent via mobile app, transaction reference from John."
                    class="w-full px-3.5 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition">{{ $transaction ? $transaction->customer_notes : '' }}</textarea>
            </div>

            <!-- Submit Button with loading state -->
            <div class="pt-2 flex flex-col sm:flex-row items-center gap-3">
                <button type="submit"
                    id="submit-proof-btn"
                    class="w-full sm:w-auto flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-sm rounded-xl shadow-md transition transform active:scale-[0.99] disabled:opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span id="btn-text">{{ $hasTransaction ? 'Update Submitted Proof' : 'I Have Transferred — Confirm Payment' }}</span>
                </button>

                @if(!empty($whatsappUrl))
                <a href="{{ $whatsappUrl }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-emerald-600/30 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 text-xs font-bold transition">
                    <svg class="w-4 h-4 fill-current text-emerald-600" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.153.57 4.175 1.564 5.921l-1.636 5.981 6.136-1.609c1.706.929 3.659 1.457 5.736 1.457 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"/></svg>
                    <span>Also Notify on WhatsApp</span>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Client-Side Scripting: Multi-bank tabs, copy clipboard, file preview & AJAX polling -->
    <script>
        const INVOICE_ID = "{{ $invoiceId }}";
        const STATUS_URL = "{{ function_exists('route') && \Illuminate\Support\Facades\Route::has('multibankqr.status') ? route('multibankqr.status', $invoiceId) : '/extensions/multibankqr/status/' . $invoiceId }}";
        const SUBMIT_URL = "{{ function_exists('route') && \Illuminate\Support\Facades\Route::has('multibankqr.submit-proof') ? route('multibankqr.submit-proof', $invoiceId) : '/extensions/multibankqr/submit-proof/' . $invoiceId }}";
        let pollInterval = null;

        // Switch Bank Account Tabs
        function selectBankAccount(accId) {
            document.querySelectorAll('.bank-panel').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.multibank-tab-btn').forEach(b => b.classList.remove('active'));

            const panel = document.getElementById('panel-' + accId);
            const btn = document.getElementById('tab-btn-' + accId);
            if (panel) panel.classList.remove('hidden');
            if (btn) {
                btn.classList.add('active');
                const nameSpan = btn.querySelector('span');
                if (nameSpan) {
                    document.getElementById('selected-bank-name').value = nameSpan.innerText.trim();
                }
            }
        }

        // 1-Click Clipboard Copy with Feedback
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
                // PDF fallback
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
            btnText.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Submitting & Notifying Team...
            `;

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
                btnText.innerText = 'Update Submitted Proof';

                if (data.success) {
                    // Show live status card
                    const statusCard = document.getElementById('live-status-card');
                    statusCard.className = 'mb-6 rounded-2xl p-5 border shadow-sm transition-all duration-300 bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-700/60';
                    statusCard.classList.remove('hidden');

                    statusCard.innerHTML = `
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div class="flex items-start gap-3.5">
                                <div class="relative w-7 h-7 flex items-center justify-center shrink-0 mt-0.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-amber-500"></span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-base text-gray-900 dark:text-white">Status: Payment Under Review</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 border border-amber-300">
                                            Awaiting Admin Confirmation
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                                        Your payment proof has been successfully delivered. Our team is verifying the transaction reference. Order will auto-activate upon approval.
                                    </p>
                                </div>
                            </div>
                            <button type="button" onclick="checkStatusNow()" class="px-3.5 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg border border-gray-300 dark:border-gray-700 shadow-sm">
                                Check Status
                            </button>
                        </div>
                    `;

                    // Start background polling
                    startPolling();
                } else {
                    alert(data.message || 'An error occurred while submitting proof.');
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                btnText.innerText = 'I Have Transferred — Confirm Payment';
                console.error(err);
                alert('Submission failed. Please check your internet connection or contact us on WhatsApp/Discord.');
            });
        }

        // Live Status Background Poller
        function checkStatusNow() {
            fetch(STATUS_URL, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.is_paid || data.status === 'approved') {
                    // Stop polling
                    if (pollInterval) clearInterval(pollInterval);

                    const statusCard = document.getElementById('live-status-card');
                    statusCard.className = 'mb-6 rounded-2xl p-6 border shadow-lg transition-all duration-300 bg-emerald-50 dark:bg-emerald-950/50 border-emerald-400 dark:border-emerald-700';
                    statusCard.classList.remove('hidden');
                    statusCard.innerHTML = `
                        <div class="text-center py-4 space-y-2">
                            <span class="text-4xl">🎉</span>
                            <h3 class="text-xl font-black text-emerald-900 dark:text-emerald-100">Payment Verified & Approved!</h3>
                            <p class="text-xs sm:text-sm text-emerald-800 dark:text-emerald-200">
                                Your invoice has been marked as <strong>PAID</strong>. Your services are being activated right now.
                            </p>
                            <p class="text-xs text-emerald-700 dark:text-emerald-400 font-semibold pt-1">
                                Redirecting to your invoice dashboard...
                            </p>
                        </div>
                    `;

                    setTimeout(() => {
                        window.location.href = data.redirect_url || window.location.href;
                    }, 2500);
                } else if (data.status === 'flagged') {
                    const statusCard = document.getElementById('live-status-card');
                    statusCard.className = 'mb-6 rounded-2xl p-5 border shadow-sm transition-all duration-300 bg-sky-50 dark:bg-sky-950/40 border-sky-300 dark:border-sky-700';
                    statusCard.classList.remove('hidden');
                    statusCard.innerHTML = `
                        <div class="flex items-start gap-3">
                            <span class="text-2xl mt-0.5">⚠️</span>
                            <div>
                                <span class="font-bold text-base text-sky-900 dark:text-sky-200">Action Required on Payment</span>
                                <p class="text-xs text-sky-800 dark:text-sky-300 mt-0.5">
                                    <strong>Admin Note:</strong> ${data.admin_notes || 'Please verify your transaction reference.'}
                                </p>
                                <p class="text-[11px] text-gray-500 mt-1">Please review the note and re-submit your proof above or reach out on WhatsApp/Discord.</p>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(err => console.error('Status check error:', err));
        }

        function startPolling() {
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(checkStatusNow, 5000);
        }

        // Start polling automatically if a transaction is already under review
        @if($hasTransaction && $transaction->isPending())
            startPolling();
        @endif
    </script>
</div>
