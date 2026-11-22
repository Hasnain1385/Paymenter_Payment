<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100 dark:bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Invoice #{{ $invoice->id }} - {{ $gatewayTitle ?? 'Direct Bank Transfer & QR Pay' }}</title>
    <!-- Tailwind CSS CDN for Standalone View -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-full py-6 sm:py-10 px-2 sm:px-6">
    <div class="max-w-4xl mx-auto">
        <!-- Return Link to Client Area / Invoice -->
        <div class="mb-4 flex items-center justify-between px-2">
            <a href="{{ function_exists('route') && \Illuminate\Support\Facades\Route::has('invoices.show') ? route('invoices.show', $invoice->id) : '/invoices/' . $invoice->id }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Invoice</span>
            </a>
            <span class="text-xs text-slate-400">Order Reference: INV-{{ $invoice->id }}</span>
        </div>

        @include('multibankqr::pay')
    </div>
</body>
</html>
