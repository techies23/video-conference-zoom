<?php

namespace Codemanas\VczApi\Admin\Foundation\PostType;

class CustomPostType {

	private string $postType;

	public function __construct( string $postType ) {
		$this->postType = $postType;
	}

	public function register(): void {
		$definition = new ZoomPostTypeDefinition( $this->postType );
		register_post_type( $this->postType, $definition->getArgs() );
	}

	public function showFilterOptions( $postType ): void {
		if ( $this->postType !== $postType ) {
			return;
		}

		$slug     = 'zoom-meeting';
		$taxonomy = get_taxonomy( $slug );
		$selected = $_REQUEST[ $slug ] ?? '';
		wp_dropdown_categories( array(
			'show_option_all' => $taxonomy->labels->all_items,
			'taxonomy'        => $slug,
			'name'            => $slug,
			'orderby'         => 'name',
			'value_field'     => 'slug',
			'selected'        => $selected,
			'hierarchical'    => true,
			'hide_if_empty'   => true,
		) );
	}

}