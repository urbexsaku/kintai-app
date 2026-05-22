@extends('layouts.app')

@section('title','勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css')  }}">
@endsection

@section('content')
<div class="detail__content">
  <div class="detail__inner">
    <h1 class="detail__title">勤怠詳細</h1>
  
    <form class="detail__form" action="/attendance/detail/{{ $attendance->id }}" method="post">
      @csrf
      <table class="detail__table">
        <tr class="detail__row">
          <th class="detail__label">名前</th>
          <td class="detail__text">{{ $attendance->user->name }}</td>
        </tr>
        <tr  class="detail__row">
          <th class="detail__label">日付</th>
          <td class="detail__text">
            <div class="detail__inline">
              <span>{{ $attendance->work_date->format('Y年') }}</span>
              <span></span>
              <span>{{ $attendance->work_date->format('n月j日') }}</span>
            </div>
          </td>
        </tr>
        <tr class="detail__row">
          <th class="detail__label">出勤・退勤</th>
          <td class="detail__text">
            <div class="detail__inline">
              <input type="text" name="clock_in" value="{{ $attendance->clock_in->format('H:i') }}" {{ $isPending ? 'disabled' : '' }}>
              <span class="detail__separator">～</span>
              <input type="text" name="clock_out" value="{{ $attendance->clock_out->format('H:i') }}" {{ $isPending ? 'disabled' : '' }}>
            </div>
          </td>
        </tr>
        @foreach ($attendance->breakRecords as $breakRecord)
        <tr class="detail__row">
          <th class="detail__label">休憩{{ $loop->iteration }}</th> <!-- 1からループ回数表示 -->
          <td class="detail__text">
            <div class="detail__inline">
              <input type="text" name="start_at[]" value="{{ $breakRecord->start_at->format('H:i') }}" {{ $isPending ? 'disabled' : '' }}>
              <span class="detail__separator">～</span>
              <input type="text" name="end_at[]" value="{{ $breakRecord->end_at->format('H:i') }}" {{ $isPending ? 'disabled' : '' }}>
            </div>
          </td>
        </tr>
        @endforeach
        <tr class="detail__row">
          <th class="detail__label">休憩{{ $attendance->breakRecords->count() +1 }}</th>
          <td class="detail__text">
            <div class="detail__inline">
              <input type="text" name="start_at[]" {{ $isPending ? 'disabled' : '' }}>
              <span class="detail__separator">～</span>
              <input type="text" name="end_at[]" {{ $isPending ? 'disabled' : '' }}>
            </div>
          </td>
        </tr>
        <tr class="detail__row">
          <th class="detail__label">備考</th>
          <td><textarea class="detail__textarea" name="comment" {{ $isPending ? 'disabled' : '' }}></textarea></td>
        </tr>
      </table>
      @if ($isPending)
      <p class="detail__pending">*承認待ちのため修正はできません。</p>
      @else
      <div class="detail__button">
        <button class="detail__button-submit">修正</button>
      </div>
      @endif
    </form>
  </div>
</div>
@endsection