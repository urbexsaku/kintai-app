<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title')</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/common.css') }}">
  @yield('css')
</head>

<body>
  <header class="header">
    <div>
      <img class="header__logo" src="{{ asset('images/header-logo.png') }}" alt="ロゴ">
    </div>
    <nav class="header__nav">
      <ul>
        @auth
          @if(Auth::user()->admin_status)
            @include('components.header-admin')
          @else
            @include('components.header-staff')
          @endif
        @endauth
      </ul>
    </nav>
  </header>

  <main>
    @yield('content')
  </main>

</body>
</html>