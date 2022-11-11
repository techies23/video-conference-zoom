<?php
/**
 * Users Controller
 *
 * @since   2.0.0
 * @author  Deepen
 */

class Zoom_Video_Conferencing_Admin_Users {

	public static $message = '';
	public $settings;

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
			require_once ZVC_PLUGIN_VIEWS_PATH . '/live/tpl-list-pending-users.php';
		} else {
			//Get Template
			require_once ZVC_PLUGIN_VIEWS_PATH . '/live/tpl-list-users.php';
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

	/**
	 * Assign Host ID
	 */
	static function assign_host_id() {
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-js' );

		if ( isset( $_POST['saving_host_id'] ) ) {
			check_admin_referer( '_zoom_assign_hostid_nonce_action', '_zoom_assign_hostid_nonce' );

			$host_ids  = filter_input( INPUT_POST, 'zoom_host_id', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$email_ids = filter_input( INPUT_POST, 'zoom_host_email', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			if ( ! empty( $host_ids ) ) {
				foreach ( $host_ids as $k => $host_id ) {
					if ( $host_id == "0" ) {
						update_user_meta( $k, 'user_zoom_hostid', '' );
					} else {
						update_user_meta( $k, 'user_zoom_hostid', $host_id );
					}
				}
			}

			if ( ! empty( $email_ids ) ) {
				foreach ( $email_ids as $k => $email_id ) {
					if ( $email_id == "Not a Host" ) {
						update_user_meta( $k, 'vczapi_user_zoom_email_address', '' );
					} else {
						update_user_meta( $k, 'vczapi_user_zoom_email_address', $email_id );
					}
				}
			}

			self::set_message( 'updated', __( "Saved !", "video-conferencing-with-zoom-api" ) );
		}

		require_once ZVC_PLUGIN_VIEWS_PATH . '/live/tpl-assign-host-id.php';
	}

	static function get_message() {
		return self::$message;
	}

	static function set_message( $class, $message ) {
		self::$message = '<div class=' . $class . '><p>' . $message . '</p></div>';
	}
}

new Zoom_Video_Conferencing_Admin_Users();