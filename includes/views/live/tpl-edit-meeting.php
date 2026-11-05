<?php
/**
 * View: Edit Meeting Template
 *
 * @package Video_Conferencing_With_Zoom_Api
 */

use Codemanas\VczApi\Helpers\Date;
use Codemanas\VczApi\Helpers\MeetingType;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if any transient by name is available.
$users         = video_conferencing_zoom_api_get_user_transients();
$edit_id       = isset( $_GET['edit'] ) ? sanitize_text_field( wp_unslash( $_GET['edit'] ) ) : '';
$host_id_param = isset( $_GET['host_id'] ) ? sanitize_text_field( wp_unslash( $_GET['host_id'] ) ) : '';

$meeting_info = [];
if ( ! empty( $edit_id ) ) {
    $raw_meeting_info = zoom_conference()->getMeetingInfo( $edit_id );
    if ( ! empty( $raw_meeting_info ) ) {
        $meeting_info = json_decode( $raw_meeting_info );
    }
}

$back_url = add_query_arg(
        [
                'post_type' => 'zoom-meetings',
                'page'      => 'zoom-video-conferencing',
                'host_id'   => ! empty( $meeting_info->host_id ) ? $meeting_info->host_id : $host_id_param,
        ],
        admin_url( 'edit.php' )
);

