@extends('layouts.app')

@section('title','会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

@include('components.header')
<div class="auth-form__content">
  <h1 class="auth-form__heading">会員登録</h1>
  <form class="auth-form" action="{{ route('register') }}" method="post" novalidate>
    @csrf
    <div class="auth-form__group">
      <div class="auth-form__group-title">
        <span class="auth-form__label">ユーザー名</span>
      </div>
      <div class="auth-form__group-content">
        <div class="auth-form__input">
          <input type="text" name="name" value="{{ old('name') }}">
        </div>
        <div class="auth-form__error">
          @error('name')
          {{ $message }}
          @enderror
        </div>
      </div>
    </div>

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

    <div class="auth-form__group">
      <div class="auth-form__group-title">
        <span class="auth-form__label">確認用パスワード</span>
      </div>
      <div class="auth-form__group-content">
        <div class="auth-form__input">
          <input type="password" name="password_confirmation">
        </div>
        <div class="auth-form__error">
          @error('password')
          {{ $message }}
          @enderror
        </div>
      </div>
    </div>

    <div class="auth-form__button">
      <button type="submit" class="auth-form__button-submit">登録する</button>
    </div>
    <a class="auth-form__link" href="/login">ログインはこちら</a>
  </form>
</div>
@endsection