@extends('layouts.app')

@section('title','マイ勤怠レポート')

@section('css')
<link rel="stylesheet" href="{{ asset('css/report.css')  }}">
@endsection

@section('content')
<div class="report__content">
  <div class="report__inner">
    <h1 class="report__title">マイ勤怠レポート</h1>
    <p class="report__description">過去6ヶ月の勤怠データから集計しています。</p>

    <h2 class="report__header">基本サマリー</h2>
    <div class="report__wrapper">
      <div class="report__group">
        <span class="report__label">総労働時間</span>
        <span class="report__value"> {{ $totalWorkTime }}</span>
      </div>
      <div class="report__group">
        <span class="report__label">総残業時間</span>
        <span class="report__value"> {{ $totalOvertimeTime }}</span>
      </div>
      <div class="report__group">
        <span class="report__label">平均労働時間 / 日</span>
        <span class="report__value"> {{ $averageWorkTime }}</span>
      </div>
    </div>

    <h2 class="report__header">月次推移（過去6ヶ月）</h2>
     <table class="report__table">
      <thead>
        <tr class="report__row">
          <th class="report__head">月</th>
          <th class="report__head">労働時間</th>
          <th class="report__head">残業時間</th>
        </tr>
      </thead>
    
      <tbody>
        @foreach ($monthlyReports as $report)
        <tr class="report__row">
          <td class="report__data">{{ $report['month'] }}</td>
          <td class="report__data">{{ $report['work_time'] }}</td>
          <td class="report__data">{{ $report['overtime_time'] }}</td>
        </tr>
        @endforeach
      </tbody>     
    </table>

    <h2 class="report__header">今月の異常検知</h2>
    <p>基準：始業 09:00 / 終業 18:00 / 長時間労働は1日10時間超</p>
    <div class="report__wrapper">
      <div class="report__group">
        <span class="report__label">遅刻回数</span>
        <span class="report__value">{{ $lateCount }}回</span>
      </div>
      <div class="report__group">
        <span class="report__label">早退回数</span>
        <span class="report__value">{{ $earlyLeaveCount }}回</span>
      </div>
      <div class="report__group">
        <span class="report__label">長時間労働日数</span>
        <span class="report__value">{{ $longWorkCount }}日</span>
      </div>
    </div>
  </div>
</div>
@endsection