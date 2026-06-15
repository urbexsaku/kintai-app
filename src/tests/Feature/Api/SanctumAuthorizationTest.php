<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SanctumAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;

    protected User $user2;

    protected AttendanceRecord $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\UserTableSeeder::class);

        $this->user1 = User::where('email', 'user1@example.com')->first();
        $this->user2 = User::where('email', 'user2@example.com')->first();

        $this->attendance = AttendanceRecord::create([
            'user_id' => $this->user1->id,
            'work_date' => today(),
            'clock_in' => today()->copy()->setTime(9, 0),
            'clock_out' => today()->copy()->setTime(18, 0),
        ]);
    }

    public function test_401_error_is_displayed_for_unauthenticated_user()
    {
        // 未承認ユーザーがPOST実行
        $response = $this->postJson('/api/v1/attendance-records', [
            'work_date' => '2026-01-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '勤怠作成テスト',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        // 未承認ユーザーがPUT実行
        $attendance = AttendanceRecord::create([
            'user_id' => $this->user1->id,
            'work_date' => '2026-01-01',
            'clock_in' => today()->copy()->setTime(9, 0),
            'clock_out' => today()->copy()->setTime(18, 0),
        ]);

        $response = $this->putJson(
            "/api/v1/attendance-records/{$attendance->id}",
            [
                'work_date' => '2026-01-02',
                'clock_in' => '10:00:00',
                'clock_out' => '19:00:00',
                'comment' => '勤怠更新テスト',
            ]
        );

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        // 未承認ユーザーがDELETE実行
        $response = $this->deleteJson("/api/v1/attendance-records/{$this->attendance->id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_authenticated_user_can_update_and_delete()
    {
        Sanctum::actingAs($this->user1, ['*']);

        $attendance = AttendanceRecord::create([
            'user_id' => $this->user1->id,
            'work_date' => '2026-01-01',
            'clock_in' => today()->copy()->setTime(9, 0),
            'clock_out' => today()->copy()->setTime(18, 0),
        ]);

        // 自分の勤怠に対してPUTを実行
        $response = $this->putJson(
            "/api/v1/attendance-records/{$attendance->id}",
            [
                'work_date' => '2026-01-02',
                'clock_in' => '10:00:00',
                'clock_out' => '19:00:00',
                'comment' => '勤怠更新テスト',
            ]
        );

        $response->assertStatus(200);

        // 自分の勤怠に対してDELETEを実行
        $response = $this->deleteJson("/api/v1/attendance-records/{$this->attendance->id}");

        $response->assertStatus(204);
    }

    public function test_user_cannot_update_or_delete_attendance_of_others()
    {
        Sanctum::actingAs($this->user2, ['*']);

        // 他ユーザーの勤怠に対してPUTを実行
        $response = $this->putJson(
            "/api/v1/attendance-records/{$this->attendance->id}",
            [
                'work_date' => '2026-01-01',
                'clock_in' => '10:00:00',
                'clock_out' => '19:00:00',
                'comment' => '勤怠更新テスト',
            ]
        );

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'この操作を実行する権限がありません。',
            ]);

        // 他ユーザーの勤怠に対してDELETEを実行
        $response = $this->deleteJson("/api/v1/attendance-records/{$this->attendance->id}");

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'この操作を実行する権限がありません。',
            ]);
    }
}
