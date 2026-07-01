<li><a class="header__link" href="/attendance">Time Clock</a></li>
<li><a class="header__link" href="/attendance/list">Attendance</a></li>
<li><a class="header__link" href="/stamp_correction_request/list">Requests</a></li>
<li><a class="header__link" href="/attendance/report">Report</a></li>
<li>
  <form action="/logout" method="post">
    @csrf
    <button class="header__logout" type="submit">Logout</button>
  </form>
</li>