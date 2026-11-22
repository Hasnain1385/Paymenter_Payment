<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PakPay - Bank Transfer & QR Gateway Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="https://raw.githubusercontent.com/Hasnain1385/Paymenter_Payment/main/logo.png">
</head>
<body class="min-h-full p-4 sm:p-8 bg-slate-950 font-sans antialiased text-slate-200">
    <div class="max-w-7xl mx-auto">
        @include('multibankqr::admin.pakpay-content')
    </div>
</body>
</html>