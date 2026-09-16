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

		$taxnomy  = 'zoom-meeting';
		$taxonomy = get_taxonomy( $taxnomy );
		$selected = isset( $_REQUEST[ $taxnomy ] ) ? $_REQUEST[ $taxnomy ] : '';
		wp_dropdown_categories( array(
			'show_option_all' => $taxonomy->labels->all_items,
			'taxonomy'        => $taxnomy,
			'name'            => $taxnomy,
			'orderby'         => 'name',
			'value_field'     => 'slug',
			'selected'        => $selected,
			'hierarchical'    => true,
			'hide_if_empty'   => true,
		) );
	}

}