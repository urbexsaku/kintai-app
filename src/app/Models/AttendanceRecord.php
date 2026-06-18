<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function breakRecords(): HasMany
    {
        return $this->hasMany(BreakRecord::class);
    }

    public function attendanceCorrectRequests(): HasMany
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }

    public function getTotalBreakAttribute(): string
    {
        $totalBreak = $this->calculateTotalBreakSeconds();

        $hours = floor($totalBreak / 3600);
        $minutes = floor(($totalBreak % 3600) / 60); // 時間を除いた余り

        // フォーマット指定して文字列へ変換
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function getWorkTimeAttribute(): ?string
    {
        // 出勤中であればnullを返す
        if (! $this->clock_out) {
            return null;
        }

        $totalBreak = $this->calculateTotalBreakSeconds();

        $workSeconds = $this->clock_out->diffInSeconds($this->clock_in) - $totalBreak;

        $hours = floor($workSeconds / 3600);
        $minutes = floor(($workSeconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function getWorkMinutesAttribute(): ?int
    {
        if (! $this->clock_out) {
            return null;
        }

        $totalBreak = $this->calculateTotalBreakSeconds();

        $workSeconds = $this->clock_out->diffInSeconds($this->clock_in) - $totalBreak;

        return floor($workSeconds / 60);
    }

    private function calculateTotalBreakSeconds(): int
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
