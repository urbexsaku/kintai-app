<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectRequest;
use App\Models\BreakRecord;
use Illuminate\Http\Request;

class AdminRequestController extends Controller
{
    /**
     * 勤怠修正申請一覧を表示する
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $page = $request->query('page', 'pending');

        $query = AttendanceCorrectRequest::with([
            'attendanceRecord.user',
        ]);

        $query->where('status', $page);

        $attendanceCorrectRequests = $query->get();

        return view('admin.requests.index', compact('attendanceCorrectRequests', 'page'));
    }

    /**
     * 勤怠修正申請承認画面を表示する
     *
     * @param int $attendance_correct_request_id
     * @return \Illuminate\View\View
     */
    public function show($attendance_correct_request_id)
    {
        $attendanceCorrectRequest = AttendanceCorrectRequest::with(
            'breakCorrectRequests',
            'attendanceRecord.user'
        )->findOrFail($attendance_correct_request_id);

        return view('admin.requests.approve', compact('attendanceCorrectRequest'));
    }

    /**
     * 勤怠修正申請を承認する
     *
     * @param int $attendance_correct_request_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($attendance_correct_request_id)
    {

        $attendanceCorrectRequest = AttendanceCorrectRequest::with([
            'attendanceRecord.breakRecords',
            'breakCorrectRequests',
        ])->findOrFail($attendance_correct_request_id);

        $workDate = $attendanceCorrectRequest->attendanceRecord->work_date;

        $attendanceCorrectRequest->attendanceRecord->update([
            'clock_in' => $workDate->copy()->setTimeFromTimeString(
                $attendanceCorrectRequest->requested_clock_in->format('H:i:s')
            ),
            'clock_out' => $workDate->copy()->setTimeFromTimeString(
                $attendanceCorrectRequest->requested_clock_out->format('H:i:s'),
            ),
            'comment' => $attendanceCorrectRequest->comment,
        ]);

        foreach ($attendanceCorrectRequest->breakCorrectRequests as $index => $breakCorrectRequest) {
            $breakRecord = $attendanceCorrectRequest->attendanceRecord->breakRecords[$index] ?? null;

            if ($breakRecord) {
                $breakRecord->update([
                    'start_at' => $breakCorrectRequest->requested_start_at,
                    'end_at' => $breakCorrectRequest->requested_end_at,
                ]);
            } else {
                BreakRecord::create([
                    'attendance_record_id' => $attendanceCorrectRequest->attendanceRecord->id,
                    'start_at' => $breakCorrectRequest->requested_start_at,
                    'end_at' => $breakCorrectRequest->requested_end_at,
                ]);
            }
        }

        $attendanceCorrectRequest->update([
            'status' => 'approved',
        ]);

        return back()->with('message', '承認しました');
    }
}
