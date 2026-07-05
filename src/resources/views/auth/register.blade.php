@extends('layouts.app')

@section('title','Register')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="page__container">
  <h1 class="auth-form__heading">Register</h1>
  <div class="auth-form__box">
    <form class="auth-form" action="{{ route('register') }}" method="post">
      @csrf
      <div class="auth-form__group">
        <div class="auth-form__group-title">
          <label class="auth-form__label" for="name">名前</label>
        </div>
        <div class="auth-form__group-content">
          <div class="auth-form__input">
            <input id="name" type="text" name="name" value="{{ old('name') }}">
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

      <div class="auth-form__group">
        <div class="auth-form__group-title">
          <label class="auth-form__label" for="password_confirmation">パスワード確認</label>
        </div>
        <div class="auth-form__group-content">
          <div class="auth-form__input">
            <input id="password_confirmation" type="password" name="password_confirmation">
          </div>
          <div class="auth-form__error">
            @error('password')
            {{ $message }}
            @enderror
          </div>
        </div>
      </div>

      <div class="auth-form__button">
        <button type="submit" class="page__button-submit">登録する</button>
      </div>

      <a class="auth-form__link" href="/login">ログインはこちら</a>
    </div>  
   </form>
</div>
@endsection