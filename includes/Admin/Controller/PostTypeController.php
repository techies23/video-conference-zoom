<?php

namespace Codemanas\VczApi\Admin\Controller;


use Codemanas\VczApi\Admin\Foundation\PostType\CustomPostType;
use Codemanas\VczApi\Admin\Foundation\PostType\PostTypeTemplates;
use Codemanas\VczApi\Admin\Foundation\PostType\Taxonomy;
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
		$taxonomy       = new Taxonomy( $this->postType );
		$templates      = new PostTypeTemplates( $this->postType );

		//Custom Post Type
		add_action( 'init', [ $customPostType, 'register' ] );
		add_action( 'restrict_manage_posts', [ $customPostType, 'showFilterOptions' ] );
		add_filter( 'manage_' . $this->postType . '_posts_columns', [ $customPostType, 'addColumns' ], 20 );
		add_action( 'manage_' . $this->postType . '_posts_custom_column', [ $customPostType, 'columnData' ], 20, 2 );
		add_action( 'manage_edit-' . $this->postType . '_sortable_columns', [ $customPostType, 'sortableData' ], 30 );
		add_filter( 'views_edit-' . $this->postType, [ $customPostType, 'addFiltersOnSubSubSub' ] );
		add_filter( 'pre_get_posts', [ $customPostType, 'filterPosts' ] );

		//Taxonomy
		add_action( 'init', [ $taxonomy, 'register' ] );

		//Metabox
		add_action( 'add_meta_boxes', [ $zoomMetabox, 'register' ] );

		//Templates
		add_filter( 'single_template', [ $templates, 'single' ], 20 );
		add_filter( 'archive_template', [ $templates, 'archive' ], 20 );
		add_filter( 'template_include', [ $templates, 'template_filter' ], 99 );
	}
}