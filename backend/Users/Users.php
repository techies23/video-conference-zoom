<?php

namespace Codemanas\VczApi\Backend\Users;

/**
 * Class Users
 * @since 4.0.0
 * @package Codemanas\VczApi\Backend\Users
 */
class Users {

	public static $message = '';

	/**
	 * List meetings page
	 *
	 * @since   1.0.0
	 * @changes in CodeBase
	 * @author  Deepen Bajracharya
	 */
	public static function list_users() {
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-js' );

		//Check if any transient by name is available
		if ( isset( $_GET['flush'] ) == true ) {
			video_conferencing_zoom_api_delete_user_cache();
			self::set_message( 'updated', __( "Flushed User Cache!", "video-conferencing-with-zoom-api" ) );
		}

		if ( isset( $_GET['status'] ) && $_GET['status'] === "pending" ) {
			//Get Template
			require_once VCZAPI_BACKEND_PATH . '/Users/views/list-pending-users.php';
		} else {
			//Get Template
			require_once VCZAPI_BACKEND_PATH . '/Users/views/list-users.php';
		}
	}

	/**
	 * Add Zoom users view
	 *
	 * @since   1.0.0
	 * @changes in CodeBase
	 * @author  Deepen Bajracharya
	 */
	public static function add_zoom_users() {
		wp_enqueue_script( 'video-conferencing-with-zoom-api-js' );

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

			$result = zoom_conference()->createAUser( $postData );
			if ( ! empty( $result->code ) ) {
				self::set_message( 'error', $result->message );
			} else {
				self::set_message( 'updated', __( "Created a User. Please check email for confirmation. Added user will only appear in the list after approval.", "video-conferencing-with-zoom-api" ) );

				update_user_meta( $postData['user_id'], 'user_zoom_hostid', $result->id );

				//After user has been created delete this transient in order to fetch latest Data.
				video_conferencing_zoom_api_delete_user_cache();
			}
		}

		require_once VCZAPI_BACKEND_PATH . '/Users/views/add-user.php';
	}

	static function assign_host_id() {
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-js' );

		if ( isset( $_POST['saving_host_id'] ) ) {
			check_admin_referer( '_zoom_assign_hostid_nonce_action', '_zoom_assign_hostid_nonce' );

			$host_ids = filter_input( INPUT_POST, 'zoom_host_id', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			foreach ( $host_ids as $k => $host_id ) {
				update_user_meta( $k, 'user_zoom_hostid', $host_id );
			}

			self::set_message( 'updated', __( "Saved !", "video-conferencing-with-zoom-api" ) );
		}

		require_once VCZAPI_BACKEND_PATH . '/Users/views/assign-host-id.php';
	}

	static function get_message() {
		return self::$message;
	}

	static function set_message( $class, $message ) {
		self::$message = '<div class=' . $class . '><p>' . $message . '</p></div>';
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