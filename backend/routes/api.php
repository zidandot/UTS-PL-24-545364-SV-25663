<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/health', function () {
    $status = [
        'database' => 'ok',
        'cache' => 'ok'
    ];
    $isHealthy = true;

    try {
        DB::connection()->getPdo();
    } catch (\Exception $e) {
        $status['database'] = 'error: ' . $e->getMessage();
        $isHealthy = false;

        Log::error('Health check gagal: basis data tidak dapat dihubungi', [
            'action' => 'health_check_db',
            'reason' => $e->getMessage()
        ]);
    }

    try {
        Cache::store()->set('health_check_test', 'active', 10);
        if (Cache::store()->get('health_check_test') !== 'active') {
            throw new \Exception('Cache read/write verification failed');
        }
    } catch (\Exception $e) {
        $status['cache'] = 'error: ' . $e->getMessage();
        $isHealthy = false;
    }

    return response()->json([
        'status' => $isHealthy ? 'healthy' : 'unhealthy',
        'checks' => $status
    ], $isHealthy ? 200 : 503);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('books', BookController::class);
});
