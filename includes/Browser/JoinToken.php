<?php
/**
 * @package     Video Conferencing with Zoom API
 * @subpackage  Browser
 * @author      Deepen Bajracharya
 * @since       4.9.0
 */

namespace Codemanas\VczApi\Browser;

use Codemanas\VczApi\Helpers\Encryption;
use Codemanas\VczApi\Helpers\Signature;

/**
 * A signed, expiring capability token that authorises exactly one meeting join.
 *
 * The Join-via-Browser link used to carry a raw AES ciphertext of the meeting
 * number in `?join=`. That value was self-issued but *unbound*: it could be
 * lifted from any public join link and replayed against any other meeting post
 * or archive URL, because nothing tied it to a post, an expiry, or a password.
 *
 * A token fixes that by signing the full set of claims with a server secret:
 *
 *   base64url( json claims ) . '.' . base64url( hmac-sha256( claims, key ) )
 *
 * Consequences that matter:
 *
 *  - The signature endpoint can require a token, so the SDK secret stops being
 *    a signing oracle for arbitrary meeting numbers.
 *  - A token is bound to `post_id`, so a token minted for meeting A cannot be
 *    replayed against meeting B.
 *  - Tokens expire, unlike the old links which were valid forever.
 *
 * The meeting password is carried *encrypted* (`Encryption::encrypt`) rather
 * than in the clear, which is what the old `pak` parameter did. The plaintext
 * is only ever produced server-side, for a caller that already proved it holds
 * a valid token, inside the signature endpoint's JSON response.
 *
 * @since 4.9.0
 */
final class JoinToken {

	/**
	 * Token format version. Bump when the claim shape changes incompatibly so
	 * that tokens minted by an older release are rejected rather than
	 * misinterpreted.
	 */
	public const VERSION = 1;

	/**
	 * Default lifetime in seconds (30 days).
	 *
	 * Join links get emailed and pasted into posts, so the window has to be
	 * generous. It is still an improvement on the previous behaviour, where a
	 * link never expired at all.
	 */
	public const DEFAULT_LIFETIME = MONTH_IN_SECONDS;

	/**
	 * Token payload.
	 *
	 * @var array
	 */
	private array $claims;

	/**
	 * @param array $claims Decoded, already validated claims.
	 */
	private function __construct( array $claims ) {
		$this->claims = $claims;
	}

	/**
	 * Mint a token for a meeting.
	 *
	 * @param int    $post_id        Meeting post the token is bound to. 0 for
	 *                               shortcode/archive links that have no post.
	 * @param string $meeting_number Zoom meeting or webinar number.
	 * @param string $password       Meeting passcode, if the host set one.
	 * @param array  $extra          Additional claims. Supported keys are
	 *                               `tk` (registrant token) and `dj` (direct join).
	 *
	 * @return string The signed token, or an empty string when the meeting
	 *                number is not a plausible Zoom ID.
	 */
	public static function issue( int $post_id, string $meeting_number, string $password = '', array $extra = [] ): string {
		$meeting_number = Signature::sanitize_meeting_number( $meeting_number );

		if ( '' === $meeting_number ) {
			return '';
		}

		$issued_at = time();
		$claims    = array(
			'v'     => self::VERSION,
			'pid'   => $post_id,
			'mn'    => $meeting_number,
			'iat'   => $issued_at,
			'exp'   => $issued_at + self::lifetime(),
		);

		// The password travels encrypted so the token can sit in a URL without
		// exposing it. It is only decrypted for token holders.
		if ( '' !== $password ) {
			$claims['pwd'] = Encryption::encrypt( $password );
		}

		if ( ! empty( $extra['tk'] ) && is_scalar( $extra['tk'] ) ) {
			$claims['tk'] = substr( (string) $extra['tk'], 0, 512 );
		}

		// Coerce explicitly. `direct_join` arrives from a query string, and the
		// old code did `(bool) $_GET['direct_join']`, which is true for the
		// string "0" — so `?direct_join=0` still skipped the join form.
		if ( self::to_bool( $extra['dj'] ?? false ) ) {
			$claims['dj'] = 1;
		}

		/**
		 * Filter the claims before a join token is signed.
		 *
		 * Anything added here is covered by the HMAC, so it is as trustworthy
		 * as the built-in claims.
		 *
		 * @param array  $claims        Token claims.
		 * @param int    $post_id       Meeting post id.
		 * @param string $meeting_number Zoom meeting number.
		 *
		 * @since 4.9.0
		 */
		$claims = (array) apply_filters( 'vczapi_join_token_claims', $claims, $post_id, $meeting_number );

		ksort( $claims );

		return self::base64url_encode( (string) wp_json_encode( $claims ) ) . '.' . self::sign( $claims );
	}

