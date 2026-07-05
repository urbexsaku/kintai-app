@extends('layouts.app')

@section('title', request()->routeIs('admin.login') ? 'Admin Login' : 'Login')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="page__container">
  <h1 class="auth-form__heading">
    {{ request()->routeIs('admin.login') ? 'Admin Login' : 'Login' }}
  </h1>

  <div class="auth-form__box">
    <form class="auth-form" action="{{ request()->routeIs('admin.login') ? route('admin.login') : route('login') }}" method="post">
      @csrf
      <div class="auth-form__group">
        <div class="auth-form__group-title">
          <label class="auth-form__label" for="email">メールアドレス</label>
        </div>
        <div class="auth-form__group-content">
          <div class="auth-form__input">
            <input id="email" type="email" name="email" value="{{ old('email') }}">
          </div>
          <div class="auth-form__error">
            @error('email')
            {{ $message }}
            @enderror
          </div>
        </div>
      </div>

      <div class="auth-form__group">
        <div class="auth-form__group-title">
          <label class="auth-form__label" for="password">パスワード</label>
        </div>
        <div class="auth-form__group-content">
          <div class="auth-form__input">
            <input id="password" type="password" name="password">
          </div>
          <div class="auth-form__error">
            @error('password')
            {{ $message }}
            @enderror
          </div>
        </div>
      </div>

    <div class="auth-form__button">
      <button type="submit" class="page__button-submit">
        {{ request()->routeIs('admin.login') ? '管理者ログインする' : 'ログインする' }}
      </button>
    </div>

    <!-- 一般ログイン画面のみリンク表示 -->
    @if (request()->routeIs('login'))
    <a class="auth-form__link" href="/register">会員登録はこちら</a>
    @endif
  </div>
 </form>
</div>
@endsection