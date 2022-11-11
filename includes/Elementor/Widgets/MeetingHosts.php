<?php

namespace CodeManas\VczApi\Elementor\Widgets;

use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Elementor widget for Meeting via Hosts
 *
 * @since 3.4.0
 * @author CodeManas
 */
class MeetingHosts extends Widget_Base {

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
		return 'vczapi_meetings_by_host';
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
		return __( 'Zoom Meetings via Host', 'video-conferencing-with-zoom-api' );
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
				'label' => __( 'Show Meeting by Zoom User', 'video-conferencing-with-zoom-api' ),
			]
		);

		$this->add_control(
			'host_id',
			[
				'name'        => 'host_id',
				'label'       => __( 'Select User', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => false,
				'options'     => $this->get_hosts(),
				'default'     => ''
			]
		);

		$this->add_control(
			'type',
			[
				'name'        => 'type',
				'label'       => __( 'Type', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'multiple'    => false,
				'options'     => [
					1 => 'Meeting',
					2 => 'Webinar'
				],
				'default'     => 1
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Get Taxonomies for Zoom meeting
	 *
	 * @return array
	 */
	private function get_hosts() {
		$users  = video_conferencing_zoom_api_get_user_transients();
		$result = array();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$result[ $user->id ] = $user->first_name . ' ' . $user->last_name . '(' . $user->email . ')';
			}
		}

		return $result;
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

		$host_id = ! empty( $settings['host_id'] ) ? $settings['host_id'] : false;
		$type    = ! empty( $settings['type'] ) ? $settings['type'] : 1;

		if ( ! empty( $host_id ) ) {
			if ( $type == 1 ) {
				echo do_shortcode( '[zoom_list_host_meetings host=' . esc_attr( $host_id ) . ']' );
			} else {
				echo do_shortcode( '[zoom_list_host_webinars host=' . esc_attr( $host_id ) . ']' );
			}
		} else {
			_e( 'No user selected.', 'video-conferencing-with-zoom-api' );
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