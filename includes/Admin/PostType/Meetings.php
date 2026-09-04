<?php

namespace Codemanas\VczApi\Admin\PostType;

class Meetings {

	private static ?Meetings $instance = null;

	public static function get_instance(): ?Meetings {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private string $post_type = 'zoom-meetings';

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'hidePostType' ] );
	}

	/**
	 * Register Post Type
	 */
	public function registerPostType(): void {
		$label = [
			'name'               => _x( 'Zoom Meetings and Webinars', 'Zoom Meetings and Webinars', 'video-conferencing-with-zoom-api' ),
			'singular_name'      => _x( 'Zoom Events', 'Zoom Events', 'video-conferencing-with-zoom-api' ),
			'menu_name'          => _x( 'Zoom Events', 'Zoom Events', 'video-conferencing-with-zoom-api' ),
			'name_admin_bar'     => _x( 'Zoom Events', 'Zoom Events', 'video-conferencing-with-zoom-api' ),
			'add_new'            => __( 'Add New', 'video-conferencing-with-zoom-api' ),
			'add_new_item'       => __( 'Add New Event', 'video-conferencing-with-zoom-api' ),
			'new_item'           => __( 'New Zoom Event', 'video-conferencing-with-zoom-api' ),
			'edit_item'          => __( 'Edit Zoom Event', 'video-conferencing-with-zoom-api' ),
			'view_item'          => __( 'View meetings', 'video-conferencing-with-zoom-api' ),
			'all_items'          => __( 'All Events', 'video-conferencing-with-zoom-api' ),
			'search_items'       => __( 'Search meetings', 'video-conferencing-with-zoom-api' ),
			'parent_item_colon'  => __( 'Parent meetings:', 'video-conferencing-with-zoom-api' ),
			'not_found'          => __( 'No zoom events found.', 'video-conferencing-with-zoom-api' ),
			'not_found_in_trash' => __( 'No zoom events found in Trash.', 'video-conferencing-with-zoom-api' ),
		];

		$args = array(
			'labels'             => apply_filters( 'vczapi_admin_cpt_labels', $label ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'menu_icon'          => 'dashicons-video-alt2',
			'capability_type'    => apply_filters( 'vczapi_cpt_capabilities_type', 'post' ),
			'capabilities'       => apply_filters( 'vczapi_cpt_capabilities', array() ),
			'has_archive'        => true,
			'hierarchical'       => apply_filters( 'vczapi_cpt_hierarchical', false ),
			'show_in_rest'       => apply_filters( 'vczapi_cpt_show_in_rest', true ),
			'rest_base'          => 'zoom_meetings',
			'menu_position'      => apply_filters( 'vczapi_cpt_menu_position', 5 ),
			'map_meta_cap'       => apply_filters( 'vczapi_cpt_meta_cap', null ),
			'supports'           => array(
				'title',
				'editor',
				'author',
				'thumbnail',
			),
			'rewrite'            => array( 'slug' => apply_filters( 'vczapi_cpt_slug', $this->post_type ) ),
		);

		register_post_type( $this->post_type, $args );
	}

	/**
	 * Hide Post Type page
	 */
	public function hidePostType(): void
	{
		if (isset($_GET['post_type']) && $_GET['post_type'] !== $this->post_type) {
			return;
		}

		if (! vczapi_is_zoom_activated()) {
			global $submenu;
			//			unset( $submenu['edit.php?post_type=zoom-meetings'][5] );
			unset($submenu['edit.php?post_type=zoom-meetings'][10]);
			unset($submenu['edit.php?post_type=zoom-meetings'][15]);
		}
	}

}