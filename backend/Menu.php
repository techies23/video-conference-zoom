<?php

namespace Codemanas\VczApi\Backend;

use Codemanas\VczApi\Backend\Events\Meetings;
use Codemanas\VczApi\Backend\Events\Recordings;
use Codemanas\VczApi\Backend\Settings\Ajax;
use Codemanas\VczApi\Backend\Settings\Settings;
use Codemanas\VczApi\Backend\Users\Users;

/**
 * Class Menu
 * @package Codemanas\VczApi\Backend
 */
class Menu {

	private static $_instance = null;

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

	public static $message = '';

	public function __construct() {
		$this->load_dependencies();
		add_action( 'admin_menu', array( $this, 'menu' ) );
	}

	public function load_dependencies() {
		//Ajax
		Ajax::instance();
	}

	/**
	 * Register Menus
	 *
	 * @since   1.0.0
	 * @updated 4.0.0
	 * @author  Deepen Bajracharya <dpen.connectify@gmail.com>
	 */
	public function menu() {
		$access_token = 'test';
		if ( ! empty( $access_token ) ) {
			if ( apply_filters( 'vczapi_show_live_meetings', false ) ) {
				add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Live Webinars', 'video-conferencing-with-zoom-api' ), __( 'Live Webinars', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-webinars', array(
					'Zoom_Video_Conferencing_Admin_Webinars',
					'list_webinars'
				) );

				add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Live Meetings', 'video-conferencing-with-zoom-api' ), __( 'Live Meetings', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing', array(
					Meetings::instance(),
					'list_meetings'
				) );

				add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Add Live Meeting', 'video-conferencing-with-zoom-api' ), __( 'Add Live Meeting', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-add-meeting', array(
					Meetings::instance(),
					'add_meeting'
				) );
			}

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Zoom Users', 'video-conferencing-with-zoom-api' ), __( 'Zoom Users', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-list-users', array(
				Users::instance(),
				'list_users'
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', 'Add Users', __( 'Add Users', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-add-users', array(
				Users::instance(),
				'add_zoom_users'
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Reports', 'video-conferencing-with-zoom-api' ), __( 'Reports', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-reports', array(
				'Zoom_Video_Conferencing_Reports',
				'zoom_reports'
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Recordings', 'video-conferencing-with-zoom-api' ), __( 'Recordings', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-recordings', array(
				Recordings::instance(),
				'zoom_recordings'
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Extensions', 'video-conferencing-with-zoom-api' ), __( 'Extensions', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-addons', array(
				'Zoom_Video_Conferencing_Admin_Addons',
				'render'
			) );

			//Only for developers or PRO version. So this is hidden !
			if ( defined( 'VIDEO_CONFERENCING_HOST_ASSIGN_PAGE' ) ) {
				add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Host to WP Users', 'video-conferencing-with-zoom-api' ), __( 'Host to WP Users', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-host-id-assign', array(
					Users::instance(),
					'assign_host_id'
				) );
			}

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Import', 'video-conferencing-with-zoom-api' ), __( 'Import', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-sync', array(
				'Zoom_Video_Conferencing_Admin_Sync',
				'render'
			) );
		}

		add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Settings', 'video-conferencing-with-zoom-api' ), __( 'Settings', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-settings', array(
			Settings::instance(),
			'render_settings'
		) );
	}
}
