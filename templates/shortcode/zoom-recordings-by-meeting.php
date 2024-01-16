<?php
/**
 * The Template for displaying list of recordings via meeting ID
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/zoom-recordings-by-meeting.php.
 *
 * @package     Video Conferencing with Zoom API/Templates
 * @version     3.5.0
 */

$recordings = ! empty( $args['recordings'] ) ? $args['recordings'] : false;
if ( $recordings ) {
	?>
    <div class="vczapi-recordings-meeting-id-description">
        <ul>
            <li><strong><?php _e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo $recordings[0]->id; ?></li>
            <li><strong><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo $recordings[0]->topic; ?></li>
            <li>
                <a href="<?php echo esc_url( add_query_arg( [ 'flush_cache' => 'yes' ], get_the_permalink() ) ); ?>"><?php _e( 'Check for latest' ); ?></a>
            </li>
        </ul>
    </div>
    <table class="responsive vczapi-recordings-by-meeting-id-table">
        <thead>
        <tr>
            <th><?php _e( 'Recording Date', 'video-conferencing-with-zoom-api' ); ?></th>
            <th><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></th>
            <th><?php _e( 'Size', 'video-conferencing-with-zoom-api' ); ?></th>
            <th><?php _e( 'Action', 'video-conferencing-with-zoom-api' ); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php
		if ( ! empty( $recordings ) ) {
			foreach ( $recordings as $zoom_recording ) {
				?>
                <tr>
                    <td data-sort="<?php echo strtotime( $zoom_recording->start_time ); ?>"><?php echo \Codemanas\VczApi\Helpers\Date::dateConverter( $zoom_recording->start_time, $zoom_recording->timezone ); ?></td>
                    <td><?php echo $zoom_recording->duration; ?></td>
                    <td><?php echo vczapi_filesize_converter( $zoom_recording->total_size ); ?></td>
                    <td>
                        <a href="<?php echo $zoom_recording->share_url; ?>" target="_blank"><?php _e( 'Play', 'video-conferencing-with-zoom-api' ); ?></a>
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
