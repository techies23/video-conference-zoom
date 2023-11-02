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
                <input class="text regular-text" id="meeting-shortcode" type="text" readonly
                       value='[zoom_meeting_post post_id="<?php echo $post->ID; ?>" template="boxed"]'
                       onclick="this.select(); document.execCommand('copy'); alert('Copied to clipboard');"/>
                <p class="description">
					<?php _e( 'If you need to show this meeting on another page or post please use this shortcode', 'video-conferencing-with-zoom-api' ); ?>
                </p>
            </td>
        </tr>
		<?php
	}
	if ( ! empty( $meeting_details ) && ! empty( $meeting_details->id ) && ( $post->post_status === 'publish' || $post->post_status === 'draft' || $post->post_status === 'pending' || $post->post_status == 'private' ) ) {
		?>
        <tr>
            <th scope="row"><label
                        for="meeting_type"><?php _e( 'Meeting Type', 'video-conferencing-with-zoom-api' ); ?></label></th>
            <td>
                <p><?php echo ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 2 ? __( 'Zoom Webinar', 'video-conferencing-with-zoom-api' ) : __( 'Zoom Meeting', 'video-conferencing-with-zoom-api' ); ?></p>
                <input type="hidden" name="meeting_type" value="<?php esc_attr_e( $meeting_fields['meeting_type'] ); ?>">
            </td>
        </tr>
		<?php
	} else {
		$host_id = get_user_meta( get_current_user_id(), 'user_zoom_hostid', true );
		if ( ! empty( $host_id ) ) {
			?>
            <tr class="zoom-host-id-selection-admin">
            <th scope="row"><label
                        for="userId"><?php _e( 'Meeting Host *', 'video-conferencing-with-zoom-api' ); ?></label></th>
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
                <p class="description"
                   id="userId-description"><?php _e( 'This is host ID for the meeting (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
            </td>
			<?php
		} else {
			?>
            <tr class="zoom-host-id-selection-admin">
                <th scope="row"><label
                            for="userId"><?php _e( 'Meeting Host *', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
					<?php
					if ( ! empty( $users ) ) {
						$count = count( $users );
						if ( $count == 1 ) {
							?>
                            <input type="hidden" name="userId" value="<?php echo $users[0]->id; ?>">
                            <span><?php echo esc_html( $users[0]->first_name ) . ' ( ' . esc_html( $users[0]->email ) . ' )'; ?></span>
						<?php } else { ?>
                            <select name="userId" required
                                    class="zvc-hacking-select vczapi-admin-post-type-host-selector" style="width:50%;">
                                <option value=""><?php _e( 'Select a Host', 'video-conferencing-with-zoom-api' ); ?></option>
								<?php foreach ( $users as $user ) { ?>
                                    <option value="<?php echo $user->id; ?>" <?php ! empty( $meeting_fields['userId'] ) ? selected( $meeting_fields['userId'], $user->id ) : ''; ?> ><?php echo esc_html( $user->first_name ) . ' ( ' . esc_html( $user->email ) . ' )'; ?></option>
								<?php } ?>
                            </select>
                            <p class="vczapi-manually-hostid-wrap"><a href="javascript:void(0);"
                                                                      class="vczapi-admin-hostID-manually-add"><?php _e( 'User not in the list? Click here to manually enter Host.', 'video-conferencing-with-zoom-api' ); ?></a>
                            </p>
						<?php } ?>
                        <p class="description"
                           id="userId-description"><?php _e( 'This is host ID for the meeting (Required).', 'video-conferencing-with-zoom-api' ); ?></p>
					<?php } else {
						printf( __( 'Did not find any hosts here ? Please %scheck here%s to verify your API keys are working correctly.', 'video-conferencing-with-zoom-api' ), '<a href="' . admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' ) . '">', '</a>' );
					} ?>
                </td>
            </tr>
		<?php } ?>
        <tr class="zoom-meeting-type-selection-admin">
            <th scope="row"><label
                        for="meeting_type"><?php _e( 'Meeting Type', 'video-conferencing-with-zoom-api' ); ?></label></th>
            <td>
                <select id="vczapi-admin-meeting-ype" name="meeting_type" class="meeting-type-selection">
                    <option value="1" <?php ! empty( $meeting_fields['meeting_type'] ) ? selected( esc_attr( absint( $meeting_fields['meeting_type'] ) ), 1 ) : false; ?>>
                        Meeting
                    </option>
                    <option value="2" <?php ! empty( $meeting_fields['meeting_type'] ) ? selected( esc_attr( absint( $meeting_fields['meeting_type'] ) ), 2 ) : false; ?>>
                        Webinar
                    </option>
                </select>
                <p class="description"
                   id="userId-description"><?php _e( 'Which type of meeting do you want to create. Note: Webinar requires Zoom Webinar Plan enabled in your account.', 'video-conferencing-with-zoom-api' ); ?>
                    ?</p>
            </td>
        </tr>
		<?php
	}
	?>
    <tr>
        <th scope="row"><label
                    for="start_date"><?php _e( 'Start Date/Time *', 'video-conferencing-with-zoom-api' ); ?></label></th>

        <td>
			<?php if ( ! empty( $meeting_fields['start_date'] ) ) : ?>
                <span><?php echo esc_attr( \Codemanas\VczApi\Helpers\Date::dateConverter( $meeting_fields['start_date'], $meeting_fields['timezone'] ) ); ?></span>
			<?php endif; ?>
        </td>
    </tr>

    </tr>

	<?php do_action( 'vczapi_admin_before_additional_fields' ); ?>

    <tr>
        <th scope="row"><label for="timezone"><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
			<?php
			$tzlists     = \Codemanas\VczApi\Helpers\Date::timezone_list();
			$wp_timezone = \Codemanas\VczApi\Helpers\Date::get_timezone_offset();
			?>
			<?php if ( ! empty( $meeting_fields['timezone'] ) ): ?>
                <span><?php echo esc_html( $tzlists[ $meeting_fields['timezone'] ] ); ?></span>
			<?php elseif ( ! empty( $wp_timezone ) && ! empty( $tzlists[ $wp_timezone ] ) && $tzlists[ $wp_timezone ] !== false ): ?>
                <span><?php echo esc_html( $tzlists[ $wp_timezone ] ); ?></span>
			<?php endif; ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="duration"><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td>
			<?php
			$duration = ! empty( $meeting_fields['duration'] ) ? vczapi_convertMinutesToHM( $meeting_fields['duration'], false ) : vczapi_convertMinutesToHM( 40, false );
			if ( $duration['hr'] != 0 ) {
				echo $duration['hr'] . ' hour ' . $duration['min'] . ' minutes';
			} else {
				echo $duration['min'] . ' minutes';
			};
			?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="password"><?php _e( 'Password', 'video-conferencing-with-zoom-api' ); ?></label></th>
        <td class="zvc-meetings-form">
			<?php if ( ! empty( $meeting_details->password ) ): ?>
                <span><?php echo esc_attr( $meeting_details->password ); ?></span>
			<?php endif; ?>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="disable-waiting-room"><?php _e( 'Disable Waiting Room', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description" id="disable-waiting-room">
				<?php
				$disable_waiting_room = ! empty( $meeting_fields['disable_waiting_room'] ) ? $meeting_fields['disable_waiting_room'] : '';
				if ( $disable_waiting_room === 'yes' ) {
					echo __( 'Enabled', 'video-conferencing-with-zoom-api' );
				} else {
					echo __( 'Disabled', 'video-conferencing-with-zoom-api' );
				}
				?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row"><label
                    for="meeting-authentication"><?php _e( 'Meeting Authentication', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description" id="meeting-authentication">
				<?php
				$meeting_authentication = ! empty( $meeting_fields['meeting_authentication'] ) ? $meeting_fields['meeting_authentication'] : '';
				if ( $meeting_authentication === '1' ) {
					echo __( 'Enabled', 'video-conferencing-with-zoom-api' );
				} else {
					echo __( 'Disabled', 'video-conferencing-with-zoom-api' );
				}
				?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-hide-on-webinar" <?php echo ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 2 ? 'style="display: none;"' : false; ?>>
        <th scope="row"><label
                    for="join_before_host"><?php _e( 'Join Before Host', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description" id="join_before_host-description">
				<?php
				$join_before_host = ! empty( $meeting_fields['join_before_host'] ) ? $meeting_fields['join_before_host'] : '';
				if ( $join_before_host === '1' ) {
					echo __( 'Enabled', 'video-conferencing-with-zoom-api' );
				} else {
					echo __( 'Disabled', 'video-conferencing-with-zoom-api' );
				}
				?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row"><label
                    for="option_host_video"><?php _e( 'Start When Host Joins', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description" id="option_host_video-description">
				<?php
				$option_host_video = ! empty( $meeting_fields['option_host_video'] ) ? $meeting_fields['option_host_video'] : '';
				if ( $option_host_video === '1' ) {
					echo __( 'Enabled', 'video-conferencing-with-zoom-api' );
				} else {
					echo __( 'Disabled', 'video-conferencing-with-zoom-api' );
				}
				?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-hide-on-webinar" <?php echo ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 2 ? 'style="display: none;"' : false; ?>>
        <th scope="row"><label
                    for="option_participants_video"><?php _e( 'Participants Video', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description" id="option_participants_video-description">
				<?php
				$option_participants_video = ! empty( $meeting_fields['option_participants_video'] ) ? $meeting_fields['option_participants_video'] : '';
				if ( $option_participants_video === '1' ) {
					echo __( 'Enabled', 'video-conferencing-with-zoom-api' );
				} else {
					echo __( 'Disabled', 'video-conferencing-with-zoom-api' );
				}
				?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-hide-on-webinar" <?php echo ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 2 ? 'style="display: none;"' : false; ?>>
        <th scope="row">
            <label for="option_mute_participants_upon_entry"><?php _e( 'Mute Participants upon entry', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description" id="option_mute_participants_upon_entry">
				<?php
				$option_mute_participants = ! empty( $meeting_fields['option_mute_participants'] ) ? $meeting_fields['option_mute_participants'] : '';
				if ( $option_mute_participants === '1' ) {
					echo __( 'Enabled', 'video-conferencing-with-zoom-api' );
				} else {
					echo __( 'Disabled', 'video-conferencing-with-zoom-api' );
				}
				?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-show-on-webinar" <?php echo !empty($meeting_fields['meeting_type']) && $meeting_fields['meeting_type'] === 1 ? 'style="display: none;"' : false; ?>>
        <th scope="row">
            <label for="panelists_video"><?php _e('When Panelists Join', 'video-conferencing-with-zoom-api'); ?></label>
        </th>
        <td>
            <p class="description">
				<?php
				$panelists_video = !empty($meeting_fields['panelists_video']) ? $meeting_fields['panelists_video'] : '';
				if ($panelists_video === '1') {
					echo __('Enabled', 'video-conferencing-with-zoom-api');
				} else {
					echo __('Disabled', 'video-conferencing-with-zoom-api');
				}
				?>
            </p>
        </td>
    </tr>

    <tr class="vczapi-admin-show-on-webinar" <?php echo !empty($meeting_fields['meeting_type']) && $meeting_fields['meeting_type'] === 1 ? 'style="display: none;"' : false; ?>>
        <th scope="row">
            <label for="practice_session"><?php _e('Practice Session', 'video-conferencing-with-zoom-api'); ?></label>
        </th>
        <td>
            <p class="description">
				<?php
				$practice_session = !empty($meeting_fields['practice_session']) ? $meeting_fields['practice_session'] : '';
				if ($practice_session === '1') {
					echo __('Enabled', 'video-conferencing-with-zoom-api');
				} else {
					echo __('Disabled', 'video-conferencing-with-zoom-api');
				}
				?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-show-on-webinar" <?php echo !empty($meeting_fields['meeting_type']) && $meeting_fields['meeting_type'] === 1 ? 'style="display: none;"' : false; ?>>
        <th scope="row">
            <label for="hd_video"><?php _e('HD Video', 'video-conferencing-with-zoom-api'); ?></label>
        </th>
        <td>
            <p class="description">
				<?php
				$hd_video = !empty($meeting_fields['hd_video']) ? $meeting_fields['hd_video'] : '';
				if ($hd_video === '1') {
					echo __('Enabled', 'video-conferencing-with-zoom-api');
				} else {
					echo __('Disabled', 'video-conferencing-with-zoom-api');
				}
				?>
            </p>
        </td>
    </tr>
    <tr class="vczapi-admin-show-on-webinar" <?php echo ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 1 ? 'style="display: none;"' : false; ?>>
        <th scope="row">
            <label for="allow_multiple_devices"><?php _e( 'Allow Multiple Devices', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
            <p class="description">
                <input type="checkbox" name="allow_multiple_devices"
                       value="1" <?php ! empty( $meeting_fields['allow_multiple_devices'] ) ? checked( '1', $meeting_fields['allow_multiple_devices'] ) : false; ?>
                       class="regular-text"><?php _e( 'Allow attendess to join from multiple devices.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row"><label
                    for="option_auto_recording"><?php _e( 'Auto Recording', 'video-conferencing-with-zoom-api' ); ?></label>
        </th>
        <td>
			<?php
			$option_auto_recording = ! empty( $meeting_fields['option_auto_recording'] ) ? $meeting_fields['option_auto_recording'] : 'none';
			switch ( $option_auto_recording ) {
				case 'none':
					echo __( 'No Recordings', 'video-conferencing-with-zoom-api' );
					break;
				case 'local':
					echo __( 'Local', 'video-conferencing-with-zoom-api' );
					break;
				case 'cloud':
					echo __( 'Cloud', 'video-conferencing-with-zoom-api' );
					break;
			}
			?>
        </td>
    </tr>
    </tbody>
</table>