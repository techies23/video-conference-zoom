<?php

namespace Codemanas\VczApi;

use Codemanas\VczApi\Shortcodes\Embed;
use Codemanas\VczApi\Shortcodes\Meetings;
use Codemanas\VczApi\Shortcodes\Recordings;
use Codemanas\VczApi\Shortcodes\Webinars;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Shortcodes Controller
 *
 * @since   3.0.0
 * @author  Deepen
 */
class Shortcodes {

	public static $_instance = null;

	public static function get_instance() {
		return is_null( self::$_instance ) ? self::$_instance = new self() : self::$_instance;
	}

	/**
	 * Shortcodes container
	 *
	 * @var array
	 */
	private array $shortcodes;

	/**
	 * Zoom_Video_Conferencing_Shorcodes constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 100 );

		$meetings         = Meetings::get_instance();
		$webinars         = Webinars::get_instance();
		$recordings       = Recordings::get_instance();
		$embedded         = Embed::get_instance();
		$this->shortcodes = array(
			'zoom_api_link'              => array( $meetings, 'show_meeting_by_ID' ),
			'zoom_meeting_post'          => array( $meetings, 'show_meeting_by_postTypeID' ),
			'zoom_list_meetings'         => array( $meetings, 'list_cpt_meetings' ),
			'zoom_list_host_meetings'    => array( $meetings, 'list_live_host_meetings' ),

			//Embed Browser
			'zoom_join_via_browser'      => array( $embedded, 'join_via_browser' ),

			//Webinars
			'zoom_api_webinar'           => array( $webinars, 'show_webinar_by_ID' ),
			'zoom_list_webinars'         => array( $webinars, 'list_cpt_webinars' ),
			'zoom_list_host_webinars'    => array( $webinars, 'list_live_host_webinars' ),

			//Recordings
			'zoom_recordings'            => array( $recordings, 'recordings_by_user' ),
			'zoom_recordings_by_meeting' => array( $recordings, 'recordings_by_meeting_id' )
		);

		$this->init_shortcodes();
	}

	/**
	 * Init the Shortcode adding function
	 */
	public function init_shortcodes() {
		foreach ( $this->shortcodes as $shortcode => $callback ) {
			add_shortcode( $shortcode, $callback );
		}
	}

	/**
	 * Enqueuing Scripts
	 */
	public function enqueue_scripts() {
		$minified = SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'video-conferencing-with-zoom-api' );
		wp_register_style( 'video-conferencing-with-zoom-api-datable', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable/jquery.dataTables.min.css', false, ZVC_PLUGIN_VERSION );
		wp_register_style( 'video-conferencing-with-zoom-api-datable-responsive', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable-responsive/responsive.dataTables.min.css', [ 'video-conferencing-with-zoom-api-datable' ], ZVC_PLUGIN_VERSION );
		wp_register_script( 'video-conferencing-with-zoom-api-datable-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable/jquery.dataTables.min.js', [ 'jquery' ], ZVC_PLUGIN_VERSION, true );
		wp_register_script( 'video-conferencing-with-zoom-api-datable-dt-responsive-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable-responsive/dataTables.responsive.min.js', [
			'jquery',
			'video-conferencing-with-zoom-api-datable-js'
		], ZVC_PLUGIN_VERSION, true );
		wp_register_script( 'video-conferencing-with-zoom-api-datable-responsive-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable-responsive/responsive.dataTables.min.js', [
			'jquery',
			'video-conferencing-with-zoom-api-datable-js'
		], ZVC_PLUGIN_VERSION, true );
		wp_register_script( 'video-conferncing-with-zoom-browser-js', ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/js/join-via-browser' . $minified . '.js', array( 'jquery' ), ZVC_PLUGIN_VERSION, true );
		wp_register_script( 'video-conferencing-with-zoom-api-shortcode-js', ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/js/shortcode' . $minified . '.js', [
			'jquery',
			'video-conferencing-with-zoom-api-datable-js'
		], ZVC_PLUGIN_VERSION, true );
		wp_localize_script( 'video-conferencing-with-zoom-api-shortcode-js', 'vczapi_ajax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		) );
		wp_localize_script( 'video-conferencing-with-zoom-api-datable-js', 'vczapi_dt_i18n', array(
			'emptyTable'     => __( 'No data available in table', 'video-conferencing-with-zoom-api' ),
			'info'           => sprintf( __( 'Showing %s to %s of %s entries', 'video-conferencing-with-zoom-api' ), '_START_', '_END_', '_TOTAL_' ),
			'infoEmpty'      => __( '', 'video-conferencing-with-zoom-api' ),
			'infoFiltered'   => sprintf( __( 'filtered from %s total entries', 'video-conferencing-with-zoom-api' ), '_MAX_' ),
			'lengthMenu'     => sprintf( __( 'Show %s entries', 'video-conferencing-with-zoom-api' ), '_MENU_' ),
			'loadingRecords' => __( 'Loading', 'video-conferencing-with-zoom-api' ),
			'processing'     => __( 'Processing', 'video-conferencing-with-zoom-api' ),
			'search'         => __( 'Search', 'video-conferencing-with-zoom-api' ),
			'zeroRecords'    => __( 'No matching records found', 'video-conferencing-with-zoom-api' ),
			'paginate'       => [
				'first'    => __( 'First', 'video-conferencing-with-zoom-api' ),
				'last'     => __( 'Last', 'video-conferencing-with-zoom-api' ),
				'next'     => __( 'Next', 'video-conferencing-with-zoom-api' ),
				'previous' => __( 'Previous', 'video-conferencing-with-zoom-api' )
			]
		) );
	}
}