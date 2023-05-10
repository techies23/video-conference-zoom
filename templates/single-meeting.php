<?php
/**
 * The Template for displaying all single meetings
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/single-meetings.php.
 *
 * @package    Video Conferencing with Zoom API/Templates
 * @version     3.0.0
 * @since       3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! vczapi_is_fse_theme() ) {
	get_header();
}

/**
 * vczoom_before_main_content hook.
 *
 * @hooked video_conference_zoom_output_content_wrapper
 */
do_action( 'vczoom_before_main_content' );

while ( have_posts() ) {
	the_post();

	vczapi_get_template_part( 'content', 'single-meeting' );
}

/**
 * vczoom_after_main_content hook.
 *
 * @hooked video_conference_zoom_output_content_end
 */
do_action( 'vczoom_after_main_content' );

if ( ! vczapi_is_fse_theme() ) {
	get_footer();
}
/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
