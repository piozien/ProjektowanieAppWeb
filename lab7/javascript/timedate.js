function gettheDate() {
  var Toodays = new Date();
  var TheDate =
    "" +
    (Toodays.getMonth() + 1) +
    " / " +
    Toodays.getDate() +
    " / " +
    (Toodays.getFullYear() - 2000);
  document.getElementById("data").innerHTML = TheDate;
}

var timeID = null;
var timerRunning = false;

function stopclock() {
  if (timerRunning) {
    clearTimeout(timeID);
  }
  timerRunning = false;
}

function startclock() {
  stopclock();
  gettheDate();
  showtime();
}

function showtime() {
  var now = new Date();
  var hours = now.getHours();
  var minutes = now.getMinutes();
  var seconds = now.getSeconds();

  var timeValue = "" + (hours > 12 ? hours - 12 : hours);
  timeValue += (minutes < 10 ? ":0" : ":") + minutes;
  timeValue += (seconds < 10 ? ":0" : ":") + seconds;
  timeValue += hours >= 12 ? " P.M." : " A.M.";

  document.getElementById("zegarek").innerHTML = timeValue;

  timeID = setTimeout(showtime, 1000); 
  timerRunning = true;
}
