<?php

/**
 * Class VCZAPIZoomOauth
 *
 * No need to create an instance or object of this class
 * It will only be extended in other classes
 */
class VCZAPIZoomOauth {
	private static $instance = null;

	//	Codemanas Development Credentials
	private $client_id = 'LhF1UQO0SKuuBFUH69iyrw';
	private $secret_id = 'fwlJ987YTaY5MICPNIXSCaAexPGrq1DW';

	private $zoom_request_user_authorization_url = '';
	private $zoom_make_post_request_url = '';
	private $authorization_code = '';

	public $zoom_authorize_url = 'https://zoom.us/oauth/authorize';
	public $zoom_token_url = 'https://zoom.us/oauth/token';
	public $zoom_user_info_url = 'https://api.zoom.us/v2/users/me';
	public $zoom_revoke_token_url = 'https://zoom.us/oauth/revoke';

	public $site_url = null;
	public $site_redirect_url = null;
	private $oauth_redirect_url = null;
	public $redirect_state_url = null;
	public $revoke_url = null;

	private $oauth_ret = array();

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
		$this->set();
	}

	/**
	 * Sets the value to properties
	 *
	 * @return void
	 */
	public function set( $authorization_code = '' ) {

		$this->authorization_code = $authorization_code;
		$this->site_url           = esc_url( trailingslashit( home_url( '' ) ) );

		$this->site_redirect_url = admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' );

		$this->oauth_redirect_url = 'https://oauth.codemanas.com/zprocess/';

		$this->redirect_state_url = $this->oauth_redirect_url . '?state=' . $this->site_redirect_url;

		$this->zoom_request_user_authorization_url = add_query_arg(
			array(
				'response_type' => 'code',
				'client_id'     => $this->client_id,
				'redirect_uri'  => $this->redirect_state_url,
			),
			$this->zoom_authorize_url
		);

	}

	/**
	 * Returns the Zoom button link
	 *
	 * @return string $this->zoom_request_user_authorization_url
	 */
	public function get_zoom_request_user_authorization_url() {
		return $this->zoom_request_user_authorization_url;
	}

	/**
	 * Makes remote post request to get the Access tokens
	 *
	 * @return array $oauth_ret
	 */
	public function make_remote_post_request() {

		// https://zoom.us/oauth/token?grant_type=authorization_code&code=obBEe8ewaL_KdYKjnimT4KPd8KKdQt9FQ&redirect_url=https://yourapp.com.

		$this->zoom_make_post_request_url = add_query_arg(
			array(
				'grant_type'   => 'authorization_code',
				'code'         => $this->authorization_code,
				'redirect_uri' => $this->redirect_state_url,
			),
			$this->zoom_token_url
		);

		$header_args = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->secret_id ),
			),
		);

		$token_info_response = wp_remote_post( $this->zoom_make_post_request_url, $header_args );

		if ( is_wp_error( $token_info_response ) ) {

			$this->oauth_ret['success'] = false;
			$this->oauth_ret['message'] = 'Something went wrong: ' . $token_info_response->get_error_message();

			return $this->oauth_ret;

		} elseif ( $token_info_response['response']['code'] == 200 ) {

			// We now got the access token, start
			// Note: This access token can now be used to make requests to the Zoom API. Access tokens expire after 1 hour.
			$token_info_arr = json_decode( $token_info_response['body'], true );

			$zoom_user_info_response = $this->get_zoom_user_info_with_access_token( $token_info_arr['token_type'], $token_info_arr['access_token'] );

			$zoom_user_info_arr = $zoom_user_info_response['vczapi_oauth_zoom_user_info'];

			$this->oauth_ret['success']                           = true;
			$this->oauth_ret['vczapi_oauth_zoom_user_info']       = $zoom_user_info_arr;
			$this->oauth_ret['vczapi_oauth_zoom_user_token_info'] = $token_info_arr;

			return $this->oauth_ret;

		} else {

			// some issues with the authorization
			echo 'Response: <pre>';
			print_r( $token_info_response );
			echo '</pre>';
			echo '<p>Response ' . $token_info_response['response']['code'] . ': ' . $token_info_response['response']['message'] . '</p>';
		}
	}

	/**
	 * Retrieves the zoom user's info by making a GET request
	 * Uses $access_token to get the info
	 * Requests new $access_token if expired using refresh token
	 * Finally returns the user info with new $access_token
	 * And also, returns new token infos to store in user meta
	 *
	 * @param string $token_type
	 * @param string $access_token
	 * @param string $refresh_token
	 *
	 * @return array $oauth_ret
	 */
	public function get_zoom_user_info_with_access_token( $token_type, $access_token ) {

		// get the access token and get user info.
		$header_args                = array(
			'headers' => array(
				'Authorization' => $token_type . ' ' . $access_token,
			),
		);
		$response_with_access_token = wp_remote_get( $this->zoom_user_info_url, $header_args );

		$body = $response_with_access_token['body']; // body contains the info we need for now

		$body = json_decode( $body, true ); // converts the string content into an associative array

		if ( is_wp_error( $response_with_access_token ) ) {

			$this->oauth_ret['success'] = false;
			$this->oauth_ret['message'] = 'Something went wrong: ' . $response_with_access_token->get_error_message();

		} elseif ( $response_with_access_token['response']['code'] != 200 ) {

			$error_code = json_decode( $response_with_access_token['body'] );

			$this->oauth_ret['success']    = false;
			$this->oauth_ret['error_code'] = $error_code->code;
			$this->oauth_ret['message']    = $error_code->message;

			return $this->oauth_ret;

		} else {

			// everything good
			// new access token is acquired
			$this->oauth_ret['success']                     = true;
			$this->oauth_ret['vczapi_oauth_zoom_user_info'] = $body;

			return $this->oauth_ret;
		}
	}

	/**
	 * Uses the refresh token and creates new access token
	 *
	 * @param string $refresh_token
	 *
	 * @return array|null $oauth_return
	 */
	public function refresh_access_token( $refresh_token ) {

		$header_args = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->secret_id ),
			),
		);

		$refresh_action_post_request = wp_remote_post(
			add_query_arg(
				array(
					'grant_type'    => 'refresh_token',
					'refresh_token' => $refresh_token,
				),
				$this->zoom_token_url
			),
			$header_args
		);

		if ( $refresh_action_post_request['response']['code'] == 200 ) {

			$refreshed_tokens = json_decode( $refresh_action_post_request['body'] );

			$this->oauth_ret['success']                           = true;
			$this->oauth_ret['vczapi_oauth_zoom_user_token_info'] = $refreshed_tokens;

			return $this->oauth_ret;
		} else {
			return false;
		}
	}

	public function save_refreshed_access_token() {
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

	/**
	 * Revokes a users access token
	 *
	 * @return void
	 */
	public function deauthorize() {

		// get access token of current live user
		$tokens = get_option( 'vczapi_oauth_zoom_user_token_info' );

		if ( $tokens != "" ) {
			$header_args = array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->secret_id ),
				),
			);

			$revoke_action_post_request = wp_remote_post(
				add_query_arg(
					array(
						'token' => $tokens['access_token'],
					),
					$this->zoom_revoke_token_url
				),
				$header_args
			);
		}
	}
}

