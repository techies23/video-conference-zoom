"use strict";

jQuery(function ($) {
  var zoom_browser_integration = {
    init: function init() {
      var browseinfo = ZoomMtg.checkSystemRequirements();
      var page_html = '<ul><li><strong>Browser Info:</strong> ' + browseinfo.browserInfo + '</li>';
      page_html += '<li><strong>Browser Name:</strong> ' + browseinfo.browserName + '</li>';
      page_html += '<li><strong>Browser Version:</strong> ' + browseinfo.browserVersion + '</li></ul>';
      $('.vczapi-zoom-browser-meeting--info__browser').html(page_html);
      ZoomMtg.preLoadWasm();
      ZoomMtg.prepareJssdk();
      this.eventHandlers();
    },
    eventHandlers: function eventHandlers() {
      $('#vczapi-zoom-browser-meeting-join-mtg').on('click', this.loadMeeting.bind(this));
    },
    setCookie: function setCookie(cname, cvalue) {
      var exdays = 1;
      var d = new Date();
      d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
      var expires = 'expires=' + d.toUTCString();
      document.cookie = cname + '=' + cvalue + ';' + expires + ';path=/';
    },
    loadMeeting: function loadMeeting(e) {
      e.preventDefault();
      var meeting_id = atob(zvc_ajx.meeting_id);
      var REDIRECTION = zvc_ajx.redirect_page;
      var PASSWD = zvc_ajx.meeting_pwd !== false ? atob(zvc_ajx.meeting_pwd) : false;
      var EMAIL_USER = '';
      $('body').append('<span id="zvc-cover"></span>');

      if (meeting_id) {
        $.post(zvc_ajx.ajaxurl, {
          action: 'get_auth',
          noncce: zvc_ajx.zvc_security,
          meeting_id: meeting_id
        }).done(function (response) {
          if (response.success) {
            $('#zvc-cover').remove();
            var API_KEY = response.data.key;
            var SIGNATURE = response.data.sig;
            var REQUEST_TYPE = response.data.type;

            if (API_KEY && SIGNATURE) {
              var display_name = $('#vczapi-jvb-display-name');
              var email = $('#vczapi-jvb-email');
              var pwd = $('#meeting_password');

              if (!display_name.val()) {
                $('.vczapi-zoom-browser-meeting--info__browser').html('Error: Name is Required!').css('color', 'red');
                $('#zvc-cover').remove();
                return false;
              } //Email Validation


              if (email.val() === '') {
                $('.vczapi-zoom-browser-meeting--info__browser').html('Error: Email is Required!').css('color', 'red');
                EMAIL_USER = email.val();
                return false;
              } //Password Validation


              if (!PASSWD && pwd.length > 0 && pwd.val().length > 0) {
                PASSWD = pwd.val();
              }

              $('#vczapi-zoom-browser-meeting').remove();
              var lang = $('#meeting_lang');
              var meetConfig = {
                meetingNumber: parseInt(meeting_id, 10),
                userName: display_name.val(),
                passWord: PASSWD,
                lang: lang.length > 0 ? lang.val() : 'en-US',
                leaveUrl: REDIRECTION,
                signaure: SIGNATURE,
                email: EMAIL_USER
              };

              if (window.location !== window.parent.location) {
                REDIRECTION = window.location.href;
              }

              var meetingJoinParams = {
                meetingNumber: meetConfig.meetingNumber,
                userName: meetConfig.userName,
                signature: meetConfig.signaure,
                apiKey: meetConfig.apiKey,
                userEmail: meetConfig.email,
                passWord: meetConfig.passWord,
                success: function success(res) {
                  console.log('Join Meeting Success');
                },
                error: function error(res) {
                  console.log(res);
                }
              };

              if (REQUEST_TYPE === 'jwt') {
                meetingJoinParams.apiKey = API_KEY;
              } else if (REQUEST_TYPE === 'sdk') {
                meetingJoinParams.sdkKey = API_KEY;
              }

              ZoomMtg.init({
                leaveUrl: REDIRECTION,
                isSupportAV: true,
                meetingInfo: zvc_ajx.meetingInfo,
                disableInvite: zvc_ajx.disableInvite,
                disableRecord: zvc_ajx.disableRecord,
                disableJoinAudio: zvc_ajx.disableJoinAudio,
                isSupportChat: zvc_ajx.isSupportChat,
                isSupportQA: zvc_ajx.isSupportQA,
                isSupportBreakout: zvc_ajx.isSupportBreakout,
                isSupportCC: zvc_ajx.isSupportCC,
                screenShare: zvc_ajx.screenShare,
                success: function success() {
                  ZoomMtg.i18n.load(meetConfig.lang);
                  ZoomMtg.i18n.reload(meetConfig.lang);
                  ZoomMtg.join(meetingJoinParams);
                },
                error: function error(res) {
                  console.log(res);
                }
              });
            } else {
              $('#zvc-cover').remove();
              alert('NOT AUTHORIZED');
            }
          }
        });
      } else {
        $('#zvc-cover').remove();
        alert('Incorrect Meeting ID');
      }
    }
  };
  zoom_browser_integration.init();
});