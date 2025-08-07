<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use \App\Http\Controllers\Api\CallCenterController;

Route::prefix('/products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('{id}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store']);
    Route::put('{id}', [ProductController::class, 'update']);
    Route::delete('{id}', [ProductController::class, 'destroy']);
});

Route::prefix('/callcenter')->group(function () {
    Route::post('finance', [CallCenterController::class, 'storeFinance']);
    Route::post('sales', [CallCenterController::class, 'storeSalesReport']);
    Route::get('finance', [CallCenterController::class, 'getFinanceList']);
    Route::get('sales', [CallCenterController::class, 'getSalesList']);
});

