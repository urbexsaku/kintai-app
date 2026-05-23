<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;

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
            'currentDate',
            'previousDate',
            'nextDate',
            'users',
        ));
    }
}
