<?php
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
        <th><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></th>
        <th><?php _e( 'Actions', 'video-conferencing-with-zoom-api' ); ?></th>
    </tr>
    </thead>
    <tbody>
	<?php
	if ( ! empty( $args ) ) {
		foreach ( $args as $webinar ) {
			$pass = ! empty( $webinar->password ) ? $webinar->password : false;
			?>
            <tr>
                <td><?php echo $webinar->topic; ?></td>
                <td><?php echo vczapi_dateConverter( $webinar->start_time, $webinar->timezone ); ?></td>
                <td><?php echo $webinar->timezone; ?></td>
                <td>
                    <a href="<?php echo $webinar->join_url; ?>"><?php _e( 'Join via App', 'video-conferencing-with-zoom-api' ); ?></a><?php echo vczapi_get_browser_join_shortcode( $webinar->id, $pass, false, ' / ' ); ?>
                </td>
            </tr>
			<?php
		}
	}
	?>
    </tbody>
</table>