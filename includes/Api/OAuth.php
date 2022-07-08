<?php

namespace Codemanas\VczApi\Includes\Api;

use Codemanas\VczApi\Includes\Fields;

/**
 * Class OAuth
 *
 * @since 4.0.0
 * @package Codemanas\VczApi\Includes\Api
 */
class OAuth extends ZoomClient {

	//redirect uri required to generate request access token url
	const REDIRECT_URI = 'https://oauth.codemanas.com/zprocess/';

	// authorization is 2nd part of OAuth process
	const AUTHORIZE_URI = 'https://zoom.us/oauth/authorize';
	const ACCESS_TOKEN_URI = 'https://zoom.us/oauth/token';

	// revoke details
	const REVOKE_URI = 'https://zoom.us/oauth/revoke';

	/**
	 * Auth Header Placeholder
	 *
	 * @var string
	 */
	private $_authorization_header = '';

	/**
	 * Client ID
	 *
	 * @var string
	 */
	private $_client_id = 'LhF1UQO0SKuuBFUH69iyrw';

	/**
	 * Client secret ID
	 *
	 * @var string
	 */
	private $_secret_id = 'fwlJ987YTaY5MICPNIXSCaAexPGrq1DW';

	//redirect uri required to generate request access token url
	public $zoom_verify_listener = '';

	//user details
	public $userData = null;
	private $_current_user_id = null;
	private $_connected_user_info = null;


	/**
	 * OAuth constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		parent::__construct();
		$this->init();
	}

	/**
	 * Initialization
	 *
	 * @since 4.0.0
	 */
	public function init() {
		//Set required class parameters
		$this->_setParams();

		add_action( 'admin_init', [ $this, 'requestOrRevokeAccessToken' ] );

		//Check if user is using OAuth
		if ( ! empty( $this->userData ) ) {
//			add_filter( 'vczapi_users_list', [ $this, 'set_zoom_user' ] );
			add_filter( 'vczapi_checkApiResponse', [ $this, 'validateAccessTokenExpiry' ], 10, 4 );
		}

		add_filter( 'vczapi_core_api_request_headers', [ $this, 'modifyRequestHeaders' ] );
	}

	/**
	 * Set Parameters for the class
	 *
	 * @since 4.0.0
	 */
	protected function _setParams() {
		$this->_authorization_header = 'Basic ' . base64_encode( $this->_client_id . ':' . $this->_secret_id );
		$this->zoom_verify_listener  = admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' );
		$this->_current_user_id      = get_current_user_id();

		$this->userData             = Fields::get_user_meta( $this->_current_user_id, 'connected_access_token' );
		$this->_connected_user_info = Fields::get_user_meta( $this->_current_user_id, 'connected_user_info' );
	}

	public function modifyRequestHeaders( $headers ) {
		if ( ! empty( $this->userData ) ) {
			$headers['Authorization'] = 'Bearer ' . $this->userData->access_token;
		}

		return $headers;
	}

	/**
	 * Renew token if expired
	 *
	 * @param $response
	 * @param $calledFunction
	 * @param $data
	 * @param $request
	 *
	 * @return array|bool|WP_Error|string
	 * @since 4.0.0
	 *
	 */
	public function validateAccessTokenExpiry( $response, $calledFunction, $data, $request ) {
		//** remove filter so we don't end up in a loop */
		remove_filter( 'vczapi_checkApiResponse', [ $this, 'validateAccessTokenExpiry' ], 10 );
		if ( isset( $response->code ) && $response->code == '124' ) {
			$response_body = $this->regenerateAccessToken();
			if ( ! empty( $response_body ) && ! empty( $response_body->access_token ) ) {
				$response = $this->sendRequest( $calledFunction, $data, $request );
			}
		}

		return $response;
	}

