<?php

use Codemanas\VczApi\Helpers\Encryption;

/**
 * Class for all the administration ajax calls
 *
 * @since   2.0.0
 * @author  Deepen
 */
class Zoom_Video_Conferencing_Admin_Ajax {

	public function __construct() {
		//Delete Meeting
		add_action( 'wp_ajax_zoom_dimiss_notice', array( $this, 'dismiss_notice' ) );
		add_action( 'wp_ajax_check_connection', array( $this, 'check_connection' ) );

		//Join via browser Auth Call
		add_action( 'wp_ajax_nopriv_get_auth', array( $this, 'get_auth' ) );
		add_action( 'wp_ajax_get_auth', array( $this, 'get_auth' ) );

		//AJAX call for fetching users
		add_action( 'wp_ajax_get_assign_host_id', [ $this, 'assign_host_id' ] );

		//Ajax called for dismissing notice
		add_action( 'wp_ajax_vczapi_dismiss_admin_notice', [ $this, 'admin_notice' ] );
	}

	public function admin_notice() {
		$option = filter_input( INPUT_POST, 'option' );
		$nonce  = filter_input( INPUT_POST, 'security' );
		if ( ! wp_verify_nonce( $nonce, 'vczapi-dismiss-nonce' ) ) {
			wp_send_json_error( [ 'message' => 'Error' ] );
		} elseif ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Error' ] );
		}


		if ( $option == 'vczapi_dismiss_sdk_not_active_notice' ) {
			update_option( 'vczapi_dismiss_sdk_not_active_notice', true );
		}
		wp_send_json_success();
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
	 * @since  3.0.0
	 * @author Deepen Bajracharya
	 */
	public function check_connection() {
		check_ajax_referer( '_nonce_zvc_security', 'security' );

		$type = filter_input( INPUT_POST, 'type' );
		if ( $type === "oAuth" ) {
			$test = \Codemanas\VczApi\Requests\Zoom::instance()->me();
			if ( ! empty( $test->code ) ) {
				wp_send_json_error( $test->message );
			}

			//After user has been created delete this transient in order to fetch latest Data.
			video_conferencing_zoom_api_delete_user_cache();
			wp_send_json_success( [ 'msg' => "API Connection is good. You can refresh this and start creating your Zoom Events." ] );
		}

		wp_die();
	}

	/**
	 * Get authenticated io
	 *
	 * @since  3.2.0
	 * @author Deepen Bajracharya
	 */
	public function get_auth() {
		// check_ajax_referer('_nonce_zvc_security', 'zvc_security');

		$referer  = wp_get_referer();
		$home_url = home_url();

		// Block requests with no referer or external referers
		if ( ! $referer || parse_url( $referer, PHP_URL_HOST ) !== parse_url( $home_url, PHP_URL_HOST ) ) {
			wp_send_json_error( 'Invalid request source.' );
		}

		// 2. Sanitize and validate the Meeting ID
		$meeting_id = filter_input( INPUT_POST, 'meeting_id', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $meeting_id ) ) {
			wp_send_json_error( 'Invalid Meeting ID' );
		}

		if ( vczapi_is_sdk_enabled() ) {
			$sdk_key    = get_option( 'vczapi_sdk_key' );
			$secret_key = get_option( 'vczapi_sdk_secret_key' );

			if ( empty( $sdk_key ) || empty( $secret_key ) ) {
				wp_send_json_error( 'SDK configuration error.' );
			}

			// Ensure role is 0 (Participant). NEVER allow Role 1 (Host) for guests.
			$signature = $this->generate_sdk_signature( $sdk_key, $secret_key, $meeting_id, 0 );

			wp_send_json_success( [
				'sig'  => $signature,
				'type' => 'sdk'
			] );
		} else {
			wp_send_json_error( 'Service Unavailable' );
		}

