<?php

namespace CodeManas\ZoomVideoConferencing\Elementor;

use CodeManas\ZoomVideoConferencing\Elementor\Widgets\Zoom_Video_Conferencing_Elementor_Webinar;
use CodeManas\ZoomVideoConferencing\Elementor\Widgets\Zoom_Video_Conferencing_ElementorMeetingsList;
use CodeManas\ZoomVideoConferencing\Elementor\Widgets\Zoom_Video_Conferencing_Elementor_Meetings;
use CodeManas\ZoomVideoConferencing\Elementor\Widgets\Zoom_Video_Conferencing_ElementorMeetingsHost;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Invoke Elementor Dependency Class
 *
 * Register new elementor widget.
 *
 * @since 3.4.0
 * @author CodeManas
 */
class Zoom_Video_Conferencing_Elementor {

	/**
	 * Constructor
	 *
	 * @since 3.4.0
	 * @author CodeManas
	 *
	 * @access public
	 */
	public function __construct() {
		$this->add_actions();
	}

	/**
	 * Add Actions
	 *
	 * @since 3.4.0
	 * @author CodeManas
	 *
	 * @access private
	 */
	private function add_actions() {
		// Register widget scripts.
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'widget_scripts' ] );

		add_action( 'elementor/widgets/widgets_registered', [ $this, 'on_widgets_registered' ] );
	}

	/**
	 * Widget Styles
	 *
	 * Load required plugin core files.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function widget_scripts() {
		wp_enqueue_script( 'video-conferencing-zoom-elementor', ZVC_PLUGIN_ADMIN_ASSETS_URL . '/js/elementor.js', [ 'elementor-editor' ], ZVC_PLUGIN_VERSION, true );
	}

	/**
	 * On Widgets Registered
	 *
	 * @since 3.4.0
	 * @author CodeManas
	 *
	 * @access public
	 */
	public function on_widgets_registered() {
		$this->includes();
		$this->register_widget();
	}

	/**
	 * Includes
	 *
	 * @since 3.4.0
	 * @author CodeManas
	 *
	 * @access private
	 */
	private function includes() {
		require ZVC_PLUGIN_INCLUDES_PATH . '/elementor/widgets/class-zvc-elementor-meetings.php';
		require ZVC_PLUGIN_INCLUDES_PATH . '/elementor/widgets/class-zvc-elementor-meeting-list.php';
		require ZVC_PLUGIN_INCLUDES_PATH . '/elementor/widgets/class-zvc-elementor-meeting-host.php';
	}

	/**
	 * Register Widget
	 *
	 * @since 3.4.0
	 * @author CodeManas
	 *
	 * @access private
	 */
	private function register_widget() {
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Zoom_Video_Conferencing_Elementor_Meetings() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Zoom_Video_Conferencing_ElementorMeetingsList() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Zoom_Video_Conferencing_ElementorMeetingsHost() );
	}
}

new Zoom_Video_Conferencing_Elementor();