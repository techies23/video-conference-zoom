<?php
/**
 * @package     Video Conferencing with Zoom API
 * @subpackage  Browser/Rest
 * @author      Deepen Bajracharya
 * @since       4.7.0
 */

namespace Codemanas\VczApi\WebSDK\Rest;

use Codemanas\VczApi\WebSDK\JoinRequest;
use Codemanas\VczApi\WebSDK\JoinToken;
use Codemanas\VczApi\Helpers\Signature;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Issues Meeting SDK signatures for a verified join token.
 *
 * This replaces two open signing oracles.
 *
 * The REST route at `includes/Rest/WebSDK.php` was declared public
 * (`'permission_callback' => '__return_true'`) and accepted any 9-13 digit
 * meeting number. It was never even registered, because nothing hooked
 * `rest_api_init`, so every request 404'd.
 *
 * The client therefore fell back to the `admin-ajax` `get_auth` action, which
 * *was* live — and which had its `check_ajax_referer()` call commented out
 * (see `Zoom_Video_Conferencing_Admin_Ajax::get_auth`). Its only protection was
 * a `Referer` header check, which any HTTP client can set to anything. Combined
 * with the site's SDK secret, that turned the site into an oracle that would mint
 * a valid Meeting SDK JWT for any meeting number on the Zoom account, on
 * demand, to anyone.
 *
 * The signature is now issued only against a join token that has passed
 * `JoinRequest`, which means: HMAC-verified, unexpired, bound to a real meeting
 * post whose meeting number still matches, and only while the feature is
 * enabled. There is no bare-meeting-number path and no ajax fallback.
 *
 * @since 4.9.0
 */
final class SignatureEndpoint {

	/**
	 * REST namespace.
	 */
	public const NAMESPACE = 'vczapi/v1';

	/**
	 * Route within the namespace.
	 */
	public const ROUTE = 'signature';

	/**
	 * Signature requests allowed per IP per minute.
	 *
	 * A signature is cheap for us and useful to an attacker, so the endpoint is
	 * rate limited. The limit is generous enough that a shared NAT gateway or a
	 * busy office cannot trip it during a normal meeting.
	 */
	public const RATE_LIMIT = 20;

	/**
	 * Not instantiable.
	 */
	private function __construct() {
	}