	/**
	 * Regenerate access token
	 * @return bool|mixed
	 * @since 4.0.0
	 */
	public function regenerateAccessToken() {
		if ( ! empty( $this->userData ) && ! empty( $this->userData->refresh_token ) ) {
			$regen_access_token_url = add_query_arg(
				[
					'grant_type'    => 'refresh_token',
					'refresh_token' => $this->userData->refresh_token
				],
				self::ACCESS_TOKEN_URI
			);

			$header_args = [
				'headers' => [
					'Authorization' => $this->_authorization_header,
				],
			];

			$response      = wp_remote_post( $regen_access_token_url, $header_args );
			$response_body = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! isset( $response_body->error ) ) {
				$this->_saveAccessToken( $response_body );
			}

			return $response_body;
		}

		return false;
	}

	/**
	 * Request or revoke access of the token
	 * @since 4.0.0
	 */
	public function requestOrRevokeAccessToken() {
		global $pagenow;

		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		if ( $pagenow == 'edit.php' && filter_input( INPUT_GET, 'post_type' ) == 'zoom-meetings' && filter_input( INPUT_GET, 'page' ) == 'zoom-video-conferencing-settings' ) {
			$this->requestAccessToken();
			$this->revokeAccessToken();
		}
	}

	/**
	 * Provides Access token upon request
	 *
	 * @since 4.0.0
	 */
	public function requestAccessToken() {
		$code = filter_input( INPUT_GET, 'code' );
		if ( empty( $code ) ) {
			return;
		}

		$request_access_token_url = add_query_arg(
			[
				'grant_type'   => 'authorization_code',
				'code'         => $code,
				'redirect_uri' => self::REDIRECT_URI . '?state=' . esc_url( $this->zoom_verify_listener ),
			],
			self::ACCESS_TOKEN_URI
		);

		$header_args = [
			'headers' => [
				'Authorization' => $this->_authorization_header,
			],
		];

		$post_response = wp_remote_post( $request_access_token_url, $header_args );
		$response_body = json_decode( wp_remote_retrieve_body( $post_response ) );

		if ( $response_body->error ) {
			//log error in logger
		} else {
			$this->_saveAccessToken( $response_body );

			wp_redirect( $this->zoom_verify_listener );
		}
	}

	/**
	 * Saves the access token to user meta or options table
	 *
	 * @param $response_body
	 *
	 * @since 4.0.0
	 */
	private function _saveAccessToken( $response_body ) {
		if ( ! empty( $response_body ) ) {
			//update user oauth data first or else getting connected user info will be wrong
			$this->userData             = $response_body;
			$this->_connected_user_info = $this->getMyInfo();

			Fields::set_option( 'using_oauth', 1 );
			Fields::set_user_meta( $this->_current_user_id, 'connected_access_token', $response_body );
			Fields::set_user_meta( $this->_current_user_id, 'connected_user_info', $this->_connected_user_info );
		}
	}

	public function revokeAccessToken() {
		$revoke = filter_input( INPUT_GET, 'revoke_access_token' );

		if ( empty( $revoke ) ) {
			return;
		}

		$revoke_query = add_query_arg(
			[
				'token' => $this->userData->access_token
			],
			self::REVOKE_URI
		);

		$header_args = [
			'headers' => [
				'Authorization' => $this->_authorization_header,
			],
		];

		$response      = wp_remote_post( $revoke_query, $header_args );
		$response_body = wp_remote_retrieve_body( $response );

		$this->_removeOauthCredentials();

		wp_redirect( $this->zoom_verify_listener );
		exit;
	}

	private function _removeOauthCredentials() {
		Fields::set_user_meta( $this->_current_user_id, 'connected_access_token', '' );
		Fields::set_user_meta( $this->_current_user_id, 'connected_user_info', '' );
	}

	/**
	 * Returns current user info
	 *
	 * @return array|bool|WP_Error|string
	 * @since 4.0.0
	 */
	public function getMyInfo() {
		return $this->sendRequest( 'users/me', [] );
	}

	/**
	 * Returns authentication url
	 *
	 * @return string
	 * @since 4.0.0
	 */
	public function getUserAuthenticationUrl() {
		return add_query_arg( [
			'response_type' => 'code',
			'client_id'     => $this->_client_id,
			'redirect_uri'  => self::REDIRECT_URI . '?state=' . $this->zoom_verify_listener
		], self::AUTHORIZE_URI );
	}

	/**
	 * @var
	 */
	protected static $_instance;

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