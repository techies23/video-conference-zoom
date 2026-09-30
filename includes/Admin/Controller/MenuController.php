<?php

namespace Codemanas\VczApi\Admin\Controller;

use Codemanas\VczApi\Admin\Foundation\Events\CreateMeeting;
use Codemanas\VczApi\Helpers\Common;
use Codemanas\VczApi\Helpers\Config;
use Codemanas\VczApi\Helpers\Templates;

/**
 * Handles registration of WordPress admin menus and submenus.
 */
class MenuController {

	private static ?MenuController $instance = null;

	private string $postType;

	/**
	 * @var callable View callback for rendering the settings page
	 */
	private $settingsCallback;

	public static function get_instance( ?callable $settingsCallback = null ): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $settingsCallback );
		}

		return self::$instance;
	}

	/**
	 * @param callable $settingsCallback Callable function or method array to render the settings view
	 */
	public function __construct( callable $settingsCallback ) {
		$this->postType         = Config::get( 'post_type' );
		$this->settingsCallback = $settingsCallback;
		$this->registerHooks();
	}

	private function registerHooks(): void {
		add_action( 'admin_menu', [ $this, 'registerAdminMenus' ] );
	}

	/**
	 * Registers all plugin submenus under the primary Zoom post type menu.
	 */
	public function registerAdminMenus(): void {
		$parent_slug  = 'edit.php?post_type=' . $this->postType;
		$submenu_slug = 'post-new.php?post_type=' . $this->postType;

		remove_submenu_page( $parent_slug, $submenu_slug );

		$validated = Common::validateZoomCredentials();
		if ( $validated ) {
			$submenus = [];

			$submenus[] = [
				'page_title' => __( 'Add Event', 'video-conferencing-with-zoom-api' ),
				'menu_title' => __( 'Add Event', 'video-conferencing-with-zoom-api' ),
				'capability' => 'edit_posts',
				'menu_slug'  => CreateMeeting::MENU_SLUG,
				'callback'   => [ CreateMeeting::get_instance(), 'render' ],
				'position'   => 1,
			];

			$submenus[] = [
				'page_title' => __( 'Users', 'video-conferencing-with-zoom-api' ),
				'menu_title' => __( 'Users', 'video-conferencing-with-zoom-api' ),
				'capability' => 'manage_options',
				'menu_slug'  => 'zoom-video-conferencing-list-users',
				'callback'   => [ UserController::get_instance(), 'list' ],
				'position'   => 3,
			];
			$submenus[] = [
				'page_title' => __( 'Recordings', 'video-conferencing-with-zoom-api' ),
				'menu_title' => __( 'Recordings', 'video-conferencing-with-zoom-api' ),
				'capability' => apply_filters( 'vczapi_admin_settings_capabilities', 'edit_published_posts' ),
				'menu_slug'  => 'zoom-video-conferencing-recordings',
				'callback'   => [ RecordingController::get_instance(), 'list' ],
				'position'   => 4,
			];
			$submenus[] = [
				'page_title' => __( 'Extensions', 'video-conferencing-with-zoom-api' ),
				'menu_title' => __( 'Extensions', 'video-conferencing-with-zoom-api' ),
				'capability' => 'manage_options',
				'menu_slug'  => 'zoom-video-conferencing-addons',
				'callback'   => [ $this, 'renderExtensionTemplate' ],
				'position'   => 5,
			];

			$submenus[] = [
				'page_title' => __( 'Import', 'video-conferencing-with-zoom-api' ),
				'menu_title' => __( 'Import', 'video-conferencing-with-zoom-api' ),
				'capability' => 'manage_options',
				'menu_slug'  => 'zoom-video-conferencing-sync',
				'callback'   => [ SyncController::get_instance(), 'list' ],
				'position'   => 6,
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
				$menu['callback'],
				$menu['position']
			);
		}
	}
}