	/**
	 * Register the route.
	 */
	public static function register_routes(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register the route with the REST server.
	 *
	 * Hooked rather than called directly: `rest_api_init` fires once the REST
	 * server exists. The previous class was never wired to it at all.
	 */
	public static function register(): void {
		register_rest_route(
			self::NAMESPACE,
			'/' . self::ROUTE,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'handle' ),
					/*
					 * The token is the authorisation. It is a capability: an
					 * unforgeable, expiring, meeting-bound grant that the visitor
					 * already had to possess to load the join page at all.
					 *
					 * A nonce is sent by the client and WordPress' REST cookie
					 * machinery still checks it, but it is deliberately not the
					 * gate here: for a logged-out visitor the `wp_rest` nonce is
					 * identical across all anonymous sessions, so it would add
					 * the appearance of CSRF protection without providing any.
					 * Requiring it would also break guest joins outright.
					 */
					'permission_callback' => '__return_true',
					'args'                => array(
						'token'     => array(
							'type'        => 'string',
							'required'    => true,
							'description' => __( 'The join token issued with this meeting link.', 'video-conferencing-with-zoom-api' ),
						),
						'userName'  => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Display name for this participant.', 'video-conferencing-with-zoom-api' ),
						),
						'userEmail' => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Email for this participant.', 'video-conferencing-with-zoom-api' ),
						),
						'passWord'  => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Meeting passcode, when the host requires one and the visitor typed it.', 'video-conferencing-with-zoom-api' ),
						),
						'lang'      => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'BCP-47 locale for the SDK interface.', 'video-conferencing-with-zoom-api' ),
						),
					),
				),
			)
		);
	}

	/**
	 * Issue a signature for a verified join token.
	 *
	 * @param WP_REST_Request $request Incoming request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function handle( WP_REST_Request $request ) {
		if ( ! Signature::is_configured() ) {
			return self::fail(
				__( 'Joining in the browser is not configured on this site. Please use the Zoom app or desktop client instead.', 'video-conferencing-with-zoom-api' ),
				'vczapi_sdk_not_configured',
				503
			);
		}

		$throttle = self::check_rate_limit();

		if ( null !== $throttle ) {
			return $throttle;
		}

		// One code path: the token is verified, then every server-side gate in
		// JoinRequest is applied. There is deliberately no way to request a
		// signature for a bare meeting number.
		$join = JoinRequest::from_token_string( (string) $request->get_param( 'token' ) );

		if ( ! $join->is_valid() ) {
			// 403 rather than 401: there is no session to authenticate, and the
			// distinction between "no token" and "bad token" is not something the
			// caller can act on differently.
			return self::fail( $join->error(), 'vczapi_join_not_authorised', 403 );
		}

		$token = $join->token();

		// Role is always participant. A guest who can choose its own role can
		// start the meeting.
		$signature = Signature::for_meeting( $token->meeting_number() );

		if ( false === $signature ) {
			return self::fail(
				__( 'This meeting could not be authorised. Please use the Zoom app or desktop client instead.', 'video-conferencing-with-zoom-api' ),
				'vczapi_signature_failed',
				500
			);
		}

		/**
		 * Filter the join payload returned with the signature.
		 *
		 * @param array $payload Join payload.
		 * @param JoinToken $token Verified token.
		 * @param WP_REST_Request $request Incoming request.
		 *
		 * @since 4.9.0
		 */
		$payload = (array) apply_filters(
			'vczapi_join_via_browser_payload',
			array(
				'signature'       => $signature,
				'sdkKey'          => Signature::sdk_key(),
				'meetingNumber'   => $token->meeting_number(),
				'passWord'        => self::resolve_password( $request, $token ),
				'registrantToken' => $token->registrant_token(),
				'userName'        => self::resolve_name( $request ),
				'userEmail'       => self::resolve_email( $request ),
				'lang'            => self::resolve_lang( $request ),
			),
			$token,
			$request
		);

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $payload,
			)
		);
	}

	/**
	 * Resolve the meeting passcode: what the visitor typed, else the token's.
	 */
	private static function resolve_password( WP_REST_Request $request, JoinToken $token ): string {
		$typed = $request->get_param( 'passWord' );

		if ( is_string( $typed ) && '' !== trim( $typed ) ) {
			return trim( $typed );
		}

		return $token->password();
	}

	/**
	 * Resolve the participant display name.
	 */
	private static function resolve_name( WP_REST_Request $request ): string {
		$name = $request->get_param( 'userName' );

		if ( is_string( $name ) && '' !== trim( $name ) ) {
			return self::clean_text( $name, 128 );
		}

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();

			if ( $user instanceof \WP_User && '' !== $user->display_name ) {
				return self::clean_text( $user->display_name, 128 );
			}
		}

		return __( 'Guest', 'video-conferencing-with-zoom-api' );
	}

	/**
	 * Resolve the participant email.
	 */
	private static function resolve_email( WP_REST_Request $request ): string {
		$email = $request->get_param( 'userEmail' );

		if ( is_string( $email ) && is_email( $email ) ) {
			return sanitize_email( $email );
		}

		if ( is_user_logged_in() ) {
			$user_email = wp_get_current_user()->user_email;

			if ( is_email( $user_email ) ) {
				return (string) $user_email;
			}
		}

		return '';
	}

	/**
	 * Resolve the SDK interface language.
	 */
	private static function resolve_lang( WP_REST_Request $request ): string {
		$lang = $request->get_param( 'lang' );

		if ( ! is_string( $lang ) || '' === $lang ) {
			return 'en-US';
		}

		// The SDK ships a fixed set of locale bundles; anything else would
		// throw inside i18n.load() and take down the join.
		$supported = \Codemanas\VczApi\Helpers\Locales::getSupportedTranslationsForWeb();

		if ( isset( $supported[ $lang ] ) ) {
			return $lang;
		}

		return 'en-US';
	}

	/**
	 * Strip control characters from a display name.
	 */
	private static function clean_text( string $value, int $max_length ): string {
		$value = sanitize_text_field( $value );

		return mb_substr( $value, 0, $max_length );
	}

	/**
	 * Throttle signature requests per IP.
	 *
	 * A fixed-window counter in a transient. Not a distributed rate limiter, but
	 * it is enough to make bulk signature harvesting impractical and it adds no
	 * infrastructure.
	 *
	 * @return WP_Error|null Error when throttled, null when allowed.
	 */
	private static function check_rate_limit(): ?WP_Error {
		/**
		 * Filter the signature endpoint rate limit.
		 *
		 * @param int $limit Requests allowed per IP per window.
		 *
		 * @since 4.9.0
		 */
		$limit = (int) apply_filters( 'vczapi_signature_rate_limit', self::RATE_LIMIT );

		if ( $limit <= 0 ) {
			return null;
		}

		$ip = self::client_ip();

		if ( '' === $ip ) {
			return null;
		}

		$key   = 'vczapi_sig_' . md5( $ip );
		$count = (int) get_transient( $key );

		if ( $count >= $limit ) {
			return self::fail(
				__( 'Too many join attempts from this connection. Please wait a minute and try again.', 'video-conferencing-with-zoom-api' ),
				'vczapi_rate_limited',
				429
			);
		}

		set_transient( $key, $count + 1, MINUTE_IN_SECONDS );

		return null;
	}

	/**
	 * Best-effort client IP.
	 *
	 * Only REMOTE_ADDR is trusted. The usual proxy headers are attacker
	 * controlled, so honouring them here would let anyone bypass the rate limit
	 * by setting a header.
	 */
	private static function client_ip(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
	}

	/**
	 * Build an error response.
	 *
	 * The shape matches what the previous endpoint returned so existing client
	 * error handling keeps working.
	 *
	 * @param string $message Human readable reason.
	 * @param string $code Machine readable code.
	 * @param int $status HTTP status.
	 */
	private static function fail( string $message, string $code, int $status ): WP_Error {
		return new WP_Error(
			$code,
			$message,
			array(
				'status'  => $status,
				'success' => false,
				'data'    => array( 'message' => $message ),
			)
		);
	}
}
