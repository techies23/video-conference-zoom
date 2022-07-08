<?php

namespace Codemanas\VczApi\Includes;

use Codemanas\VczApi\Includes\Api\OAuth;

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
		$this->load_dependencies();
		$this->init_api();

		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );
		add_filter( 'wp_headers', [ $this, 'set_corp_headers' ], 10, 2 );
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

	/**
	 * INitialize the hooks
	 *
	 * @since    2.0.0
	 * @modified 2.1.0
	 * @author   Deepen Bajracharya
	 */
	protected function init_api() {
		if ( Fields::get_option( "using_oauth" ) ) {
			OAuth::instance();
		}
	}

	/**
	 * Load Frontend Scriptsssssss
	 *
	 * @since   3.0.0
	 * @author  Deepen Bajracharya
	 */
	function enqueue_scripts() {
		$minified = SCRIPT_DEBUG ? '' : '.min';
		wp_register_style( 'video-conferencing-with-zoom-api', ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/css/style' . $minified . '.css', false, $this->plugin_version );
		//Enqueue MomentJS
		wp_register_script( 'video-conferencing-with-zoom-api-moment', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/moment/moment.min.js', array( 'jquery' ), $this->plugin_version, true );
		wp_register_script( 'video-conferencing-with-zoom-api-moment-locales', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/moment/moment-with-locales.min.js', array(
			'jquery',
			'video-conferencing-with-zoom-api-moment'
		), $this->plugin_version, true );
		//Enqueue MomentJS Timezone
		wp_register_script( 'video-conferencing-with-zoom-api-moment-timezone', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/moment-timezone/moment-timezone-with-data-10-year-range.min.js', array( 'jquery' ), $this->plugin_version, true );
		wp_register_script( 'video-conferencing-with-zoom-api', ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/js/public' . $minified . '.js', array(
			'jquery',
			'video-conferencing-with-zoom-api-moment'
		), $this->plugin_version, true );
		if ( is_singular( 'zoom-meetings' ) ) {
			wp_enqueue_style( 'video-conferencing-with-zoom-api' );
			wp_enqueue_script( 'video-conferencing-with-zoom-api-moment' );
			wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-locales' );
			wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-timezone' );
			wp_enqueue_script( 'video-conferencing-with-zoom-api' );
			// Localize the script with new data
			$date_format = get_option( 'zoom_api_date_time_format' );
			//check if custom time format
			// that is it is in either of L LT, l LT,llll,lll,LLLL

			if ( $date_format == 'custom' ) {
				$date_format = get_option( 'zoom_api_custom_date_time_format' );
				$date_format = vczapi_convertPHPToMomentFormat( $date_format );
			}

			$zoom_started        = get_option( 'zoom_started_meeting_text' );
			$zoom_going_to_start = get_option( 'zoom_going_tostart_meeting_text' );
			$zoom_ended          = get_option( 'zoom_ended_meeting_text' );
			$translation_array   = apply_filters( 'vczapi_meeting_event_text', array(
				'meeting_started'  => ! empty( $zoom_started ) ? $zoom_started : __( 'Meeting Has Started ! Click below join button to join meeting now !', 'video-conferencing-with-zoom-api' ),
				'meeting_starting' => ! empty( $zoom_going_to_start ) ? $zoom_going_to_start : __( 'Click join button below to join the meeting now !', 'video-conferencing-with-zoom-api' ),
				'meeting_ended'    => ! empty( $zoom_ended ) ? $zoom_ended : __( 'This meeting has been ended by the host.', 'video-conferencing-with-zoom-api' ),
				'date_format'      => $date_format
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
//		require_once ZVC_PLUGIN_INCLUDES_PATH . '/api/class-zvc-zoom-api-v2.php';

		//Loading Includes
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/helpers.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/Data/Datastore.php';

		//AJAX CALLS SCRIPTS
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-ajax.php';

		//Admin Classes
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-post-type.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-meetings.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-webinars.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-reports.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-recordings.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-addons.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/admin/class-zvc-admin-sync.php';

		//Timezone
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/Timezone.php';

		//Templates
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/template-hooks.php';
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/Filters.php';

		//Shortcode
		require_once ZVC_PLUGIN_INCLUDES_PATH . '/Shortcodes.php';

		if ( did_action( 'elementor/loaded' ) ) {
			require ZVC_PLUGIN_INCLUDES_PATH . '/Elementor/Elementor.php';
		}

		require_once ZVC_PLUGIN_INCLUDES_PATH . '/Blocks/Blocks.php';
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
}