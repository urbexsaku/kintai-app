@extends('layouts.app')

@section('title','メール認証')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify.css') }}">
@endsection

@section('content')
<div class="verify">
  <div class="verify__content">
    <p class="verify__message">登録していただいたメールアドレスに認証メールを送付しました。<br>メール認証を完了してください。</p>

    <a href="http://localhost:8025/" class="verify__link" target="_blank">認証はこちらから</a>

    <div class="verify__group">
      @if (session('message'))
      <p class="verify__notice">{{ session('message') }}</p>
      @endif

      <form class="verify__resend" action="{{ route('verification.send') }}" method="post">
        @csrf
        <button class="verify__resend-button" type="submit">認証メールを再送する</button>
      </form>
    </div>
  </div>
</div>
@endsection