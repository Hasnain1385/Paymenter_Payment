<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Accounts Manager - PakBank QR Gateway</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full p-4 sm:p-8 bg-slate-950 font-sans antialiased text-slate-200">
    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Top Navigation Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-800">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center p-2 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </span>
                    <div>
                        <h1 class="text-xl font-bold text-white">Bank Accounts & Scanned QRs</h1>
                        <p class="text-xs text-slate-400">Add, edit, or remove multiple Pakistani merchant bank accounts & mobile wallets</p>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('multibankqr.admin.transactions') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-emerald-950/60 hover:bg-emerald-900/60 border border-emerald-700/50 text-emerald-300 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Payment History</span>
                </a>

                <a href="{{ route('multibankqr.admin.updates') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-indigo-950/60 hover:bg-indigo-900/60 border border-indigo-700/50 text-indigo-300 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>GitHub Updates & Sync</span>
                </a>

                <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import from Settings</span>
                </button>

                <button onclick="openAddModal()" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg shadow-lg shadow-emerald-950 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>➕ Add Bank Account</span>
                </button>

                <a href="/admin/extensions" class="inline-flex items-center gap-1 px-3 py-2 text-xs font-medium bg-slate-800/80 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700/70 transition">
                    <span>Back to Extensions</span>
                </a>
            </div>
        </div>

        <!-- Success/Error Notification Flash -->
        <div id="alertBox" class="hidden p-4 rounded-xl text-sm font-medium transition-all duration-300"></div>

        @if(session('success'))
            <div class="p-4 rounded-xl text-sm font-medium bg-emerald-950/70 border border-emerald-500/40 text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <!-- Quick Summary Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-slate-900/80 border border-slate-800 rounded-xl p-4 flex items-center justify-between">
                <div>
                    <span class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Total Accounts</span>
                    <p class="text-2xl font-bold text-white mt-1">{{ count($accounts) }}</p>
                </div>
                <div class="p-3 bg-slate-800 rounded-lg text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
            </div>

            <div class="bg-slate-900/80 border border-slate-800 rounded-xl p-4 flex items-center justify-between">
                <div>
                    <span class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Active & Visible</span>
                    <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $accounts->where('is_active', true)->count() }}</p>
                </div>
                <div class="p-3 bg-emerald-950/60 text-emerald-400 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <div class="bg-slate-900/80 border border-slate-800 rounded-xl p-4 flex items-center justify-between">
                <div>
                    <span class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Gateway Version</span>
                    <p class="text-2xl font-bold text-indigo-400 mt-1">v{{ $version }}</p>
                </div>
                <div class="p-3 bg-indigo-950/60 text-indigo-400 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
        </div>

        <!-- Accounts Grid / Table -->
        @if(count($accounts) === 0)
            <div class="bg-slate-900/60 border border-dashed border-slate-800 rounded-2xl p-12 text-center space-y-4">
                <div class="mx-auto w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center text-slate-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">No Bank Accounts Configured in Database</h3>
                    <p class="text-xs text-slate-400 max-w-md mx-auto mt-1">Add your Pakistani bank accounts, Raast ID, Easypaisa, JazzCash, or SadaPay with static scanned QR codes, or import accounts from your extension settings.</p>
                </div>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <button onclick="openAddModal()" class="px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg shadow transition">
                        ➕ Add First Account
                    </button>
                    <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg border border-slate-700 transition">
                        🔄 Import from Gateway Settings
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
                                <button onclick="toggleAccount({{ $acc->id }})" title="Toggle Active Status" class="p-1.5 rounded-lg border transition {{ $acc->is_active ? 'bg-emerald-950/80 border-emerald-700/60 text-emerald-400' : 'bg-slate-800 border-slate-700 text-slate-500' }}">
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
                                    <span class="text-slate-500 block text-[10px] uppercase font-semibold">Account / IBAN / Raast ID</span>
                                    <span class="font-mono font-semibold text-emerald-400 select-all">{{ $acc->account_number }}</span>
                                </div>
                                @if($acc->routing_code)
                                    <div>
                                        <span class="text-slate-500 block text-[10px] uppercase font-semibold">Bank / Branch / Code</span>
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
                            <button onclick='openEditModal(@json($acc))' class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg border border-slate-700 transition">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit</span>
                            </button>

                            <button onclick="deleteAccount({{ $acc->id }}, '{{ addslashes($acc->name) }}')" class="inline-flex items-center justify-center p-1.5 text-xs font-semibold bg-red-950/50 hover:bg-red-900/60 border border-red-800/40 text-red-300 rounded-lg transition" title="Delete Account">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>

    <!-- ADD ACCOUNT MODAL -->
    <div id="addModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="text-emerald-400">➕</span> Add New Bank / Wallet Account
                </h3>
                <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
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
                        <input type="text" name="badge" placeholder="e.g. Instant Raast, 0 Fee, Recommended" value="Instant" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Account Holder Title *</label>
                        <input type="text" name="account_title" required placeholder="e.g. Mirza Hasnain Baig" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Account Number / IBAN / Raast ID *</label>
                        <input type="text" name="account_number" required placeholder="e.g. PK42MEZN... or 03001234567" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white font-mono focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Bank / Branch / Code</label>
                        <input type="text" name="routing_code" placeholder="e.g. Meezan Bank Gulberg" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Currency</label>
                        <input type="text" name="currency" value="PKR" placeholder="PKR or ALL" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" value="1" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Scanned Static QR Code Image (URL or File)</label>
                    <div class="space-y-2">
                        <input type="text" name="qr_image_url" placeholder="https://example.com/meezan_qr.png or storage/..." class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                        <input type="file" name="qr_image_file" accept="image/*" class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Payment Instructions / Notes</label>
                    <input type="text" name="instructions" placeholder="e.g. Please put Invoice ID in remarks and message on WhatsApp" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" id="add_is_active" value="1" checked class="w-4 h-4 rounded text-emerald-600 bg-slate-950 border-slate-800">
                    <label for="add_is_active" class="text-xs text-slate-300 font-medium">Account is Active & Visible to Customers</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-slate-800 rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg shadow-lg">Save Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT ACCOUNT MODAL -->
    <div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="text-indigo-400">✏️</span> Edit Bank Account
                </h3>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Bank / Wallet Name *</label>
                        <input type="text" id="edit_name" name="name" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Badge / Tag</label>
                        <input type="text" id="edit_badge" name="badge" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Account Holder Title *</label>
                        <input type="text" id="edit_account_title" name="account_title" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Account Number / IBAN / Raast ID *</label>
                        <input type="text" id="edit_account_number" name="account_number" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Bank / Branch / Code</label>
                        <input type="text" id="edit_routing_code" name="routing_code" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Currency</label>
                        <input type="text" id="edit_currency" name="currency" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Sort Order</label>
                        <input type="number" id="edit_sort_order" name="sort_order" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Scanned Static QR Code Image (URL or File)</label>
                    <div class="space-y-2">
                        <input type="text" id="edit_qr_image_url" name="qr_image_url" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                        <input type="file" name="qr_image_file" accept="image/*" class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Payment Instructions / Notes</label>
                    <input type="text" id="edit_instructions" name="instructions" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="w-4 h-4 rounded text-indigo-600 bg-slate-950 border-slate-800">
                    <label for="edit_is_active" class="text-xs text-slate-300 font-medium">Account is Active & Visible</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white bg-slate-800 rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg shadow-lg">Update Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- IMPORT MODAL -->
    <div id="importModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="text-emerald-400">🔄</span> Import from Settings
                </h3>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed">
                This will read the bank accounts previously configured in your <strong>Paymenter Gateway Settings</strong> (Account 1 through Account 4) and import them into this database manager so you can add, edit, or remove them freely without losing any configuration.
            </p>
            <form action="{{ route('multibankqr.admin.accounts.import') }}" method="POST" class="pt-2">
                @csrf
                <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 text-xs font-medium text-slate-400 bg-slate-800 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg">Proceed with Import</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function openEditModal(account) {
            document.getElementById('editForm').action = '/admin/multibankqr/accounts/' + account.id + '/update';
            document.getElementById('edit_name').value = account.name || '';
            document.getElementById('edit_badge').value = account.badge || '';
            document.getElementById('edit_account_title').value = account.account_title || '';
            document.getElementById('edit_account_number').value = account.account_number || '';
            document.getElementById('edit_routing_code').value = account.routing_code || '';
            document.getElementById('edit_currency').value = account.currency || 'PKR';
            document.getElementById('edit_sort_order').value = account.sort_order || 0;
            document.getElementById('edit_qr_image_url').value = account.qr_image_url || '';
            document.getElementById('edit_instructions').value = account.instructions || '';
            document.getElementById('edit_is_active').checked = !!account.is_active;

            document.getElementById('editModal').classList.remove('hidden');
        }

        function showAlert(msg, isSuccess = true) {
            const el = document.getElementById('alertBox');
            el.className = `p-4 rounded-xl text-sm font-medium ${isSuccess ? 'bg-emerald-950/80 border border-emerald-500/40 text-emerald-300' : 'bg-red-950/80 border border-red-500/40 text-red-300'}`;
            el.textContent = msg;
            el.classList.remove('hidden');
            setTimeout(() => el.classList.add('hidden'), 4000);
        }

        async function toggleAccount(id) {
            try {
                const res = await fetch(`/admin/multibankqr/accounts/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                if (data.success) {
                    showAlert(data.message, true);
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
                showAlert('Failed to toggle account: ' + e.message, false);
            }
        }

        async function deleteAccount(id, name) {
            if (!confirm(`Are you sure you want to remove the bank account "${name}"?`)) {
                return;
            }

            try {
                const res = await fetch(`/admin/multibankqr/accounts/${id}/delete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                if (data.success) {
                    showAlert(data.message, true);
                    const card = document.getElementById(`account-card-${id}`);
                    if (card) card.remove();
                } else {
                    showAlert(data.message || 'Failed to remove account', false);
                }
            } catch (e) {
                showAlert('Failed to remove account: ' + e.message, false);
            }
        }
    </script>
</body>
</html>
