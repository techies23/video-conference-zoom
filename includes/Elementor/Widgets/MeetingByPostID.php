<?php

namespace CodeManas\VczApi\Elementor\Widgets;

use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Elementor widget for showing meeting by POST ID
 *
 * @since 3.4.0
 */
class MeetingByPostID extends Widget_Base {

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
		return 'vczapi_zoom_meeting_by_post_id';
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
		return __( 'Zoom Meeting by Post ID', 'video-conferencing-with-zoom-api' );
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
		return 'fas fa-video';
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
			'post_id',
			[
				'name'        => 'post_id',
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
				$result[ $meeting->ID ] = $meeting->post_title;
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
		if ( ! empty( $settings['post_id'] ) ) {
			echo do_shortcode( '[zoom_meeting_post post_id="' . $settings['post_id'] . '"]' );
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


