<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceCorrectRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorrectionApprovalTest extends TestCase
{
use RefreshDatabase;

    protected User $admin;
    protected User $user;
    protected AttendanceRecord $attendance;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_pending_request_is_displayed_in_request_list()
    {
        AttendanceCorrectRequest::create([
            'attendance_record_id' => $this->attendance->id,
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'comment' => '承認待ちテストコメント',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/stamp_correction_request/list?page=pending');

        $response->assertStatus(200);
        $response->assertSee('承認待ちテストコメント');
    }

    public function test_approved_request_is_displayed_in_request_list()
    {
        $request = AttendanceCorrectRequest::create([
            'attendance_record_id' => $this->attendance->id,
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'comment' => '承認済みテストコメント',
        ]);

        $this->actingAs($this->admin)->post("/stamp_correction_request/approve/{$request->id}");

        $response = $this->actingAs($this->admin)->get('/stamp_correction_request/list?page=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済みテストコメント');
    }

    public function test_admin_can_view_correction_request_detail()
    {
        $request = AttendanceCorrectRequest::create([
            'attendance_record_id' => $this->attendance->id,
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'comment' => '詳細テストコメント',
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/stamp_correction_request/detail/{$request->id}");

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('詳細テストコメント');
    }

    public function test_admin_can_approve_correction_request()
    {
        $request = AttendanceCorrectRequest::create([
            'attendance_record_id' => $this->attendance->id,
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'comment' => '承認処理テストコメント',
        ]);

        $this->actingAs($this->admin)
            ->post("/stamp_correction_request/approve/{$request->id}");

        $this->assertDatabaseHas('attendance_records', [
            'id' => $this->attendance->id,
            'comment' => '承認処理テストコメント',
        ]);

        $this->attendance->refresh();
        $this->assertEquals('10:00', $this->attendance->clock_in->format('H:i'));
        $this->assertEquals('19:00', $this->attendance->clock_out->format('H:i'));
    }
}
