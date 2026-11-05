<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
        <th><?php esc_html_e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php esc_html_e( 'Meeting Status', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php esc_html_e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php esc_html_e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php esc_html_e( 'Actions', 'video-conferencing-with-zoom-api' ); ?></th>
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

			echo '<td>' . esc_html( $meeting->topic ) . '</td>';
			echo '<td>' . wp_kses_post( $meeting_status ) . '</td>';
			echo '<td>' . esc_html( \Codemanas\VczApi\Helpers\Date::dateConverter( $meeting->start_time, $meeting->timezone, 'F j, Y, g:i a' ) ) . '</td>';
			echo '<td>' . esc_html( $meeting->timezone ) . '</td>';
			echo '<td><div class="view">
<a href="' . esc_url( $meeting->join_url ) . '" rel="permalink" target="_blank">' . esc_html__( 'Join via App', 'video-conferencing-with-zoom-api' ) . '</a></div><div class="view">' . wp_kses_post( vczapi_get_browser_join_shortcode( $meeting->id, $meeting->password, false, ' / ' ) ) . '</div></td>';
			echo '</tr>';
		}
	}
	?>
    </tbody>
</table>