<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\StaffAttendanceController;
use App\Http\Controllers\StaffRequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    Route::get('/attendance/report', [StaffAttendanceController::class, 'report']);
    Route::get('/attendance/detail/{attendance_id}', [StaffAttendanceController::class, 'show']);
    Route::post('/attendance/detail/{attendance_id}', [StaffAttendanceController::class, 'store']);
});

// 管理者ユーザーページ
Route::name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/admin/attendance/staff/{user_id}', [AdminAttendanceController::class, 'history']);
    Route::get('/admin/attendance/staff/{user_id}/export', [AdminAttendanceController::class, 'export']);
    Route::get('/admin/attendance/{attendance_id}', [AdminAttendanceController::class, 'show']);
    Route::post('/admin/attendance/{attendance_id}', [AdminAttendanceController::class, 'update']);
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index']);
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestController::class, 'show']);
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestController::class, 'update']);
    Route::post('/admin/attendance/export', [AdminAttendanceController::class, 'export']);
});

Route::middleware(['auth'])->get('/stamp_correction_request/list', function (Request $request) {
    return auth()->user()->admin_status
        ? app(AdminRequestController::class)->index($request)
        : app(StaffRequestController::class)->index($request);
});
