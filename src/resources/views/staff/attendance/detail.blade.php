@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="page__container">
  <div class="detail__inner">
    <h1 class="page__title">勤怠詳細</h1>

    <form class="detail__form" action="/attendance/detail/{{ $attendance->id }}" method="post">
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
              <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in?->format('H:i')) }}" {{ $isPending ? 'disabled' : '' }}>
              <span class="detail__separator">～</span>
              <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out?->format('H:i')) }}" {{ $isPending ? 'disabled' : '' }}>
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
              <input type="time" name="start_at[]" value="{{ old('start_at.'.$loop->index, $breakRecord->start_at?->format('H:i')) }}" {{ $isPending ? 'disabled' : '' }}>
              <span class="detail__separator">～</span>
              <input type="time" name="end_at[]" value="{{ old('end_at.'.$loop->index, $breakRecord->end_at?->format('H:i')) }}" {{ $isPending ? 'disabled' : '' }}>
            </div>

            <div class="detail__error">
              @error('start_at.'.$loop->index)
              <p>{{ $message }}</p>
              @enderror

              @error('end_at.'.$loop->index)
              <p>{{ $message }}</p>
              @enderror
            </div>

          </td>
        </tr>
        @endforeach
        <tr class="detail__row">
          <th class="detail__label">休憩{{ $attendance->breakRecords->count() + 1 }}</th>
          <td class="detail__text">
            <div class="detail__inline">
              <input type="time" name="start_at[]" value="{{ old('start_at.'.$attendance->breakRecords->count()) }}" {{ $isPending ? 'disabled' : '' }}>
              <span class="detail__separator">～</span>
              <input type="time" name="end_at[]" value="{{ old('end_at.'.$attendance->breakRecords->count()) }}" {{ $isPending ? 'disabled' : '' }}>
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
          <td class="detail__text">
            <div class="detail__comment">
              <textarea class="detail__textarea" name="comment" {{ $isPending ? 'disabled' : '' }}>{{ old('comment', $attendance->comment) }}</textarea>
            </div>

            <div class="detail__error">
              @error('comment')
              <p>{{ $message }}</p>
              @enderror
            </div>

          </td>
        </tr>
      </table>
      @if ($isPending)
        <p class="detail__notice">*承認待ちのため修正はできません。</p>
      @elseif ($isWorking)
        <p class="detail__notice">*当日の勤怠は勤務終了後に修正申請できます。</p>
      @else
      <div class="page__button">
        <button class="page__button-submit" type="submit">修正</button>
      </div>
      @endif
    </form>
  </div>
</div>
@endsection