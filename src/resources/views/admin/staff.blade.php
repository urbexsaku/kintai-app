@extends('layouts.app')

@section('title','スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff.css')  }}">
@endsection

@section('content')
<div class="page">
  <div class="staff__inner">
    <h1 class="staff__title">スタッフ一覧</h1>
    <div class="staff__wrapper">
      <table class="staff__table">
        <tr class="staff__row">
          <th class="staff__head">名前</th>
          <th class="staff__head">メールアドレス</th>
          <th class="staff__head">月次勤怠</th>
        </tr>

        @foreach ($users as $user)
        <tr class="staff__row">
          <td class="staff__data">
            {{ $user->name }}
          </td>
          <td class="staff__data">
            {{ $user->email }}
          </td>
          <td class="staff__data">
            <a class="staff__detail" href="/admin/attendance/staff/{{ $user->id }}">詳細</a>
          </td>
        </tr>
        @endforeach
      </table>
    </div>
  </div>
</div>
@endsection