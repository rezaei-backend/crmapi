<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CallCenterController;
use App\Http\Controllers\Api\UserController;

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



Route::prefix('/callCenters')->group(function () {
//    Route::get('/AddFinance', [CallCenterController::class, 'index']);
//    Route::get('{id}', [ProductController::class, 'show']);
//    Route::post('/', [ProductController::class, 'store']);
//    Route::put('{id}', [ProductController::class, 'update']);
//    Route::delete('{id}', [ProductController::class, 'destroy']);
});
