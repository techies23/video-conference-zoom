<?php


namespace Codemanas\VczApi\Blocks;

use WP_Block_Template;

class BlockTemplates {
	public static ?BlockTemplates $instance = null;

	public static function get_instance(): ?BlockTemplates {
		return is_null( self::$instance ) ? self::$instance = new self() : self::$instance;
	}

	protected function __construct() {
		add_filter( 'get_block_templates', [ $this, 'add_meetings_block_template' ], 10, 2 );
	}

	public function add_meetings_block_template( $query_results, $query ) {

		$slugs = $query['slug__in'] ?? [];

		if ( ! is_admin() && ! in_array( 'single-zoom-meetings', $slugs ) ) {
			return $query_results;
		}
		//test change
		$template                 = new WP_Block_Template();
		$template->type           = 'wp_template';
		$template->theme          = 'vczapi/vczapi';
		$template->slug           = 'single-zoom-meetings';
		$template->id             = 'vczapi/vczapi//single-zoom-meeting';
		$template->title          = 'Single Meeting';
		$template->content        = '<!-- wp:template-part {"slug":"header","tagName":"header","theme":"twentytwentythree"} /-->
<!-- wp:group {"layout":{"inherit":true}} -->
<div class="wp-block-group"><!-- wp:paragraph --><p>digthis is testing</p><!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- wp:template-part {"slug":"footer","tagName":"footer","theme":"twentytwentythree"} /-->';
		$template->description    = 'Displays a single meeting';
		$template->source         = 'plugin';
		$template->origin         = 'plugin';
		$template->status         = 'publish';
		$template->has_theme_file = true;
		$template->is_custom      = false;
		$template->author         = null;
		$template->post_types     = [];

		$query_results[] = $template;

		return $query_results;
	}

	public function default_content(): array {
		return array(
			array(
				'blockName' => 'core / paragraph',
				'attrs'     => array(
					'content' => 'This is the default content for the Zoom Meetings . ',
				),
			),
		);
	}
}