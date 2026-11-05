<?php
/**
 * The Template for displaying list of recordings via Host ID
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/zoom-recordings.php.
 *
 * @package    Video Conferencing with Zoom API/Templates
 * @version     3.5.0
 */

global $zoom_recordings;
$args         = $args ?? [];
$post_id      = ! empty( $args['post_id'] ) ? absint( $args['post_id'] ) : 0;
$downloadable = ! empty( $args['downloadable'] ) && 'yes' === $args['downloadable'] ? 'yes' : 'no';
?>
    <div class="vczapi-recordings-range-selector-wrap">
        <form action="" class="vczapi-recording-range-selector" method="GET">
            <label><?php _e( 'Select a month to filter:', 'video-conferencing-with-zoom-api' ); ?></label>
            <input type="text" name="date" id="vczapi-check-recording-date" class="vczapi-check-recording-date" value="<?php echo isset( $_GET['date'] ) ? esc_html( $_GET['date'] ) : date( 'F Y' ); ?>"/> <input type="submit" name="fetch_recordings" value="<?php _e( 'Check', 'video-conferencing-with-zoom-api' ); ?>">
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
		foreach ( $zoom_recordings->meetings as $recording ) {
			if ( $use_meeting_id ) {
				$recording_uuid = $recording->id;
			} else {
				$recording_uuid = $recording->uuid;
			}

			$recording_token = \Codemanas\VczApi\Helpers\RecordingToken::issue(
				'recording',
				$recording_uuid,
				$post_id,
				[ 'downloadable' => $downloadable ]
			);
			?>
            <tr>
                <td><?php echo $recording->id; ?></td>
                <td><?php echo $recording->topic; ?></td>
                <td><?php echo $recording->duration; ?></td>
                <td data-sort="<?php echo strtotime( $recording->start_time ); ?>"><?php echo \Codemanas\VczApi\Helpers\Date::dateConverter( $recording->start_time, $recording->timezone ); ?></td>
                <td><?php echo vczapi_filesize_converter( $recording->total_size ); ?></td>
                <td>
                    <a href="javascript:void(0);" class="vczapi-view-recording"
                       data-recording-id="<?php echo esc_attr( $recording_uuid ); ?>"
                       data-token="<?php echo esc_attr( $recording_token ); ?>"
                       data-downloadable="<?php echo 'yes' === $downloadable ? 1 : 0; ?>"
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
if ( ! empty( $zoom_recordings ) ) {
	vczapi_zoom_api_paginator( $zoom_recordings, 'recordings' );
}
?>