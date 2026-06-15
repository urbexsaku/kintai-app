<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user1;

    protected User $user2;

    protected AttendanceRecord $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-01-01');

        $this->seed(\Database\Seeders\UserTableSeeder::class);

        $this->user1 = User::where('email', 'user1@example.com')->first();
        $this->user2 = User::where('email', 'user2@example.com')->first();
        $this->admin = User::where('email', 'user3@example.com')->first();

        $this->attendance = AttendanceRecord::create([
            'user_id' => $this->user1->id,
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
        $response->assertSee($this->user1->name);
        $response->assertSee($this->user1->email);
        $response->assertSee($this->user2->name);
        $response->assertSee($this->user2->email);
    }

    public function test_attendance_detail_is_displayed()
    {
        $response = $this->actingAs($this->admin)->get("/admin/attendance/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($this->user1->name);
        $response->assertSee($this->attendance->work_date->format('Y年'));
        $response->assertSee($this->attendance->work_date->format('n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_previous_month_is_displayed()
    {
        $previousMonth = now()->subMonth()->format('Y-m');

        $response = $this->actingAs($this->admin)->get("/admin/attendance/staff/{$this->user1->id}?month={$previousMonth}");

        $response->assertStatus(200);
        $response->assertSee('2025/12');
    }

    public function test_next_month_is_displayed()
    {
        $nextMonth = now()->addMonth()->format('Y-m');

        $response = $this->actingAs($this->admin)->get("/admin/attendance/staff/{$this->user1->id}?month={$nextMonth}");

        $response->assertStatus(200);
        $response->assertSee('2026/02');
    }

    public function test_admin_can_view_selected_attendance_detail()
    {
        $response = $this->actingAs($this->admin)->get("/admin/attendance/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($this->attendance->work_date->format('Y年'));
        $response->assertSee($this->attendance->work_date->format('n月j日'));
    }
}
