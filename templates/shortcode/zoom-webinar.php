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

use Codemanas\VczApi\Helpers\Date;
use Codemanas\VczApi\Helpers\MeetingType;
use Codemanas\VczApi\Shortcodes\Support\ZoomResponse;

global $zoom_webinars;

$webinar = ZoomResponse::to_array( $zoom_webinars );
?>
<div class="dpn-zvc-shortcode-op-wrapper">
	<?php
	$hide_join_link_nloggedusers = get_option( 'zoom_api_hide_shortcode_join_links' );
	?>
    <table class="vczapi-shortcode-meeting-table">
        <tr class="vczapi-shortcode-meeting-table--row1">
            <td><?php _e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?></td>
            <td><?php echo esc_html( $webinar['id'] ?? '' ); ?></td>
        </tr>
        <tr class="vczapi-shortcode-meeting-table--row2">
            <td><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></td>
            <td><?php echo esc_html( $webinar['topic'] ?? '' ); ?></td>
        </tr>
		<?php
		if ( ! empty( $webinar['type'] ) && MeetingType::is_recurring_fixed_time_webinar( $webinar['type'] ) ) {
			if ( ! empty( $webinar['occurrences'] ) ) {
				?>
                <tr class="vczapi-shortcode-meeting-table--row4">
                    <td><?php _e( 'Type', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php _e( 'Recurring Meeting', 'video-conferencing-with-zoom-api' ); ?></td>
                </tr>
                <tr class="vczapi-shortcode-meeting-table--row4">
                    <td><?php _e( 'Occurrences', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php echo count( (array) $webinar['occurrences'] ); ?></td>
                </tr>
                <tr class="vczapi-shortcode-meeting-table--row5">
                    <td><?php _e( 'Next Start Time', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td>
						<?php
						$timezone            = $webinar['timezone'] ?? 'UTC';
						$now                = new DateTime( 'now -1 hour', new DateTimeZone( $timezone ) );
						$closest_occurrence = false;

						foreach ( (array) $webinar['occurrences'] as $occurrence ) {
							$status      = $occurrence['status'] ?? '';
							$start_time  = $occurrence['start_time'] ?? '';
							$start_date  = $start_time ? new DateTime( $start_time, new DateTimeZone( $timezone ) ) : null;

							if ( 'available' !== $status || ! $start_date || $start_date < $now ) {
								continue;
							}

							$closest_occurrence = $start_time;
							break;
						}

						if ( $closest_occurrence ) {
							echo esc_html( Date::dateConverter( $closest_occurrence, $timezone, 'F j, Y @ g:i a' ) );
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
		} elseif ( ! empty( $webinar['type'] ) && MeetingType::is_recurring_no_fixed_time_webinar( $webinar['type'] ) ) {
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
                <td><?php echo esc_html( Date::dateConverter( $webinar['start_time'] ?? '', $webinar['timezone'] ?? 'UTC', 'F j, Y @ g:i a' ) ); ?></td>
            </tr>
		<?php } ?>
        <tr class="vczapi-shortcode-meeting-table--row7">
            <td><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></td>
            <td><?php echo esc_html( $webinar['timezone'] ?? '' ); ?></td>
        </tr>
		<?php if ( ! empty( $webinar['duration'] ) ) { ?>
            <tr class="zvc-table-shortcode-duration">
                <td><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></td>
                <td><?php echo esc_html( $webinar['duration'] ); ?></td>
            </tr>
			<?php
		}

		if ( ! empty( $hide_join_link_nloggedusers ) ) {
			$show_join_links = is_user_logged_in();
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
			do_action( 'vczoom_meeting_shortcode_join_links_webinar', $webinar );
		}
		?>
    </table>
</div>