$form_action_url = add_query_arg(
        [
                'post_type' => 'zoom-meetings',
                'page'      => 'zoom-video-conferencing-add-meeting',
                'edit'      => $edit_id,
                'host_id'   => $host_id_param,
        ],
        admin_url( 'edit.php' )
);
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Edit a Meeting', 'video-conferencing-with-zoom-api' ); ?></h1>
    <a href="<?php echo esc_url( $back_url ); ?>"><?php esc_html_e( 'Back to List', 'video-conferencing-with-zoom-api' ); ?></a>

    <div class="message">
        <?php
        $message = self::get_message();
        if ( ! empty( $message ) ) {
            echo wp_kses_post( $message );
        }
        ?>
    </div>

    <form action="<?php echo esc_url( $form_action_url ); ?>" method="POST" class="zvc-meetings-form">
        <?php wp_nonce_field( '_zoom_update_meeting_nonce_action', '_zoom_update_meeting_nonce' ); ?>
        <input type="hidden" name="meeting_id" value="<?php echo ! empty( $meeting_info->id ) ? esc_attr( $meeting_info->id ) : ''; ?>">

        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="meetingTopic"><?php esc_html_e( 'Meeting Topic *', 'video-conferencing-with-zoom-api' ); ?></label>
                </th>
                <td>
                    <input type="text" id="meetingTopic" name="meetingTopic" size="100" class="regular-text" required
                           value="<?php echo ! empty( $meeting_info->topic ) ? esc_attr( $meeting_info->topic ) : ''; ?>"
                           aria-describedby="meetingTopic-description">
                    <p class="description" id="meetingTopic-description"><?php esc_html_e( 'Meeting topic. (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="meetingAgenda"><?php esc_html_e( 'Meeting Agenda', 'video-conferencing-with-zoom-api' ); ?></label>
                </th>
                <td>
                    <input type="text" id="meetingAgenda" name="agenda" class="regular-text"
                           value="<?php echo ! empty( $meeting_info->agenda ) ? esc_attr( $meeting_info->agenda ) : ''; ?>"
                           aria-describedby="meetingAgenda-description">
                    <p class="description" id="meetingAgenda-description"><?php esc_html_e( 'Meeting Description.', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="userId"><?php esc_html_e( 'Meeting Host *', 'video-conferencing-with-zoom-api' ); ?></label>
                </th>
                <td>
                    <select id="userId" name="userId" required class="zvc-hacking-select" aria-describedby="userId-description">
                        <option value=""><?php esc_html_e( 'Select a Host', 'video-conferencing-with-zoom-api' ); ?></option>
                        <?php if ( ! empty( $users ) ) : ?>
                            <?php foreach ( $users as $user ) : ?>
                                <?php $current_host_id = ! empty( $meeting_info->host_id ) ? $meeting_info->host_id : ''; ?>
                                <option value="<?php echo esc_attr( $user->id ); ?>" <?php selected( $current_host_id, $user->id ); ?>>
                                    <?php echo esc_html( sprintf( '%s ( %s )', $user->first_name, $user->email ) ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <p class="description" id="userId-description"><?php esc_html_e( 'This is host ID for the meeting (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr style="<?php echo ! empty( $meeting_info->type ) && MeetingType::is_recurring_no_fixed_time_meeting( $meeting_info->type ) ? 'display:none;' : 'display:table-row;'; ?>">
                <th scope="row">
                    <label for="datetimepicker"><?php esc_html_e( 'Start Date/Time *', 'video-conferencing-with-zoom-api' ); ?></label>
                </th>
                <td>
                    <?php
                    $start_time = ! empty( $meeting_info->start_time ) ? $meeting_info->start_time : '';
                    $timezone   = ! empty( $meeting_info->timezone ) ? $meeting_info->timezone : 'America/Los_Angeles';
                    $date_formatted = '';
                    if ( ! empty( $start_time ) ) {
                        try {
                            $tz   = new DateTimeZone( $timezone );
                            $date = new DateTime( $start_time );
                            $date->setTimezone( $tz );
                            $date_formatted = $date->format( 'Y-m-d H:i:s' );
                        } catch ( Exception $e ) {
                            $date_formatted = '';
                        }
                    }
                    ?>
                    <input type="text" name="start_date" id="datetimepicker"
                           data-existingdate="<?php echo esc_attr( $date_formatted ); ?>"
                           value="<?php echo esc_attr( $date_formatted ); ?>"
                           required class="regular-text" aria-describedby="start_date-description">
                    <p class="description" id="start_date-description"><?php esc_html_e( 'Starting Date and Time of the Meeting (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="timezone"><?php esc_html_e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></label>
                </th>
                <td>
                    <?php
                    $tzlists           = Date::timezone_list();
                    $meeting_timezone  = ! empty( $meeting_info->timezone ) ? $meeting_info->timezone : '';
                    ?>
                    <select id="timezone" name="timezone" class="zvc-hacking-select" aria-describedby="timezone-description">
                        <?php foreach ( $tzlists as $k => $tzlist ) : ?>
                            <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $meeting_timezone, $k ); ?>>
                                <?php echo esc_html( $tzlist ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description" id="timezone-description"><?php esc_html_e( 'Meeting Timezone', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="duration"><?php esc_html_e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></label>
                </th>
                <td>
                    <input type="number" id="duration" name="duration" class="regular-text"
                           value="<?php echo ! empty( $meeting_info->duration ) ? esc_attr( $meeting_info->duration ) : 40; ?>"
                           aria-describedby="duration-description">
                    <p class="description" id="duration-description"><?php esc_html_e( 'Meeting duration (minutes). (optional)', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="password"><?php esc_html_e( 'Meeting Password', 'video-conferencing-with-zoom-api' ); ?></label>
                </th>
                <td>
                    <input type="text" id="password" name="password" class="regular-text" maxlength="10" data-maxlength="9"
                           value="<?php echo ! empty( $meeting_info->password ) ? esc_attr( $meeting_info->password ) : ''; ?>"
                           aria-describedby="password-description">
                    <p class="description" id="password-description"><?php esc_html_e( 'Password to join the meeting. Password may only contain the following characters: [a-z A-Z 0-9]. Max of 10 characters.( Leave blank for auto generate )', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="join_before_host"><?php esc_html_e( 'Join Before Host', 'video-conferencing-with-zoom-api' ); ?></label>
                </th>
                <td>
                    <p class="description" id="join_before_host-description">
                        <input type="checkbox" id="join_before_host" name="join_before_host" value="1"
                                <?php checked( ! empty( $meeting_info->settings->join_before_host ) ); ?> class="regular-text">
                        <?php esc_html_e( 'Join meeting before host start the meeting. Only for scheduled or recurring meetings.', 'video-conferencing-with-zoom-api' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="option_host_video"><?php esc_html_e( 'Host join start', 'video-conferencing-with-zoom-api' ); ?></label>
                </th>
                <td>
                    <p class="description" id="option_host_video-description">
                        <input type="checkbox" id="option_host_video" name="option_host_video" value="1"
                                <?php checked( ! empty( $meeting_info->settings->host_video ) ); ?> class="regular-text">
                        <?php esc_html_e( 'Start video when host join meeting.', 'video-conferencing-with-zoom-api' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="option_participants_video"><?php esc_html_e( 'Participants Video', 'video-conferencing-with-zoom-api' ); ?></label>
                </th>
                <td>
                    <p class="description" id="option_participants_video-description">
                        <input type="checkbox" id="option_participants_video" name="option_participants_video" value="1"
                                <?php checked( ! empty( $meeting_info->settings->participant_video ) ); ?> class="regular-text">
                        <?php esc_html_e( 'Start video when participants join meeting.', 'video-conferencing-with-zoom-api' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="option_mute_participants"><?php esc_html_e( 'Mute Participants upon entry', 'video-conferencing-with-zoom-api' ); ?></label>
                </th>
                <td>
                    <p class="description" id="option_mute_participants-description">
                        <input type="checkbox" id="option_mute_participants" name="option_mute_participants" value="1"
                                <?php checked( ! empty( $meeting_info->settings->mute_upon_entry ) ); ?> class="regular-text">
                        <?php esc_html_e( 'Mutes Participants when entering the meeting.', 'video-conferencing-with-zoom-api' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="option_auto_recording"><?php esc_html_e( 'Auto Recording', 'video-conferencing-with-zoom-api' ); ?></label>
                </th>
                <td>
                    <?php $auto_recording = ! empty( $meeting_info->settings->auto_recording ) ? $meeting_info->settings->auto_recording : 'none'; ?>
                    <select id="option_auto_recording" name="option_auto_recording" aria-describedby="option_auto_recording_description">
                        <option value="none" <?php selected( $auto_recording, 'none' ); ?>><?php esc_html_e( 'No Recordings', 'video-conferencing-with-zoom-api' ); ?></option>
                        <option value="local" <?php selected( $auto_recording, 'local' ); ?>><?php esc_html_e( 'Local', 'video-conferencing-with-zoom-api' ); ?></option>
                        <option value="cloud" <?php selected( $auto_recording, 'cloud' ); ?>><?php esc_html_e( 'Cloud', 'video-conferencing-with-zoom-api' ); ?></option>
                    </select>
                    <p class="description" id="option_auto_recording_description"><?php esc_html_e( 'Set what type of auto recording feature you want to add. Default is none.', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="alternative_host_ids"><?php esc_html_e( 'Alternative Hosts', 'video-conferencing-with-zoom-api' ); ?></label>
                </th>
                <td>
                    <?php
                    $option_alternative_hosts = [];
                    if ( ! empty( $meeting_info->settings->alternative_hosts ) ) {
                        $option_alternative_hosts = array_map( 'trim', explode( ',', $meeting_info->settings->alternative_hosts ) );
                    }
                    ?>
                    <select id="alternative_host_ids" name="alternative_host_ids[]" multiple class="zvc-hacking-select" aria-describedby="settings_alternative_hosts-description">
                        <option value=""><?php esc_html_e( 'Select a Host', 'video-conferencing-with-zoom-api' ); ?></option>
                        <?php if ( ! empty( $users ) ) : ?>
                            <?php foreach ( $users as $user ) : ?>
                                <?php $is_host_found = in_array( $user->email, $option_alternative_hosts, true ); ?>
                                <option value="<?php echo esc_attr( $user->id ); ?>" <?php selected( $is_host_found ); ?>>
                                    <?php echo esc_html( sprintf( '%s ( %s )', $user->first_name, $user->email ) ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <p class="description" id="settings_alternative_hosts-description"><?php esc_html_e( 'Paid Zoom Account is required for this !! Alternative hosts IDs. Multiple value separated by comma.', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" name="update_meeting" class="button button-primary" value="<?php esc_attr_e( 'Update Meeting', 'video-conferencing-with-zoom-api' ); ?>">
        </p>
    </form>
</div>