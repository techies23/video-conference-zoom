/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};


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
    onReady: function () {
      this.setupDOM();
      this.eventListeners();
      this.initializeDependencies();
    },
    setupDOM: function () {
      $dom.changeSelectType = $('.zvc-hacking-select');
      $dom.dateTimePicker = $('#datetimepicker');
      $dom.goToAccordionEl = $('.vczapi-go-to-open-accordion');
      $dom.reportsDatePicker = $('#reports_date');
      $dom.zoomAccountDatepicker = $('.zoom_account_datepicker');
      $dom.meetingListDTable = $('#zvc_users_list_table, #zvc_meetings_list_table');
      $dom.meetingListTableCheck = $('#zvc_meetings_list_table');
      $dom.usersListTable = $('#vczapi-get-host-users-wp');
      $dom.meetingListTbl = $dom.meetingListTableCheck.find('input[type=checkbox]');
      $dom.cover = $('#zvc-cover');
      $dom.changeMeetingState = $('.vczapi-meeting-state-change');
      $dom.endMeetingEl = $('.vczapi-meeting-state-end_meeting');
      $dom.show_on_meeting_delete_error = $('.show_on_meeting_delete_error');
      $dom.toggleTriggerElement = $('.vczapi-toggle-trigger');
      this.$manualHostID = $('.vczapi-admin-hostID-manually-add');
      $dom.accordionElement = $('.vczapi-admin-accordion');
      $dom.connectBox = $('.vczapi-connect-box');
    },
    eventListeners: function () {
      //toggle show hide
      $dom.toggleTriggerElement.on('click', this.togglePasswordText.bind(this));
      //accordion js
      $dom.accordionElement.on('click', '.vczapi-admin-accordion--header', this.toggleAccordion.bind(this));
      //go to accordiong
      $dom.goToAccordionEl.on('click', this.expandAccordion.bind(this));

      //Check All Table Elements for Meetings List
      $dom.meetingListTableCheck.find('#checkall').on('click', this.meetingListTableCheck);

      /**
       * Bulk Delete Function
       * @author  Deepen
       * @since 2.0.0
       */
      $('#bulk_delete_meeting_listings').on('click', this.bulkDeleteMeetings);

      //For Password field
      $('.zvc-meetings-form').find('input[name="password"]').on('keypress', this.meetingPassword);

      /**
       * Confirm Deletion of the Meeting
       */
      $('.delete-meeting').on('click', this.deleteMetting);
      $('.zvc-dismiss-message').on('click', this.dismissNotice.bind(this));
      $('.check-api-connection').on('click', this.checkConnection.bind(this));

      //End and Resume Meetings
      $($dom.changeMeetingState).on('click', this.meetingStateChange.bind(this));

      //Manual Host Selector
      this.$manualHostID.on('click', this.showManualHostIDField.bind(this));

      //End Meeting
      $dom.endMeetingEl.on('click', this.endMeeting.bind(this));
    },
    endMeeting: function (e) {
      e.preventDefault();
      let el = e.target;
      let meetingID = el.getAttribute('data-id');
      let postData = {
        'action': 'vczapi_end_meeting',
        'access': zvc_ajax.zvc_security,
        'meeting_id': meetingID
      };
      let endMeeting = confirm('Are you sure you want to end this meeting');
      if (endMeeting) {
        $.post(zvc_ajax.ajaxurl, postData).done(function (response) {
          location.reload();
        });
      }
    },
    //Expand Accordion
    expandAccordion: function (e) {
      e.preventDefault();
      let $el = $(e.currentTarget);
      let $targetAccordionEl = $($el.attr('href'));
      if ($targetAccordionEl !== undefined && $targetAccordionEl.length > 0) {
        $targetAccordionEl.addClass('expanded');
        $('html,body').animate({
          scrollTop: $targetAccordionEl.offset().top
        }, 1000);
        $targetAccordionEl.focus();
      }
    },
    /**
     * Toggle Accordiong Element
     */
    toggleAccordion: function (e) {
      e.preventDefault();
      let $accordionHeader = $(e.currentTarget);
      let $accordionWrapper = $accordionHeader.parent();
      $accordionWrapper.toggleClass('expanded');
    },
    /**
     * Toggle Show or hide
     */
    togglePasswordText: function (e) {
      e.preventDefault();
      let $triggerElement = $(e.currentTarget);
      let $targetElement = $($triggerElement.data('element'));
      let isElementVisible = $triggerElement.data('visible');
      if (isElementVisible === 0) {
        $targetElement.attr('type', 'text');
        $triggerElement.data('visible', 1);
        $triggerElement.text('Hide');
      } else {
        $targetElement.attr('type', 'password');
        $triggerElement.data('visible', 0);
        $triggerElement.text('Show');
      }
    },
    /**
     * Show Manual Host ID selector field
     *
     * @param e
     */
    showManualHostIDField: function (e) {
      e.preventDefault();
      $('.vczapi-admin-post-type-host-selector').select2('destroy').remove();
      $('.vczapi-manually-hostid-wrap').before('<input type="text" placeholder="' + zvc_ajax.lang.host_id_search + '" class="regular-text vczapi-search-host-id" name="userId" required>').remove();
    },
    datePickers: function () {
      //For Datepicker
      if ($dom.dateTimePicker.length > 0) {
        var d = new Date();
        var month = d.getMonth() + 1;
        var day = d.getDate();
        var time = d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds();
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
      }

      //For Reports Section
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
            var month = $('#ui-datepicker-div .ui-datepicker-month :selected').val();
            var year = $('#ui-datepicker-div .ui-datepicker-year').html();
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
          beforeShow: function (input, inst) {
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
            var month = $('#ui-datepicker-div .ui-datepicker-month :selected').val();
            var year = $('#ui-datepicker-div .ui-datepicker-year :selected').val();
            thisCalendar.datepicker('setDate', new Date(year, month, 1));
          });
        });
      }
      if ($dom.zoomAccountDatepicker.length > 0) {
        $dom.zoomAccountDatepicker.datepicker({
          dateFormat: 'yy-mm-dd'
        });
      }
    },
    initializeDependencies: function () {
      if ($dom.changeSelectType.length > 0) {
        $dom.changeSelectType.select2();
      }

      //DatePickers
      this.datePickers();

      /***********************************************************
       * Start For Users and Meeting DATA table Listing Section
       **********************************************************/
      if ($dom.meetingListDTable.length > 0) {
        $dom.meetingListDTable.dataTable({
          'pageLength': 25,
          'columnDefs': [{
            'targets': 0,
            'orderable': false
          }]
        });
      }
      if ($dom.usersListTable.length > 0) {
        $dom.usersListTable.dataTable({
          processing: true,
          serverSide: true,
          pageLength: 25,
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
          }],
          drawCallback: function (settings) {
            $('.vczapi-get-zoom-hosts').select2({
              ajax: {
                url: ajaxurl + '?action=vczapi_get_zoom_host_query',
                type: 'GET',
                dataType: 'json',
                delay: 200,
                cache: true
              },
              allowClear: true,
              placeholder: 'Filter a zoom user by email ID or host ID...',
              width: '100%'
            }).on('select2:select', function (event) {
              if ($('.vczapi-host-email-field-' + $(this).data('userid')).length > 0) {
                $('.vczapi-host-email-field-' + $(this).data('userid')).val(event.params.data.text);
              } else {
                $('<input type="hidden" class="vczapi-host-email-field-' + $(this).data('userid') + '" name="zoom_host_email[' + $(this).data('userid') + ']" value="' + event.params.data.text + '" />').insertAfter(this);
              }
            });
          }
        });
      }
      if ($('#vczapi-select-wp-user-for-host').length > 0) {
        $('#vczapi-select-wp-user-for-host').select2({
          ajax: {
            url: ajaxurl + '?action=vczapi_get_wp_users',
            type: 'GET',
            dataType: 'json'
          },
          placeholder: 'Select a WordPress User',
          width: '300px'
        });
      }
    },
    meetingListTableCheck: function () {
      if ($(this).is(':checked')) {
        $dom.meetingListTbl.each(function () {
          $(this).prop('checked', true);
        });
      } else {
        $dom.meetingListTbl.each(function () {
          $(this).prop('checked', false);
        });
      }
    },
    /**
     * Bulk Meeting DELETE Function
     * @returns {boolean}
     */
    bulkDeleteMeetings: function () {
      var r = confirm('Confirm bulk delete these Meeting?');
      if (r == true) {
        var arr_checkbox = [];
        $dom.meetingListTableCheck.find('input.checkthis').each(function () {
          if ($(this).is(':checked')) {
            arr_checkbox.push($(this).val());
          }
        });
        var type = $(this).data('type');
        //Process bulk delete
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
    meetingPassword: function (e) {
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
    deleteMetting: function () {
      var meeting_id = $(this).data('meetingid');
      var type = $(this).data('type');
      var r = confirm('Confirm Delete this Meeting?');
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
    dismissNotice: function (e) {
      e.preventDefault();
      $(e.currentTarget).closest('.notice-success').hide();
      $.post(zvc_ajax.ajaxurl, {
        action: 'zoom_dimiss_notice'
      }).done(function (result) {
        //Done
        console.log(result);
      });
    },
    checkConnection: function (e) {
      e.preventDefault();
      $dom.connectBox.html('<pre>Making demo request to Zoom Servers... Please wait.</pre>');
      $.post(zvc_ajax.ajaxurl, {
        action: 'check_connection',
        security: zvc_ajax.zvc_security,
        type: 'oAuth'
      }).done(function (result) {
        if (result.success) {
          $dom.connectBox.append(`<pre style="color:green;">${result.data.msg}</pre>`);
        } else {
          $dom.connectBox.append(`<pre style="color:red;">${result.data.msg}</pre>`);
        }
      });
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
        action: 'state_change',
        accss: zvc_ajax.zvc_security
      };
      if (state === 'resume') {
        this.changeState(postData);
      } else if (state === 'end') {
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
    changeState: function (postData) {
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
    init: function () {
      this.cacheDOM();
      this.evntHandlers();
    },
    cacheDOM: function () {
      //Sync DOMS
      this.notificationWrapper = $('.vczapi-status-notification');
      this.syncUserId = $('.vczapi-sync-user-id');
    },
    evntHandlers: function () {
      this.syncUserId.on('change', this.fetchMeetingsByUser.bind(this));
    },
    fetchMeetingsByUser: function (e) {
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
            $('.vczapi-choose-meetings-to-sync-select2').select2({
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
    syncMeeting: function (e) {
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
    doSync: function (arrCount, sync_meeting_ids) {
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
  const vczapi_webinars = {
    init: function () {
      this.cacheDOM();
      this.evntHandlers();
    },
    cacheDOM: function () {
      this.meetingSelector = $('#vczapi-admin-meeting-ype');
      this.hideOnWebinarSelector = $('.vczapi-admin-hide-on-webinar');
      this.showOnWebinarSelector = $('.vczapi-admin-show-on-webinar');
    },
    evntHandlers: function () {
      this.meetingSelector.on('change', this.webinarElementsShow.bind(this));
    },
    webinarElementsShow: function (e) {
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
  const vczapiMigrationWizard = {
    init: function () {
      this.cacheDOM();
      if (this.$wizardWrapper !== undefined && this.$wizardWrapper.length > 0) {
        this.eventListeners();
      }
    },
    cacheDOM: function () {
      this.$wizardOverlay = $('.vczapi-migrate-to-s2sOauth--overlay');
      this.$wizardWrapper = $('.vczapi-migrate-to-s2sOauth');
      this.$s2sOauthForm = $('#vczapi-s2sOauthCredentials-wizard-form');
      this.$appSDKForm = $('#vczapi-s2soauth-app-sdk-form');
      this.$messageWrapper = this.$wizardWrapper.find('.vczapi-migrate-to-s2sOauth--message');
    },
    eventListeners: function () {
      this.maybeTriggerMigrationWizard();
      this.$wizardWrapper.find('.next-step').on('click', this.navigateToStep.bind(this));
      this.$s2sOauthForm.on('submit', this.s2sOauthFormHandler.bind(this));
      this.$appSDKForm.on('submit', this.appSDKFormHandler.bind(this));
      $('body').on('click', this.maybeCloseWizard.bind(this));
    },
    maybeCloseWizard: function (e) {
      const clickTriggerEl = e.target;
      if ($(clickTriggerEl)[0] === this.$wizardOverlay[0]) {
        this.$wizardOverlay.removeClass('expanded');
      }
    },
    showMessage: function (type, message = 'text') {
      const messageClass = 'show-message ' + (type === 'success' ? 'success-message' : 'error-message');
      this.$wizardWrapper.find('.vczapi-migrate-to-s2sOauth--message').removeClass(['error-message', 'success-message']);
      this.$wizardWrapper.find('.vczapi-migrate-to-s2sOauth--message').addClass(messageClass).text(message);
    },
    s2sOauthFormHandler: function (e) {
      e.preventDefault();
      const $form = $(e.target);
      const data = $form.serialize();
      $.ajax({
        type: 'POST',
        url: ajaxurl + '?action=vczapi_save_oauth_credentials',
        context: this,
        data: data,
        beforeSend: function () {
          this.$s2sOauthForm.find('input').prop('disabled', true);
          this.$s2sOauthForm.addClass('submitting');
        },
        success: function (response) {
          this.$s2sOauthForm.find('input').prop('disabled', false);
          this.$s2sOauthForm.removeClass('submitting');
          if (response.hasOwnProperty('success') && response.success) {
            this.showMessage('success', response?.data.message);
            this.$wizardWrapper.find('.next-step').attr('disabled', false);
          } else {
            this.showMessage('error', response?.data.message);
          }
        },
        error: function (MLHttpRequest, textStatus, errorThrown) {
          console.log('Error thrown', errorThrown);
        }
      });
    },
    appSDKFormHandler: function (e) {
      e.preventDefault();
      const $form = $(e.target);
      const data = $form.serialize();
      $.ajax({
        type: 'POST',
        url: ajaxurl + '?action=vczapi_save_app_sdk_credentials',
        context: this,
        data: data,
        beforeSend: function () {
          this.$appSDKForm.find('input').prop('disabled', true);
          this.$appSDKForm.addClass('submitting');
        },
        success: function (response) {
          this.$appSDKForm.find('input').prop('disabled', false);
          this.$appSDKForm.removeClass('submitting');
          if (response.hasOwnProperty('success') && response.success) {
            this.showMessage('success', response?.data.message);
            this.$wizardWrapper.find('.next-step').attr('disabled', false);
          } else {
            this.showMessage('error', response?.data.message);
          }
        },
        error: function (MLHttpRequest, textStatus, errorThrown) {
          console.log('Error thrown', errorThrown);
        }
      });
    },
    maybeTriggerMigrationWizard: function () {
      let params = this.getSearchParameters();
      if (params.hasOwnProperty('page') && params.page === 'zoom-video-conferencing-settings' && params.hasOwnProperty('migrate') && params.migrate === 'now') {
        this.$wizardOverlay.addClass('expanded');
      }
    },
    getSearchParameters: function () {
      let prmstr = window.location.search.substring(1);
      return prmstr != null && prmstr !== '' ? this.transformToAssocArray(prmstr) : {};
    },
    transformToAssocArray: function (prmstr) {
      var params = {};
      var prmarr = prmstr.split('&');
      for (var i = 0; i < prmarr.length; i++) {
        var tmparr = prmarr[i].split('=');
        params[tmparr[0]] = tmparr[1];
      }
      return params;
    },
    navigateToStep: function (e) {
      e.preventDefault();
      let $el = $(e.currentTarget);
      let currentStep = $el.data('step');
      let finalStep = $el.data('final_step');
      if (currentStep === undefined) {
        console.log('Error no steps defined');
      } else {
        let passedThisRef = this;
        let goToStep = passedThisRef.$wizardWrapper.find('.step-' + currentStep);
        if (goToStep.length > 0) {
          let nextStep = parseInt(currentStep) + 1;
          //check if it's final step
          this.$wizardWrapper.find('.step.active').removeClass('active').fadeOut('slow', function () {
            goToStep.addClass('active').fadeIn('slow');
            $el.data('step', nextStep);
            $el.attr('disabled', true);
            if (nextStep > parseInt(finalStep)) {
              $el.hide();
            }
            passedThisRef.$messageWrapper.removeClass('show-message');
          });
        } else {
          $el.hide();
        }
      }
    }
  };
  const vczapi_dismiss_notice = {
    init: function () {
      $('.vczapi-dismiss-admin-notice').on('click', this.dismissNotice.bind(this));
    },
    dismissNotice: function (e) {
      e.preventDefault();
      let $el = $(e.target);
      let option = $el.data('id');
      let security = $el.data('security');
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'vczapi_dismiss_admin_notice',
          option: option,
          security: security
        },
        beforeSend: function () {
          if ($el.parents('.vczapi-notice').length > 0) {
            $el.parents('.vczapi-notice').fadeOut();
          }
        },
        success: function (response) {
          if (response.hasOwnProperty('success') && response.success) {
            console.log(response);
          }
        }
      });
    }
  };
  $(function () {
    vczapiMigrationWizard.init();
    vczapi_dismiss_notice.init();
    ZoomAPIJS.onReady();
    vczapi_sync_meetings.init();
    vczapi_webinars.init();
  });
})(jQuery);
/******/ })()
;