<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use App\Models\User;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_email_is_sent_after_registration()
    {
        Notification::fake();

        // 1. 会員登録をする
        $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        // 登録したメールアドレス宛に認証メールが送信されたことを確認
        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    public function test_verified_user_redirects_to_stamp_page()
    {
        // 未認証ユーザーの作成
        $user = User::factory()->unverified()->create();

        // 1. メール認証を完了する
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        // プロフィール設定画面への遷移確認
        $response->assertRedirect(route('staff.attendance.stamp'));

        // ユーザーが認証済みであることを確認
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
