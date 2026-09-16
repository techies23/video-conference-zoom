<?php

namespace Codemanas\VczApi\Admin\Foundation\PostType;

class Taxonomy {

	private string $postType;

	public function __construct( string $postType ) {
		$this->postType = $postType;
	}

	public function register(): void {
		$labels = array(
			'name' => _x( 'Category', 'Zoom Category Name', 'video-conferencing-with-zoom-api' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rest_base'         => 'zoom_meeting_cats',
			'query_var'         => true,
		);

		register_taxonomy( 'zoom-meeting', array( $this->postType ), $args );
	}
}