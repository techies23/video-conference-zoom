<?php
/**
 * The Template for displaying list of recordings via Host ID
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/zoom-recordings.php.
 *
 * @package    Video Conferencing with Zoom API/Templates
 * @version     3.5.0
 */

use Codemanas\VczApi\Helpers\Date;
use Codemanas\VczApi\Shortcodes\Support\ZoomResponse;

global $zoom_recordings;

$recordings   = ZoomResponse::to_array( $zoom_recordings );
$args         = ! empty( $args ) ? $args : [];
$downloadable = 'yes' === ( $args['downloadable'] ?? 'no' );
?>
    <div class="vczapi-recordings-range-selector-wrap">
        <form action="" class="vczapi-recording-range-selector" method="GET">
            <label><?php _e( 'Select a month to filter:', 'video-conferencing-with-zoom-api' ); ?></label>
            <input type="text" name="date" id="vczapi-check-recording-date" class="vczapi-check-recording-date" value="<?php echo isset( $_GET['date'] ) ? esc_html( sanitize_text_field( wp_unslash( $_GET['date'] ) ) ) : date( 'F Y' ); ?>"/> <input type="submit" name="fetch_recordings" value="<?php esc_attr_e( 'Check', 'video-conferencing-with-zoom-api' ); ?>">
        </form>
    </div>
    <table id="vczapi-recordings-list-table" class="vczapi-recordings-list-table responsive nowrap">
        <thead>
        <tr>
            <th><?php _e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?></th>
            <th><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
            <th><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></th>
            <th><?php _e( 'Recorded', 'video-conferencing-with-zoom-api' ); ?></th>
            <th><?php _e( 'Size', 'video-conferencing-with-zoom-api' ); ?></th>
            <th><?php _e( 'Action', 'video-conferencing-with-zoom-api' ); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php
		$use_meeting_id = apply_filters( 'vczapi_zoom_recordings_shortcodeby_meeting_id', false );

		foreach ( (array) ( $recordings['meetings'] ?? [] ) as $recording ) {
			$recording = (array) $recording;

			$recording_uuid = $use_meeting_id
				? ( $recording['id'] ?? '' )
				: urlencode( (string) ( $recording['uuid'] ?? '' ) );
			?>
            <tr>
                <td><?php echo esc_html( $recording['id'] ?? '' ); ?></td>
                <td><?php echo esc_html( $recording['topic'] ?? '' ); ?></td>
                <td><?php echo esc_html( $recording['duration'] ?? '' ); ?></td>
                <td data-sort="<?php echo esc_attr( (string) strtotime( $recording['start_time'] ?? '' ) ); ?>"><?php echo esc_html( Date::dateConverter( $recording['start_time'] ?? '', $recording['timezone'] ?? '' ) ); ?></td>
                <td><?php echo esc_html( vczapi_filesize_converter( $recording['total_size'] ?? 0 ) ); ?></td>
                <td>
                    <a href="javascript:void(0);" class="vczapi-view-recording"
                       data-recording-id="<?php echo esc_attr( $recording_uuid ); ?>"
                       data-downloadable="<?php echo $downloadable ? 1 : 0; ?>"
                    >
                        <?php _e( 'View Recordings', 'video-conferencing-with-zoom-api' ); ?></a>
                    <div class="vczapi-modal"></div>
                </td>
            </tr>
			<?php
		}
		?>
        </tbody>
    </table>

<?php
if ( ! empty( $recordings ) ) {
	vczapi_zoom_api_paginator( $recordings, 'recordings' );
}
?>