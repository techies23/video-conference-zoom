<?php

namespace Codemanas\VczApi\Admin\PostType;

class PostTypeController {

	private static ?PostTypeController $instance = null;

	private Meetings $postType;
	protected static string $viewsPath;

	public static function get_instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		self::$viewsPath = ZVC_PLUGIN_ADMIN_VIEWS_PATH;

		$this->init_components();
		add_action( 'init', [ $this, 'registerPostType' ] );
	}

	/**
	 * Instantiate sub-components via composition rather than inheritance.
	 */
	private function init_components(): void {
		$this->postType = Meetings::get_instance();

		// Pass the views path or controller reference to Metabox
		new Metabox();
	}

	public function registerPostType(): void {
		$this->postType->registerPostType();
	}

	/**
	 * Helper method to render views safely.
	 */
	public static function renderView( string $template, array $args = [] ): void {
		$file = rtrim( self::$viewsPath, '/' ) . '/' . ltrim( $template, '/' );

		if ( file_exists( $file ) ) {
			extract( $args );
			require $file;
		}
	}
}