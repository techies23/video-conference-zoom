<?php

namespace Codemanas\VczApi\Blocks;

use WP_Block_Template;

class BlockTemplates {
	public static ?BlockTemplates $instance = null;

	/**
	 * Singleton instance retriever to ensure hooks are registered only once.
	 */
	public static function get_instance(): ?BlockTemplates {
		return is_null( self::$instance ) ? self::$instance = new self() : self::$instance;
	}

	protected function __construct() {
//		return;
		// STEP A: Intercept individual template requests from REST API or FSE template loader.
		// /wp-includes/rest-api/endpoints/class-wp-rest-templates-controller.php calls this.
		add_filter( 'pre_get_block_file_template', [ $this, 'get_templates' ], 10, 3 );

		// STEP B: Inject custom block template into the query list when WP queries block templates.
		add_filter( 'get_block_templates', [ $this, 'add_meetings_block_template' ], 10, 2 );

		// STEP C: Restrict certain blocks (e.g. single meeting block) from showing up in normal page editors.
		add_filter( 'allowed_block_types_all', [ $this, 'remove_template_blocks' ], 10, 2 );
	}

	/**
	 * Hides the 'vczapi/single-zoom-meeting' block in standard post/page editor screens
	 * so users only use it inside full-site editing templates.
	 */
	public function remove_template_blocks( $allowed_block_types, $block_editor_context ) {
		// Skip restricting blocks if specific block plugins are active that alter block registrations.
		if ( vczapi_is_plugin_active( 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php' ) || vczapi_is_plugin_active( 'meow-gallery/meow-gallery.php' ) ) {
			return $allowed_block_types;
		}

		// Get all registered block types from WordPress core registry.
		$registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();

		// If editing a standard post/page, remove single-zoom-meeting from available blocks.
		if ( $block_editor_context->name == 'core/edit-post' ) {
			unset( $registered_blocks['vczapi/single-zoom-meeting'] );

			return array_keys( $registered_blocks );
		}

		return $allowed_block_types;
	}

	/**
	 * RENDERING STEP 1:
	 * Hooked to `get_block_templates`. When WP queries block templates (e.g. Site Editor list view),
	 * this method appends our custom 'single-zoom-meetings' WP_Block_Template to the results array.
	 */
	public function add_meetings_block_template( $query_results, $query ) {
		$slugs = $query['slug__in'] ?? [];

		// If WordPress is querying specific slugs and single-zoom-meeting is NOT requested, skip.
		if ( ! empty( $slugs ) && ! in_array( 'single-zoom-meetings', $slugs ) && ! in_array( 'single-zoom-meeting', $slugs ) ) {
			return $query_results;
		}

		// Check if user edited and saved a customized version of this template in WP Database.
		$template_from_db = $this->get_template_from_db( $slugs );
		if ( $template_from_db !== null ) {
			$query_results[] = $template_from_db;

			return $query_results;
		}

		// Fallback: Serve default hardcoded Block Template provided by plugin.
		$query_results[] = $this->single_meeting_template();

		return $query_results;
	}

	/**
	 * Checks if a customized template post exists in the database under post_type 'wp_template'.
	 */
	private function get_template_from_db( $slugs ): ?WP_Block_Template {
		$template = null;

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

	/**
	 * Maps a `wp_template` CPT database post into a WP_Block_Template object.
	 */
	private function create_template_from_db( $post ): WP_Block_Template {
		$terms = get_the_terms( $post, 'wp_theme' );
		$theme = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0]->name : wp_get_theme()->get_stylesheet();

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
		$template->post_types     = array();
		$template->area           = 'uncategorized';

		return $template;
	}

	/**
	 * RENDERING STEP 2:
	 * Hooked to `pre_get_block_file_template`.
	 * When WordPress looks up a template file by ID (e.g. 'vczapi//single-zoom-meetings'),
	 * this filter bypasses the standard file lookup and supplies our virtual WP_Block_Template object.
	 */
	public function get_templates( $template, $id, $template_type ) {
		if ( $template_type != 'wp_template' ) {
			return $template;
		}

		// Expected ID structure: 'theme_or_namespace//template_slug' (e.g. 'vczapi//single-zoom-meetings')
		$template_name_parts = explode( '//', $id );
		if ( count( $template_name_parts ) < 2 ) {
			return $template;
		}

		list( $template_id, $template_slug ) = $template_name_parts;

		// Ensure request belongs to our plugin namespace.
		if ( $template_id != 'vczapi' ) {
			return $template;
		}

		if ( $template_slug == 'single-zoom-meetings' ) {
			// Check DB first for user edits, fallback to default template.
			$db_template = $this->get_template_from_db( [ $template_slug ] );

			return $db_template !== null ? $db_template : $this->single_meeting_template();
		}

		return $template;
	}

	/**
	 * Generates default WP_Block_Template containing block markup (Header + Zoom Block + Footer).
	 */
	private function single_meeting_template(): WP_Block_Template {
		// Hardcoded block markup layout.
		$template_content = '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->
<!-- wp:group {"layout":{"inherit":true}} -->
<div class="wp-block-group"><!-- wp:vczapi/single-zoom-meeting /--></div>
<!-- /wp:group -->
<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->';

		$modified_with_theme_template_content = '';
		$blocks                               = parse_blocks( $template_content );

		// Ensure theme-specific header/footer template parts load dynamically from active theme.
		foreach ( $blocks as $block ) {
			if (
				'core/template-part' === $block['blockName'] &&
				! isset( $block['attrs']['theme'] )
			) {
				$block['attrs']['theme'] = wp_get_theme()->get_stylesheet();
			}
			$modified_with_theme_template_content .= serialize_block( $block );
		}

		// Instantiate core WordPress WP_Block_Template object.
		$template                 = new WP_Block_Template();
		$template->type           = 'wp_template';
		$template->theme          = 'vczapi';
		$template->slug           = 'single-zoom-meetings';
		$template->id             = 'vczapi//single-zoom-meetings';
		$template->title          = 'Single Meeting';
		$template->content        = $modified_with_theme_template_content; // Gutenberg HTML structure
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