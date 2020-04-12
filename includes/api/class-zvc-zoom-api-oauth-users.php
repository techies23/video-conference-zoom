<?php

/**
 * Class VCZAPIZoomUser
 */
class VCZAPIZoomUser extends VCZAPIZoomOauth {

	private static $instance = null;

	// this will be always fetched from the oauth request so that we know token is expired or not
	// if this is set, access token is either good or refreshed automatically
	// if this is not set, access token should be expired.
	public $live_id = null;

	/**
	 * Returns the instance of the class
	 *
	 * @return object $instance
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Construct Function
	 */
	public function __construct() {

		parent::__construct();

		$this->set_live_user();

		$this->init_logic();

		$this->check_access_token_expiry();
	}

	/**
	 * Sets wp current user to live user property
	 */
	public function set_live_user() {

		$this->live_user = wp_get_current_user();
	}

	public function init_logic() {

		$stored = $this->get_stored_zoom_user_info();

		if ( '' == $stored['vczapi_oauth_zoom_user_token_info'] ) {
			add_action( 'init', array( $this, 'grab_zoom_authorization_code' ) );
		}

		// check for any other params
		if ( isset( $_GET['revoke_access'] ) && $_GET['revoke_access'] == 'true' ) {

			$this->deauthorize();
			// also dont forget to remove the user token and user info from user meta
			$this->remove_stored_zoom_user_info();

			// finally redirect without the revoke access param
			wp_redirect( $this->site_redirect_url );
			exit;

		}

	}

	/**
	 * Grabs the code from the URL parameter
	 *
	 * @return void
	 */
	public function grab_zoom_authorization_code() {

		if ( isset( $_GET['code'] ) && $_GET['code'] != '' ) {

			// $this->authorization_code = sanitize_text_field( $_GET['code'] );
			$this->set( $_GET['code'] );
			$response = $this->make_remote_post_request();

			if ( $response['success'] ) {

				$current_user = wp_get_current_user();
				$this->store_zoom_user_info( $response['vczapi_oauth_zoom_user_info'] );
				$this->store_zoom_user_token_info( $response['vczapi_oauth_zoom_user_token_info'] );

			} else {

				echo $response['message'];
			}
		} else {
			// do nothing.
		}
	}


	/**
	 * Stores the zoom user info into user meta
	 *
	 * Have put the update in one function previously, but there was a problem
	 * i.e. sometimes only one of them is to be updated and passing both params was less ideal
	 * Therefore, have separated these two functions store_zoom_user_info | store_zoom_user_token_info
	 *
	 * @param array $zoom_user_info zoom user info to be stored.
	 *
	 * @return void
	 */
	public function store_zoom_user_info( $zoom_user_info ) {

		update_option( 'vczapi_oauth_zoom_user_info', $zoom_user_info ); // update zoom user info

	}

	/**
	 * Stores the zoom user token info into user meta
	 *
	 * @param int   $user_id              Current user ID.
	 * @param array $zoom_user_token_info Token info to be stored.
	 *
	 * @return void
	 */
	public function store_zoom_user_token_info( $zoom_user_token_info ) {

		update_option( 'vczapi_oauth_zoom_user_token_info', $zoom_user_token_info ); // update zoom user token info

	}

	/**
	 * Returns the zoom user's info stored in the user meta
	 *
	 * @return array $zoom_user_info
	 */
	public function get_stored_zoom_user_info() {
		$zoom_user_info       = get_option( 'vczapi_oauth_zoom_user_info' );
		$zoom_user_token_info = get_option( 'vczapi_oauth_zoom_user_token_info' );

		return array(
			'vczapi_oauth_zoom_user_info'       => $zoom_user_info,
			'vczapi_oauth_zoom_user_token_info' => $zoom_user_token_info,
		);
	}

	/**
	 * Removes the zoom user's info stored in the user meta
	 *
	 * @return void
	 */
	public function remove_stored_zoom_user_info() {

		delete_option( 'vczapi_oauth_zoom_user_info' );
		delete_option( 'vczapi_oauth_zoom_user_token_info' );
	}


	/**
	 * Checks the user's access token expiry state
	 */
	public function check_access_token_expiry() {

		$stored = $this->get_stored_zoom_user_info();

		if ( isset( $stored['vczapi_oauth_zoom_user_token_info'] ) && ! empty( $stored['vczapi_oauth_zoom_user_token_info'] ) ) {

			$zoom_user_infos = $this->get_zoom_user_info_with_access_token( $stored['vczapi_oauth_zoom_user_token_info']['token_type'], $stored['vczapi_oauth_zoom_user_token_info']['access_token'] );

			if ( ! $zoom_user_infos['success'] ) { // some kind of failure happened

				if ( $zoom_user_infos['error_code'] === 124 ) {
					//var_dump($stored['vczapi_oauth_zoom_user_token_info']['refresh_token']);
					// $access_token is expired, so get a new one
					$refreshed_access_tokens = $this->refresh_access_token( $stored['vczapi_oauth_zoom_user_token_info']['refresh_token'] );

					if ( isset( $refreshed_access_tokens['success'] )
					     && ! empty( $refreshed_access_tokens['success'] )
					) {

						// Immediately store the newly refreshed token info in the database else you will miss the window
						// If you miss one refresh then - old refresh token will be invalid and you will miss to save new refresh token
						// You will be in limbo
						// Also, convert to array from object before storing
						$refreshed_access_tokens_arr = json_decode( json_encode( $refreshed_access_tokens['vczapi_oauth_zoom_user_token_info'] ), true );

						$this->store_zoom_user_token_info( $refreshed_access_tokens_arr );

						$stored = $this->get_stored_zoom_user_info();

						// now to verify, again try to get the zoom user infos with this new $refreshed access_token
						$zoom_user_info = $this->get_zoom_user_info_with_access_token( $stored['vczapi_oauth_zoom_user_token_info']['token_type'], $stored['vczapi_oauth_zoom_user_token_info']['access_token'] );

						if ( $zoom_user_info['success'] ) {

							$zoom_user_info = json_decode( $zoom_user_infos['vczapi_oauth_zoom_user_info'] );
							$this->store_zoom_user_info( $zoom_user_info['vczapi_oauth_zoom_user_info'] );
						}
					}
				}
			} else { // direct success to get the zoom user info with stored access token

				$this->store_zoom_user_info( $zoom_user_infos['vczapi_oauth_zoom_user_info'] );
			}

		}
	}
}