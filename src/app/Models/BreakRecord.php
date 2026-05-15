<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_record_id',
        'start_at',
        'end_at',
    ];

    public function attendanceRecords()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
}
