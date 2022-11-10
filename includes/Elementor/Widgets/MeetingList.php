<?php

namespace CodeManas\VczApi\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Base_Control;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Elementor widget for showing meeting LIST
 *
 * @since 3.4.0
 * @author CodeManas
 */
class MeetingList extends Widget_Base {

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
		return 'vczapi_meetings_list';
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
		return __( 'Zoom Meetings List', 'video-conferencing-with-zoom-api' );
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
				'label' => __( 'Content', 'video-conferencing-with-zoom-api' ),
			]
		);

		$this->add_control(
			'author_id',
			[
				'name'        => 'Author ID',
				'label'       => __( 'Author ID', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'label_block' => true,
				'min'         => 1,
				'description' => __( 'Show meeting posts based on Author ID', 'video-conferencing-with-zoom-api' )
			]
		);

		$this->add_control(
			'category',
			[
				'name'        => 'category',
				'label'       => __( 'Meetings Category', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'options'     => $this->get_taxnomies(),
				'default'     => ''
			]
		);

		$this->add_control(
			'order',
			[
				'name'        => 'order',
				'label'       => __( 'Meetings Order By', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => [
					'ASC'  => 'Ascending',
					'DESC' => 'Descending'
				],
				'default'     => 'DESC'
			]
		);

		$this->add_control(
			'type',
			[
				'name'        => 'type',
				'label'       => __( 'Meetings Type', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => [
					''         => 'Show All',
					'upcoming' => 'Upcoming',
					'past'     => 'Past'
				],
				'default'     => ''
			]
		);

		$this->add_control(
			'filter',
			[
				'name'        => 'filter',
				'label'       => __( 'Show Filter', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => [
					'yes' => 'Yes',
					'no'  => 'No'
				],
				'default'     => 'no'
			]
		);

		$this->add_control(
			'count',
			[
				'name'        => 'count',
				'label'       => __( 'Count of meetings', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'label_block' => true,
				'min'         => 1,
				'default'     => 3
			]
		);

		$this->add_control(
			'show_on_past',
			[
				'name'        => 'show_on_past',
				'label'       => __( 'Meeting Display Threshold', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => [
					'yes' => 'Yes',
					'no'  => 'No'
				],
				'default'     => 'yes',
				'description' => __( 'Setting this to yes will display meetings for 30 minutes more after the meeting date has passed. This will only work if Meeting Type is defined.', 'video-conferencing-with-zoom-api' )
			]
		);

		$this->add_control(
			'cols',
			[
				'name'        => 'cols',
				'label'       => __( 'Column Layout', 'video-conferencing-with-zoom-api' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => [
					1 => '1 column layout',
					2 => '2 column layout',
					3 => '3 column layout',
					4 => '4 column layout',
				],
				'default'     => 3
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Get Taxonomies for Zoom meeting
	 *
	 * @return array
	 */
	private function get_taxnomies() {
		$args   = array(
			'taxonomy'   => 'zoom-meeting',
			'hide_empty' => false
		);
		$terms  = get_terms( $args );
		$result = [];
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[ $term->slug ] = $term->name;
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

		$count        = ! empty( $settings['count'] ) ? $settings['count'] : 5;
		$category     = ! empty( $settings['category'] ) ? implode( ',', $settings['category'] ) : '';
		$type         = ! empty( $settings['type'] ) ? $settings['type'] : '';
		$order        = ! empty( $settings['order'] ) ? $settings['order'] : 'DESC';
		$filter       = ! empty( $settings['filter'] ) ? $settings['filter'] : 'no';
		$author_id    = ! empty( $settings['author_id'] ) ? 'author=' . $settings['author_id'] : '';
		$show_on_past = ! empty( $settings['show_on_past'] ) ? $settings['show_on_past'] : 'yes';
		$columns      = ! empty( $settings['cols'] ) ? 'cols=' . $settings['cols'] : 3;

		echo do_shortcode( '[zoom_list_meetings ' . $author_id . ' show_on_past="' . esc_attr( $show_on_past ) . '" filter="' . esc_attr( $filter ) . '" per_page="' . esc_attr( $count ) . '" ' . $columns . ' category="' . esc_attr( $category ) . '" order="' . esc_attr( $order ) . '" type="' . esc_attr( $type ) . '"]' );
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