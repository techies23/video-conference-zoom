<?php
/**
 * The Template for displaying list of recordings via meeting ID
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/zoom-recordings-by-meeting.php.
 *
 * @package     Video Conferencing with Zoom API/Templates
 * @version     3.5.0
 */

global $zoom_recordings, $zoom_recordings_is_downloadable;
$total_size = 0;
$table_body = '';

foreach ( $zoom_recordings as $zoom_recording ) {
	$total_size += $zoom_recording->total_size;
	ob_start();
	foreach ( $zoom_recording->recording_files as $recording ) {
		if ( $recording->file_type !== "MP4" ) {
			break;
		}
		?>
        <tr>
            <td data-sort="<?php echo strtotime( $recording->recording_start ); ?>"><?php echo vczapi_dateConverter( $recording->recording_start, $zoom_recording->timezone ); ?></td>
            <td data-sort="<?php echo strtotime( $recording->recording_end ); ?>"><?php echo vczapi_dateConverter( $recording->recording_end, $zoom_recording->timezone ); ?></td>
            <td><?php echo vczapi_filesize_converter( $recording->file_size ); ?></td>
            <td>
                <a href="<?php echo $recording->play_url; ?>" target="_blank"><?php _e( 'Play', 'video-conferencing-with-zoom-api' ); ?></a>
				<?php if ( $zoom_recordings_is_downloadable ) { ?>
                    <a href="<?php echo $recording->download_url; ?>" target="_blank"><?php _e( 'Download', 'video-conferencing-with-zoom-api' ); ?></a>
				<?php } ?>
            </td>

        </tr>
		<?php
	}
	$table_body .= ob_get_clean();
}
?>
<div class="vczapi-recordings-meeting-id-description">
    <ul>
        <li><strong><?php _e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo $zoom_recordings[0]->id; ?></li>
        <li><strong><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo $zoom_recordings[0]->topic; ?></li>
        <li><strong><?php _e( 'Total Size', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo vczapi_filesize_converter( $total_size ); ?></li>
        <li>
            <a href="<?php echo esc_url( add_query_arg( [ 'flush_cache' => 'yes' ], get_the_permalink() ) ); ?>"><?php _e( 'Check for latest' ); ?></a>
        </li>
    </ul>
</div>
<table id="vczapi-recordings-list-table" class="responsive nowrap vczapi-recordings-list-table-meeting-id vczapi-user-meeting-list">
    <thead>
    <tr>
        <th><?php _e( 'Start Date', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php _e( 'End Date', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php _e( 'Size', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php _e( 'Action', 'video-conferencing-with-zoom-api' ); ?></th>
    </tr>
    </thead>
    <tbody>
	<?php
	echo $table_body;
	?>
    </tbody>
</table>