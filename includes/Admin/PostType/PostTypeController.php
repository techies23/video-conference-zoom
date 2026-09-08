<?php

namespace Codemanas\VczApi\Admin\PostType;

class PostTypeController {

	private static ?PostTypeController $instance = null;

	public static function get_instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->init_components();
	}

	/**
	 * Instantiate sub-components via composition rather than inheritance.
	 */
	private function init_components(): void {
		new CustomPostType();
		new Metabox();
	}
}