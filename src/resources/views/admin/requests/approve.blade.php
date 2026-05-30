@extends('layouts.app')

@section('title','修正申請承認')

@section('css')
<link rel="stylesheet" href="{{ asset('css/approve.css')  }}">
@endsection

@section('content')
<div class="approve__content">
  <div class="approve__inner">
    <h1 class="approve__title">勤怠詳細</h1>

    <table class="approve__table">
      <tr class="approve__row">
        <th class="approve__label">名前</th>
        <td class="approve__text approve__text--name">{{ $attendanceCorrectRequest->attendanceRecord?->user?->name }}</td>
      </tr>
      <tr class="approve__row">
        <th class="approve__label">日付</th>
        <td class="approve__text">
          <div class="approve__inline">
            <span>{{ $attendanceCorrectRequest->attendanceRecord->work_date->format('Y年') }}</span>
            <span></span>
            <span>{{ $attendanceCorrectRequest->attendanceRecord->work_date->format('n月j日') }}</span>
          </div>
        </td>
      </tr>
      <tr class="approve__row">
        <th class="approve__label">出勤・退勤</th>
        <td class="approve__text">
          <div class="approve__inline">
            <span>{{ $attendanceCorrectRequest->requested_clock_in?->format('H:i') }}</span>
            <span class="approve__separator">～</span>
            <span>{{ $attendanceCorrectRequest->requested_clock_out?->format('H:i') }}</span>
          </div>
        </td>
      </tr>
      @foreach ($attendanceCorrectRequest->breakCorrectRequests as $breakCorrectRequest)
      <tr class="approve__row">
        <th class="approve__label">休憩{{ $loop->iteration }}</th> <!-- 1からループ回数表示 -->
        <td class="approve__text">
          <div class="approve__inline">
            <span>{{ $breakCorrectRequest->requested_start_at->format('H:i') }}</span>
            <span class="approve__separator">～</span>
            <span>{{ $breakCorrectRequest->requested_end_at->format('H:i') }}</span>
          </div>
        </td>
      </tr>
      @endforeach
      <tr class="approve__row">
        <th class="approve__label">休憩{{ $attendanceCorrectRequest->breakCorrectRequets?->count() +1 }}</th>
        <td class="approve__text">
          <div class="approve__inline">
            <span></span>
            <span class="approve__separator">～</span>
            <span></span>
          </div>
        </td>
      </tr>
      <tr class="approve__row">
        <th class="approve__label">備考</th>
        <td>
          <div class="approve__comment">
            {{ $attendanceCorrectRequest->comment }}
          </div>
        </td>
      </tr>
    </table>
    @if (session('message'))
    <p class="approve__notice">{{ session('message') }}</p>
    @endif

    @if ($attendanceCorrectRequest->status === 'approved')
    <div class="approve__button">
      <button type="button" class="approve__button-submit approve__button-submit--approved">承認済み</button>
    </div>
    @else
    <form action="/admin/stamp_correction_request/approve/{{ $attendanceCorrectRequest->id }}" method="post">
      @csrf
      <div class="approve__button">
        <button class="approve__button-submit">承認</button>
      </div>
      @endif
    </form>
  </div>
</div>
@endsection