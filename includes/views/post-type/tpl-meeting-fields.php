<?php
/**
 * View: Post Meta Meeting Form Template
 *
 * @package Video_Conferencing_With_Zoom_Api
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;
$meeting_details = get_post_meta( $post->ID, '_meeting_zoom_details', true );
$meeting_fields  = ! empty( $meeting_fields ) && is_array( $meeting_fields ) ? $meeting_fields : [];
if ( empty( $meeting_fields['meeting_type'] ) ) {
    $meeting_fields['meeting_type'] = 1;
}
?>

<table class="form-table">
    <tbody>
    <?php if ( 'publish' === $post->post_status && is_object( $meeting_details ) && ! empty( $meeting_details->id ) ) : ?>
        <?php
        $shortcode = sprintf( '[zoom_meeting_post post_id="%d" template="boxed"]', (int) $post->ID );
        $copy_msg  = __( 'Copied to clipboard', 'video-conferencing-with-zoom-api' );
        ?>
        <tr>
            <th scope="row"><label for="meeting-shortcode"><?php esc_html_e( 'Shortcode', 'video-conferencing-with-zoom-api' ); ?></label></th>
            <td>
                <input class="text regular-text" id="meeting-shortcode" type="text" readonly value="<?php echo esc_attr( $shortcode ); ?>" onclick="this.select(); document.execCommand('copy'); alert('<?php echo esc_js( $copy_msg ); ?>');"/>
                <p class="description">
                    <?php esc_html_e( 'If you need to show this meeting on another page or post please use this shortcode', 'video-conferencing-with-zoom-api' ); ?>
                </p>
            </td>
        </tr>
    <?php endif; ?>

    <?php if ( ! empty( $meeting_details ) && ! empty( $meeting_details->id ) && in_array( $post->post_status, [ 'publish', 'draft', 'pending', 'private' ], true ) ) : ?>
        <tr class="zoom-host-id-selection-admin">
            <th scope="row"><label for="userId"><?php esc_html_e( 'Meeting Host *', 'video-conferencing-with-zoom-api' ); ?></label></th>
            <td>
                <?php
                if ( ! empty( $meeting_details->host_id ) ) {
                    $raw_user = zoom_conference()->getUserInfo( $meeting_details->host_id );
                    $user     = ! is_wp_error( $raw_user ) && is_string( $raw_user ) ? json_decode( $raw_user ) : null;
                    if ( ! empty( $user ) ) {
                        if ( ! empty( $user->code ) ) {
                            echo esc_html( $user->message );
                        } else {
                            echo '<input type="hidden" name="userId" value="' . esc_attr( $user->id ) . '">';
                            echo esc_html( sprintf( '%s ( %s )', ! empty( $user->first_name ) ? $user->first_name : '', ! empty( $user->email ) ? $user->email : '' ) );
                        }
                    } else {
                        esc_html_e( 'Please check your internet connection or API connection.', 'video-conferencing-with-zoom-api' );
                    }
                } else {
                    $settings_url = admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' );
                    printf(
                    /* translators: 1: Opening anchor tag for settings page, 2: Closing anchor tag */
                            esc_html__( 'Did not find any hosts here ? Please %1$scheck here%2$s to verify your API keys are working correctly.', 'video-conferencing-with-zoom-api' ),
                            '<a href="' . esc_url( $settings_url ) . '">',
                            '</a>'
                    );
                }
                ?>
                <p class="description" id="userId-description"><?php esc_html_e( 'This is host ID for the meeting (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="meeting_type"><?php esc_html_e( 'Meeting Type', 'video-conferencing-with-zoom-api' ); ?></label></th>
            <td>
                <p>
                    <?php echo ( ! empty( $meeting_fields['meeting_type'] ) && 2 === (int) $meeting_fields['meeting_type'] ) ? esc_html__( 'Zoom Webinar', 'video-conferencing-with-zoom-api' ) : esc_html__( 'Zoom Meeting', 'video-conferencing-with-zoom-api' ); ?>
                </p>
                <p class="description"><?php esc_html_e( 'You cannot update meeting type. This is not allowed to avoid any conflict issues.', 'video-conferencing-with-zoom-api' ); ?></p>
                <input type="hidden" name="meeting_type" value="<?php echo esc_attr( absint( $meeting_fields['meeting_type'] ) ); ?>">
            </td>
        </tr>
    <?php else : ?>
        <?php
        $host_id = get_user_meta( get_current_user_id(), 'user_zoom_hostid', true );
        if ( ! empty( $host_id ) ) :
            ?>
            <tr class="zoom-host-id-selection-admin">
                <th scope="row"><label for="userId"><?php esc_html_e( 'Meeting Host *', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <?php
                    $raw_user = zoom_conference()->getUserInfo( $host_id );
                    $user     = ! is_wp_error( $raw_user ) && is_string( $raw_user ) ? json_decode( $raw_user ) : null;
                    if ( ! empty( $user ) ) {
                        if ( ! empty( $user->code ) ) {
                            echo esc_html( $user->message );
                        } else {
                            echo '<input type="hidden" name="userId" value="' . esc_attr( $user->id ) . '">';
                            echo esc_html( sprintf( '%s ( %s )', ! empty( $user->first_name ) ? $user->first_name : '', ! empty( $user->email ) ? $user->email : '' ) );
                        }
                    } else {
                        esc_html_e( 'Please check your internet connection or API connection.', 'video-conferencing-with-zoom-api' );
                    }
                    ?>
                    <p class="description" id="userId-description"><?php esc_html_e( 'This is host ID for the meeting (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
        <?php else : ?>
            <tr class="zoom-host-id-selection-admin">
                <th scope="row"><label for="userId"><?php esc_html_e( 'Meeting Host *', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <?php if ( ! empty( $users ) && is_array( $users ) ) : ?>
                        <?php
                        $count = count( $users );
                        if ( 1 === $count ) :
                            ?>
                            <input type="hidden" name="userId" value="<?php echo esc_attr( $users[0]->id ); ?>">
                            <span><?php echo esc_html( sprintf( '%s ( %s )', ! empty( $users[0]->first_name ) ? $users[0]->first_name : '', ! empty( $users[0]->email ) ? $users[0]->email : '' ) ); ?></span>
                        <?php else : ?>
                            <select id="userId" name="userId" required class="zvc-hacking-select vczapi-admin-post-type-host-selector" style="width:50%;" aria-describedby="userId-description">
                                <option value=""><?php esc_html_e( 'Select a Host', 'video-conferencing-with-zoom-api' ); ?></option>
                                <?php foreach ( $users as $user ) : ?>
                                    <option value="<?php echo esc_attr( $user->id ); ?>" <?php selected( ! empty( $meeting_fields['userId'] ) ? $meeting_fields['userId'] : '', $user->id ); ?>>
                                        <?php echo esc_html( sprintf( '%s ( %s )', ! empty( $user->first_name ) ? $user->first_name : '', ! empty( $user->email ) ? $user->email : '' ) ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="vczapi-manually-hostid-wrap">
                                <a href="javascript:void(0);" class="vczapi-admin-hostID-manually-add"><?php esc_html_e( 'User not in the list? Click here to manually enter Host.', 'video-conferencing-with-zoom-api' ); ?></a>
                            </p>
                        <?php endif; ?>
                        <p class="description" id="userId-description"><?php esc_html_e( 'This is host ID for the meeting (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
                    <?php else : ?>
                        <?php
                        $settings_url = admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' );
                        printf(
                        /* translators: 1: Opening anchor tag for settings page, 2: Closing anchor tag */
                                esc_html__( 'Did not find any hosts here ? Please %1$scheck here%2$s to verify your API keys are working correctly.', 'video-conferencing-with-zoom-api' ),
                                '<a href="' . esc_url( $settings_url ) . '">',
                                '</a>'
                        );
                        ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endif; ?>
        <tr class="zoom-meeting-type-selection-admin">
            <th scope="row"><label for="vczapi-admin-meeting-ype"><?php esc_html_e( 'Meeting Type', 'video-conferencing-with-zoom-api' ); ?></label></th>
            <td>
                <select id="vczapi-admin-meeting-ype" name="meeting_type" class="meeting-type-selection" aria-describedby="meeting-type-description">
                    <option value="1" <?php selected( ! empty( $meeting_fields['meeting_type'] ) ? absint( $meeting_fields['meeting_type'] ) : 1, 1 ); ?>><?php esc_html_e( 'Meeting', 'video-conferencing-with-zoom-api' ); ?></option>
                    <option value="2" <?php selected( ! empty( $meeting_fields['meeting_type'] ) ? absint( $meeting_fields['meeting_type'] ) : 1, 2 ); ?>><?php esc_html_e( 'Webinar', 'video-conferencing-with-zoom-api' ); ?></option>
                </select>
                <p class="description" id="meeting-type-description"><?php esc_html_e( 'Which type of meeting do you want to create.', 'video-conferencing-with-zoom-api' ); ?></p>
                <p class="description"><?php esc_html_e( 'Note: Webinar requires Zoom Webinar Plan enabled in your account.', 'video-conferencing-with-zoom-api' ); ?></p>
            </td>
        </tr>
    <?php endif; ?>

    <tr>
        <th scope="row"><label for="datetimepicker"><?php esc_html_e( 'Start Date/Time *', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <?php $start_date_val = ! empty( $meeting_fields['start_date'] ) ? $meeting_fields['start_date'] : ''; ?>
            <input type="text" name="start_date" id="datetimepicker" data-existingdate="<?php echo esc_attr( $start_date_val ); ?>" value="<?php echo esc_attr( $start_date_val ); ?>" required class="regular-text" aria-describedby="start_date-description">
            <p class="description" id="start_date-description"><?php esc_html_e( 'Starting Date and Time of the Meeting (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
        </td>
    </tr>

    <?php do_action( 'vczapi_admin_before_additional_fields' ); ?>

    <tr>
        <th scope="row"><label for="timezone"><?php esc_html_e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <?php
            $tzlists     = \Codemanas\VczApi\Helpers\Date::timezone_list();
            $wp_timezone = \Codemanas\VczApi\Helpers\Date::get_timezone_offset();
            $selected_tz = ! empty( $meeting_fields['timezone'] ) ? $meeting_fields['timezone'] : ( ! empty( $wp_timezone ) ? $wp_timezone : '' );
            ?>
            <select id="timezone" name="timezone" class="zvc-hacking-select" aria-describedby="timezone-description">
                <?php foreach ( $tzlists as $k => $tzlist ) : ?>
                    <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $selected_tz, $k ); ?>><?php echo esc_html( $tzlist ); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description" id="timezone-description"><?php esc_html_e( 'Meeting Timezone', 'video-conferencing-with-zoom-api' ); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="option_duration_hour"><?php esc_html_e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <?php
            $duration_mins = ! empty( $meeting_fields['duration'] ) ? (int) $meeting_fields['duration'] : 40;
            $duration      = vczapi_convertMinutesToHM( $duration_mins, false );
            $selected_hr   = isset( $duration['hr'] ) ? (int) $duration['hr'] : 0;
            $selected_min  = isset( $duration['min'] ) ? (int) $duration['min'] : 0;
            ?>
            <label>
                <select id="option_duration_hour" class="option_duration_hour" name="option_duration_hour">
                    <?php for ( $hr = 0; $hr <= 24; $hr ++ ) : ?>
                        <option value="<?php echo (int) $hr; ?>" <?php selected( $selected_hr, $hr ); ?>><?php echo (int) $hr; ?></option>
                    <?php endfor; ?>
                </select> <?php esc_html_e( 'hr', 'video-conferencing-with-zoom-api' ); ?>
            </label>
            <label style="margin-left:10px;">
                <select class="option_duration_minutes" name="option_duration_minutes" aria-label="<?php esc_attr_e( 'Duration Minutes', 'video-conferencing-with-zoom-api' ); ?>">
                    <option value="0" <?php selected( $selected_min, 0 ); ?>>0</option>
                    <option value="15" <?php selected( $selected_min, 15 ); ?>>15</option>
                    <option value="30" <?php selected( $selected_min, 30 ); ?>>30</option>
                    <option value="45" <?php selected( $selected_min, 45 ); ?>>45</option>
                </select> <?php esc_html_e( 'min', 'video-conferencing-with-zoom-api' ); ?>
            </label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="password"><?php esc_html_e( 'Password', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td class="zvc-meetings-form">
            <input type="text" id="password" name="password" maxlength="10" data-maxlength="10" class="regular-text" value="<?php echo ! empty( $meeting_details->password ) ? esc_attr( $meeting_details->password ) : ''; ?>" aria-describedby="email-description">
            <p class="description" id="email-description"><?php esc_html_e( 'Password to join the meeting. Password may only contain the following characters: [a-z A-Z 0-9]. Max of 10 characters.( Leave blank for auto generate )', 'video-conferencing-with-zoom-api' ); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="disable-waiting-room"><?php esc_html_e( 'Disable Waiting Room', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description">
                <input type="checkbox" id="disable-waiting-room" name="disable-waiting-room" value="yes" <?php checked( ! empty( $meeting_fields['disable_waiting_room'] ) ? $meeting_fields['disable_waiting_room'] : '', 'yes' ); ?> class="regular-text">
                <?php esc_html_e( 'Waiting Room is enabled by default - if you want users to skip the waiting room and join the meeting directly - enable this option.', 'video-conferencing-with-zoom-api' ); ?>
                <span style="color:red"><?php esc_html_e( 'Please keep in mind anyone with the meeting link will be able to join without you allowing them into the meeting.', 'video-conferencing-with-zoom-api' ); ?></span>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="meeting-authentication"><?php esc_html_e( 'Meeting Authentication', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <p class="description">
                <input type="checkbox" id="meeting-authentication" name="meeting_authentication" value="1" <?php checked( ! empty( $meeting_fields['meeting_authentication'] ) ? $meeting_fields['meeting_authentication'] : '', '1' ); ?> class="regular-text">
                <?php esc_html_e( 'Only loggedin users in Zoom App can join this Meeting.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-hide-on-webinar" <?php echo ( ! empty( $meeting_fields['meeting_type'] ) && 2 === (int) $meeting_fields['meeting_type'] ) ? 'style="display: none;"' : ''; ?>>
        <th scope="row"><label for="join_before_host"><?php esc_html_e( 'Join Before Host', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <p class="description">
                <input type="checkbox" id="join_before_host" name="join_before_host" value="1" <?php checked( ! empty( $meeting_fields['join_before_host'] ) ? $meeting_fields['join_before_host'] : '', '1' ); ?> class="regular-text">
                <?php esc_html_e( 'Allow users to join meetin before host start/joins the meeting. Only for scheduled or recurring meetings. If the waiting room is enabled, this setting will not work.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-hide-on-webinar" <?php echo ( ! empty( $meeting_fields['meeting_type'] ) && 2 === (int) $meeting_fields['meeting_type'] ) ? 'style="display: none;"' : ''; ?>>
        <th scope="row"><label for="jbh_time"><?php esc_html_e( 'Join Before Host Time', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <?php $jbh_time_val = ! empty( $meeting_fields['jbh_time'] ) ? (int) $meeting_fields['jbh_time'] : 0; ?>
            <select id="jbh_time" name="jbh_time" aria-describedby="jbh_time-description">
                <option value="0" <?php selected( $jbh_time_val, 0 ); ?>><?php esc_html_e( 'Allow participant to join anytime.', 'video-conferencing-with-zoom-api' ); ?></option>
                <option value="5" <?php selected( $jbh_time_val, 5 ); ?>><?php esc_html_e( 'Allow participant to join 5 minutes before meeting start time.', 'video-conferencing-with-zoom-api' ); ?></option>
                <option value="10" <?php selected( $jbh_time_val, 10 ); ?>><?php esc_html_e( 'Allow participant to join 10 minutes before meeting start time.', 'video-conferencing-with-zoom-api' ); ?></option>
                <option value="15" <?php selected( $jbh_time_val, 15 ); ?>><?php esc_html_e( 'Allow participant to join 15 minutes before meeting start time.', 'video-conferencing-with-zoom-api' ); ?></option>
            </select>
            <p class="description" id="jbh_time-description"><?php esc_html_e( 'If the value of join_before_host field is set to true, use this field to indicate time limits when a participant may join a meeting before a host.', 'video-conferencing-with-zoom-api' ); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="option_host_video"><?php esc_html_e( 'Host Video', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <p class="description">
                <input type="checkbox" id="option_host_video" name="option_host_video" value="1" <?php checked( ! empty( $meeting_fields['option_host_video'] ) ? $meeting_fields['option_host_video'] : '', '1' ); ?> class="regular-text">
                <?php esc_html_e( 'Start video when the host joins the meeting.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-hide-on-webinar" <?php echo ( ! empty( $meeting_fields['meeting_type'] ) && 2 === (int) $meeting_fields['meeting_type'] ) ? 'style="display: none;"' : ''; ?>>
        <th scope="row"><label for="option_participants_video"><?php esc_html_e( 'Participants Video', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <p class="description">
                <input type="checkbox" id="option_participants_video" name="option_participants_video" value="1" <?php checked( ! empty( $meeting_fields['option_participants_video'] ) ? $meeting_fields['option_participants_video'] : '', '1' ); ?> class="regular-text">
                <?php esc_html_e( 'Start video when participants join meeting.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-hide-on-webinar" <?php echo ( ! empty( $meeting_fields['meeting_type'] ) && 2 === (int) $meeting_fields['meeting_type'] ) ? 'style="display: none;"' : ''; ?>>
        <th scope="row">
            <label for="option_mute_participants"><?php esc_html_e( 'Mute Participants upon entry', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description">
                <input type="checkbox" id="option_mute_participants" name="option_mute_participants" value="1" <?php checked( ! empty( $meeting_fields['option_mute_participants'] ) ? $meeting_fields['option_mute_participants'] : '', '1' ); ?> class="regular-text">
                <?php esc_html_e( 'Mutes Participants when entering the meeting.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-show-on-webinar" <?php echo ( ! empty( $meeting_fields['meeting_type'] ) && 1 === (int) $meeting_fields['meeting_type'] ) ? 'style="display: none;"' : ''; ?>>
        <th scope="row">
            <label for="panelists_video"><?php esc_html_e( 'When Panelists Join', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description">
                <input type="checkbox" id="panelists_video" name="panelists_video" value="1" <?php checked( ! empty( $meeting_fields['panelists_video'] ) ? $meeting_fields['panelists_video'] : '', '1' ); ?> class="regular-text">
                <?php esc_html_e( 'Start video when panelists join webinar.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-show-on-webinar" <?php echo ( ! empty( $meeting_fields['meeting_type'] ) && 1 === (int) $meeting_fields['meeting_type'] ) ? 'style="display: none;"' : ''; ?>>
        <th scope="row">
            <label for="practice_session"><?php esc_html_e( 'Practise Session', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description">
                <input type="checkbox" id="practice_session" name="practice_session" value="1" <?php checked( ! empty( $meeting_fields['practice_session'] ) ? $meeting_fields['practice_session'] : '', '1' ); ?> class="regular-text">
                <?php esc_html_e( 'Enable Practise Session.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-show-on-webinar" <?php echo ( ! empty( $meeting_fields['meeting_type'] ) && 1 === (int) $meeting_fields['meeting_type'] ) ? 'style="display: none;"' : ''; ?>>
        <th scope="row">
            <label for="hd_video"><?php esc_html_e( 'HD Video', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description">
                <input type="checkbox" id="hd_video" name="hd_video" value="1" <?php checked( ! empty( $meeting_fields['hd_video'] ) ? $meeting_fields['hd_video'] : '', '1' ); ?> class="regular-text">
                <?php esc_html_e( 'Defaults to HD video.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-show-on-webinar" <?php echo ( ! empty( $meeting_fields['meeting_type'] ) && 1 === (int) $meeting_fields['meeting_type'] ) ? 'style="display: none;"' : ''; ?>>
        <th scope="row">
            <label for="allow_multiple_devices"><?php esc_html_e( 'Allow Multiple Devices', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description">
                <input type="checkbox" id="allow_multiple_devices" name="allow_multiple_devices" value="1" <?php checked( ! empty( $meeting_fields['allow_multiple_devices'] ) ? $meeting_fields['allow_multiple_devices'] : '', '1' ); ?> class="regular-text">
                <?php esc_html_e( 'Allow attendess to join from multiple devices.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="option_auto_recording"><?php esc_html_e( 'Auto Recording', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <?php $auto_rec = ! empty( $meeting_fields['option_auto_recording'] ) ? $meeting_fields['option_auto_recording'] : 'none'; ?>
            <select id="option_auto_recording" name="option_auto_recording" aria-describedby="option_auto_recording_description">
                <option value="none" <?php selected( $auto_rec, 'none' ); ?>><?php esc_html_e( 'No Recordings', 'video-conferencing-with-zoom-api' ); ?></option>
                <option value="local" <?php selected( $auto_rec, 'local' ); ?>><?php esc_html_e( 'Local', 'video-conferencing-with-zoom-api' ); ?></option>
                <option value="cloud" <?php selected( $auto_rec, 'cloud' ); ?>><?php esc_html_e( 'Cloud', 'video-conferencing-with-zoom-api' ); ?></option>
            </select>
            <p class="description" id="option_auto_recording_description"><?php esc_html_e( 'Set what type of auto recording feature you want to add. Default is none.', 'video-conferencing-with-zoom-api' ); ?></p>
        </td>
    </tr>
    <?php
    $show_host = apply_filters( 'vczapi_admin_show_alternative_host_selection', true );
    if ( $show_host ) :
        ?>
        <tr>
            <th scope="row"><label for="settings_alternative_hosts"><?php esc_html_e( 'Alternative Hosts', 'video-conferencing-with-zoom-api' ); ?></label></th>
            <td>
                <?php if ( ! empty( $users ) && is_array( $users ) ) : ?>
                    <?php $selected_alt_hosts = ! empty( $meeting_fields['alternative_host_ids'] ) && is_array( $meeting_fields['alternative_host_ids'] ) ? $meeting_fields['alternative_host_ids'] : []; ?>
                    <select id="settings_alternative_hosts" name="alternative_host_ids[]" multiple class="zvc-hacking-select" style="width: 50%;" aria-describedby="settings_alternative_hosts-description">
                        <option value=""><?php esc_html_e( 'Select a Host', 'video-conferencing-with-zoom-api' ); ?></option>
                        <?php foreach ( $users as $user ) : ?>
                            <option value="<?php echo esc_attr( $user->id ); ?>" <?php selected( in_array( (string) $user->id, array_map( 'strval', $selected_alt_hosts ), true ) ); ?>>
                                <?php echo esc_html( sprintf( '%s ( %s )', ! empty( $user->first_name ) ? $user->first_name : '', ! empty( $user->email ) ? $user->email : '' ) ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else : ?>
                    <?php
                    $settings_url = admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' );
                    printf(
                    /* translators: 1: Opening anchor tag for settings page, 2: Closing anchor tag */
                            esc_html__( 'Did not find any hosts here ? Please %1$scheck here%2$s to verify your API keys are working correctly.', 'video-conferencing-with-zoom-api' ),
                            '<a href="' . esc_url( $settings_url ) . '">',
                            '</a>'
                    );
                    ?>
                <?php endif; ?>
                <p class="description" id="settings_alternative_hosts-description"><?php esc_html_e( 'Paid Zoom Account is required for this !! Alternative hosts IDs. Multiple value separated by comma.', 'video-conferencing-with-zoom-api' ); ?></p>
            </td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>