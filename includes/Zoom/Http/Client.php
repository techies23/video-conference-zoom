<?php

namespace Codemanas\VczApi\Zoom\Http;

use Codemanas\VczApi\Data\Logger;
use Codemanas\VczApi\Zoom\Auth\S2SOAuth;
use WP_Error;

class Client {
	/**
	 * Base URL for Zoom v2 API.
	 *
	 * @var string
	 */
	protected string $baseUrl = 'https://api.zoom.us/v2';

	/**
	 * Retry limits.
	 *
	 * @var int
	 */
	protected int $max401Retries = 1;
	protected int $max429Retries = 2;
	protected int $max5xxRetries = 2;

	/**
	 * Request to Zoom API with S2S OAuth.
	 *
	 * Success:
	 *  - 200/201/202/204 -> array (JSON-decoded) or [] for empty/non-JSON.
	 * Failure:
	 *  - WP_Error with code (HTTP status or transport code), message, and data.
	 *
	 * @param string $method   GET|POST|PATCH|PUT|DELETE
	 * @param  string  $endpoint Absolute or relative endpoint (e.g. /users/me)
	 * @param array  $payload  Request body or query params.
	 * @param array  $args     Extra WP HTTP args overrides.
	 *
	 * @return array|WP_Error
	 */
	public function request( $method, string $endpoint, array $payload = array(), array $args = array() ) {
		$method   = strtoupper( $method );
		$url      = $this->buildUrl( $endpoint, $method, $payload );
		$attempts = array( '401' => 0, '429' => 0, '5xx' => 0 );

		$token = S2SOAuth::get_instance()->getAccessToken();
		if ( is_wp_error( $token ) ) {
			$this->logError( 'OAuth token error', 0, $url, $method, $token->get_error_message() );
			return $token;
		}

		$headers = array(
			'Authorization' => 'Bearer ' . $token,
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
			'User-Agent'    => $this->userAgent(),
		);

		$requestArgs = array_merge(
			array(
				'method'      => $method,
				'timeout'     => 30,
				'headers'     => $headers,
				'sslverify'   => true,
				'redirection' => 3,
			),
			$args
		);

		// Add body for non-GET requests.
		if ( $method !== 'GET' && ! empty( $payload ) ) {
			$requestArgs['body'] = wp_json_encode( $payload );
		}

		/**
		 * Allow 3rd-party to filter request before dispatch.
		 *
		 * @param array  $requestArgs
		 * @param string $url
		 * @param string $method
		 * @param array  $payload
		 */
		$requestArgs = apply_filters( 'vczapi_http_before_request', $requestArgs, $url, $method, $payload );

		while ( true ) {
			$response = wp_remote_request( $url, $requestArgs );

			if ( is_wp_error( $response ) ) {
				$this->logError( 'Transport error', 0, $url, $method, $response->get_error_message() );
				return $response; // already a WP_Error
			}

			$status  = (int) wp_remote_retrieve_response_code( $response );
			$headers = wp_remote_retrieve_headers( $response );
			$body    = wp_remote_retrieve_body( $response );

			// 401 Unauthorized -> attempt token refresh and retry once.
			if ( $status === 401 && $attempts['401'] < $this->max401Retries ) {
				$attempts['401']++;
				$this->logDebug( '401 received. Refreshing token and retrying.', $status, $url, $method );
				S2SOAuth::get_instance()->regenerateAccessTokenAndSave();
				$newToken = S2SOAuth::get_instance()->getAccessToken();
				if ( is_wp_error( $newToken ) ) {
					$this->logError( 'OAuth refresh error', 401, $url, $method, $newToken->get_error_message() );
					return $newToken;
				}
				$requestArgs['headers']['Authorization'] = 'Bearer ' . $newToken;
				continue;
			}

			// 429 Too Many Requests -> honor Retry-After if present.
			if ( $status === 429 && $attempts['429'] < $this->max429Retries ) {
				$attempts['429']++;
				$retryAfter = $this->getRetryAfter( $headers );
				$this->logDebug( '429 received. Backing off.', $status, $url, $method, array( 'retry_after' => $retryAfter ) );
				$this->sleepWithJitter( $retryAfter );
				continue;
			}

			// 5xx -> retry with backoff.
			if ( $status >= 500 && $status <= 599 && $attempts['5xx'] < $this->max5xxRetries ) {
				$attempts['5xx']++;
				$this->logDebug( '5xx received. Retrying with backoff.', $status, $url, $method, array( 'attempt' => $attempts['5xx'] ) );
				$this->sleepWithJitter( 1 + $attempts['5xx'] ); // small backoff
				continue;
			}

			// Success codes.
			if ( in_array( $status, array( 200, 201, 202, 204 ), true ) ) {
				$decoded = $this->decodeJson( $body );
				$result  = ( $status === 204 || $decoded === null ) ? array() : $decoded;

				return apply_filters( 'vczapi_http_after_success', $result, $status, $url, $method, $payload, (array) $headers );
			}

			// Non-success: create WP_Error.
			$errorBody = $this->decodeJson( $body );
			$message   = $this->extractErrorMessage( $status, $errorBody, $response );

			$this->logError( 'API error', $status, $url, $method, $message );

			$wp_error = new WP_Error(
				$status ? $status : 'vczapi_http_error',
				$message ? $message : 'Zoom API request failed.',
				array(
					'headers' => (array) $headers,
					'url'     => $this->redactSensitiveInUrl( $url ),
					'method'  => $method,
					'payload' => $this->redactSensitiveInPayload( $payload ),
					'body'    => $errorBody !== null ? $errorBody : $body,
				)
			);

			/**
			 * Allow consumers to mutate the error.
			 *
			 * @param WP_Error $wp_error
			 * @param int       $status
			 * @param string    $url
			 * @param string    $method
			 * @param array     $payload
			 * @param array     $headers
			 */
			return apply_filters( 'vczapi_http_after_error_wp', $wp_error, $status, $url, $method, $payload, (array) $headers );
		}
	}

