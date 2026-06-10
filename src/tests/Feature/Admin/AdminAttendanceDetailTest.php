<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;

class AdminAttendanceDetailTest extends TestCase
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

    public function test_selected_date_is_displayed()
    {
        $response = $this->actingAs($this->admin)->get("/admin/attendance/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($this->attendance->work_date->format('Y年'));
        $response->assertSee($this->attendance->work_date->format('n月j日'));
    }

    public function test_validation_message_is_displayed_when_clock_in_is_later_than_clock_out()
    {
        $response = $this->actingAs($this->admin)
            ->from("/admin/attendance/{$this->attendance->id}")
            ->post("/admin/attendance/{$this->attendance->id}", [

                'clock_in' => '18:00',
                'clock_out' => '09:00',
                'comment' => 'テスト',
            ]);

        $response->assertSessionHasErrors('clock_in');

        // バリデーションメッセージ表示確認
        $this->assertEquals(
            '出勤時間もしくは退勤時間が不適切な値です',
            session('errors')->first('clock_in')
        );
    }

    public function test_validation_message_is_displayed_when_break_start_is_later_than_clock_out()
    {
        $response = $this->actingAs($this->admin)
            ->from("/admin/attendance/{$this->attendance->id}")
            ->post("/admin/attendance/{$this->attendance->id}", [

                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'start_at' => ['19:00'],
                'comment' => 'テスト',
            ]);

        $response->assertSessionHasErrors('start_at.0');

        // バリデーションメッセージ表示確認
        $this->assertEquals(
            '休憩時間が不適切な値です',
            session('errors')->first('start_at.0')
        );
    }

    public function test_validation_message_is_displayed_when_break_end_is_later_than_clock_out()
    {
        $response = $this->actingAs($this->admin)
            ->from("/admin/attendance/{$this->attendance->id}")
            ->post("/admin/attendance/{$this->attendance->id}", [

                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'start_at' => ['17:00'],
                'end_at' => ['20:00'],
                'comment' => 'テスト',
            ]);

        $response->assertSessionHasErrors('end_at.0');

        // バリデーションメッセージ表示確認
        $this->assertEquals(
            '休憩時間もしくは退勤時間が不適切な値です',
            session('errors')->first('end_at.0')
        );
    }

    public function test_validation_message_is_displayed_when_comment_is_empty()
    {
        $response = $this->actingAs($this->admin)
            ->from("/admin/attendance/{$this->attendance->id}")
            ->post("/admin/attendance/{$this->attendance->id}", [

                'clock_in' => '10:00',
                'clock_out' => '19:00',
                'comment' => '',
            ]);

        $response->assertSessionHasErrors('comment');

        // バリデーションメッセージ表示確認
        $this->assertEquals(
            '備考を記入してください',
            session('errors')->first('comment')
        );
    }    
}
