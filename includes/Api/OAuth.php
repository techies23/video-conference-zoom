<?php

namespace Codemanas\Vczapi\Api;

use \Zoom_Video_Conferencing_Api;
use \Firebase\JWT\JWT;

require_once( ZVC_PLUGIN_INCLUDES_PATH . '/helpers.php' );

/**
 * Class OAuth
 *
 * Handle OAuth operations for Zoom API.
 *
 * @since   3.9.0
 * @package Codemanas\Zoom\Core
 */
class OAuth extends Zoom_Video_Conferencing_Api {

	/**
	 * @var null
	 */
	public static $_instance = null;

	/**
	 * Auth Header Placeholder
	 *
	 * @var string
	 */
	private $authorization_header = '';

	/**
	 * Client ID
	 *
	 * @var string
	 */
	private $client_id = 'LhF1UQO0SKuuBFUH69iyrw';

	/**
	 * Client secret ID
	 *
	 * @var string
	 */
	private $secret_id = 'fwlJ987YTaY5MICPNIXSCaAexPGrq1DW';

	//redirect uri required to generate request access token url
	private $redirect_uri = 'https://oauth.codemanas.com/zprocess/';

	// authorization is 2nd part of OAuth process
	private $authorize_uri = 'https://zoom.us/oauth/authorize';
	private $zoom_verify_listener = '';
	private $access_token_url = 'https://zoom.us/oauth/token';

	// revoke details
	private $revoke_uri = 'https://zoom.us/oauth/revoke';

	//user details
	private $current_user_id = null;
	private $user_oauth_data = null;
	private $connected_user_info = null;
	

	/**
	 * Get Instance
	 *
	 * @return OAuth|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * OAuth constructor.
	 */
	public function __construct() {
		$this->set_required_fields();
		parent::__construct();
		add_action( 'admin_init', [ $this, 'request_or_remove_access_token' ] );

		//Check if user is using OAuth
		if ( ! empty( $this->user_oauth_data ) ) {
			add_filter( 'vczapi_users_list', [ $this, 'set_zoom_user' ] );
			add_filter( 'vczapi_check_oauth_response', [ $this, 'check_refresh_token_and_resend_request' ], 10, 4 );
		}

		add_filter( 'vczapi_core_api_request_headers', [ $this, 'change_request_headers' ] );
		add_action( 'wp_ajax_vczapi_verify_jwt_keys', [ $this, 'verify_jwt_keys' ] );
		add_action( 'pre_get_posts', [ $this, 'show_only_own_zoom_meetings' ] );
	}

	/**
	 * Set Required Variables for OAuth to work properly
	 *
	 * @since 3.9.0
	 */
	public function set_required_fields() {
		$this->zoom_verify_listener = admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' );
		$this->authorization_header = 'Basic ' . base64_encode( $this->client_id . ':' . $this->secret_id );
		$this->current_user_id      = get_current_user_id();

		$this->user_oauth_data     = ( ! vczapi_is_oauth_used_globally() ) ? get_user_meta( $this->current_user_id, 'vczapi_zoom_oauth', true ) : get_option( 'vczapi_global_zoom_oauth' );
		$this->connected_user_info = ( ! vczapi_is_oauth_used_globally() ) ? get_user_meta( $this->current_user_id, 'vczapi_connected_user_info', true ) : get_option( 'vczapi_global_connected_user_info' );
	}

	/**
	 * Saves users oauth data to system
	 *
	 * @param $response_body
	 *
	 * @since 3.9.0
	 */
	private function save_oauth_credentials( $response_body ) {
		if ( ! isset( $response_body->error ) ) {
			//update user oauth data first or else getting connected user info will be wrong

			$this->user_oauth_data      = $response_body;
			$vczpai_connected_user_info = json_decode( $this->getMyInfo() );

			update_user_meta( $this->current_user_id, 'vczapi_zoom_oauth', $response_body );
			update_user_meta( $this->current_user_id, 'vczapi_connected_user_info', $vczpai_connected_user_info );
			$this->connected_user_info = $vczpai_connected_user_info;

			if ( vczapi_is_oauth_used_globally() ) {
				update_option( 'vczapi_global_zoom_oauth', $response_body );
				update_option( 'vczapi_global_connected_user_info', $vczpai_connected_user_info );
			}
		}
	}

