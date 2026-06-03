<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\AttendanceCorrectRequest;
use App\Models\AttendanceRecord;
use App\Models\BreakCorrectRequest;
use App\Models\BreakRecord;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffAttendanceController extends Controller
{
    public function index()
    {
        // 今日の出勤データを取得
        $attendance = AttendanceRecord::with(['breakRecords' => function ($q) {
            $q->latest('start_at');
        }])
            ->where('user_id', Auth::id())
            ->where('work_date', today())
            ->first();

        $break = $attendance?->breakRecords->first();

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
            } elseif ($break && ! $break->end_at) { // 最新の休憩データがend_atではない場合
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
    public function clockIn()
    {
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
    public function breakStart()
    {
        $attendance = $this->getTodayAttendance();

        BreakRecord::create([
            'attendance_record_id' => $attendance->id,
            'start_at' => now(),
        ]);

        return redirect()->route('staff.attendance.stamp');
    }

    // 休憩戻登録
    public function breakEnd()
    {
        $attendance = $this->getTodayAttendance();

        $break = $attendance->breakRecords()->latest('start_at')->first();

        $break->update([
            'end_at' => now(),
        ]);

        return redirect()->route('staff.attendance.stamp');
    }

    // 退勤登録
    public function clockOut()
    {
        $attendance = $this->getTodayAttendance();

        $attendance->update([
            'clock_out' => now(),
        ]);

        return redirect()->route('staff.attendance.stamp');
    }

    public function history(Request $request)
    {

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

        $isPending = $attendance->attendanceCorrectRequests()->where('status', 'pending')->exists();

        return view('staff.attendance.detail', compact('attendance', 'isPending'));
    }

    public function store(AttendanceRequest $request, $attendance_id)
    {
        $attendanceCorrectRequest = AttendanceCorrectRequest::create([
            'attendance_record_id' => $attendance_id,
            'requested_clock_in' => $request->clock_in,
            'requested_clock_out' => $request->clock_out,
            'comment' => $request->comment,
        ]);

        // start_atが無ければ空配列を返し、有れば各データに対して実行
        foreach ($request->input('start_at', []) as $index => $start) {
            $end = $request->end_at[$index] ?? null;

            // 両方空ならスキップ
            if (! $start && ! $end) {
                continue;
            }

            // 片方だけはエラー
            if (! $start || ! $end) {
                return back()->withErrors([
                    'breaks' => '休憩開始・終了を入力してください',
                ])
                    ->withInput();
            }

            BreakCorrectRequest::create([
                'attendance_correct_request_id' => $attendanceCorrectRequest->id,
                'requested_start_at' => $start,
                'requested_end_at' => $end,
            ]);
        }

        return redirect('/attendance/list');
    }

    public function report()
    {
        $user = auth()->id();

        // 起点：当月月初の5カ月前、終点：当月月末
        $startDate = now()->startOfMonth()->subMonth(5);
        $endDate = now()->endOfMonth();

        $attendanceRecords = AttendanceRecord::with('breakRecords')
            ->where('user_id', $user)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get();

        // 合計労働時間集計
        $totalWorkMinutes = $attendanceRecords->sum('work_minutes');

        // 残業時間集計
        $totalOvertimeMinutes = $attendanceRecords->sum(function ($record) {
            return max(0, $record->work_minutes - 480);
        });

        // 平均労働時間集計（勤怠レコードなければ0）
        $averageMinutes = $attendanceRecords->count() ? round($totalWorkMinutes / $attendanceRecords->count()) : 0;

        // 表示形式を設定
        $totalWorkTime = $this->formatMinutes($totalWorkMinutes);
        $totalOvertimeTime = $this->formatMinutes($totalOvertimeMinutes);
        $averageWorkTime = $this->formatMinutes($averageMinutes);

        $groupedRecords = $attendanceRecords->groupBy(function ($record) {
            return $record->work_date->format('Y-m');
        });

        $monthlyReports = collect(range(5, 0))
            ->map(function ($i) use ($groupedRecords) {

                $month = now()->startOfMonth()->subMonths($i)->format('Y-m');

                $records = $groupedRecords->get($month, collect());

                return [
                    'month' => $month,
                    'work_time' => $this->formatMinutes(
                        $records->sum('work_minutes')
                    ),
                    'overtime_time' => $this->formatMinutes(
                        $records->sum(fn ($record) => max(0, $record->work_minutes - 480)
                        )
                    ),
                ];
            });

        $currentMonthRecords = $groupedRecords->get(
            now()->format('Y-m'),
            collect()
        );

        // 遅刻回数カウント
        $lateCount = $currentMonthRecords->filter(function ($record) {
            return $record->clock_in && $record->clock_in->gt($record->work_date->copy()->setTime(9, 0));
        })->count();

        // 早退回数カウント
        $earlyLeaveCount = $currentMonthRecords->filter(function ($record) {
            return $record->clock_out && $record->clock_out->lt($record->work_date->copy()->setTime(18, 0));
        })->count();

        // 長時間労働日数カウント
        $longWorkCount = $currentMonthRecords->filter(function ($record) {
            return $record->work_minutes > 600;
        })->count();

        return view('staff.attendance.report', compact(
            'totalWorkTime',
            'totalOvertimeTime',
            'averageWorkTime',
            'monthlyReports',
            'lateCount',
            'earlyLeaveCount',
            'longWorkCount',
        ));
    }

    private function getTodayAttendance()
    {
        return AttendanceRecord::where('user_id', Auth::id())
            ->where('work_date', today())
            ->firstOrFail();
    }

    private function formatMinutes(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;

        return "{$hours}h {$minutes}m";
    }
}
