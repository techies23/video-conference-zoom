<?php

namespace Codemanas\VczApi\Admin;

use Codemanas\VczApi\Admin\Controller\PostTypeController;
use Codemanas\VczApi\Admin\Controller\SettingController;
use Codemanas\VczApi\Admin\Controller\UserController;
use Codemanas\VczApi\Helpers\Common;
use Codemanas\VczApi\Helpers\Templates;

/**
 * Admin Controller Class
 *
 * @added 4.7.0
 */
class AdminController {

	private static ?AdminController $instance = null;

	public static function get_instance(): ?AdminController {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->init();

		add_action( 'admin_notices', [ $this, 'showAdminNotices' ] );
	}

	public function init(): void {
		PostTypeController::get_instance();
		SettingController::get_instance();
		UserController::get_instance();
	}

	public function showAdminNotices(): void {
		$pg     = 'zoom-meetings_page_zoom';
		$screen = get_current_screen();

		if ( $screen->id === "zoom-meetings" || $screen->id === "$pg-video-conferencing-settings" || $screen->id === "$pg-video-conferencing-list-users" || $screen->id === "$pg-video-conferencing-addons" || $screen->id === "$pg-video-conferencing-reports" || $screen->id === "$pg-video-conferencing-recordings" ) {
			if ( Common::validateZoomCredentials() ) {
				return;
			}

			Templates::includeFile( VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/notices/incorrect-api-configuration.php' );
		}
	}
}