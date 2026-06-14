<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
use RefreshDatabase;

    protected User $admin;
    protected User $user1;
    protected User $user2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\UserTableSeeder::class);

        $this->user1 = User::where('email', 'user1@example.com')->first();
        $this->user2 = User::where('email', 'user2@example.com')->first();
        $this->admin = User::where('email', 'user3@example.com')->first();
    
        Carbon::setTestNow('2026-01-01');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_attendance_data_of_all_users_for_today_is_displayed()
    {
        AttendanceRecord::create([
            'user_id' => $this->user1->id,
            'work_date' => today(),
            'clock_in' => today()->copy()->setTime(9, 0),
            'clock_out' => today()->copy()->setTime(18, 0),
        ]);

        AttendanceRecord::create([
            'user_id' => $this->user2->id,
            'work_date' => today(),
            'clock_in' => today()->copy()->setTime(10, 0),
            'clock_out' => today()->copy()->setTime(19, 0),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $this->user1->name,
            '09:00',
            '18:00',
            $this->user2->name,
            '10:00',
            '19:00',
        ]);
    }

    public function test_current_date_is_displayed()
    {
        $response = $this->actingAs($this->admin)->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('2026/01/01');      
    }

    public function test_attendance_data_of_previous_day_is_displayed()
    {
        $previousDate = now()->subDay();

        AttendanceRecord::create([
            'user_id' => $this->user1->id,
            'work_date' => $previousDate,
            'clock_in' => $previousDate->copy()->setTime(9, 0),
            'clock_out' => $previousDate->copy()->setTime(18, 0),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/attendance/list?date=' . $previousDate->format('Y-m-d'));

        $response->assertStatus(200);

        $response->assertSee($previousDate->format('Y/m/d'));
        $response->assertSee($this->user1->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_attendance_data_of_next_day_is_displayed()
    {
        $nextDate = now()->addDay();

        AttendanceRecord::create([
            'user_id' => $this->user1->id,
            'work_date' => $nextDate,
            'clock_in' => $nextDate->copy()->setTime(9, 0),
            'clock_out' => $nextDate->copy()->setTime(18, 0),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/attendance/list?date=' . $nextDate->format('Y-m-d'));

        $response->assertStatus(200);

        $response->assertSee($nextDate->format('Y/m/d'));
        $response->assertSee($this->user1->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
