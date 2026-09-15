<?php

namespace Codemanas\VczApi\Admin\Foundation\PostType;

class CustomPostType {

	private string $postType;

	public function __construct( string $postType ) {
		$this->postType = $postType;
	}

	public function registerPostType(): void {
		$definition = new ZoomPostTypeDefinition( $this->postType );
		register_post_type( $this->postType, $definition->getArgs() );
	}

	public function hidePostType(): void {
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] !== $this->postType ) {
			return;
		}

		if ( ! vczapi_is_zoom_activated() ) {
			global $submenu;
			unset( $submenu['edit.php?post_type=zoom-meetings'][10] );
			unset( $submenu['edit.php?post_type=zoom-meetings'][15] );
		}
	}
}