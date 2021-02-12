"use strict";

(function ($) {
  var vczAPIListUserMeetings = {
    init: function init() {
      this.cacheDOM();
      this.defaultActions();
    },
    cacheDOM: function cacheDOM() {
      this.$wrapper = $('.vczapi-user-meeting-list');

      if (this.$wrapper === undefined || this.$wrapper.length < 1) {
        return false;
      }
    },
    defaultActions: function defaultActions() {
      this.$wrapper.DataTable({
        responsive: true
      });
    }
  };
  var vczAPIMeetingFilter = {
    init: function init() {
      this.cacheDOM();
      this.evntHandlers();
    },
    cacheDOM: function cacheDOM() {
      this.$taxonomyOrder = $('.vczapi-taxonomy-ordering');
      this.$orderType = $('.vczapi-ordering');
    },
    evntHandlers: function evntHandlers() {
      this.$taxonomyOrder.on('change', this.taxOrdering.bind(this));
      this.$orderType.on('change', this.upcomingLatest.bind(this));
    },
    taxOrdering: function taxOrdering(e) {
      $(e.currentTarget).closest('form').submit();
    },
    upcomingLatest: function upcomingLatest(e) {
      $(e.currentTarget).closest('form').submit();
    }
  };
  var vczAPIRecordingsGenerateModal = {
    init: function init() {
      this.cacheDOM();
      this.evntHandlers();
    },
    cacheDOM: function cacheDOM() {
      this.$recordingsDatePicker = $('.vczapi-check-recording-date');
    },
    evntHandlers: function evntHandlers() {
      $(document).on('click', '.vczapi-view-recording', this.openModal.bind(this));
      $(document).on('click', '.vczapi-modal-close', this.closeModal.bind(this));

      if ($('.vczapi-recordings-list-table').length > 0) {
        $('.vczapi-recordings-list-table').DataTable({
          responsive: true,
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
          beforeShow: function beforeShow(input, inst) {
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
    closeModal: function closeModal(e) {
      e.preventDefault();
      $('.vczapi-modal-content').remove();
      $('.vczapi-modal').hide();
    },
    openModal: function openModal(e) {
      e.preventDefault();
      var recording_id = $(e.currentTarget).data('recording-id');
      var postData = {
        recording_id: recording_id,
        action: 'get_recording',
        downlable: vczapi_ajax.downloadable
      };
      $('.vczapi-modal').html('<p class="vczapi-modal-loader">' + vczapi_ajax.loading + '</p>').show();
      $.get(vczapi_ajax.ajaxurl, postData).done(function (response) {
        $('.vczapi-modal').html(response.data).show();
      });
    }
  };
  $(function () {
    vczAPIMeetingFilter.init();
    vczAPIListUserMeetings.init();
    vczAPIRecordingsGenerateModal.init();
  });
})(jQuery);