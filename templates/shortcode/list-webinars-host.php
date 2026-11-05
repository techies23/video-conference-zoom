<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * The template for displaying list of webinars hosts  table.
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/list-meetings-host.php
 *
 * @author Deepen Bajracharya
 * @since 3.4.0
 * @version 3.4.0
 */
?>

<table id="vczapi-show-webinars-list-table" class="responsive nowrap vczapi-user-meeting-list">
    <thead>
    <tr>
        <th><?php esc_html_e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php esc_html_e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php esc_html_e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php esc_html_e( 'Actions', 'video-conferencing-with-zoom-api' ); ?></th>
    </tr>
    </thead>
    <tbody>
	<?php
	if ( ! empty( $args ) ) {
		foreach ( $args as $webinar ) {
			$pass = ! empty( $webinar->password ) ? $webinar->password : false;
			?>
            <tr>
                <td><?php echo esc_html( $webinar->topic ); ?></td>
                <td><?php echo esc_html( \Codemanas\VczApi\Helpers\Date::dateConverter( $webinar->start_time, $webinar->timezone ) ); ?></td>
                <td><?php echo esc_html( $webinar->timezone ); ?></td>
                <td>
                    <a href="<?php echo esc_url( $webinar->join_url ); ?>"><?php esc_html_e( 'Join via App', 'video-conferencing-with-zoom-api' ); ?></a><?php echo wp_kses_post( vczapi_get_browser_join_shortcode( $webinar->id, $pass, false, ' / ' ) ); ?>
                </td>
            </tr>
			<?php
		}
	}
	?>
    </tbody>
</table>