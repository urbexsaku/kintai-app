<header class="header">
  <div>
    <a href="/">
      <img class="header__logo" src="{{ asset('images/header-logo.png') }}" alt="ロゴ">
    </a>
  </div>
  <nav class="header_nav">
    <ul>
      <li><a class="header__link" href="/mypage">勤怠</a></li>
      <li><a class="header__link" href="/mypage">勤怠一覧</a></li>
      <li><a class="header__link" href="/mypage">申請</a></li>
      <li>
        <form action="/logout" method="post">
          @csrf
          <button class="header__logout" type="submit">ログアウト</button>
        </form>
      </li>
    </ul>
  </nav>
</header>