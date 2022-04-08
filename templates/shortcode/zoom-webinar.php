<?php
/**
 * The template for displaying shortcode
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/zoom-shortcode.php.
 *
 * @author Deepen.
 * @since 3.0.0
 * @version 3.0.0
 */

global $zoom_webinars;
?>

<div class="dpn-zvc-shortcode-op-wrapper">
	<?php
	$hide_join_link_nloggedusers = get_option( 'zoom_api_hide_shortcode_join_links' );
	?>
    <table class="vczapi-shortcode-meeting-table">
        <tr class="vczapi-shortcode-meeting-table--row1">
            <td><?php _e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?></td>
            <td><?php echo $zoom_webinars->id; ?></td>
        </tr>
        <tr class="vczapi-shortcode-meeting-table--row2">
            <td><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></td>
            <td><?php echo $zoom_webinars->topic; ?></td>
        </tr>
		<?php
		if ( ! empty( $zoom_webinars->type ) && $zoom_webinars->type === 9 ) {
			if ( ! empty( $zoom_webinars->occurrences ) ) {
				?>
                <tr class="vczapi-shortcode-meeting-table--row4">
                    <td><?php _e( 'Type', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php _e( 'Recurring Meeting', 'video-conferencing-with-zoom-api' ); ?></td>
                </tr>
                <tr class="vczapi-shortcode-meeting-table--row4">
                    <td><?php _e( 'Ocurrences', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php echo count( $zoom_webinars->occurrences ); ?></td>
                </tr>
                <tr class="vczapi-shortcode-meeting-table--row5">
                    <td><?php _e( 'Next Start Time', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td>
						<?php
						$now               = new DateTime( 'now -1 hour', new DateTimeZone( $zoom_webinars->timezone ) );
						$closest_occurence = false;
						if ( ! empty( $zoom_webinars->type ) && $zoom_webinars->type === 9 && ! empty( $zoom_webinars->occurrences ) ) {
							foreach ( $zoom_webinars->occurrences as $occurrence ) {
								if ( $occurrence->status === "available" ) {
									$start_date = new DateTime( $occurrence->start_time, new DateTimeZone( $zoom_webinars->timezone ) );
									if ( $start_date >= $now ) {
										$closest_occurence = $occurrence->start_time;
										break;
									}

									_e( 'Meeting has ended !', 'video-conferencing-with-zoom-api' );
									break;
								}
							}
						}

						if ( $closest_occurence ) {
							echo vczapi_dateConverter( $closest_occurence, $zoom_webinars->timezone, 'F j, Y @ g:i a' );
						} else {
							_e( 'Meeting has ended !', 'video-conferencing-with-zoom-api' );
						}
						?>
                    </td>
                </tr>
				<?php
			} else {
				?>
                <tr class="vczapi-shortcode-meeting-table--row6">
                    <td><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php _e( 'Meeting has ended !', 'video-conferencing-with-zoom-api' ); ?></td>
                </tr>
				<?php
			}
		} else if ( ! empty( $zoom_webinars->type ) && $zoom_webinars->type === 6 ) {
			?>
            <tr class="vczapi-shortcode-meeting-table--row6">
                <td><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></td>
                <td><?php _e( 'This is a meeting with no Fixed Time.', 'video-conferencing-with-zoom-api' ); ?></td>
            </tr>
			<?php
		} else {
			?>
            <tr class="vczapi-shortcode-meeting-table--row6">
                <td><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></td>
                <td><?php echo vczapi_dateConverter( $zoom_webinars->start_time, $zoom_webinars->timezone, 'F j, Y @ g:i a' ); ?></td>
            </tr>
		<?php } ?>
        <tr class="vczapi-shortcode-meeting-table--row7">
            <td><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></td>
            <td><?php echo $zoom_webinars->timezone; ?></td>
        </tr>
		<?php if ( ! empty( $zoom_webinars->duration ) ) { ?>
            <tr class="zvc-table-shortcode-duration">
                <td><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></td>
                <td><?php echo $zoom_webinars->duration; ?></td>
            </tr>
			<?php
		}

		if ( ! empty( $hide_join_link_nloggedusers ) ) {
			if ( is_user_logged_in() ) {
				$show_join_links = true;
			} else {
				$show_join_links = false;
			}
		} else {
			$show_join_links = true;
		}

		if ( $show_join_links ) {
			/**
			 * Hook: vczoom_meeting_shortcode_join_links_webinar
			 *
			 * @video_conference_zoom_shortcode_join_link_webinar - 10
			 *
			 */
			do_action( 'vczoom_meeting_shortcode_join_links_webinar', $zoom_webinars );
		}
		?>
    </table>
</div>
