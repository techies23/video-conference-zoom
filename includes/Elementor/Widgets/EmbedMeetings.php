<?php

namespace CodeManas\VczApi\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Base_Control;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Elementor widget for Embedding Meetings in browser
 *
 * @since  3.4.0
 * @author CodeManas
 */
class EmbedMeetings extends Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @return string Widget name.
	 * @since  3.4.0
	 * @author CodeManas
	 *
	 * @access public
	 *
	 */
	public function get_name() {
		return 'vczapi_meetings_embed';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @return string Widget title.
	 * @since  3.4.0
	 * @author CodeManas
	 *
	 * @access public
	 *
	 */
	public function get_title() {
		return __( 'Embed Zoom Meeting', 'video-conferencing-with-zoom-api' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @return string Widget icon.
	 * @since  3.4.0
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
	 * @since  3.4.0
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
				'label' => __( 'Embed Meeting', 'video-conferencing-with-zoom-api' ),
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
			'login_required',
			[
				'name'        => 'login_required',
				'label'       => __( 'Requires Login?', 'video-conferencing-with-zoom-api' ),
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

		$this->add_control(
			'iframe',
			[
				'name'        => 'iframe',
				'label'       => __( 'Show in Iframe?', 'video-conferencing-with-zoom-api' ),
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

		$this->add_control(
			'title_text',
			[
				'label'       => __( 'Title', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Default title', 'video-conferencing-with-zoom-api' ),
				'placeholder' => __( 'Type your title here', 'video-conferencing-with-zoom-api' ),
			]
		);

		$this->add_control(
			'height',
			[
				'label'       => __( 'Embed Height', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'description' => __( 'Only valid when show in iframe is enabled.', 'video-conferencing-with-zoom-api' ),
				'placeholder' => '500',
				'default'     => 500,
				'min'         => 1
			]
		);

		$this->add_control(
			'disable_countdown',
			[
				'name'        => 'disable_countdown',
				'label'       => __( 'Disable Countdown Timer?', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'multiple'    => false,
				'options'     => [
					'yes' => 'Yes',
					'no'  => 'No'
				],
				'default'     => 'yes'
			]
		);

		$this->add_control(
			'passcode',
			[
				'label'       => __( 'Meeting Password', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __( 'Meeting Password', 'video-conferencing-with-zoom-api' ),
			]
		);

		$this->add_control(
			'image',
			[
				'name'        => 'image',
				'label'       => __( 'Image URL', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html( 'https://images.unsplash.com/photo-1459257831348-f0cdd359235f' ),
				'options'     => false,
				'label_block' => true,
			]
		);

		$this->add_control(
			'enable_webinar',
			[
				'name'        => 'enable_webinar',
				'label'       => __( 'Is Webinar ?', 'video-conferencing-with-zoom-api' ),
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
	 * @since  3.4.0
	 * @author CodeManas
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$meeting_id        = ! empty( $settings['meeting_id'] ) ? $settings['meeting_id'] : false;
		$login_required    = ! empty( $settings['login_required'] ) ? $settings['login_required'] : 'no';
		$iframe            = ! empty( $settings['iframe'] ) ? $settings['iframe'] : 'no';
		$title_text        = ! empty( $settings['title_text'] ) ? $settings['title_text'] : false;
		$height            = ! empty( $settings['height'] ) ? (int) $settings['height'] : 500;
		$disable_countdown = ! empty( $settings['disable_countdown'] ) ? $settings['disable_countdown'] : 'yes';
		$passcode          = ! empty( $settings['passcode'] ) ? $settings['passcode'] : '';
		$enable_webinar    = ! empty( $settings['enable_webinar'] ) ? $settings['enable_webinar'] : 'yes';
		$image             = ! empty( $settings['image'] ) && ! empty( $settings['image']['url'] ) ? 'image=' . esc_url( $settings['image']['url'] ) : false;
		if ( ! empty( $meeting_id ) ) {
			echo do_shortcode( '[zoom_join_via_browser meeting_id="' . esc_attr( $meeting_id ) . '" login_required="' . esc_attr( $login_required ) . '" iframe="' . esc_attr( $iframe ) . '" title="' . esc_attr( $title_text ) . '" height="' . esc_attr( $height ) . 'px" disable_countdown="' . esc_attr( $disable_countdown ) . '" passcode="' . esc_attr( $passcode ) . '" webinar="' . esc_attr( $enable_webinar ) . '" ' . $image . ' ]' );
		} else {
			_e( 'No Meeting ID is defined.', 'video-conferencing-with-zoom-api' );
		}
	}

	/**
	 * Render the widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since  3.4.0
	 * @author CodeManas
	 *
	 * @access protected
	 */
	protected function _content_template() {

	}
}