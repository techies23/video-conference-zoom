<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Check if any transient by name is available
$users = video_conferencing_zoom_api_get_user_transients();
?>
<div class="wrap">
    <h1><?php _e( 'Edit Webinar', 'video-conferencing-with-zoom-api' ); ?></h1>
    <div class="message">
		<?php
		$message = self::get_message();
		if ( isset( $message ) && ! empty( $message ) ) {
			echo $message;
		}
		?>
    </div>
	<?php video_conferencing_zoom_api_show_api_notice(); ?>

    <a href="edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-webinars<?php echo isset( $_GET['host_id'] ) ? '&host_id=' . esc_html( $_GET['host_id'] ) : false; ?>"><?php _e( 'Back to selected host Webinars list', 'video-conferencing-with-zoom-api' ); ?></a>

    <form action="" method="POST" class="zvc-meetings-form">
		<?php wp_nonce_field( '_zoom_add_meeting_nonce_action', '_zoom_add_meeting_nonce' ); ?>
        <input type="hidden" name="webinar_id" value="<?php echo isset( $meeting_info ) ? $meeting_info->id : ''; ?>">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="meetingTopic"><?php _e( 'Webinar Topic *', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <input type="text" name="meetingTopic" size="100" value="<?php echo ! empty( $meeting_info->topic ) ? esc_html( $meeting_info->topic ) : false; ?>" required class="regular-text">
                    <p class="description" id="meetingTopic-description"><?php _e( 'Webinar topic. (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="meetingAgenda"><?php _e( 'Webinar Agenda', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <input type="text" name="agenda" value="<?php echo ! empty( $meeting_info->agenda ) ? esc_html( $meeting_info->agenda ) : false; ?>" class="regular-text">
                    <p class="description" id="meetingTopic-description"><?php _e( 'Webinar Description.', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr <?php echo $meeting_info->type === 6 || $meeting_info->type === 9 ? 'style="display:none;"' : 'style="display:table-row;"'; ?>>
                <th scope="row"><label for="start_date"><?php _e( 'Start Date/Time *', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
					<?php
					if ( ! empty( $meeting_info->start_time ) && ! empty( $meeting_info->timezone ) ) {
						$date = vczapi_dateConverter( $meeting_info->start_time, $meeting_info->timezone, 'Y-m-d H:i:s', false );
					} else {
						$date = false;
					}
					?>
                    <input type="text" name="start_date" id="datetimepicker" data-existingdate="<?php echo esc_html( $date ); ?>" value="<?php echo $date; ?>" required class="regular-text">
                    <p class="description" id="start_date-description"><?php _e( 'Starting Date and Time of the Webinar (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="timezone"><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
					<?php $tzlists = \Codemanas\VczApi\Helpers\Date::timezone_list(); ?>
                    <select id="timezone" name="timezone" class="zvc-hacking-select">
						<?php foreach ( $tzlists as $k => $tzlist ) { ?>
                            <option value="<?php echo $k; ?>" <?php echo $meeting_info->timezone == $k ? 'selected' : null; ?>><?php echo $tzlist; ?></option>
						<?php } ?>
                    </select>
                    <p class="description" id="timezone-description"><?php _e( 'Webinar Timezone', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="duration"><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <input type="number" name="duration" class="regular-text" value="<?php echo ! empty( $meeting_info->duration ) && $meeting_info->duration ? $meeting_info->duration : 40; ?>">
                    <p class="description" id="duration-description"><?php _e( 'Webinar duration (minutes). (optional)', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="password"><?php _e( 'Webinar Password', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <input type="text" name="password" class="regular-text" maxlength="10" data-maxlength="9" value="<?php echo ! empty( $meeting_info->password ) ? $meeting_info->password : false; ?>">
                    <p class="description" id="email-description"><?php _e( 'Password to join the meeting. Password may only contain the following characters: [a-z A-Z 0-9]. Max of 10 characters.( Leave blank for auto generate )', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="option_host_video"><?php _e( 'Host Video', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <p class="description" id="option_host_video-description">
                        <input type="checkbox" <?php echo ! empty( $meeting_info->settings->host_video ) ? 'checked' : false; ?> name="option_host_video" value="1" class="regular-text"><?php _e( 'Start video when host join meeting.', 'video-conferencing-with-zoom-api' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="option_panelist_video"><?php _e( 'Panelists Video', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <p class="description" id="option_panelist_video-description">
                        <input type="checkbox" <?php echo ! empty( $meeting_info->settings->panelists_video ) ? 'checked' : false; ?> name="option_panelist_video" value="1" class="regular-text"><?php _e( 'Start video when panelists join meeting.', 'video-conferencing-with-zoom-api' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="option_hd_video"><?php _e( 'HD Video', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <p class="description" id="option_hd_video-description">
                        <input type="checkbox" <?php echo ! empty( $meeting_info->settings->hd_video ) ? 'checked' : false; ?> name="option_hd_video" value="1" class="regular-text"><?php _e( 'Defaults to HD video.', 'video-conferencing-with-zoom-api' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="option_auto_recording"><?php _e( 'Auto Recording', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <select id="option_auto_recording" name="option_auto_recording">
                        <option <?php echo ! empty( $meeting_info->settings->auto_recording ) ? 'selected' : false; ?> value="none"><?php _e( 'No Recordings', 'video-conferencing-with-zoom-api' ); ?>
                        </option>
                        <option <?php echo ! empty( $meeting_info->settings->auto_recording ) ? 'selected' : false; ?> value="local"><?php _e( 'Local', 'video-conferencing-with-zoom-api' ); ?></option>
                        <option <?php echo ! empty( $meeting_info->settings->auto_recording ) ? 'selected' : false; ?> value="cloud"><?php _e( 'Cloud', 'video-conferencing-with-zoom-api' ); ?></option>
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
						$option_alternative_hosts = $meeting_info->settings->alternative_hosts ? $meeting_info->settings->alternative_hosts : false;
						if ( ! empty( $option_alternative_hosts ) ) {
							$option_alternative_hosts = explode( ', ', $option_alternative_hosts );
						}
						foreach ( $users as $user ):
							$user_found = false;
							if ( ! empty( $option_alternative_hosts ) && in_array( $user->email, $option_alternative_hosts ) ) {
								$user_found = true;
							}
							?>
                            <option value="<?php echo $user->id; ?>" <?php echo $user_found ? 'selected' : null; ?>><?php echo $user->first_name . ' ( ' . $user->email . ' )'; ?></option>
						<?php endforeach; ?>
                    </select>
                    <p class="description" id="settings_alternative_hosts"><?php _e( 'Paid Zoom Account is required for this !! Alternative hosts IDs. Multiple value separated by comma.', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" name="create_meeting" class="button button-primary" value="<?php _e( 'Edit Webinar', 'video-conferencing-with-zoom-api' ); ?>"></p>
    </form>
</div>