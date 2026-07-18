<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceCorrectRequest;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceRecordApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected AttendanceRecord $attendance;

    protected BreakRecord $break;

    protected AttendanceCorrectRequest $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\UserTableSeeder::class);

        $this->user = User::where('email', 'user1@example.com')->first();

        $this->attendance = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'work_date' => today(),
            'clock_in' => today()->copy()->setTime(9, 0),
            'clock_out' => today()->copy()->setTime(18, 0),
        ]);

        $this->break = BreakRecord::create([
            'attendance_record_id' => $this->attendance->id,
            'start_at' => today()->copy()->setTime(12, 0),
            'end_at' => today()->copy()->setTime(13, 0),
        ]);

        $this->application = AttendanceCorrectRequest::create([
            'attendance_record_id' => $this->attendance->id,
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'comment' => 'テスト',
        ]);
    }

    public function test_attendance_list_can_be_retrieved()
    {
        Sanctum::actingAs($this->user, ['*']);
    
        AttendanceRecord::factory()
            ->count(100)
            ->create([
                'user_id' => $this->user->id,
            ]);

        $response = $this->getJson('/api/v1/attendance-records');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'date',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);

        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.per_page', 20);
        $response->assertJsonPath('meta.total', 101);
    }

    public function test_attendance_detail_can_be_retrieved()
    {
        Sanctum::actingAs($this->user, ['*']);
        
        $response = $this->getJson("/api/v1/attendance-records/{$this->attendance->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'user' => [
                    'id',
                    'name',
                ],
                'date',
                'clock_in',
                'clock_out',
                'breaks' => [
                    '*' => [
                        'id',
                        'break_in',
                        'break_out',
                    ],
                ],
                'applications',
                'comment',
            ],
        ]);

        $response->assertJsonPath(
            'data.id',
            $this->attendance->id
        );

        $response->assertJsonPath(
            'data.user.id',
            $this->user->id
        );
    }

    public function test_404_error_is_displayed_for_unregistered_id()
    {
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/attendance-records/99999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => '勤怠情報が見つかりませんでした。',
            ]);
    }

    public function test_attendance_record_can_be_created()
    {
        Sanctum::actingAs($this->user, ['*']);

        $response = $this->postJson('/api/v1/attendance-records', [
            'work_date' => '2026-01-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '勤怠作成テスト',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $this->user->id,
            'work_date' => '2026-01-01',
        ]);
    }

    public function test_422_error_is_displayed_without_required_data()
    {
        Sanctum::actingAs($this->user, ['*']);

        $response = $this->postJson('/api/v1/attendance-records', [
            'work_date' => '',
            'clock_in' => '',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'work_date',
            'clock_in',
        ]);

        $response->assertJsonPath(
            'errors.work_date.0',
            '勤怠日は必須です。'
        );

        $response->assertJsonPath(
            'errors.clock_in.0',
            '出勤時刻は必須です。'
        );
    }

    public function test_attendance_record_can_be_updated()
    {
        Sanctum::actingAs($this->user, ['*']);

        $attendance = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-01-01',
            'clock_in' => today()->copy()->setTime(9, 0),
            'clock_out' => today()->copy()->setTime(18, 0),
        ]);

        $response = $this->putJson("/api/v1/attendance-records/{$attendance->id}",
            [
                'work_date' => '2026-01-02',
                'clock_in' => '10:00:00',
                'clock_out' => '19:00:00',
                'comment' => '勤怠更新テスト',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('attendance_records', [
            'id' => $attendance->id,
            'work_date' => '2026-01-02',
            'comment' => '勤怠更新テスト',
        ]);
    }

    public function test_attendance_record_can_be_deleted()
    {
        Sanctum::actingAs($this->user, ['*']);

        $response = $this->deleteJson("/api/v1/attendance-records/{$this->attendance->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('attendance_records', [
            'id' => $this->attendance->id,
        ]);
    }
}
