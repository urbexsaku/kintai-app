<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use Carbon\Carbon;

class AttendanceRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::whereIn('id', [1, 2])->get();

        foreach ($users as $user) {
            if ($user->id === 1) {
                $this->createUser1Data($user);
            } else {
                $this->createUser2Data($user);
            }
        }
    }

    private function createUser1Data($user)
    {
        // 過去5カ月のデータ作成
        for ($month = 1; $month <= 5; $month++) {

        // 月初日を取得
        $date = now()->startOfMonth()->subMonths($month);

        $count = 0;

            // 平日15日分の勤怠データ（通常勤務）を作成
            while ($count < 15) {
                if ($date->isWeekday()){
                    $attendance = AttendanceRecord::create([
                    'user_id' => $user->id,
                    'work_date' => $date->copy(),
                    'clock_in' => $date->copy()->setTime(9, 0),
                    'clock_out' => $date->copy()->setTime(18, 0),
                    ]);

                    BreakRecord::create([
                        'attendance_record_id' => $attendance->id,
                        'start_at' => $date->copy()->setTime(12, 0),
                        'end_at' => $date->copy()->setTime(13, 0),
                    ]);

                    $count++;
                }
                
                $date->addDay();            
            }
        }

        // 当月の17日分データ作成
        $patterns = [
            ['in' => [9, 0],  'out' => [18, 0], 'count' => 10], // 通常
            ['in' => [9, 0],  'out' => [20, 0], 'count' => 3],  // 残業
            ['in' => [9, 30], 'out' => [18, 0], 'count' => 2],  // 遅刻
            ['in' => [9, 0],  'out' => [17, 0], 'count' => 1],  // 早退
            ['in' => [8, 0],  'out' => [21, 0], 'count' => 1],  // 長時間
        ];

        $baseMonth = now()->format('Y-m');

        $day = 1;

        // 各パターン指定カウント数のデータを作成
        foreach ($patterns as $pattern) {
            for ($i = 0; $i < $pattern['count']; $i++) {
                $targetDate = Carbon::parse($baseMonth. '-' . $day);

                $attendance = AttendanceRecord::create([
                    'user_id' => $user->id,
                    'work_date' => $targetDate,
                    'clock_in' => $targetDate->copy()->setTime(
                        $pattern['in'][0], // 時取得
                        $pattern['in'][1], // 分取得
                    ),
                    'clock_out' => $targetDate->copy()->setTime(
                        $pattern['out'][0],
                        $pattern['out'][1],
                    ),
                ]);

                BreakRecord::create([
                    'attendance_record_id' => $attendance->id,
                    'start_at' => $targetDate->copy()->setTime(12, 0),
                    'end_at' => $targetDate->copy()->setTime(13, 0),
                ]);

                $day++;
            }
        }
    }

    private function createUser2Data($user)
    {
        // 過去5カ月
        for ($month = 1; $month <= 5; $month++) {

            // 月初から月末まで通常勤務データを作成
            $date = now()->startOfMonth()->subMonths($month);

            while ($date->lte(now()->subMonths($month)->endOfMonth())) {
                if ($date->isWeekday()){
                    $attendance = AttendanceRecord::create([
                    'user_id' => $user->id,
                    'work_date' => $date->copy(),
                    'clock_in' => $date->copy()->setTime(9, 0),
                    'clock_out' => $date->copy()->setTime(18, 0),
                    ]);

                    BreakRecord::create([
                        'attendance_record_id' => $attendance->id,
                        'start_at' => $date->copy()->setTime(12, 0),
                        'end_at' => $date->copy()->setTime(13, 0),
                    ]);
                }
                
                $date->addDay();            
            }
        }
    }
}