<li><a class="header__link" href="/attendance">勤怠</a></li>
<li><a class="header__link" href="/attendance/list">勤怠一覧</a></li>
<li><a class="header__link" href="/mypage">申請</a></li>
<li>
  <form action="/logout" method="post">
    @csrf
    <button class="header__logout" type="submit">ログアウト</button>
  </form>
</li>