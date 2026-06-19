<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    /**
     * 指定日の勤怠一覧を表示する
     *
     * @return \Illuminate\View\View
     */
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
                },
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

    /**
     * 勤怠詳細画面を表示する
     *
     * @param  int  $attendance_id
     * @return \Illuminate\View\View
     */
    public function show($attendance_id)
    {
        $attendance = AttendanceRecord::with('breakRecords', 'user')
            ->findOrFail($attendance_id);

        $isPending = $attendance->attendanceCorrectRequests()->where('status', 'pending')->exists();

        return view('admin.attendance.detail', compact('attendance', 'isPending'));
    }

    /**
     * 勤怠情報を更新する
     *
     * @param  int  $attendance_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AttendanceRequest $request, $attendance_id)
    {
        $attendance = AttendanceRecord::with('breakRecords')
            ->findOrFail($attendance_id);

        $workDate = $attendance->work_date;

        $attendance->update([
            'clock_in' => $workDate->copy()->setTimeFromTimeString(
                $request->clock_in
            ),
            'clock_out' => $workDate->copy()->setTimeFromTimeString(
                $request->clock_out
            ),
            'comment' => $request->comment,
        ]);

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

        return redirect('/admin/attendance/'.$attendance_id)
            ->with('message', '勤怠データを更新しました');
    }

    /**
     * ユーザーの月次勤怠一覧を表示する
     *
     * @param  int  $user_id
     * @return \Illuminate\View\View
     */
    public function history(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);

        // クエリパラメータでmonthの指定がなければ当月
        $currentMonth = $request->input('month', now()->format('Y-m'));

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
            ->where('user_id', $user_id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get();

        // 出勤データを日付キーで指定
        $attendanceMap = $attendances->keyBy(function ($attendance) {
            return $attendance->work_date->format('Y-m-d');
        });

        return view('admin.attendance.monthly', compact(
            'user',
            'currentMonth',
            'previousMonth',
            'nextMonth',
            'dates',
            'attendanceMap',
        ));
    }

    /**
     * 月次勤怠データをCSV出力する
     *
     * @param  int  $user_id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function export(Request $request, $user_id)
    {
        $currentMonth = $request->input('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

        $attendances = AttendanceRecord::with([
            'user',
            'breakRecords',
        ])
            ->where('user_id', $user_id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get();

        $filename = 'attendance_'.$currentMonth.'.csv';

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");

        fputcsv($stream, [
            '名前',
            '日付',
            '出勤',
            '退勤',
            '休憩',
            '合計',
        ]);

        foreach ($attendances as $attendance) {
            fputcsv($stream, [
                $attendance->user->name,
                $attendance->work_date->format('Y/m/d'),
                $attendance->clock_in?->format('H:i'),
                $attendance->clock_out?->format('H:i'),
                $attendance->total_break,
                $attendance->work_time,
            ]);
        }

        rewind($stream);

        return response(stream_get_contents($stream), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
