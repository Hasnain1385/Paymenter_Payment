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

<div class="w-full max-w-3xl mx-auto space-y-4 font-sans text-slate-800 dark:text-slate-100">

    <!-- 1. Invoice & Amount Due Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800/80">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Invoice #{{ $invoiceId }}</span>
                    @if($isVerified)
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400">Verified</span>
                    @elseif($isPendingVerif)
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-400">Under Verification</span>
                    @else
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">Pending Payment</span>
                    @endif
                </div>
                <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white mt-1">
                    {{ $gatewayTitle ?? 'Bank Transfer & QR Pay' }}
                </h1>
            </div>

            <div class="sm:text-right">
                <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Total Amount</span>
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    <span class="text-sm font-bold text-slate-500 dark:text-slate-400 mr-0.5">{{ $invoiceCurrency }}</span>{{ $invoiceTotal }}
                </span>
            </div>
        </div>

        <!-- 2. WhatsApp & Discord Contact Buttons Directly Below Amount -->
        <div class="pt-4 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            @if(!empty($whatsappPrePayUrl) || !empty($whatsappPhone))
                <a href="{{ $whatsappPrePayUrl ?? ('https://wa.me/' . preg_replace('/[^0-9]/', '', $whatsappPhone)) }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#25D366] hover:bg-[#20ba59] text-white text-xs sm:text-sm font-bold shadow-sm transition active:scale-[0.99]">
                    <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.153.57 4.175 1.564 5.921l-1.636 5.981 6.136-1.609c1.706.929 3.659 1.457 5.736 1.457 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"/></svg>
                    <span>Contact on WhatsApp</span>
                </a>
            @endif

            @if(!empty($discordInvite))
                <a href="{{ $discordInvite }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#5865F2] hover:bg-[#4752c4] text-white text-xs sm:text-sm font-bold shadow-sm transition active:scale-[0.99]">
                    <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.929 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.893.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>
                    <span>Join Discord</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Notice (if specified) -->
    @if(!empty($customNotice))
        <div class="px-4 py-3 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300">
            {{ $customNotice }}
        </div>
    @endif

    <!-- 3. Collapsible Bank Accounts (Accordion - Only One Active at a Time) -->
    <div class="space-y-2">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-1">
            Select Bank / Payment Method
        </h2>

        @if(empty($accounts) || count($accounts) === 0)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 text-center text-slate-500">
                <p class="text-sm font-semibold">No bank accounts currently configured.</p>
                <p class="text-xs mt-1">Please contact support on WhatsApp or Discord for payment details.</p>
            </div>
        @else
            <div id="bankAccordion" class="space-y-2">
                @foreach($accounts as $index => $acc)
                    @php
                        $isDefaultOpen = ($index === 0);
                    @endphp
                    <div id="acc-item-{{ $index }}" class="bg-white dark:bg-slate-900 border {{ $isDefaultOpen ? 'border-emerald-500 dark:border-emerald-600 ring-1 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-800' }} rounded-xl transition duration-200 overflow-hidden shadow-sm">
                        <!-- Accordion Header Button -->
                        <button type="button"
                                onclick="toggleBankAccordion({{ $index }})"
                                class="w-full flex items-center justify-between p-4 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                        {{ $acc['name'] }}
                                    </h3>
                                    @if(!empty($acc['badge']))
                                        <span class="inline-block text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                            {{ $acc['badge'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Accordion Chevron Arrow -->
                            <div class="flex items-center gap-2">
                                <svg id="chevron-{{ $index }}" class="w-4 h-4 text-slate-400 transition-transform duration-200 {{ $isDefaultOpen ? 'rotate-180 text-emerald-600 dark:text-emerald-400' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </button>

                        <!-- Accordion Body: Left details, Right QR -->
                        <div id="body-{{ $index }}" class="{{ $isDefaultOpen ? 'block' : 'hidden' }} border-t border-slate-100 dark:border-slate-800/80 p-5 bg-slate-50/50 dark:bg-slate-950/40">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">

                                <!-- LEFT COLUMN: Account Details & Copy Buttons -->
                                <div class="space-y-3">
                                    <!-- Account Title -->
                                    <div>
                                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Account Title</span>
                                        <span class="text-sm font-bold text-slate-800 dark:text-slate-100 select-all">{{ $acc['account_title'] }}</span>
                                    </div>

                                    <!-- Account Number / IBAN / Raast ID with Copy Button -->
                                    <div>
                                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Account Number / IBAN / Raast ID</span>
                                        <div class="mt-1 flex items-center gap-2">
                                            <span id="acc-num-{{ $index }}" class="font-mono text-sm font-extrabold text-emerald-600 dark:text-emerald-400 bg-white dark:bg-slate-900 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 select-all">
                                                {{ $acc['account_number'] }}
                                            </span>
                                            <button type="button"
                                                    onclick="copyText('acc-num-{{ $index }}', this)"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                                    title="Copy Account Number">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                <span>Copy</span>
                                            </button>
                                        </div>
                                    </div>

                                    @if(!empty($acc['routing_code']))
                                        <div>
                                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Bank / Branch</span>
                                            <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">{{ $acc['routing_code'] }}</span>
                                        </div>
                                    @endif

                                    <!-- Payment Reference / Remarks -->
                                    <div>
                                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Transfer Remarks / Reference</span>
                                        <div class="mt-1 flex items-center gap-2">
                                            <span id="ref-num-{{ $index }}" class="font-mono text-xs font-bold text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-900 px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-800 select-all">INV-{{ $invoiceId }}</span>
                                            <button type="button"
                                                    onclick="copyText('ref-num-{{ $index }}', this)"
                                                    class="text-[11px] font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white transition">
                                                Copy
                                            </button>
                                        </div>
                                    </div>

                                    @if(!empty($acc['instructions']))
                                        <div class="text-xs text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-900 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800">
                                            {{ $acc['instructions'] }}
                                        </div>
                                    @endif
                                </div>

                                <!-- RIGHT COLUMN: Scanned Static QR Code -->
                                <div class="flex flex-col items-center justify-center p-3 text-center">
                                    @if(!empty($acc['qr_src']))
                                        <div class="p-2 bg-white rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 cursor-pointer group"
                                             onclick="openQrModal('{{ $acc['qr_src'] }}', '{{ addslashes($acc['name']) }}')"
                                             title="Click to view full size">
                                            <img src="{{ $acc['qr_src'] }}"
                                                 alt="{{ $acc['name'] }} QR"
                                                 class="w-36 h-36 object-contain rounded-lg group-hover:opacity-90 transition">
                                        </div>
                                        <button type="button"
                                                onclick="openQrModal('{{ $acc['qr_src'] }}', '{{ addslashes($acc['name']) }}')"
                                                class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 mt-2 transition">
                                            Scan or Click to Enlarge
                                        </button>
                                    @else
                                        <div class="w-36 h-36 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 flex flex-col items-center justify-center text-slate-400 text-xs p-3">
                                            <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                            <span>Manual Transfer</span>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- 4. Payment Confirmation / Proof Submission Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-sm">
        @if($isVerified)
            <!-- Verified State -->
            <div class="text-center py-4 space-y-2">
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Payment Verified</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Your transfer has been verified and this invoice is now marked as paid.</p>
                <div class="pt-2">
                    <a href="/invoices/{{ $invoiceId }}" class="inline-flex items-center gap-1 px-4 py-2 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-500 transition">
                        View Invoice
                    </a>
                </div>
            </div>
        @elseif($isPendingVerif)
            <!-- Pending Verification State -->
            <div class="space-y-4">
                <div class="flex items-start gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-900 dark:text-amber-200">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/60 rounded-lg shrink-0 mt-0.5">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </div>
                    <div class="flex-1 text-xs">
                        <h4 class="font-bold text-sm">Payment Details Under Verification</h4>
                        <p class="mt-0.5">Your payment details (Transaction ID: <strong class="font-mono">{{ $transaction->transaction_id }}</strong>) have been received. We will verify and activate your order shortly.</p>
                        <p class="text-[11px] text-amber-700 dark:text-amber-400 mt-2">This page updates automatically upon verification.</p>
                    </div>
                </div>

                @if(!empty($whatsappPostPayUrl))
                    <div class="text-center pt-1">
                        <a href="{{ $whatsappPostPayUrl }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl bg-[#25D366] hover:bg-[#20ba59] text-white shadow-sm transition">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.153.57 4.175 1.564 5.921l-1.636 5.981 6.136-1.609c1.706.929 3.659 1.457 5.736 1.457 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"/></svg>
                            <span>Send Screenshot on WhatsApp</span>
                        </a>
                    </div>
                @endif
            </div>
        @else
            <!-- Initial Submission Form -->
            <form action="{{ route('multibankqr.submit-proof', $invoiceId) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Confirm Payment</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Please provide your transfer details after sending payment.</p>
                </div>

                @if($errors->any())
                    <div class="p-3 rounded-lg bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 text-xs text-red-600 dark:text-red-400 space-y-1">
                        @foreach($errors->all() as $err)
                            <p>• {{ $err }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Transaction ID -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Transaction ID / Reference (TRX / UTR) *
                        </label>
                        <input type="text"
                               name="transaction_id"
                               required
                               placeholder="e.g. 1928374652"
                               class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white font-mono focus:outline-none focus:border-emerald-500 dark:focus:border-emerald-500">
                    </div>

                    <!-- Sender Name / Title -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Sender Name or Mobile Number *
                        </label>
                        <input type="text"
                               name="sender_name"
                               required
                               placeholder="e.g. Muhammad Ali or 03001234567"
                               class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 dark:focus:border-emerald-500">
                    </div>
                </div>

                <!-- Proof Screenshot Upload -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Payment Screenshot / Receipt {{ $requireProofImage ? '*' : '(Optional)' }}
                    </label>
                    <input type="file"
                           name="proof_image"
                           {{ $requireProofImage ? 'required' : '' }}
                           accept="image/jpeg,image/png,image/webp,application/pdf"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-200 hover:file:bg-slate-200 dark:hover:file:bg-slate-700">
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm shadow-sm transition active:scale-[0.99]">
                        <span>Submit Payment Proof</span>
                    </button>
                </div>
            </form>
        @endif
    </div>

</div>

<!-- Lightbox Modal for Enlargeable QR Image -->
<div id="qrModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeQrModal()">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-sm w-full p-6 text-center space-y-4 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
            <h4 id="qrModalTitle" class="text-sm font-bold text-slate-900 dark:text-white truncate">Bank QR Code</h4>
            <button type="button" onclick="closeQrModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold">&times;</button>
        </div>
        <div class="p-3 bg-white rounded-xl border border-slate-200 inline-block shadow-inner">
            <img id="qrModalImg" src="" alt="QR" class="w-56 h-56 object-contain rounded-lg mx-auto">
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400">Scan this QR code from your banking or wallet app.</p>
    </div>
</div>

<script>
    // Copy Text to Clipboard with Visual Feedback
    function copyText(elementId, btn) {
        const text = document.getElementById(elementId).innerText.trim();
        navigator.clipboard.writeText(text).then(() => {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = `<span class="text-emerald-600 dark:text-emerald-400 font-bold">Copied!</span>`;
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 1800);
        }).catch(() => {
            alert('Copied: ' + text);
        });
    }

    // Toggle Bank Accordion: Only ONE bank item expanded at a time
    function toggleBankAccordion(selectedIndex) {
        const totalItems = {{ count($accounts ?? []) }};
        for (let i = 0; i < totalItems; i++) {
            const body = document.getElementById(`body-${i}`);
            const chevron = document.getElementById(`chevron-${i}`);
            const item = document.getElementById(`acc-item-${i}`);

            if (!body) continue;

            if (i === selectedIndex) {
                // Expand selected
                body.classList.remove('hidden');
                body.classList.add('block');
                chevron.classList.add('rotate-180', 'text-emerald-600', 'dark:text-emerald-400');
                item.classList.add('border-emerald-500', 'dark:border-emerald-600', 'ring-1', 'ring-emerald-500/20');
                item.classList.remove('border-slate-200', 'dark:border-slate-800');
            } else {
                // Collapse others
                body.classList.add('hidden');
                body.classList.remove('block');
                chevron.classList.remove('rotate-180', 'text-emerald-600', 'dark:text-emerald-400');
                item.classList.remove('border-emerald-500', 'dark:border-emerald-600', 'ring-1', 'ring-emerald-500/20');
                item.classList.add('border-slate-200', 'dark:border-slate-800');
            }
        }
    }

    // Lightbox QR Zoom
    function openQrModal(src, title) {
        document.getElementById('qrModalImg').src = src;
        document.getElementById('qrModalTitle').innerText = title;
        document.getElementById('qrModal').classList.remove('hidden');
    }

    function closeQrModal() {
        document.getElementById('qrModal').classList.add('hidden');
    }

    // Status Polling for Pending Verification
    @if($isPendingVerif)
    const pollInterval = setInterval(async () => {
        try {
            const res = await fetch('{{ route("multibankqr.status", $invoiceId) }}', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.status === 'verified' || data.invoice_status === 'paid') {
                clearInterval(pollInterval);
                window.location.reload();
            }
        } catch (e) {
            // Ignore temporary network drop
        }
    }, 5000);
    @endif
</script>
