<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Invoice #{{ $invoice->id }}</title>
    <!-- Theme Detection -->
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        body {
            margin: 0;
            padding: 24px 16px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
            box-sizing: border-box;
            transition: background-color 0.2s, color 0.2s;
        }
        .dark body,
        html.dark body {
            background-color: #0b1120;
            color: #f8fafc;
        }
        .mb-topbar {
            max-width: 680px;
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .mb-back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s;
        }
        .mb-back-link:hover {
            color: #0f172a;
        }
        .dark .mb-back-link {
            color: #94a3b8;
        }
        .dark .mb-back-link:hover {
            color: #ffffff;
        }
        .mb-theme-btn {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .dark .mb-theme-btn {
            background: #1e293b;
            border-color: #334155;
            color: #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="mb-topbar">
        <a href="{{ function_exists('route') && \Illuminate\Support\Facades\Route::has('invoices.show') ? route('invoices.show', $invoice->id) : '/invoices/' . $invoice->id }}"
           class="mb-back-link">
            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Invoice</span>
        </a>

        <!-- Dark / Light Mode Toggle Button -->
        <button type="button" onclick="toggleTheme()" class="mb-theme-btn" title="Toggle Theme">
            <span id="themeLabel">Toggle Theme</span>
        </button>
    </div>

    <!-- Main Payment Page Component -->
    @include('multibankqr::pay')

    <script>
        function updateThemeLabel() {
            const isDark = document.documentElement.classList.contains('dark') || 
                (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            const btn = document.getElementById('themeLabel');
            if (btn) btn.innerText = isDark ? '☀️ Light' : '🌙 Dark';
        }

        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
            updateThemeLabel();
        }

        updateThemeLabel();
    </script>
</body>
</html>
