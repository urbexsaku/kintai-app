const clock = document.getElementById('current-time');

function updateClock() {
  const now = new Date();

  const week = ['日', '月', '火', '水', '木', '金', '土'];
  
  const year = now.getFullYear();
  const month = now.getMonth() + 1;
  const day = now.getDate();
  const weekday = week[now.getDay()];

  const hours = String(now.getHours()).padStart(2, '0');
  const minutes = String(now.getMinutes()).padStart(2, '0');

  clock.innerHTML = `
    <p class="attendance__date">
      ${year}年${month}月${day}日(${weekday})
    </p>
    <p class="attendance__time">
      ${hours}:${minutes}
    </p> 
  `;
}

updateClock();

// updateClockの繰り返し実行間隔を1秒に設定
setInterval(updateClock, 1000);