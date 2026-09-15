<?php

namespace Codemanas\VczApi\Admin\Controller;


use Codemanas\VczApi\Admin\Foundation\PostType\CustomPostType;
use Codemanas\VczApi\Admin\Foundation\PostType\ZoomMetabox;
use Codemanas\VczApi\Helpers\Config;

/**
 * Register the main post type.
 */
class PostTypeController {

	private static ?PostTypeController $instance = null;

	private string $postType;

	public static function get_instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->postType = Config::get( 'post_type' );

		$this->registerHooks();
	}


	private function registerHooks(): void {
		$customPostType = new CustomPostType( $this->postType );
		$zoomMetabox    = new ZoomMetabox( $this->postType );

		add_action( 'init', [ $customPostType, 'registerPostType' ] );
		add_action( 'add_meta_boxes', [ $zoomMetabox, 'register' ] );
	}
}