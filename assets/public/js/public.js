/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};

jQuery(function ($) {
  var video_conferencing_zoom_api_public = {
    init: function () {
      this.cacheVariables();
      this.countDownTimerMoment();
      this.evntLoaders();
    },
    cacheVariables: function () {
      this.$timer = $('#dpn-zvc-timer');
      this.changeMeetingState = $('.vczapi-meeting-state-change');
    },
    evntLoaders: function () {
      $(document).ready(this.setTimezone.bind(this));
      //End and Resume Meetings
      $(this.changeMeetingState).on('click', this.meetingStateChange.bind(this));
    },
    countDownTimerMoment: function () {
      var clock = this.$timer;
      if (!clock.length) {
        return;
      }
      var valueDate = clock.data('date');
      var mtgTimezone = clock.data('tz');
      var mtgState = clock.data('state');
      var userTimezone = moment.tz.guess();
      if (userTimezone === 'Asia/Katmandu') {
        userTimezone = 'Asia/Kathmandu';
      }

      // Meeting time in the meeting timezone
      var meetingTime = moment.tz(valueDate, mtgTimezone);

      // Meeting time converted to user's timezone
      var userMeetingTime = meetingTime.clone().tz(userTimezone);

      // Check time difference
      var diffTime = userMeetingTime.diff(moment());
      var lang = document.documentElement.lang;
      var dateFormat = zvc_strings.date_format !== '' ? zvc_strings.date_format : 'LLLL';
      $('.sidebar-start-time').html(userMeetingTime.clone().locale(lang).format(dateFormat));
      $('.vczapi-single-meeting-timezone').html(userTimezone);
      var second = 1000;
      var minute = second * 60;
      var hour = minute * 60;
      var day = hour * 24;
      if (mtgState === 'ended') {
        $(clock).html('<div class="dpn-zvc-meeting-ended">' + '<h3>' + zvc_strings.meeting_ended + '</h3>' + '</div>');
        return;
      }
      if (diffTime <= 0) {
        $(clock).remove();
        return;
      }
      var countDown = userMeetingTime.valueOf();
      var x = setInterval(function () {
        var distance = countDown - Date.now();
        if (distance <= 0) {
          clearInterval(x);
          $(clock).html('<div class="dpn-zvc-meeting-ended">' + '<h3>' + zvc_strings.meeting_starting + '</h3>' + '</div>');
          return;
        }
        var days = Math.floor(distance / day);
        var hours = Math.floor(distance % day / hour);
        var minutes = Math.floor(distance % hour / minute);
        var seconds = Math.floor(distance % minute / second);
        var daysEl = document.getElementById('dpn-zvc-timer-days');
        var hoursEl = document.getElementById('dpn-zvc-timer-hours');
        var minutesEl = document.getElementById('dpn-zvc-timer-minutes');
        var secondsEl = document.getElementById('dpn-zvc-timer-seconds');
        if (daysEl) {
          daysEl.innerText = days;
        }
        if (hoursEl) {
          hoursEl.innerText = hours;
        }
        if (minutesEl) {
          minutesEl.innerText = minutes;
        }
        if (secondsEl) {
          secondsEl.innerText = seconds;
        }
      }, second);
    },
    /**
     * Set timezone and get links accordingly
     */
    setTimezone: function () {
      var timezone = moment.tz.guess();
      if (timezone === 'Asia/Katmandu') {
        timezone = 'Asia/Kathmandu';
      }
      try {
        if (typeof mtg_data !== undefined && mtg_data.page === 'single-meeting') {
          $('.dpn-zvc-sidebar-content').after('<div class="dpn-zvc-sidebar-box remove-sidebar-loder-text"><p>Loading..Please wait..</p></div>');
          var pageData = {
            action: 'set_timezone',
            user_timezone: timezone,
            post_id: mtg_data.post_id,
            mtg_timezone: mtg_data.timezone,
            start_date: mtg_data.start_date,
            meeting_type: mtg_data.meeting_type,
            type: 'page'
          };
          $.post(mtg_data.ajaxurl, pageData).done(function (response) {
            if (response.success) {
              $('.dpn-zvc-sidebar-content').after(response.data);
            } else {
              $('.dpn-zvc-sidebar-content').after('<div class="dpn-zvc-sidebar-box vczapi-no-longer-valid">' + response.data + '</div>');
            }
            $('.remove-sidebar-loder-text').remove();
          });
        }

        /**
         * For shortcode
         * @deprecated 3.3.1
         */
        if (typeof mtg_data !== undefined && mtg_data.type === 'shortcode') {
          var shortcodeData = {
            action: 'set_timezone',
            user_timezone: timezone,
            mtg_timezone: mtg_data.timezone,
            join_uri: mtg_data.join_uri,
            browser_url: mtg_data.browser_url,
            start_date: mtg_data.start_date,
            type: 'shortcode'
          };
          $('.zvc-table-shortcode-duration').after('<tr class="remove-shortcode-loder-text"><td colspan="2">Loading.. Please wait..</td></tr>');
          $.post(mtg_data.ajaxurl, shortcodeData).done(function (response) {
            if (response.success) {
              $('.zvc-table-shortcode-duration').after(response.data);
            } else {
              $('.zvc-table-shortcode-duration').after('<tr><td colspan="2">' + response.data + '</td></tr>');
            }
            $('.remove-shortcode-loder-text').remove();
          });
        }
      } catch (e) {
        //leave blank
      }
    },
    /**
     * Change Meeting State
     * @param e
     */
    meetingStateChange: function (e) {
      e.preventDefault();
      var state = $(e.currentTarget).data('state');
      var post_id = $(e.currentTarget).data('postid');
      var postData = {
        id: $(e.currentTarget).data('id'),
        state: state,
        type: $(e.currentTarget).data('type'),
        post_id: post_id ? post_id : false,
        action: 'vczapi_meeting_state_change',
        nonce: vczapi_state.nonce
      };
      if (state === 'resume') {
        this.changeState(postData);
      } else if (state === 'end') {
        var c = confirm(vczapi_state.lang.confirm_end);
        if (c) {
          this.changeState(postData);
        } else {
          return;
        }
      }
    },
    /**
     * Change the state triggere now
     * @param postData
     */
    changeState: function (postData) {
      $.post(vczapi_state.ajaxurl, postData).done(function (response) {
        location.reload();
      });
    }
  };
  video_conferencing_zoom_api_public.init();
});
/******/ })()
;