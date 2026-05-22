<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class StaffAttendanceController extends Controller
{
    public function index()
    {
        // 今日の出勤データを取得
        $attendance = AttendanceRecord::where('user_id', Auth::id())
            ->where('work_date', today())
            ->first();
        
        $break = null;

        // 今日の出勤データが存在する場合、その勤怠に紐づく最新の休憩データを取得
        if ($attendance) {
            $break = $attendance?->breakRecords()->latest('start_at')->first();
        }
        
        $isWorking = false;
        $isBreaking = false;
        $isFinished = false;

        if ($attendance) {
            if ($attendance->clock_out) { // clock_outが登録されている場合
                $isFinished = true; // 退勤済
            } elseif ($break && !$break->end_at) { // 最新の休憩データがend_atではない場合
                $isBreaking = true; // 休憩中
            } else {
                $isWorking = true; // 勤務中
            }
        }
    
        return view('staff.attendance.stamp', compact(
            'attendance',
            'isWorking',
            'isBreaking',
            'isFinished'
        ));
    }

    // 出勤登録
    public function clockIn() {
        $attendance = AttendanceRecord::where('user_id', Auth::id())->where('work_date', today())->first();

        if ($attendance) {
            return redirect()->route('staff.attendance.stamp');
        }

        AttendanceRecord::create([
            'user_id' => Auth::id(),
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        return redirect()->route('staff.attendance.stamp');
    }

    // 休憩入登録
    public function breakStart() {
        $attendance = AttendanceRecord::where('user_id', Auth::id())->where('work_date', today())->first();

        BreakRecord::create([
            'attendance_record_id' => $attendance->id,
            'start_at' => now(),
        ]);

        return redirect()->route('staff.attendance.stamp');
    }

    // 休憩戻登録
    public function breakEnd() {
        $attendance = AttendanceRecord::where('user_id', Auth::id())->where('work_date', today())->first();

        $break = $attendance->breakRecords()->latest('start_at')->first();

        $break->update([
            'end_at' => now(),
        ]);

        return redirect()->route('staff.attendance.stamp');
    }

    // 退勤登録
    public function clockOut() {
        $attendance = AttendanceRecord::where('user_id', Auth::id())->where('work_date', today())->first();

        $attendance->update([
            'clock_out' => now(),
        ]);

        return redirect()->route('staff.attendance.stamp');
    }

    public function history(Request $request) {

        // クエリパラメータでmonthの指定がなければ当月
        $currentMonth = $request->month ?? now()->format('Y-m');

        // 取得した年月情報を日付データへ変換し、月初日・月最終日を取得
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

        // 前月・翌月を取得
        $previousMonth = $startOfMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $startOfMonth->copy()->addMonth()->format('Y-m');

        // 当月の日付を取得
        $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);

        // 該当月の出勤データを取得
        $attendances = AttendanceRecord::with('breakRecords')
            ->where('user_id', Auth::id())
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get();

        // 出勤データを日付キーで指定
        $attendanceMap = $attendances->KeyBy(function ($attendance) {
            return $attendance->work_date->format('Y-m-d');
        });
        
        return view('staff.attendance.monthly', compact(
            'currentMonth',
            'previousMonth',
            'nextMonth',
            'dates',
            'attendanceMap',
        ));
    }

    public function show($attendance_id)
    {
        $attendance = AttendanceRecord::findOrFail($attendance_id);

        return view('staff.attendance/detail', compact('attendance'));
    }
}
