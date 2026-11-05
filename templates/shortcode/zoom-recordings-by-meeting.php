<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * The Template for displaying list of recordings via meeting ID
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/zoom-recordings-by-meeting.php.
 *
 * @package     Video Conferencing with Zoom API/Templates
 * @version     3.5.0
 */

$recordings  = ! empty( $args['recordings'] ) ? $args['recordings'] : false;
$passcode    = ! empty( $args['passcode'] ) && $args['passcode'] == "yes";
$downlodable = ! empty( $args['downloadable'] ) && $args['downloadable'] == "yes";
$post_id     = ! empty( $args['post_id'] ) ? absint( $args['post_id'] ) : 0;
if ( $recordings ) {
	?>
    <div class="vczapi-recordings-meeting-id-description">
        <ul>
            <li><strong><?php esc_html_e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo esc_html( $recordings[0]->id ); ?></li>
            <li><strong><?php esc_html_e( 'Topic', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo esc_html( $recordings[0]->topic ); ?></li>
        </ul>
    </div>
    <table class="responsive vczapi-recordings-by-meeting-id-table">
        <thead>
        <tr>
            <th><?php esc_html_e( 'Recording Date', 'video-conferencing-with-zoom-api' ); ?></th>
            <th><?php esc_html_e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></th>
			<?php if ( $passcode ) { ?>
                <th><?php esc_html_e( 'Passcode', 'video-conferencing-with-zoom-api' ); ?></th>
			<?php } ?>
            <th><?php esc_html_e( 'Size', 'video-conferencing-with-zoom-api' ); ?></th>
            <th><?php esc_html_e( 'Action', 'video-conferencing-with-zoom-api' ); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php
		if ( ! empty( $recordings ) ) {
			foreach ( $recordings as $zoom_recording ) {
				$recording_uuid  = ! empty( $zoom_recording->uuid ) ? $zoom_recording->uuid : $zoom_recording->id;
				$recording_token = \Codemanas\VczApi\Helpers\RecordingToken::issue(
					'recording',
					$recording_uuid,
					$post_id,
					[ 'downloadable' => $downlodable ? 'yes' : 'no' ]
				);
				?>
                <tr>
                    <td data-sort="<?php echo esc_attr( strtotime( $zoom_recording->start_time ) ); ?>"><?php echo esc_html( \Codemanas\VczApi\Helpers\Date::dateConverter( $zoom_recording->start_time, $zoom_recording->timezone ) ); ?></td>
                    <td><?php echo esc_html( $zoom_recording->duration ); ?></td>
					<?php if ( $passcode ) { ?>
                        <td><?php echo esc_html( $zoom_recording->password ); ?></td>
					<?php } ?>
                    <td><?php echo esc_html( vczapi_filesize_converter( $zoom_recording->total_size ) ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( $zoom_recording->share_url ); ?>" target="_blank"><?php esc_html_e( 'Play', 'video-conferencing-with-zoom-api' ); ?></a> &nbsp;/&nbsp;
                        <a href="javascript:void(0);" class="vczapi-view-recording" data-recording-id="<?php echo esc_attr( $recording_uuid ); ?>" data-token="<?php echo esc_attr( $recording_token ); ?>" data-downloadable="<?php echo $downlodable ? 1 : 0; ?>">
		                    <?php esc_html_e( 'View Recordings', 'video-conferencing-with-zoom-api' ); ?></a>
                        <div class="vczapi-modal"></div>
                    </td>
                </tr>
				<?php
			}
		}
		?>
        </tbody>
    </table>
	<?php
}
