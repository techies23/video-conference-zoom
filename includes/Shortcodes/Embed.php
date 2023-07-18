<?php

namespace Codemanas\VczApi\Shortcodes;

use Codemanas\VczApi\Helpers\Date;

class Embed {

	/**
	 * Instance
	 *
	 * @var null
	 */
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

	public function enqueue_scripts() {
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-locales' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-timezone' );
		wp_enqueue_script( 'video-conferncing-with-zoom-browser-js' );
	}

	/**
	 * Join via browser shortcode
	 *
	 * @param $atts
	 * @param $content
	 *
	 * @return mixed|string|void
	 * @deprecated 3.3.1
	 *
	 */
	public function join_via_browser( $atts, $content = null ) {
		// Allow addon devs to perform action before window rendering
		do_action( 'vczapi_before_shortcode_content' );

		$attributes = shortcode_atts( array(
			'meeting_id'        => '',
			'title'             => '',
			'id'                => 'zoom_video_uri',
			'login_required'    => "no",
			'height'            => "500px",
			'disable_countdown' => 'yes',
			'passcode'          => '',
			'webinar'           => 'no',
			'image'             => '',
			'iframe'            => 'yes'
		), $atts );

		if ( $attributes['disable_countdown'] == "no" ) {
			$this->enqueue_scripts();
		}

		unset( $GLOBALS['zoom'] );

		$meeting_id = $attributes['meeting_id'];

		ob_start();
		echo '<div class="vczapi-join-via-browser-main-wrapper">';
		if ( empty( $meeting_id ) ) {
			echo '<h4 class="no-meeting-id"><strong style="color:red;">' . __( 'ERROR: ', 'video-conferencing-with-zoom-api' ) . '</strong>' . __( 'No meeting id set in the shortcode', 'video-conferencing-with-zoom-api' ) . '</h4>';

			return;
		}

		if ( ! empty( $attributes['login_required'] ) && $attributes['login_required'] === "yes" && ! is_user_logged_in() ) {
			echo '<h3>' . esc_html__( 'Restricted access, please login to continue.', 'video-conferencing-with-zoom-api' ) . '</h3>';

			return;
		}

		$meetingInfo = ! empty( $attributes['webinar'] ) && $attributes['webinar'] == "yes" ? zoom_conference()->getWebinarInfo( $meeting_id ) : zoom_conference()->getMeetingInfo( $meeting_id );

		if ( is_wp_error( $meetingInfo ) ) {
			echo $meetingInfo->get_error_message();
			return;
		} else {
			$meeting = json_decode( $meetingInfo );
		}

		$meeting = apply_filters( 'vczapi_join_via_browser_shortcode_meetings', $meeting );

		$zoom_states = get_option( 'zoom_api_meeting_options' );
		if ( ! empty( $zoom_states ) ) {
			$meeting->zoom_states = $zoom_states;
		}

		$zoom_vanity_url = get_option( 'zoom_vanity_url' );
		if ( empty( $zoom_vanity_url ) ) {
			$meeting->mobile_zoom_url = 'https://zoom.us/j/' . $meeting_id;
		} else {
			$meeting->mobile_zoom_url = trailingslashit( $zoom_vanity_url . '/j' ) . $meeting_id;
		}

		if ( ! empty( $meeting->type ) && ( $meeting->type === 9 || $meeting->type === 8 ) && ! empty( $meeting->occurrences ) ) {
			$occurrences  = ( isset( $meeting->occurrences ) && is_array( $meeting->occurrences ) ) ? $meeting->occurrences : '';
			$meeting_time = is_array( $occurrences ) ? $occurrences[0]->start_time : date( 'Y-m-d h:i a', time() );
		} else {
			$start_time   = ! empty( $meeting->start_time ) ? $meeting->start_time : 'now';
			$meeting_time = date( 'Y-m-d h:i a', strtotime( $start_time ) );
		}

		if( !empty($meeting->timezone) ) {
			$meeting->meeting_timezone_time = Date::dateConverter( 'now', $meeting->timezone, false );
			$meeting->meeting_time_check    = Date::dateConverter( $meeting_time, $meeting->timezone, false );
		}

		$meeting->shortcode_attributes  = $attributes;

		$GLOBALS['zoom'] = $meeting;

		if ( ! empty( $meeting ) && ! empty( $meeting->code ) ) {
			echo $meeting->message;
		} else {
			if ( ! empty( $meeting ) ) {
				//Get Template
				vczapi_get_template( 'shortcode/embed-session.php', true, false );
			} else {
				printf( __( 'Please try again ! Some error occured while trying to fetch meeting with id:  %d', 'video-conferencing-with-zoom-api' ), $meeting_id );
			}
		}

		echo "</div>";
		$content .= ob_get_clean();

		return $content;
	}
}