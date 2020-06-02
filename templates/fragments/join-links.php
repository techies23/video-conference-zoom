<?php
/**
 * The template for displaying meeting join and start links
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/fragments/join-links.php.
 *
 * @author Deepen.
 * @created_on 11/19/19
 */

global $zoom;

if ( ! empty( $zoom ) ) {
	?>
    <div class="dpn-zvc-sidebar-box">
        <div class="join-links">
			<?php
			/**
			 * Hook: vczoom_meeting_join_links
			 *
			 * @video_conference_zoom_meeting_join_link - 10
			 */
			do_action( 'vczoom_meeting_join_links', $zoom );
			?>

			<?php if ( ! empty( $zoom->start_url ) && vczapi_check_author( $post_id ) ) { ?>
                <a target="_blank" href="<?php echo esc_url( $zoom->start_url ); ?>" class="btn btn-start-link"><?php _e( 'Start Meeting', 'video-conferencing-with-zoom-api' ); ?></a>
			<?php } ?>
        </div>
    </div>
	<?php
}