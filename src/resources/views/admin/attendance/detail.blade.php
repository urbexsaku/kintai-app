@extends('layouts.app')

@section('title','勤怠詳細（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css')  }}">
@endsection

@section('content')
<div class="detail__content">
  <div class="detail__inner">
    <h1 class="detail__title">勤怠詳細</h1>

    <form class="detail__form" action="/admin/attendance/{{ $attendance->id }}" method="post">
      @csrf
      <table class="detail__table">
        <tr class="detail__row">
          <th class="detail__label">名前</th>
          <td class="detail__text detail__text--name">{{ $attendance->user->name }}</td>
        </tr>
        <tr class="detail__row">
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
              <input type="time" name="clock_in" value="{{ $attendance->clock_in?->format('H:i') }}">
              <span class="detail__separator">～</span>
              <input type="time" name="clock_out" value="{{ $attendance->clock_out?->format('H:i') }}">
            </div>

            <div class="detail__error">
              @error('clock_in')
                <p>{{ $message }}</p>
              @enderror
              @error('clock_out')
                <p>{{ $message }}</p>
              @enderror
            </div>
          </td>
        </tr>
        @foreach ($attendance->breakRecords as $breakRecord)
        <tr class="detail__row">
          <th class="detail__label">休憩{{ $loop->iteration }}</th> <!-- 1からループ回数表示 -->
          <td class="detail__text">
            <div class="detail__inline">
              <input type="time" name="start_at[]" value="{{ $breakRecord->start_at->format('H:i') }}">
              <span class="detail__separator">～</span>
              <input type="time" name="end_at[]" value="{{ $breakRecord->end_at->format('H:i') }}">
            </div>

            <div class="detail__error">
              @error('start_at.'.$loop->index)
              <p>{{ $message }}</P>
              @enderror

              @error('end_at.'.$loop->index)
              <p>{{ $message }}</P>
              @enderror
            </div>

          </td>
        </tr>
        @endforeach
        <tr class="detail__row">
          <th class="detail__label">休憩{{ $attendance->breakRecords->count() +1 }}</th>
          <td class="detail__text">
            <div class="detail__inline">
              <input type="time" name="start_at[]">
              <span class="detail__separator">～</span>
              <input type="time" name="end_at[]">
            </div>

            <div class="detail__error">
              @error('start_at.'.$attendance->breakRecords->count())
              <p>{{ $message }}</p>
              @enderror

              @error('end_at.'.$attendance->breakRecords->count() )
              <p>{{ $message }}</p>
              @enderror
            </div>

          </td>
        </tr>
        <tr class="detail__row">
          <th class="detail__label">備考</th>
          <td>
            <textarea class="detail__textarea" name="comment"></textarea>

            <div class="detail__error">
              @error('comment')
              <p>{{ $message }}</p>
              @enderror
            </div>

          </td>
        </tr>
      </table>
      @if (session('message'))
      <p class="detail__notice">{{ session('message') }}</p>
      @endif
      <div class="detail__button">
        <button class="detail__button-submit">修正</button>
      </div>
    </form>
  </div>
</div>
@endsection