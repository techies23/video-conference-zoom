<?php
/**
 * @author Deepen.
 * @created_on 11/20/19
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Globals
add_action( 'vczoom_before_main_content', 'video_conference_zoom_output_content_start', 10 );
add_action( 'vczoom_after_main_content', 'video_conference_zoom_output_content_end', 10 );

//Left Section Single Content
add_action( 'vczoom_single_content_left', 'video_conference_zoom_featured_image', 10 );
add_action( 'vczoom_single_content_left', 'video_conference_zoom_main_content', 20 );

//Right Section Single Content
$settings = get_option( '_vczapi_zoom_settings' );
$settings = ! empty( $settings ) ? $settings : false;
if ( empty( $settings['disable_countdown_timer'] ) ) {
	add_action( 'vczoom_single_content_right', 'video_conference_zoom_countdown_timer', 10 );
}
add_action( 'vczoom_single_content_right', 'video_conference_zoom_meeting_details', 20 );
add_action( 'vczoom_single_content_right', 'video_conference_zoom_meeting_join', 30 );
add_action( 'vczoom_single_content_right', 'video_conference_zoom_meeting_end_author', 40 );

//Archive Content
add_action( 'vczapi_main_content_post_loop', 'video_conference_zoom_before_post_loop', 10 );

//single content
add_action( 'vczoom_meeting_join_links', 'video_conference_zoom_meeting_join_link', 10 );

//Shortcode Hooks
add_action( 'vczoom_meeting_before_shortcode', 'video_conference_zoom_shortcode_table', 10 );
add_action( 'vczoom_meeting_shortcode_join_links', 'video_conference_zoom_shortcode_join_link', 10 );
add_action( 'vczoom_meeting_shortcode_join_links_webinar', 'video_conference_zoom_shortcode_join_link_webinar', 10 );

//JBH Hooks
add_action( 'vczoom_jbh_before_content', 'video_conference_zoom_before_jbh_html', 10 );
add_action( 'vczoom_jbh_after_content', 'video_conference_zoom_after_jbh_html', 10 );