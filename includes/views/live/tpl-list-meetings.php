<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Check if any transient by name is available
$users = video_conferencing_zoom_api_get_user_transients();

if ( isset( $_GET['host_id'] ) ) {
	$encoded_meetings = zoom_conference()->listMeetings( $_GET['host_id'] );
	$decoded_meetings = json_decode( $encoded_meetings );
	$meetings         = ! empty( $decoded_meetings->meetings ) ? $decoded_meetings->meetings : array();
	$meeting_states   = get_option( 'zoom_api_meeting_options' );
}
?>
<div id="zvc-cover" style="display: none;"></div>
<div class="wrap">
    <h2><?php _e( "Meetings", "video-conferencing-with-zoom-api" ); ?></h2>
    <!--For Errors while deleteing this user-->
    <div id="message" style="display:none;" class="notice notice-error show_on_meeting_delete_error"><p></p></div>

	<?php
	video_conferencing_zoom_api_show_like_popup();
	video_conferencing_zoom_api_show_api_notice();
	?>

	<?php if ( ! empty( $error ) ) { ?>
        <div id="message" class="notice notice-error"><p><?php echo $error; ?></p></div>
	<?php } else {
		$get_host_id = isset( $_GET['host_id'] ) ? esc_attr( $_GET['host_id'] ) : null;
		?>
        <div class="select_zvc_user_listings_wrapp">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e( "Select bulk action", "video-conferencing-with-zoom-api" ); ?></label>
                <select name="action" id="bulk-action-selector-top">
                    <option value="trash"><?php _e( "Move to Trash", "video-conferencing-with-zoom-api" ); ?></option>
                </select> <input type="submit" id="bulk_delete_meeting_listings" data-type="meeting" class="button action" value="<?php _e( 'Apply', 'video-conferencing-with-zoom-api' ); ?>">
                <a href="?post_type=zoom-meetings&page=zoom-video-conferencing-add-meeting&host_id=<?php echo esc_html( $get_host_id ); ?>" class="button action" title="Add new meeting"><?php _e( 'Add New Meeting', 'video-conferencing-with-zoom-api' ); ?></a>
            </div>
            <div class="alignright">
                <select onchange="location = this.value;" class="zvc-hacking-select">
                    <option value="?post_type=zoom-meetings&page=zoom-video-conferencing"><?php _e( 'Select a User', 'video-conferencing-with-zoom-api' ); ?></option>
					<?php foreach ( $users as $user ) { ?>
                        <option
                                value="?post_type=zoom-meetings&page=zoom-video-conferencing&host_id=<?php echo $user->id; ?>"
							<?php selected( $get_host_id, $user->id ); ?> >
							<?php echo $user->first_name . ' ( ' . $user->email . ' )'; ?>
                        </option>
					<?php } ?>
                </select>
            </div>
            <div class="clear"></div>
        </div>

        <div class="zvc_listing_table">
            <table id="zvc_meetings_list_table" class="display" width="100%">
                <thead>
                <tr>
                    <th class="zvc-text-center"><input type="checkbox" id="checkall"/></th>
                    <th class="zvc-text-left"><?php _e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th class="zvc-text-left"><?php _e( 'Shortcode', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th class="zvc-text-left"><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th class="zvc-text-left"><?php _e( 'Status', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th class="zvc-text-left" class="zvc-text-left"><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th class="zvc-text-left"><?php _e( 'Meeting State', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th class="zvc-text-left"><?php _e( 'Created On', 'video-conferencing-with-zoom-api' ); ?></th>
                </tr>
                </thead>
                <tbody>
				<?php
				if ( ! empty( $meetings ) ) {
					foreach ( $meetings as $meeting ) {
						?>
                        <tr>
                            <td class="zvc-text-center">
                                <input type="checkbox" name="meeting_id_check[]" class="checkthis" value="<?php echo esc_attr( $meeting->id ); ?>"/></td>
                            <td><?php echo $meeting->id; ?></td>
                            <td>
                                <input class="text" id="meeting-shortcode-<?php echo $meeting->id; ?>" type="text" readonly value='[zoom_api_link meeting_id="<?php echo $meeting->id; ?>" link_only="no"]' onclick="this.select(); document.execCommand('copy'); alert('Copied to clipboard');"/>
                                <p class="description"><?php _e( 'Click to Copy Shortcode !', 'video-conferencing-with-zoom-api' ); ?></p>
                            </td>
                            <td>
                                <a href="edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-add-meeting&edit=<?php echo $meeting->id; ?>&host_id=<?php echo $meeting->host_id; ?>"><?php echo $meeting->topic; ?></a>
								<?php
								$zoom_host_url             = 'https://zoom.us' . '/wc/' . $meeting->id . '/start';
								$zoom_host_url             = apply_filters( 'video_conferencing_zoom_join_url_host', $zoom_host_url );
								$start_meeting_via_browser = '<a class="start-meeting-btn reload-meeting-started-button" target="_blank" href="' . esc_url( $zoom_host_url ) . '" class="join-link">' . __( 'Start via Browser', 'video-conferencing-with-zoom-api' ) . '</a>';
								?>
                                <div class="row-actionss">
                                    <span class="trash"><a style="color:red;" href="javascript:void(0);" data-meetingid="<?php echo $meeting->id; ?>" data-type="meeting" class="submitdelete delete-meeting"><?php _e( 'Trash', 'video-conferencing-with-zoom-api' ); ?></a> | </span>
                                    <span class="view"><a href="<?php echo ! empty( $meeting->start_url ) ? $meeting->start_url : $meeting->join_url; ?>" rel="permalink" target="_blank"><?php _e( 'Start via App', 'video-conferencing-with-zoom-api' ); ?></a></span>
                                    <span class="view"> | <?php echo $start_meeting_via_browser; ?></span>
                                </div>
                            </td>
                            <td><?php
								if ( ! empty( $meeting->status ) ) {
									switch ( $meeting->status ) {
										case 0;
											echo '<img src="' . ZVC_PLUGIN_IMAGES_PATH . '/2.png" style="width:14px;" title="Not Started" alt="Not Started">';
											break;
										case 1;
											echo '<img src="' . ZVC_PLUGIN_IMAGES_PATH . '/3.png" style="width:14px;" title="Completed" alt="Completed">';
											break;
										case 2;
											echo '<img src="' . ZVC_PLUGIN_IMAGES_PATH . '/1.png" style="width:14px;" title="Currently Live" alt="Live">';
											break;
										default;
											break;
									}
								} else {
									echo "N/A";
								}
								?>
                            </td>
                            <td>
								<?php
								if ( $meeting->type === 2 ) {
									echo vczapi_dateConverter( $meeting->start_time, $meeting->timezone, 'F j, Y, g:i a ( e )' );
								} else if ( $meeting->type === 3 ) {
									_e( 'This is a recurring meeting with no fixed time.', 'video-conferencing-with-zoom-api' );
								} else if ( $meeting->type === 8 ) {
									_e( 'Recurring Meeting', 'video-conferencing-with-zoom-api' );
								} else {
									echo "N/A";
								}
								?>
                            </td>
                            <td style="width: 120px;">
								<?php if ( ! isset( $meeting_states[ $meeting->id ]['state'] ) ) { ?>
                                    <a href="javascript:void(0);" class="vczapi-meeting-state-change" data-type="shortcode" data-state="end" data-id="<?php echo $meeting->id ?>"><?php _e( 'Disable Join', 'video-conferencing-with-zoom-api' ); ?></a>
                                    <div class="vczapi-admin-info-tooltip">
                                        <span class="dashicons dashicons-info"></span>
                                        <span class="vczapi-admin-info-tooltip--text"><?php _e( 'Ending this will disable users to join this meeting. Applies to any shortcode output only.', 'video-conferencing-with-zoom-api' ); ?></span>
                                    </div>
								<?php } else { ?>
                                    <a href="javascript:void(0);" class="vczapi-meeting-state-change" data-type="shortcode" data-state="resume" data-id="<?php echo $meeting->id ?>"><?php _e( 'Enable Join', 'video-conferencing-with-zoom-api' ); ?></a>
                                    <div class="vczapi-admin-info-tooltip">
                                        <span class="dashicons dashicons-info "></span>
                                        <span class="vczapi-admin-info-tooltip--text"><?php _e( 'Resuming this will enable users to join this meeting. Applies to any shortcode output only.', 'video-conferencing-with-zoom-api' ); ?></span>
                                    </div>
								<?php } ?>
                            </td>
                            <td><?php echo date( 'F j, Y, g:i a', strtotime( $meeting->created_at ) ); ?></td>
                        </tr>
						<?php
					}
				} ?>
                </tbody>
            </table>
        </div>
	<?php } ?>
</div>
