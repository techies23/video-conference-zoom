<?php
/**
 * @author     Deepen.
 * @created_on 11/19/19
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<table class="form-table">
    <tbody>
	<?php
	global $post;
	$meeting_details = get_post_meta( $post->ID, '_meeting_zoom_details', true );
	$meeting_fields  = ! empty( $meeting_fields ) ? $meeting_fields : array();
	if ( empty( $meeting_fields['meeting_type'] ) ) {
		$meeting_fields['meeting_type'] = 1;
	}

	if ( $post->post_status == 'publish' && is_object( $meeting_details ) && isset( $meeting_details->id ) ) {
		?>
        <tr>
            <th scope="row"><label for="meeting-shortcode">Shortcode</label></th>
            <td>
                <input class="text regular-text" id="meeting-shortcode" type="text" readonly value='[zoom_meeting_post post_id="<?php echo $post->ID; ?>" template="boxed"]' onclick="this.select(); document.execCommand('copy'); alert('Copied to clipboard');"/>
                <p class="description">
					<?php _e( 'If you need to show this meeting on another page or post please use this shortcode', 'video-conferencing-with-zoom-api' ); ?>
                </p>
            </td>
        </tr>
		<?php
	}

	if ( ! empty( $meeting_details ) && ! empty( $meeting_details->id ) && ( $post->post_status === 'publish' || $post->post_status === 'draft' || $post->post_status === 'pending' || $post->post_status == 'private' ) ) { ?>
        <tr class="zoom-host-id-selection-admin">
            <th scope="row"><label for="userId"><?php _e( 'Meeting Host *', 'video-conferencing-with-zoom-api' ); ?></label></th>
            <td>
				<?php
				if ( ! empty( $meeting_details->host_id ) ) {
					$user = json_decode( zoom_conference()->getUserInfo( $meeting_details->host_id ) );
					if ( ! empty( $user ) ) {
						if ( ! empty( $user->code ) ) {
							echo $user->message;
						} else {
							echo '<input type="hidden" name="userId" value="' . $user->id . '">';
							echo esc_html( $user->first_name ) . ' ( ' . esc_html( $user->email ) . ' )';
						}
					} else {
						_e( 'Please check your internet connection or API connection.', 'video-conferencing-with-zoom-api' );
					}
				} else {
					printf( __( 'Did not find any hosts here ? Please %scheck here%s to verify your API keys are working correctly.', 'video-conferencing-with-zoom-api' ), '<a href="' . admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' ) . '">', '</a>' );
				} ?>
                <p class="description" id="userId-description"><?php _e( 'This is host ID for the meeting (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="meeting_type"><?php _e( 'Meeting Type', 'video-conferencing-with-zoom-api' ); ?></label></th>
            <td>
                <p><?php echo ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 2 ? __( 'Zoom Webinar', 'video-conferencing-with-zoom-api' ) : __( 'Zoom Meeting', 'video-conferencing-with-zoom-api' ); ?></p>
                <p class="description"><?php _e( 'You cannot update meeting type. This is not allowed to avoid any conflict issues.', 'video-conferencing-with-zoom-api' ); ?></p>
                <input type="hidden" name="meeting_type" value="<?php esc_attr_e( $meeting_fields['meeting_type'] ); ?>">
            </td>
        </tr>
		<?php
	} else {
		$host_id = get_user_meta( get_current_user_id(), 'user_zoom_hostid', true );
		if ( ! empty( $host_id ) ) {
			?>
            <tr class="zoom-host-id-selection-admin">
            <th scope="row"><label for="userId"><?php _e( 'Meeting Host *', 'video-conferencing-with-zoom-api' ); ?></label></th>
            <td>
				<?php
				$user = json_decode( zoom_conference()->getUserInfo( $host_id ) );
				if ( ! empty( $user ) ) {
					if ( ! empty( $user->code ) ) {
						echo $user->message;
					} else {
						echo '<input type="hidden" name="userId" value="' . $user->id . '">';
						echo esc_html( $user->first_name ) . ' ( ' . esc_html( $user->email ) . ' )';
					}
				} else {
					_e( 'Please check your internet connection or API connection.', 'video-conferencing-with-zoom-api' );
				}
				?>
                <p class="description" id="userId-description"><?php _e( 'This is host ID for the meeting (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
            </td>
			<?php
		} else {
			?>
            <tr class="zoom-host-id-selection-admin">
                <th scope="row"><label for="userId"><?php _e( 'Meeting Host *', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
					<?php
					if ( ! empty( $users ) ) {
						$count = count( $users );
						if ( $count == 1 ) {
							?>
                            <input type="hidden" name="userId" value="<?php echo $users[0]->id; ?>">
                            <span><?php echo esc_html( $users[0]->first_name ) . ' ( ' . esc_html( $users[0]->email ) . ' )'; ?></span>
						<?php } else { ?>
                            <select name="userId" required class="zvc-hacking-select vczapi-admin-post-type-host-selector" style="width:50%;">
                                <option value=""><?php _e( 'Select a Host', 'video-conferencing-with-zoom-api' ); ?></option>
								<?php foreach ( $users as $user ) { ?>
                                    <option value="<?php echo $user->id; ?>" <?php ! empty( $meeting_fields['userId'] ) ? selected( $meeting_fields['userId'], $user->id ) : ''; ?> ><?php echo esc_html( $user->first_name ) . ' ( ' . esc_html( $user->email ) . ' )'; ?></option>
								<?php } ?>
                            </select>
                            <p class="vczapi-manually-hostid-wrap"><a href="javascript:void(0);" class="vczapi-admin-hostID-manually-add"><?php _e( 'User not in the list? Click here to manually enter Host.', 'video-conferencing-with-zoom-api' ); ?></a></p>
						<?php } ?>
                        <p class="description" id="userId-description"><?php _e( 'This is host ID for the meeting (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
					<?php } else {
						printf( __( 'Did not find any hosts here ? Please %scheck here%s to verify your API keys are working correctly.', 'video-conferencing-with-zoom-api' ), '<a href="' . admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' ) . '">', '</a>' );
					} ?>
                </td>
            </tr>
		<?php } ?>
        <tr class="zoom-meeting-type-selection-admin">
            <th scope="row"><label for="meeting_type"><?php _e( 'Meeting Type', 'video-conferencing-with-zoom-api' ); ?></label></th>
            <td>
                <select id="vczapi-admin-meeting-ype" name="meeting_type" class="meeting-type-selection">
                    <option value="1" <?php ! empty( $meeting_fields['meeting_type'] ) ? selected( esc_attr( absint( $meeting_fields['meeting_type'] ) ), 1 ) : false; ?>>Meeting</option>
                    <option value="2" <?php ! empty( $meeting_fields['meeting_type'] ) ? selected( esc_attr( absint( $meeting_fields['meeting_type'] ) ), 2 ) : false; ?>>Webinar</option>
                </select>
                <p class="description" id="userId-description"><?php _e( 'Which type of meeting do you want to create.', 'video-conferencing-with-zoom-api' ); ?></p>
                <p class="description" id="userId-description"><?php _e( 'Note: Webinar requires Zoom Webinar Plan enabled in your account.', 'video-conferencing-with-zoom-api' ); ?>?</p>

            </td>
        </tr>
		<?php
	}
	?>
    <tr>
        <th scope="row"><label for="start_date"><?php _e( 'Start Date/Time *', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <input type="text" name="start_date" id="datetimepicker" data-existingdate="<?php echo ! empty( $meeting_fields['start_date'] ) ? esc_attr( $meeting_fields['start_date'] ) : false; ?>" required class="regular-text" value="<?php echo ! empty( $meeting_fields['start_date'] ) ? esc_attr( $meeting_fields['start_date'] ) : false; ?>">
            <p class="description" id="start_date-description"><?php _e( 'Starting Date and Time of the Meeting (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
        </td>
    </tr>

	<?php do_action( 'vczapi_admin_before_additional_fields' ); ?>

    <tr>
        <th scope="row"><label for="timezone"><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
			<?php
			$tzlists     = \Codemanas\VczApi\Helpers\Date::timezone_list();
			$wp_timezone = \Codemanas\VczApi\Helpers\Date::get_timezone_offset();
			?>
            <select id="timezone" name="timezone" class="zvc-hacking-select">
				<?php foreach ( $tzlists as $k => $tzlist ) {
					$option_tz_selected = false;
					if ( ! empty( $meeting_fields['timezone'] ) ) {
						$option_tz_selected = selected( $k, $meeting_fields['timezone'], false );
					} else if ( ! empty( $wp_timezone ) && ! empty( $tzlists[ $wp_timezone ] ) && $tzlists[ $wp_timezone ] !== false ) {
						$option_tz_selected = selected( $k, $wp_timezone, false );
					}
					?>
                    <option value="<?php echo $k; ?>" <?php echo $option_tz_selected; ?>><?php echo esc_html( $tzlist ); ?></option>
				<?php } ?>
            </select>
            <p class="description" id="timezone-description"><?php _e( 'Meeting Timezone', 'video-conferencing-with-zoom-api' ); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="duration"><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
			<?php $duration = ! empty( $meeting_fields['duration'] ) ? vczapi_convertMinutesToHM( $meeting_fields['duration'], false ) : vczapi_convertMinutesToHM( 40, false ); ?>
            <label>
                <select class="option_duration_hour" name="option_duration_hour">
					<?php for ( $hr = 0; $hr <= 24; $hr ++ ) { ?>
                        <option value="<?php echo $hr; ?>" <?php echo $duration['hr'] == $hr ? 'selected' : ''; ?>><?php echo $hr; ?></option>
					<?php } ?>
                </select> hr
            </label>
            <label style="margin-left:10px;">
                <select class="option_duration_minutes" name="option_duration_minutes">
                    <option value="0" <?php echo $duration['min'] == 0 ? 'selected' : ''; ?>>0</option>
                    <option value="15" <?php echo $duration['min'] == 15 ? 'selected' : ''; ?>>15</option>
                    <option value="30" <?php echo $duration['min'] == 30 ? 'selected' : ''; ?>>30</option>
                    <option value="45" <?php echo $duration['min'] == 40 ? 'selected' : ''; ?>>40</option>
                </select> min
            </label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="password"><?php _e( 'Password', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td class="zvc-meetings-form">
            <input type="text" name="password" maxlength="10" data-maxlength="10" class="regular-text" value="<?php echo ! empty( $meeting_details->password ) ? esc_attr( $meeting_details->password ) : false; ?>">
            <p class="description" id="email-description"><?php _e( 'Password to join the meeting. Password may only contain the following characters: [a-z A-Z 0-9]. Max of 10 characters.( Leave blank for auto generate )', 'video-conferencing-with-zoom-api' ); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="disable-waiting-room"><?php _e( 'Disable Waiting Room', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <p class="description" id="disable-waiting-room">
                <input type="checkbox" id="disable-waiting-room" name="disable-waiting-room" value="yes" <?php ! empty( $meeting_fields['disable_waiting_room'] ) ? checked( 'yes', $meeting_fields['disable_waiting_room'] ) : false; ?> class="regular-text">
				<?php _e( 'Waiting Room is enabled by default - if you want users to skip the waiting room and join the meeting directly - enable this option.' ); ?>
                <span style="color:red"><?php _e( 'Please keep in mind anyone with the meeting link will be able to join without you allowing them into the meeting.', 'video-conferencing-with-zoom-api' ) ?></span>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="meeting-authentication"><?php _e( 'Meeting Authentication', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <p class="description" id="meeting-authentication">
                <input type="checkbox" name="meeting_authentication" value="1" <?php ! empty( $meeting_fields['meeting_authentication'] ) ? checked( '1', $meeting_fields['meeting_authentication'] ) : false; ?> class="regular-text"><?php _e( 'Only loggedin users in Zoom App can join this Meeting.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-hide-on-webinar" <?php echo ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 2 ? 'style="display: none;"' : false; ?>>
        <th scope="row"><label for="join_before_host"><?php _e( 'Join Before Host', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <p class="description" id="join_before_host-description">
                <input type="checkbox" name="join_before_host" value="1" <?php ! empty( $meeting_fields['join_before_host'] ) ? checked( '1', $meeting_fields['join_before_host'] ) : false; ?> class="regular-text"><?php _e( 'Allow users to join meetin before host start/joins the meeting. Only for scheduled or recurring meetings. If the waiting room is enabled, this setting will not work.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="option_host_video"><?php _e( 'Start When Host Joins', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <p class="description" id="option_host_video-description">
                <input type="checkbox" name="option_host_video" value="1" <?php ! empty( $meeting_fields['option_host_video'] ) ? checked( '1', $meeting_fields['option_host_video'] ) : false; ?> class="regular-text"><?php _e( 'Start video when host join meeting.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-hide-on-webinar" <?php echo ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 2 ? 'style="display: none;"' : false; ?>>
        <th scope="row"><label for="option_participants_video"><?php _e( 'Participants Video', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <p class="description" id="option_participants_video-description">
                <input type="checkbox" name="option_participants_video" <?php ! empty( $meeting_fields['option_participants_video'] ) ? checked( '1', $meeting_fields['option_participants_video'] ) : false; ?> value="1" class="regular-text"><?php _e( 'Start video when participants join meeting.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-hide-on-webinar" <?php echo ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 2 ? 'style="display: none;"' : false; ?>>
        <th scope="row">
            <label for="option_mute_participants_upon_entry"><?php _e( 'Mute Participants upon entry', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description" id="option_mute_participants_upon_entry">
                <input type="checkbox" name="option_mute_participants" value="1" <?php ! empty( $meeting_fields['option_mute_participants'] ) ? checked( '1', $meeting_fields['option_mute_participants'] ) : false; ?> class="regular-text"><?php _e( 'Mutes Participants when entering the meeting.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-show-on-webinar" <?php echo ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 1 ? 'style="display: none;"' : false; ?>>
        <th scope="row">
            <label for="panelists_video"><?php _e( 'When Panelists Join', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description">
                <input type="checkbox" name="panelists_video" value="1" <?php ! empty( $meeting_fields['panelists_video'] ) ? checked( '1', $meeting_fields['panelists_video'] ) : false; ?> class="regular-text"><?php _e( 'Start video when panelists join webinar.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-show-on-webinar" <?php echo ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 1 ? 'style="display: none;"' : false; ?>>
        <th scope="row">
            <label for="practice_session"><?php _e( 'Practise Session', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description">
                <input type="checkbox" name="practice_session" value="1" <?php ! empty( $meeting_fields['practice_session'] ) ? checked( '1', $meeting_fields['practice_session'] ) : false; ?> class="regular-text"><?php _e( 'Enable Practise Session.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-show-on-webinar" <?php echo ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 1 ? 'style="display: none;"' : false; ?>>
        <th scope="row">
            <label for="hd_video"><?php _e( 'HD Video', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description">
                <input type="checkbox" name="hd_video" value="1" <?php ! empty( $meeting_fields['hd_video'] ) ? checked( '1', $meeting_fields['hd_video'] ) : false; ?> class="regular-text"><?php _e( 'Defaults to HD video.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-show-on-webinar" <?php echo ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 1 ? 'style="display: none;"' : false; ?>>
        <th scope="row">
            <label for="allow_multiple_devices"><?php _e( 'Allow Multiple Devices', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description">
                <input type="checkbox" name="allow_multiple_devices" value="1" <?php ! empty( $meeting_fields['allow_multiple_devices'] ) ? checked( '1', $meeting_fields['allow_multiple_devices'] ) : false; ?> class="regular-text"><?php _e( 'Allow attendess to join from multiple devices.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="option_auto_recording"><?php _e( 'Auto Recording', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
            <select id="option_auto_recording" name="option_auto_recording">
                <option value="none" <?php ! empty( $meeting_fields['option_auto_recording'] ) ? selected( 'none', $meeting_fields['option_auto_recording'] ) : false; ?>>
					<?php _e( 'No Recordings', 'video-conferencing-with-zoom-api' ); ?>
                </option>
                <option value="local" <?php ! empty( $meeting_fields['option_auto_recording'] ) ? selected( 'local', $meeting_fields['option_auto_recording'] ) : false; ?>>
					<?php _e( 'Local', 'video-conferencing-with-zoom-api' ); ?>
                </option>
                <option value="cloud" <?php ! empty( $meeting_fields['option_auto_recording'] ) ? selected( 'cloud', $meeting_fields['option_auto_recording'] ) : false; ?>>
					<?php _e( 'Cloud', 'video-conferencing-with-zoom-api' ); ?>
                </option>
            </select>
            <p class="description" id="option_auto_recording_description"><?php _e( 'Set what type of auto recording feature you want to add. Default is none.', 'video-conferencing-with-zoom-api' ); ?></p>
        </td>
    </tr>
	<?php
	$show_host = apply_filters( 'vczapi_admin_show_alternative_host_selection', true );
	if ( $show_host ) {
		?>
        <tr>
            <th scope="row"><label for="settings_alternative_hosts"><?php _e( 'Alternative Hosts', 'video-conferencing-with-zoom-api' ); ?></label>
            </th>
            <td>
				<?php if ( ! empty( $users ) ) { ?>
                    <select name="alternative_host_ids[]" multiple class="zvc-hacking-select" style="width: 50%;">
                        <option value=""><?php _e( 'Select a Host', 'video-conferencing-with-zoom-api' ); ?></option>
						<?php foreach ( $users as $user ): ?>
                            <option value="<?php echo $user->id; ?>" <?php echo ! empty( $meeting_fields['alternative_host_ids'] ) && in_array( $user->id, $meeting_fields['alternative_host_ids'] ) ? 'selected' : false; ?>><?php echo esc_html( $user->first_name ) . ' ( ' . esc_html( $user->email ) . ' )'; ?></option>
						<?php endforeach; ?>
                    </select>
				<?php } else {
					printf( __( 'Did not find any hosts here ? Please %scheck here%s to verify your API keys are working correctly.', 'video-conferencing-with-zoom-api' ), '<a href="' . admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' ) . '">', '</a>' );
				} ?>
                <p class="description" id="settings_alternative_hosts"><?php _e( 'Paid Zoom Account is required for this !! Alternative hosts IDs. Multiple value separated by comma.', 'video-conferencing-with-zoom-api' ); ?></p>
            </td>
        </tr>
	<?php } ?>
    </tbody>
</table>