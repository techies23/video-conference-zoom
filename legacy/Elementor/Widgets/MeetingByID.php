<?php

namespace CodeManas\VczApi\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Base_Control;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Elementor widget for Fetching meeting BY ID
 *
 * @since 3.4.0
 */
class MeetingByID extends Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @return string Widget name.
	 * @since 3.4.0
	 *
	 * @access public
	 *
	 */
	public function get_name() {
		return 'vczapi_zoom_meeting';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @return string Widget title.
	 * @since 3.4.0
	 *
	 * @access public
	 *
	 */
	public function get_title() {
		return __( 'Zoom Meeting', 'video-conferencing-with-zoom-api' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @return string Widget icon.
	 * @since 3.4.0
	 *
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'eicon-video-camera';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @return array Widget categories.
	 * @since 3.4.0
	 *
	 * @access public
	 *
	 */
	public function get_categories() {
		return [ 'vczapi-elements' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Select a Zoom Meeting', 'video-conferencing-with-zoom-api' ),
			]
		);

		$this->add_control(
			'meeting_id',
			[
				'name'        => 'meeting_id',
				'label'       => __( 'Meeting', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => $this->getMeetings(),
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Get Meetings
	 *
	 * @return array
	 */
	private function getMeetings() {
		$args       = array(
			'numberposts' => - 1,
			'post_type'   => 'zoom-meetings'
		);
		$result     = array();
		$meetings   = get_posts( $args );
		$result[''] = __( 'Select a Meeting', 'video-conferencing-with-zoom-api' );
		if ( ! empty( $meetings ) ) {
			foreach ( $meetings as $meeting ) {
				$meeting_details            = get_post_meta( $meeting->ID, '_meeting_zoom_meeting_id', true );
				$result[ $meeting_details ] = $meeting->post_title;
			}
		}

		wp_reset_postdata();

		return $result;
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 3.4.0
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		if ( ! empty( $settings['meeting_id'] ) ) {
			echo do_shortcode( '[zoom_api_link meeting_id="' . $settings['meeting_id'] . '"]' );
		}
	}

	/**
	 * Render the widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 3.4.0
	 *
	 * @access protected
	 */
	protected function _content_template() {

	}
}


