<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
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

    Route::post('products/{product}/stock-in',     [StockController::class, 'stockIn']);
    Route::post('products/{product}/stock-out',    [StockController::class, 'stockOut']);
    Route::post('products/{product}/adjust-stock', [StockController::class, 'adjustStock']);
    Route::get('products/{product}/transactions',  [StockController::class, 'transactions']);

    Route::get('dashboard/summary', [DashboardController::class, 'summary']);

    Route::get('notifications',               [NotificationController::class, 'index']);
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead']);

    Route::get('analytics/insights',          [AnalyticsController::class, 'insights']);
    Route::post('analytics/insights/regenerate', [AnalyticsController::class, 'regenerate']);
});
