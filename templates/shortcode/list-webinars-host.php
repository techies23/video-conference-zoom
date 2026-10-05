<?php
/**
 * The template for displaying list of webinars hosts  table.
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/list-webinars-host.php
 *
 * @author Deepen Bajracharya
 * @since 3.4.0
 * @version 3.4.0
 */

use Codemanas\VczApi\Helpers\Date;

$webinars = ! empty( $args ) ? (array) $args : [];
?>

<table id="vczapi-show-webinars-list-table" class="responsive nowrap vczapi-user-meeting-list">
    <thead>
    <tr>
        <th><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php _e( 'Actions', 'video-conferencing-with-zoom-api' ); ?></th>
    </tr>
    </thead>
    <tbody>
	<?php
	foreach ( $webinars as $webinar ) {
		$webinar = (array) $webinar;

		$passcode = ! empty( $webinar['password'] ) ? $webinar['password'] : false;
		?>
		<tr>
			<td><?php echo esc_html( $webinar['topic'] ?? '' ); ?></td>
			<td><?php echo esc_html( Date::dateConverter( $webinar['start_time'] ?? '', $webinar['timezone'] ?? '' ) ); ?></td>
			<td><?php echo esc_html( $webinar['timezone'] ?? '' ); ?></td>
			<td>
				<a href="<?php echo esc_url( $webinar['join_url'] ?? '' ); ?>"><?php esc_html_e( 'Join via App', 'video-conferencing-with-zoom-api' ); ?></a>
				<?php echo vczapi_get_browser_join_shortcode( $webinar['id'] ?? '', $passcode, false, ' / ' ); ?>
			</td>
		</tr>
	<?php
	}
	?>
    </tbody>
</table>