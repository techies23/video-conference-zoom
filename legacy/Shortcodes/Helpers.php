<?php

namespace Codemanas\VczApi\Shortcodes;

/**
 * Class Helpers for Shortcode helper functions
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
	 * @return bool|mixed|null
	 */
	public static function fetch_meeting( $meeting_id ) {
		$meeting = json_decode( zoom_conference()->getMeetingInfo( $meeting_id ) );
		if ( ! empty( $meeting->error ) ) {
			return false;
		}

		return $meeting;
	}

	/**
	 * Get a webinar detail
	 *
	 * @param $webinar_id
	 *
	 * @return bool|mixed|null
	 */
	public static function fetch_webinar( $webinar_id ) {
		$webinar = json_decode( zoom_conference()->getWebinarInfo( $webinar_id ) );

		return $webinar;
	}


}