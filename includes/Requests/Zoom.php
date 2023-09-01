<?php

namespace Codemanas\VczApi\Requests;

use Codemanas\VczApi\Data\Logger;

/**
 * Method here/i/am to serve all the Zoom server requests
 *
 * @added 4.4.0
 * @purpose I will soon replace all the old endpoints, so call me everyone who sees this? buhahahah
 */
class Zoom {

	/**
	 * Hold my instance
	 *
	 * @var
	 */
	protected static $_instance;

	protected static int $OAuth_revalidate_attempts = 0;

	/**
	 * API endpoint base
	 *
	 * @var string
	 */
	private string $api_url = 'https://api.zoom.us/v2';

	/**
	 * Create only one instance so that it may not Repeat
	 *
	 * @since 2.0.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Zoom_Video_Conferencing_Api constructor.
	 */
	protected function __construct() {
		$this->_setKeys();
	}

	private function _setKeys() {
	}

	/**
	 * Send request to API
	 *
	 * @param $endpoint
	 * @param  string  $method
	 * @param  array  $data
	 *
	 * @return array|bool|string|WP_Error
	 */
	protected function sendRequest( $endpoint, array $data = [], string $method = 'GET' ) {
		$bearerToken = $this->getBearerToken();
		$args        = array(
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $bearerToken,
				'Content-Type'  => 'application/json',
			),
		);

		$callApi      = $this->api_url . $endpoint;
		$args['body'] = ! empty( $data ) ? ( $method == "GET" ? $data : json_encode( $data ) ) : [];
		$request      = wp_remote_request( $callApi, $args );

		if ( is_wp_error( $request ) ) {
			$this->logMessage( $request->get_error_message(), $request->get_error_code(), $request );

			return $request; // Bail early
		}

		$responseCode = wp_remote_retrieve_response_code( $request );
		$responseBody = wp_remote_retrieve_body( $request );
		$response     = json_decode( $responseBody );
		$debug_log    = get_option( 'zoom_api_enable_debug_log' );

		if ( $responseCode == 401 && vczapi_is_oauth_active() ) {
			//only regenerate access token if it's already active;
			\vczapi\S2SOAuth::get_instance()->regenerateAccessTokenAndSave();
			//only retry twice;
			if ( self::$OAuth_revalidate_attempts <= 2 ) {
				self::$OAuth_revalidate_attempts ++;

				//resend the request after regenerating access token
				return $this->sendRequest( $endpoint, $data );
			} else {
				self::$OAuth_revalidate_attempts = 0;
				if ( ! empty( $debug_log ) ) {
					$this->logMessage( $responseBody, $responseCode, $request );
				}
			}
		}

		//If Debug log is enabled.
		$goodCodes = [ 200, 201, 202, 204 ];
		if ( ! empty( $debug_log ) && ! in_array( $responseCode, $goodCodes ) ) {
			$this->logMessage( $responseBody, $responseCode, $request );
		}

		//Allow 3rd parties to alter the $args
		return apply_filters( 'vczapi_sendRequest', $response, $responseCode, $endpoint, $data, $request );
	}

	/**
	 * Check is given string a correct json object
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	public function isJson( $string ): bool {
		json_decode( $string );

		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Check is Valid XML
	 *
	 * @param $xml
	 *
	 * @return bool
	 */
	public function isValidXML( $xml ): bool {
		$doc = @simplexml_load_string( $xml );
		if ( $doc ) {
			return true; //this is valid
		} else {
			return false; //this is not valid
		}
	}

	/**
	 * Just log the message for now because of backwards incompatibility issues
	 *
	 * @param $responseBody
	 * @param $responseCode
	 * @param $request
	 *
	 * @author Deepen Bajracharya
	 *
	 * @since  3.8.18
	 */
	public function logMessage( $responseBody, $responseCode, $request ) {
		$message = $responseCode . ' ::: ';
		$message .= wp_remote_retrieve_response_message( $request );

		if ( ! empty( $responseBody ) ) {
			//Response body validation
			if ( $this->isValidXML( $responseBody ) ) {
				$responseBody = simplexml_load_string( $responseBody );
			} elseif ( $this->isJson( $responseBody ) ) {
				$responseBody = json_decode( $responseBody );
			}

			if ( ! empty( $responseBody ) && ! empty( $responseBody->message ) ) {
				$message .= ' ::: MESSAGE => ' . $responseBody->message;
			} elseif ( ! empty( $responseBody ) && is_string( $responseBody ) ) {
				$message .= ' ::: MESSAGE => ' . $responseBody;
			}

			if ( ! empty( $responseBody ) && ! empty( $responseBody->errors ) && is_object( $responseBody->errors ) && ! empty( $responseBody->errors->message ) ) {
				$message .= ' ::: ERRORS => ' . $responseBody->errors->message;
			}
		}

		$logger = new Logger();
		$logger->error( $message );
	}

	private function getBearerToken() {
		//@todo this will need to be modified for each user scenario
		$OauthData = get_option( 'vczapi_global_oauth_data' );

		return ! empty( $OauthData ) ? $OauthData->access_token : false;
	}

	public function me() {
		return $this->sendRequest( '/users/me' );
	}
}
