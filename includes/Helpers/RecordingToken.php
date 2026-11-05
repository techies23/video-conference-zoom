<?php

namespace Codemanas\VczApi\Helpers;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Issues and verifies scoped tokens for cloud recording requests.
 *
 * A token proves that a recording shortcode was rendered on a specific post
 * and therefore that the requested meeting / recording is published on the
 * site. Requests without a valid token are rejected before any Zoom API call
 * is made.
 *
 * @since 4.6.13
 */
class RecordingToken {

	/**
	 * Token format version.
	 */
	private const VERSION = 'v1';

	/**
	 * Domain separation prefix for the HMAC input.
	 */
	private const HMAC_PREFIX = 'vczapi-recording-token|';

	/**
	 * Supported token scopes.
	 */
	private const SCOPES = array( 'meeting', 'recording', 'host' );

	/**
	 * Issue a signed token.
	 *
	 * @param string $scope   One of "meeting", "recording" or "host".
	 * @param string $id      Meeting ID, recording UUID or host ID.
	 * @param int    $post_id Post that rendered the shortcode. 0 when unknown.
	 * @param array  $options Optional "passcode" / "downloadable" yes/no values.
	 *
	 * @return string Empty string when the token could not be created.
	 */
	public static function issue( string $scope, string $id, int $post_id = 0, array $options = array() ): string {
		$scope = strtolower( $scope );
		$id    = trim( $id );

		if ( empty( $id ) || ! in_array( $scope, self::SCOPES, true ) ) {
			return '';
		}

		$ttl = (int) apply_filters( 'vczapi_recording_token_ttl', 7 * DAY_IN_SECONDS );
		if ( $ttl <= 0 ) {
			$ttl = 7 * DAY_IN_SECONDS;
		}

		$payload = array(
			'v'            => 1,
			'scope'        => $scope,
			'id'           => $id,
			'post'         => absint( $post_id ),
			'passcode'     => self::yes_no( $options['passcode'] ?? 'no' ),
			'downloadable' => self::yes_no( $options['downloadable'] ?? 'no' ),
			'exp'          => time() + $ttl,
		);

		$json = wp_json_encode( $payload );
		if ( false === $json ) {
			return '';
		}

		$encoded   = self::base64url_encode( $json );
		$signature = self::base64url_encode(
			hash_hmac( 'sha256', self::HMAC_PREFIX . $encoded, self::secret(), true )
		);

		return self::VERSION . '.' . $encoded . '.' . $signature;
	}

	/**
	 * Verify a token and return its payload.
	 *
	 * @param string $token
	 *
	 * @return array|false False when the token is missing, malformed, tampered
	 *                     with or expired.
	 */
	public static function verify( string $token ): array|false {
		$token = trim( $token );
		if ( empty( $token ) ) {
			return false;
		}

		$parts = explode( '.', $token );
		if ( 3 !== count( $parts ) || self::VERSION !== $parts[0] ) {
			return false;
		}

		$encoded   = $parts[1];
		$signature = $parts[2];
		$expected  = self::base64url_encode(
			hash_hmac( 'sha256', self::HMAC_PREFIX . $encoded, self::secret(), true )
		);

		if ( ! hash_equals( $expected, $signature ) ) {
			return false;
		}

		$json = self::base64url_decode( $encoded );
		if ( false === $json ) {
			return false;
		}

		$payload = json_decode( $json, true );
		if ( ! is_array( $payload ) ) {
			return false;
		}

		if ( empty( $payload['scope'] ) || ! in_array( $payload['scope'], self::SCOPES, true ) ) {
			return false;
		}

		if ( ! isset( $payload['id'] ) || '' === (string) $payload['id'] ) {
			return false;
		}

		$expires = isset( $payload['exp'] ) ? (int) $payload['exp'] : 0;
		if ( $expires <= time() ) {
			return false;
		}

		$payload['id']   = (string) $payload['id'];
		$payload['post'] = isset( $payload['post'] ) ? absint( $payload['post'] ) : 0;

		return $payload;
	}

	/**
	 * Normalize a yes/no option.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	private static function yes_no( $value ): string {
		$value = strtolower( (string) $value );

		return in_array( $value, array( 'yes', 'no' ), true ) ? $value : 'no';
	}

	/**
	 * Secret used to sign recording tokens.
	 *
	 * @return string
	 */
	private static function secret(): string {
		$secret = get_option( '_vczapi_secret' );
		if ( empty( $secret ) ) {
			$secret = wp_salt( 'auth' );
		}

		return (string) $secret;
	}

	/**
	 * URL-safe base64 encode.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	private static function base64url_encode( string $value ): string {
		return rtrim( strtr( base64_encode( $value ), '+/', '-_' ), '=' );
	}

	/**
	 * URL-safe base64 decode.
	 *
	 * @param string $value
	 *
	 * @return string|false
	 */
	private static function base64url_decode( string $value ) {
		$remainder = strlen( $value ) % 4;
		if ( $remainder ) {
			$value .= str_repeat( '=', 4 - $remainder );
		}

		return base64_decode( strtr( $value, '-_', '+/' ), true );
	}
}