	/**
	 * Verify a token and return it.
	 *
	 * Rejects: malformed input, unknown version, a bad signature, an expired
	 * token, or a token whose meeting number is not a plausible Zoom ID. Any
	 * failure returns null, so callers only have to null-check.
	 *
	 * @param string $token Raw token from the request.
	 *
	 * @return JoinToken|null
	 */
	public static function parse( string $token ): ?JoinToken {
		$token = trim( $token );

		if ( '' === $token || substr_count( $token, '.' ) !== 1 ) {
			return null;
		}

		list( $encoded_claims, $encoded_signature ) = explode( '.', $token, 2 );

		$json = self::base64url_decode( $encoded_claims );
		$signature = self::base64url_decode( $encoded_signature );

		if ( '' === $json || '' === $signature ) {
			return null;
		}

		$claims = json_decode( $json, true );

		if ( ! is_array( $claims ) ) {
			return null;
		}

		ksort( $claims );

		// Compare the raw HMAC bytes, so the check covers exactly the claims we
		// serialised and is not sensitive to base64url normalisation.
		if ( ! hash_equals( self::hmac( $claims ), $signature ) ) {
			return null;
		}

		if ( (int) ( $claims['v'] ?? 0 ) !== self::VERSION ) {
			return null;
		}

		if ( time() > (int) ( $claims['exp'] ?? 0 ) ) {
			return null;
		}

		$meeting_number = Signature::sanitize_meeting_number( $claims['mn'] ?? '' );

		if ( '' === $meeting_number ) {
			return null;
		}

		$claims['mn'] = $meeting_number;
		$claims['pid'] = absint( $claims['pid'] ?? 0 );

		return new self( $claims );
	}

	/**
	 * Post the token is bound to. 0 when the link came from a shortcode.
	 */
	public function post_id(): int {
		return (int) ( $this->claims['pid'] ?? 0 );
	}

	/**
	 * Zoom meeting or webinar number.
	 */
	public function meeting_number(): string {
		return (string) ( $this->claims['mn'] ?? '' );
	}

	/**
	 * Whether the link should skip the join form.
	 */
	public function direct_join(): bool {
		return ! empty( $this->claims['dj'] );
	}

	/**
	 * Zoom registrant token, for joining a registered meeting.
	 */
	public function registrant_token(): string {
		return isset( $this->claims['tk'] ) ? (string) $this->claims['tk'] : '';
	}

	/**
	 * Unix timestamp the token expires at.
	 */
	public function expires_at(): int {
		return (int) ( $this->claims['exp'] ?? 0 );
	}

	/**
	 * Decrypt the meeting password carried by the token.
	 *
	 * Returns an empty string when the host set no passcode or the ciphertext
	 * has been tampered with. A decryption failure is not distinguishable from
	 * an absent password for AES-CBC without a MAC, which is acceptable here:
	 * a forged ciphertext cannot pass the HMAC check in `parse()` in the first
	 * place, so the only way to reach a bad `pwd` claim is to already hold the
	 * signing key.
	 */
	public function password(): string {
		if ( empty( $this->claims['pwd'] ) || ! is_string( $this->claims['pwd'] ) ) {
			return '';
		}

		$password = Encryption::decrypt( $this->claims['pwd'] );

		return is_string( $password ) ? $password : '';
	}

	/**
	 * Build the canonical join URL for this token.
	 */
	public function to_url(): string {
		return JoinViaBrowser::url_for( (string) $this->to_string() );
	}

	/**
	 * Serialise back to the wire format.
	 */
	public function to_string(): string {
		$claims = $this->claims;
		ksort( $claims );

		return self::base64url_encode( (string) wp_json_encode( $claims ) ) . '.' . self::sign( $claims );
	}

	/**
	 * Sign a sorted claim set.
	 *
	 * @param array $claims Claims, already sorted.
	 *
	 * @return string base64url encoded signature.
	 */
	private static function sign( array $claims ): string {
		return self::base64url_encode( self::hmac( $claims ) );
	}

	/**
	 * Raw HMAC over the serialised claims.
	 *
	 * @param array $claims Claims, already sorted.
	 */
	private static function hmac( array $claims ): string {
		return hash_hmac( 'sha256', (string) wp_json_encode( $claims ), self::key(), true );
	}

	/**
	 * Signing key.
	 *
	 * `wp_salt('auth')` is used rather than a plugin option so the token
	 * survives option deletion and stays in sync with WordPress' own secret
	 * rotation. A site that changes its salts invalidates outstanding join
	 * links, which is the correct trade-off for a capability URL.
	 */
	private static function key(): string {
		return (string) wp_salt( 'auth' );
	}

	/**
	 * Token lifetime in seconds.
	 */
	private static function lifetime(): int {
		/**
		 * Filter how long a Join-via-Browser token stays valid.
		 *
		 * @param int $lifetime Lifetime in seconds.
		 *
		 * @since 4.9.0
		 */
		$lifetime = (int) apply_filters( 'vczapi_join_token_lifetime', self::DEFAULT_LIFETIME );

		// Guard against a filter returning zero or a negative value, which would
		// mint tokens that are already expired.
		return $lifetime > 0 ? $lifetime : self::DEFAULT_LIFETIME;
	}

	/**
	 * Interpret a loosely-typed flag as a boolean.
	 *
	 * Only the literal `1` and the strings `"1"`/`"true"` count as true, so
	 * `"0"`, `"false"`, `""` and `null` are all false. This is deliberately
	 * stricter than PHP truthiness, which treats `"0"` as true.
	 *
	 * @param mixed $value Raw flag value.
	 */
	private static function to_bool( $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_string( $value ) ) {
			return in_array( strtolower( trim( $value ) ), array( '1', 'true' ), true );
		}

		return 1 === $value;
	}

	/**
	 * URL-safe base64 encode.
	 */
	private static function base64url_encode( string $value ): string {
		return rtrim( strtr( base64_encode( $value ), '+/', '-_' ), '=' );
	}

	/**
	 * URL-safe base64 decode.
	 */
	private static function base64url_decode( string $value ): string {
		$remainder = strlen( $value ) % 4;

		if ( $remainder ) {
			$value .= str_repeat( '=', 4 - $remainder );
		}

		$decoded = base64_decode( strtr( $value, '-_', '+/' ), true );

		return false === $decoded ? '' : $decoded;
	}
}
