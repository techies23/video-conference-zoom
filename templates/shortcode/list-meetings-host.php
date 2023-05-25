<?php
/**
 * The template for displaying list of meeting hosts  table.
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/list-meetings-host.php
 *
 * @author Deepen Bajracharya
 * @since 3.4.0
 * @version 3.4.0
 */
?>

<table id="vczapi-show-meetings-list-table" class="responsive nowrap vczapi-user-meeting-list">
    <thead>
    <tr>
        <th><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php _e( 'Meeting Status', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php _e( 'Actions', 'video-conferencing-with-zoom-api' ); ?></th>
    </tr>
    </thead>
    <tbody>
	<?php
	if ( ! empty( $args ) ) {
		foreach ( $args as $meeting ) {
			$meeting->password = ! empty( $meeting->password ) ? $meeting->password : false;
			$meeting_status    = '';
			if ( ! empty( $meeting->status ) ) {
				switch ( $meeting->status ) {
					case 0;
						$meeting_status = '<img src="' . ZVC_PLUGIN_IMAGES_PATH . '/2.png" style="width:14px;" title="Not Started" alt="Not Started">';
						break;
					case 1;
						$meeting_status = '<img src="' . ZVC_PLUGIN_IMAGES_PATH . '/3.png" style="width:14px;" title="Completed" alt="Completed">';
						break;
					case 2;
						$meeting_status = '<img src="' . ZVC_PLUGIN_IMAGES_PATH . '/1.png" style="width:14px;" title="Currently Live" alt="Live">';
						break;
					default;
						break;
				}
			} else {
				$meeting_status = "N/A";
			}

			echo '<td>' . $meeting->topic . '</td>';
			echo '<td>' . $meeting_status . '</td>';
			echo '<td>' . vczapi_dateConverter( $meeting->start_time, $meeting->timezone, 'F j, Y, g:i a' ) . '</td>';
			echo '<td>' . $meeting->timezone . '</td>';
			echo '<td><div class="view">
<a href="' . $meeting->join_url . '" rel="permalink" target="_blank">' . __( 'Join via App', 'video-conferencing-with-zoom-api' ) . '</a></div><div class="view">' . vczapi_get_browser_join_shortcode( $meeting->id, $meeting->password, false, ' / ' ) . '</div></td>';
			echo '</tr>';
		}
	}
	?>
    </tbody>
</table>