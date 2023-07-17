<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Check if any transient by name is available
$users        = video_conferencing_zoom_api_get_user_transients();
$meeting_info = json_decode( zoom_conference()->getMeetingInfo( $_GET['edit'] ) );
if ( ! empty( $meeting_info ) ) {
	$option_jbh                = ! empty( $meeting_info->settings->join_before_host ) && $meeting_info->settings->join_before_host ? 'checked' : false;
	$option_host_video         = ! empty( $meeting_info->settings->host_video ) && $meeting_info->settings->host_video ? 'checked' : false;
	$option_participants_video = ! empty( $meeting_info->settings->participant_video ) && $meeting_info->settings->participant_video ? 'checked' : false;
	$option_mute_participants  = ! empty( $meeting_info->settings->mute_upon_entry ) && $meeting_info->settings->mute_upon_entry ? 'checked' : false;
	$option_enforce_login      = ! empty( $meeting_info->settings->enforce_login ) && $meeting_info->settings->enforce_login ? 'checked' : false;
	$option_alternative_hosts  = $meeting_info->settings->alternative_hosts ? $meeting_info->settings->alternative_hosts : false;
	if ( ! empty( $option_alternative_hosts ) ) {
		$option_alternative_hosts = explode( ', ', $option_alternative_hosts );
	}
}
?>
<div class="wrap">
    <h1><?php _e( 'Edit a Meeting', 'video-conferencing-with-zoom-api' ); ?></h1>
    <a href="edit.php?post_type=zoom-meetings&page=zoom-video-conferencing&host_id=<?php echo $meeting_info->host_id; ?>"><?php _e( 'Back to List', 'video-conferencing-with-zoom-api' ); ?></a>
    <div class="message">
		<?php
		$message = self::get_message();
		if ( isset( $message ) && ! empty( $message ) ) {
			echo $message;
		}
		?>
    </div>
    <form action="edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-add-meeting&edit=<?php echo esc_html( $_GET['edit'] ); ?>&host_id=<?php echo esc_html( $_GET['host_id'] ); ?>" method="POST" class="zvc-meetings-form">
		<?php wp_nonce_field( '_zoom_update_meeting_nonce_action', '_zoom_update_meeting_nonce' ); ?>
        <input type="hidden" name="meeting_id" value="<?php echo esc_attr( $meeting_info->id ); ?>">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="meetingTopic"><?php _e( 'Meeting Topic *', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <input type="text" name="meetingTopic" size="100" class="regular-text" required value="<?php echo ! empty( $meeting_info->topic ) ? esc_attr( $meeting_info->topic ) : null; ?>">
                    <p class="description" id="meetingTopic-description"><?php _e( 'Meeting topic. (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="meetingAgenda"><?php _e( 'Meeting Agenda', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <input type="text" name="agenda" class="regular-text" value="<?php echo ! empty( $meeting_info->agenda ) ? esc_attr( $meeting_info->agenda ) : null; ?>">
                    <p class="description" id="meetingTopic-description"><?php _e( 'Meeting Description.', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="userId"><?php _e( 'Meeting Host *', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <select name="userId" required class="zvc-hacking-select">
                        <option value=""><?php _e( 'Select a Host', 'video-conferencing-with-zoom-api' ); ?></option>
						<?php foreach ( $users as $user ): ?>
                            <option value="<?php echo esc_attr( $user->id ); ?>" <?php echo $meeting_info->host_id == $user->id ? 'selected' : null; ?>><?php echo $user->first_name . ' ( ' . $user->email . ' )'; ?></option>
						<?php endforeach; ?>
                    </select>
                    <p class="description" id="userId-description"><?php _e( 'This is host ID for the meeting (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr <?php echo $meeting_info->type === 3 ? 'style="display:none;"' : 'style="display:table-row;"'; ?>>
                <th scope="row"><label for="start_date"><?php _e( 'Start Date/Time *', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
					<?php
					$start_time = ! empty( $meeting_info->start_time ) ? $meeting_info->start_time : false;
					$timezone   = ! empty( $meeting_info->timezone ) ? $meeting_info->timezone : "America/Los_Angeles";
					$tz         = new DateTimeZone( $timezone );
					$date       = new DateTime( $start_time );
					$date->setTimezone( $tz );
					?>
                    <input type="text" name="start_date" id="datetimepicker" data-existingdate="<?php echo $date->format( 'Y-m-d H:i:s' ); ?>" required class="regular-text">
                    <p class="description" id="start_date-description"><?php _e( 'Starting Date and Time of the Meeting (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="timezone"><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
					<?php $tzlists = \Codemanas\VczApi\Helpers\Date::timezone_list(); ?>
                    <select id="timezone" name="timezone" class="zvc-hacking-select">
						<?php foreach ( $tzlists as $k => $tzlist ) { ?>
                            <option value="<?php echo esc_attr( $k ); ?>" <?php echo $meeting_info->timezone == $k ? 'selected' : null; ?>><?php echo $tzlist; ?></option>
						<?php } ?>
                    </select>
                    <p class="description" id="timezone-description"><?php _e( 'Meeting Timezone', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="duration"><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <input type="number" name="duration" class="regular-text" value="<?php echo ! empty( $meeting_info->duration ) && $meeting_info->duration ? esc_attr( $meeting_info->duration ) : 40; ?>">
                    <p class="description" id="duration-description"><?php _e( 'Meeting duration (minutes). (optional)', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="password"><?php _e( 'Meeting Password', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <input type="text" name="password" class="regular-text" maxlength="10" data-maxlength="9" value="<?php echo ! empty( $meeting_info->password ) ? esc_attr( $meeting_info->password ) : false; ?>">
                    <p class="description" id="email-description"><?php _e( 'Password to join the meeting. Password may only contain the following characters: [a-z A-Z 0-9]. Max of 10 characters.( Leave blank for auto generate )', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="join_before_host"><?php _e( 'Join Before Host', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <p class="description" id="join_before_host-description">
                        <input type="checkbox" <?php echo $option_jbh; ?> name="join_before_host" value="1" class="regular-text"><?php _e( 'Join meeting before host start the meeting. Only for scheduled or recurring meetings.', 'video-conferencing-with-zoom-api' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="option_host_video"><?php _e( 'Host join start', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <p class="description" id="option_host_video-description">
                        <input type="checkbox" <?php echo $option_host_video; ?> name="option_host_video" value="1" class="regular-text"><?php _e( 'Start video when host join meeting.', 'video-conferencing-with-zoom-api' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="option_participants_video"><?php _e( 'Participants Video', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <p class="description" id="option_participants_video-description">
                        <input type="checkbox" <?php echo $option_participants_video; ?> name="option_participants_video" value="1" class="regular-text"><?php _e( 'Start video when participants join meeting.', 'video-conferencing-with-zoom-api' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="option_mute_participants_upon_entry"><?php _e( 'Mute Participants upon entry', 'video-conferencing-with-zoom-api' ); ?></label>
                </th>
                <td>
                    <p class="description" id="option_mute_participants_upon_entry">
                        <input type="checkbox" <?php echo $option_mute_participants; ?> value="1" name="option_mute_participants" class="regular-text"><?php _e( 'Mutes Participants when entering the meeting.', 'video-conferencing-with-zoom-api' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="option_auto_recording"><?php _e( 'Auto Recording', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <select id="option_auto_recording" name="option_auto_recording">
                        <option value="none" <?php echo ! empty( $meeting_info->settings->auto_recording ) && $meeting_info->settings->auto_recording == "none" ? "selected" : false; ?>>
							<?php _e( 'No Recordings', 'video-conferencing-with-zoom-api' ); ?>
                        </option>
                        <option value="local" <?php echo ! empty( $meeting_info->settings->auto_recording ) && $meeting_info->settings->auto_recording == "local" ? "selected" : false; ?>>
							<?php _e( 'Local', 'video-conferencing-with-zoom-api' ); ?>
                        </option>
                        <option value="cloud" <?php echo ! empty( $meeting_info->settings->auto_recording ) && $meeting_info->settings->auto_recording == "cloud" ? "selected" : false; ?>>
							<?php _e( 'Cloud', 'video-conferencing-with-zoom-api' ); ?>
                        </option>
                    </select>
                    <p class="description" id="option_auto_recording_description"><?php _e( 'Set what type of auto recording feature you want to add. Default is none.', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="settings_alternative_hosts"><?php _e( 'Alternative Hosts', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <select name="alternative_host_ids[]" multiple class="zvc-hacking-select">
                        <option value=""><?php _e( 'Select a Host', 'video-conferencing-with-zoom-api' ); ?></option>
						<?php
						foreach ( $users as $user ):
							$user_found = false;
							if ( ! empty( $option_alternative_hosts ) && in_array( $user->email, $option_alternative_hosts ) ) {
								$user_found = true;
							}
							?>
                            <option value="<?php echo esc_attr( $user->id ); ?>" <?php echo $user_found ? 'selected' : null; ?>><?php echo $user->first_name . ' ( ' . $user->email . ' )'; ?></option>
						<?php endforeach; ?>
                    </select>
                    <p class="description" id="settings_alternative_hosts"><?php _e( 'Paid Zoom Account is required for this !! Alternative hosts IDs. Multiple value separated by comma.', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" name="update_meeting" class="button button-primary" value="<?php _e( 'Update Meeting', 'video-conferencing-with-zoom-api' ); ?>"></p>
    </form>
</div>