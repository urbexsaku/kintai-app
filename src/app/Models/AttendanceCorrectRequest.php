<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_record_id',
        'requested_clock_in',
        'requested_clock_out',
        'comment',
    ];

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'pending' => '承認待ち',
            'approved' => '承認済み',
        };
    }

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function breakCorrectRequest()
    {
        return $this->hasMany(BreakCorrectRequest::class);
    }
}
