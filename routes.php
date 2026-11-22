<?php

if (!class_exists(\Illuminate\Support\Facades\Route::class)) {
    return;
}

// Paymenter Extension Root Route Entry Point
if (file_exists(__DIR__ . '/routes/web.php')) {
    require_once __DIR__ . '/routes/web.php';
}