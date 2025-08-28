<?php

namespace Codemanas\VczApi\Zoom\Auth;

use WP_Error;

class S2SOAuth {
	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	protected static ?S2SOAuth $instance = null;

	/**
	 * Option names.
	 */
	const OPTION_OAUTH_DATA      = 'vczapi_global_oauth_data';
	const OPTION_ACCOUNT_ID      = 'vczapi_oauth_account_id';
	const OPTION_CLIENT_ID       = 'vczapi_oauth_client_id';
	const OPTION_CLIENT_SECRET   = 'vczapi_oauth_client_secret';
	const TRANSIENT_REFRESH_LOCK = 'vczapi_oauth_refresh_lock';

	/**
	 * Refresh token this many seconds before expiry.
	 */
	const EARLY_REFRESH_SECONDS = 300; // 5 minutes.

	/**
	 * Get singleton.
	 *
	 * @return self
	 */
	public static function get_instance(): ?S2SOAuth {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get a valid access token, refreshing if needed.
	 *
	 * @return string|WP_Error
	 */
	public function getAccessToken() {
		$oauthData = get_option( self::OPTION_OAUTH_DATA );

		// If no data present, try to generate and save using saved credentials.
		if ( empty( $oauthData ) || empty( $oauthData->access_token ) ) {
			$result = $this->generateFromSavedCredentials();
			if ( is_wp_error( $result ) ) {
				return $result;
			}
			$oauthData = $result;
		}

		// If expiring soon, attempt refresh (with a lock to prevent stampede).
		if ( $this->isExpiringSoon( $oauthData ) ) {
			$this->refreshWithLock();
			$oauthData = get_option( self::OPTION_OAUTH_DATA );
		}

		return ! empty( $oauthData ) && ! empty( $oauthData->access_token )
			? $oauthData->access_token
			: new WP_Error( 'vczapi_oauth_missing_token', 'Zoom OAuth access token is missing.' );
	}

	/**
	 * Force-refresh token using saved credentials and persist it.
	 *
	 * @return void
	 */
	public function regenerateAccessTokenAndSave() {
		$this->generateFromSavedCredentials();
	}

	/**
	 * True if token expires within EARLY_REFRESH_SECONDS.
	 *
	 * @param  object  $oauthData
	 *
	 * @return bool
	 */
	protected function isExpiringSoon( object $oauthData ): bool {
		// Zoom token payload typically includes: access_token, token_type, expires_in, scope
		// We store the received object as-is, but we can calculate expiry_at from when it was stored.
		// If we have explicit 'expires_in' and 'created_at' (or 'date'), we can derive it. Fallback to try best.
		$now = time();

		$createdAt = 0;
		if ( isset( $oauthData->date ) && is_numeric( $oauthData->date ) ) {
			$createdAt = (int) $oauthData->date;
		} elseif ( isset( $oauthData->created_at ) && is_numeric( $oauthData->created_at ) ) {
			$createdAt = (int) $oauthData->created_at;
		}

		$expiresIn = isset( $oauthData->expires_in ) ? (int) $oauthData->expires_in : 0;
		if ( $createdAt > 0 && $expiresIn > 0 ) {
			$expiryAt = $createdAt + $expiresIn;

			return ( $expiryAt - $now ) <= self::EARLY_REFRESH_SECONDS;
		}

		// If we cannot determine, be conservative.
		return true;
	}

	/**
	 * Generate a token using saved creds and persist it; returns decoded object or WP_Error.
	 *
	 * @return object|WP_Error
	 */
	protected function generateFromSavedCredentials() {
		$account_id    = get_option( self::OPTION_ACCOUNT_ID );
		$client_id     = get_option( self::OPTION_CLIENT_ID );
		$client_secret = get_option( self::OPTION_CLIENT_SECRET );

		if ( empty( $account_id ) || empty( $client_id ) || empty( $client_secret ) ) {
			return new WP_Error( 'vczapi_oauth_missing_credentials', 'Zoom OAuth credentials are not configured.' );
		}

		$result = $this->generateAccessToken( $account_id, $client_id, $client_secret );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Annotate with created_at for expiry calculation.
		$result->date = time();
		update_option( self::OPTION_OAUTH_DATA, $result );

		return $result;
	}

	/**
	 * Try to refresh with a transient lock to avoid thundering herd.
	 *
	 * @return void
	 */
	protected function refreshWithLock() {
		if ( get_transient( self::TRANSIENT_REFRESH_LOCK ) ) {
			// Another process is refreshing; give it a moment.
			usleep( 250000 ); // 250ms

			return;
		}

		// Set lock for a short period.
		set_transient( self::TRANSIENT_REFRESH_LOCK, 1, 30 );

		try {
			$this->generateFromSavedCredentials();
		} finally {
			delete_transient( self::TRANSIENT_REFRESH_LOCK );
		}
	}

	/**
	 * Generate token from Zoom OAuth server.
	 *
	 * @param  string  $account_id
	 * @param  string  $client_id
	 * @param  string  $client_secret
	 *
	 * @return object|WP_Error
	 */
	protected function generateAccessToken( string $account_id, string $client_id, string $client_secret ) {
		if ( empty( $account_id ) ) {
			return new WP_Error( 'vczapi_oauth_account_id', 'Account ID is missing.' );
		}
		if ( empty( $client_id ) ) {
			return new WP_Error( 'vczapi_oauth_client_id', 'Client ID is missing.' );
		}
		if ( empty( $client_secret ) ) {
			return new WP_Error( 'vczapi_oauth_client_secret', 'Client Secret is missing.' );
		}

		$basic = base64_encode( $client_id . ':' . $client_secret );
		$args  = array(
			'method'  => 'POST',
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Basic ' . $basic,
			),
			'body'    => array(
				'grant_type' => 'account_credentials',
				'account_id' => $account_id,
			),
		);

		$response = wp_remote_post( 'https://zoom.us/oauth/token', $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code    = (int) wp_remote_retrieve_response_code( $response );
		$body    = wp_remote_retrieve_body( $response );
		$decoded = json_decode( $body );

		if ( $code === 200 && is_object( $decoded ) && ! empty( $decoded->access_token ) ) {
			// Add created_at when saving above.
			return $decoded;
		}

		$message = wp_remote_retrieve_response_message( $response );
		if ( is_object( $decoded ) && ! empty( $decoded->errorMessage ) ) {
			$message = $decoded->errorMessage;
		}

		return new WP_Error(
			$code ? $code : 'vczapi_oauth_http_error',
			$message ? $message : 'OAuth token request failed.',
			array(
				'response_code' => $code,
				'raw_body'      => $body,
			)
		);
	}
}
