<?php

namespace CodeManas\VczApi\Elementor\Widgets;

use Elementor\Widget_Base;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor widget for Showing recordings by Meeting ID
 *
 * @since 3.4.0
 * @author CodeManas
 */
class RecordingByMeetingID extends Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @return string Widget name.
	 * @since 3.4.0
	 * @author CodeManas
	 *
	 * @access public
	 *
	 */
	public function get_name() {
		return 'vczapi_meetings_recordings_by_meetingid';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @return string Widget title.
	 * @since 3.4.0
	 * @author CodeManas
	 *
	 * @access public
	 *
	 */
	public function get_title() {
		return __( 'Recordings by Meeting', 'video-conferencing-with-zoom-api' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @return string Widget icon.
	 * @since 3.4.0
	 * @author CodeManas
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
	 * @author CodeManas
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
				'label' => __( 'Recording by Meeting', 'video-conferencing-with-zoom-api' ),
			]
		);

		$this->add_control(
			'meeting_id',
			[
				'label'       => __( 'Meeting ID', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => '1234567890',
				'title'       => 'Your meeting ID'
			]
		);

		$this->add_control(
			'downloadable',
			[
				'name'        => 'downloadable',
				'label'       => __( 'Show Downloadable Link', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'multiple'    => false,
				'options'     => [
					'yes' => 'Yes',
					'no'  => 'No'
				],
				'default'     => 'no'
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 3.4.0
	 * @author CodeManas
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$meeting_id   = ! empty( $settings['meeting_id'] ) ? $settings['meeting_id'] : false;
		$downloadable = ! empty( $settings['downloadable'] ) ? $settings['downloadable'] : 'no';
		if ( ! empty( $meeting_id ) ) {
			echo do_shortcode( '[zoom_recordings_by_meeting meeting_id=' . esc_attr( $meeting_id ) . ' downloadable=' . esc_attr( $downloadable ) . ']' );
		} else {
			_e( 'No meeting ID is defined.', 'video-conferencing-with-zoom-api' );
		}
	}

	/**
	 * Render the widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 3.4.0
	 * @author CodeManas
	 *
	 * @access protected
	 */
	protected function _content_template() {

	}
}