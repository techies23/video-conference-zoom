<?php

namespace Codemanas\VczApi\Admin\Foundation\PostType;

class PostTypeTemplates {

	private string $postType;

	public function __construct( string $postType ) {
		$this->postType = $postType;
	}

	/**
	 * Single Page Template
	 *
	 * @param $template
	 *
	 * @return string
	 * @since  3.0.0
	 *
	 * @author Deepen
	 */
	public function single( $template ): string {
		global $post;

		if ( ! empty( $post ) && $post->post_type == $this->postType ) {
			$template = vczapi_get_single_or_zoom_template( $post, $template );
		}

		//Call before single template file is loaded
		do_action( 'vczapi_before_single_template_load' );

		return $template;
	}

	/**
	 * Archive page template
	 *
	 * @param $template
	 *
	 * @return bool|string
	 * @return bool|string|void
	 * @since  3.0.0
	 *
	 * @author Deepen
	 */
	public function archive( $template ): bool|string {
		if ( ! is_post_type_archive( $this->postType ) ) {
			return $template;
		}

		if ( self::is_legacy_join_request() ) {
			// A pre-4.9 archive join link. JoinViaBrowser::maybe_render() runs on
			// template_redirect at priority 1 and 301s this to the canonical
			// /zoom-join/<token>/ endpoint, so it never reaches this method.
			// Falling through to the archive is only a safety net.
			return $template;
		}

		if ( wp_is_block_theme() ) {
			//use default WordPress for archive template otherwise the display gets broken.
			return $template;
		}

		return vczapi_get_template( 'archive-meetings.php' );
	}

	/**
	 * Change Filter Name to Override Page Builders overridng join via browser window.
	 *
	 * @param $template_name
	 *
	 * @return string
	 */
	public function template_filter( $template_name ): string {
		return $template_name;
	}

	/**
	 * Whether the current request is a pre-4.9 `?type=meeting&join=` join link.
	 *
	 * Join links used to be served by hijacking `template_include` for both
	 * single posts and the archive. They now live on their own endpoint, so the
	 * only thing left to do with the legacy shape is recognise it: so
	 * `JoinViaBrowser` can migrate it, and so the cross-origin isolation headers
	 * the Web SDK needs are still applied to it.
	 *
	 * @return bool
	 */
	public static function is_legacy_join_request(): bool {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Detecting a public join URL; no state is read or written.
		if ( ! isset( $_GET['type'], $_GET['join'] ) ) {
			return false;
		}

		if ( 'meeting' !== $_GET['type'] || ! is_scalar( $_GET['join'] ) ) {
			return false;
		}

		if ( '' === trim( (string) $_GET['join'] ) ) {
			return false;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return is_singular( 'zoom-meetings' ) || is_post_type_archive( 'zoom-meetings' );
	}

}