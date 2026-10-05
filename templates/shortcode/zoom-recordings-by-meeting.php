<?php
/**
 * The Template for displaying list of recordings via meeting ID
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/zoom-recordings-by-meeting.php.
 *
 * @package     Video Conferencing with Zoom API/Templates
 * @version     3.5.0
 */

use Codemanas\VczApi\Helpers\Date;

$recordings   = ! empty( $args['recordings'] ) ? (array) $args['recordings'] : [];
$show_passcode = 'yes' === ( $args['passcode'] ?? 'no' );
$downloadable = 'yes' === ( $args['downloadable'] ?? 'no' );

if ( empty( $recordings ) ) {
	return;
}

$first = (array) reset( $recordings );
?>
    <div class="vczapi-recordings-meeting-id-description">
        <ul>
            <li><strong><?php _e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo esc_html( $first['id'] ?? '' ); ?></li>
            <li><strong><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo esc_html( $first['topic'] ?? '' ); ?></li>
        </ul>
    </div>
    <table class="responsive vczapi-recordings-by-meeting-id-table">
        <thead>
        <tr>
            <th><?php _e( 'Recording Date', 'video-conferencing-with-zoom-api' ); ?></th>
            <th><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></th>
			<?php if ( $show_passcode ) { ?>
                <th><?php _e( 'Passcode', 'video-conferencing-with-zoom-api' ); ?></th>
			<?php } ?>
            <th><?php _e( 'Size', 'video-conferencing-with-zoom-api' ); ?></th>
            <th><?php _e( 'Action', 'video-conferencing-with-zoom-api' ); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php
		foreach ( $recordings as $zoom_recording ) {
			$zoom_recording = (array) $zoom_recording;
			?>
                <tr>
                    <td data-sort="<?php echo esc_attr( (string) strtotime( $zoom_recording['start_time'] ?? '' ) ); ?>"><?php echo esc_html( Date::dateConverter( $zoom_recording['start_time'] ?? '', $zoom_recording['timezone'] ?? '' ) ); ?></td>
                    <td><?php echo esc_html( $zoom_recording['duration'] ?? '' ); ?></td>
					<?php if ( $show_passcode ) { ?>
                        <td><?php echo esc_html( $zoom_recording['password'] ?? '' ); ?></td>
					<?php } ?>
                    <td><?php echo esc_html( vczapi_filesize_converter( $zoom_recording['total_size'] ?? 0 ) ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( $zoom_recording['share_url'] ?? '' ); ?>" target="_blank" rel="noopener noreferrer"><?php _e( 'Play', 'video-conferencing-with-zoom-api' ); ?></a> &nbsp;/&nbsp;
                        <a href="javascript:void(0);" class="vczapi-view-recording" data-recording-id="<?php echo esc_attr( $zoom_recording['uuid'] ?? '' ); ?>" data-downloadable="<?php echo $downloadable ? 1 : 0; ?>">
		                    <?php _e( 'View Recordings', 'video-conferencing-with-zoom-api' ); ?></a>
                        <div class="vczapi-modal"></div>
                    </td>
                </tr>
			<?php
		}
		?>
        </tbody>
    </table>