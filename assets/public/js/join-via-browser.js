/******/ (function() { // webpackBootstrap
var __webpack_exports__ = {};
jQuery(function ($) {
  var video_conferencing_zoom_jbv = {
    init: function () {
      this.cacheVariables();
      this.countDown();
    },
    cacheVariables: function () {
      this.$timer = $('#dpn-zvc-timer');
    },
    countDown: function () {
      var clock = this.$timer;
      if (!clock.length) {
        return;
      }
      var valueDate = clock.data('date');
      var mtgTimezone = clock.data('tz');
      var userTimezone = moment.tz.guess();
      if (userTimezone === 'Asia/Katmandu') {
        userTimezone = 'Asia/Kathmandu';
      }

      // Meeting time in the meeting timezone
      var meetingTime = moment.tz(valueDate, mtgTimezone);

      // Meeting time converted to the user's timezone
      var userMeetingTime = meetingTime.clone().tz(userTimezone);

      // Check time difference
      var diffTime = userMeetingTime.diff(moment());
      var lang = document.documentElement.lang;
      $('.sidebar-start-time').html(userMeetingTime.clone().locale(lang).format('LLLL'));
      var second = 1000;
      var minute = second * 60;
      var hour = minute * 60;
      var day = hour * 24;

      // Meeting has already started
      if (diffTime <= 0) {
        $(clock).remove();
        return;
      }
      var countDown = userMeetingTime.valueOf();
      var x = setInterval(function () {
        var distance = countDown - Date.now();
        var days = Math.floor(distance / day);
        var hours = Math.floor(distance % day / hour);
        var minutes = Math.floor(distance % hour / minute);
        var seconds = Math.floor(distance % minute / second);
        var daysEl = document.getElementById('dpn-zvc-timer-days');
        var hoursEl = document.getElementById('dpn-zvc-timer-hours');
        var minutesEl = document.getElementById('dpn-zvc-timer-minutes');
        var secondsEl = document.getElementById('dpn-zvc-timer-seconds');
        if (daysEl) {
          daysEl.innerText = Math.max(0, days);
        }
        if (hoursEl) {
          hoursEl.innerText = Math.max(0, hours);
        }
        if (minutesEl) {
          minutesEl.innerText = Math.max(0, minutes);
        }
        if (secondsEl) {
          secondsEl.innerText = Math.max(0, seconds);
        }
        if (distance <= 0) {
          clearInterval(x);
          location.reload();
        }
      }, second);
    }
  };
  video_conferencing_zoom_jbv.init();
});
/******/ })()
;