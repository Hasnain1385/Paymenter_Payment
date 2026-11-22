<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History & Verification - PakBank QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="min-h-full p-4 sm:p-8 bg-slate-950 font-sans antialiased text-slate-200">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Top Header & Navigation -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <img src="https://raw.githubusercontent.com/Hasnain1385/Paymenter_Payment/main/logo.png" alt="PakBank" class="w-10 h-10 rounded-xl shadow-md shrink-0">
                <div>
                    <h1 class="text-xl font-bold text-white">Payment History & Verification</h1>
                    <p class="text-xs text-slate-400">Review customer payment proofs, approve or decline transactions with 1 click, and track revenue</p>
                </div>
            </div>

            <!-- Navigation Bar -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('multibankqr.admin.accounts') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Bank Accounts</span>
                </a>

                <a href="{{ route('multibankqr.admin.updates') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>GitHub Updates</span>
                </a>

                <a href="/admin/extensions" class="inline-flex items-center gap-1 px-3 py-2 text-xs font-medium bg-slate-800/80 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700/70 transition">
                    <span>Back to Extensions</span>
                </a>
            </div>
        </div>

        <!-- Alert Notification Box -->
        <div id="statusAlert" class="hidden p-4 rounded-xl text-sm font-medium transition-all duration-300"></div>

        <!-- Metrics Overview Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Pending Verification Card -->
            <div class="bg-slate-900 border {{ $pendingCount > 0 ? 'border-amber-500/50 bg-amber-950/20' : 'border-slate-800' }} rounded-xl p-4 flex items-center justify-between">
                <div>
                    <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Pending Approval</span>
                    <p class="text-2xl font-black {{ $pendingCount > 0 ? 'text-amber-400 animate-pulse' : 'text-slate-300' }} mt-1">
                        {{ $pendingCount }}
                    </p>
                </div>
                <div class="p-3 {{ $pendingCount > 0 ? 'bg-amber-500/20 text-amber-400' : 'bg-slate-800 text-slate-400' }} rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <!-- Total Verified Revenue -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex items-center justify-between">
                <div>
                    <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Verified Volume</span>
                    <p class="text-2xl font-black text-emerald-400 mt-1">
                        PKR {{ number_format((float) $verifiedTotal, 2) }}
                    </p>
                </div>
                <div class="p-3 bg-emerald-950/60 text-emerald-400 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <!-- Total Approved Orders -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex items-center justify-between">
                <div>
                    <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Verified Orders</span>
                    <p class="text-2xl font-black text-white mt-1">{{ $verifiedCount }}</p>
                </div>
                <div class="p-3 bg-slate-800 text-slate-400 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>

            <!-- Total Submissions -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex items-center justify-between">
                <div>
                    <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Total Submissions</span>
                    <p class="text-2xl font-black text-indigo-400 mt-1">{{ $totalCount }}</p>
                </div>
                <div class="p-3 bg-indigo-950/60 text-indigo-400 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
        </div>

        <!-- Filter Tabs & Search Bar -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Filter Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto text-xs font-semibold">
                <a href="{{ route('multibankqr.admin.transactions') }}"
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                    All Submissions ({{ $totalCount }})
                </a>
                <a href="{{ route('multibankqr.admin.transactions', ['status' => 'pending']) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'pending' ? 'bg-amber-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                    <span>Pending Verification</span>
                    @if($pendingCount > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-amber-950 text-amber-200 border border-amber-700">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('multibankqr.admin.transactions', ['status' => 'verified']) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'verified' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                    Verified ({{ $verifiedCount }})
                </a>
                <a href="{{ route('multibankqr.admin.transactions', ['status' => 'declined']) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'declined' ? 'bg-rose-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                    Declined
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('multibankqr.admin.transactions') }}" method="GET" class="flex items-center gap-2">
                @if($currentStatus !== 'all')
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="relative w-full md:w-64">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search Invoice, TRX, Sender..."
                           class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-8 pr-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                    <svg class="w-3.5 h-3.5 text-slate-500 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg border border-slate-700 transition">
                    Filter
                </button>
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            @if(count($transactions) === 0)
                <div class="p-12 text-center space-y-3">
                    <div class="mx-auto w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-slate-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-white">No Payment Records Found</h3>
                    <p class="text-xs text-slate-400">There are no payment submissions matching your current filter criteria.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950/60 text-slate-400 uppercase text-[10px] font-bold border-b border-slate-800">
                            <tr>
                                <th class="px-4 py-3">Invoice</th>
                                <th class="px-4 py-3">Transaction ID (TRX)</th>
                                <th class="px-4 py-3">Sender Details</th>
                                <th class="px-4 py-3">Amount</th>
                                <th class="px-4 py-3">Receipt Proof</th>
                                <th class="px-4 py-3">Submitted At</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            @foreach($transactions as $t)
                                <tr id="row-{{ $t->id }}" class="hover:bg-slate-800/40 transition">
                                    <!-- Invoice # -->
                                    <td class="px-4 py-3.5 font-bold text-white whitespace-nowrap">
                                        <a href="/admin/invoices/{{ $t->invoice_id }}" target="_blank" class="text-indigo-400 hover:underline flex items-center gap-1" title="Open Invoice in Admin">
                                            <span>#{{ $t->invoice_id }}</span>
                                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </td>

                                    <!-- Transaction ID with Copy -->
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5 font-mono">
                                            <span id="trx-{{ $t->id }}" class="font-bold text-emerald-400 select-all">{{ $t->transaction_id }}</span>
                                            <button onclick="copyTrx('trx-{{ $t->id }}', this)" class="text-slate-500 hover:text-white" title="Copy TRX">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            </button>
                                        </div>
                                    </td>

                                    <!-- Sender Details -->
                                    <td class="px-4 py-3.5">
                                        <div class="font-semibold text-slate-200">{{ $t->sender_name }}</div>
                                        @if($t->sender_account)
                                            <div class="text-[11px] text-slate-400 font-mono">{{ $t->sender_account }}</div>
                                        @endif
                                    </td>

                                    <!-- Amount -->
                                    <td class="px-4 py-3.5 font-bold text-white whitespace-nowrap">
                                        <span class="text-slate-400 text-[11px] mr-0.5">{{ $t->currency }}</span>
                                        {{ number_format((float) $t->amount, 2) }}
                                    </td>

                                    <!-- Receipt Proof Image Modal Trigger -->
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @if(!empty($t->proof_image))
                                            <button type="button" onclick="openReceiptModal('/{{ ltrim($t->proof_image, '/') }}', 'Invoice #{{ $t->invoice_id }} - TRX: {{ $t->transaction_id }}')"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-indigo-300 border border-slate-700 transition" title="Inspect Receipt">
                                                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span>View Receipt</span>
                                            </button>
                                        @else
                                            <span class="text-slate-500 text-[11px]">No File Uploaded</span>
                                        @endif
                                    </td>

                                    <!-- Submitted At -->
                                    <td class="px-4 py-3.5 text-slate-400 whitespace-nowrap text-[11px]">
                                        {{ $t->created_at ? $t->created_at->format('M d, Y H:i') : 'N/A' }}
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <span id="badge-{{ $t->id }}">
                                            @if($t->isVerified())
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-950 text-emerald-400 border border-emerald-800">Verified</span>
                                            @elseif($t->isPendingVerification())
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-950 text-amber-400 border border-amber-800">Pending Verification</span>
                                            @elseif($t->isDeclined())
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-950 text-rose-400 border border-rose-800">Declined</span>
                                            @else
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-800 text-slate-300">{{ $t->getStatusLabel() }}</span>
                                            @endif
                                        </span>
                                    </td>

                                    <!-- Action Buttons -->
                                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                        <div id="actions-{{ $t->id }}" class="flex items-center justify-end gap-1.5">
                                            @if($t->isPendingVerification())
                                                <!-- 1-Click Approve Button -->
                                                <button type="button" onclick="approvePayment({{ $t->id }}, {{ $t->invoice_id }})"
                                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow transition active:scale-95"
                                                        title="Verify & Mark Invoice as Paid">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Approve</span>
                                                </button>

                                                <!-- Decline Button -->
                                                <button type="button" onclick="declinePayment({{ $t->id }})"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-950/70 hover:bg-rose-900 border border-rose-800/50 text-rose-300 text-xs transition"
                                                        title="Decline Payment">
                                                    <span>Decline</span>
                                                </button>
                                            @elseif($t->isVerified())
                                                <span class="text-xs text-emerald-400 font-semibold flex items-center justify-end gap-1">
                                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Approved</span>
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-500">Processed</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($transactions->hasPages())
                    <div class="p-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                        {{ $transactions->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>

    <!-- RECEIPT PROOF PREVIEW MODAL -->
    <div id="receiptModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeReceiptModal()">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-2xl w-full p-5 space-y-4 shadow-2xl" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 id="receiptModalTitle" class="text-sm font-bold text-white">Payment Receipt Proof</h3>
                <button onclick="closeReceiptModal()" class="text-slate-400 hover:text-white text-lg font-bold">&times;</button>
            </div>
            <div class="bg-black/40 rounded-xl p-2 flex items-center justify-center overflow-auto max-h-[70vh]">
                <img id="receiptModalImg" src="" alt="Receipt" class="max-w-full max-h-[65vh] object-contain rounded-lg">
            </div>
            <div class="flex items-center justify-between pt-2">
                <a id="receiptDownloadLink" href="" target="_blank" download class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg border border-slate-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Open in Full Size / Download</span>
                </a>
                <button onclick="closeReceiptModal()" class="px-4 py-1.5 text-xs font-medium text-slate-400 bg-slate-800 rounded-lg">Close</button>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function showAlert(msg, isSuccess = true) {
            const el = document.getElementById('statusAlert');
            el.className = `p-4 rounded-xl text-sm font-medium ${isSuccess ? 'bg-emerald-950/80 border border-emerald-500/40 text-emerald-300' : 'bg-rose-950/80 border border-rose-500/40 text-rose-300'}`;
            el.textContent = msg;
            el.classList.remove('hidden');
            setTimeout(() => el.classList.add('hidden'), 5000);
        }

        function copyTrx(id, btn) {
            const text = document.getElementById(id).innerText.trim();
            navigator.clipboard.writeText(text).then(() => {
                const orig = btn.innerHTML;
                btn.innerHTML = `<span class="text-[10px] text-emerald-400 font-bold">✓</span>`;
                setTimeout(() => { btn.innerHTML = orig; }, 1500);
            });
        }

        function openReceiptModal(src, title) {
            document.getElementById('receiptModalImg').src = src;
            document.getElementById('receiptDownloadLink').href = src;
            document.getElementById('receiptModalTitle').innerText = title;
            document.getElementById('receiptModal').classList.remove('hidden');
        }

        function closeReceiptModal() {
            document.getElementById('receiptModal').classList.add('hidden');
        }

        async function approvePayment(transactionId, invoiceId) {
            if (!confirm(`Are you sure you want to approve Invoice #${invoiceId}? This will mark the invoice as PAID and activate the customer's service.`)) {
                return;
            }

            try {
                const res = await fetch(`/admin/multibankqr/transactions/${transactionId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();

                if (data.success) {
                    showAlert(`Invoice #${invoiceId} approved and marked PAID!`, true);
                    // Update row UI to Verified
                    const badge = document.getElementById(`badge-${transactionId}`);
                    const actions = document.getElementById(`actions-${transactionId}`);
                    if (badge) {
                        badge.innerHTML = `<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-950 text-emerald-400 border border-emerald-800">Verified</span>`;
                    }
                    if (actions) {
                        actions.innerHTML = `<span class="text-xs text-emerald-400 font-semibold flex items-center justify-end gap-1"><svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Approved</span></span>`;
                    }
                } else {
                    showAlert(data.message || 'Failed to approve invoice.', false);
                }
            } catch (e) {
                showAlert('Error approving payment: ' + e.message, false);
            }
        }

        async function declinePayment(transactionId) {
            const reason = prompt('Please enter an optional reason for declining this payment proof:');
            if (reason === null) return; // User cancelled

            try {
                const res = await fetch(`/admin/multibankqr/transactions/${transactionId}/decline`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ reason: reason })
                });
                const data = await res.json();

                if (data.success) {
                    showAlert('Payment marked as Declined.', true);
                    const badge = document.getElementById(`badge-${transactionId}`);
                    const actions = document.getElementById(`actions-${transactionId}`);
                    if (badge) {
                        badge.innerHTML = `<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-950 text-rose-400 border border-rose-800">Declined</span>`;
                    }
                    if (actions) {
                        actions.innerHTML = `<span class="text-xs text-rose-400">Declined</span>`;
                    }
                } else {
                    showAlert('Failed to decline payment.', false);
                }
            } catch (e) {
                showAlert('Error declining payment: ' + e.message, false);
            }
        }
    </script>
</body>
</html>
