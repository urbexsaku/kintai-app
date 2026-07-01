<li><a class="header__link" href="/admin/attendance/list">Attendance</a></li>
<li><a class="header__link" href="/admin/staff/list">Staff</a></li>
<li><a class="header__link" href="/stamp_correction_request/list">Requests</a></li>
<li>
  <form action="/logout" method="post">
    @csrf
    <input type="hidden" name="admin_status" value="1">
    <button class="header__logout" type="submit">Logout</button>
  </form>
</li>