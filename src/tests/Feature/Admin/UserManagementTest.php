<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;
    protected AttendanceRecord $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-07');

        $this->seed(\Database\Seeders\UserTableSeeder::class);

        $this->user = User::where('email', 'user1@example.com')->first();
        $this->admin = User::where('email', 'user3@example.com')->first();

        $this->attendance = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'work_date' => today(),
            'clock_in' => today()->copy()->setTime(9, 0),
            'clock_out' => today()->copy()->setTime(18, 0),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_can_view_all_staff()
    {
        $response = $this->actingAs($this->admin)->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee('ユーザー1');
        $response->assertSee('user1@example.com');
        $response->assertSee('ユーザー2');
        $response->assertSee('user2@example.com');
    }

    public function test_attendance_list_displays_all_attendances()
    {
        $response = $this->actingAs($this->admin)->get("/admin/attendance/staff/{$this->user->id}");

        $response->assertStatus(200);
        $response->assertSee('ユーザー1');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_previous_month_is_displayed()
    {
        $previousMonth = now()->subMonth()->format('Y-m');

        $response = $this->actingAs($this->admin)->get("/admin/attendance/staff/{$this->user->id}?month={$previousMonth}");

        $response->assertStatus(200);
        $response->assertSee('2026/05');
    }

    public function test_next_month_is_displayed()
    {
        $nextMonth = now()->addMonth()->format('Y-m');

        $response = $this->actingAs($this->admin)->get("/admin/attendance/staff/{$this->user->id}?month={$nextMonth}");

        $response->assertStatus(200);
        $response->assertSee('2026/07');
    }

    public function test_admin_can_view_selected_attendance_detail()
    {
        $response = $this->actingAs($this->admin)->get("/admin/attendance/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($this->attendance->work_date->format('Y年'));
        $response->assertSee($this->attendance->work_date->format('n月j日'));
    }
}
