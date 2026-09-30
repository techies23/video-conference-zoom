<?php
/**
 * @package     Video Conferencing with Zoom API
 * @subpackage  RestApi
 * @author      Deepen Bajracharya
 * @since       4.7.0
 */

namespace Codemanas\VczApi\RestApi;

use Codemanas\VczApi\Helpers\Signature;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

/**
 * REST endpoints backing the Join-via-Browser page.
 */
final class MeetingRest {

	/**
	 * REST namespace.
	 */
	public const NAMESPACE = 'vczapi/v1';

	/**
	 * Not instantiable.
	 */
	private function __construct() {}

	/**
	 * Register the routes.
	 *
	 * @return void
	 */
	public static function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/signature',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( self::class, 'signature' ),
					// Guests join without an account, so the route is public. The
					// SDK secret never leaves the server, and a signature is only
					// ever issued for the participant role.
					'permission_callback' => '__return_true',
					'args'                => array(
						'meetingNumber' => array(
							'type'        => 'string',
							'required'    => true,
							'description' => __( 'Zoom meeting or webinar number.', 'video-conferencing-with-zoom-api' ),
						),
					),
				),
			)
		);
	}

	/**
	 * Issue a Meeting SDK signature for a guest.
	 *
	 * The response deliberately mirrors the legacy admin-ajax `get_auth` shape so
	 * the client can share one parser between the two endpoints.
	 *
	 * @param WP_REST_Request $request Incoming request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function signature( WP_REST_Request $request ) {
		if ( ! Signature::is_configured() ) {
			return self::fail( 'SDK configuration error.', 'vczapi_sdk_not_configured', 500 );
		}

		$meeting_number = Signature::sanitize_meeting_number( $request->get_param( 'meetingNumber' ) );

		if ( '' === $meeting_number ) {
			return self::fail( 'Invalid Meeting ID', 'vczapi_invalid_meeting_id', 400 );
		}

		$signature = Signature::for_meeting( $meeting_number );

		if ( false === $signature ) {
			return self::fail( 'Could not authorise this meeting.', 'vczapi_signature_failed', 500 );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'sig'  => $signature,
					'type' => 'sdk',
				),
			)
		);
	}

	/**
	 * Build an error response that matches the legacy payload shape.
	 *
	 * @param string $message Human readable reason.
	 * @param string $code    Machine readable code.
	 * @param int    $status  HTTP status.
	 *
	 * @return WP_Error
	 */
	private static function fail( string $message, string $code, int $status ): WP_Error {
		return new WP_Error(
			$code,
			$message,
			array(
				'status'   => $status,
				'success'  => false,
				'data'     => array( 'message' => $message ),
			)
		);
	}
}
