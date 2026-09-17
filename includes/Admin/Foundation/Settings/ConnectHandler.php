<?php

namespace Codemanas\VczApi\Admin\Foundation\Settings;

use Codemanas\VczApi\Admin\Foundation\Notification;
use Codemanas\VczApi\Zoom\Auth\S2SOAuth;

class ConnectHandler {

	/**
	 * Handle the classic (non-AJAX) form submission on admin_init.
	 *
	 * @return void
	 */
	public function handle(): void {
		if ( ! isset( $_POST['vczapi_zoom_connect_nonce'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( sanitize_key( $_POST['vczapi_zoom_connect_nonce'] ), 'verify_vczapi_zoom_connect' ) ) {
			return;
		}

		$result = $this->saveAndVerifyCredentials();

		if ( is_wp_error( $result ) ) {
			Notification::setNotice(
				sprintf( esc_html__( 'Zoom Auth Error: "%s" - %s', 'video-conferencing-with-zoom-api' ), esc_html( $result->get_error_code() ), esc_html( $result->get_error_message() ) ),
				'error'
			);

			delete_option( S2SOAuth::OPTION_OAUTH_DATA );

			return;
		}

		Notification::setNotice( __( 'Zoom: Credentials successfully verified and saved.', 'video-conferencing-with-zoom-api' ), 'success' );
	}

	/**
	 * AJAX handler for saving and verifying connect credentials.
	 *
	 * @return void
	 */
	public function ajaxHandler(): void {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'verify_vczapi_zoom_connect', 'vczapi_zoom_connect_nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'video-conferencing-with-zoom-api' ) ), 403 );
		}

		$result = $this->saveAndVerifyCredentials();

		if ( is_wp_error( $result ) ) {
			delete_option( S2SOAuth::OPTION_OAUTH_DATA );
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 );
		}

		wp_send_json_success( array( 'connected' => true ) );
	}

	/**
	 * Sanitize and persist the credentials, then attempt to mint a fresh
	 * access token to prove they are valid.
	 *
	 * @return string|\WP_Error Access token on success, WP_Error on failure.
	 */
	private function saveAndVerifyCredentials(): string|\WP_Error {
		$credentials = [
			'vczapi_oauth_account_id'    => sanitize_text_field( $_POST['vczapi_oauth_account_id'] ?? '' ),
			'vczapi_oauth_client_id'     => sanitize_text_field( $_POST['vczapi_oauth_client_id'] ?? '' ),
			'vczapi_oauth_client_secret' => sanitize_text_field( $_POST['vczapi_oauth_client_secret'] ?? '' ),
			'vczapi_sdk_key'             => sanitize_text_field( $_POST['vczapi_sdk_key'] ?? '' ),
			'vczapi_sdk_secret_key'      => sanitize_text_field( $_POST['vczapi_sdk_secret_key'] ?? '' ),
		];

		foreach ( $credentials as $option_name => $value ) {
			update_option( $option_name, $value );
		}

		$auth = S2SOAuth::get_instance();
		$auth->regenerateAccessTokenAndSave();

		return $auth->getAccessToken();
	}
}