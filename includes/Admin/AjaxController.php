<?php

namespace Codemanas\VczApi\Admin;

class AjaxController {

	public function __construct() {
		add_action( 'wp_ajax_postTypeFetchHosts', [ $this, 'fetchHosts' ] );
	}

	public function fetchHosts(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'video-conferencing-with-zoom-api' ) ], 403 );
		}

		$search_string = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_SPECIAL_CHARS );
		$results       = [];

		if ( ! empty( $search_string ) ) {
			$user = json_decode( zoom_conference()->getUserInfo( $search_string ) );
			if ( empty( $user->code ) && ! empty( $user ) ) {
				$results[] = [
					'id'   => $user->id,
					'text' => $user->email,
				];
			}
		} else {
			$users = json_decode( zoom_conference()->listUsers() );
			if ( empty( $users->code ) && ! empty( $users->users ) ) {
				foreach ( $users->users as $user ) {
					$results[] = [
						'id'   => $user->id,
						'text' => $user->email,
					];
				}
			}
		}

		wp_send_json( [ 'results' => $results ] );
	}

	private static ?AjaxController $instance = null;

	public static function get_instance(): ?AjaxController {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}