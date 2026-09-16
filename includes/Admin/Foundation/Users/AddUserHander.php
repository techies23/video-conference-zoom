<?php

namespace Codemanas\VczApi\Admin\Foundation\Users;

use Codemanas\VczApi\Admin\Controller\NoticeController;
use Codemanas\VczApi\Admin\Service\UsersService;

class AddUserHander {

	private UsersService $usersService;

	public function __construct( UsersService $usersService ) {
		$this->usersService = $usersService;
	}

	public function handle(): void {
		if ( ! isset( $_POST['vczapi_add_user_nonce'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( sanitize_key( $_POST['vczapi_add_user_nonce'] ), 'vczapi_add_user_nonce_action' ) ) {
			return;
		}

		if ( ! isset( $_POST['add_zoom_user'] ) ) {
			return;
		}

		$postData = array(
			'action'     => filter_input( INPUT_POST, 'action' ),
			'email'      => sanitize_email( filter_input( INPUT_POST, 'email' ) ),
			'first_name' => sanitize_text_field( filter_input( INPUT_POST, 'first_name' ) ),
			'last_name'  => sanitize_text_field( filter_input( INPUT_POST, 'last_name' ) ),
			'type'       => filter_input( INPUT_POST, 'type' ),
			'user_id'    => filter_input( INPUT_POST, 'user_id' )
		);

		$result = $this->usersService->create( $postData );
		if ( is_wp_error( $result ) ) {
			NoticeController::setNotice( $result->get_error_message() );
		} else {
			NoticeController::setNotice( __( "Created a User. Please check email for confirmation. Added user will only appear in the list after approval.", "video-conferencing-with-zoom-api" ), "success" );

//				if ( ! empty( $result['id'] ) ) {
//					update_user_meta( $postData['user_id'], 'user_zoom_hostid', $result['id'] );
//				}
		}
	}
}