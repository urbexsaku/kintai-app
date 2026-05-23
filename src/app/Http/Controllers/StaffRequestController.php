<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorrectionRequest;

class StaffRequestController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->query('page', 'pending');

        $query = CorrectionRequest::whereHas('attendanceRecord', function ($q) {
            $q->where('user_id', auth()->id());
        });

        if ($page) {
            $query->where('status', $page);
        }

        $correctionRequests = $query->with('attendanceRecord.user')->get();

        return view('staff.requests', compact('correctionRequests', 'page'));
    }
}
