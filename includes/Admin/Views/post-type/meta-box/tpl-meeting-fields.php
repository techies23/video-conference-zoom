<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Pre-process Template Data
 */
$meeting_fields = ! empty( $meeting_fields ) && is_array( $meeting_fields ) ? $meeting_fields : [];
$meeting_type   = isset( $meeting_fields['meeting_type'] ) ? (int) $meeting_fields['meeting_type'] : 1;
$is_published   = $post->post_status === 'publish';
$has_zoom_id    = ! empty( $meeting_details ) && is_object( $meeting_details ) && ! empty( $meeting_details->id );
$is_webinar     = ( $meeting_type === 2 );
$text_domain    = 'video-conferencing-with-zoom-api';

// Retrieve Host Data safely
$host_user_meta_id = get_user_meta( get_current_user_id(), 'user_zoom_hostid', true );
$current_host_id   = $meeting_details->host_id ?? $host_user_meta_id;
?>

<table class="form-table">
    <tbody>
    <?php /* ---------------- Shortcode Display ---------------- */ ?>
    <?php if ( $is_published && $has_zoom_id ) : ?>
        <tr>
            <th scope="row"><label for="start_date"><?php esc_html_e( "Shortcode", $text_domain ); ?></label></th>
            <td>
                <span class="dashicons dashicons-admin-page"></span> <span style="background: #f0f0f1;padding: 10px;border: 1px solid #8c8f94;border-radius: 5px;">[zoom_meeting_post post_id="<?php echo esc_attr( $post->ID ); ?>" template="boxed"]</span>
                <p class="description" style="padding-top:10px;">
                    <?php esc_html_e( 'If you need to show this meeting on another page or post please use this shortcode', $text_domain ); ?>
                </p>
            </td>
        </tr>
    <?php endif; ?>

    <?php /* ---------------- Host & Meeting Type Fields ---------------- */ ?>
    <?php if ( $has_zoom_id && in_array( $post->post_status, [ 'publish', 'draft', 'pending', 'private' ], true ) ) : ?>
        <tr>
            <th scope="row"><label for="userId"><?php esc_html_e( 'Meeting Host *', $text_domain ); ?></label></th>
            <td>
                <?php if ( ! empty( $meeting_details->host_id ) ) : ?>
                    <?php
                    $user_data = json_decode( zoom_conference()->getUserInfo( $meeting_details->host_id ) );
                    if ( ! empty( $user_data ) ) {
                        if ( ! empty( $user_data->code ) ) {
                            echo esc_html( $user_data->message );
                        } else {
                            echo '<input type="hidden" name="userId" value="' . esc_attr( $user_data->id ) . '">';
                            echo esc_html( $user_data->first_name ) . ' ( ' . esc_html( $user_data->email ) . ' )';
                        }
                    } else {
                        esc_html_e( 'Please check your internet connection or API connection.', $text_domain );
                    }
                    ?>
                <?php else : ?>
                    <?php printf( esc_html__( 'Did not find any hosts here? Please %scheck here%s to verify your API keys are working correctly.', $text_domain ), '<a href="' . esc_url( admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' ) ) . '">', '</a>' ); ?>
                <?php endif; ?>
                <p class="description"><?php esc_html_e( 'This is host ID for the meeting (Required).', $text_domain ); ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="meeting_type"><?php esc_html_e( 'Meeting Type', $text_domain ); ?></label></th>
            <td>
                <p><?php echo $is_webinar ? esc_html__( 'Zoom Webinar', $text_domain ) : esc_html__( 'Zoom Meeting', $text_domain ); ?></p>
                <p class="description"><?php esc_html_e( 'You cannot update meeting type. This is not allowed to avoid any conflict issues.', $text_domain ); ?></p>
                <input type="hidden" name="meeting_type" value="<?php echo esc_attr( $meeting_type ); ?>">
            </td>
        </tr>

    <?php else : ?>

    <!--
    @TODO

    API CALL when searching user profile.~
    -->
        <tr class="zoom-host-id-selection-admin">
            <th scope="row"><label for="userId"><?php esc_html_e( 'Meeting Host *', $text_domain ); ?></label></th>
            <td>
                <?php if ( ! empty( $host_id ) ) {
                    $user_data = json_decode( zoom_conference()->getUserInfo( $host_id ) );
                    if ( ! empty( $user_data ) ) {
                        if ( ! empty( $user_data->code ) ) {
                            echo esc_html( $user_data->message );
                        } else {
                            echo '<input type="hidden" name="userId" value="' . esc_attr( $user_data->id ) . '">';
                            echo esc_html( $user_data->first_name ) . ' ( ' . esc_html( $user_data->email ) . ' )';
                        }
                    } else {
                        esc_html_e( 'Please check your internet connection or API connection.', $text_domain );
                    }
                } else {
                    // Format static initial users for select options
                    $host_options = [ '' => __( 'Type to search host...', $text_domain ) ];
                    if ( ! empty( $users ) && is_array( $users ) ) {
                        foreach ( $users as $user ) {
                            $host_options[ $user->id ] = sprintf( '%s %s (%s)', $user->first_name ?? '', $user->last_name ?? '', $user->email ?? '' );
                        }
                    }

                    \Codemanas\VczApi\Helpers\FormHelper::fields(
                            'userId',
                            [
                                    'type'              => 'select',
                                    'description'       => __( 'This is host ID for the meeting (Required).', $text_domain ),
                                    'required'          => true,
                                    'options'           => $host_options,
                                    'input_class'       => [ 'vczapi-choices', 'vczapi-admin-post-type-host-selector' ],
//                                    'custom_attributes' => [
//                                            'data-api-action'  => 'vczapi_search_zoom_users',
//                                            'data-placeholder' => __( 'Search host by name or email...', $text_domain ),
//                                            'data-min-search'  => '2',
//                                            'data-searchable'  => 'true',
//                                    ],
                            ],
                            $meeting_fields['userId'] ?? ''
                    );
                    ?>
                    <p class="vczapi-manually-hostid-wrap">
                        <a href="javascript:void(0);" class="vczapi-admin-hostID-manually-add">
                            <?php esc_html_e( 'User not in the list? Click here to manually enter Host.', $text_domain ); ?>
                        </a>
                    </p>
                <?php } ?>
            </td>
        </tr>
    <?php endif; ?>

    <?php /* ---------------- Start Date / Time ---------------- */ ?>
    <tr>
        <th scope="row"><label for="datetimepicker"><?php esc_html_e( 'Start Date/Time *', $text_domain ); ?></label></th>
        <td>
            <?php
            $start_date = $meeting_fields['start_date'] ?? '';
            \Codemanas\VczApi\Helpers\FormHelper::fields(
                    'start_date',
                    [
                            'type'              => 'text',
                            'description'       => __( "Starting Date and Time of the Meeting (Required).", $text_domain ),
                            'required'          => true,
                            'input_class'       => [ 'vczapi-datetimepicker' ],
                            'custom_attributes' => [ 'data-enable-time' => 'true' ],
                    ],
                    $start_date
            );
            ?>
        </td>
    </tr>

    <?php do_action( 'vczapi_admin_before_additional_fields' ); ?>

    <?php /* ---------------- Timezone Selection ---------------- */ ?>
    <tr>
        <th scope="row"><label for="timezone"><?php _e( "Timezone", $text_domain ); ?></label></th>
        <td>
            <?php
            $tzlists           = \Codemanas\VczApi\Helpers\Date::timezone_list();
            $wp_timezone       = \Codemanas\VczApi\Helpers\Date::get_timezone_offset();
            $selected_timezone = $meeting_fields['timezone'] ?? ( ! empty( $tzlists[ $wp_timezone ] ) ? $wp_timezone : '' );
            \Codemanas\VczApi\Helpers\FormHelper::fields(
                    'timezone',
                    [
                            'type'        => 'select',
                            'options'     => $tzlists,
                            'input_class' => [ 'vczapi-choices' ]
                    ],
                    ! empty( $selected_timezone ) ? $selected_timezone : ''
            );
            ?>
        </td>
    </tr>

    <?php /* ---------------- Duration ---------------- */ ?>
    <tr>
        <th scope="row"><label for="duration"><?php _e( "Duration", $text_domain ); ?></label></th>
        <td>
            <span style="margin-right: 5px;">
			<?php
            $duration = vczapi_convertMinutesToHM( $meeting_fields['duration'] ?? 40, false );
            \Codemanas\VczApi\Helpers\FormHelper::fields(
                    'hour',
                    [
                            'type'       => 'select',
                            'options'    => [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24 ],
                            'after_html' => '&nbsp;' . __( "hours", $text_domain )
                    ],
                    ! empty( $duration['hr'] ) ? $duration['hr'] : 0
            );
            ?>
            </span>
            <span>
            <?php
            \Codemanas\VczApi\Helpers\FormHelper::fields(
                    'minute',
                    [
                            'type'       => 'select',
                            'options'    => [ 0 => 0, 10 => 10, 15 => 15, 20 => 20, 30 => 30, 40 => 40, 45 => 45 ],
                            'after_html' => '&nbsp;' . __( "minutes", $text_domain )
                    ],
                    ! empty( $duration['min'] ) ? $duration['min'] : 40
            );
            ?>
            </span>
        </td>
    </tr>

    <?php /* ---------------- Security & Room Rules ---------------- */ ?>
    <tr>
        <th scope="row"><label for="password"><?php esc_html_e( 'Password', $text_domain ); ?></label></th>
        <td class="zvc-meetings-form">
            <input type="text" name="password" id="password" maxlength="10" data-maxlength="10" class="regular-text"
                   value="<?php echo esc_attr( $meeting_details->password ?? '' ); ?>">
            <p class="description"><?php esc_html_e( 'Password to join the meeting. Password may only contain [a-z A-Z 0-9]. Max 10 characters. (Leave blank to auto generate)', $text_domain ); ?></p>
        </td>
    </tr>

    <tr>
        <th scope="row"><label for="disable-waiting-room"><?php esc_html_e( 'Disable Waiting Room', $text_domain ); ?></label></th>
        <td>
            <p class="description">
                <input type="checkbox" id="disable-waiting-room" name="disable-waiting-room" value="yes" <?php checked( 'yes', $meeting_fields['disable_waiting_room'] ?? '' ); ?> class="regular-text">
                <?php esc_html_e( 'Waiting Room is enabled by default - enable this option to allow users to skip waiting room and join directly.', $text_domain ); ?>
                <span style="color:red; display:block; margin-top:5px;"><?php esc_html_e( 'Please keep in mind anyone with the meeting link will be able to join without host authorization.', $text_domain ); ?></span>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row"><label for="meeting_authentication"><?php esc_html_e( 'Meeting Authentication', $text_domain ); ?></label></th>
        <td>
            <p class="description">
                <input type="checkbox" id="meeting_authentication" name="meeting_authentication" value="1" <?php checked( '1', $meeting_fields['meeting_authentication'] ?? '' ); ?> class="regular-text">
                <?php esc_html_e( 'Only logged-in users in Zoom App can join this Meeting.', $text_domain ); ?>
            </p>
        </td>
    </tr>

    <?php /* ---------------- Meeting-Specific Options ---------------- */ ?>
    <tr class="vczapi-admin-hide-on-webinar" <?php echo $is_webinar ? 'style="display: none;"' : ''; ?>>
        <th scope="row"><label for="join_before_host"><?php esc_html_e( 'Join Before Host', $text_domain ); ?></label></th>
        <td>
            <p class="description">
                <input type="checkbox" id="join_before_host" name="join_before_host" value="1" <?php checked( '1', $meeting_fields['join_before_host'] ?? '' ); ?> class="regular-text">
                <?php esc_html_e( 'Allow users to join meeting before host starts/joins. Only for scheduled or recurring meetings.', $text_domain ); ?>
            </p>
        </td>
    </tr>

    <tr class="vczapi-admin-hide-on-webinar" <?php echo $is_webinar ? 'style="display: none;"' : ''; ?>>
        <th scope="row"><label for="jbh_time"><?php esc_html_e( 'Join Before Host Time', $text_domain ); ?></label></th>
        <td>
            <?php $jbh_time = (int) ( $meeting_fields['jbh_time'] ?? 0 ); ?>
            <select id="jbh_time" name="jbh_time">
                <option value="0" <?php selected( $jbh_time, 0 ); ?>><?php esc_html_e( 'Allow participant to join anytime.', $text_domain ); ?></option>
                <option value="5" <?php selected( $jbh_time, 5 ); ?>><?php esc_html_e( 'Allow participant to join 5 minutes before meeting start time.', $text_domain ); ?></option>
                <option value="10" <?php selected( $jbh_time, 10 ); ?>><?php esc_html_e( 'Allow participant to join 10 minutes before meeting start time.', $text_domain ); ?></option>
                <option value="15" <?php selected( $jbh_time, 15 ); ?>><?php esc_html_e( 'Allow participant to join 15 minutes before meeting start time.', $text_domain ); ?></option>
            </select>
            <p class="description"><?php esc_html_e( 'If join_before_host is enabled, set the allowed time buffer for participants.', $text_domain ); ?></p>
        </td>
    </tr>

    <tr>
        <th scope="row"><label for="option_host_video"><?php esc_html_e( 'Host Video', $text_domain ); ?></label></th>
        <td>
            <p class="description">
                <input type="checkbox" id="option_host_video" name="option_host_video" value="1" <?php checked( '1', $meeting_fields['option_host_video'] ?? '' ); ?> class="regular-text">
                <?php esc_html_e( 'Start video when the host joins the meeting.', $text_domain ); ?>
            </p>
        </td>
    </tr>

    <tr class="vczapi-admin-hide-on-webinar" <?php echo $is_webinar ? 'style="display: none;"' : ''; ?>>
        <th scope="row"><label for="option_participants_video"><?php esc_html_e( 'Participants Video', $text_domain ); ?></label></th>
        <td>
            <p class="description">
                <input type="checkbox" id="option_participants_video" name="option_participants_video" value="1" <?php checked( '1', $meeting_fields['option_participants_video'] ?? '' ); ?> class="regular-text">
                <?php esc_html_e( 'Start video when participants join meeting.', $text_domain ); ?>
            </p>
        </td>
    </tr>

    <tr class="vczapi-admin-hide-on-webinar" <?php echo $is_webinar ? 'style="display: none;"' : ''; ?>>
        <th scope="row"><label for="option_mute_participants"><?php esc_html_e( 'Mute Participants upon entry', $text_domain ); ?></label></th>
        <td>
            <p class="description">
                <input type="checkbox" id="option_mute_participants" name="option_mute_participants" value="1" <?php checked( '1', $meeting_fields['option_mute_participants'] ?? '' ); ?> class="regular-text">
                <?php esc_html_e( 'Mutes participants when entering the meeting.', $text_domain ); ?>
            </p>
        </td>
    </tr>

    <?php /* ---------------- Webinar-Specific Options ---------------- */ ?>
    <tr class="vczapi-admin-show-on-webinar" <?php echo ! $is_webinar ? 'style="display: none;"' : ''; ?>>
        <th scope="row"><label for="panelists_video"><?php esc_html_e( 'When Panelists Join', $text_domain ); ?></label></th>
        <td>
            <p class="description">
                <input type="checkbox" id="panelists_video" name="panelists_video" value="1" <?php checked( '1', $meeting_fields['panelists_video'] ?? '' ); ?> class="regular-text">
                <?php esc_html_e( 'Start video when panelists join webinar.', $text_domain ); ?>
            </p>
        </td>
    </tr>

    <tr class="vczapi-admin-show-on-webinar" <?php echo ! $is_webinar ? 'style="display: none;"' : ''; ?>>
        <th scope="row"><label for="practice_session"><?php esc_html_e( 'Practice Session', $text_domain ); ?></label></th>
        <td>
            <p class="description">
                <input type="checkbox" id="practice_session" name="practice_session" value="1" <?php checked( '1', $meeting_fields['practice_session'] ?? '' ); ?> class="regular-text">
                <?php esc_html_e( 'Enable Practice Session.', $text_domain ); ?>
            </p>
        </td>
    </tr>

    <tr class="vczapi-admin-show-on-webinar" <?php echo ! $is_webinar ? 'style="display: none;"' : ''; ?>>
        <th scope="row"><label for="hd_video"><?php esc_html_e( 'HD Video', $text_domain ); ?></label></th>
        <td>
            <p class="description">
                <input type="checkbox" id="hd_video" name="hd_video" value="1" <?php checked( '1', $meeting_fields['hd_video'] ?? '' ); ?> class="regular-text">
                <?php esc_html_e( 'Defaults to HD video.', $text_domain ); ?>
            </p>
        </td>
    </tr>

    <tr class="vczapi-admin-show-on-webinar" <?php echo ! $is_webinar ? 'style="display: none;"' : ''; ?>>
        <th scope="row"><label for="allow_multiple_devices"><?php esc_html_e( 'Allow Multiple Devices', $text_domain ); ?></label></th>
        <td>
            <p class="description">
                <input type="checkbox" id="allow_multiple_devices" name="allow_multiple_devices" value="1" <?php checked( '1', $meeting_fields['allow_multiple_devices'] ?? '' ); ?> class="regular-text">
                <?php esc_html_e( 'Allow attendees to join from multiple devices.', $text_domain ); ?>
            </p>
        </td>
    </tr>

    <?php /* ---------------- Auto Recording ---------------- */ ?>
    <tr>
        <th scope="row"><label for="option_auto_recording"><?php esc_html_e( 'Auto Recording', $text_domain ); ?></label></th>
        <td>
            <?php $auto_recording = $meeting_fields['option_auto_recording'] ?? 'none'; ?>
            <select id="option_auto_recording" name="option_auto_recording">
                <option value="none" <?php selected( 'none', $auto_recording ); ?>><?php esc_html_e( 'No Recordings', $text_domain ); ?></option>
                <option value="local" <?php selected( 'local', $auto_recording ); ?>><?php esc_html_e( 'Local', $text_domain ); ?></option>
                <option value="cloud" <?php selected( 'cloud', $auto_recording ); ?>><?php esc_html_e( 'Cloud', $text_domain ); ?></option>
            </select>
            <p class="description"><?php esc_html_e( 'Set what type of auto recording feature you want to add. Default is none.', $text_domain ); ?></p>
        </td>
    </tr>

    <?php /* ---------------- Alternative Hosts ---------------- */ ?>
    <?php if ( apply_filters( 'vczapi_admin_show_alternative_host_selection', true ) ) : ?>
        <tr>
            <th scope="row"><label for="settings_alternative_hosts"><?php esc_html_e( 'Alternative Hosts', $text_domain ); ?></label></th>
            <td>
                <?php if ( ! empty( $users ) ) : ?>
                    <?php $alt_hosts = $meeting_fields['alternative_host_ids'] ?? []; ?>
                    <select id="settings_alternative_hosts" name="alternative_host_ids[]" multiple class="zvc-hacking-select" style="width: 50%;">
                        <option value=""><?php esc_html_e( 'Select a Host', $text_domain ); ?></option>
                        <?php foreach ( $users as $user ) : ?>
                            <option value="<?php echo esc_attr( $user->id ); ?>" <?php selected( in_array( $user->id, $alt_hosts, true ) ); ?>>
                                <?php echo esc_html( $user->first_name ) . ' ( ' . esc_html( $user->email ) . ' )'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else : ?>
                    <?php printf( esc_html__( 'Did not find any hosts here? Please %scheck here%s to verify your API keys are working correctly.', $text_domain ), '<a href="' . esc_url( admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' ) ) . '">', '</a>' ); ?>
                <?php endif; ?>
                <p class="description"><?php esc_html_e( 'Paid Zoom Account is required for alternative hosts.', $text_domain ); ?></p>
            </td>
        </tr>
    <?php endif; ?>

    </tbody>
</table>