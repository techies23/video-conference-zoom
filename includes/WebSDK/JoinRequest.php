<?php
/**
 * @package     Video Conferencing with Zoom API
 * @subpackage  Browser
 * @author      Deepen Bajracharya
 * @since       4.7.0
 */

namespace Codemanas\VczApi\WebSDK;

use Codemanas\VczApi\Admin\Repository\SettingsRepository;
use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\Common;
use Codemanas\VczApi\Helpers\Encryption;
use Codemanas\VczApi\Helpers\MeetingType;
use Codemanas\VczApi\Helpers\Signature;
use WP_Post;

/**
 * The single place where a Join-via-Browser request is read and validated.
 *
 * Previously the join page read `$_GET['join']`, `$_GET['pak']`, `$_GET['tk']`
 * and `$_GET['direct_join']` inline, in the template footer, with no
 * cross-checking and no error handling. Consequences:
 *
 *  - `Encryption::decrypt()` returning false was passed straight to
 *    `base64_encode()`, so a tampered link silently produced an empty meeting
 *    number instead of an error.
 *  - Nothing confirmed that the meeting in the URL belonged to the post being
 *    viewed, so a ciphertext lifted from any public join link could be replayed
 *    anywhere.
 *  - `(bool) $_GET['direct_join']` is true for the string `"0"`, so
 *    `?direct_join=0` still forced a join and skipped the form.
 *
 * This class removes all of that: it accepts a signed token (canonical) or the
 * legacy query arguments (migrated once, then redirected), and returns either a
 * validated `JoinToken` or a `WP_Error` explaining precisely why not.
 *
 * @since 4.9.0
 */
final class JoinRequest {

	/**
	 * Validated token.
	 *
	 * @var JoinToken|null
	 */
	private ?JoinToken $token;

	/**
	 * Reason the request was rejected, or null when it is valid.
	 *
	 * @var string
	 */
	private string $error = '';

	/**
	 * @param JoinToken|null $token Validated token, or null when rejected.
	 * @param string $error Human readable rejection reason.
	 */
	private function __construct( ?JoinToken $token, string $error = '' ) {
		$this->token = $token;
		$this->error = $error;
	}

	/**
	 * Resolve the current request.
	 *
	 * @return JoinRequest
	 */
	public static function from_request(): JoinRequest {
		$raw = self::raw_token_from_request();

		if ( '' === $raw ) {
			return self::rejected( __( 'This join link is missing its access token. Please ask the organiser for a new link.', 'video-conferencing-with-zoom-api' ) );
		}

		$token = JoinToken::parse( $raw );

		// A token-shaped value that fails verification is either expired or
		// forged. The old links used an undelimited AES ciphertext, so we can
		// tell them apart and migrate them instead of showing an error.
		if ( null === $token ) {
			$migrated = self::migrate_legacy_link( $raw );

			if ( null !== $migrated ) {
				return new self( $migrated );
			}

			return self::rejected( __( 'This join link is no longer valid. It may have expired or been changed — please ask the organiser for a new link.', 'video-conferencing-with-zoom-api' ) );
		}

		return self::validate( $token );
	}

	/**
	 * Build a request around an already-parsed token.
	 *
	 * Used by the signature endpoint, which receives the token in a POST body
	 * rather than a URL.
	 *
	 * @param string $raw Raw token.
	 *
	 * @return JoinRequest
	 */
	public static function from_token_string( string $raw ): JoinRequest {
		$token = JoinToken::parse( $raw );

		if ( null === $token ) {
			return self::rejected( __( 'This join link is no longer valid. It may have expired or been changed — please ask the organiser for a new link.', 'video-conferencing-with-zoom-api' ) );
		}

		return self::validate( $token );
	}

	/**
	 * Whether the request is good to join.
	 */
	public function is_valid(): bool {
		return null !== $this->token;
	}

	/**
	 * Validated token, or null.
	 */
	public function token(): ?JoinToken {
		return $this->token;
	}

	/**
	 * Rejection reason.
	 */
	public function error(): string {
		return $this->error;
	}

	/**
	 * Meeting post the token is bound to, if any.
	 *
	 * @return WP_Post|null
	 */
	public function post(): ?WP_Post {
		if ( null === $this->token || 0 === $this->token->post_id() ) {
			return null;
		}

		$post = get_post( $this->token->post_id() );

		return $post instanceof WP_Post ? $post : null;
	}

	/**
	 * Check is webinar
	 *
	 * @return bool
	 */
	public function is_webinar(): bool {
		$meetingType = Metastore::getPostMeta( $this->token->post_id(), 'meeting_type' );

		return ! empty( $meetingType ) && $meetingType == "webinar";
	}

	/**
	 * Default language for the Zoom event.
	 *
	 * @return string
	 */
	public function default_lang(): string {
		$lang = Metastore::get_plugin_settings( "join_via_browser_default_lang" );

		return ! empty( $lang ) ? $lang : 'en-US';
	}

