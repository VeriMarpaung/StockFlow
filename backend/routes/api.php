<?php

// routes/api.php
// Tambahkan route ini ke file routes/api.php Laravel kamu

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

// Health check endpoint — untuk verifikasi Docker setup
Route::get('/health', function () {
    $status = [
        'app'      => 'ok',
        'database' => 'ok',
        'redis'    => 'ok',
    ];

    try {
        DB::connection()->getPdo();
    } catch (\Exception $e) {
        $status['database'] = 'error: ' . $e->getMessage();
    }

    try {
        Redis::ping();
    } catch (\Exception $e) {
        $status['redis'] = 'error: ' . $e->getMessage();
    }

    $allOk = collect($status)->every(fn($v) => $v === 'ok');

    return response()->json([
        'status'   => $allOk ? 'healthy' : 'degraded',
        'services' => $status,
        'timestamp' => now()->toISOString(),
    ], $allOk ? 200 : 503);
});
