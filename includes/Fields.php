<?php

namespace Codemanas\VczApi\Includes;

/**
 * Class Fields
 * @package Codemanas\VczApi\Includes
 */
class Fields {

	/**
	 * Field slug
	 *
	 * @var string
	 */
	private static $fields_slug = '_vczapi_';

	/**
	 * Get option fields data
	 *
	 * @param $key
	 *
	 * @return bool|mixed|void
	 */
	public static function get_option( $key ) {
		$result = get_option( self::$fields_slug . $key );
		if ( empty( $result ) ) {
			return false;
		}

		return $result;
	}

	/**
	 * Get Post meta results
	 *
	 * @param $post_id
	 * @param $key
	 *
	 * @return bool|mixed
	 */
	public static function get_meta( $post_id, $key ) {
		$result = get_post_meta( $post_id, self::$fields_slug . $key, true );
		if ( empty( $result ) ) {
			return false;
		}

		return $result;
	}

	/**
	 * Get User Meta
	 *
	 * @param $user_id
	 * @param $key
	 *
	 * @return bool|mixed
	 */
	public static function get_user_meta( $user_id, $key ) {
		$result = get_user_meta( $user_id, self::$fields_slug . $key, true );
		if ( empty( $result ) ) {
			return false;
		}

		return $result;
	}

	/**
	 * Set Options
	 *
	 * @param $key
	 * @param $value
	 */
	public static function set_option( $key, $value ) {
		if ( is_array( $key ) ) {
			foreach ( $key as $k => $v ) {
				update_option( self::$fields_slug . $k, $v );
			}
		} else {
			update_option( self::$fields_slug . $key, $value );
		}
	}

	/**
	 * Delete option by key
	 *
	 * @param $key
	 */
	public static function delete_option( $key ) {
		delete_option( self::$fields_slug . $key );
	}

	/**
	 * Set and update post meta values from here
	 *
	 * @param $post_id
	 * @param $key
	 * @param $value
	 */
	public static function set_post_meta( $post_id, $key, $value = false ) {
		if ( is_array( $key ) && $value === false ) {
			foreach ( $key as $k => $v ) {
				update_post_meta( $post_id, self::$fields_slug . $k, $v );
			}
		} else {
			update_post_meta( $post_id, self::$fields_slug . $key, $value );
		}
	}

	/**
	 * Update User Meta
	 *
	 * @param $user_id
	 * @param $key
	 * @param bool $value
	 */
	public static function set_user_meta( $user_id, $key, $value = false ) {
		if ( is_array( $key ) && $value === false ) {
			foreach ( $key as $k => $v ) {
				update_user_meta( $user_id, self::$fields_slug . $k, $v );
			}
		} else {
			update_user_meta( $user_id, self::$fields_slug . $key, $value );
		}
	}

	/**
	 * Set the data to cache
	 *
	 * @param $post_id
	 * @param $key
	 * @param bool $value
	 * @param bool $time
	 */
	public static function set_cache( $post_id, $key, $value = false, $time = false ) {
		$time = ! empty( $time ) ? time() + $time : time() + 60 * 60;
		self::set_post_meta( $post_id, 'cache_exp_' . $key, $time );
		self::set_post_meta( $post_id, 'cache_' . $key, $value );
	}

	/**
	 * Caheed value getter and setter
	 *
	 * @param $post_id
	 * @param $key
	 *
	 * @return bool|mixed
	 */
	public static function get_cache( $post_id, $key ) {
		$exp = self::get_meta( $post_id, 'cache_exp_' . $key );
		if ( $exp > time() ) {
			return self::get_meta( $post_id, 'cache_' . $key );
		} else {
			self::set_post_meta( $post_id, 'cache_' . $key, '' );
			self::set_post_meta( $post_id, 'cache_exp_' . $key, '' );

			return false;
		}
	}

	/**
	 * Flushing stored cache
	 *
	 * @param $post_id
	 * @param $key
	 */
	public static function flush_cache( $post_id, $key ) {
		self::set_post_meta( $post_id, 'cache_exp_' . $key, false );
		self::set_post_meta( $post_id, 'cache_' . $key, false );
	}

}