@extends('layouts.app')

@section('title','勤怠打刻')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp.css')  }}">
@endsection

@section('content')
<div class="attendance">
  <div class="attendance-status">
    @if(!$attendance)<p>勤務外</p>
    @elseif($isWordking)<p>出勤中</p>
    @elseif($isBreaking)<p>休憩中</p>
    @elseif($isFinished)<p>退勤済</p>
  </div>
  <div id="current-time"></div>

  {{-- 出勤前 --}}
  @if(!$attendance)

  <form action="/attendance/clock-in" method="post">
    @csrf
    <button type="submit">出勤</button>
  </form>

  {{-- 出勤中 --}}
  @elseif($isWordking)
  <div class="attendance-button">
    <form action="/attendance/clock-out" method="post">
      @csrf
      <button type="submit">退勤</button>
    </form>

    <form action="/attendance/break-start" method="post">
      @csrf
      <button type="submit">休憩入</button>
    </form>
  </div>

  {{-- 休憩中 --}}
  @elseif($isBreaking)
  <form action="/attendance/break-end" method="post">
    @csrf
    <button type="submit">休憩戻</button>
  </form>

  {{-- 退勤後 --}}
  @elseif($isFinished)
  <p>お疲れさまでした。</p>
  @endif
</div>
<script src="{{ asset('js/stamp.js') }}"></script>
@endsection