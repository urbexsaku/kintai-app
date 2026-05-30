<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\StaffAttendanceController;
use App\Http\Controllers\StaffRequestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'store']);

// メール認証機能
// メール未認証ユーザーに認証案内画面を表示
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// メール認証済ユーザーに打刻画面を表示
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // 認証完了

    return redirect()->route('staff.attendance.stamp');
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メール再送処理
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', '認証メールを再送信しました');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// 一般ユーザーページ
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [StaffAttendanceController::class, 'index'])->name('staff.attendance.stamp');
    Route::post('/attendance/clock-in', [StaffAttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [StaffAttendanceController::class, 'clockOut']);
    Route::post('/attendance/break-start', [StaffAttendanceController::class, 'breakStart']);
    Route::post('/attendance/break-end', [StaffAttendanceController::class, 'breakEnd']);
    Route::get('/attendance/list', [StaffAttendanceController::class, 'history']);
    Route::get('/attendance/detail/{attendance_id}', [StaffAttendanceController::class, 'show']);
    Route::post('/attendance/detail/{attendance_id}', [StaffAttendanceController::class, 'store']);
    Route::get('/stamp_correction_request/list', [StaffRequestController::class, 'index']);
});

// 管理者ユーザーページ
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/staff/{user_id}', [AdminAttendanceController::class, 'history']);
    Route::get('/attendance/{attendance_id}', [AdminAttendanceController::class, 'show']);
    Route::post('/attendance/{attendance_id}', [AdminAttendanceController::class, 'update']);
    Route::get('/staff/list', [AdminStaffController::class, 'index']);
    Route::get('/stamp_correction_request/list', [AdminRequestController::class, 'index']);
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestController::class, 'show']);
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestController::class, 'update']);
});