	/**
	 * Build absolute URL and inject GET params if needed.
	 *
	 * @param string $endpoint
	 * @param string $method
	 * @param array  $payload
	 * @return string
	 */
	protected function buildUrl( $endpoint, $method, array $payload = array() ) {
		$isAbsolute = ( strpos( $endpoint, 'http://' ) === 0 || strpos( $endpoint, 'https://' ) === 0 );
		$base       = $isAbsolute ? rtrim( $endpoint, '/' ) : rtrim( $this->baseUrl, '/' ) . '/' . ltrim( $endpoint, '/' );

		if ( $method === 'GET' && ! empty( $payload ) ) {
			$base = add_query_arg( $payload, $base );
		}

		return $base;
	}

	/**
	 * Determine retry delay from headers.
	 *
	 * @param array|\ArrayAccess $headers
	 * @return int Seconds
	 */
	protected function getRetryAfter( $headers ) {
		$retryAfter = 1;

		if ( is_array( $headers ) ) {
			if ( isset( $headers['retry-after'] ) ) {
				$retryAfter = (int) $headers['retry-after'];
			} elseif ( isset( $headers['Retry-After'] ) ) {
				$retryAfter = (int) $headers['Retry-After'];
			}
		} elseif ( is_object( $headers ) && method_exists( $headers, 'offsetGet' ) ) {
			$val = $headers->offsetGet( 'retry-after' );
			if ( $val ) {
				$retryAfter = (int) $val;
			}
		}

		return $retryAfter > 0 ? $retryAfter : 1;
	}

	/**
	 * Sleep with a tiny jitter to avoid synchronized retries.
	 *
	 * @param int $seconds
	 * @return void
	 */
	protected function sleepWithJitter( $seconds ) {
		$ms = $seconds * 1000000 + rand( 0, 250000 ); // + up to 250ms
		usleep( $ms );
	}

