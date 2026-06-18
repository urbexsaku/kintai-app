@extends('layouts.app')

@section('title','勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css')  }}">
@endsection

@section('content')
<div class="page">
  <div class="detail__inner">
    <h1 class="detail__title">勤怠詳細</h1>
    <table class="detail__table">
      <tr class="detail__row">
        <th class="detail__label">名前</th>
        <td class="detail__text detail__text--name">{{ $attendanceCorrectRequest->attendanceRecord->user->name }}</td>
      </tr>
      <tr class="detail__row">
        <th class="detail__label">日付</th>
        <td class="detail__text">
          <div class="detail__inline">
            <span>{{ $attendanceCorrectRequest->attendanceRecord->work_date->format('Y年') }}</span>
            <span></span>
            <span>{{ $attendanceCorrectRequest->attendanceRecord->work_date->format('n月j日') }}</span>
          </div>
        </td>
      </tr>
      <tr class="detail__row">
        <th class="detail__label">出勤・退勤</th>
        <td class="detail__text">
          <div class="detail__inline">
            <span>{{ $attendanceCorrectRequest->requested_clock_in?->format('H:i') }}</span>
            <span class="detail__separator">～</span>
            <span>{{ $attendanceCorrectRequest->requested_clock_out?->format('H:i') }}</span>
          </div>
        </td>
      </tr>
      @foreach ($attendanceCorrectRequest->breakCorrectRequests as $breakRecord)
      <tr class="detail__row">
        <th class="detail__label">休憩{{ $loop->iteration }}</th> <!-- 1からループ回数表示 -->
        <td class="detail__text">
          <div class="detail__inline">
            <span>{{ $breakRecord->requested_start_at->format('H:i') }}</span>
            <span class="detail__separator">～</span>
            <span>{{ $breakRecord->requested_end_at->format('H:i') }}</span>
          </div>
        </td>
      </tr>
      @endforeach
      <tr class="detail__row">
        <th class="detail__label">休憩{{ $attendanceCorrectRequest->breakCorrectRequests->count() +1 }}</th>
        <td class="detail__text">
          <div class="detail__inline">
            <span></span>
            <span class="detail__separator">～</span>
            <span></span>
          </div>
        </td>
      </tr>
      <tr class="detail__row">
        <th class="detail__label">備考</th>
        <td class="detail__text detail__text--comment">
          {{ $attendanceCorrectRequest->comment }}
        </td>
      </tr>
    </table>
    <p class="detail__pending">*承認待ちのため修正はできません。</p>
  </div>
</div>
@endsection