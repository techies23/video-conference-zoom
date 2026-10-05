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

use Codemanas\VczApi\Helpers\Date;

$meetings = ! empty( $args ) ? (array) $args : [];

$status_icons = [
	0 => '<img src="' . esc_url( ZVC_PLUGIN_IMAGES_PATH ) . '/2.png" style="width:14px;" title="Not Started" alt="Not Started">',
	1 => '<img src="' . esc_url( ZVC_PLUGIN_IMAGES_PATH ) . '/3.png" style="width:14px;" title="Completed" alt="Completed">',
	2 => '<img src="' . esc_url( ZVC_PLUGIN_IMAGES_PATH ) . '/1.png" style="width:14px;" title="Currently Live" alt="Live">',
];
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
	foreach ( $meetings as $meeting ) {
		$meeting = (array) $meeting;

		$status = $meeting['status'] ?? null;
		if ( null === $status || '' === $status ) {
			$meeting_status = 'N/A';
		} else {
			$meeting_status = $status_icons[ (int) $status ] ?? '';
		}

		$passcode = ! empty( $meeting['password'] ) ? $meeting['password'] : false;
		?>
		<tr>
			<td><?php echo esc_html( $meeting['topic'] ?? '' ); ?></td>
			<td><?php echo wp_kses_post( $meeting_status ); ?></td>
			<td><?php echo esc_html( Date::dateConverter( $meeting['start_time'] ?? '', $meeting['timezone'] ?? '', 'F j, Y, g:i a' ) ); ?></td>
			<td><?php echo esc_html( $meeting['timezone'] ?? '' ); ?></td>
			<td>
				<div class="view"><a href="<?php echo esc_url( $meeting['join_url'] ?? '' ); ?>" rel="permalink noopener" target="_blank"><?php esc_html_e( 'Join via App', 'video-conferencing-with-zoom-api' ); ?></a></div>
				<div class="view"><?php echo vczapi_get_browser_join_shortcode( $meeting['id'] ?? '', $passcode, false, ' / ' ); ?></div>
			</td>
		</tr>
	<?php
	}
	?>
    </tbody>
</table>