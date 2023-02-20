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
      if (clock.length > 0) {
        var valueDate = clock.data('date');
        var mtgTimezone = clock.data('tz');

        // var dateFormat = moment(valueDate).format('MMM D, YYYY HH:mm:ss');

        var user_timezone = moment.tz.guess();
        if (user_timezone === 'Asia/Katmandu') {
          user_timezone = 'Asia/Kathmandu';
        }

        //Converting Timezones to locals
        var source_timezone = moment.tz(valueDate, mtgTimezone).format();
        var converted_timezone = moment.tz(source_timezone, user_timezone).format('MMM D, YYYY HH:mm:ss');
        var convertedTimezonewithoutFormat = moment.tz(source_timezone, user_timezone).format();

        //Check Time Difference for Validations
        var currentTime = moment().unix();
        var eventTime = moment(convertedTimezonewithoutFormat).unix();
        var diffTime = eventTime - currentTime;
        var lang = document.documentElement.lang;
        $('.sidebar-start-time').html(moment.parseZone(convertedTimezonewithoutFormat).locale(lang).format('LLLL'));
        var second = 1000,
          minute = second * 60,
          hour = minute * 60,
          day = hour * 24;

        // if time to countdown
        if (diffTime > 0) {
          var countDown = new Date(converted_timezone).getTime();
          var x = setInterval(function () {
            var now = new Date().getTime();
            var distance = countDown - now;
            document.getElementById('dpn-zvc-timer-days').innerText = Math.floor(distance / day);
            document.getElementById('dpn-zvc-timer-hours').innerText = Math.floor(distance % day / hour);
            document.getElementById('dpn-zvc-timer-minutes').innerText = Math.floor(distance % hour / minute);
            document.getElementById('dpn-zvc-timer-seconds').innerText = Math.floor(distance % minute / second);
            if (distance < 0) {
              clearInterval(x);
              location.reload();
            }
          }, second);
        } else {
          // location.reload();
          $(clock).remove();
        }
      }
    }
  };
  video_conferencing_zoom_jbv.init();
});
/******/ })()
;