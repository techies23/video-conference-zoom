<?php
/**
 * The Template for displaying all single meetings
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode-listing.php.
 *
 * @package    Video Conferencing with Zoom API/Templates
 * @version     3.2.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $zoom_meetings;

if ( ! is_object( $zoom_meetings ) && ! ( $zoom_meetings instanceof \WP_Query ) ) {
	return;
}
?>
<div class="vczapi-list-zoom-meetings">
    <div class="vczapi-list-zoom-meetings--items">
        <?php
		while ( $zoom_meetings->have_posts() ) {
			$zoom_meetings->the_post();

			vczapi_get_template_part( 'shortcode/zoom', 'listing' );
		}

		wp_reset_postdata();
		?>
    </div>
    <div class="vczapi-list-zoom-meetings--pagination">
		<?php Zoom_Video_Conferencing_Shorcodes::pagination( $zoom_meetings ); ?>
    </div>
</div>