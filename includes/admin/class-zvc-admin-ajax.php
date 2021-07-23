<?php
/**
 * Class for all the administration ajax calls
 *
 * @since   2.0.0
 * @author  Deepen
 */

class Zoom_Video_Conferencing_Admin_Ajax {

	public function __construct() {
		//Delete Meeting
		add_action( 'wp_ajax_zvc_delete_meeting', array( $this, 'delete_meeting' ) );
		add_action( 'wp_ajax_zvc_bulk_meetings_delete', array( $this, 'delete_bulk_meeting' ) );
		add_action( 'wp_ajax_zoom_dimiss_notice', array( $this, 'dismiss_notice' ) );
		add_action( 'wp_ajax_check_connection', array( $this, 'check_connection' ) );

		//Join via browser Auth Call
		add_action( 'wp_ajax_nopriv_get_auth', array( $this, 'get_auth' ) );
		add_action( 'wp_ajax_get_auth', array( $this, 'get_auth' ) );

		//Call meeting state
		add_action( 'wp_ajax_nopriv_state_change', array( $this, 'state_change' ) );
		add_action( 'wp_ajax_state_change', array( $this, 'state_change' ) );

		//AJAX call for fetching users
		add_action( 'wp_ajax_get_assign_host_id', [ $this, 'assign_host_id' ] );
		add_action( 'wp_ajax_vczapi_get_wp_users', [ $this, 'get_wp_usersByRole' ] );
	}

	/**
	 * Delete a Meeting
	 *
	 * @author   Deepen
	 * @since    2.0.0
	 * @modified 2.1.0
	 */
	public function delete_meeting() {
		check_ajax_referer( '_nonce_zvc_security', 'security' );

		$meeting_id   = filter_input( INPUT_POST, 'meeting_id' );
		$meeting_type = filter_input( INPUT_POST, 'type' );
		if ( $meeting_id ) {
			if ( ! empty( $meeting_type ) && $meeting_type === "webinar" ) {
				zoom_conference()->deleteAWebinar( $meeting_id );
			} else {
				zoom_conference()->deleteAMeeting( $meeting_id );
			}

			wp_send_json( array( 'error' => 0, 'msg' => __( "Deleted Meeting with ID", "video-conferencing-with-zoom-api" ) . ': ' . $meeting_id ) );
		} else {
			wp_send_json( array(
				'error' => 1,
				'msg'   => __( "An error occured. Host ID and Meeting ID not defined properly.", "video-conferencing-with-zoom-api" )
			) );
		}

		wp_die();
	}

