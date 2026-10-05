<?php

namespace Codemanas\VczApi\Shortcodes\Utils;

/**
 * Shared shortcode helpers.
 *
 * @package Codemanas\VczApi\Shortcodes
 */
class Support {

	/**
	 * Output only singel link
	 *
	 * @since  3.0.4
	 * @author Deepen
	 */
	public static function generate_link_only() {
		//Get Template
		vczapi_get_template( 'shortcode/zoom-single-link.php', true, false );
	}

	/**
	 * Get Meeting INFO
	 *
	 * @param $meeting_id
	 *
	 */
	public static function fetch_meeting( $meeting_id ) {
		return zoom_conference_v2()->meetings()->get( $meeting_id );
	}

	/**
	 * Get a webinar detail
	 *
	 * @param $webinar_id
	 *
	 */
	public static function fetch_webinar( $webinar_id ) {
		return zoom_conference_v2()->webinars()->get( $webinar_id );
	}

	/**
	 * Resolve an API response into either a payload or a human readable error.
	 *
	 * The 4.7.0 client returns `WP_Error` on failure, so every shortcode needs
	 * the same error handling.
	 *
	 * @param WP_Error|array|null $response API response.
	 *
	 * @return string Error message, or an empty string when the response is usable.
	 */
	public static function error_message( $response ): string {
		return $response instanceof \WP_Error ? $response->get_error_message() : '';
	}

	/**
	 * Read-through option cache for the host meeting/webinar listing shortcodes.
	 *
	 * `[zoom_list_host_meetings]` and `[zoom_list_host_webinars]` both list a
	 * host's upcoming entities, cache the result in an option for five minutes,
	 * and fall back to a "check Host ID" message when Zoom returns nothing.
	 *
	 * @param string   $option_key      Option name holding the cached items.
	 * @param string   $collection      Response key holding the items, `meetings` or `webinars`.
	 * @param string   $fallback_message Message used when the response has no items.
	 * @param callable $fetch           Fetcher, returns `WP_Error|array`.
	 * @param int      $lifetime        Cache lifetime in seconds.
	 *
	 * @return array|WP_Error Cached/fresh items, or `WP_Error` on failure.
	 */
	public static function host_listing( string $option_key, string $collection, string $fallback_message, callable $fetch, int $lifetime = 300 ): array|WP_Error {
		$cached   = get_option( $option_key );
		$expires  = (int) get_option( $option_key . '_expiration' );

		if ( ! empty( $cached ) && $expires > time() ) {
			return (array) $cached;
		}

		$response = $fetch();

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( empty( $response[ $collection ] ) ) {
			return new \WP_Error( 'vczapi_host_listing_empty', $fallback_message );
		}

		update_option( $option_key, $response[ $collection ] );
		update_option( $option_key . '_expiration', time() + $lifetime );

		return (array) $response[ $collection ];
	}
}