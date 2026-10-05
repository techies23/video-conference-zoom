<?php
/**
 * Template for showing single link with join url.
 *
 * @author Deepen.
 * @since 3.0.0
 * @version 3.9.0
 */

global $zoom_meetings;
?>

<a href="<?php echo esc_url( $zoom_meetings['join_url'] ?? '' ); ?>" title="<?php esc_attr_e( 'Join Meeting', 'video-conferencing-with-zoom-api' ); ?>"><?php esc_html_e( 'Join Meeting', 'video-conferencing-with-zoom-api' ); ?></a>
