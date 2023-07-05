<?php

namespace Codemanas\VczApi;

use Codemanas\VczApi\Blocks\Blocks;
use Codemanas\VczApi\Blocks\BlockTemplates;
use Codemanas\VczApi\Helpers\Encryption;

if ( ! defined( 'ABSPATH' ) ) {
	die( "Not Allowed Here !" ); // If this file is called directly, abort.
}

/**
 * Ready Main Class
 *
 * @since   2.1.0
 * @updated 3.6.0
 * @author  Deepen
 */
final class Bootstrap {

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

	private $plugin_version = ZVC_PLUGIN_VERSION;

	/**
	 * Constructor method for loading the components
	 *
	 * @since  2.0.0
	 * @author Deepen
	 */
	public function __construct() {
		$this->autoloader();
		$this->load_dependencies();
		$this->init_api();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_backend' ) );
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

        //Block Themes Compat: register scripts on init - required as block themes fire the content before page render
		add_action( 'init', [ $this, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'plugin_action_links', array( $this, 'action_link' ), 10, 2 );
		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );
		add_filter( 'wp_headers', [ $this, 'set_corp_headers' ], 10, 2 );

		add_action( 'in_plugin_update_message-' . ZVC_PLUGIN_ABS_NAME, function ( $plugin_data ) {
			$this->version_update_warning( ZVC_PLUGIN_VERSION, $plugin_data['new_version'] );
		} );
	}

