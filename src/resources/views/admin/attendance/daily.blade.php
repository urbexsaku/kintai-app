@extends('layouts.app')

@section('title','勤怠一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/daily.css')  }}">
@endsection

@section('content')
<div class="page">
  <div class="daily__inner">
    <h1 class="daily__title">{{ \Carbon\Carbon::parse($currentDate)->format('Y年n月j日') }}の勤怠</h1>

    <nav class="daily__header">
      <a class="daily__link" href="/admin/attendance/list?date={{ $previousDate }}">
        <img class="daily__arrow" src="{{ asset('images/arrow.png') }}" alt="矢印">
        前日
      </a>
      <div class="daily__current">
        <img class="daily__calendar" src="{{ asset('images/calendar.png') }}" alt="カレンダー">
        <p class="daily__text">{{ \Carbon\Carbon::parse($currentDate)->format('Y/m/d') }}</p>
      </div>
      <a class="daily__link" href="/admin/attendance/list?date={{ $nextDate }}">
        翌日
        <img class="daily__arrow daily__arrow--next" src="{{ asset('images/arrow.png') }}" alt="矢印">
      </a>
    </nav>

    <div class="daily__wrapper">
      <table class="daily__table">
        <thead>
          <tr class="daily__row">
            <th class="daily__head">名前</th>
            <th class="daily__head">出勤</th>
            <th class="daily__head">退勤</th>
            <th class="daily__head">休憩</th>
            <th class="daily__head">合計</th>
            <th class="daily__head">詳細</th>
          </tr>
        </thead>

        <tbody>
          @foreach ($users as $user)
          <tr class="daily__row">
            <td class="daily__data">
              {{ $user->name }}
            </td>
            <td class="daily__data">
              {{ $user->attendance?->clock_in?->format('H:i') }}
            </td>
            <td class="daily__data">
              {{ $user->attendance?->clock_out?->format('H:i') }}
            </td>
            <td class="daily__data">
              {{ $user->attendance?->total_break }}
            </td>
            <td class="daily__data">
              {{ $user->attendance?->work_time }}
            </td>
            <td class="daily__data">
              @if($user->attendance)
              <a class="daily__detail" href="/admin/attendance/{{ $user->attendance->id }}">詳細</a>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection