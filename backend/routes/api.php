<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    $status = ['app' => 'ok', 'database' => 'ok', 'redis' => 'ok'];

    try { DB::connection()->getPdo(); } catch (\Exception $e) {
        $status['database'] = 'error: ' . $e->getMessage();
    }

    try { Redis::ping(); } catch (\Exception $e) {
        $status['redis'] = 'error: ' . $e->getMessage();
    }

    $allOk = collect($status)->every(fn ($v) => $v === 'ok');

    return response()->json([
        'status'    => $allOk ? 'healthy' : 'degraded',
        'services'  => $status,
        'timestamp' => now()->toISOString(),
    ], $allOk ? 200 : 503);
});

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',     [AuthController::class, 'me']);

    Route::apiResource('products',   ProductController::class);
    Route::apiResource('categories', CategoryController::class);
});
