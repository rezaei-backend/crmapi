<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use \App\Http\Controllers\Api\CallCenterController;

Route::prefix('/products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('{id}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store']);
    Route::put('{id}', [ProductController::class, 'update']);
    Route::delete('{id}', [ProductController::class, 'destroy']);
});

//user api
Route::prefix('/users')->group(function () {
    Route::post('/block', [UserController::class, 'blockUser']);
    Route::put('/update', [UserController::class, 'updateUserInfo']);
    Route::get('/info', [UserController::class, 'getUserInfo']);
});

Route::prefix('/callcenter')->group(function () {
    Route::post('finance', [CallCenterController::class, 'storeFinance']);
    Route::post('sales', [CallCenterController::class, 'storeSalesReport']);
    Route::get('finance', [CallCenterController::class, 'getFinanceList']);
    Route::get('sales', [CallCenterController::class, 'getSalesList']);
});

