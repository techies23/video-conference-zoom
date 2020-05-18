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

            $dom.select2 = $('.zvc-hacking-select');
            $dom.dateTimePicker = $('#datetimepicker');
            $dom.reportsDatePicker = $('#reports_date');
            $dom.zoomAccountDatepicker = $(".zoom_account_datepicker");
            $dom.dataTable = $('#zvc_users_list_table, #zvc_meetings_list_table');
            $dom.meetingListTableCheck = $("#zvc_meetings_list_table");
            $dom.meetingListTbl = $dom.meetingListTableCheck.find('input[type=checkbox]');
            $dom.cover = $('#zvc-cover');
            $dom.togglePwd = $('.toggle-api');
            $dom.toggleSecret = $('.toggle-secret');

            $dom.changeMeetingState = $('.vczapi-meeting-state-change');

            $dom.show_on_meeting_delete_error = $('.show_on_meeting_delete_error');
        },
        eventListeners: function () {

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

            //FOr the Password Hashing API
            $dom.togglePwd.on('click', this.toggleAPISettings.bind(this));
            $dom.toggleSecret.on('click', this.toggleSecretSettings.bind(this));

            $('.vanity-url-enable').on('click', this.vanityURLShow);
            $('.zvc-dismiss-message').on('click', this.dismissNotice.bind(this));

            $('.check-api-connection').on('click', this.checkConnection.bind(this));

            //End and Resume Meetings
            $($dom.changeMeetingState).on('click', this.meetingStateChange.bind(this));
        },

        initializeDependencies: function () {
            if ($dom.select2.length > 0) {
                $dom.select2.select2();
            }

            //DatePickers
            this.datePickers();

            /***********************************************************
             * Start For Users and Meeting DATA table Listing Section
             **********************************************************/
            if ($dom.dataTable.length > 0) {
                $dom.dataTable.dataTable({
                    "pageLength": 25,
                    "columnDefs": [{
                        "targets": 0,
                        "orderable": false
                    }]
                });
            }
        },

        datePickers: function () {
            //For Datepicker
            if ($dom.dateTimePicker.length > 0) {
                var d = new Date();
                var month = d.getMonth() + 1;
                var day = d.getDate();
                var time = d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
                var output = d.getFullYear() + '-' +
                    (month < 10 ? '0' : '') + month + '-' +
                    (day < 10 ? '0' : '') + day + ' ' + time;
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
                        var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                        var year = $("#ui-datepicker-div .ui-datepicker-year").html();
                        thisCalendar.datepicker('setDate', new Date(year, month, 1));
                    });
                });
            }

            if ($dom.zoomAccountDatepicker.length > 0) {
                $dom.zoomAccountDatepicker.datepicker({dateFormat: "yy-mm-dd"});
            }
        },

        meetingListTableCheck: function () {
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

        bulkDeleteMeetings: function () {
            var r = confirm("Confirm bulk delete these Meeting?");
            if (r == true) {
                var arr_checkbox = [];
                $dom.meetingListTableCheck.find('input.checkthis').each(function () {
                    if ($(this).is(':checked')) {
                        arr_checkbox.push($(this).val());
                    }
                });

                var hostid = $(this).data('hostid');
                //Process bulk delete
                if (arr_checkbox) {
                    var data = {meetings_id: arr_checkbox, host_id: hostid, action: 'zvc_bulk_meetings_delete', security: zvc_ajax.zvc_security};
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

        deleteMetting: function () {
            var meeting_id = $(this).data('meetingid');
            var host_id = $(this).data('hostid');

            var r = confirm("Confirm Delete this Meeting?");
            if (r == true) {
                var data = {meeting_id: meeting_id, host_id: host_id, action: 'zvc_delete_meeting', security: zvc_ajax.zvc_security};
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

        toggleAPISettings: function () {
            var inputID = $('#zoom_api_key');
            if ($dom.togglePwd.html() === "Show") {
                $dom.togglePwd.html('Hide');
                inputID.attr('type', 'text');
            } else {
                $dom.togglePwd.html('Show');
                inputID.attr('type', 'password');
            }
        },

        toggleSecretSettings: function () {
            var secretID = $('#zoom_api_secret');
            if ($dom.toggleSecret.html() === "Show") {
                $dom.toggleSecret.html('Hide');
                secretID.attr('type', 'text');
            } else {
                $dom.toggleSecret.html('Show');
                secretID.attr('type', 'password');
            }
        },

        dismissNotice: function (e) {
            e.preventDefault();
            $(e.currentTarget).closest('.notice-success').hide();
            $.post(zvc_ajax.ajaxurl, {action: 'zoom_dimiss_notice'}).done(function (result) {
                //Done
                console.log(result);
            });
        },

        checkConnection: function (e) {
            e.preventDefault();
            $dom.cover.show();
            $.post(zvc_ajax.ajaxurl, {action: 'check_connection', security: zvc_ajax.zvc_security}).done(function (result) {
                //Done
                $dom.cover.hide();
                alert(result);
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
        changeState: function (postData) {
            $.post(zvc_ajax.ajaxurl, postData).done(function (response) {
               location.reload();
            });
        }
    };

    $(function () {
        ZoomAPIJS.onReady();
    });

})(jQuery);