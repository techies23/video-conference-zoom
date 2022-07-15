<?php

class VCZAPI_Admin_Setup_Wizard {
	public static ?VCZAPI_Admin_Setup_Wizard $instance = null;

	/**
	 * @return VCZAPI_Admin_Setup_Wizard|null
	 */
	public static function get_instance(): ?VCZAPI_Admin_Setup_Wizard {
		return is_null( self::$instance ) ? self::$instance = new self() : self::$instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_vczapi_save_oauth_credentials', [ $this, 'save_oauth_credentials' ] );
		add_action( 'wp_ajax_vczapi_save_app_sdk_credentials', [ $this, 'save_app_sdk_credentials' ] );
	}

	public function save_oauth_credentials() {
		$nonce = filter_input( INPUT_POST, 's2sOauth_wizard_nonce' );

		if ( ! wp_verify_nonce( $nonce, 'verify_s2sOauth_wizard_nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$account_id    = filter_input( INPUT_POST, 'vczapi_wizard_oauth_account_id' );
		$client_id     = filter_input( INPUT_POST, 'vczapi_wizard_oauth_client_id' );
		$client_secret = filter_input( INPUT_POST, 'vczapi_wizard_oauth_client_secret' );

		$result = \vczapi\S2SOAuth::get_instance()->generateAndSaveAccessToken( $account_id, $client_id, $client_secret );
		if ( ! is_wp_error( $result ) ) {
			$decoded_users = json_decode( zoom_conference()->listUsers() );
			if ( ! is_null( $decoded_users ) ) {
				wp_send_json_success( [ 'message' => 'Credentials verified and saved, please continue to next step' ] );
			} else {
				wp_send_json_error( [ 'code' => 'Random', 'message' => 'Could not make API Call - please try saving again' ] );
			}
		} else {
			wp_send_json_error( [ 'code' => $result->get_error_code(), 'message' => $result->get_error_message() . ' Please double check your credentials' ] );
		}
	}

	public function save_app_sdk_credentials() {
		$nonce = filter_input( INPUT_POST, 's2sOauth_wizard_nonce' );

		if ( ! wp_verify_nonce( $nonce, 'verify_s2sOauth_wizard_nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$vczapi_sdk_key        = filter_input( INPUT_POST, 'vczapi_wizard_sdk_key' );
		$vczapi_sdk_secret_key = filter_input( INPUT_POST, 'vczapi_wizard_sdk_secret_key' );
		if ( empty( $vczapi_sdk_key) ) {
			wp_send_json_error(['message' => 'SDK Key is missing, please double check your credentials'] );
		}else if( empty($vczapi_sdk_secret_key)){
			wp_send_json_error(['message' => 'SDK Secret Key is missing, please double check your credentials'] );
		}

		update_option( 'vczapi_sdk_key', $vczapi_sdk_key );
		update_option( 'vczapi_sdk_secret_key', $vczapi_sdk_secret_key );
		
		wp_send_json_success(['message' => 'App SDK Keys succesfully saved, please check that join via browser is working on your site.']);
	}
}

VCZAPI_Admin_Setup_Wizard::get_instance();