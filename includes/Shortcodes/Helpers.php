<?php

namespace Codemanas\VczApi\Shortcodes;

use Codemanas\VczApi\Shortcodes\Support\ZoomResponse;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Shared shortcode helpers.
 *
 * @package Codemanas\VczApi\Shortcodes
 */
class Helpers {

	/**
	 * Set Cache Helper
	 *
	 * @param      $post_id
	 * @param      $key
	 * @param      $value
	 * @param bool $time_in_secods
	 *
	 * @return bool
	 */
	public static function set_post_cache( $post_id, $key, $value, $time_in_secods = false ) {
		if ( ! $post_id ) {
			return false;
		}
		update_post_meta( $post_id, $key, $value );
		update_post_meta( $post_id, $key . '_expiry_time', time() + $time_in_secods );
	}

	/**
	 * Get Cache Data
	 *
	 * @param $post_id
	 * @param $key
	 *
	 * @return bool|mixed
	 */
	public static function get_post_cache( $post_id, $key ) {
		$expiry = get_post_meta( $post_id, $key . '_expiry_time', true );
		if ( ! empty( $expiry ) && $expiry > time() ) {
			return get_post_meta( $post_id, $key, true );
		} else {
			update_post_meta( $post_id, $key, '' );
			update_post_meta( $post_id, $key . '_expiry_time', '' );

			return false;
		}
	}

	/**
	 * Pagination
	 *
	 * Called directly from `templates/shortcode-listing.php`, which is theme
	 * overridable, so this signature is part of the public contract.
	 *
	 * @param $query
	 */
	public static function pagination( $query, $page_num = 1, $base_url = '' ) {
		$big = 999999999999999;
		if ( is_front_page() ) {
			$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
		} else {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}
		//ajax
		if ( wp_doing_ajax() ) {
			$paged = $page_num;
		}
		$base_url = ! wp_doing_ajax() ? get_pagenum_link( $big ) : $base_url;
		echo paginate_links( array(
			'base'    => str_replace( $big, '%#%', esc_url( $base_url ) ),
			'format'  => '?paged=%#%',
			'current' => max( 1, $paged ),
			'total'   => $query->max_num_pages
		) );
	}

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
	 * @return ZoomResponse|null
	 */
	public static function fetch_meeting( $meeting_id ) {
		return ZoomResponse::make( zoom_conference_v2()->meetings()->get( $meeting_id ) );
	}

	/**
	 * Get a webinar detail
	 *
	 * @param $webinar_id
	 *
	 * @return ZoomResponse|null
	 */
	public static function fetch_webinar( $webinar_id ) {
		return ZoomResponse::make( zoom_conference_v2()->webinars()->get( $webinar_id ) );
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
		return $response instanceof WP_Error ? $response->get_error_message() : '';
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
			return new WP_Error( 'vczapi_host_listing_empty', $fallback_message );
		}

		update_option( $option_key, $response[ $collection ] );
		update_option( $option_key . '_expiration', time() + $lifetime );

		return (array) $response[ $collection ];
	}
}