<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_message_is_displayed_when_email_is_empty()
    {
        // メールアドレス以外のユーザー情報を入力して、ログインの処理を行う
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');

        // バリデーションメッセージ表示確認
        $this->assertEquals(
            'メールアドレスを入力してください',
            session('errors')->first('email')
        );
    }

    public function test_validation_message_is_displayed_when_password_is_empty()
    {
        // パスワード以外のユーザー情報を入力して、ログインの処理を行う
        $response = $this->post('/admin/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');

        // バリデーションメッセージ表示確認
        $this->assertEquals(
            'パスワードを入力してください',
            session('errors')->first('password')
        );
    }

    public function test_validation_message_is_displayed_when_credentials_are_invalid()
    {
        // 管理者ユーザーを作成する
        User::create([
            'name' => '管理者ユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'admin_status' => true,
        ]);

        // 誤ったメールアドレスのユーザー情報を入力して、ログインの処理を行う
        $response = $this->post('/admin/login', [
            'email' => 'unknown@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');

        // バリデーションメッセージ表示確認
        $this->assertEquals(
            'ログイン情報が登録されていません',
            session('errors')->first('email')
        );
    }
}
