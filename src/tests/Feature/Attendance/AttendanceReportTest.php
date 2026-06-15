<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
{
    use DatabaseMigrations;

    protected User $user;

    protected AttendanceRecord $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-01-01');

        $this->seed(\Database\Seeders\UserTableSeeder::class);
        $this->seed(\Database\Seeders\AttendanceRecordSeeder::class);

        $this->user = User::where('email', 'user1@example.com')->first();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guest_cannot_access_report_page()
    {
        $response = $this->get('/attendance/report');

        $response->assertRedirect('/login');
    }

    public function test_attendance_summary_is_calculated_correctly()
    {
        $response = $this->actingAs($this->user)->get('/attendance/report');

        $response->assertStatus(200);
        $response->assertSee('総労働時間');
        $response->assertSee('744h 0m');

        $response->assertSee('総残業時間');
        $response->assertSee('10h 0m');

        $response->assertSee('平均労働時間 / 日');
        $response->assertSee('8h 5m');

        $response->assertSee('遅刻回数');
        $response->assertSee('2回');

        $response->assertSee('早退回数');
        $response->assertSee('1回');

        $response->assertSee('長時間労働日数');
        $response->assertSee('1日');

        $expected = [
            '2025-08' => ['120h 0m', '0h 0m'],
            '2025-09' => ['120h 0m', '0h 0m'],
            '2025-10' => ['120h 0m', '0h 0m'],
            '2025-11' => ['120h 0m', '0h 0m'],
            '2025-12' => ['120h 0m', '0h 0m'],
            '2026-01' => ['144h 0m', '10h 0m'],
        ];

        foreach ($expected as $month => [$work, $overtime]) {
            $response->assertSee($month);
            $response->assertSee($work);
            $response->assertSee($overtime);
        }
    }

    public function test_user_without_attendance_data_can_view_report()
    {
        $userWithoutAttendance = User::factory()->create();

        $response = $this->actingAs($userWithoutAttendance)->get('/attendance/report');

        $response->assertStatus(200);
        $response->assertSee('総労働時間');
        $response->assertSee('0h 0m');

        $response->assertSee('総残業時間');
        $response->assertSee('0h 0m');

        $response->assertSee('平均労働時間 / 日');
        $response->assertSee('0h 0m');

        $response->assertSee('遅刻回数');
        $response->assertSee('0回');

        $response->assertSee('早退回数');
        $response->assertSee('0回');

        $response->assertSee('長時間労働日数');
        $response->assertSee('0日');

        $expected = [
            '2025-08' => ['0h 0m', '0h 0m'],
            '2025-09' => ['0h 0m', '0h 0m'],
            '2025-10' => ['0h 0m', '0h 0m'],
            '2025-11' => ['0h 0m', '0h 0m'],
            '2025-12' => ['0h 0m', '0h 0m'],
            '2026-01' => ['0h 0m', '0h 0m'],
        ];

        foreach ($expected as $month => [$work, $overtime]) {
            $response->assertSee($month);
            $response->assertSee($work);
            $response->assertSee($overtime);
        }
    }
}
