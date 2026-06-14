<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AttendanceRecordFactory extends Factory
{
    protected $model = AttendanceRecord::class;

    public function definition(): array
    {
        $workDate = now()->subDays($this->faker->unique()->numberBetween(1, 180));

        $clockIn = Carbon::instance($workDate)->setTime(9, 0);
        $clockOut = Carbon::instance($workDate)->setTime(18, 0);
    
        return [
            'work_date' => Carbon::instance($workDate)->format('Y-m-d'),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }
}
