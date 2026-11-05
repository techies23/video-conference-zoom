<?php

namespace Codemanas\VczApi\Helpers;

/**
 * Issues and verifies short-lived tokens for the join-via-browser flow.
 *
 * The token is the authorisation object used by the unauthenticated
 * `vczapi_jvb_get_auth` AJAX action. It is bound to a single meeting ID
 * so a token obtained from one join link can never be used to generate
 * a signature for another meeting.
 *
 * @since 4.6.13
 */
final class JvbToken {

	/**
	 * Default token lifetime in seconds (12 hours).
	 */
	private const TTL = 43200;

	protected function __construct() {
	}

	/**
	 * Create a token for the given meeting ID.
	 *
	 * @param string $meeting_id Zoom meeting ID.
	 *
	 * @return string
	 */
	public static function issue( string $meeting_id ): string {
		$expires = time() + (int) apply_filters( 'vczapi_jvb_token_ttl', self::TTL );
		$payload = $meeting_id . '|' . $expires;
		$sig     = hash_hmac( 'sha256', $payload, self::secret() );

		return self::base64url_encode( $payload . '|' . $sig );
	}

	/**
	 * Verify a token and return the meeting ID it was issued for.
	 *
	 * @param string $token Token from the browser.
	 *
	 * @return string|null Meeting ID on success, null on failure.
	 */
	public static function verify( string $token ): ?string {
		$decoded = self::base64url_decode( $token );
		if ( '' === $decoded ) {
			return null;
		}

		$parts = explode( '|', $decoded );
		if ( 3 !== count( $parts ) ) {
			return null;
		}

		list( $meeting_id, $expires, $sig ) = $parts;

		if ( ! preg_match( '/^\d{9,11}$/', $meeting_id ) ) {
			return null;
		}

		if ( (int) $expires < time() ) {
			return null;
		}

		$expected = hash_hmac( 'sha256', $meeting_id . '|' . $expires, self::secret() );
		if ( ! hash_equals( $expected, $sig ) ) {
			return null;
		}

		return $meeting_id;
	}

	/**
	 * Secret used to sign tokens.
	 *
	 * @return string
	 */
	private static function secret(): string {
		$secret = get_option( '_vczapi_secret' );

		return ! empty( $secret ) ? $secret : wp_salt( 'auth' );
	}

	/**
	 * URL-safe base64 encoding.
	 *
	 * @param string $data Raw data.
	 *
	 * @return string
	 */
	private static function base64url_encode( string $data ): string {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}

	/**
	 * URL-safe base64 decoding.
	 *
	 * @param string $data Encoded data.
	 *
	 * @return string
	 */
	private static function base64url_decode( string $data ): string {
		$data = strtr( $data, '-_', '+/' );
		$data .= str_repeat( '=', ( 4 - strlen( $data ) % 4 ) % 4 );

		return base64_decode( $data, true ) ?: '';
	}
}
