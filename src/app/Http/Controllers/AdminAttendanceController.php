<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Http\Requests\AttendanceRequest;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', Carbon::today()->toDateString());

        $currentDate = Carbon::parse($date);
        $previousDate = $currentDate->copy()->subDay()->toDateString();
        $nextDate = $currentDate->copy()->addDay()->toDateString();

        $users = User::where('admin_status', false)
            ->with([
                'attendanceRecords' => function ($query) use ($currentDate) {
                    $query->with('breakRecords')
                        ->whereDate('work_date', $currentDate);
                }
            ])
            ->get();

        $users->each(function ($user) {
            $user->setRelation(
                'attendance',
                $user->attendanceRecords->first()
            );
        });
    
        return view('admin.attendance.daily', compact(
            'date',
            'currentDate',
            'previousDate',
            'nextDate',
            'users',
        ));
    }

    public function show($attendance_id)
    {
        $attendance = AttendanceRecord::with('breakRecords', 'user')
            ->findOrFail($attendance_id);

        return view('admin.attendance.detail', compact('attendance'));
    }

    public function update(AttendanceRequest $request, $attendance_id)
    {
        $attendance = AttendanceRecord::with('breakRecords')
            ->findOrFail($attendance_id);

        $attendance->update([
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            // 'comment' => $request->comment,
        ]);

        foreach ($request->input('start_at', []) as $index => $start) {
            $end = $request->end_at[$index] ?? null;

            // 両方空ならスキップ
            if (!$start && !$end) {
                continue;
            }

            // 片方だけはエラー
            if (!$start || !$end) {
                return back ()->withErrors([
                    'breaks' => '休憩開始・終了を入力してください'
                ])
                ->withInput();
            }

            $breakRecord = $attendance->breakRecords[$index] ?? null;

            // 既存データの修正なら更新
            if ($breakRecord) {
                $breakRecord->update([
                'start_at' => $start,
                'end_at' => $end,
                ]);
            }

            // 新規データなら作成
            else {
                BreakRecord::create([
                    'attendance_record_id' => $attendance->id,
                    'start_at' => $start,
                    'end_at' => $end,
                ]);
            }            
        }

        return redirect('/admin/attendance/' . $attendance_id)
            ->with('message', '勤怠データを更新しました');;
    }
}
