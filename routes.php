<?php

// Standalone PSR-4 autoloader: ensures extension classes load reliably across any Paymenter core updates
spl_autoload_register(function ($class) {
    $prefix = 'Paymenter\\Extensions\\Gateways\\MultiBankQR\\';
    if (str_starts_with($class, $prefix)) {
        $relativeClass = substr($class, strlen($prefix));
        $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

if (!class_exists(\Illuminate\Support\Facades\Route::class)) {
    return;
}

// Paymenter Extension Root Route Entry Point
if (file_exists(__DIR__ . '/routes/web.php')) {
    require_once __DIR__ . '/routes/web.php';
}