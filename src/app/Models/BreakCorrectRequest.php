<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakCorrectRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correct_request_id',
        'requested_start_at',
        'requested_end_at',
    ];

    public function attendanceCorrectRequest()
    {
        return $this->belongsTo(AttendanceCorrectRequest::class);
    }
}
