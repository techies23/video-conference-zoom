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
		add_action( 'wp_ajax_vczapiSyncZoomUsers', [ UserSyncService::class, 'syncUsers' ] );
	}
}