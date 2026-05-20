@extends('layouts.app')

@section('title', request()->routeIs('admin.login') ? '管理者ログイン' : 'ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-form__content">
  <h1 class="auth-form__heading">
    {{ request()->routeIs('admin.login') ? '管理者ログイン' : 'ログイン' }}
  </h1>

  <!-- ルートでpost先の分岐 -->
  <form class="auth-form" action="{{ request()->routeIs('admin.login') ? route('admin.login') : route('login') }}" method="post">
    @csrf
    <div class="auth-form__group">
      <div class="auth-form__group-title">
        <span class="auth-form__label">メールアドレス</span>
      </div>
      <div class="auth-form__group-content">
        <div class="auth-form__input">
          <input type="email" name="email" value="{{ old('email') }}">
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
        <span class="auth-form__label">パスワード</span>
      </div>
      <div class="auth-form__group-content">
        <div class="auth-form__input">
          <input type="password" name="password">
        </div>
        <div class="auth-form__error">
          @error('password')
          {{ $message }}
          @enderror
        </div>
      </div>
    </div>

    <div class="auth-form__button">
      <button type="submit" class="auth-form__button-submit">
        {{ request()->routeIs('admin.login') ? '管理者ログインする' : 'ログインする' }}
      </button>
    </div>
    
    <!-- 一般ログイン画面のみリンク表示 -->
    @if(request()->routeIs('login'))
    <a class="auth-form__link" href="/register">会員登録はこちら</a>
    @endif
  </form>
</div>
@endsection