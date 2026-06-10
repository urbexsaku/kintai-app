<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrectRequest;

class AttendanceCorrectionTest extends TestCase
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

    public function test_validation_message_is_displayed_when_clock_in_is_later_than_clock_out()
    {
        $response = $this->actingAs($this->user)
            ->from("/attendance/detail/{$this->attendance->id}")
            ->post("/attendance/detail/{$this->attendance->id}", [
        
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
        $response = $this->actingAs($this->user)
            ->from("/attendance/detail/{$this->attendance->id}")
            ->post("/attendance/detail/{$this->attendance->id}", [

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
        $response = $this->actingAs($this->user)
            ->from("/attendance/detail/{$this->attendance->id}")
            ->post("/attendance/detail/{$this->attendance->id}", [

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
        $response = $this->actingAs($this->user)
            ->from("/attendance/detail/{$this->attendance->id}")
            ->post("/attendance/detail/{$this->attendance->id}", [

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

    public function test_user_can_request_correction()
    {
        $this->actingAs($this->user)->from("/attendance/detail/{$this->attendance->id}")
            ->post("/attendance/detail/{$this->attendance->id}", [

                'clock_in' => '10:00',
                'clock_out' => '19:00',
                'comment' => 'テストコメント',
            ]);
            
        $response = $this->actingAs($this->admin)
            ->get('/stamp_correction_request/list');

        // 管理画面に表示されているか確認
        $response->assertStatus(200);
        $response->assertSee('テストコメント');
    }

    public function test_pending_request_is_displayed_in_request_list()
    {
        $this->actingAs($this->user);
        $this->from("/attendance/detail/{$this->attendance->id}")
            ->post("/attendance/detail/{$this->attendance->id}", [

                'clock_in' => '10:00',
                'clock_out' => '19:00',
                'comment' => '承認待ちテストコメント',
            ]);

        $response = $this->get('/stamp_correction_request/list?page=pending');

        $response->assertStatus(200);
        $response->assertSee('承認待ちテストコメント');
    }

    public function test_approved_request_is_displayed_in_request_list()
    {
        $this->actingAs($this->user)
            ->from("/attendance/detail/{$this->attendance->id}")
            ->post("/attendance/detail/{$this->attendance->id}", [

                'clock_in' => '10:00',
                'clock_out' => '19:00',
                'comment' => '承認済みテストコメント',
            ]);

        $request = AttendanceCorrectRequest::latest()->first();

        $response = $this->actingAs($this->admin)
            ->post("/stamp_correction_request/approve/{$request->id}");

        $response = $this->actingAs($this->user)
            ->get('/stamp_correction_request/list?page=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済みテストコメント');
    }

    public function test_user_can_view_correction_request_detail()
    {
        $this->actingAs($this->user);
        $this->from("/attendance/detail/{$this->attendance->id}")
            ->post("/attendance/detail/{$this->attendance->id}", [
                'clock_in' => '10:00',
                'clock_out' => '19:00',
                'comment' => '詳細テストコメント',
            ]);

        $request = AttendanceCorrectRequest::latest()->first();

        $response = $this->get("/stamp_correction_request/detail/{$request->id}");
        $response->assertStatus(200);
        $response->assertSee('詳細テストコメント');
    }
}
