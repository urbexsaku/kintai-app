<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectRequest;
use Illuminate\Http\Request;

class StaffRequestController extends Controller
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

        $query = AttendanceCorrectRequest::whereHas('attendanceRecord', function ($q) {
            $q->where('user_id', auth()->id());
        });

        if ($page) {
            $query->where('status', $page);
        }

        $attendanceCorrectRequests = $query->with('attendanceRecord.user')->get();

        return view('staff.requests.index', compact('attendanceCorrectRequests', 'page'));
    }

    /**
     * 勤怠修正申請詳細画面を表示する
     *
     * @param int $attendance_correct_request_id
     * @return \Illuminate\View\View
     */
    public function show($attendance_correct_request_id)
    {
        $attendanceCorrectRequest = AttendanceCorrectRequest::findOrFail($attendance_correct_request_id);

        return view('staff.requests.detail', compact('attendanceCorrectRequest'));
    }
}
