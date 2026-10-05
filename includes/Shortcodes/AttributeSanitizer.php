<?php

namespace Codemanas\VczApi\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Shortcode attribute sanitizers.
 *
 * Every value that reaches a shortcode callback, an AJAX payload or the Zoom API
 * passes through here. These were previously duplicated as private methods on
 * each of the four shortcode classes; they now have a single home.
 *
 * All methods are stateless.
 *
 * @package Codemanas\VczApi\Shortcodes
 */
class AttributeSanitizer {

	/**
	 * Cast a value to string, rejecting arrays and objects.
	 *
	 * Shortcode attributes arrive from three untrusted sources (the post content,
	 * `$_GET`/`$_POST`, and `filter_input()`), so array/object input is treated
	 * as absent rather than being silently stringified.
	 *
	 * @param mixed  $value   Value to normalize.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	public static function scalar( $value, string $default = '' ): string {
		if ( is_array( $value ) || is_object( $value ) ) {
			return $default;
		}

		return (string) $value;
	}

	/**
	 * Normalize a yes/no value.
	 *
	 * @param mixed  $value   Value to normalize.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	public static function yes_no( $value, string $default = 'no' ): string {
		$value = strtolower( self::scalar( $value, $default ) );

		return in_array( $value, [ 'yes', 'no' ], true ) ? $value : $default;
	}

	/**
	 * Normalize a true/false value to the string form used in templates.
	 *
	 * @param mixed  $value   Value to normalize.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	public static function true_false( $value, string $default = 'true' ): string {
		if ( is_bool( $value ) ) {
			return $value ? 'true' : 'false';
		}

		$value = strtolower( self::scalar( $value, $default ) );

		if ( in_array( $value, [ '1', 'yes', 'true' ], true ) ) {
			return 'true';
		}

		if ( in_array( $value, [ '0', 'no', 'false' ], true ) ) {
			return 'false';
		}

		return $default;
	}

	/**
	 * Normalize a sort order.
	 *
	 * @param mixed  $value   Value to normalize.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	public static function order( $value, string $default = 'DESC' ): string {
		$value = strtoupper( self::scalar( $value, $default ) );

		return in_array( $value, [ 'ASC', 'DESC' ], true ) ? $value : $default;
	}

	/**
	 * Normalize a list type filter.
	 *
	 * @param mixed  $value   Value to normalize.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	public static function list_type( $value, string $default = '' ): string {
		$value = strtolower( self::scalar( $value, $default ) );

		return in_array( $value, [ 'upcoming', 'past' ], true ) ? $value : $default;
	}

	/**
	 * Normalize the meeting post template name.
	 *
	 * @param mixed  $value   Value to normalize.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	public static function template_name( $value, string $default = '' ): string {
		$value = strtolower( self::scalar( $value, $default ) );

		return in_array( $value, [ 'boxed', 'none', '' ], true ) ? $value : $default;
	}

	/**
	 * Sanitize a Zoom numeric meeting/webinar ID.
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return string
	 */
	public static function numeric_id( $value ): string {
		return preg_replace( '/[^0-9]/', '', self::scalar( $value ) );
	}

	/**
	 * Sanitize a Zoom host identifier.
	 *
	 * Zoom host IDs are expected to be alphanumeric API identifiers or email-like
	 * identifiers. This intentionally strips shortcode/HTML syntax.
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return string
	 */
	public static function host_id( $value ): string {
		return preg_replace( '/[^A-Za-z0-9_\-@.]/', '', self::scalar( $value ) );
	}

	/**
	 * Sanitize a Zoom meeting/recording identifier.
	 *
	 * Recording lookup may use numeric meeting IDs or Zoom UUID-like values. Zoom
	 * UUIDs can contain characters such as slash, plus, equals, underscore, and
	 * hyphen, so this is intentionally broader than numeric meeting ID handling.
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return string
	 */
	public static function recording_identifier( $value ): string {
		return preg_replace( '/[^A-Za-z0-9_\-@.\/+=]/', '', self::scalar( $value ) );
	}

	/**
	 * Sanitize a Zoom meeting passcode.
	 *
	 * Zoom meeting passcodes are limited to 10 characters and may contain
	 * alphanumeric characters and special characters. Avoid display-oriented
	 * sanitizers because passcodes are credentials, not HTML display text.
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return string
	 */
	public static function passcode( $value ): string {
		$passcode = str_replace(
			[ "\r", "\n", "\t", ']' ],
			'',
			self::scalar( $value )
		);

		return function_exists( 'mb_substr' ) ? mb_substr( $passcode, 0, 10 ) : substr( $passcode, 0, 10 );
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
	public static function css_length( $value, string $default = '500px' ): string {
		$value = trim( self::scalar( $value, $default ) );

		if ( '' === $value ) {
			return $default;
		}

		if ( preg_match( '/^\d+(?:\.\d+)?(?:px|em|rem|vh|vw|%)?$/', $value ) ) {
			return $value;
		}

		return $default;
	}

	/**
	 * Sanitize a positive integer, falling back to a default.
	 *
	 * @param mixed $value   Value to sanitize.
	 * @param int   $default Default value.
	 *
	 * @return int
	 */
	public static function positive_int( $value, int $default = 0 ): int {
		$value = absint( $value );

		return ! empty( $value ) ? $value : $default;
	}

	/**
	 * Sanitize a comma-separated category slug list.
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return array
	 */
	public static function category_slugs( $value ): array {
		$value = self::scalar( $value );

		if ( '' === $value ) {
			return [];
		}

		$categories = array_map( 'trim', explode( ',', $value ) );
		$categories = array_map( 'sanitize_title', $categories );
		$categories = array_filter( $categories );

		return array_values( array_unique( $categories ) );
	}

	/**
	 * Sanitize category slugs from an array input.
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return array
	 */
	public static function category_slug_array( $value ): array {
		if ( ! is_array( $value ) ) {
			return [];
		}

		$categories = array_map( 'sanitize_title', array_map( 'trim', $value ) );
		$categories = array_filter( $categories );

		return array_values( array_unique( $categories ) );
	}
}