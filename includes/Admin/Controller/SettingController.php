<?php

namespace Codemanas\VczApi\Admin\Controller;

use Codemanas\VczApi\Admin\Foundation\Notification;
use Codemanas\VczApi\Admin\Foundation\Settings\ConnectHandler;
use Codemanas\VczApi\Admin\Foundation\Settings\GeneralSettingsHandler;
use Codemanas\VczApi\Admin\Foundation\Settings\LogHandler;
use Codemanas\VczApi\Admin\Foundation\Settings\SettingsPageView;
use Codemanas\VczApi\Admin\Repository\SettingsRepository;

/**
 * Register main settings page.
 */
class SettingController {

	private static ?SettingController $instance = null;

	private SettingsRepository $settingsRepo;
	private SettingsPageView $view;

	public static function get_instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->settingsRepo = new SettingsRepository();
		$this->view         = new SettingsPageView( $this->settingsRepo );

		$this->registerHooks();
	}

	private function registerHooks(): void {
		$connectHandler  = new ConnectHandler();
		$settingsHandler = new GeneralSettingsHandler( $this->settingsRepo );
		$logHandler      = new LogHandler();
		$adminMenu       = new MenuController( [ $this->view, 'render' ] );

		add_action( 'admin_menu', [ $adminMenu, 'registerAdminMenus' ] );
//		add_action( 'admin_init', [ $connectHandler, 'handle' ] );
		add_action( 'admin_init', [ $settingsHandler, 'handle' ] );
		add_action( 'admin_init', [ $logHandler, 'handle' ] );
		add_action( 'admin_notices', [ Notification::get_instance(), 'displayNotices' ] );

		//Ajax Call
		add_action( 'wp_ajax_vczapi_connect_credentials', [ $connectHandler, 'ajaxHandler' ] );
	}

	/**
	 * Backward compatibility wrapper for getSettings
	 */
	public function getSettings(): array {
		return $this->settingsRepo->getSettings();
	}
}