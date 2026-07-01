@extends('layouts.app')

@section('title','スタッフ別勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/monthly.css')  }}">
@endsection

@section('content')
<div class="page__container">
  <div class="page__inner">
    <h1 class="page__title">{{ $user->name }}さんの勤怠</h1>

    <div class="page__header">
      <a class="monthly__link" href="/admin/attendance/staff/{{ $user->id }}?month={{ $previousMonth }}">
        <img class="monthly__arrow" src="{{ asset('images/arrow.png') }}" alt="矢印">
        前月
      </a>
      <div class="monthly__current">
        <img class="monthly__calendar" src="{{ asset('images/calendar.png') }}" alt="カレンダー">
        <p class="monthly__text">{{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}</p>
      </div>
      <a class="monthly__link" href="/admin/attendance/staff/{{ $user->id }}?month={{ $nextMonth }}">
        翌月
        <img class="monthly__arrow monthly__arrow--next" src="{{ asset('images/arrow.png') }}" alt="矢印">
      </a>
    </div>

    <div class="page__wrapper">
      <table class="page__table">
        <thead>
          <tr class="monthly__row">
            <th class="monthly__head">日付</th>
            <th class="monthly__head">出勤</th>
            <th class="monthly__head">退勤</th>
            <th class="monthly__head">休憩</th>
            <th class="monthly__head">合計</th>
            <th class="monthly__head">詳細</th>
          </tr>
        </thead>

        <tbody>
          @foreach ($dates as $date)

          <!-- 勤怠がない日はnull取得 -->
          @php
          $attendance = $attendanceMap[$date->format('Y-m-d')] ?? null;
          @endphp

          <tr class="monthly__row">
            <td class="monthly__data">
              {{ $date->format('m/d') }}
              ({{ ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] }})
            </td>
            <td class="monthly__data">
              {{ $attendance?->clock_in?->format('H:i') }}
            </td>
            <td class="monthly__data">
              {{ $attendance?->clock_out?->format('H:i') }}
            </td>
            <td class="monthly__data">
              {{ $attendance?->total_break }}
            </td>
            <td class="monthly__data">
              {{ $attendance?->work_time }}
            </td>
            <td class="monthly__data">
              @if($date->isToday() || $date->isFuture())
              @elseif($attendance)
              <a class="monthly__detail" href="/admin/attendance/{{ $attendance->id }}">詳細</a>
              @else
              <p class="monthly__detail">詳細</p>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <form action="/admin/attendance/staff/{{ $user->id }}/export" method="get">
      <input type="hidden" name="month" value="{{ $currentMonth }}">
      <div class="page__button">
        <button class="page__button-submit" type="submit">CSV出力</button>
      </div>
    </form>
  </div>
</div>
@endsection