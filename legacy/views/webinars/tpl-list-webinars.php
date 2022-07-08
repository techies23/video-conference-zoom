<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$get_host_id = isset( $_GET['host_id'] ) ? $_GET['host_id'] : null;
?>
<div id="zvc-cover" style="display: none;"></div>
<div class="wrap">
    <h2><?php _e( "Webinars", "video-conferencing-with-zoom-api" ); ?></h2>
	<?php video_conferencing_zoom_api_show_like_popup(); ?>
    <div class="message">
		<?php
		$message = self::get_message();
		if ( isset( $message ) && ! empty( $message ) ) {
			echo $message;
		}
		?>
    </div>

    <div class="select_zvc_user_listings_wrapp">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e( "Select bulk action", "video-conferencing-with-zoom-api" ); ?></label>
            <select name="action" id="bulk-action-selector-top">
                <option value="trash"><?php _e( "Move to Trash", "video-conferencing-with-zoom-api" ); ?></option>
            </select>
            <input type="submit" id="bulk_delete_meeting_listings" data-type="webinar" class="button action" value="<?php _e( "Apply", "video-conferencing-with-zoom-api" ); ?>">
            <a href="<?php echo esc_url( add_query_arg( array(
				'post_type' => 'zoom-meetings',
				'new'       => 'zoom-video-conferencing-webinars-add'
			) ) ); ?>" class="button action" title="Add new meeting"><?php _e( 'Add New Webinar', 'video-conferencing-with-zoom-api' ); ?></a>
        </div>
        <div class="alignright">
            <select onchange="location = this.value;" class="zvc-hacking-select">
                <option value="<?php echo esc_url( add_query_arg( array(
					'post_type' => 'zoom-meetings',
					'page'      => 'zoom-video-conferencing-webinars',
				) ) ); ?>"><?php _e( 'Select a User', 'video-conferencing-with-zoom-api' ); ?></option>
				<?php foreach ( $users as $user ) { ?>
                    <option value="<?php echo esc_url( add_query_arg( array(
						'post_type' => 'zoom-meetings',
						'page'      => 'zoom-video-conferencing-webinars',
						'host_id'   => $user->id
					) ) ); ?>"
						<?php selected( $get_host_id, $user->id ); ?>
                    >
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
                <th class="zvc-text-left"><?php _e( 'Webinar ID', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'Shortcode', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'Status', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left" class="zvc-text-left"><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'Created On', 'video-conferencing-with-zoom-api' ); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php
			if ( ! empty( $webinars ) ) {
				foreach ( $webinars as $webinar ) {
					?>
                    <tr>
                        <td class="zvc-text-center">
                            <input type="checkbox" name="meeting_id_check[]" class="checkthis" value="<?php echo $webinar->id; ?>"/>
                        </td>
                        <td><?php echo $webinar->id; ?></td>
                        <td>
                            <input class="text" id="meeting-shortcode-<?php echo $webinar->id; ?>" type="text" readonly value='[zoom_api_webinar webinar_id="<?php echo $webinar->id; ?>" link_only="no"]' onclick="this.select(); document.execCommand('copy'); alert('Copied to clipboard');"/>
                            <p class="description"><?php _e( 'Click to Copy Shortcode !', 'video-conferencing-with-zoom-api' ); ?></p>
                        </td>
                        <td>
                            <a href="edit.php?post_type=zoom-meetings&new=zoom-video-conferencing-webinars-add&page=zoom-video-conferencing-webinars&edit=<?php echo $webinar->id; ?>&host_id=<?php echo $webinar->host_id; ?>"><?php echo $webinar->topic; ?></a>
							<?php
							$zoom_host_url             = 'https://zoom.us' . '/wc/' . $webinar->id . '/start';
							$zoom_host_url             = apply_filters( 'video_conferencing_zoom_join_url_host', $zoom_host_url );
							$start_meeting_via_browser = '<a class="start-meeting-btn reload-meeting-started-button" target="_blank" href="' . esc_url( $zoom_host_url ) . '" class="join-link">' . __( 'Start via Browser', 'video-conferencing-with-zoom-api' ) . '</a>';
							?>
                            <div class="row-actionss">
                                <span class="trash"><a style="color:red;" href="javascript:void(0);" data-meetingid="<?php echo $webinar->id; ?>" data-type="webinar" class="submitdelete delete-meeting"><?php _e( 'Trash', 'video-conferencing-with-zoom-api' ); ?></a> | </span>
                                <span class="view"><a href="<?php echo ! empty( $webinar->start_url ) ? $webinar->start_url : $webinar->join_url; ?>" rel="permalink" target="_blank"><?php _e( 'Start via App', 'video-conferencing-with-zoom-api' ); ?></a></span>
                                <span class="view"> | <?php echo $start_meeting_via_browser; ?></span>
                            </div>
                        </td>
                        <td><?php
							if ( ! empty( $webinar->status ) ) {
								switch ( $webinar->status ) {
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
							if ( $webinar->type === 5 ) {
								echo vczapi_dateConverter( $webinar->start_time, $webinar->timezone, 'F j, Y, g:i a ( e )' );
							} else if ( $webinar->type === 6 ) {
								_e( 'This is a recurring meeting with no fixed time.', 'video-conferencing-with-zoom-api' );
							} else if ( $webinar->type === 9 ) {
								_e( 'Recurring Webinar', 'video-conferencing-with-zoom-api' );
							} else {
								echo "N/A";
							}
							?>
                        </td>
                        <td><?php echo date( 'F j, Y, g:i a', strtotime( $webinar->created_at ) ); ?></td>
                    </tr>
					<?php
				}
			} ?>
            </tbody>
        </table>
    </div>
</div>
