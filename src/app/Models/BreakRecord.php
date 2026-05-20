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

    // データのCarbon化
    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
}
