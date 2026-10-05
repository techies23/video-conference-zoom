<?php

namespace Codemanas\VczApi\Shortcodes;

use Codemanas\VczApi\Helpers\Date;
use Codemanas\VczApi\Helpers\MeetingType;
use Codemanas\VczApi\Shortcodes\Support\ZoomResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Legacy Join-via-Browser embed shortcode.
 *
 * @deprecated 3.3.1 Superseded by the WebSDK `/zoom-join/` page. The tag is kept
 *             registered because it is part of the plugin's public API.
 */
class Embed {

	/**
	 * @var Embed|null
	 */
	private static ?Embed $_instance = null;

	/**
	 * @return Embed
	 */
	public static function get_instance(): ?Embed {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Render the embed.
	 *
	 * `[zoom_join_via_browser]`
	 *
	 * @param array $atts    Shortcode attributes.
	 * @param mixed $content Enclosed content.
	 *
	 * @return string
	 */
	public function join_via_browser( $atts, $content = null ): string {
		//Allow addon devs to perform action before window rendering
		do_action( 'vczapi_before_shortcode_content' );

		$attributes = $this->sanitize_attributes(
			shortcode_atts(
				[
					'meeting_id'        => '',
					'title'             => '',
					'id'                => 'zoom_video_uri',
					'login_required'    => 'no',
					'height'            => '500px',
					'disable_countdown' => 'yes',
					'passcode'          => '',
					'webinar'           => 'no',
					'image'             => '',
					'iframe'            => 'yes',
				],
				$atts
			)
		);

		unset( $GLOBALS['zoom'] );

		$meeting_id = $attributes['meeting_id'];

		ob_start();
		echo '<div class="vczapi-join-via-browser-main-wrapper">';

		if ( empty( $meeting_id ) ) {
			echo '<h4 class="no-meeting-id"><strong style="color:red;">'
				. esc_html__( 'ERROR: ', 'video-conferencing-with-zoom-api' )
				. '</strong>' . esc_html__( 'No meeting id set in the shortcode', 'video-conferencing-with-zoom-api' )
				. '</h4></div>';

			return (string) ob_get_clean();
		}

		if ( 'yes' === $attributes['login_required'] && ! is_user_logged_in() ) {
			echo '<h3>' . esc_html__( 'Restricted access, please login to continue.', 'video-conferencing-with-zoom-api' ) . '</h3></div>';

			return (string) ob_get_clean();
		}

		$meeting = $this->fetch_meeting( $meeting_id, $attributes['webinar'] );

		if ( is_wp_error( $meeting ) ) {
			echo esc_html( $meeting->get_error_message() ) . '</div>';

			return (string) ob_get_clean();
		}

		$meeting = $this->prepare_meeting( $meeting, $meeting_id, $attributes );

		$GLOBALS['zoom'] = $meeting;

		vczapi_get_template( 'shortcode/embed-session.php', true, false );

		echo '</div>';

		return (string) ( (string) $content . ob_get_clean() );
	}

	/**
	 * Fetch the meeting or webinar behind the embed.
	 *
	 * @param string $meeting_id Meeting ID.
	 * @param string $webinar    `yes` to read a webinar instead of a meeting.
	 *
	 * @return array|\WP_Error
	 */
	private function fetch_meeting( string $meeting_id, string $webinar ) {
		$response = 'yes' === $webinar
			? zoom_conference_v2()->webinars()->get( $meeting_id )
			: zoom_conference_v2()->meetings()->get( $meeting_id );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response = apply_filters( 'vczapi_join_via_browser_shortcode_meetings', $response );

		return is_array( $response ) ? $response : [];
	}

	/**
	 * Derive the derived values the embed template relies on.
	 *
	 * @param array  $meeting    Meeting payload.
	 * @param string $meeting_id Meeting ID.
	 * @param array  $attributes Sanitized shortcode attributes.
	 *
	 * @return ZoomResponse
	 */
	private function prepare_meeting( array $meeting, string $meeting_id, array $attributes ): ZoomResponse {
		$meeting = ZoomResponse::make( $meeting );

		$zoom_states = get_option( 'zoom_api_meeting_options' );
		if ( ! empty( $zoom_states ) ) {
			$meeting['zoom_states'] = $zoom_states;
		}

		$vanity_url = get_option( 'zoom_vanity_url' );
		$meeting['mobile_zoom_url'] = empty( $vanity_url )
			? 'https://zoom.us/j/' . $meeting_id
			: trailingslashit( $vanity_url . '/j' ) . $meeting_id;

		$meeting['shortcode_attributes'] = $attributes;

		if ( empty( $meeting['occurrences'] ) || ! MeetingType::is_recurring_fixed_time_webinar_or_meeting( $meeting['type'] ?? 0 ) ) {
			$meeting_time = date( 'Y-m-d h:i a', strtotime( $meeting['start_time'] ?? 'now' ) );
		} else {
			$occurrences  = (array) $meeting['occurrences'];
			$meeting_time = date( 'Y-m-d h:i a', strtotime( $occurrences[0]['start_time'] ?? 'now' ) );
		}

		if ( ! empty( $meeting['timezone'] ) ) {
			$meeting['meeting_timezone_time'] = Date::dateConverter( 'now', $meeting['timezone'], false );
			$meeting['meeting_time_check']    = Date::dateConverter( $meeting_time, $meeting['timezone'], false );
		}

		return $meeting;
	}

	/**
	 * Sanitize the shortcode attributes.
	 *
	 * @param array $attributes Shortcode attributes.
	 *
	 * @return array
	 */
	private function sanitize_attributes( array $attributes ): array {
		$attributes['meeting_id']        = AttributeSanitizer::numeric_id( $attributes['meeting_id'] ?? '' );
		$attributes['title']             = sanitize_text_field( AttributeSanitizer::scalar( $attributes['title'] ?? '' ) );
		$attributes['id']                = sanitize_html_class( AttributeSanitizer::scalar( $attributes['id'] ?? 'zoom_video_uri', 'zoom_video_uri' ), 'zoom_video_uri' );
		$attributes['login_required']    = AttributeSanitizer::yes_no( $attributes['login_required'] ?? 'no', 'no' );
		$attributes['height']            = AttributeSanitizer::css_length( $attributes['height'] ?? '500px', '500px' );
		$attributes['disable_countdown'] = AttributeSanitizer::yes_no( $attributes['disable_countdown'] ?? 'yes', 'yes' );
		$attributes['passcode']          = AttributeSanitizer::passcode( $attributes['passcode'] ?? '' );
		$attributes['webinar']           = AttributeSanitizer::yes_no( $attributes['webinar'] ?? 'no', 'no' );
		$attributes['image']             = esc_url_raw( AttributeSanitizer::scalar( $attributes['image'] ?? '' ) );
		$attributes['iframe']            = AttributeSanitizer::yes_no( $attributes['iframe'] ?? 'yes', 'yes' );

		if ( '' === $attributes['id'] ) {
			$attributes['id'] = 'zoom_video_uri';
		}

		return $attributes;
	}
}