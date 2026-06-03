<li><a class="header__link" href="/admin/attendance/list">勤怠一覧</a></li>
<li><a class="header__link" href="/admin/staff/list">スタッフ一覧</a></li>
<li><a class="header__link" href="/stamp_correction_request/list">申請一覧</a></li>
<li>
  <form action="/logout" method="post">
    @csrf
    <input type="hidden" name="admin_status" value="1">
    <button class="header__logout" type="submit">ログアウト</button>
  </form>
</li>