	/**
	 * Delete Meeting in Bulk
	 *
	 * @since    1.0.0
	 * @modified 2.1.0
	 */
	public function delete_bulk_meeting() {
		check_ajax_referer( '_nonce_zvc_security', 'security' );

		$deleted      = false;
		$meeting_ids  = filter_input( INPUT_POST, 'meetings_id', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$meeting_type = filter_input( INPUT_POST, 'type' );
		if ( ! empty( $meeting_ids ) ) {
			$meeting_count = count( $meeting_ids );
			foreach ( $meeting_ids as $meeting_id ) {
				if ( ! empty( $meeting_type ) && $meeting_type === "webinar" ) {
					zoom_conference()->deleteAWebinar( $meeting_id );
				} else {
					zoom_conference()->deleteAMeeting( $meeting_id );
				}
				$deleted = true;
			}

			if ( $deleted ) {
				wp_send_json( array(
					'error' => 0,
					'msg'   => sprintf( __( "Deleted %d Meeting(s).", "video-conferencing-with-zoom-api" ), $meeting_count )
				) );
			}
		} else {
			wp_send_json( array( 'error' => 1, 'msg' => __( "You need to select a data in order to initiate this action." ) ) );
		}

		wp_die();
	}

	/**
	 * Dismiss admin notice
	 */
	public function dismiss_notice() {
		update_option( 'zoom_api_notice', 1 );
		wp_send_json( 1 );
		wp_die();
	}

	/**
	 * Check API connection
	 *
	 * @since 3.0.0
	 * @author Deepen Bajracharya
	 */
	public function check_connection() {
		check_ajax_referer( '_nonce_zvc_security', 'security' );

		$test = json_decode( zoom_conference()->listUsers() );
		if ( ! empty( $test ) ) {
			if ( ! empty( $test->code ) ) {
				wp_send_json( $test->message );
			}

			if ( http_response_code() === 200 ) {
				//After user has been created delete this transient in order to fetch latest Data.
				video_conferencing_zoom_api_delete_user_cache();

				wp_send_json( "API Connection is good. Please refresh !" );
			} else {
				wp_send_json( $test );
			}
		}
		wp_die();
	}

	/**
	 * Get authenticated
	 *
	 * @since 3.2.0
	 * @author Deepen Bajracharya
	 */
	public function get_auth() {
		check_ajax_referer( '_nonce_zvc_security', 'noncce' );

		$zoom_api_key    = get_option( 'zoom_api_key' );
		$zoom_api_secret = get_option( 'zoom_api_secret' );

		$meeting_id = filter_input( INPUT_POST, 'meeting_id' );
		if ( ! empty( $zoom_api_key ) && ! empty( $zoom_api_secret ) ) {
			$signature = $this->generate_signature( $zoom_api_key, $zoom_api_secret, $meeting_id, 0 );
			wp_send_json_success( array( 'sig' => $signature, 'key' => $zoom_api_key ) );
		} else {
			wp_send_json_error( 'Error occured!' );
		}

		wp_die();
	}

	/**
	 * Generate Signature
	 *
	 * @param $api_key
	 * @param $api_sercet
	 * @param $meeting_number
	 * @param $role
	 *
	 * @return string
	 * @since 3.2.0
	 *
	 * @author ZoomUS
	 */
	private function generate_signature( $api_key, $api_sercet, $meeting_number, $role ) {
		//Set the timezone to UTC
		date_default_timezone_set( "UTC" );
		$time = time() * 1000 - 30000; //time in milliclearseconds (or close enough)
		$data = base64_encode( $api_key . $meeting_number . $time . $role );
		$hash = hash_hmac( 'sha256', $data, $api_sercet, true );
		$_sig = $api_key . "." . $meeting_number . "." . $time . "." . $role . "." . base64_encode( $hash );

		//return signature, url safe base64 encoded
		return rtrim( strtr( base64_encode( $_sig ), '+/', '-_' ), '=' );
	}

	/**
	 * Change State of the Meeting from here !
	 */
	public function state_change() {
		check_ajax_referer( '_nonce_zvc_security', 'accss' );

		$type       = sanitize_text_field( filter_input( INPUT_POST, 'type' ) );
		$state      = sanitize_text_field( filter_input( INPUT_POST, 'state' ) );
		$meeting_id = sanitize_text_field( filter_input( INPUT_POST, 'id' ) );
		$post_id    = sanitize_text_field( filter_input( INPUT_POST, 'post_id' ) );

		$success = false;
		switch ( $state ) {
			case 'end':
				if ( $type === "shortcode" ) {
					$meeting_options = get_option( 'zoom_api_meeting_options' );
					if ( ! empty( $meeting_options ) ) {
						$meeting_options[ $meeting_id ]['state'] = 'ended';
						update_option( 'zoom_api_meeting_options', $meeting_options );
					} else {
						$new[ $meeting_id ]['state'] = 'ended';
						update_option( 'zoom_api_meeting_options', $new );
					}

					$success = true;
				}

				if ( $type === "post_type" ) {
					$meeting = get_post_meta( $post_id, '_meeting_zoom_details', true );
					if ( ! empty( $meeting ) ) {
						$meeting->state = 'ended';
						update_post_meta( $post_id, '_meeting_zoom_details', $meeting );
					}

					$success = true;
				}

				break;
			case 'resume':
				if ( $type === "shortcode" ) {
					$meeting_options = get_option( 'zoom_api_meeting_options' );
					unset( $meeting_options[ $meeting_id ] );
					update_option( 'zoom_api_meeting_options', $meeting_options );
					$success = true;
				}

				if ( $type === "post_type" ) {
					$meeting = get_post_meta( $post_id, '_meeting_zoom_details', true );
					if ( ! empty( $meeting ) ) {
						$meeting->state = '';
						update_post_meta( $post_id, '_meeting_zoom_details', $meeting );
					}

					$success = true;
				}
				break;

		}

		if ( $success ) {
			wp_send_json_success( $success );
		} else {
			wp_send_json_error( $success );
		}

		wp_die();
	}

	/**
	 * Assign Host ID page
	 */
	public function assign_host_id() {
		$users      = vczapi_getWpUsers_basedon_UserRoles();
		$result     = array();
		$zoom_users = video_conferencing_zoom_api_get_user_transients();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$user_zoom_hostid = get_user_meta( $user->ID, 'user_zoom_hostid', true );
				$host_id_field    = '';
				if ( ! empty( $zoom_users ) ) {
					$host_id_field .= '<select name="zoom_host_id[' . $user->ID . ']" style="width:100%">';
					$host_id_field .= '<option value="">' . __( 'Not a Host', 'video-conferencing-with-zoom-api' ) . '</option>';
					foreach ( $zoom_users as $zoom_usr ) {
						$selected_host_id = ! empty( $user_zoom_hostid ) && $user_zoom_hostid === $zoom_usr->id ? 'selected="selected"' : false;
						$full_name        = ! empty( $zoom_usr->first_name ) ? $zoom_usr->first_name . ' ' . $zoom_usr->last_name : $zoom_usr->email;
						$host_id_field    .= '<option value="' . $zoom_usr->id . '" ' . $selected_host_id . '>' . $full_name . '</option>';
					}
					$host_id_field .= '</select>';

					$result[] = array(
						'id'      => $user->ID,
						'email'   => $user->user_email,
						'name'    => empty( $user->first_name ) ? $user->display_name : $user->first_name . ' ' . $user->last_name,
						'host_id' => $host_id_field
					);
				}
			}

			wp_send_json_success( $result );

			wp_die();
		}
	}

	public function get_wp_usersByRole() {
		$search_string = filter_input( INPUT_GET, 'term' );
		$users         = vczapi_getWpUsers_basedon_UserRoles( $search_string );
		$results       = array();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$results[] = array(
					'id'   => $user->ID,
					'text' => $user->user_email
				);
			}
		}

		wp_send_json( array( 'results' => $results ) );

		wp_die();
	}
}

new Zoom_Video_Conferencing_Admin_Ajax();
