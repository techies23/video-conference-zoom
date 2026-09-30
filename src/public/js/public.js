import '../sass/style.scss'
import { formatForUser, guessTimeZone, normalizeTimeZone, resolveMeetingInstant, secondsUntil } from './utils/meeting-time'

jQuery(function ($) {

  var video_conferencing_zoom_api_public = {

    init: function () {
      this.cacheVariables()
      this.countDownTimer()
      this.evntLoaders()
    },

    cacheVariables: function () {
      this.$timer = $('#dpn-zvc-timer')
      this.changeMeetingState = $('.vczapi-meeting-state-change')
    },

    evntLoaders: function () {
      $(document).ready(this.setTimezone.bind(this))
      //End and Resume Meetings
      $(this.changeMeetingState).on('click', this.meetingStateChange.bind(this))
    },

    countDownTimer: function () {
      var clock = this.$timer
      if (clock.length === 0) {
        return
      }

      var instant = resolveMeetingInstant(clock.data('date'), clock.data('tz'))

      if (!instant) {
        return
      }

      // Single-meeting pages localise `zvc_strings`; the embed shortcode does not.
      // Both render the same countdown markup, so pick the mode from the globals
      // that are actually present rather than shipping two bundles of date-fns.
      var isSingleMeeting = typeof zvc_strings !== 'undefined'
      var lang = document.documentElement.lang

      if (isSingleMeeting) {
        var userTimezone = normalizeTimeZone(guessTimeZone())
        var dateFormat = zvc_strings.date_format !== '' ? zvc_strings.date_format : 'PPPPpp'

        $('.sidebar-start-time').text(formatForUser(instant, dateFormat, lang))
        $('.vczapi-single-meeting-timezone').text(userTimezone)
      } else {
        $('.sidebar-start-time').text(formatForUser(instant, 'PPPPpp', lang))
      }

      var second = 1000
      var minute = second * 60
      var hour = minute * 60
      var day = hour * 24

      if (isSingleMeeting && clock.data('state') === 'ended') {
        $(clock).html('<div class=\'dpn-zvc-meeting-ended\'><h3>' + zvc_strings.meeting_ended + '</h3></div>')
        return
      }

      if (secondsUntil(instant) <= 0) {
        $(clock).remove()
        return
      }

      var countDown = instant.getTime()
      var x = setInterval(function () {
        var distance = countDown - Date.now()

        document.getElementById('dpn-zvc-timer-days').innerText = Math.floor(distance / day)
        document.getElementById('dpn-zvc-timer-hours').innerText = Math.floor((distance % day) / hour)
        document.getElementById('dpn-zvc-timer-minutes').innerText = Math.floor((distance % hour) / minute)
        document.getElementById('dpn-zvc-timer-seconds').innerText = Math.floor((distance % minute) / second)

        if (distance < 0) {
          clearInterval(x)

          if (isSingleMeeting) {
            $(clock).html('<div class=\'dpn-zvc-meeting-ended\'><h3>' + zvc_strings.meeting_starting + '</h3></div>')
          } else {
            location.reload()
          }
        }
      }, second)
    },

    /**
     * Set timezone and get links accordingly
     */
    setTimezone: function () {
      var timezone = normalizeTimeZone(guessTimeZone())

      try {
        if (typeof mtg_data !== 'undefined' && mtg_data.page === 'single-meeting') {
          $('.dpn-zvc-sidebar-content').after('<div class="dpn-zvc-sidebar-box remove-sidebar-loder-text"><p>Loading..Please wait..</p></div>')
          var pageData = {
            action: 'set_timezone',
            user_timezone: timezone,
            post_id: mtg_data.post_id,
            mtg_timezone: mtg_data.timezone,
            start_date: mtg_data.start_date,
            meeting_type: mtg_data.meeting_type,
            type: 'page'
          }

          $.post(mtg_data.ajaxurl, pageData).done(function (response) {
            if (response.success) {
              $('.dpn-zvc-sidebar-content').after(response.data)
            } else {
              $('.dpn-zvc-sidebar-content').after('<div class="dpn-zvc-sidebar-box vczapi-no-longer-valid">' + response.data + '</div>')
            }

            $('.remove-sidebar-loder-text').remove()
          })
        }

        /**
         * For shortcode
         * @deprecated 3.3.1
         */
        if (typeof mtg_data !== 'undefined' && mtg_data.type === 'shortcode') {
          var shortcodeData = {
            action: 'set_timezone',
            user_timezone: timezone,
            mtg_timezone: mtg_data.timezone,
            join_uri: mtg_data.join_uri,
            browser_url: mtg_data.browser_url,
            start_date: mtg_data.start_date,
            type: 'shortcode'
          }

          $('.zvc-table-shortcode-duration').after('<tr class="remove-shortcode-loder-text"><td colspan="2">Loading.. Please wait..</td></tr>')
          $.post(mtg_data.ajaxurl, shortcodeData).done(function (response) {
            if (response.success) {
              $('.zvc-table-shortcode-duration').after(response.data)
            } else {
              $('.zvc-table-shortcode-duration').after('<tr><td colspan="2">' + response.data + '</td></tr>')
            }

            $('.remove-shortcode-loder-text').remove()
          })
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
      e.preventDefault()
      var state = $(e.currentTarget).data('state')
      var post_id = $(e.currentTarget).data('postid')
      var postData = {
        id: $(e.currentTarget).data('id'),
        state: state,
        type: $(e.currentTarget).data('type'),
        post_id: post_id ? post_id : false,
        action: 'state_change',
        accss: vczapi_state.zvc_security
      }

      if (state === 'resume') {
        this.changeState(postData)
      } else if (state === 'end') {
        var c = confirm(vczapi_state.lang.confirm_end)
        if (c) {
          this.changeState(postData)
        } else {
          return
        }
      }
    },

    /**
     * Change the state triggere now
     * @param postData
     */
    changeState: function (postData) {
      $.post(vczapi_state.ajaxurl, postData).done(function (response) {
        location.reload()
      })
    }
  }

  video_conferencing_zoom_api_public.init()
})