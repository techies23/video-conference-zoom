<?php

namespace Codemanas\VczApi\Backend\Settings;

use Codemanas\VczApi\Includes\Api\Methods;
use Codemanas\VczApi\Includes\Fields;

class Ajax {

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

	public function __construct() {
		add_action( 'wp_ajax_save_jwt_keys', [ $this, 'save_jwt' ] );
	}

	/**
	 * Save JWT keys
	 */
	public function save_jwt() {
		$postData = [
			'api_key'    => sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_key' ) ),
			'api_secret' => sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_secret' ) )
		];
		Fields::set_option( 'jwt_keys', $postData );
		Fields::set_option( 'using_oauth', '' ); //Set null value for backwards compatibility

		//Check if JWT keys are valid
		$api   = Methods::instance();
		$users = $api->listUsers();
		if ( ! empty( $users->code ) && ! empty( $users->message ) ) {
			wp_send_json_error( $users->message );
		}

		wp_send_json_success( __( "Zoom JWT keys have been saved. Reloading page in 2 seconds.", "video-conferencing-with-zoom-api" ) );

		wp_die();
	}
}