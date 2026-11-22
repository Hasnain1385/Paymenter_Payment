<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Authentication Required - PakPay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="https://raw.githubusercontent.com/Hasnain1385/Paymenter_Payment/main/logo.png">
</head>
<body class="min-h-full flex items-center justify-center p-4 bg-slate-950 font-sans antialiased text-slate-200">
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl text-center space-y-6">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-emerald-950/80 border border-emerald-700/50 flex items-center justify-center p-2 shadow-lg">
            <img src="https://raw.githubusercontent.com/Hasnain1385/Paymenter_Payment/main/logo.png" alt="PakPay" class="w-12 h-12 rounded-xl">
        </div>

        <div class="space-y-2">
            <h1 class="text-xl font-bold text-white tracking-tight">Admin Authentication Required</h1>
            <p class="text-xs text-slate-400 leading-relaxed">
                You must be logged in as an administrator in the <strong>Paymenter Admin Panel</strong> to access PakPay gateway management.
            </p>
        </div>

        <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-xl text-left text-xs space-y-1 text-slate-400">
            <div class="flex items-center justify-between">
                <span>Required Panel:</span>
                <span class="font-mono text-emerald-400">/admin</span>
            </div>
            <div class="flex items-center justify-between">
                <span>Session Status:</span>
                <span class="text-amber-400 font-semibold">Active Admin Session Not Detected</span>
            </div>
        </div>

        <div class="pt-2 space-y-3">
            <a href="/admin/login" class="block w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-950 transition">
                👉 Log in to Paymenter Admin Panel (/admin)
            </a>
            <a href="/admin" class="block w-full py-2 px-4 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs rounded-xl border border-slate-700 transition">
                Return to Admin Dashboard
            </a>
        </div>
    </div>
</body>
</html>