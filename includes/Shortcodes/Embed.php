<?php

namespace Codemanas\VczApi\Shortcodes;

use Codemanas\VczApi\Helpers\Date;
use Codemanas\VczApi\Helpers\MeetingType;

class Embed {

	/**
	 * Holds the single instance of the class.
	 */
	private static ?Embed $_instance = null;

	/**
	 * Create only one instance so that it may not Repeat
	 *
	 * @since 2.0.0
	 */
	public static function get_instance(): ?Embed {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function enqueue_scripts(): void {
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-locales' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-timezone' );
		wp_enqueue_script( 'video-conferncing-with-zoom-browser-js' );
	}

	/**
	 * Get a scalar value.
	 *
	 * @param mixed  $value   Value to normalize.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	private function get_scalar_value( $value, string $default = '' ): string {
		if ( is_array( $value ) || is_object( $value ) ) {
			return $default;
		}

		return (string) $value;
	}

	/**
	 * Normalize a yes/no shortcode attribute.
	 *
	 * @param mixed  $value   Attribute value.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	private function sanitize_yes_no_attribute( $value, string $default = 'no' ): string {
		$value = strtolower( $this->get_scalar_value( $value, $default ) );

		return in_array( $value, [ 'yes', 'no' ], true ) ? $value : $default;
	}

	/**
	 * Sanitize a Zoom meeting/webinar numeric ID.
	 *
	 * @param mixed $value Attribute value.
	 *
	 * @return string
	 */
	private function sanitize_zoom_numeric_id( $value ): string {
		return preg_replace( '/[^0-9]/', '', $this->get_scalar_value( $value ) );
	}

	/**
	 * Sanitize a CSS length for iframe height.
	 *
	 * Allows common length units used by the shortcode docs/UI while rejecting
	 * quotes, semicolons, event handlers, functions, and arbitrary CSS.
	 *
	 * @param mixed  $value   Attribute value.
	 * @param string $default Default height.
	 *
	 * @return string
	 */
	private function sanitize_css_length( $value, string $default = '500px' ): string {
		$value = trim( $this->get_scalar_value( $value, $default ) );

		if ( '' === $value ) {
			return $default;
		}

		if ( preg_match( '/^\d+(?:\.\d+)?(?:px|em|rem|vh|vw|%)?$/', $value ) ) {
			return $value;
		}

		return $default;
	}

	/**
	 * Sanitize a Zoom meeting passcode.
	 *
	 * Zoom meeting passcodes are limited to 10 characters and may contain
	 * alphanumeric characters and special characters. Avoid display-oriented
	 * sanitizers because passcodes are credentials, not HTML display text.
	 *
	 * @param mixed $value Attribute value.
	 *
	 * @return string
	 */
	private function sanitize_zoom_passcode( $value ): string {
		$passcode = $this->get_scalar_value( $value );

		$passcode = str_replace(
			[ "\r", "\n", "\t", ']' ],
			'',
			$passcode
		);

		return function_exists( 'mb_substr' ) ? mb_substr( $passcode, 0, 10 ) : substr( $passcode, 0, 10 );
	}