	/**
	 * Hide Email Address
	 *
	 * @return bool
	 */
	public function hide_email_address(): bool {
		$hide = Metastore::get_plugin_settings( "hide_email_jvb" );

		return ! empty( $hide );
	}

	/**
	 * Current logged in user name.
	 *
	 * @return string
	 */
	public function get_user_name(): string {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$user      = wp_get_current_user();
		$full_name = trim( "{$user->first_name} {$user->last_name}" );

		return '' !== $full_name ? $full_name : $user->display_name;
	}

	/**
	 * Whether the host set a passcode on this meeting.
	 */
	public function has_password(): bool {
		return '' !== $this->token()->password();
	}

	/**
	 * The meeting topic, preferring live post meta over anything cached in the token.
	 *
	 * The token deliberately does not carry the topic: a token outlives any
	 * number of meeting edits, so a renamed meeting would keep showing the
	 * original subject forever.
	 */
	public function topic(): string {
		$post = $this->post();

		if ( $post ) {
			$details = Metastore::getPostMeta( $post->ID, 'meeting_zoom_details' );

			if ( ! empty( $details['topic'] ) ) {
				return (string) $details['topic'];
			}
		}

		return '';
	}

	/**
	 * Apply every server-side gate a token must pass to authorise a join.
	 *
	 * The point of this method is that signature verification alone is not
	 * enough. A token is a capability, so it also has to still be *wanted*:
	 * the feature must be enabled, the SDK must be configured, and the token
	 * must not have drifted away from the post it claims to belong to.
	 *
	 * @param JoinToken $token Verified token.
	 */
	private static function validate( JoinToken $token ): JoinRequest {
		if ( ! Common::validateSDKCredentials() ) {
			return self::rejected( __( 'Joining in the browser is not available right now. Please use the Zoom app or desktop client instead.', 'video-conferencing-with-zoom-api' ) );
		}

		if ( ! class_exists( '\Firebase\JWT\JWT' ) ) {
			return self::rejected( __( 'Joining in the browser is temporarily unavailable. Please use the Zoom app or desktop client instead.', 'video-conferencing-with-zoom-api' ) );
		}

		// The join-via-browser kill switch. Previously this was only consulted
		// when *generating* a link, so turning it off left every already-shared
		// link fully working.
		if ( Metastore::checkDisableJoinViaBrowser() ) {
			return self::rejected( __( 'Joining in the browser has been disabled for this site. Please use the Zoom app or desktop client instead.', 'video-conferencing-with-zoom-api' ) );
		}

		$post_id = $token->post_id();

		// A token minted for a post must still belong to that post, and must
		// still name the meeting the post actually points at. This is what
		// stops a token being replayed against a different meeting.
		if ( $post_id > 0 ) {
			$post = get_post( $post_id );

			if ( ! $post instanceof WP_Post || 'zoom-meetings' !== $post->post_type ) {
				return self::rejected( __( 'This meeting is no longer available. Please use the Zoom app or desktop client instead.', 'video-conferencing-with-zoom-api' ) );
			}

			$expected = self::meeting_number_for_post( $post );

			if ( '' === $expected ) {
				return self::rejected( __( 'This meeting is no longer available. Please use the Zoom app or desktop client instead.', 'video-conferencing-with-zoom-api' ) );
			}

			if ( ! hash_equals( $expected, $token->meeting_number() ) ) {
				return self::rejected( __( 'This join link does not match this meeting. Please ask the organiser for a new link.', 'video-conferencing-with-zoom-api' ) );
			}
		}

		/**
		 * Filter whether a verified join token may proceed to join.
		 *
		 * Returning false blocks the join. Runs after the built-in gates so an
		 * add-on can layer on its own rules (membership, email domain, capacity)
		 * without having to re-implement signature verification.
		 *
		 * @param bool $allowed Whether the join may proceed.
		 * @param JoinToken $token Verified token.
		 *
		 * @since 4.9.0
		 */
		if ( ! apply_filters( 'vczapi_join_token_allowed', true, $token ) ) {
			return self::rejected( __( 'You are not authorised to join this meeting. Please log in or contact the organiser.', 'video-conferencing-with-zoom-api' ) );
		}

		return new self( $token );
	}

	/**
	 * The meeting number a post currently points at.
	 *
	 * Prefers the PMI, matching how Links builds join links, because that is
	 * the number the host actually dials.
	 */
	private static function meeting_number_for_post( WP_Post $post ): string {
		$details = Metastore::getPostMeta( $post->ID, 'meeting_zoom_details' );

		if ( empty( $details ) || ! is_array( $details ) ) {
			return '';
		}

		$number = ! empty( $details['pmi'] ) ? $details['pmi'] : ( $details['id'] ?? '' );

		return Signature::sanitize_meeting_number( $number );
	}

