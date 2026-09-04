<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="zoom-metabox-wrapper">
	<?php
	if ( ! empty( $meeting_details ) ) {
		if ( ! empty( $meeting_details->code ) && ! empty( $meeting_details->message ) ) {
			?>
            <p>
                <strong><?php _e( 'Meeting has not been created for this post yet. Publish your meeting or hit update to create a new one for this post !', 'video-conferencing-with-zoom-api' ) ?></strong>
            </p>
			<?php
			echo '<p style="color:red;font-size:18px;"><strong>Zoom Error:</strong> ' . $meeting_details->message . '</p>';
		} else {
			$zoom_host_url = 'https://zoom.us' . '/wc/' . $meeting_details->id . '/start';
			$zoom_host_url = apply_filters( 'video_conferencing_zoom_join_url_host', $zoom_host_url );

			$join_url = ! empty( $meeting_details->encrypted_password ) ? vczapi_get_pwd_embedded_join_link( $meeting_details->join_url, $meeting_details->encrypted_password ) : $meeting_details->join_url;
			?>
            <div class="zoom-metabox-content">
                <p><a target="_blank" href="<?php echo esc_url( $meeting_details->start_url ); ?>"
                      title="Start URL"><?php _e( 'Start Meeting', 'video-conferencing-with-zoom-api' ) ?></a>
                </p>
                <p><a target="_blank" href="<?php echo esc_url( $join_url ); ?>"
                      title="Start URL"><?php _e( 'Join Meeting', 'video-conferencing-with-zoom-api' ) ?></a>
                </p>
                <p><a target="_blank" href="<?php echo esc_url( $zoom_host_url ); ?>"
                      title="Start URL"><?php _e( 'Start via Browser', 'video-conferencing-with-zoom-api' ) ?></a>
                </p>
                <p><strong><?php _e( 'Meeting ID', 'video-conferencing-with-zoom-api' ) ?>
                        :</strong> <?php echo $meeting_details->id; ?></p>
				<?php do_action( 'vczapi_meeting_details_admin', $meeting_details ); ?>
            </div>
            <hr>
			<?php
		}
	} else { ?>
        <p>
            <strong><?php _e( 'Meeting has not been created for this post yet. Publish your meeting or hit update to create a new one for this post !', 'video-conferencing-with-zoom-api' ); ?></strong>
        </p>
	<?php } ?>
    <div class="zoom-metabox-content">
        <p><?php _e( 'Requires Login?', 'video-conferencing-with-zoom-api' ); ?>
            <input type="checkbox" name="option_logged_in"
                   value="1" <?php ! empty( $meeting_fields['site_option_logged_in'] ) ? checked( '1', $meeting_fields['site_option_logged_in'] ) : false; ?>
                   class="regular-text">
        </p>
        <p class="description"><?php _e( 'Only logged in users of this site will be able to join this meeting.', 'video-conferencing-with-zoom-api' ); ?></p>
        <p><?php _e( 'Hide Join via browser link ?', 'video-conferencing-with-zoom-api' ); ?>
            <input type="checkbox" name="option_browser_join"
                   value="1" <?php ! empty( $meeting_fields['site_option_browser_join'] ) ? checked( '1', $meeting_fields['site_option_browser_join'] ) : false; ?>
                   class="regular-text">
        </p>
        <p class="description"><?php _e( 'This will disable join via browser link in frontend page.', 'video-conferencing-with-zoom-api' ); ?></p>
    </div>
</div>