<?php
namespace Codemanas\VczApi\Admin\Foundation\Settings;

use Codemanas\VczApi\Admin\Foundation\Notification;

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

		$access_token = \vczapi\S2SOAuth::get_instance()->generateAndSaveAccessToken(
			$credentials['vczapi_oauth_account_id'],
			$credentials['vczapi_oauth_client_id'],
			$credentials['vczapi_oauth_client_secret']
		);

		if ( is_wp_error( $access_token ) ) {
			Notification::setNotice(
				sprintf( esc_html__( 'Zoom OAuth Error Code: "%s" - %s', 'video-conferencing-with-zoom-api' ), esc_html( $access_token->get_error_code() ), esc_html( $access_token->get_error_message() ) ),
				'error'
			);

			video_conferencing_zoom_api_delete_user_cache();
			delete_option( 'vczapi_global_oauth_data' );

			return;
		}

		if ( 'on' === sanitize_text_field( $_POST['vczapi-delete-jwt-keys'] ?? '' ) ) {
			delete_option( 'zoom_api_key' );
			delete_option( 'zoom_api_secret' );
		}

		$decoded_users = json_decode( zoom_conference()->listUsers() );
		if ( ! empty( $decoded_users->code ) && is_admin() ) {
			add_action( 'admin_notices', 'vczapi_check_connection_error' );
		} else {
			vczapi_set_cache( '_zvc_user_lists', $decoded_users->users ?? false, 108000 );
		}

		Notification::setNotice( __( 'Zoom: Credentials successfully verified and saved.', 'video-conferencing-with-zoom-api' ), 'success' );
		video_conferencing_zoom_api_get_user_transients();
	}
}