<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AdminStaffController;
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

Route::get('/admin/login', [AdminAttendanceController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAttendanceController::class, 'store']);

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


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [StaffAttendanceController::class, 'index'])->name('staff.attendance.stamp');
    Route::get('/attendance/list', [StaffAttendanceController::class, 'show']);
    Route::get('/attendance/detail/{user_id}', [StaffAttendanceController::class, 'show']);
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::get('admin/attendance/{user_id}', [AdminAttendanceController::class, 'show']);
    Route::get('/admin/attendance/staff/{user_id}', [AdminAttendanceController::class, 'history']);
});