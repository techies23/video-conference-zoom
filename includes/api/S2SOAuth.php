<?php

namespace vczapi;

class S2SOAuth {
	public static $instance = null;

	public static function get_instance() {
		return is_null( self::$instance ) ? self::$instance = new self() : self::$instance;
	}

	public function __construct() {

	}

	/**
	 * @param $account_id
	 * @param $client_id
	 * @param $client_secret
	 *
	 * @return mixed
	 */
	private function generateAccessToken( $account_id, $client_id, $client_secret ) {

		if ( empty( $account_id ) ) {
			return new \WP_Error( 'Account ID', 'Account ID is missing' );
		} elseif ( empty( $client_id ) ) {
			return new \WP_Error( 'Client ID', 'Client ID is missing' );
		} elseif ( empty( $client_secret ) ) {
			return new \WP_Error( 'Client Secret', 'Client Secret is missing' );
		}

		$base64Encoded = base64_encode( $client_id . ':' . $client_secret );
		$result        = new \WP_Error( 0, 'Something went wrong' );

		$args = [
			'method'  => 'POST',
			'headers' => [
				'Authorization' => "Basic $base64Encoded",
			],
			'body'    => [
				'grant_type' => 'account_credentials',
				'account_id' => $account_id,
			],
		];

		$request_url      = "https://zoom.us/oauth/token";
		$response         = wp_remote_post( $request_url, $args );
		$responseCode     = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );
		if ( $responseCode == 200 && strtolower( $response_message ) == 'ok' ) {
			$responseBody          = wp_remote_retrieve_body( $response );
			$decoded_response_body = json_decode( $responseBody );
			if ( isset( $decoded_response_body->access_token ) && ! empty( $decoded_response_body->access_token ) ) {
				$result = $decoded_response_body;
			} elseif ( isset( $decoded_response_body->errorCode ) && ! empty( $decoded_response_body->errorCode ) ) {
				$result = new \WP_Error( $decoded_response_body->errorCode, $decoded_response_body->errorMessage );
			}
		} else {
			$result = new \WP_Error( $responseCode, $response_message );
		}

		return $result;
	}

	/**
	 * @param $account_id
	 * @param $client_id
	 * @param $client_secret
	 *
	 * @return mixed
	 */
	public function generateAndSaveAccessToken( $account_id, $client_id, $client_secret ) {
		$result = $this->generateAccessToken( $account_id, $client_id, $client_secret );
		if ( ! is_wp_error( $result ) ) {
			//@todo - implement a per person option to allow other users to add their own API Credentials and generate own access token
			update_option( 'vczapi_global_oauth_data', $result );
		}

		return $result;
	}

	/**
	 * Should only be used when regenerating access token from saved keys
	 *
	 * @return void
	 */
	public function regenerateAccessTokenAndSave() {
		$account_id    = get_option( 'vczapi_oauth_account_id' );
		$client_id     = get_option( 'vczapi_oauth_client_id' );
		$client_secret = get_option( 'vczapi_oauth_client_secret' );

		$result = $this->generateAndSaveAccessToken( $account_id, $client_id, $client_secret );
		if ( is_wp_error( $result ) ) {
			//@todo log error if regenerating access token unsuccessful
		}
	}
}