	/**
	 * Removes users oauth data from system
	 *
	 * @since 3.9.0
	 */
	private function remove_oauth_credentials() {
		delete_user_meta( $this->current_user_id, 'vczapi_zoom_oauth' );
		delete_user_meta( $this->current_user_id, 'vczapi_connected_user_info' );

		if ( \vczapi_is_oauth_used_globally() ) {
			delete_option( 'vczapi_global_zoom_oauth' );
			delete_option( 'vczapi_global_connected_user_info' );
		}
	}

	/**
	 * @param \WP_Query $wp_query
	 *
	 * @since 3.9.0
	 */
	public function show_only_own_zoom_meetings( \WP_Query $wp_query ) {
		if ( vczapi_is_oauth_used_globally() ) {
			return;
		}

		if ( $wp_query->is_main_query() && is_admin() && $wp_query->get( 'post_type' ) == 'zoom-meetings' ) {
			$screen = get_current_screen();
			if ( $screen->id == 'edit-zoom-meetings' ) {
				$wp_query->set( 'author', $this->current_user_id );
				add_filter( 'views_edit-zoom-meetings', '__return_false' );
			}
		}

	}

	/**
	 * Purpose of this is to allow 3rd Party extensions to get OAuth Data and use it to create meetings
	 *
	 * @param false $user_id
	 *
	 * @return false|mixed|void
	 * @since 3.9.0
	 *
	 */
	public function get_oauth_data( $user_id = false ) {
		$oauth_data = false;
		if ( $user_id != false ) {
			$user_id = (int) $user_id;
			$user    = \get_user_by( 'id', $user_id );
			if ( is_object( $user ) ) {
				$oauth_data = get_user_meta( $user_id, 'vczapi_zoom_oauth', true );
			}
		} elseif ( \vczapi_is_oauth_used_globally() ) {
			$oauth_data = get_option( 'vczapi_global_zoom_oauth' );
		} else {
			$oauth_data = get_user_meta( $this->current_user_id, 'vczapi_zoom_oauth', true );
		}

		return $oauth_data;
	}

	/**
	 * Get authorized user information
	 *
	 * @return array|bool|string|\WP_Error
	 * @since 3.9.0
	 *
	 */
	public function getMyInfo() {
		return $this->sendRequest( 'users/me', [] );
	}

	/**
	 * Generate connect to Zoom html
	 *
	 * @since 3.9.0
	 */
	public function maybe_connected_to_zoom_html() {
		include_once ZVC_PLUGIN_VIEWS_PATH . '/api/connect-oauth.php';
	}

	/**
	 * Generate authentication URL
	 *
	 * @return string return Authentication URL
	 * @since 3.9.0
	 *
	 */
	public function get_request_user_authentication_url() {
		return add_query_arg( [
			'response_type' => 'code',
			'client_id'     => $this->client_id,
			'redirect_uri'  => $this->redirect_uri . '?state=' . $this->zoom_verify_listener,
		], $this->authorize_uri );
	}