		wp_die();
	}

	private function generate_sdk_signature( $sdk_key, $secret_key, $meeting_number, $role ) {
		$iat     = round( ( time() * 1000 - 30000 ) / 1000 );
		$exp     = $iat + 86400;
		$payload = [
			'sdkKey'   => $sdk_key,
			'mn'       => $meeting_number,
			'role'     => $role,
			'iat'      => $iat,
			'exp'      => $exp,
			'appKey'   => $sdk_key,
			'tokenExp' => $exp,
		];

		if ( empty( $secret_key ) ) {
			return false;
		}

		return \Firebase\JWT\JWT::encode( $payload, $secret_key, 'HS256' );
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
	 * @throws Exception
	 */
	private function generate_signature( $api_key, $api_sercet, $meeting_number, $role ) {
		//Set the timezone to UTC
		$date_utc = new \DateTime( "now", new \DateTimeZone( "UTC" ) );
		$time     = $date_utc->getTimestamp() * 1000 - 30000; //time in milliclearseconds (or close enough)
		$data     = base64_encode( $api_key . $meeting_number . $time . $role );
		$hash     = hash_hmac( 'sha256', $data, $api_sercet, true );
		$_sig     = $api_key . "." . $meeting_number . "." . $time . "." . $role . "." . base64_encode( $hash );

		//return signature, url safe base64 encoded
		return rtrim( strtr( base64_encode( $_sig ), '+/', '-_' ), '=' );
	}

	/**
	 * Assign Host ID page
	 */
	public function assign_host_id(): void {
		check_ajax_referer( '_nonce_zvc_security', 'security' );

		$draw   = filter_input( INPUT_GET, 'draw' );
		$length = filter_input( INPUT_GET, 'length' );
		$start  = filter_input( INPUT_GET, 'start' );
		$search = filter_input( INPUT_GET, 'search', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$args   = [
			'number' => ! empty( $length ) ? absint( $length ) : 10,
			'paged'  => $start == 0 ? 1 : $start / $length + 1,
		];

		if ( ! empty( $search['value'] ) ) {
			$args['search'] = '*' . $search['value'] . '*';
		}

		$users      = vczapi_getWpUsers_basedon_UserRoles( $args );
		$tableData  = array();
		$zoom_users = video_conferencing_zoom_api_get_user_transients();
		if ( ! empty( $users ) ) {
			foreach ( $users->get_results() as $user ) {
				$user_zoom_hostid = get_user_meta( $user->ID, 'user_zoom_hostid', true );
				$email_address    = get_user_meta( $user->ID, 'vczapi_user_zoom_email_address', true );
				$host_id_field    = '';
				$host_id_field    .= '<select data-userid="' . $user->ID . '" name="zoom_host_id[' . $user->ID . ']" class="vczapi-get-zoom-hosts" style="width:100%">';
				if ( ! empty( $user_zoom_hostid ) && ! empty( $email_address ) ) {
					$host_id_field .= '<option value="' . $user_zoom_hostid . '" selected>' . $email_address . '</option>';
				}

				//This is for backwards compatibility before version 4.0.7
				if ( ! empty( $zoom_users ) && ! empty( $user_zoom_hostid ) && empty( $email_address ) ) {
					foreach ( $zoom_users as $zoom_usr ) {
						$selected_host_id = $user_zoom_hostid === $zoom_usr->id ? 'selected="selected"' : false;
						$full_name        = ! empty( $zoom_usr->first_name ) ? $zoom_usr->first_name . ' ' . $zoom_usr->last_name : $zoom_usr->email;
						$host_id_field    .= '<option value="' . $zoom_usr->id . '" ' . $selected_host_id . '>' . $full_name . '</option>';
					}
				}

				$host_id_field .= '</select>';

				if ( ! empty( $email_address ) ) {
					$host_id_field .= '<input type="hidden" class="vczapi-host-email-field-' . $user->ID . '" name="zoom_host_email[' . $user->ID . ']" value="' . $email_address . '" />';
				}

				$tableData[] = [
					'id'      => $user->ID,
					'email'   => $user->user_email,
					'name'    => empty( $user->first_name ) ? $user->display_name : $user->first_name . ' ' . $user->last_name,
					'host_id' => $host_id_field,
				];
			}

			$results = [
				'draw'            => absint( $draw ),
				'recordsTotal'    => $users->get_total(),
				'recordsFiltered' => $users->get_total(),
				'data'            => $tableData,
			];

			wp_send_json( $results );

			wp_die();
		}
	}


}

new Zoom_Video_Conferencing_Admin_Ajax();
