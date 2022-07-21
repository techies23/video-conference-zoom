<?php

class VCZAPI_Admin_Setup_Wizard {

	public static $instance = null;

	/**
	 * @return VCZAPI_Admin_Setup_Wizard|null
	 */
	public static function get_instance() {
		return is_null( self::$instance ) ? self::$instance = new self() : self::$instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_vczapi_save_oauth_credentials', [ $this, 'save_oauth_credentials' ] );
		add_action( 'wp_ajax_vczapi_save_app_sdk_credentials', [ $this, 'save_app_sdk_credentials' ] );
	}

	/**
	 * @return void
	 */
	public function save_oauth_credentials() {
		$nonce = filter_input( INPUT_POST, 's2sOauth_wizard_nonce' );

		if ( ! wp_verify_nonce( $nonce, 'verify_s2sOauth_wizard_nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$account_id      = sanitize_text_field( filter_input( INPUT_POST, 'vczapi_wizard_oauth_account_id' ) );
		$client_id       = sanitize_text_field( filter_input( INPUT_POST, 'vczapi_wizard_oauth_client_id' ) );
		$client_secret   = sanitize_text_field( filter_input( INPUT_POST, 'vczapi_wizard_oauth_client_secret' ) );
		$delete_jwt_keys = sanitize_text_field( filter_input( INPUT_POST, 'vczapi_wizard_delete_jwt_keys' ) );

		//added for Oauth S2S
		update_option( 'vczapi_oauth_account_id', $account_id );
		update_option( 'vczapi_oauth_client_id', $client_id );
		update_option( 'vczapi_oauth_client_secret', $client_secret );

		$result = \vczapi\S2SOAuth::get_instance()->generateAndSaveAccessToken( $account_id, $client_id, $client_secret );
		if ( ! is_wp_error( $result ) ) {
			//this can't be a cached request
			$decoded_users = json_decode( zoom_conference()->listUsers() );
			if ( ! is_null( $decoded_users ) ) {
				if ( $delete_jwt_keys == 'on' ) {
					delete_option( 'zoom_api_key' );
					delete_option( 'zoom_api_secret' );
				}
				wp_send_json_success( [ 'message' => 'Credentials verified and saved, please continue to next step' ] );
			} else {
				wp_send_json_error( [ 'code' => 'Random', 'message' => 'Could not make API Call - please try saving again' ] );
			}
		} else {
			wp_send_json_error( [ 'code' => $result->get_error_code(), 'message' => $result->get_error_message() . ' Please double check your credentials' ] );
		}
	}

	/**
	 * @return void
	 */
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
		update_option( 'vczapi_sdk_key', $vczapi_sdk_key );
		update_option( 'vczapi_sdk_secret_key', $vczapi_sdk_secret_key );
		if ( empty( $vczapi_sdk_key ) ) {
			wp_send_json_error( [ 'message' => 'SDK Key is missing, please double check your credentials' ] );
		} elseif ( empty( $vczapi_sdk_secret_key ) ) {
			wp_send_json_error( [ 'message' => 'SDK Secret Key is missing, please double check your credentials' ] );
		}

		wp_send_json_success( [ 'message' => 'App SDK Keys succesfully saved, please check that join via browser is working on your site.' ] );
	}
}

VCZAPI_Admin_Setup_Wizard::get_instance();