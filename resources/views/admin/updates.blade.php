<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub Updates & In-Place Sync - PakBank QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="min-h-full p-4 sm:p-8 bg-slate-950 font-sans antialiased text-slate-200">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Top Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <img src="https://raw.githubusercontent.com/Hasnain1385/Paymenter_Payment/main/logo.png" alt="PakBank" class="w-10 h-10 rounded-xl shadow-md shrink-0">
                <div>
                    <h1 class="text-xl font-bold text-white">GitHub In-Place Updates & Sync</h1>
                    <p class="text-xs text-slate-400">Check for upstream updates, pull directly in-place, or push commits to GitHub</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('multibankqr.admin.transactions') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-emerald-950/60 hover:bg-emerald-900/60 border border-emerald-700/50 text-emerald-300 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Payment History</span>
                </a>
                <a href="{{ route('multibankqr.admin.accounts') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Bank Accounts</span>
                </a>
                <a href="/admin/extensions" class="inline-flex items-center gap-1 px-3 py-2 text-xs font-medium bg-slate-800/80 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700/70 transition">
                    <span>Back to Extensions</span>
                </a>
            </div>
        </div>

        <!-- Alert Notification -->
        <div id="statusAlert" class="hidden p-4 rounded-xl text-sm font-medium transition-all duration-300"></div>

        <!-- Version & Update Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-6 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Target GitHub Repository</span>
                    <a href="https://github.com/Hasnain1385/Paymenter_Payment" target="_blank" class="text-sm font-semibold text-indigo-400 hover:underline flex items-center gap-1 mt-0.5">
                        <span>Hasnain1385/Paymenter_Payment</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Current Installed</span>
                        <span class="text-base font-bold text-white font-mono">v{{ $version }}</span>
                    </div>
                    <div class="h-8 w-px bg-slate-800"></div>
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Latest on GitHub</span>
                        <span id="latestVersionBadge" class="text-base font-bold text-indigo-400 font-mono">
                            {{ $updateInfo['latest_version'] ? 'v' . $updateInfo['latest_version'] : 'Checking...' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Update Status Banner -->
            <div id="updateBanner" class="p-4 rounded-xl border {{ $updateInfo['has_update'] ? 'bg-amber-950/60 border-amber-600/50 text-amber-200' : 'bg-emerald-950/50 border-emerald-600/40 text-emerald-200' }}">
                <div class="flex items-start gap-3">
                    <span id="updateIcon" class="p-2 rounded-lg {{ $updateInfo['has_update'] ? 'bg-amber-900/60 text-amber-300' : 'bg-emerald-900/60 text-emerald-300' }}">
                        @if($updateInfo['has_update'])
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </span>
                    <div class="flex-1">
                        <h4 id="updateTitle" class="text-sm font-bold">
                            {{ $updateInfo['has_update'] ? '🚀 Update Available: ' . $updateInfo['release_name'] : '✅ Extension is Up-to-Date' }}
                        </h4>
                        <p id="updateDescription" class="text-xs text-slate-300 mt-1">
                            {{ $updateInfo['release_notes'] ?: 'You are currently running the latest published release.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <button id="btnCheck" onclick="checkForUpdates()" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 rounded-xl transition">
                    <svg id="spinnerCheck" class="hidden w-4 h-4 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>🔄 Check GitHub for Updates</span>
                </button>

                <button id="btnApply" onclick="applyUpdateInPlace()" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl shadow-lg shadow-emerald-950 transition">
                    <svg id="spinnerApply" class="hidden w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>⚡ Update In-Place from GitHub</span>
                </button>
            </div>
        </div>

        <!-- Git Status & In-Place Push Section -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-5 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="text-indigo-400">🐙</span>
                    <h3 class="text-sm font-bold text-white">Git In-Place Push & Synchronization</h3>
                </div>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md {{ $updateInfo['is_git_repo'] ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-slate-800 text-slate-400' }}">
                    {{ $updateInfo['is_git_repo'] ? 'Git Repo Active' : 'Standalone ZIP Mode' }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs bg-slate-950/70 rounded-xl p-4 border border-slate-800">
                <div>
                    <span class="text-slate-500 block text-[10px] uppercase font-semibold">Active Branch</span>
                    <span class="font-mono text-slate-200 font-semibold">{{ $updateInfo['git_status']['branch'] ?? 'main' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block text-[10px] uppercase font-semibold">Local Commit</span>
                    <span class="font-mono text-emerald-400 font-semibold">{{ $updateInfo['git_status']['local_commit'] ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block text-[10px] uppercase font-semibold">Modified Files</span>
                    <span class="font-semibold {{ ($updateInfo['git_status']['modified_count'] ?? 0) > 0 ? 'text-amber-400' : 'text-slate-400' }}">
                        {{ $updateInfo['git_status']['modified_count'] ?? 0 }} changed
                    </span>
                </div>
            </div>

            <!-- Commit & Push Form -->
            <div class="space-y-3">
                <label class="block text-xs font-semibold text-slate-300">Commit Message for Push:</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" id="commitMessage" placeholder="e.g. Added new Meezan Bank QR and updated WhatsApp contact" value="Update from Paymenter Admin Panel" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    <button id="btnPush" onclick="pushGitUpdates()" class="inline-flex items-center justify-center gap-2 px-5 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl shadow transition">
                        <svg id="spinnerPush" class="hidden w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>🚀 Push to GitHub In-Place</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Live Terminal / Console Output -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span class="font-mono font-semibold uppercase tracking-wider text-[10px]">Operation Output Console</span>
                <button onclick="document.getElementById('consoleLog').textContent = '> Ready.'" class="text-slate-500 hover:text-slate-300 text-[10px]">Clear</button>
            </div>
            <pre id="consoleLog" class="bg-slate-950 rounded-xl p-4 font-mono text-xs text-slate-300 overflow-x-auto border border-slate-800/80 min-h-[100px] leading-relaxed">> System initialized. Ready to check or apply in-place GitHub updates.</pre>
        </div>

    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function appendLog(line) {
            const el = document.getElementById('consoleLog');
            const time = new Date().toLocaleTimeString();
            el.textContent += `\n[${time}] ${line}`;
            el.scrollTop = el.scrollHeight;
        }

        function showAlert(msg, isSuccess = true) {
            const el = document.getElementById('statusAlert');
            el.className = `p-4 rounded-xl text-sm font-medium ${isSuccess ? 'bg-emerald-950/80 border border-emerald-500/40 text-emerald-300' : 'bg-red-950/80 border border-red-500/40 text-red-300'}`;
            el.textContent = msg;
            el.classList.remove('hidden');
        }

        async function checkForUpdates() {
            const btn = document.getElementById('btnCheck');
            const spinner = document.getElementById('spinnerCheck');
            btn.disabled = true;
            spinner.classList.remove('hidden');
            appendLog('Querying GitHub API (Hasnain1385/Paymenter_Payment)...');

            try {
                const res = await fetch('{{ route("multibankqr.admin.updates.check") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                appendLog(`Remote version found: v${data.latest_version}`);

                document.getElementById('latestVersionBadge').textContent = 'v' + data.latest_version;
                const banner = document.getElementById('updateBanner');

                if (data.has_update) {
                    banner.className = 'p-4 rounded-xl border bg-amber-950/60 border-amber-600/50 text-amber-200';
                    document.getElementById('updateTitle').textContent = `🚀 Update Available: ${data.release_name}`;
                    document.getElementById('updateDescription').textContent = data.release_notes || 'A newer version is ready for installation.';
                    showAlert(`A new update (v${data.latest_version}) is available on GitHub!`, true);
                    appendLog(`Update available: Current v${data.current_version} -> Latest v${data.latest_version}`);
                } else {
                    banner.className = 'p-4 rounded-xl border bg-emerald-950/50 border-emerald-600/40 text-emerald-200';
                    document.getElementById('updateTitle').textContent = '✅ Extension is Up-to-Date';
                    document.getElementById('updateDescription').textContent = 'You are running the latest release (v' + data.current_version + ').';
                    showAlert('You are already on the latest version.', true);
                    appendLog('All files are up-to-date.');
                }
            } catch (e) {
                appendLog('Error checking GitHub: ' + e.message);
                showAlert('Failed to check updates: ' + e.message, false);
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
            }
        }

        async function applyUpdateInPlace() {
            if (!confirm('Apply in-place update from GitHub? This will overwrite the extension files in place with the latest version.')) {
                return;
            }

            const btn = document.getElementById('btnApply');
            const spinner = document.getElementById('spinnerApply');
            btn.disabled = true;
            spinner.classList.remove('hidden');
            appendLog('Starting in-place update from GitHub...');

            try {
                const res = await fetch('{{ route("multibankqr.admin.updates.apply") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();

                if (data.success) {
                    appendLog('✅ ' + data.message);
                    if (data.output) appendLog(data.output);
                    showAlert(data.message, true);
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    appendLog('❌ ' + data.message);
                    showAlert(data.message, false);
                }
            } catch (e) {
                appendLog('Error applying update: ' + e.message);
                showAlert('Update failed: ' + e.message, false);
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
            }
        }

        async function pushGitUpdates() {
            const msgInput = document.getElementById('commitMessage');
            const message = msgInput.value.trim() || 'Update from Paymenter Admin';

            if (!confirm(`Commit local changes and push to GitHub origin main?\n\nMessage: "${message}"`)) {
                return;
            }

            const btn = document.getElementById('btnPush');
            const spinner = document.getElementById('spinnerPush');
            btn.disabled = true;
            spinner.classList.remove('hidden');
            appendLog(`Initiating Git add, commit, and push in-place: "${message}"...`);

            try {
                const res = await fetch('{{ route("multibankqr.admin.updates.push") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ commit_message: message })
                });
                const data = await res.json();

                if (data.success) {
                    appendLog('✅ ' + data.message);
                    if (data.output) appendLog(data.output);
                    showAlert(data.message, true);
                } else {
                    appendLog('⚠️ ' + data.message);
                    if (data.output) appendLog(data.output);
                    showAlert(data.message, false);
                }
            } catch (e) {
                appendLog('Error pushing to GitHub: ' + e.message);
                showAlert('Push failed: ' + e.message, false);
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
