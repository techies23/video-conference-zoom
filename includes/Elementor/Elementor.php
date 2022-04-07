<?php

namespace CodeManas\VczApi\Elementor;

use CodeManas\VczApi\Elementor\Widgets\MeetingByPostID;
use CodeManas\VczApi\Elementor\Widgets\MeetingList;
use CodeManas\VczApi\Elementor\Widgets\MeetingByID;
use CodeManas\VczApi\Elementor\Widgets\MeetingHosts;
use CodeManas\VczApi\Elementor\Widgets\EmbedMeetings;
use CodeManas\VczApi\Elementor\Widgets\RecordingsByHost;
use CodeManas\VczApi\Elementor\Widgets\RecordingByMeetingID;
use CodeManas\VczApi\Elementor\Widgets\WebinarList;
use Elementor\Plugin;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Invoke Elementor Dependency Class
 *
 * Register new elementor widget.
 *
 * @since 3.4.0
 * @author CodeManas
 */
class Elementor {

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
		#add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'widget_scripts' ] );
		add_action( 'elementor/widgets/register', [ $this, 'on_widgets_registered' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'widget_categories' ] );
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
	 * @modified 3.9.0
	 * @author Deepen Bajracharya
	 *
	 * @access public
	 *
	 * @param $widgets_manager
	 *
	 * @throws \Exception
	 */
	public function on_widgets_registered( $widgets_manager ) {
		$this->includes();
		$this->register_widget( $widgets_manager );
	}

	/**
	 * Register Widget Category
	 *
	 * @param $elements_manager
	 */
	public function widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'vczapi-elements',
			[
				'title'  => 'Zoom',
				'icon'   => 'eicon-video-camera',
				'active' => true
			]
		);
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
		require ZVC_PLUGIN_INCLUDES_PATH . '/Elementor/Widgets/MeetingByID.php';
		require ZVC_PLUGIN_INCLUDES_PATH . '/Elementor/Widgets/MeetingList.php';
		require ZVC_PLUGIN_INCLUDES_PATH . '/Elementor/Widgets/WebinarList.php';
		require ZVC_PLUGIN_INCLUDES_PATH . '/Elementor/Widgets/MeetingHosts.php';
		require ZVC_PLUGIN_INCLUDES_PATH . '/Elementor/Widgets/EmbedMeetings.php';
		require ZVC_PLUGIN_INCLUDES_PATH . '/Elementor/Widgets/RecordingsByHost.php';
		require ZVC_PLUGIN_INCLUDES_PATH . '/Elementor/Widgets/RecordingByMeetingID.php';
		require ZVC_PLUGIN_INCLUDES_PATH . '/Elementor/Widgets/MeetingByPostID.php';
	}

	/**
	 * Register Widget
	 *
	 * @param $widgets_manager
	 *
	 * @throws \Exception
	 * @since 3.4.0
	 * @modified 3.9.0
	 * @author Deepen Bajracharya
	 */
	private function register_widget( $widgets_manager ) {
		$widgets_manager->register( new MeetingByID() );
		$widgets_manager->register( new MeetingList() );
		$widgets_manager->register( new MeetingHosts() );
		$widgets_manager->register( new EmbedMeetings() );
		$widgets_manager->register( new RecordingsByHost() );
		$widgets_manager->register( new RecordingByMeetingID() );
		$widgets_manager->register( new WebinarList() );
		$widgets_manager->register( new MeetingByPostID() );
	}
}

new Elementor();