"use strict";

/**
 * Jquery Scripts
 *
 * @author  Deepen
 * @since  1.0.0
 * @modified in 3.0.0
 */
(function ($) {
  //Cache
  var $dom = {};
  var ZoomAPIJS = {
    onReady: function onReady() {
      this.setupDOM();
      this.eventListeners();
      this.initializeDependencies();
    },
    setupDOM: function setupDOM() {
      $dom.changeSelectType = $('.zvc-hacking-select');
      $dom.dateTimePicker = $('#datetimepicker');
      $dom.reportsDatePicker = $('#reports_date');
      $dom.zoomAccountDatepicker = $(".zoom_account_datepicker");
      $dom.meetingListDTable = $('#zvc_users_list_table, #zvc_meetings_list_table');
      $dom.meetingListTableCheck = $("#zvc_meetings_list_table");
      $dom.usersListTable = $('#vczapi-get-host-users-wp');
      $dom.meetingListTbl = $dom.meetingListTableCheck.find('input[type=checkbox]');
      $dom.cover = $('#zvc-cover');
      $dom.togglePwd = $('.toggle-api');
      $dom.toggleSecret = $('.toggle-secret');
      $dom.changeMeetingState = $('.vczapi-meeting-state-change');
      $dom.show_on_meeting_delete_error = $('.show_on_meeting_delete_error');
      this.$manualHostID = $('.vczapi-admin-hostID-manually-add');
    },
    eventListeners: function eventListeners() {
      //Check All Table Elements for Meetings List
      $dom.meetingListTableCheck.find('#checkall').on('click', this.meetingListTableCheck);
      /**
       * Bulk Delete Function
       * @author  Deepen
       * @since 2.0.0
       */

      $('#bulk_delete_meeting_listings').on('click', this.bulkDeleteMeetings); //For Password field

      $('.zvc-meetings-form').find('input[name="password"]').on('keypress', this.meetingPassword);
      /**
       * Confirm Deletion of the Meeting
       */

      $('.delete-meeting').on('click', this.deleteMetting); //FOr the Password Hashing API

      $dom.togglePwd.on('click', this.toggleAPISettings.bind(this));
      $dom.toggleSecret.on('click', this.toggleSecretSettings.bind(this));
      $('.zvc-dismiss-message').on('click', this.dismissNotice.bind(this));
      $('.check-api-connection').on('click', this.checkConnection.bind(this)); //End and Resume Meetings

      $($dom.changeMeetingState).on('click', this.meetingStateChange.bind(this)); //Manual Host Selector

      this.$manualHostID.on('click', this.showManualHostIDField.bind(this));
    },

    /**
     * Show Manual Host ID selector field
     *
     * @param e
     */
    showManualHostIDField: function showManualHostIDField(e) {
      e.preventDefault();
      $('.vczapi-admin-post-type-host-selector').select2('destroy').remove();
      $('.vczapi-manually-hostid-wrap').before('<input type="text" placeholder="' + zvc_ajax.lang.host_id_search + '" class="regular-text vczapi-search-host-id" name="userId" required>').remove();
    },
    datePickers: function datePickers() {
      //For Datepicker
      if ($dom.dateTimePicker.length > 0) {
        var d = new Date();
        var month = d.getMonth() + 1;
        var day = d.getDate();
        var time = d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
        var output = d.getFullYear() + '-' + (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day + ' ' + time;
        var start_date_check = $dom.dateTimePicker.data('existingdate');

        if (start_date_check) {
          output = start_date_check;
        }

        $dom.dateTimePicker.datetimepicker({
          value: output,
          step: 15,
          minDate: 0,
          format: 'Y-m-d H:i'
        });
      } //For Reports Section


      if ($dom.reportsDatePicker.length > 0) {
        $dom.reportsDatePicker.datepicker({
          changeMonth: true,
          changeYear: false,
          showButtonPanel: true,
          dateFormat: 'MM yy'
        }).focus(function () {
          var thisCalendar = $(this);
          $('.ui-datepicker-calendar').detach();
          $('.ui-datepicker-close').click(function () {
            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = $("#ui-datepicker-div .ui-datepicker-year").html();
            thisCalendar.datepicker('setDate', new Date(year, month, 1));
          });
        });
      }

      if ($('#vczapi-check-recording-date').length > 0) {
        $('#vczapi-check-recording-date').datepicker({
          changeMonth: true,
          changeYear: true,
          showButtonPanel: true,
          dateFormat: 'MM yy',
          beforeShow: function beforeShow(input, inst) {
            setTimeout(function () {
              inst.dpDiv.css({
                top: $('#vczapi-check-recording-date').offset().top + 35,
                left: $('#vczapi-check-recording-date').offset().left
              });
            }, 0);
          }
        }).focus(function () {
          var thisCalendar = $(this);
          $('.ui-datepicker-calendar').detach();
          $('.ui-datepicker-close').click(function () {
            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            thisCalendar.datepicker('setDate', new Date(year, month, 1));
          });
        });
      }

      if ($dom.zoomAccountDatepicker.length > 0) {
        $dom.zoomAccountDatepicker.datepicker({
          dateFormat: "yy-mm-dd"
        });
      }
    },
    initializeDependencies: function initializeDependencies() {
      if ($dom.changeSelectType.length > 0) {
        $dom.changeSelectType.select2();
      } //DatePickers


      this.datePickers();
      /***********************************************************
       * Start For Users and Meeting DATA table Listing Section
       **********************************************************/

      if ($dom.meetingListDTable.length > 0) {
        $dom.meetingListDTable.dataTable({
          "pageLength": 25,
          "columnDefs": [{
            "targets": 0,
            "orderable": false
          }]
        });
      }

      if ($dom.usersListTable.length > 0) {
        $dom.usersListTable.dataTable({
          "pageLength": 25,
          "columnDefs": [{
            "targets": 0,
            "orderable": true
          }],
          ajax: {
            url: ajaxurl + '?action=get_assign_host_id'
          },
          columns: [{
            data: 'id'
          }, {
            data: 'email'
          }, {
            data: 'name'
          }, {
            data: 'host_id'
          }]
        });
      }

      if ($('#vczapi-select-wp-user-for-host').length > 0) {
        $('#vczapi-select-wp-user-for-host').select2({
          ajax: {
            url: ajaxurl + '?action=vczapi_get_wp_users',
            type: "GET",
            dataType: 'json'
          },
          placeholder: "Select a WordPress User",
          width: '300px'
        });
      }
    },
    meetingListTableCheck: function meetingListTableCheck() {
      if ($(this).is(':checked')) {
        $dom.meetingListTbl.each(function () {
          $(this).prop("checked", true);
        });
      } else {
        $dom.meetingListTbl.each(function () {
          $(this).prop("checked", false);
        });
      }
    },

    /**
     * Bulk Meeting DELETE Function
     * @returns {boolean}
     */
    bulkDeleteMeetings: function bulkDeleteMeetings() {
      var r = confirm("Confirm bulk delete these Meeting?");

      if (r == true) {
        var arr_checkbox = [];
        $dom.meetingListTableCheck.find('input.checkthis').each(function () {
          if ($(this).is(':checked')) {
            arr_checkbox.push($(this).val());
          }
        });
        var type = $(this).data('type'); //Process bulk delete

        if (arr_checkbox) {
          var data = {
            meetings_id: arr_checkbox,
            type: type,
            action: 'zvc_bulk_meetings_delete',
            security: zvc_ajax.zvc_security
          };
          $dom.cover.show();
          $.post(zvc_ajax.ajaxurl, data).done(function (response) {
            $dom.cover.fadeOut('slow');

            if (response.error == 1) {
              $dom.show_on_meeting_delete_error.show().html('<p>' + response.msg + '</p>');
            } else {
              $dom.show_on_meeting_delete_error.show().html('<p>' + response.msg + '</p>');
              location.reload();
            }
          });
        }
      } else {
        return false;
      }
    },

    /**
     * Meeting Password Selector
     * @param e
     * @returns {boolean}
     */
    meetingPassword: function meetingPassword(e) {
      if (!/([a-zA-Z0-9])+/.test(String.fromCharCode(e.which))) {
        return false;
      }

      var text = $(this).val();
      var maxlength = $(this).data('maxlength');

      if (maxlength > 0) {
        $(this).val(text.substr(0, maxlength));
      }
    },

    /**
     * Delete meeting funciton
     * @returns {boolean}
     */
    deleteMetting: function deleteMetting() {
      var meeting_id = $(this).data('meetingid');
      var type = $(this).data('type');
      var r = confirm("Confirm Delete this Meeting?");

      if (r == true) {
        var data = {
          meeting_id: meeting_id,
          type: type,
          action: 'zvc_delete_meeting',
          security: zvc_ajax.zvc_security
        };
        $dom.cover.show();
        $.post(zvc_ajax.ajaxurl, data).done(function (result) {
          $dom.cover.fadeOut('slow');

          if (result.error == 1) {
            $dom.show_on_meeting_delete_error.show().html('<p>' + result.msg + '</p>');
          } else {
            $dom.show_on_meeting_delete_error.show().html('<p>' + result.msg + '</p>');
            location.reload();
          }
        });
      } else {
        return false;
      }
    },

    /**
     * Toggle API keys hide unhide
     */
    toggleAPISettings: function toggleAPISettings() {
      var inputID = $('#zoom_api_key');

      if ($dom.togglePwd.html() === "Show") {
        $dom.togglePwd.html('Hide');
        inputID.attr('type', 'text');
      } else {
        $dom.togglePwd.html('Show');
        inputID.attr('type', 'password');
      }
    },

    /**
     * Toggle secret hide unhide
     */
    toggleSecretSettings: function toggleSecretSettings() {
      var secretID = $('#zoom_api_secret');

      if ($dom.toggleSecret.html() === "Show") {
        $dom.toggleSecret.html('Hide');
        secretID.attr('type', 'text');
      } else {
        $dom.toggleSecret.html('Show');
        secretID.attr('type', 'password');
      }
    },
    dismissNotice: function dismissNotice(e) {
      e.preventDefault();
      $(e.currentTarget).closest('.notice-success').hide();
      $.post(zvc_ajax.ajaxurl, {
        action: 'zoom_dimiss_notice'
      }).done(function (result) {
        //Done
        console.log(result);
      });
    },
    checkConnection: function checkConnection(e) {
      e.preventDefault();
      $dom.cover.show();
      $.post(zvc_ajax.ajaxurl, {
        action: 'check_connection',
        security: zvc_ajax.zvc_security
      }).done(function (result) {
        //Done
        $dom.cover.hide();
        alert(result);
      });
    },

    /**
     * Change Meeting State
     * @param e
     */
    meetingStateChange: function meetingStateChange(e) {
      e.preventDefault();
      var state = $(e.currentTarget).data('state');
      var post_id = $(e.currentTarget).data('postid');
      var postData = {
        id: $(e.currentTarget).data('id'),
        state: state,
        type: $(e.currentTarget).data('type'),
        post_id: post_id ? post_id : false,
        action: 'state_change',
        accss: zvc_ajax.zvc_security
      };

      if (state === "resume") {
        this.changeState(postData);
      } else if (state === "end") {
        var c = confirm(zvc_ajax.lang.confirm_end);

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
    changeState: function changeState(postData) {
      $.post(zvc_ajax.ajaxurl, postData).done(function (response) {
        location.reload();
      });
    }
  };
  /**
   * Sync Meeting Functions
   * @type {{init: init, fetchMeetingsByUser: fetchMeetingsByUser, cacheDOM: cacheDOM, evntHandlers: evntHandlers, syncMeeting: syncMeeting}}
   */

  var vczapi_sync_meetings = {
    init: function init() {
      this.cacheDOM();
      this.evntHandlers();
    },
    cacheDOM: function cacheDOM() {
      //Sync DOMS
      this.notificationWrapper = $('.vczapi-status-notification');
      this.syncUserId = $('.vczapi-sync-user-id');
    },
    evntHandlers: function evntHandlers() {
      this.syncUserId.on('change', this.fetchMeetingsByUser.bind(this));
    },
    fetchMeetingsByUser: function fetchMeetingsByUser(e) {
      e.preventDefault();
      var that = this;
      var user_id = $(this.syncUserId).val();
      var postData = {
        user_id: user_id,
        action: 'vczapi_sync_user',
        type: 'check'
      };
      var results = $('.results');
      results.html('<p>' + vczapi_sync_i10n.before_sync + '</p>');
      $.post(ajaxurl, postData).done(function (response) {
        //Success
        if (response.success) {
          var page_html = '<div class="vczapi-sync-details">';
          page_html += '<p><strong>' + vczapi_sync_i10n.total_records_found + ':</strong> ' + response.data.total_records + '</p>';
          page_html += '<p><strong>' + vczapi_sync_i10n.total_not_synced_records + ':</strong> ' + _.size(response.data.meetings) + ' (Only listing Scheduled Meetings)</p>';
          page_html += '<select class="vczapi-choose-meetings-to-sync-select2" name="sync-meeting-ids[]" multiple="multiple">';
          $(response.data.meetings).each(function (i, r) {
            page_html += '<option value="' + r.id + '">' + r.topic + '</option>';
          });
          page_html += '</select>';
          setTimeout(function () {
            $(".vczapi-choose-meetings-to-sync-select2").select2({
              maximumSelectionLength: 10,
              placeholder: vczapi_sync_i10n.select2_placeholder
            });
          }, 100);
          page_html += '<p><a href="javascript:void(0);" class="vczapi-sync-meeting button button-primary" data-userid="' + user_id + '">' + vczapi_sync_i10n.sync_btn + '</a></p>';
          page_html += '</div>';
          results.html(page_html);
          $('.vczapi-sync-meeting').on('click', that.syncMeeting.bind(that));
        } else {
          results.html('<p>' + response.data + '</p>');
        }
      });
    },
    syncMeeting: function syncMeeting(e) {
      e.preventDefault();
      $(e.currentTarget).attr('disabled', 'disabled');
      var sync_meeting_ids = $('.vczapi-choose-meetings-to-sync-select2').val();

      if (_.size(sync_meeting_ids) > 0) {
        this.notificationWrapper.show().html('<p>' + vczapi_sync_i10n.sync_start + '</p>').removeClass('vczapi-error');
        this.doSync(0, sync_meeting_ids);
      } else {
        this.notificationWrapper.show().html('<p>' + vczapi_sync_i10n.sync_error + '</p>').addClass('vczapi-error');
        $(e.currentTarget).removeAttr('disabled');
      }
    },

    /**
     * Run AJAX call based on per meeting selected
     * @param arrCount
     * @param sync_meeting_ids
     */
    doSync: function doSync(arrCount, sync_meeting_ids) {
      var that = this;
      var postData = {
        action: 'vczapi_sync_user',
        type: 'sync',
        meeting_id: sync_meeting_ids[arrCount]
      };
      $.post(ajaxurl, postData).done(function (response) {
        arrCount++;
        that.notificationWrapper.show().append('<p> ' + response.data.msg + '</p>');

        if (arrCount < _.size(sync_meeting_ids)) {
          vczapi_sync_meetings.doSync(arrCount, sync_meeting_ids);
        } else {
          if (response.success) {
            that.notificationWrapper.show().append('<p>' + vczapi_sync_i10n.sync_completed + '</p>');
            $('.vczapi-sync-meeting').removeAttr('disabled');
          } else {
            that.notificationWrapper.show().append('<p>' + response.data.msg + '</p>');
            $('.vczapi-sync-meeting').removeAttr('disabled');
          }
        }
      });
    }
  };
  /**
   * Webinar Functions
   * @type {{init: init, cacheDOM: cacheDOM, evntHandlers: evntHandlers, webinarElementsShow: webinarElementsShow}}
   */

  var vczapi_webinars = {
    init: function init() {
      this.cacheDOM();
      this.evntHandlers();
    },
    cacheDOM: function cacheDOM() {
      this.meetingSelector = $('#vczapi-admin-meeting-ype');
      this.hideOnWebinarSelector = $('.vczapi-admin-hide-on-webinar');
      this.showOnWebinarSelector = $('.vczapi-admin-show-on-webinar');
    },
    evntHandlers: function evntHandlers() {
      this.meetingSelector.on('change', this.webinarElementsShow.bind(this));
    },
    webinarElementsShow: function webinarElementsShow(e) {
      var meeting_type = $(e.currentTarget).val();

      if (meeting_type === '2') {
        this.hideOnWebinarSelector.hide();
        this.showOnWebinarSelector.show();
      } else {
        this.hideOnWebinarSelector.show();
        this.showOnWebinarSelector.hide();
      }
    }
  };
  $(function () {
    ZoomAPIJS.onReady();
    vczapi_sync_meetings.init();
    vczapi_webinars.init();
  });
})(jQuery);