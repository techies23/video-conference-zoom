<?php


namespace Codemanas\VczApi\Blocks;

use WP_Block_Template;

class BlockTemplates {
	public static ?BlockTemplates $instance = null;

	public static function get_instance(): ?BlockTemplates {
		return is_null( self::$instance ) ? self::$instance = new self() : self::$instance;
	}

	protected function __construct() {
		//used when saving /wp-includes/rest-api/endpoints/class-wp-rest-templates-controller.php used by block templates
		add_filter( 'pre_get_block_file_template', [ $this, 'get_templates' ], 10, 3 );
		add_filter( 'get_block_templates', [ $this, 'add_meetings_block_template' ], 10, 2 );
		//remove from other post types
		add_filter( 'allowed_block_types_all', [ $this, 'remove_template_blocks' ], 10, 2 );
	}

	public function remove_template_blocks( $allowed_block_types, $block_editor_context ) {
		//spectra plugin aka ultimate addons for gutenberg does not register blocks properly so they would be hidden
		if ( vczapi_is_plugin_active( 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php' ) || vczapi_is_plugin_active( 'meow-gallery/meow-gallery.php' ) ) {
			return $allowed_block_types;
		}
		$registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
		if ( $block_editor_context->name == 'core/edit-post' ) {
			unset( $registered_blocks['vczapi/single-zoom-meeting'] );

			return array_keys( $registered_blocks );
		}


		return $allowed_block_types;
	}

	public function add_meetings_block_template( $query_results, $query ) {

//		var_dump($query_results);

		$slugs = $query['slug__in'] ?? [];

		if ( ! is_admin() && ! empty( $slugs ) && ! in_array( 'single-zoom-meetings', $slugs ) ) {
			return $query_results;
		}

		$template_from_db = $this->get_template_from_db( $slugs );
		if ( $template_from_db !== null ) {
			$query_results[] = $template_from_db;

			return $query_results;
		}


		$query_results[] = $this->single_meeting_template();


		return $query_results;
	}

	private function get_template_from_db( $slugs ): ?WP_Block_Template {
		$template = null;
		//check if template is saved in DB and retrieve it from their
		$args = [
			'post_type' => 'wp_template',
			'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => [ 'vczapi' ],
				),
			),
		];


		if ( is_array( $slugs ) && count( $slugs ) > 0 ) {
			$args['post_name__in'] = $slugs;
		}

		$check_templates = new \WP_Query( $args );

		if ( $check_templates->found_posts > 0 ) {
			foreach ( $check_templates->posts as $post ) {
				$template = $this->create_template_from_db( $post );
				break;
			}
		}

		return $template;
	}

	private function create_template_from_db( $post ): WP_Block_Template {
		$terms = get_the_terms( $post, 'wp_theme' );
		$theme = $terms[0]->name;

		$template = new \WP_Block_Template();

		$template->wp_id          = $post->ID;
		$template->id             = $theme . '//' . $post->post_name;
		$template->theme          = $theme;
		$template->content        = $post->post_content;
		$template->slug           = $post->post_name;
		$template->source         = 'custom';
		$template->type           = $post->post_type;
		$template->description    = $post->post_excerpt;
		$template->title          = $post->post_title;
		$template->status         = $post->post_status;
		$template->has_theme_file = true;
		$template->is_custom      = false;
		$template->post_types     = array(); //
		$template->area           = 'uncategorized';

		return $template;
	}

	public function get_templates( $template, $id, $template_type ) {
		if ( $template_type != 'wp_template' ) {
			return $template;
		}

		$template_name_parts = explode( '//', $id );
		if ( count( $template_name_parts ) < 2 ) {
			return $template;
		}

		list( $template_id, $template_slug ) = $template_name_parts;

		if ( $template_id != 'vczapi' ) {
			return $template;
		}

		if ( $template_slug == 'single-zoom-meetings' ) {
			return $this->single_meeting_template();
		}

		return $template;
	}

	private function single_meeting_template(): WP_Block_Template {
		$template_content                     = '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->
<!-- wp:group {"layout":{"inherit":true}} -->
<div class="wp-block-group"><!-- wp:vczapi/single-zoom-meeting /--></div>
<!-- /wp:group -->
<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->';
		$modified_with_theme_template_content = '';
		$blocks                               = parse_blocks( $template_content );
		foreach ( $blocks as $block ) {
			if (
				'core/template-part' === $block['blockName'] &&
				! isset( $block['attrs']['theme'] )
			) {
				$block['attrs']['theme'] = wp_get_theme()->get_stylesheet();
			}
			$modified_with_theme_template_content .= serialize_block( $block );

		}

		$template        = new WP_Block_Template();
		$template->type  = 'wp_template';
		$template->theme = 'vczapi';
		$template->slug  = 'single-zoom-meetings';
		//id needs to be combination of $template->theme and $template->slug
		$template->id             = 'vczapi//single-zoom-meetings';
		$template->title          = 'Single Meeting';
		$template->content        = $modified_with_theme_template_content;
		$template->description    = 'Displays a single meeting';
		$template->source         = 'plugin';
		$template->origin         = 'plugin';
		$template->status         = 'publish';
		$template->has_theme_file = false;
		$template->is_custom      = false;
		$template->author         = null;
		$template->post_types     = [];
		$template->area           = 'uncategorized';

		return $template;
	}
}