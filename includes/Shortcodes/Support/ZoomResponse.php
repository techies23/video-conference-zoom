<?php

namespace Codemanas\VczApi\Shortcodes\Support;

use ArrayObject;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Response container handed to shortcode templates.
 *
 * Since 4.7.0 the Zoom API facade (`zoom_conference_v2()`) returns associative
 * arrays, whereas the legacy `zoom_conference()` layer returned JSON strings
 * that every shortcode `json_decode()`d into `stdClass`. Templates shipped with
 * the plugin now use array access.
 *
 * This wrapper is the backwards compatibility shim: it implements `ArrayAccess`
 * (the supported style) while still resolving `->` property access so that
 * themes that copied `templates/shortcode/*.php` before 4.7.0 keep rendering.
 *
 * @deprecated 4.7.0 Use array access. Property access will be removed in 5.0.0.
 *
 * @package Codemanas\VczApi\Shortcodes\Support
 */
class ZoomResponse extends ArrayObject {

	/**
	 * Keys whose values are API collections and therefore need wrapping too,
	 * so `foreach ( $response->meetings as $item ) { $item->topic }` keeps working.
	 *
	 * @var string[]
	 */
	private const COLLECTION_KEYS = [ 'meetings', 'webinars', 'occurrences', 'recording_files' ];

	/**
	 * Property names already reported, to keep debug logs readable.
	 *
	 * @var array<string,bool>
	 */
	private static array $reported = [];

	/**
	 * Wrap an API response, tolerating `null`, `stdClass` and `WP_Error`.
	 *
	 * @param mixed $data Raw response payload.
	 *
	 * @return self|null
	 */
	public static function make( $data ) {
		if ( is_wp_error( $data ) ) {
			return null;
		}

		if ( $data ) {
			return $data;
		}

		if ( is_object( $data ) ) {
			$data = get_object_vars( $data );
		}

		if ( ! is_array( $data ) ) {
			return null;
		}

		$response = new self( $data );

		foreach ( self::COLLECTION_KEYS as $key ) {
			$response->wrapCollection( $key );
		}

		return $response;
	}

	/**
	 * Wrap an API response together with its nested collections.
	 *
	 * @param mixed $data Raw response payload.
	 *
	 * @return array|null
	 */
	public static function makeList( $data ): ?array {
		if ( is_object( $data ) && ! ( $data instanceof self ) ) {
			$data = get_object_vars( $data );
		}

		if ( ! is_array( $data ) ) {
			return null;
		}

		$list = [];
		foreach ( $data as $item ) {
			$list[] = self::make( $item );
		}

		return $list;
	}

	/**
	 * Normalize any supported response shape into a plain array.
	 *
	 * Shortcode callbacks hand templates a `ZoomResponse`, but a theme override (or
	 * an add-on calling a public callback directly) may hand over a plain array,
	 * a `stdClass` from the pre-4.7.0 `json_decode()` layer, or nothing at all.
	 * Templates use this instead of `is_array()` so a `ZoomResponse` is never
	 * mistaken for "no response".
	 *
	 * @param mixed $data Response payload in any supported shape.
	 *
	 * @return array
	 */
	public static function to_array( $data ): array {
		if ( null === $data || is_wp_error( $data ) ) {
			return [];
		}

		if ( $data instanceof ArrayObject ) {
			return $data->getArrayCopy();
		}

		if ( is_object( $data ) ) {
			return get_object_vars( $data );
		}

		return is_array( $data ) ? $data : [];
	}

	/**
	 * Replace every element of a collection key with a wrapped instance.
	 *
	 * @param string $key Response key.
	 *
	 * @return self
	 */
	public function wrapCollection( string $key ): self {
		if ( ! $this->offsetExists( $key ) || ! is_array( $this->offsetGet( $key ) ) ) {
			return $this;
		}

		$this->offsetSet( $key, self::makeList( $this->offsetGet( $key ) ) );

		return $this;
	}

	/**
	 * @param string $name Property name.
	 *
	 * @return mixed
	 */
	public function __get( string $name ) {
		if ( ! $this->offsetExists( $name ) ) {
			return null;
		}

		$this->report_deprecated_access( $name );

		return $this->offsetGet( $name );
	}

	/**
	 * @param string $name  Property name.
	 * @param mixed  $value Property value.
	 *
	 * @return void
	 */
	public function __set( string $name, $value ) {
		$this->report_deprecated_access( $name );

		$this->offsetSet( $name, $value );
	}

	/**
	 * @param string $name Property name.
	 *
	 * @return bool
	 */
	public function __isset( string $name ): bool {
		return $this->offsetExists( $name );
	}

	/**
	 * @param string $name Property name.
	 *
	 * @return void
	 */
	public function __unset( string $name ) {
		$this->offsetUnset( $name );
	}

	/**
	 * Report property access once per property name.
	 *
	 * @param string $name Property name.
	 *
	 * @return void
	 */
	private function report_deprecated_access( string $name ): void {
		if ( isset( self::$reported[ $name ] ) || ! function_exists( '_deprecated_argument' ) ) {
			return;
		}

		self::$reported[ $name ] = true;

		_deprecated_argument(
			sprintf( '%s::$%s', static::class, $name ),
			'5.0.0',
			sprintf( '%s::%s() is deprecated, use array access instead.', static::class, $name )
		);
	}
}