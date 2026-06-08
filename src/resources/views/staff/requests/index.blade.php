@extends('layouts.app')

@section('title','申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css')  }}">
@endsection

@section('content')
<div class="request__content">
  <div class="request__inner">
    <h1 class="request__title">申請一覧</h1>


    <nav class="request__tab">
      <ul>
        <li class="request__tab-item {{ $page === 'pending' ? 'is-active' : '' }}">
          <a href="/stamp_correction_request/list?page=pending">承認待ち</a>
        </li>
        <li class="request__tab-item {{ $page === 'approved' ? 'is-active' : '' }}">
          <a href="/stamp_correction_request/list?page=approved">承認済み</a>
        </li>
      </ul>
    </nav>

    <div class="request__wrapper">
      <table class="request__table">
        <tr class="request__row">
          <th class="request__head">状態</th>
          <th class="request__head">名前</th>
          <th class="request__head">対象日時</th>
          <th class="request__head">申請理由</th>
          <th class="request__head">申請日時</th>
          <th class="request__head">詳細</th>
        </tr>

        @foreach ($attendanceCorrectRequests as $attendanceCorrectRequest)
        <tr class="request__row">
          <td class="request__data">
            {{ $attendanceCorrectRequest->status_label }}
          </td>
          <td class="request__data">
            {{ $attendanceCorrectRequest->attendanceRecord?->user?->name }}
          </td>
          <td class="request__data">
            {{ $attendanceCorrectRequest->attendanceRecord->work_date->format('Y/m/d') }}
          </td>
          <td class="request__data">
            {{ $attendanceCorrectRequest->comment }}
          </td>
          <td class="request__data">
            {{ $attendanceCorrectRequest->created_at->format('Y/m/d') }}
          </td>
          <td class="request__data">
            @if($attendanceCorrectRequest->status === "approved")
            <a class="request__detail" href="/attendance/detail/{{ $attendanceCorrectRequest->attendanceRecord->id }}">詳細</a>
            @else
            <a class="request__detail" href="/stamp_correction_request/detail/{{ $attendanceCorrectRequest->id }}">詳細</a>
            @endif
          </td>
        </tr>
        @endforeach
      </table>
    </div>
  </div>
</div>
@endsection