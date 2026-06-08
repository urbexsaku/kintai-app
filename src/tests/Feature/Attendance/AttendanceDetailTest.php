<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;

class AttendanceDetailTest extends TestCase
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

    public function test_user_name_is_displayed()
    {
        $attendance = AttendanceRecord::first();

        $response = $this->actingAs($this->user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
    }

    public function test_selected_date_is_displayed()
    {
        $attendance = AttendanceRecord::first();

        $response = $this->actingAs($this->user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($attendance->work_date->format('Y年'));
        $response->assertSee($attendance->work_date->format('n月j日'));
    }

    public function test_clock_in_and_clock_out_are_displayed()
    {
        $attendance = AttendanceRecord::first();

        $response = $this->actingAs($this->user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
    }

    public function test_break_start_and_end_are_displayed()
    {
        $attendance = AttendanceRecord::first();
        $break = $attendance->breakRecords->first();

        $response = $this->actingAs($this->user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($break->start_at->format('H:i'));
        $response->assertSee($break->end_at->format('H:i'));
    }
}
