<?php

namespace Codemanas\VczApi\Admin\Controller;

use Codemanas\VczApi\Admin\Service\UserSyncService;

class AjaxController {

	private static ?AjaxController $instance = null;

	public static function get_instance(): ?AjaxController {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_vczapi_sync_zoom_users', [ UserSyncService::class, 'syncUsers' ] );
		add_action( 'wp_ajax_vczapi_get_user_by_query', [ UserController::get_instance(), 'getUsersByQuery' ] );
		add_action( 'wp_ajax_vczapi_list_recordings', [ RecordingController::get_instance(), 'getRecordings' ] );
	}
}