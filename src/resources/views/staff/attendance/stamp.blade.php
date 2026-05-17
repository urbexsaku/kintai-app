@extends('layouts.app')

@section('title','勤怠打刻')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp.css')  }}">
@endsection

@section('content')
<div class="attendance">
  <div class="attendance__content">
    <div class="attendance__status">
      <p>
        @if(!$attendance)
        勤務外
        @elseif($isWorking)
        出勤中
        @elseif($isBreaking)
        休憩中
        @elseif($isFinished)
        退勤済
        @endif
      </p>
    </div>

    <div class="attendance__clock" id="current-time"></div>

    <div class="attendance__stamp">
      {{-- 出勤前 --}}
      @if(!$attendance)

      <form action="/attendance/clock-in" method="post">
        @csrf
        <button type="submit" class="attendance__button">出勤</button>
      </form>

      {{-- 出勤中 --}}
      @elseif($isWorking)
      <form action="/attendance/clock-out" method="post">
        @csrf
        <button type="submit" class="attendance__button">退勤</button>
      </form>

      <form action="/attendance/break-start" method="post">
        @csrf
        <button type="submit" class="attendance__button attendance__button--break">休憩入</button>
      </form>

      {{-- 休憩中 --}}
      @elseif($isBreaking)
      <form action="/attendance/break-end" method="post">
        @csrf
        <button type="submit" class="attendance__button attendance__button--break">休憩戻</button>
      </form>

      {{-- 退勤後 --}}
      @elseif($isFinished)
      <p class="attendance__message">お疲れさまでした。</p>
      @endif
    </div>

    @if(session('message'))
    <div class="error_message">
      <p>{{ session('message') }}</p>
    </div>
    @endif

  </div>

</div>
<script src="{{ asset('js/stamp.js') }}"></script>
@endsection