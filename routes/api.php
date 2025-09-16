<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CallCenterController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DiseasesController;
use App\Http\Controllers\Api\ReservationController;

/*
|--------------------------------------------------------------------------
| Auth Routes (Admins)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

/*
|--------------------------------------------------------------------------
| Products Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/products')->middleware(['auth:sanctum', 'admin.auth'])->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('{id}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store']);
    Route::put('{id}', [ProductController::class, 'update']);
    Route::delete('{id}', [ProductController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Users Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/users')->middleware(['auth:sanctum', 'admin.auth'])->group(function () {
    Route::post('/block', [UserController::class, 'blockUser']);
    Route::put('/update', [UserController::class, 'updateUserInfo']);
    Route::get('/info', [UserController::class, 'getUserInfo']);
});

/*
|--------------------------------------------------------------------------
| CallCenter Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/callcenter')->middleware(['auth:sanctum', 'admin.auth'])->group(function () {
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

/*
|--------------------------------------------------------------------------
| Diseases Categories Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/diseases-categories')->middleware(['auth:sanctum', 'admin.auth'])->group(function () {
    Route::get('/', [DiseasesController::class, 'getCategories']);
    Route::post('/', [DiseasesController::class, 'addCategory']);
    Route::put('{id}', [DiseasesController::class, 'updateCategory']);
    Route::delete('{id}', [DiseasesController::class, 'deleteCategory']);
});

/*
|--------------------------------------------------------------------------
| Diseases Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/diseases')->middleware(['auth:sanctum', 'admin.auth'])->group(function () {
    Route::get('/', [DiseasesController::class, 'getDiseases']);
    Route::post('/', [DiseasesController::class, 'addDisease']);
    Route::put('{id}', [DiseasesController::class, 'updateDisease']);
    Route::delete('{id}', [DiseasesController::class, 'deleteDisease']);
});

/*
|--------------------------------------------------------------------------
| Reservations Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/reservations')->middleware(['auth:sanctum', 'admin.auth'])->group(function () {
    Route::get('report', [ReservationController::class, 'reservationsReport']);
    Route::get('7days-state', [ReservationController::class, 'get7DaysState']);
    Route::get('7days-state2', [ReservationController::class, 'get7DaysState2']);
    Route::get('slot-details', [ReservationController::class, 'fetchSlotDetails']);
    Route::get('7days', [ReservationController::class, 'get7Days']);
    Route::get('user-info', [ReservationController::class, 'getUserInfo']);
    Route::get('info/{id}', [ReservationController::class, 'reservationInfo']);
    Route::get('visited', [ReservationController::class, 'reservationsVisited']);
    Route::get('not-visited', [ReservationController::class, 'reservationsNotVisited']);
    Route::post('del-m', [ReservationController::class, 'delMReservation']);
    Route::post('del', [ReservationController::class, 'delReservation']);
    Route::post('add-m', [ReservationController::class, 'addMReservation']);
    Route::post('add', [ReservationController::class, 'addReservation']);
    Route::post('update-info', [ReservationController::class, 'updateReservationInfo']);
});
