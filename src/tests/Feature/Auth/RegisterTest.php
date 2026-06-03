<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_message_is_displayed_when_name_is_empty()
    {
        // 名前未入力で登録ボタンを押す
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('name');

        // バリデーションメッセージ表示確認
        $this->assertEquals(
            'お名前を入力してください',
            session('errors')->first('name')
        );
    }

    public function test_validation_message_is_displayed_when_email_is_empty()
    {
        // メールアドレス未入力で登録ボタンを押す
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
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
        // パスワード未入力で登録ボタンを押す
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');

        // バリデーションメッセージ表示確認
        $this->assertEquals(
            'パスワードを入力してください',
            session('errors')->first('password')
        );
    }

    public function test_validation_message_is_displayed_when_password_is_less_than_8_characters()
    {
        // パスワード7文字以下で登録ボタンを押す
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors('password');

        // バリデーションメッセージ表示確認
        $this->assertEquals(
            'パスワードは8文字以上で入力してください',
            session('errors')->first('password')
        );
    }

    public function test_validation_message_is_displayed_when_password_confirmation_does_not_match()
    {
        // 2確認用パスワード不一致で登録ボタンを押す
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');

        // バリデーションメッセージ表示確認
        $this->assertEquals(
            'パスワードと一致しません',
            session('errors')->first('password')
        );
    }

    public function test_user_is_registered()
    {
        // 必須項目を入力して登録ボタンを押す
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('staff.attendance.stamp'));
        $this->assertAuthenticated();

        // 会員情報登録を確認
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }
}
