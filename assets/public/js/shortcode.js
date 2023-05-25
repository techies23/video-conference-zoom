/******/ (function() { // webpackBootstrap
var __webpack_exports__ = {};
(function ($) {
  var vczAPIListUserMeetings = {
    init: function () {
      this.cacheDOM();
      this.defaultActions();
    },
    cacheDOM: function () {
      this.$wrapper = $('.vczapi-user-meeting-list');
      if (this.$wrapper === undefined || this.$wrapper.length < 1) {
        return false;
      }
    },
    defaultActions: function () {
      this.$wrapper.DataTable({
        responsive: true,
        language: vczapi_dt_i18n
      });
    }
  };
  var vczAPIMeetingFilter = {
    init: function () {
      this.cacheDOM();
      this.evntHandlers();
    },
    cacheDOM: function () {
      this.$taxonomyOrder = $('.vczapi-taxonomy-ordering');
      this.$orderType = $('.vczapi-ordering');
    },
    evntHandlers: function () {
      this.$taxonomyOrder.on('change', this.taxOrdering.bind(this));
      this.$orderType.on('change', this.upcomingLatest.bind(this));
    },
    taxOrdering: function (e) {
      $(e.currentTarget).closest('form').submit();
    },
    upcomingLatest: function (e) {
      $(e.currentTarget).closest('form').submit();
    }
  };

  /**
   * Shortcode List Meeting Ajaxify
   * Have to account for multiple instances on same page possibility
   */
  var vczAPIMeetingList = {
    paginationHandler: function () {
      $(document).on('click', '.vczapi-list-zoom-meetings--pagination .page-numbers', function (event) {
        event.preventDefault();
        var $triggerEl = $(event.target);
        var $targetWrapper = $triggerEl.parents('.vczapi-list-zoom-meetings');
        var page_num = parseInt($triggerEl.text());
        var data = $targetWrapper.data();
        var $currentPage = 1;

        //clicking of next and previous pagination buttons
        if ($triggerEl.hasClass('next')) {
          $currentPage = $targetWrapper.find('.vczapi-list-zoom-meetings--pagination').find('.page-numbers.current');
          page_num = parseInt($currentPage.next().text());
        } else if ($triggerEl.hasClass('prev')) {
          $currentPage = $targetWrapper.find('.vczapi-list-zoom-meetings--pagination').find('.page-numbers.current');
          page_num = parseInt($currentPage.prev().text());
        }
        data['page_num'] = page_num;
        var form_data = $targetWrapper.find('form.vczapi-filters').serializeArray().reduce(function (obj, item) {
          obj[item.name] = item.value;
          return obj;
        }, {});
        $.ajax({
          type: 'POST',
          url: vczapi_ajax.ajaxurl,
          data: {
            action: 'vczapi_list_meeting_shortcode_ajax_handler',
            data: data,
            form_data: form_data
          },
          beforeSend: function () {
            $targetWrapper.addClass('loading');
          },
          success: function (response) {
            $targetWrapper.removeClass('loading');
            $targetWrapper.find('.vczapi-items-wrap').html(response.content);
            $targetWrapper.find('.vczapi-list-zoom-meetings--pagination').html(response.pagination);

            // console.log(response.pagination);
          },

          error: function (MLHttpRequest, textStatus, errorThrown) {}
        });
      });
    },
    filterFormSubmitHandler: function () {
      $('form.vczapi-filters').on('submit', function (e) {
        e.preventDefault();
        var $targetWrapper = $(this).parents('.vczapi-list-zoom-meetings');
        var formData = $(this).serializeArray().reduce(function (obj, item) {
          obj[item.name] = item.value;
          return obj;
        }, {});
        var data = $targetWrapper.data();
        data['page_num'] = 1;
        //console.log(formData);
        $.ajax({
          type: 'POST',
          url: vczapi_ajax.ajaxurl,
          data: {
            action: 'vczapi_list_meeting_shortcode_ajax_handler',
            data: data,
            form_data: formData
          },
          beforeSend: function () {
            $targetWrapper.addClass('loading');
          },
          success: function (response) {
            $targetWrapper.removeClass('loading');
            $targetWrapper.find('.vczapi-items-wrap').html(response.content);
            $targetWrapper.find('.vczapi-list-zoom-meetings--pagination').html(response.pagination);
          },
          error: function (MLHttpRequest, textStatus, errorThrown) {}
        });
      });
    },
    filterOnChangeHandler: function () {
      //each individual select option will require a different listeners
      $('form.vczapi-filters').find('select').on('change', function (event) {
        event.preventDefault();
        $(this).parents('form.vczapi-filters').submit();
      });
    },
    eventListeners: function () {
      this.paginationHandler();
      this.filterOnChangeHandler();
      this.filterFormSubmitHandler();
    },
    init: function () {
      this.eventListeners();
    }
  };
  var vczAPIRecordingsGenerateModal = {
    init: function () {
      this.cacheDOM();
      this.evntHandlers();
    },
    cacheDOM: function () {
      this.$recordingsDatePicker = $('.vczapi-check-recording-date');
    },
    evntHandlers: function () {
      $(document).on('click', '.vczapi-view-recording', this.openModal.bind(this));
      $(document).on('click', '.vczapi-modal-close', this.closeModal.bind(this));
      if ($('.vczapi-recordings-list-table').length > 0) {
        console.log('here');
        $('.vczapi-recordings-list-table').DataTable({
          responsive: true,
          language: vczapi_dt_i18n,
          order: [3, "desc"],
          columnDefs: [{
            orderable: false,
            targets: [2, 5]
          }]
        });
      }
      if ($(this.$recordingsDatePicker).length > 0) {
        this.$recordingsDatePicker.datepicker({
          changeMonth: true,
          changeYear: true,
          showButtonPanel: true,
          dateFormat: 'MM yy',
          beforeShow: function (input, inst) {
            setTimeout(function () {
              inst.dpDiv.css({
                top: $('.vczapi-check-recording-date').offset().top + 35,
                left: $('.vczapi-check-recording-date').offset().left
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
    },
    closeModal: function (e) {
      e.preventDefault();
      $('.vczapi-modal-content').remove();
      $('.vczapi-modal').hide();
    },
    openModal: function (e) {
      e.preventDefault();
      var recording_id = $(e.currentTarget).data('recording-id');
      var postData = {
        recording_id: recording_id,
        action: 'get_recording',
        downlable: vczapi_recordings_data.downloadable
      };
      $('.vczapi-modal').html('<p class="vczapi-modal-loader">' + vczapi_recordings_data.loading + '</p>').show();
      $.get(vczapi_ajax.ajaxurl, postData).done(function (response) {
        $('.vczapi-modal').html(response.data).show();
      });
    }
  };
  $(function () {
    vczAPIMeetingList.init();
    //vczAPIMeetingFilter.init();
    vczAPIListUserMeetings.init();
    vczAPIRecordingsGenerateModal.init();
  });
})(jQuery);
/******/ })()
;