	/**
	 * Handler to check if you should revoke or request new access token
	 *
	 * @since 3.9.0
	 */
	public function request_or_remove_access_token() {
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
	 * Request Access Token according to code returned
	 *
	 * @since 3.9.0
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
				'redirect_uri' => $this->redirect_uri . '?state=' . esc_url( $this->zoom_verify_listener ),
			],
			$this->access_token_url
		);

		$header_args = [
			'headers' => [
				'Authorization' => $this->authorization_header,
			],
		];

		$post_response = wp_remote_post( $request_access_token_url, $header_args );
		$response_body = json_decode( wp_remote_retrieve_body( $post_response ) );

		if ( ! isset( $response_body->error ) ) {
			//update user oauth data first or else getting connected user info will be wrong
			$this->save_oauth_credentials( $response_body );

			wp_redirect( $this->zoom_verify_listener );
			exit;
		}
	}

	/**
	 * Revoke Access Token when requested.
	 *
	 * @since 3.9.0
	 */
	public function revokeAccessToken() {

		$revoke = filter_input( INPUT_GET, 'revoke_access_token' );

		if ( empty( $revoke ) ) {
			return;
		}

		$revoke_query = add_query_arg(
			[
				'token' => $this->user_oauth_data->access_token,
			],
			$this->revoke_uri
		);

		$header_args = [
			'headers' => [
				'Authorization' => $this->authorization_header,
			],
		];

		$response      = wp_remote_post( $revoke_query, $header_args );
		$response_body = wp_remote_retrieve_body( $response );

		$this->remove_oauth_credentials();

		wp_redirect( $this->zoom_verify_listener );
		exit;

	}

	/**
	 * Change request header to math OAuth request
	 *
	 * @param $headers
	 *
	 * @return mixed
	 * @since 3.9.0
	 *
	 */
	public function change_request_headers( $headers ) {
		if ( ! empty( $this->user_oauth_data ) ) {
			$headers['Authorization'] = 'Bearer ' . $this->user_oauth_data->access_token;
		}

		return $headers;
	}

	/**
	 * @param $users
	 *
	 * @return mixed|null[]
	 */
	public function set_zoom_user( $users ) {
		if ( ! vczapi_is_oauth_used_globally() && ! empty( $this->connected_user_info ) ) {
			$users = [ $this->connected_user_info ];
		} elseif ( ! vczapi_is_oauth_used_globally() && empty( $this->connected_user_info ) ) {
			$users = null;
		}

		return $users;
	}

	/**
	 * Uses refresh token if required and resends / re-dos request
	 *
	 * @param $response
	 * @param $calledFunction
	 * @param $data
	 * @param $request
	 *
	 * @return array|bool|mixed|string|\WP_Error
	 * @since 3.9.0
	 *
	 */
	public function check_refresh_token_and_resend_request( $response, $calledFunction, $data, $request ) {
		//** remove filter so we don't end up in a loop */
		remove_filter( 'vczapi_check_oauth_response', [ $this, 'refresh_token_and_resend_request' ], 10 );
		$response_check = json_decode( $response );
		if ( isset( $response_check->code ) && $response_check->code == '124' ) {
			$response_body = $this->regenerate_access_token();
			if ( isset( $response_body->access_token ) ) {
				$response = $this->sendRequest( $calledFunction, $data, $request );
			}
		}

		return $response;
	}

	/**
	 * Regenerate Access Token
	 *
	 * @return mixed|null
	 * @since 3.9.0
	 *
	 */
	public function regenerate_access_token() {
		if ( isset( $this->user_oauth_data->refresh_token ) && ! empty( $this->user_oauth_data->refresh_token ) ) {
			$regen_access_token_url = add_query_arg(
				[
					'grant_type'    => 'refresh_token',
					'refresh_token' => $this->user_oauth_data->refresh_token,
				],
				$this->access_token_url
			);

			$header_args = [
				'headers' => [
					'Authorization' => $this->authorization_header,
				],
			];

			$response      = wp_remote_post( $regen_access_token_url, $header_args );
			$response_body = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! isset( $response_body->error ) ) {
				$this->save_oauth_credentials( $response_body );
			}

			return $response_body;
		}

		return null;
	}

	/**
	 * @param null $current_user_id
	 */
	public function set_current_user_id( $current_user_id ) {
		$this->current_user_id = $current_user_id;
	}

	/**
	 * @param null $user_oauth_data
	 */
	public function set_user_oauth_data( $user_oauth_data ) {
		$this->user_oauth_data = $user_oauth_data;
	}
}

OAuth::get_instance();