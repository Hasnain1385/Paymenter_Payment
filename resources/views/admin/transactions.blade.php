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

            <!-- Navigation & Live Controls Bar -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Auto-Update Toggle Rule -->
                <label class="inline-flex items-center gap-2 cursor-pointer bg-slate-900 px-3 py-2 rounded-lg border border-slate-800 text-xs hover:border-slate-700 transition" title="Automatically poll for new customer payments and paid invoices">
                    <input type="checkbox" id="trx_auto_refresh_toggle" checked onchange="toggleTrxAutoUpdate(this.checked)" class="w-3.5 h-3.5 text-emerald-600 rounded bg-slate-950 border-slate-700 focus:ring-0">
                    <span class="flex items-center gap-1.5 font-medium text-slate-300">
                        <span id="trx_live_indicator" class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span id="trx_auto_label">Auto-Update (10s)</span>
                    </span>
                </label>

                <!-- Refresh Logs Button -->
                <button type="button" onclick="refreshTrxLogs(true)" id="trx_refresh_btn" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg shadow transition" title="Refresh payment verification logs">
                    <svg id="trx_refresh_icon" class="w-3.5 h-3.5 text-white transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Refresh Logs</span>
                </button>

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
            <div id="card_pending" class="bg-slate-900 border {{ $pendingCount > 0 ? 'border-amber-500/50 bg-amber-950/20' : 'border-slate-800' }} rounded-xl p-4 flex items-center justify-between">
                <div>
                    <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Pending Approval</span>
                    <p id="metric_pending_count" class="text-2xl font-black {{ $pendingCount > 0 ? 'text-amber-400 animate-pulse' : 'text-slate-300' }} mt-1">
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
                    <p id="metric_verified_total" class="text-2xl font-black text-emerald-400 mt-1">
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
                    <p id="metric_verified_count" class="text-2xl font-black text-white mt-1">{{ $verifiedCount }}</p>
                </div>
                <div class="p-3 bg-slate-800 text-slate-400 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>

            <!-- Total Submissions -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex items-center justify-between">
                <div>
                    <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Total Submissions</span>
                    <p id="metric_total_count" class="text-2xl font-black text-indigo-400 mt-1">{{ $totalCount }}</p>
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
                        <tbody id="trx_tbody" class="divide-y divide-slate-800/80">
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
                                        @if(!empty($t->proof_image_path))
                                            <button type="button" onclick="openReceiptModal('{{ $t->proof_image_path }}', 'Invoice #{{ $t->invoice_id }} - TRX: {{ $t->transaction_id }}')"
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
                                            <!-- Log Details Modal Trigger Button -->
                                            <button type="button" onclick='openTrxLogDetailsModal(@json($t))' class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-indigo-300 rounded border border-slate-700 transition" title="Inspect Full Payment Verification Log">
                                                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>Log Details</span>
                                            </button>

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

    <!-- TRANSACTION LOG DETAILS MODAL -->
    <div id="trxLogDetailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/85 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeTrxLogDetailsModal()">
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
                            <span id="trx_log_modal_id" class="text-xs text-slate-400 font-mono">#--</span>
                        </h3>
                        <p class="text-[11px] text-slate-400">Complete audit trail & customer submission details</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span id="trx_log_modal_status_badge" class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-800 text-slate-300">--</span>
                    <button type="button" onclick="closeTrxLogDetailsModal()" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <!-- Invoice Details Tile -->
                <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/80 space-y-1.5">
                    <span class="text-[10px] uppercase font-bold text-slate-500 block tracking-wider">Invoice Details</span>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Invoice:</span>
                        <a id="trx_log_modal_invoice_link" href="#" target="_blank" class="font-bold text-indigo-400 hover:underline inline-flex items-center gap-1">
                            <span id="trx_log_modal_invoice_id">#--</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Amount Due:</span>
                        <span id="trx_log_modal_amount" class="font-bold text-emerald-400 font-mono">PKR --</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Payment Gateway:</span>
                        <span id="trx_log_modal_bank_name" class="text-slate-200 font-medium truncate max-w-[150px]">--</span>
                    </div>
                </div>

                <!-- Customer & Sender Tile -->
                <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/80 space-y-1.5">
                    <span class="text-[10px] uppercase font-bold text-slate-500 block tracking-wider">Customer / Sender</span>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Account Title:</span>
                        <span id="trx_log_modal_sender_name" class="font-semibold text-slate-200 truncate max-w-[150px]">--</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Sender Account:</span>
                        <span id="trx_log_modal_sender_account" class="font-mono text-slate-300 select-all truncate max-w-[150px]">--</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Submitted:</span>
                        <span id="trx_log_modal_submitted_at" class="text-slate-400 truncate max-w-[150px]">--</span>
                    </div>
                </div>
            </div>

            <!-- Transaction Reference Tile (Full Width) -->
            <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/80 flex items-center justify-between gap-3 text-xs">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-500 block tracking-wider">Transaction ID / UTR / Reference</span>
                    <span id="trx_log_modal_trx_id" class="font-mono font-bold text-emerald-400 text-sm select-all">--</span>
                </div>
                <button type="button" onclick="copyTrxLogModalId()" class="px-2.5 py-1 text-[11px] font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 rounded border border-slate-700 transition">
                    <span id="trx_log_modal_copy_text">Copy TRX</span>
                </button>
            </div>

            <!-- Customer Notes if any -->
            <div id="trx_log_modal_notes_box" class="hidden bg-slate-950/40 p-3 rounded-xl border border-slate-800/60 text-xs">
                <span class="text-[10px] uppercase font-bold text-slate-500 block tracking-wider mb-1">Customer Remarks / Notes</span>
                <p id="trx_log_modal_notes" class="text-slate-300 italic"></p>
            </div>

            <!-- Receipt Proof Section -->
            <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/80 text-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Payment Receipt Screenshot</span>
                    <a id="trx_log_modal_receipt_download" href="#" target="_blank" download class="text-[11px] font-semibold text-indigo-400 hover:text-indigo-300 inline-flex items-center gap-1">
                        <span>Open Original Image</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
                <div id="trx_log_modal_receipt_container" class="bg-black/60 rounded-lg p-2 flex items-center justify-center max-h-56 overflow-hidden">
                    <img id="trx_log_modal_receipt_img" src="" alt="Proof Screenshot" class="max-h-52 w-auto object-contain rounded cursor-pointer transition hover:opacity-95" onclick="openReceiptModal(this.src, 'Proof')">
                </div>
                <div id="trx_log_modal_no_receipt" class="hidden text-center py-4 text-slate-500 text-xs">
                    No screenshot receipt was uploaded for this transaction.
                </div>
            </div>

            <!-- Admin Audit Trail -->
            <div id="trx_log_modal_audit_box" class="bg-slate-950/40 p-3 rounded-xl border border-slate-800/60 text-xs space-y-1 text-slate-400">
                <span class="text-[10px] uppercase font-bold text-slate-500 block tracking-wider mb-1">Verification Audit Trail</span>
                <div class="flex items-center justify-between">
                    <span>Reviewed By:</span>
                    <span id="trx_log_modal_reviewer" class="text-slate-300 font-medium">--</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Reviewed At:</span>
                    <span id="trx_log_modal_reviewed_at" class="text-slate-300">--</span>
                </div>
                <div id="trx_log_modal_admin_notes_row" class="hidden pt-1 border-t border-slate-800">
                    <span class="text-slate-400 block mb-0.5">Admin Note:</span>
                    <span id="trx_log_modal_admin_notes" class="text-amber-300 font-medium"></span>
                </div>
                <div id="trx_log_modal_decline_row" class="hidden pt-1 border-t border-slate-800">
                    <span class="text-slate-400 block mb-0.5">Decline Reason:</span>
                    <span id="trx_log_modal_decline_reason" class="text-red-400 font-medium"></span>
                </div>
            </div>

            <!-- Modal Footer Actions -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                <button type="button" onclick="closeTrxLogDetailsModal()" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition">Close</button>
                <div id="trx_log_modal_actions" class="flex items-center gap-2">
                    <button type="button" id="trx_log_modal_approve_btn" onclick="approveFromTrxModal()" class="px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg shadow-md transition">✅ Verify & Mark Invoice Paid</button>
                    <button type="button" id="trx_log_modal_decline_btn" onclick="declineFromTrxModal()" class="px-3 py-2 text-xs font-bold bg-red-950/80 hover:bg-red-900 border border-red-700/60 text-red-300 rounded-lg transition">❌ Decline</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let currentTrxLog = null;
        let trxAutoUpdateTimer = null;
        let knownTrxRowIds = new Set(@json($transactions->pluck('id')));

        function openTrxLogDetailsModal(t) {
            if (!t) return;
            currentTrxLog = t;

            document.getElementById('trx_log_modal_id').textContent = '#' + (t.id || '');
            document.getElementById('trx_log_modal_invoice_id').textContent = '#' + (t.invoice_id || '');
            document.getElementById('trx_log_modal_invoice_link').href = '/admin/invoices/' + (t.invoice_id || '');
            document.getElementById('trx_log_modal_amount').textContent = (t.currency || 'PKR') + ' ' + Number(t.amount || 0).toFixed(2);
            document.getElementById('trx_log_modal_bank_name').textContent = t.bank_account_name || 'Standard Gateway';
            document.getElementById('trx_log_modal_sender_name').textContent = t.sender_name || 'Customer';
            document.getElementById('trx_log_modal_sender_account').textContent = t.sender_account || 'Not specified';
            document.getElementById('trx_log_modal_submitted_at').textContent = t.created_at_human ? (t.created_at_human + ' (' + (t.created_at || '') + ')') : (t.created_at || 'Recently');
            document.getElementById('trx_log_modal_trx_id').textContent = t.transaction_id || 'N/A';

            // Status badge
            const badge = document.getElementById('trx_log_modal_status_badge');
            const isVerif = ['verified', 'approved', 'paid'].includes(t.status);
            const isDecl = ['declined', 'rejected'].includes(t.status);

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
            const notesBox = document.getElementById('trx_log_modal_notes_box');
            const notesText = document.getElementById('trx_log_modal_notes');
            if (t.customer_notes && t.customer_notes.trim()) {
                notesText.textContent = '"' + t.customer_notes.trim() + '"';
                notesBox.classList.remove('hidden');
            } else {
                notesBox.classList.add('hidden');
            }

            // Receipt Proof
            const receiptImg = document.getElementById('trx_log_modal_receipt_img');
            const receiptContainer = document.getElementById('trx_log_modal_receipt_container');
            const noReceipt = document.getElementById('trx_log_modal_no_receipt');
            const downloadLink = document.getElementById('trx_log_modal_receipt_download');
            const proofPath = t.proof_image_path || (t.proof_image ? '/' + t.proof_image.replace(/^\//, '') : null);

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
            document.getElementById('trx_log_modal_reviewer').textContent = t.reviewed_by ? ('Admin #' + t.reviewed_by) : 'Not yet reviewed';
            document.getElementById('trx_log_modal_reviewed_at').textContent = t.reviewed_at || 'Pending';

            const adminNotesRow = document.getElementById('trx_log_modal_admin_notes_row');
            if (t.admin_notes) {
                document.getElementById('trx_log_modal_admin_notes').textContent = t.admin_notes;
                adminNotesRow.classList.remove('hidden');
            } else {
                adminNotesRow.classList.add('hidden');
            }

            const declineRow = document.getElementById('trx_log_modal_decline_row');
            if (t.decline_reason) {
                document.getElementById('trx_log_modal_decline_reason').textContent = t.decline_reason;
                declineRow.classList.remove('hidden');
            } else {
                declineRow.classList.add('hidden');
            }

            // Action Buttons
            const approveBtn = document.getElementById('trx_log_modal_approve_btn');
            const declineBtn = document.getElementById('trx_log_modal_decline_btn');

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

            document.getElementById('trxLogDetailsModal').classList.remove('hidden');
        }

        function closeTrxLogDetailsModal() {
            document.getElementById('trxLogDetailsModal').classList.add('hidden');
            currentTrxLog = null;
        }

        function copyTrxLogModalId() {
            if (!currentTrxLog || !currentTrxLog.transaction_id) return;
            navigator.clipboard.writeText(currentTrxLog.transaction_id).then(() => {
                const btn = document.getElementById('trx_log_modal_copy_text');
                if (btn) {
                    btn.textContent = 'Copied!';
                    setTimeout(() => { btn.textContent = 'Copy TRX'; }, 1500);
                }
            });
        }

        async function approveFromTrxModal() {
            if (!currentTrxLog) return;
            await approvePayment(currentTrxLog.id, currentTrxLog.invoice_id);
            currentTrxLog.status = 'verified';
            openTrxLogDetailsModal(currentTrxLog);
        }

        async function declineFromTrxModal() {
            if (!currentTrxLog) return;
            await declinePayment(currentTrxLog.id);
            currentTrxLog.status = 'declined';
            openTrxLogDetailsModal(currentTrxLog);
        }

        // Live async refresh of payment logs and metric cards
        async function refreshTrxLogs(isManual = false) {
            const icon = document.getElementById('trx_refresh_icon');
            if (icon) icon.classList.add('animate-spin');

            try {
                const currentStatus = '{{ $currentStatus }}';
                let url = '/admin/multibankqr/transactions/api?status=' + encodeURIComponent(currentStatus);
                let res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) {
                    res = await fetch('/extensions/multibankqr/admin/transactions/api?status=' + encodeURIComponent(currentStatus), { headers: { 'Accept': 'application/json' } });
                }

                if (res.ok) {
                    const data = await res.json();
                    if (data.success && Array.isArray(data.transactions)) {
                        renderTrxTable(data.transactions);

                        // Update metrics overview cards
                        const elPending = document.getElementById('metric_pending_count');
                        const elVolume = document.getElementById('metric_verified_total');
                        const elVerified = document.getElementById('metric_verified_count');
                        const elTotal = document.getElementById('metric_total_count');

                        if (elPending) elPending.textContent = data.pendingCount;
                        if (elVolume) elVolume.textContent = 'PKR ' + (data.verifiedTotalFormatted || Number(data.verifiedTotal).toFixed(2));
                        if (elVerified) elVerified.textContent = data.verifiedCount;
                        if (elTotal) elTotal.textContent = data.totalCount;

                        if (isManual) {
                            showAlert('Payment verification logs refreshed!', true);
                        }
                    }
                }
            } catch (e) {
                if (isManual) showAlert('Failed to refresh logs: ' + e.message, false);
            } finally {
                if (icon) icon.classList.remove('animate-spin');
            }
        }

        function renderTrxTable(transactions) {
            const tbody = document.getElementById('trx_tbody');
            if (!tbody || !transactions) return;

            let rowsHtml = '';
            let hasNewSubmission = false;

            transactions.forEach(t => {
                const isNew = !knownTrxRowIds.has(t.id);
                if (isNew) {
                    knownTrxRowIds.add(t.id);
                    hasNewSubmission = true;
                }

                const isVerif = ['verified', 'approved', 'paid'].includes(t.status);
                const isDecl = ['declined', 'rejected'].includes(t.status);

                const badgeHtml = isVerif
                    ? '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-950 text-emerald-400 border border-emerald-800">Verified</span>'
                    : (isDecl ? '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-950 text-rose-400 border border-rose-800">Declined</span>'
                    : '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-950 text-amber-400 border border-amber-800">Pending Verification</span>');

                const proofPath = t.proof_image_path || (t.proof_image ? '/' + t.proof_image.replace(/^\//, '') : null);

                rowsHtml += `
                    <tr id="row-${t.id}" class="hover:bg-slate-800/40 transition ${isNew ? 'bg-emerald-950/40 border-l-4 border-emerald-500' : ''}">
                        <td class="px-4 py-3.5 font-bold text-white whitespace-nowrap">
                            <a href="/admin/invoices/${t.invoice_id}" target="_blank" class="text-indigo-400 hover:underline flex items-center gap-1" title="Open Invoice in Admin">
                                <span>#${t.invoice_id}</span>
                                <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-1.5 font-mono">
                                <span id="trx-${t.id}" class="font-bold text-emerald-400 select-all">${escapeTrxHtml(t.transaction_id)}</span>
                                <button onclick="copyTrx('trx-${t.id}', this)" class="text-slate-500 hover:text-white" title="Copy TRX">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-slate-200">${escapeTrxHtml(t.sender_name || 'Customer')}</div>
                            ${t.sender_account ? `<div class="text-[11px] text-slate-400 font-mono">${escapeTrxHtml(t.sender_account)}</div>` : ''}
                        </td>
                        <td class="px-4 py-3.5 font-bold text-white whitespace-nowrap">
                            <span class="text-slate-400 text-[11px] mr-0.5">${escapeTrxHtml(t.currency)}</span>
                            ${t.amount_formatted || Number(t.amount).toFixed(2)}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            ${proofPath ? `
                                <button type="button" onclick="openReceiptModal('${escapeTrxHtml(proofPath)}', 'Invoice #${t.invoice_id} - TRX: ${escapeTrxHtml(t.transaction_id)}')"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-indigo-300 border border-slate-700 transition" title="Inspect Receipt">
                                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>View Receipt</span>
                                </button>
                            ` : `<span class="text-slate-500 text-[11px]">No File Uploaded</span>`}
                        </td>
                        <td class="px-4 py-3.5 text-slate-400 whitespace-nowrap text-[11px]">
                            ${t.created_at || 'Recently'}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span id="badge-${t.id}">${badgeHtml}</span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <div id="actions-${t.id}" class="flex items-center justify-end gap-1.5">
                                <button type="button" onclick='openTrxLogDetailsModal(${JSON.stringify(t)})' class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-indigo-300 rounded border border-slate-700 transition" title="Inspect Full Payment Verification Log">
                                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Log Details</span>
                                </button>
                                ${!isVerif ? `
                                    <button type="button" onclick="approvePayment(${t.id}, ${t.invoice_id})"
                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow transition active:scale-95"
                                            title="Verify & Mark Invoice as Paid">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Approve</span>
                                    </button>
                                    <button type="button" onclick="declinePayment(${t.id})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-950/70 hover:bg-rose-900 border border-rose-800/50 text-rose-300 text-xs transition"
                                            title="Decline Payment">
                                        <span>Decline</span>
                                    </button>
                                ` : `
                                    <span class="text-xs text-emerald-400 font-semibold flex items-center justify-end gap-1">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Approved</span>
                                    </span>
                                `}
                            </div>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = rowsHtml;

            if (hasNewSubmission) {
                showAlert('🔔 New customer payment received! Table and metrics updated live.', true);
            }
        }

        function escapeTrxHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function toggleTrxAutoUpdate(enable) {
            const ind = document.getElementById('trx_live_indicator');
            const lbl = document.getElementById('trx_auto_label');

            if (enable) {
                if (ind) ind.className = 'w-2 h-2 rounded-full bg-emerald-400 animate-pulse';
                if (lbl) lbl.textContent = 'Auto-Update (10s)';
                startTrxAutoUpdate();
                refreshTrxLogs(false);
            } else {
                if (ind) ind.className = 'w-2 h-2 rounded-full bg-slate-600';
                if (lbl) lbl.textContent = 'Auto-Update (Paused)';
                stopTrxAutoUpdate();
            }
        }

        function startTrxAutoUpdate() {
            stopTrxAutoUpdate();
            trxAutoUpdateTimer = setInterval(() => {
                const toggle = document.getElementById('trx_auto_refresh_toggle');
                if (toggle && toggle.checked && !document.hidden) {
                    refreshTrxLogs(false);
                }
            }, 10000);
        }

        function stopTrxAutoUpdate() {
            if (trxAutoUpdateTimer) {
                clearInterval(trxAutoUpdateTimer);
                trxAutoUpdateTimer = null;
            }
        }

        document.addEventListener('visibilitychange', () => {
            const toggle = document.getElementById('trx_auto_refresh_toggle');
            if (!document.hidden && toggle && toggle.checked) {
                refreshTrxLogs(false);
            }
        });

        // Initialize auto-update rule on load
        startTrxAutoUpdate();

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
