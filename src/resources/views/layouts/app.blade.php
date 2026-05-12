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

<header class="header">
  <div>
    <a href="/">
      <img class="header__logo" src="{{ asset('images/header-logo.png') }}" alt="ロゴ">
    </a>
  </div>
</header>

<main>
  @yield('content')
</main>

</body>

</html>