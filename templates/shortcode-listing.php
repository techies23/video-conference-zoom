<?php
/**
 * The Template for displaying all single meetings
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode-listing.php.
 *
 * @package     Video Conferencing with Zoom API/Templates
 * @version     3.2.2
 * @version     3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $zoom_meetings;

if ( ! is_object( $zoom_meetings ) && ! ( $zoom_meetings instanceof \WP_Query ) ) {
	return;
}

$args = isset( $args ) ? $args : [];
$atts = shortcode_atts(
	array(
		'author'       => '',
		'per_page'     => 5,
		'category'     => '',
		'order'        => 'DESC',
		'type'         => '',
		'filter'       => 'yes',
		'show_on_past' => 'yes',
		'cols'         => 3,
		'meeting_type' => 'meetings',
	),
	$args, 'zoom_list_meetings'
);

?>
<div class="vczapi-list-zoom-meetings"
     data-author="<?php echo esc_attr( $atts['author'] ); ?>"
     data-per_page="<?php echo esc_attr( $atts['per_page'] ); ?>"
     data-category="<?php echo esc_attr( $atts['category'] ); ?>"
     data-order="<?php echo esc_attr( $atts['order'] ); ?>"
     data-type="<?php echo esc_attr( $atts['type'] ); ?>"
     data-filter="<?php echo esc_attr( $atts['filter'] ); ?>"
     data-show_on_past="<?php echo esc_attr( $atts['show_on_past'] ); ?>"
     data-cols="<?php echo esc_attr( $atts['cols'] ); ?>"
     data-base_url="<?php echo esc_url( get_pagenum_link( 999999999999999 ) ); ?>"
     data-meeting_type="<?php echo esc_attr( $atts['meeting_type'] ); ?>"
>
    <div class="vczapi-loader">
        <img src="<?php echo ZVC_PLUGIN_IMAGES_PATH . '/ajax-loader.gif'; ?>" alt="loading..."/>
    </div>
	<?php
	/**
	 * BEFORE LOOP HOOK
	 */
	do_action( 'vczapi_before_shortcode_content_post_loop', $zoom_meetings );

	?>

    <div class="vczapi-wrap vczapi-items-wrap">
		<?php
		if ( $zoom_meetings->have_posts() ) {
			while ( $zoom_meetings->have_posts() ) {
				$zoom_meetings->the_post();

				do_action( 'vczapi_main_content_post_loop' );

				vczapi_get_template_part( 'shortcode/zoom', 'listing' );
			}
		} else {
			echo "<p class='vczapi-no-meeting-found'>" . __( 'No Meetings found.', 'video-conferencing-with-zoom-api' ) . "</p>";
		}

		wp_reset_postdata();
		?>
    </div>

	<?php
	/**
	 * AFTER LOOP HOOK
	 */
	do_action( 'vczapi_after_shortcode_content_post_loop' );
	?>

    <div class="vczapi-list-zoom-meetings--pagination">
		<?php \Codemanas\VczApi\Shortcodes\Helpers::pagination( $zoom_meetings ); ?>
    </div>

	<?php do_action( 'vczapi_after_main_content_post_loop_pagination' ); ?>
</div>