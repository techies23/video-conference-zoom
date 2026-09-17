<?php

namespace Codemanas\VczApi\Admin\Foundation\Settings;

use Codemanas\VczApi\Admin\Foundation\Notification;
use Codemanas\VczApi\Zoom\Auth\S2SOAuth;

class ConnectHandler {

	public function handle(): void {
		if ( ! isset( $_POST['vczapi_zoom_connect_nonce'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( sanitize_key( $_POST['vczapi_zoom_connect_nonce'] ), 'verify_vczapi_zoom_connect' ) ) {
			return;
		}

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
		$access_token = $auth->getAccessToken();
		if ( is_wp_error( $access_token ) ) {
			Notification::setNotice(
				sprintf( esc_html__( 'Zoom OAuth Error Code: "%s" - %s', 'video-conferencing-with-zoom-api' ), esc_html( $access_token->get_error_code() ), esc_html( $access_token->get_error_message() ) ),
				'error'
			);

			delete_option( 'vczapi_global_oauth_data' );

			return;
		}

		if ( 'on' === sanitize_text_field( $_POST['vczapi-delete-jwt-keys'] ?? '' ) ) {
			delete_option( 'zoom_api_key' );
			delete_option( 'zoom_api_secret' );
		}

		$decoded_users = zoom_conference_v2()->users()->list( array(
			'status'    => 'active',
			'page_size' => 1,
		) );
		if ( is_wp_error( $decoded_users ) && is_admin() ) {
			add_action( 'admin_notices', 'vczapi_check_connection_error' );
		}

		Notification::setNotice( __( 'Zoom: Credentials successfully verified and saved.', 'video-conferencing-with-zoom-api' ), 'success' );
	}
}