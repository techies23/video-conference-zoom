<?php

namespace Codemanas\VczApi\Admin\Controller;

/**
 * Register user page.
 */
class UserController {

	private static ?UserController $instance = null;

	public static function get_instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
	}

	public static function list() {
		if ( isset( $_GET['status'] ) && $_GET['status'] === "pending" ) {
			//Get Template
			require_once ZVC_PLUGIN_VIEWS_PATH . '/live/tpl-list-pending-users.php';
		} else {
			//Get Template
			require_once ZVC_PLUGIN_VIEWS_PATH . '/live/tpl-list-users.php';
		}
	}

	public function add() {
		if ( isset( $_POST['add_zoom_user'] ) ) {
			check_admin_referer( '_zoom_add_user_nonce_action', '_zoom_add_user_nonce' );
			$postData = array(
				'action'     => filter_input( INPUT_POST, 'action' ),
				'email'      => sanitize_email( filter_input( INPUT_POST, 'email' ) ),
				'first_name' => sanitize_text_field( filter_input( INPUT_POST, 'first_name' ) ),
				'last_name'  => sanitize_text_field( filter_input( INPUT_POST, 'last_name' ) ),
				'type'       => filter_input( INPUT_POST, 'type' ),
				'user_id'    => filter_input( INPUT_POST, 'user_id' )
			);

			$created_user = zoom_conference()->createAUser( $postData );
			$result       = json_decode( $created_user );
			if ( ! empty( $result->code ) ) {
				self::set_message( 'error', $result->message );
			} else {
				self::set_message( 'updated', __( "Created a User. Please check email for confirmation. Added user will only appear in the list after approval.", "video-conferencing-with-zoom-api" ) );

				update_user_meta( $postData['user_id'], 'user_zoom_hostid', $result->id );

				//After user has been created delete this transient in order to fetch latest Data.
				video_conferencing_zoom_api_delete_user_cache();
			}
		}

		require_once ZVC_PLUGIN_VIEWS_PATH . '/live/tpl-add-user.php';
	}
}