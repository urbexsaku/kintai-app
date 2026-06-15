<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_message_is_displayed_when_name_is_empty()
    {
        // 名前以外のユーザー情報を入力して、会員登録の処理を行う
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
        // メールアドレス以外のユーザー情報を入力して、会員登録の処理を行う
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

    public function test_validation_message_is_displayed_when_password_is_less_than_8_characters()
    {
        // パスワードを8文字未満にし、ユーザー情報を入力して、会員登録の処理を行う
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
        // 確認用のパスワードとパスワードを一致させず、ユーザー情報を入力して、会員登録の処理を行う
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

    public function test_validation_message_is_displayed_when_password_is_empty()
    {
        // パスワード以外のユーザー情報を入力して、会員登録の処理を行う
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

    public function test_user_is_registered()
    {
        // ユーザー情報を入力して、会員登録の処理を行う
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
