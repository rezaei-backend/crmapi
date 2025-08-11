<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CallCenterController;

Route::prefix('/products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('{id}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store']);
    Route::put('{id}', [ProductController::class, 'update']);
    Route::delete('{id}', [ProductController::class, 'destroy']);
});

Route::prefix('/users')->group(function () {
    Route::post('/block', [UserController::class, 'blockUser']);
    Route::put('/update', [UserController::class, 'updateUserInfo']);
    Route::get('/info', [UserController::class, 'getUserInfo']);
});

Route::prefix('/callcenter')->group(function () {
    Route::post('finance', [CallCenterController::class, 'storeFinance']);
    Route::get('finance', [CallCenterController::class, 'getFinanceList']);
    Route::post('sales-reports', [CallCenterController::class, 'storeSalesReport']);
    Route::get('sales-reports', [CallCenterController::class, 'getSalesList']);
    Route::get('online-visits-manager-log-detail', [CallCenterController::class, 'onlineVisitsManagerLogDetail']);
    Route::post('send-cc-lids-to-admins', [CallCenterController::class, 'sendccLidsToAdmins']);
    Route::post('callcenter-update-lids-calls', [CallCenterController::class, 'callcenterUpdateLidsCalls']);
    Route::post('callcenters-add-call', [CallCenterController::class, 'callcentersAddCall']);
    Route::get('visitinfo', [CallCenterController::class, 'visitinfo']);
    Route::get('online-visits-manager-log', [CallCenterController::class, 'onlineVisitsManagerLog']);
    Route::get('online-visits-manager', [CallCenterController::class, 'onlineVisitsManager']);
    Route::get('online-visits', [CallCenterController::class, 'onlineVisits']);
    Route::get('last-day-online-visits', [CallCenterController::class, 'lastDayOnlineVisits']);
    Route::get('unanswered-calls', [CallCenterController::class, 'unansweredCalls']);
    Route::get('follow-up-calls', [CallCenterController::class, 'followUpCalls']);
    Route::get('customer-source-list', [CallCenterController::class, 'customerSourceList']);
});
