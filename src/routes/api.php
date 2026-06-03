<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AttendanceRecordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    
    Route::apiResource(
        'attendance-records',
        AttendanceRecordController::class
    )->only([
        'index',
        'show',
    ]);

    Route::middleware('auth:sanctum')->group(function () {

        Route::apiResource(
            'attendance-records',
            AttendanceRecordController::class
        )->only([
            'store',
            'update',
            'destroy',
        ]);
    });
});
