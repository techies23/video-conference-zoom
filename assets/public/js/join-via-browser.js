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
      const clock = this.$timer;
      if (!clock.length) {
        return;
      }
      const rawDate = clock.data('date');
      let userTimezone = (Intl && Intl.DateTimeFormat ? Intl.DateTimeFormat().resolvedOptions().timeZone : 'UTC') || 'UTC';
      if (userTimezone === 'Asia/Katmandu') {
        userTimezone = 'Asia/Kathmandu';
      }

      // Parse meeting date to timestamp
      const targetDate = new Date(rawDate);
      const targetEpochMs = targetDate.getTime();
      const diffTime = targetEpochMs - Date.now();
      const lang = document.documentElement.lang || navigator.language || 'en-US';

      // Format localized meeting start time
      try {
        const formatter = new Intl.DateTimeFormat(lang, {
          timeZone: userTimezone,
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: 'numeric'
        });
        $('.sidebar-start-time').html(formatter.format(targetDate));
      } catch (e) {
        $('.sidebar-start-time').html(targetDate.toLocaleString(lang));
      }
      const second = 1000;
      const minute = second * 60;
      const hour = minute * 60;
      const day = hour * 24;

      // Meeting has already started
      if (diffTime <= 0) {
        $(clock).remove();
        return;
      }
      const x = setInterval(function () {
        const distance = targetEpochMs - Date.now();
        const days = Math.floor(distance / day);
        const hours = Math.floor(distance % day / hour);
        const minutes = Math.floor(distance % hour / minute);
        const seconds = Math.floor(distance % minute / second);
        const daysEl = document.getElementById('dpn-zvc-timer-days');
        const hoursEl = document.getElementById('dpn-zvc-timer-hours');
        const minutesEl = document.getElementById('dpn-zvc-timer-minutes');
        const secondsEl = document.getElementById('dpn-zvc-timer-seconds');
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