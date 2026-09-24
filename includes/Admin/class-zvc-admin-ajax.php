<?php

use Codemanas\VczApi\Helpers\Encryption;

/**
 * Class for all the administration ajax calls
 *
 * @since   2.0.0
 * @author  Deepen
 */
class Zoom_Video_Conferencing_Admin_Ajax {

	public function __construct() {
		add_action( 'wp_ajax_check_connection', array( $this, 'check_connection' ) );

		//Join via browser Auth Call
		add_action( 'wp_ajax_nopriv_get_auth', array( $this, 'get_auth' ) );
		add_action( 'wp_ajax_get_auth', array( $this, 'get_auth' ) );
	}

	/**
	 * Check API connection
	 *
	 * @since  3.0.0
	 * @author Deepen Bajracharya
	 */
	public function check_connection() {
		check_ajax_referer( '_nonce_zvc_security', 'security' );

		$type = filter_input( INPUT_POST, 'type' );
		if ( $type === "oAuth" ) {
			$test = \Codemanas\VczApi\Requests\Zoom::instance()->me();
			if ( ! empty( $test->code ) ) {
				wp_send_json_error( $test->message );
			}

			//After user has been created delete this transient in order to fetch latest Data.
			video_conferencing_zoom_api_delete_user_cache();
			wp_send_json_success( [ 'msg' => "API Connection is good. You can refresh this and start creating your Zoom Events." ] );
		}

		wp_die();
	}

	/**
	 * Get authenticated io
	 *
	 * @since  3.2.0
	 * @author Deepen Bajracharya
	 */
	public function get_auth() {
		// check_ajax_referer('_nonce_zvc_security', 'zvc_security');

		$referer  = wp_get_referer();
		$home_url = home_url();

		// Block requests with no referer or external referers
		if ( ! $referer || parse_url( $referer, PHP_URL_HOST ) !== parse_url( $home_url, PHP_URL_HOST ) ) {
			wp_send_json_error( 'Invalid request source.' );
		}

		// 2. Sanitize and validate the Meeting ID
		$meeting_id = filter_input( INPUT_POST, 'meeting_id', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $meeting_id ) ) {
			wp_send_json_error( 'Invalid Meeting ID' );
		}

		if ( vczapi_is_sdk_enabled() ) {
			$sdk_key    = get_option( 'vczapi_sdk_key' );
			$secret_key = get_option( 'vczapi_sdk_secret_key' );

			if ( empty( $sdk_key ) || empty( $secret_key ) ) {
				wp_send_json_error( 'SDK configuration error.' );
			}

			// Ensure role is 0 (Participant). NEVER allow Role 1 (Host) for guests.
			$signature = $this->generate_sdk_signature( $sdk_key, $secret_key, $meeting_id, 0 );

			wp_send_json_success( [
				'sig'  => $signature,
				'type' => 'sdk'
			] );
		} else {
			wp_send_json_error( 'Service Unavailable' );
		}

		wp_die();
	}

	private function generate_sdk_signature( $sdk_key, $secret_key, $meeting_number, $role ) {
		$iat     = round( ( time() * 1000 - 30000 ) / 1000 );
		$exp     = $iat + 86400;
		$payload = [
			'sdkKey'   => $sdk_key,
			'mn'       => $meeting_number,
			'role'     => $role,
			'iat'      => $iat,
			'exp'      => $exp,
			'appKey'   => $sdk_key,
			'tokenExp' => $exp,
		];

		if ( empty( $secret_key ) ) {
			return false;
		}

		return \Firebase\JWT\JWT::encode( $payload, $secret_key, 'HS256' );
	}

	/**
	 * Generate Signature
	 *
	 * @param $api_key
	 * @param $api_sercet
	 * @param $meeting_number
	 * @param $role
	 *
	 * @return string
	 * @throws Exception
	 */
	private function generate_signature( $api_key, $api_sercet, $meeting_number, $role ) {
		//Set the timezone to UTC
		$date_utc = new \DateTime( "now", new \DateTimeZone( "UTC" ) );
		$time     = $date_utc->getTimestamp() * 1000 - 30000; //time in milliclearseconds (or close enough)
		$data     = base64_encode( $api_key . $meeting_number . $time . $role );
		$hash     = hash_hmac( 'sha256', $data, $api_sercet, true );
		$_sig     = $api_key . "." . $meeting_number . "." . $time . "." . $role . "." . base64_encode( $hash );

		//return signature, url safe base64 encoded
		return rtrim( strtr( base64_encode( $_sig ), '+/', '-_' ), '=' );
	}



}

new Zoom_Video_Conferencing_Admin_Ajax();
