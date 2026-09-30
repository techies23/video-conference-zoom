<?php

use Codemanas\VczApi\Helpers\Encryption;
use Codemanas\VczApi\Helpers\Signature;

/**
 * Class for all the administration ajax calls
 *
 * @since   2.0.0
 * @author  Deepen
 */
class Zoom_Video_Conferencing_Admin_Ajax {

	public function __construct() {
		//Join via browser Auth Call
		add_action( 'wp_ajax_nopriv_get_auth', array( $this, 'get_auth' ) );
		add_action( 'wp_ajax_get_auth', array( $this, 'get_auth' ) );
	}

	/**
	 * Get authenticated io
	 *
	 * @deprecated 4.7.0 Superseded by the vczapi/v1/signature REST route. Kept
	 *             so cached pages and hosts that block the REST namespace keep
	 *             working; both paths share Helpers\Signature.
	 *
	 * @since  3.2.0
	 * @author Deepen Bajracharya
	 */
	public function get_auth() {
		$referer  = wp_get_referer();
		$home_url = home_url();

		// Block requests with no referer or external referers
		if ( ! $referer || parse_url( $referer, PHP_URL_HOST ) !== parse_url( $home_url, PHP_URL_HOST ) ) {
			wp_send_json_error( 'Invalid request source.' );
		}

		if ( ! Signature::is_configured() ) {
			wp_send_json_error( 'SDK configuration error.' );
		}

		$meeting_id = Signature::sanitize_meeting_number( filter_input( INPUT_POST, 'meeting_id', FILTER_SANITIZE_NUMBER_INT ) );

		if ( '' === $meeting_id ) {
			wp_send_json_error( 'Invalid Meeting ID' );
		}

		// Ensure role is 0 (Participant). NEVER allow Role 1 (Host) for guests.
		$signature = Signature::for_meeting( $meeting_id, Signature::ROLE_PARTICIPANT );

		if ( false === $signature ) {
			wp_send_json_error( 'Could not authorise this meeting.' );
		}

		wp_send_json_success( [
			'sig'  => $signature,
			'type' => 'sdk'
		] );
	}

	/**
	 * @deprecated 4.7.0 Replaced by Helpers\Signature::for_meeting().
	 *
	 * @param string   $api_key       Zoom SDK key.
	 * @param string   $secret_key    Zoom SDK secret.
	 * @param string   $meeting_number Meeting number.
	 * @param int      $role          Zoom role.
	 *
	 * @return string|false
	 */
	private function generate_sdk_signature( $api_key, $secret_key, $meeting_number, $role ) {
		return Signature::for_meeting( (string) $meeting_number, (int) $role );
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
