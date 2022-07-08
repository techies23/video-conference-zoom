<?php

namespace Codemanas\VczApi\Backend;

use Codemanas\VczApi\Includes\Data\Logger;

/**
 * Class Debugger
 * @package Codemanas\VczApi\Backend
 */
class Debugger {

	/**
	 * Check is given string a correct json object
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	public function isJson( $string ) {
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
	public function isValidXML( $xml ) {
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
	 * @since 3.8.18
	 */
	public function logMessage( $responseBody, $responseCode, $request ) {
		$message = $responseCode . ' ::: ';
		$message .= wp_remote_retrieve_response_message( $request );

		if ( ! empty( $responseBody ) ) {
			//Response body validation
			if ( $this->isValidXML( $responseBody ) ) {
				$responseBody = simplexml_load_string( $responseBody );
			} else if ( $this->isJson( $responseBody ) ) {
				$responseBody = json_decode( $responseBody );
			}

			if ( ! empty( $responseBody ) && ! empty( $responseBody->message ) ) {
				$message .= ' ::: MESSAGE => ' . $responseBody->message;
			} else if ( ! empty( $responseBody ) && is_string( $responseBody ) ) {
				$message .= ' ::: MESSAGE => ' . $responseBody;
			}

			if ( ! empty( $responseBody ) && ! empty( $responseBody->errors ) && is_object( $responseBody->errors ) && ! empty( $responseBody->errors->message ) ) {
				$message .= ' ::: ERRORS => ' . $responseBody->errors->message;
			}
		}

		$logger = new Logger();
		$logger->error( $message );
	}


	private static $_instance = null;

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
}