	/**
	 * Read the token out of the current request.
	 *
	 * Two shapes are accepted:
	 *  - the canonical `?vczapi_join=<token>` query var on the dedicated route
	 *  - legacy `?type=meeting&join=<ciphertext>` links, handled by migrate_legacy_link
	 */
	private static function raw_token_from_request(): string {
		$raw = get_query_var( JoinViaBrowser::QUERY_VAR );

		if ( is_string( $raw ) && '' !== $raw ) {
			return $raw;
		}

		if ( isset( $_GET[ JoinViaBrowser::QUERY_VAR ] ) ) {
			return sanitize_text_field( wp_unslash( $_GET[ JoinViaBrowser::QUERY_VAR ] ) );
		}

		if ( isset( $_GET['join'] ) ) {
			return sanitize_text_field( wp_unslash( $_GET['join'] ) );
		}

		return '';
	}

	/**
	 * Convert a pre-4.9 join link into a signed token.
	 *
	 * Old links looked like `?type=meeting&join=<aes>&pak=<aes>`. The ciphertexts
	 * are self-issued but unbound, so rather than trust them directly we
	 * re-issue an equivalent token that *is* bound, and the caller redirects to
	 * it. This keeps every link already in someone's inbox working while making
	 * the token the only thing the signature endpoint will accept.
	 *
	 * @param string $raw The `join` value from the legacy URL.
	 */
	private static function migrate_legacy_link( string $raw ): ?JoinToken {
		$raw = trim( $raw );

		// A signed token always contains exactly one dot and is base64url.
		// A legacy AES ciphertext is base64 and contains none, so the shape
		// check is enough to tell them apart before we touch the database.
		if ( '' === $raw || false !== strpos( $raw, '.' ) ) {
			return null;
		}

		$meeting_number = self::decrypt_legacy( $raw );

		if ( '' === $meeting_number ) {
			return null;
		}

		$post_id = self::post_id_from_request();

		$token = JoinToken::issue(
			$post_id,
			$meeting_number,
			self::decrypt_legacy( self::legacy_param( 'pak' ) ),
			array(
				'tk' => self::legacy_param( 'tk' ),
				'dj' => self::legacy_param( 'direct_join' ),
			)
		);

		if ( '' === $token ) {
			return null;
		}

		$parsed = JoinToken::parse( $token );

		if ( null === $parsed ) {
			return null;
		}

		// Bind the migrated token to the post it came from so it cannot be
		// replayed elsewhere. If the post's meeting has changed since the link
		// was generated, fail loudly instead of silently joining the wrong
		// meeting.
		if ( $post_id > 0 ) {
			$expected = self::meeting_number_for_post( get_post( $post_id ) );

			if ( '' !== $expected && ! hash_equals( $expected, $parsed->meeting_number() ) ) {
				return null;
			}
		}

		return $parsed;
	}

	/**
	 * Determine the post a legacy link belongs to, so the migrated token can be
	 * bound to it.
	 */
	private static function post_id_from_request(): int {
		$post_id = 0;

		if ( function_exists( 'get_queried_object_id' ) ) {
			$post_id = absint( get_queried_object_id() );
		}

		if ( $post_id > 0 ) {
			$post = get_post( $post_id );

			if ( $post instanceof WP_Post && 'zoom-meetings' === $post->post_type ) {
				return $post_id;
			}
		}

		// Archive and shortcode links carry the post id explicitly.
		$from_query = absint( self::legacy_param( 'post_id' ) );

		if ( $from_query > 0 ) {
			$post = get_post( $from_query );

			if ( $post instanceof WP_Post && 'zoom-meetings' === $post->post_type ) {
				return $from_query;
			}
		}

		return 0;
	}

	/**
	 * Read a legacy query argument.
	 *
	 * Note this does not coerce: `direct_join` is treated as a flag, and only
	 * the literal `1` counts, which fixes the old `(bool) "0" === true` bug.
	 */
	private static function legacy_param( string $key ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read on a public join page; the value is re-validated as a signed token.
		if ( ! isset( $_GET[ $key ] ) || ! is_scalar( $_GET[ $key ] ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( (string) $_GET[ $key ] ) );
	}

	/**
	 * Decrypt a legacy ciphertext, returning '' rather than false.
	 *
	 * The old code did `base64_encode( Encryption::decrypt( $_GET['join'] ) )`,
	 * so a forged link became `base64_encode( false )` — an empty string that
	 * sailed on into the SDK as a blank meeting number.
	 */
	private static function decrypt_legacy( string $ciphertext ): string {
		if ( '' === $ciphertext ) {
			return '';
		}

		$decrypted = Encryption::decrypt( $ciphertext );

		if ( ! is_string( $decrypted ) || '' === $decrypted ) {
			return '';
		}

		return $decrypted;
	}

	/**
	 * Factory for a rejected request.
	 */
	private static function rejected( string $reason ): JoinRequest {
		return new self( null, $reason );
	}
}
