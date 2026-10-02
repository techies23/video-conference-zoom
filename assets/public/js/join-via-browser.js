/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};

;// CONCATENATED MODULE: ./src/public/js/moment-parser.js
/**
 * Helper to map Moment tokens and native built-in preset constants to Intl options
 */
function parseMomentFormatToIntl(formatStr) {
  // Normalize layout input context
  if (!formatStr) {
    formatStr = 'LLLL';
  }

  // 1. Resolve Moment's Core Built-in Localized Format Constants
  switch (formatStr) {
    case 'LLLL':
      // Example: Wednesday, May 6, 2020 05:00 PM
      return {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric'
      };
    case 'llll':
      // Example: Wed, May 6, 2020 05:00 AM
      return {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric'
      };
    case 'lll':
      // Example: May 6, 2020 05:00 AM
      return {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric'
      };
    case 'L LT':
      // Example: 05/06/2020 03:00 PM
      return {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: 'numeric',
        minute: '2-digit'
      };
    case 'l LT':
      // Example: 5/6/2020 03:00 PM
      return {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
      };
  }

  // 2. Fallback: Parse Custom Layout String Tokens (When 'custom' radio option is chosen)
  const options = {};

  // Explicit Hour Cycle Precision Check (12-hour vs 24-hour markers)
  if (formatStr.includes('H')) {
    options.hour12 = false;
  } else if (formatStr.includes('h') || formatStr.includes('a') || formatStr.includes('A')) {
    options.hour12 = true;
  }

  // Day of Week
  if (formatStr.includes('dddd')) options.weekday = 'long';else if (formatStr.includes('ddd')) options.weekday = 'short';

  // Year
  if (formatStr.includes('YYYY')) options.year = 'numeric';else if (formatStr.includes('YY')) options.year = '2-digit';

  // Month
  if (formatStr.includes('MMMM')) options.month = 'long';else if (formatStr.includes('MMM')) options.month = 'short';else if (formatStr.includes('MM')) options.month = '2-digit';else if (formatStr.includes('M')) options.month = 'numeric';

  // Day of Month
  if (formatStr.includes('DD')) options.day = '2-digit';else if (formatStr.includes('D')) options.day = 'numeric';

  // Hours
  if (formatStr.includes('HH') || formatStr.includes('hh')) options.hour = '2-digit';else if (formatStr.includes('H') || formatStr.includes('h')) options.hour = 'numeric';

  // Minutes
  if (formatStr.includes('mm')) options.minute = '2-digit';else if (formatStr.includes('m')) options.minute = 'numeric';

  // Seconds
  if (formatStr.includes('ss')) options.second = '2-digit';else if (formatStr.includes('s')) options.second = 'numeric';
  return options;
}
;// CONCATENATED MODULE: ./src/public/js/join-via-browser.js

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
      const customMomentFormat = typeof zvc_strings !== 'undefined' && zvc_strings.date_format ? zvc_strings.date_format : 'LLLL';
      const intlOptions = parseMomentFormatToIntl(customMomentFormat);

      // Format localized meeting start time
      try {
        const formatter = new Intl.DateTimeFormat(lang, intlOptions);
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