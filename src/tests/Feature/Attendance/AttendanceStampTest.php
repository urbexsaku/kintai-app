<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use Carbon\Carbon;

class AttendanceStampTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $user;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\UserTableSeeder::class);

        $this->user = User::where('email', 'user1@example.com')->first();
    }

    public function test_off_duty_status_is_displayed()
    {
        $response = $this->actingAs($this->user)
            ->get(route('staff.attendance.stamp'));

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_working_status_is_displayed()
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);
    
        $response = $this->actingAs($this->user)
            ->get(route('staff.attendance.stamp'));

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_break_status_is_displayed()
    {
        $attendance = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(2),
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendance->id,
            'start_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('staff.attendance.stamp'));

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_finished_status_is_displayed()
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('staff.attendance.stamp'));

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    public function test_user_can_stamp_clock_in()
    {
        $response = $this->actingAs($this->user)
            ->get(route('staff.attendance.stamp'));

        // 出勤ボタンが表示されていることを確認する
        $response->assertStatus(200);
        $response->assertSee('出勤');

        // 出勤の処理を行う
        $response = $this->post('/attendance/clock-in');
        $response->assertRedirect(route('staff.attendance.stamp'));

        // 処理後にステータスが勤務中になる
        $response = $this->get(route('staff.attendance.stamp'));
        $response->assertSee('出勤中');
    }

    public function test_user_can_stamp_clock_in_only_once_a_day()
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('staff.attendance.stamp'));

        $response->assertStatus(200);
        $response->assertSee('退勤済');
        $response->assertDontSee('出勤');
    }

    public function test_attendance_list_displays_clock_in_time()
    {
        Carbon::setTestNow('2026-06-07 09:00:00');
    
        // ステータスが勤務外のユーザーがログインし出勤の処理を行う
        $this->actingAs($this->user);
        $this->post('/attendance/clock-in');

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:00');

        Carbon::setTestNow();
    }

    public function test_user_can_stamp_break_start()
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(2),
        ]);

        $this->actingAs($this->user);
        $response = $this->get(route('staff.attendance.stamp'));

        $response->assertStatus(200);
        $response->assertSee('休憩入');

        $this->actingAs($this->user);
        $this->post('/attendance/break-start');

        $response = $this->get(route('staff.attendance.stamp'));
        $response->assertSee('休憩中');
    }

    public function test_user_can_stamp_break_start_multiple_times()
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(2),
        ]);

        $this->actingAs($this->user);
        $this->post('/attendance/break-start');
        $this->post('/attendance/break-end');

        $response = $this->get(route('staff.attendance.stamp'));

        $response->assertStatus(200);
        $response->assertSee('休憩入');
    }

    public function test_user_can_stamp_break_end()
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(2),
        ]);

        $this->actingAs($this->user);
        $response = $this->post('/attendance/break-start');

        $response = $this->get(route('staff.attendance.stamp'));
        $response->assertSee('休憩戻');

        $this->post('/attendance/break-end');

        $response = $this->get(route('staff.attendance.stamp'));
        $response->assertSee('出勤中');
    }

    public function test_user_can_stamp_break_end_multiple_times()
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'work_date' => today(),
            'clock_in' => '2026-06-07 09:00:00',
        ]);

        $this->actingAs($this->user);

        Carbon::setTestNow('2026-06-07 12:00:00');
        $this->post('/attendance/break-start');

        Carbon::setTestNow('2026-06-07 12:30:00');
        $this->post('/attendance/break-end');

        Carbon::setTestNow('2026-06-07 13:00:00');
        $this->post('/attendance/break-start');

        $response = $this->get(route('staff.attendance.stamp'));

        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        Carbon::setTestNow();
    }

    public function test_attendance_list_displays_break_time()
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'work_date' => today(),
            'clock_in' => '2026-06-07 09:00:00',
        ]);

        $this->actingAs($this->user);

        // 休憩開始
        Carbon::setTestNow('2026-06-07 12:00:00');
        $this->post('/attendance/break-start');

        // 休憩終了
        Carbon::setTestNow('2026-06-07 13:00:00');
        $this->post('/attendance/break-end');

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('01:00');

        Carbon::setTestNow();
    }

    public function test_user_can_stamp_clocl_out()
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
        ]);

        $this->actingAs($this->user);
        $response = $this->get(route('staff.attendance.stamp'));

        $response->assertStatus(200);
        $response->assertSee('退勤');

        $this->post('/attendance/clock-out');

        $response = $this->get(route('staff.attendance.stamp'));
        $response->assertSee('退勤済');
    }

    public function test_attendance_list_displays_clock_out_time()
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'work_date' => today(),
            'clock_in' => '2026-06-07 09:00:00',
        ]);

        $this->actingAs($this->user);
    
        Carbon::setTestNow('2026-06-07 18:00:00');

        $this->post('/attendance/clock-out');

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }
}
