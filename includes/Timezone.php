<?php

namespace Codemanas\VczApi;

use Codemanas\VczApi\Helpers\MeetingType;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Timezones AJAX handler
 *
 * @since   3.1.2
 * @author  Deepen
 */
class Timezone {

	private static $_instance = null;

	/**
	 * Create only one instance so that it may not Repeat
	 *
	 * @since 2.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_set_timezone', array( $this, 'set_timezone' ) );
		add_action( 'wp_ajax_nopriv_set_timezone', array( $this, 'set_timezone' ) );
	}

	/**
	 * See timezone and show links accordingly
	 *
	 * @throws \Exception
	 * @author Deepen Bajracharya
	 * @since  3.1.2
	 */
	public function set_timezone() {
		$post_id = absint( filter_input( INPUT_POST, 'post_id' ) );
		$type    = sanitize_key( (string) filter_input( INPUT_POST, 'type' ) );

		// Only the single-meeting page is supported. The old shortcode branch
		// echoed client supplied URLs and is no longer emitted by this plugin.
		if ( 'page' !== $type ) {
			wp_send_json_error( __( 'Invalid request.', 'video-conferencing-with-zoom-api' ) );
		}

		$post = $post_id ? get_post( $post_id ) : null;

		// Use a single generic response for every denial so that post IDs
		// cannot be enumerated through different error messages.
		$deny = static function () {
			wp_send_json_error( apply_filters( 'vczoom_shortcode_link_not_valid_anymore', __( 'This meeting is no longer valid and cannot be joined !', 'video-conferencing-with-zoom-api' ) ) );
		};

		// Must be a real Zoom meeting post.
		if ( empty( $post ) || 'zoom-meetings' !== $post->post_type ) {
			$deny();
		}

		// Draft, pending and private meetings are only readable by users who
		// are allowed to read the post.
		if ( 'publish' !== $post->post_status && ! current_user_can( 'read_post', $post->ID ) ) {
			$deny();
		}

		// Password protected meetings must not disclose join links via AJAX.
		if ( post_password_required( $post ) ) {
			$deny();
		}

		$meeting_fields = get_post_meta( $post->ID, '_meeting_fields', true );
		$meeting_fields = is_array( $meeting_fields ) ? $meeting_fields : array();

		// Per-meeting "must be logged in" option.
		if ( ! empty( $meeting_fields['site_option_logged_in'] ) && ! is_user_logged_in() ) {
			$deny();
		}

		$meeting_details = get_post_meta( $post->ID, '_meeting_zoom_details', true );

		// No meeting created yet, Zoom API error, ended meeting or no join URL.
		if ( empty( $meeting_details ) || ! is_object( $meeting_details ) || ! empty( $meeting_details->code ) || ! empty( $meeting_details->state ) || empty( $meeting_details->join_url ) ) {
			$deny();
		}

		// Time based visibility is a scheduling feature, not an access control,
		// but it must be evaluated from stored data instead of request data.
		if ( ! $this->is_join_window_open( $post->ID, $meeting_fields, $meeting_details ) ) {
			wp_send_json_error( apply_filters( 'vczoom_shortcode_link_not_valid_anymore', __( 'This meeting is no longer valid and cannot be joined !', 'video-conferencing-with-zoom-api' ) ) );
		}

		wp_send_json_success( $this->output_join_links_page( $post->ID ) );
	}

	/**
	 * Determines whether join links should currently be visible for a meeting.
	 *
	 * Uses the stored meeting start date and type instead of trusting the
	 * `start_date` / `meeting_type` values sent by the browser.
	 *
	 * @param int    $post_id
	 * @param array  $meeting_fields
	 * @param object $meeting_details
	 *
	 * @return bool
	 */
	private function is_join_window_open( int $post_id, array $meeting_fields, $meeting_details ): bool {
		// Explicit overrides kept for backwards compatibility.
		if ( get_option( 'zoom_past_join_links' ) ) {
			return true;
		}

		$show_defined_post = array_map( 'absint', (array) apply_filters( 'vczapi_show_join_links_specific_postID', array() ) );
		if ( in_array( $post_id, $show_defined_post, true ) ) {
			return true;
		}

		$meeting_type = ! empty( $meeting_details->type ) ? (int) $meeting_details->type : 0;

		// Recurring meetings / webinars without a fixed time are always joinable.
		if ( MeetingType::is_recurring_no_fixed_time_meeting( $meeting_type ) || MeetingType::is_recurring_no_fixed_time_webinar( $meeting_type ) ) {
			return true;
		}

		$start_date = get_post_meta( $post_id, '_meeting_field_start_date_utc', true );

		// Matches the previous client side behaviour for meetings that do not
		// have a stored start date.
		if ( empty( $start_date ) ) {
			return true;
		}

		$timezone = ! empty( $meeting_fields['timezone'] ) ? $meeting_fields['timezone'] : ( ! empty( $meeting_details->timezone ) ? $meeting_details->timezone : 'UTC' );

		try {
			$start_time = vczapi_dateConverter( $start_date, $timezone, false );
			$now        = vczapi_dateConverter( 'now', $timezone, false );
		} catch ( \Exception $e ) {
			return false;
		}

		if ( ! $start_time instanceof \DateTime || ! $now instanceof \DateTime ) {
			return false;
		}

		// Keep the same one hour grace period as the previous handler.
		return $start_time >= $now->modify( '-1 hour' );
	}

	/**
	 * Show join links from here for pages
	 *
	 * @param int $post_id
	 *
	 * @return string
	 * @author Deepen Bajracharya
	 *
	 */
	private function output_join_links_page( int $post_id ): string {
		$meeting_details = get_post_meta( $post_id, '_meeting_zoom_details', true );

		// Never render the template for an invalid meeting payload.
		if ( empty( $meeting_details ) || ! is_object( $meeting_details ) || empty( $meeting_details->join_url ) ) {
			return '';
		}

		unset( $GLOBALS['zoom'] );
		unset( $GLOBALS['vanity_enabled'] );
		$GLOBALS['zoom']           = $meeting_details;
		$GLOBALS['zoom']->post_id  = $post_id;
		$GLOBALS['vanity_enabled'] = get_option( 'zoom_vanity_url' );

		ob_start(); //Init the output buffering
		$template = vczapi_get_template( 'fragments/join-links.php', false );
		if ( ! empty( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean(); //Get the buffer and erase it
	}
}