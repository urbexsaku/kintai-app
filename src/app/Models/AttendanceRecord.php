<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'comment',
    ];

    // データの日付/Carbon化
    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakRecords()
    {
        return $this->hasMany(BreakRecord::class);
    }

    public function attendanceCorrectRequests()
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }

    public function getTotalBreakAttribute()
    {
        $totalBreak = $this->calculateTotalBreakSeconds();

        $hours = floor($totalBreak / 3600);
        $minutes = floor(($totalBreak % 3600) / 60); // 時間を除いた余り

        // フォーマット指定して文字列へ変換
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function getWorkTimeAttribute()
    {
        // 出勤中であればnullを返す
        if (!$this->clock_out) {
            return null;
        }

        $totalBreak = $this->calculateTotalBreakSeconds();

        $workSeconds = $this->clock_out->diffInSeconds($this->clock_in) - $totalBreak;

        $hours = floor($workSeconds / 3600);
        $minutes = floor(($workSeconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    private function calculateTotalBreakSeconds()
    {
        // 日毎の休憩データを秒に換算し、休憩総時間を累計算出
        $totalBreak = 0;

        foreach ($this->breakRecords as $break) {
            if ($break->end_at) {
                $totalBreak += $break->end_at->diffInSeconds($break->start_at);
            }
        }

        return $totalBreak;
    }
}