	/**
	 * Sanitize shortcode attributes for join via browser.
	 *
	 * @param array $attributes Shortcode attributes.
	 *
	 * @return array
	 */
	private function sanitize_join_via_browser_attributes( array $attributes ): array {
		$attributes['meeting_id']        = $this->sanitize_zoom_numeric_id( $attributes['meeting_id'] ?? '' );
		$attributes['title']             = sanitize_text_field( $this->get_scalar_value( $attributes['title'] ?? '' ) );
		$attributes['id']                = sanitize_html_class( $this->get_scalar_value( $attributes['id'] ?? 'zoom_video_uri', 'zoom_video_uri' ) );
		$attributes['login_required']    = $this->sanitize_yes_no_attribute( $attributes['login_required'] ?? 'no', 'no' );
		$attributes['height']            = $this->sanitize_css_length( $attributes['height'] ?? '500px', '500px' );
		$attributes['disable_countdown'] = $this->sanitize_yes_no_attribute( $attributes['disable_countdown'] ?? 'yes', 'yes' );
		$attributes['passcode']          = $this->sanitize_zoom_passcode( $attributes['passcode'] ?? '' );
		$attributes['webinar']           = $this->sanitize_yes_no_attribute( $attributes['webinar'] ?? 'no', 'no' );
		$attributes['image']             = esc_url_raw( $this->get_scalar_value( $attributes['image'] ?? '' ) );
		$attributes['iframe']            = $this->sanitize_yes_no_attribute( $attributes['iframe'] ?? 'yes', 'yes' );

		if ( '' === $attributes['id'] ) {
			$attributes['id'] = 'zoom_video_uri';
		}

		return $attributes;
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

		$attributes = $this->sanitize_join_via_browser_attributes( $attributes );

		if ( $attributes['disable_countdown'] == "no" ) {
			$this->enqueue_scripts();
		}

		unset( $GLOBALS['zoom'] );

		$meeting_id = $attributes['meeting_id'];

		ob_start();
		echo '<div class="vczapi-join-via-browser-main-wrapper">';
		if ( empty( $meeting_id ) ) {
			echo '<h4 class="no-meeting-id"><strong style="color:red;">' . esc_html__( 'ERROR: ', 'video-conferencing-with-zoom-api' ) . '</strong>' . esc_html__( 'No meeting id set in the shortcode', 'video-conferencing-with-zoom-api' ) . '</h4>';

			return;
		}

		if ( ! empty( $attributes['login_required'] ) && $attributes['login_required'] === "yes" && ! is_user_logged_in() ) {
			echo '<h3>' . esc_html__( 'Restricted access, please login to continue.', 'video-conferencing-with-zoom-api' ) . '</h3>';

			return;
		}

		$meetingInfo = ! empty( $attributes['webinar'] ) && $attributes['webinar'] == "yes" ? zoom_conference()->getWebinarInfo( $meeting_id ) : zoom_conference()->getMeetingInfo( $meeting_id );

		if ( is_wp_error( $meetingInfo ) ) {
			echo esc_html( $meetingInfo->get_error_message() );

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


		if ( ! empty( $meeting->type ) && MeetingType::is_recurring_fixed_time_webinar_or_meeting( $meeting->type ) && ! empty( $meeting->occurrences ) ) {
			$occurrences  = ( isset( $meeting->occurrences ) && is_array( $meeting->occurrences ) ) ? $meeting->occurrences : '';
			$meeting_time = is_array( $occurrences ) ? $occurrences[0]->start_time : date( 'Y-m-d h:i a', time() );
		} else {
			$start_time   = ! empty( $meeting->start_time ) ? $meeting->start_time : 'now';
			$meeting_time = date( 'Y-m-d h:i a', strtotime( $start_time ) );
		}

		if ( ! empty( $meeting->timezone ) ) {
			$meeting->meeting_timezone_time = Date::dateConverter( 'now', $meeting->timezone, false );
			$meeting->meeting_time_check    = Date::dateConverter( $meeting_time, $meeting->timezone, false );
		}

		$meeting->shortcode_attributes = $attributes;

		$GLOBALS['zoom'] = $meeting;

		if ( ! empty( $meeting ) && ! empty( $meeting->code ) ) {
			echo esc_html( $meeting->message );
		} else {
			if ( ! empty( $meeting ) ) {
				//Get Template
				vczapi_get_template( 'shortcode/embed-session.php', true, false );
			} else {
				printf( esc_html__( 'Please try again ! Some error occured while trying to fetch meeting with id:  %d', 'video-conferencing-with-zoom-api' ), absint( $meeting_id ) );
			}
		}

		echo "</div>";
		$content .= ob_get_clean();

		return $content;
	}
}