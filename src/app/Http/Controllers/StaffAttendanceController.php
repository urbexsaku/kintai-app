<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;

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
            $break = BreakRecord::where('attendance_record_id', $attendance->id)
                ->latest()
                ->first();
        }
        
        $isWorking = false;
        $isBreaking = false;
        $isFinished = false;

        if ($attendance) {
            if ($attendance->clock_out) { // clock_outが登録されている場合
                $isFinished = true; // 退勤済
            } elseif ($break && !$break->end_at){ // 最新の休憩データがend_atではない場合
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

    public function clockIn() {
        $attendance = AttendanceRecord::where('user_id', Auth::id())->where('work_date', today())->first();

        if ($attendance) {
            return redirect()->route('staff.attendance.stamp')->with('message', '本日は既に出勤済みです');
        }

        AttendanceRecord::create([
            'user_id' => Auth::id(),
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        return redirect()->route('staff.attendance.stamp');
    }
}
