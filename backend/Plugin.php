<?php

namespace Codemanas\VczApi\Backend;

/**
 * Class App
 * Since 4.0.0
 * @package Codemanas\VczApi\Backend
 */
class Plugin {

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

	/**
	 * Constructor method for loading the components
	 *
	 * @since  2.0.0
	 * @author Deepen
	 */
	public function __construct() {
		$this->load_dependencies();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_backend' ) );
		add_filter( 'plugin_action_links', array( $this, 'action_link' ), 10, 2 );
	}

	/**
	 * Load the other class dependencies
	 *
	 * @since    2.0.0
	 * @modified 2.1.0
	 * @author   Deepen Bajracharya
	 */
	public function load_dependencies() {
		if ( is_admin() ) {
			//Root
			Common::instance();
			Menu::instance();
		}
	}

	/**
	 * Enqueuing Scripts and Styles for Admin
	 *
	 * @param  $hook
	 *
	 * @since    2.0.0
	 * @modified 2.1.0
	 * @author   Deepen Bajracharya
	 */
	public function enqueue_scripts_backend( $hook ) {
		$pg = 'zoom-meetings_page_zoom-';

		$screen = get_current_screen();

		//Vendors
		if ( $hook === $pg . "video-conferencing-addons" || $hook === $pg . "video-conferencing-reports" || $hook === $pg . "video-conferencing-recordings" || $hook === $pg . "video-conferencing-list-users" || $hook === $pg . "video-conferencing" || $hook === $pg . "video-conferencing-add-meeting" || $hook === $pg . "video-conferencing-webinars" || $hook === $pg . "video-conferencing-webinars-add" || $screen->id === "zoom-meetings" || $hook === $pg . "video-conferencing-host-id-assign" || $hook === $pg . "video-conferencing-sync" || $hook === $pg . "video-conferencing-add-users" ) {
			wp_enqueue_style( 'video-conferencing-with-zoom-api-timepicker', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/dtimepicker/jquery.datetimepicker.min.css', false, VCZAPI_VERSION );
			wp_enqueue_style( 'video-conferencing-with-zoom-api-select2', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/select2/css/select2.min.css', false, VCZAPI_VERSION );
			wp_enqueue_style( 'video-conferencing-with-zoom-api-datable', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable/jquery.dataTables.min.css', false, VCZAPI_VERSION );
		}

		wp_register_script( 'video-conferencing-with-zoom-api-select2-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/select2/js/select2.min.js', array( 'jquery' ), VCZAPI_VERSION, true );
		wp_register_script( 'video-conferencing-with-zoom-api-timepicker-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/dtimepicker/jquery.datetimepicker.full.js', array( 'jquery' ), VCZAPI_VERSION, true );
		wp_register_script( 'video-conferencing-with-zoom-api-datable-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable/jquery.dataTables.min.js', array( 'jquery' ), VCZAPI_VERSION, true );

		if ( $hook === $pg . "video-conferencing-reports" || $hook === $pg . "video-conferencing-recordings" ) {
			wp_enqueue_style( 'jquery-ui-datepicker-vczapi', ZVC_PLUGIN_ADMIN_ASSETS_URL . '/css/jquery-ui.css', false, VCZAPI_VERSION );
		}

		//Plugin Scripts
		wp_enqueue_style( 'video-conferencing-with-zoom-api', ZVC_PLUGIN_ADMIN_ASSETS_URL . '/css/style.min.css', false, VCZAPI_VERSION );
		wp_register_script( 'video-conferencing-with-zoom-api-js', ZVC_PLUGIN_ADMIN_ASSETS_URL . '/js/script.min.js', array(
			'jquery',
			'video-conferencing-with-zoom-api-select2-js',
			'video-conferencing-with-zoom-api-timepicker-js',
			'video-conferencing-with-zoom-api-datable-js',
			'underscore'
		), VCZAPI_VERSION, true );

		wp_localize_script( 'video-conferencing-with-zoom-api-js', 'vczapi_ajax', array(
			'zvc_security' => wp_create_nonce( "_nonce_zvc_security" ),
			'lang'         => array(
				'confirm_end'    => __( "Are you sure you want to end this meeting ? Users won't be able to join this meeting shown from the shortcode.", "video-conferencing-with-zoom-api" ),
				'host_id_search' => __( "Add a valid Host ID or Email address.", "video-conferencing-with-zoom-api" )
			)
		) );
	}

	/**
	 * Add Action links to plugins page.
	 *
	 * @param $actions
	 * @param $plugin_file
	 *
	 * @return array
	 */
	public function action_link( $actions, $plugin_file ) {
		static $plugin;

		if ( ! isset( $plugin ) ) {
			$plugin = VCZAPI_ABS_NAME;
		}

		if ( $plugin == $plugin_file ) {
			$settings = array( 'settings' => '<a href="' . admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' ) . '">' . __( 'Settings', 'video-conferencing-with-zoom-api' ) . '</a>' );

			$actions = array_merge( $settings, $actions );
		}

		return $actions;
	}
}

Plugin::instance();