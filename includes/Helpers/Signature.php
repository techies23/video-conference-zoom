<?php
/**
 * @package     Video Conferencing with Zoom API
 * @subpackage  Helpers
 * @author      Deepen Bajracharya
 * @since       4.7.0
 */

namespace Codemanas\VczApi\Helpers;

/**
 * Builds the Meeting SDK signatures the Join-via-Browser client needs.
 *
 * The logic used to live as private methods on the admin-ajax controller, which
 * made it unreachable from the REST route. Keeping a single implementation
 * means the deprecated ajax handler and the REST route can never drift apart.
 */
final class Signature {

	/**
	 * Role assigned to every guest signature.
	 *
	 * Zoom role 1 is the host, which would let anyone who can reach the join
	 * page start a meeting. Guests are always role 0.
	 */
	public const ROLE_PARTICIPANT = 0;

	/**
	 * Not instantiable.
	 */
	private function __construct() {
	}

	/**
	 * Whether both SDK credentials are configured.
	 *
	 * @return bool
	 */
	public static function is_configured(): bool {
		return ! empty( get_option( 'vczapi_sdk_key' ) ) && ! empty( get_option( 'vczapi_sdk_secret_key' ) );
	}

	/**
	 * Sign a meeting for a guest.
	 *
	 * @param string $meeting_number Zoom meeting or webinar number.
	 * @param int $role Zoom role. Defaults to participant.
	 *
	 * @return string|false The signature, or false when it cannot be built.
	 */
	public static function for_meeting( string $meeting_number, int $role = self::ROLE_PARTICIPANT ) {
		$sdk_key    = (string) get_option( 'vczapi_sdk_key' );
		$secret_key = (string) get_option( 'vczapi_sdk_secret_key' );

		if ( empty( $sdk_key ) || empty( $secret_key ) ) {
			return false;
		}

		// Zoom rejects signatures whose iat sits in the future, so backdate by
		// 30 seconds to tolerate clock drift between the host and Zoom.
		$iat     = (int) round( ( ( time() * 1000 ) - 30000 ) / 1000 );
		$exp     = $iat + DAY_IN_SECONDS;
		$payload = array(
			'sdkKey'   => $sdk_key,
			'mn'       => $meeting_number,
			'role'     => $role,
			'iat'      => $iat,
			'exp'      => $exp,
			'appKey'   => $sdk_key,
			'tokenExp' => $exp,
		);

		if ( ! class_exists( '\Firebase\JWT\JWT' ) ) {
			return false;
		}

		return \Firebase\JWT\JWT::encode( $payload, $secret_key, 'HS256' );
	}

	/**
	 * Validate a meeting number as typed by a visitor.
	 *
	 * Zoom meeting IDs are 9 or 10 digits; personal meeting IDs and some
	 * webinar IDs are 11 digits, so anything up to 13 is accepted.
	 *
	 * @param mixed $meeting_number Raw request value.
	 *
	 * @return string The sanitised number, or an empty string when invalid.
	 */
	public static function sanitize_meeting_number( $meeting_number ): string {
		$meeting_number = preg_replace( '/\D+/', '', (string) $meeting_number );

		if ( ! is_string( $meeting_number ) || ! preg_match( '/^\d{9,13}$/', $meeting_number ) ) {
			return '';
		}

		return $meeting_number;
	}
}
