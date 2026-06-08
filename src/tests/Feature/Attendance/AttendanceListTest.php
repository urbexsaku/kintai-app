<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\UserTableSeeder::class);
        $this->seed(\Database\Seeders\AttendanceRecordSeeder::class);

        $this->user = User::where('email', 'user1@example.com')->first();
    }

    public function test_attendance_list_displays_all_attendances()
    {
        $response = $this->actingAs($this->user)->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_current_month_is_displayed()
    {
        Carbon::setTestNow('2026-06-07');

        $response = $this->actingAS($this->user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026/06');

        Carbon::setTestNow();
    }

    public function test_previous_month_is_displayed()
    {
        Carbon::setTestNow('2026-06-07');

        $previousMonth = now()->subMonth()->format('Y-m');

        $response = $this->actingAS($this->user)->get("/attendance/list?month={$previousMonth}");

        $response->assertStatus(200);
        $response->assertSee('2026/05');

        Carbon::setTestNow();
    }

    public function test_next_month_is_displayed()
    {
        Carbon::setTestNow('2026-06-07');

        $nextMonth = now()->addMonth()->format('Y-m');

        $response = $this->actingAs($this->user)->get("/attendance/list?month={$nextMonth}");

        $response->assertStatus(200);
        $response->assertSee('2026/07');

        Carbon::setTestNow();
    }

    public function test_user_can_view_attendane_detail()
    {
        $attendance = \App\Models\AttendanceRecord::first();

        $response = $this->actingAs($this->user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($attendance->work_date);
    }
}
