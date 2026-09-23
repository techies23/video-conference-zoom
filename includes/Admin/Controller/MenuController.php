<?php

namespace Codemanas\VczApi\Admin\Controller;

use Codemanas\VczApi\Helpers\Common;
use Codemanas\VczApi\Helpers\Templates;

/**
 * Handles registration of WordPress admin menus and submenus.
 */
class MenuController {

	/**
	 * @var callable View callback for rendering the settings page
	 */
	private $settingsCallback;

	/**
	 * @param callable $settingsCallback Callable function or method array to render the settings view
	 */
	public function __construct( callable $settingsCallback ) {
		$this->settingsCallback = $settingsCallback;
	}

	/**
	 * Registers all plugin submenus under the primary Zoom post type menu.
	 */
	public function registerAdminMenus(): void {
		$parent_slug = 'edit.php?post_type=zoom-meetings';

		$validated = Common::validateZoomCredentials();
		if ( $validated ) {
			$submenus = [];

			$submenus[] = [
				'page_title' => __( 'Users', 'video-conferencing-with-zoom-api' ),
				'menu_title' => __( 'Users', 'video-conferencing-with-zoom-api' ),
				'capability' => 'manage_options',
				'menu_slug'  => 'zoom-video-conferencing-list-users',
				'callback'   => [ UserController::get_instance(), 'list' ],
			];
			$submenus[] = [
				'page_title' => __( 'Recordings', 'video-conferencing-with-zoom-api' ),
				'menu_title' => __( 'Recordings', 'video-conferencing-with-zoom-api' ),
				'capability' => apply_filters( 'vczapi_admin_settings_capabilities', 'edit_published_posts' ),
				'menu_slug'  => 'zoom-video-conferencing-recordings',
				'callback'   => [ RecordingController::get_instance(), 'list' ],
			];
			$submenus[] = [
				'page_title' => __( 'Extensions', 'video-conferencing-with-zoom-api' ),
				'menu_title' => __( 'Extensions', 'video-conferencing-with-zoom-api' ),
				'capability' => 'manage_options',
				'menu_slug'  => 'zoom-video-conferencing-addons',
				'callback'   => [ $this, 'renderExtensionTemplate' ],
			];

			$submenus[] = [
				'page_title' => __( 'Import', 'video-conferencing-with-zoom-api' ),
				'menu_title' => __( 'Import', 'video-conferencing-with-zoom-api' ),
				'capability' => 'manage_options',
				'menu_slug'  => 'zoom-video-conferencing-sync',
				'callback'   => [ SyncController::get_instance(), 'list' ],
			];

			$this->registerSubmenuPages( $parent_slug, $submenus );
		}

		add_submenu_page(
			$parent_slug,
			__( 'Settings', 'video-conferencing-with-zoom-api' ),
			__( 'Settings', 'video-conferencing-with-zoom-api' ),
			'manage_options',
			'zoom-video-conferencing-settings',
			$this->settingsCallback
		);
	}

	public function renderExtensionTemplate(): void {
		Templates::includeFile( VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/extensions/index.php' );
	}

	/**
	 * Loops through array definition to attach submenus.
	 */
	private function registerSubmenuPages( string $parent_slug, array $submenus ): void {
		foreach ( $submenus as $menu ) {
			add_submenu_page(
				$parent_slug,
				$menu['page_title'],
				$menu['menu_title'],
				$menu['capability'],
				$menu['menu_slug'],
				$menu['callback']
			);
		}
	}
}