	/**
	 * Decode JSON, return null on failure or empty string.
	 *
	 * @param string $body
	 * @return array|null
	 */
	protected function decodeJson( $body ) {
		if ( $body === '' || $body === null ) {
			return null;
		}
		$decoded = json_decode( $body, true );
		return ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) ? $decoded : null;
	}

	/**
	 * Extract error message from body or response.
	 *
	 * @param int        $status
	 * @param array|null $errorBody
	 * @param array      $response
	 * @return string
	 */
	protected function extractErrorMessage( $status, $errorBody, $response ) {
		if ( is_array( $errorBody ) ) {
			if ( ! empty( $errorBody['message'] ) ) {
				return (string) $errorBody['message'];
			}
			if ( ! empty( $errorBody['error'] ) && is_string( $errorBody['error'] ) ) {
				return (string) $errorBody['error'];
			}
		}
		$msg = wp_remote_retrieve_response_message( $response );
		return $msg ? $msg : 'Request failed with status ' . $status;
	}

	/**
	 * Compose a helpful User-Agent.
	 *
	 * @return string
	 */
	protected function userAgent() {
		$version = defined( 'ZVC_PLUGIN_VERSION' ) ? ZVC_PLUGIN_VERSION : 'unknown';
		$wp      = get_bloginfo( 'version' );
		$php     = PHP_VERSION;

		return sprintf( 'Codemanas-VczApi/%s WordPress/%s PHP/%s', $version, $wp, $php );
	}

	/**
	 * Debug/info logging (redacted, toggle via option).
	 *
	 * @param string     $prefix
	 * @param int        $status
	 * @param string     $url
	 * @param string     $method
	 * @param array|null $context
	 * @return void
	 */
	protected function logDebug( $prefix, $status, $url, $method, $context = null ) {
		if ( ! get_option( 'zoom_api_enable_debug_log' ) ) {
			return;
		}
		$line = sprintf(
			'[Zoom][DEBUG] %s %s %s (%d)%s',
			$method,
			$this->redactSensitiveInUrl( $url ),
			$prefix,
			$status,
			$context ? ' :: ' . wp_json_encode( $this->redactSensitiveInPayload( (array) $context ) ) : ''
		);

		if ( class_exists( '\Codemanas\VczApi\Data\Logger' ) ) {
			$logger = new Logger();
			$logger->error( $line );
		} else {
			error_log( $line );
		}
	}

	/**
	 * Error logging (redacted).
	 *
	 * @param string $prefix
	 * @param int    $status
	 * @param string $url
	 * @param string $method
	 * @param string $message
	 * @return void
	 */
	protected function logError( $prefix, $status, $url, $method, $message ) {
		if ( ! get_option( 'zoom_api_enable_debug_log' ) ) {
			return;
		}
		$line = sprintf(
			'[Zoom][ERROR] %s %s -> %s (%d) :: %s',
			$method,
			$this->redactSensitiveInUrl( $url ),
			parse_url( $url, PHP_URL_HOST ),
			$status,
			$message
		);

		if ( class_exists( '\Codemanas\VczApi\Data\Logger' ) ) {
			$logger = new Logger();
			$logger->error( $line );
		} else {
			error_log( $line );
		}
	}

	/**
	 * Redact potential secrets in URLs.
	 *
	 * @param string $url
	 * @return string
	 */
	protected function redactSensitiveInUrl( $url ) {
		$patterns     = array(
			'/([?&])pwd=([^&]+)/i',
			'/([?&])passcode=([^&]+)/i',
			'/([?&])access_token=([^&]+)/i',
			'/([?&])token=([^&]+)/i',
		);
		$replacements = array(
			'$1pwd=[REDACTED]',
			'$1passcode=[REDACTED]',
			'$1access_token=[REDACTED]',
			'$1token=[REDACTED]',
		);

		return preg_replace( $patterns, $replacements, $url );
	}

	/**
	 * Redact sensitive fields in payload/context.
	 *
	 * @param array $payload
	 * @return array
	 */
	protected function redactSensitiveInPayload( array $payload ) {
		$redactKeys = array( 'password', 'passcode', 'token', 'access_token', 'authorization' );
		foreach ( $payload as $k => $v ) {
			if ( in_array( strtolower( $k ), $redactKeys, true ) ) {
				$payload[ $k ] = '[REDACTED]';
			}
		}
		return $payload;
	}
}