	/**
	 * Major Version Upgrade Notice
	 *
	 * @param $current_version
	 * @param $new_version
	 */
	public function version_update_warning( $current_version, $new_version ) {
		$current_version_minor_part = explode( '.', $current_version )[1];
		$new_version_minor_part     = explode( '.', $new_version )[1];

		if ( $current_version_minor_part === $new_version_minor_part ) {
			return;
		}
		?>
        <hr class="vczapi-major-update-warning__separator"/>
        <div class="vczapi-major-update-warning">
            <div class="vczapi-major-update-warning__icon">
                <span class="dashicons dashicons-info-outline"></span>
            </div>
            <div class="vczapi-major-update-warning_wrapper">
                <div class="vczapi-major-update-warning__title">
					<?php esc_html_e( 'Heads up, Please backup before upgrade!', 'video-conferencing-with-zoom-api' ); ?>
                </div>
                <div class="vczapi-major-update-warning__message">
					<?php
					esc_html_e( 'The latest update includes some substantial changes across different areas of the plugin. We highly recommend you backup your site before upgrading, and make sure you first update in a staging environment', 'video-conferencing-with-zoom-api' );
					?>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Add CORP headers for Zoom Meetings join via browser page
	 *
	 * @param $headers
	 * @param $wp
	 *
	 * @return mixed
	 */
	function set_corp_headers( $headers, $wp ) {
		$type = filter_input( INPUT_GET, 'type' );
		if ( isset( $wp->query_vars['post_type'] ) && $wp->query_vars['post_type'] == 'zoom-meetings' && ! empty( $type ) ) {
			$headers['Cross-Origin-Embedder-Policy'] = 'require-corp';
			$headers['Cross-Origin-Opener-Policy']   = 'same-origin';
		}

		return $headers;
	}

	public function autoloader() {
		require_once ZVC_PLUGIN_DIR_PATH . 'vendor/autoload.php';
	}

	/**
	 * INitialize the hooks
	 *
	 * @since    2.0.0
	 * @modified 2.1.0
	 * @author   Deepen Bajracharya
	 */
	protected function init_api() {
		//Load the Credentials
		zoom_conference()->zoom_api_key    = get_option( 'zoom_api_key' );
		zoom_conference()->zoom_api_secret = get_option( 'zoom_api_secret' );
	}

	/**
	 * @return void
	 */
	public function register_scripts(  ) {
		$minified = SCRIPT_DEBUG ? '' : '.min';
		wp_register_style( 'video-conferencing-with-zoom-api', ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/css/style' . $minified . '.css', false, $this->plugin_version );

		$disable_moment_js = get_option( 'zoom_api_disable_moment_js' );
		if ( empty( $disable_moment_js ) ) {
			//Enqueue MomentJS
			wp_register_script( 'video-conferencing-with-zoom-api-moment', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/moment/moment.min.js', array( 'jquery' ), $this->plugin_version, true );
			wp_register_script( 'video-conferencing-with-zoom-api-moment-locales', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/moment/moment-with-locales.min.js', array(
				'jquery',
				'video-conferencing-with-zoom-api-moment',
			), $this->plugin_version, true );
			//Enqueue MomentJS Timezone
			wp_register_script( 'video-conferencing-with-zoom-api-moment-timezone', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/moment-timezone/moment-timezone-with-data-10-year-range.min.js', array( 'jquery' ), $this->plugin_version, true );
			wp_register_script( 'video-conferencing-with-zoom-api', ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/js/public' . $minified . '.js', array(
				'jquery',
				'video-conferencing-with-zoom-api-moment',
			), $this->plugin_version, true );
		}
    }
    
	/**
	 * Load Frontend Scriptsssssss
	 *
	 * @since   3.0.0
	 * @author  Deepen Bajracharya
	 */
	function enqueue_scripts() {
		if ( is_singular( 'zoom-meetings' ) ) {
			wp_enqueue_style( 'video-conferencing-with-zoom-api' );
			wp_enqueue_script( 'video-conferencing-with-zoom-api-moment' );
			wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-locales' );
			wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-timezone' );
			wp_enqueue_script( 'video-conferencing-with-zoom-api' );
			// Localize the script with new data
			$date_format = get_option( 'zoom_api_date_time_format' );
			if ( $date_format == 'custom' ) {
				$date_format = get_option( 'zoom_api_custom_date_time_format' );
				$date_format = vczapi_convertPHPToMomentFormat( $date_format );
			}

			$zoom_going_to_start = get_option( 'zoom_going_tostart_meeting_text' );
			$zoom_ended          = get_option( 'zoom_ended_meeting_text' );
			$translation_array   = apply_filters( 'vczapi_meeting_event_text', array(
				'meeting_starting' => ! empty( $zoom_going_to_start ) ? $zoom_going_to_start : __( 'Click join button below to join the meeting now !', 'video-conferencing-with-zoom-api' ),
				'meeting_ended'    => ! empty( $zoom_ended ) ? $zoom_ended : __( 'This meeting has been ended by the host.', 'video-conferencing-with-zoom-api' ),
				'date_format'      => $date_format,
			) );
			wp_localize_script( 'video-conferencing-with-zoom-api', 'zvc_strings', $translation_array );
		}
	}

	/**
	 * Include template files
	 *
	 * @since  3.7.1
	 */
	public function include_template_functions() {
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/template-functions.php';
	}

	/**
	 * Load the other class dependencies
	 *
	 * @since    2.0.0
	 * @modified 2.1.0
	 * @author   Deepen Bajracharya
	 */
	protected function load_dependencies() {
		//Include the Main Class
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/api/class-zvc-zoom-api-v2.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/api/S2SOAuth.php';

		//Loading Includes
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/helpers.php';

		//AJAX CALLS SCRIPTS
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-ajax.php';

		//Admin Classes
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-post-type.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-users.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-meetings.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-webinars.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-reports.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-recordings.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-settings.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-addons.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-sync.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-setup-wizard.php';

		//Timezone
		Timezone::get_instance();

		//Templates
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/template-hooks.php';
		Filters::get_instance();

		//Shortcode
		Shortcodes::get_instance();

		if ( did_action( 'elementor/loaded' ) ) {
			require ZVC_PLUGIN_INCLUDES_PATH . '/Elementor/Elementor.php';
		}

		Blocks::get_instance();
		BlockTemplates::get_instance();

        //Helpers
        Encryption::get_instance();
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
			wp_enqueue_style( 'video-conferencing-with-zoom-api-timepicker', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/dtimepicker/jquery.datetimepicker.min.css', false, $this->plugin_version );
			wp_enqueue_style( 'video-conferencing-with-zoom-api-select2', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/select2/css/select2.min.css', false, $this->plugin_version );
			wp_enqueue_style( 'video-conferencing-with-zoom-api-datable', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable/jquery.dataTables.min.css', false, $this->plugin_version );
		}

		wp_register_script( 'video-conferencing-with-zoom-api-select2-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/select2/js/select2.min.js', array( 'jquery' ), $this->plugin_version, true );
		wp_register_script( 'video-conferencing-with-zoom-api-timepicker-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/dtimepicker/jquery.datetimepicker.full.js', array( 'jquery' ), $this->plugin_version, true );
		wp_register_script( 'video-conferencing-with-zoom-api-datable-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable/jquery.dataTables.min.js', array( 'jquery' ), $this->plugin_version, true );

		//Plugin Scripts
		wp_enqueue_style( 'video-conferencing-with-zoom-api-admin', ZVC_PLUGIN_ADMIN_ASSETS_URL . '/css/style.min.css', false, $this->plugin_version );
		wp_register_script( 'video-conferencing-with-zoom-api-js', ZVC_PLUGIN_ADMIN_ASSETS_URL . '/js/script.min.js', array(
			'jquery',
			'video-conferencing-with-zoom-api-select2-js',
			'video-conferencing-with-zoom-api-timepicker-js',
			'video-conferencing-with-zoom-api-datable-js',
			'underscore',
		), $this->plugin_version, true );

		wp_localize_script( 'video-conferencing-with-zoom-api-js', 'zvc_ajax', array(
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'zvc_security' => wp_create_nonce( "_nonce_zvc_security" ),
			'lang'         => array(
				'confirm_end'    => __( "Are you sure you want to end this meeting ? Users won't be able to join this meeting shown from the shortcode.", "video-conferencing-with-zoom-api" ),
				'host_id_search' => __( "Add a valid Host ID or Email address.", "video-conferencing-with-zoom-api" ),
			),
		) );
	}

	/**
	 * Load Plugin Domain Text here
	 *
	 * @since  2.0.0
	 * @author Deepen
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'video-conferencing-with-zoom-api', false, ZVC_PLUGIN_LANGUAGE_PATH );
	}

	/**
	 * Fire on Activation
	 *
	 * @since  1.0.0
	 * @author Deepen
	 */
	public static function activate() {
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-post-type.php';
		$post_type = \Zoom_Video_Conferencing_Admin_PostType::get_instance();
		$post_type->register();

		//Flush User Cache
		update_option( '_zvc_user_lists', '' );
		update_option( '_zvc_user_lists_expiry_time', '' );

		//Flush Permalinks
		flush_rewrite_rules();
	}

	/**
	 * Deactivating the plugin
	 */
	public static function deactivate() {
		//Flush User Cache
		update_option( '_zvc_user_lists', '' );
		update_option( '_zvc_user_lists_expiry_time', '' );

		flush_rewrite_rules();
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
			$plugin = ZVC_PLUGIN_ABS_NAME;
		}

		if ( $plugin == $plugin_file ) {
			$settings = array( 'settings' => '<a href="' . admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' ) . '">' . __( 'Settings', 'video-conferencing-with-zoom-api' ) . '</a>' );

			$actions = array_merge( $settings, $actions );
		}

		return $actions;
	}
}