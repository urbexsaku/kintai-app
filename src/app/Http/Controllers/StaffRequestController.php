<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrectRequest;

class StaffRequestController extends Controller
{
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

        return view('staff.requests', compact('attendanceCorrectRequests', 'page'));
    }
}
