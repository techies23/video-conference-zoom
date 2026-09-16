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

		if ( isset( $_GET['type'] ) && $_GET['type'] === "meeting" && isset( $_GET['join'] ) ) {
			$template = vczapi_get_template( 'join-web-browser.php' );
		} elseif ( wp_is_block_theme() ) {
			//use default WordPress for archive template otherwise the display gets broken.
			return $template;
		} else {
			$template = vczapi_get_template( 'archive-meetings.php' );
		}

		return $template;
	}

	/**
	 * Change Filter Name to Override Page Builders overridng join via browser window.
	 *
	 * @param $template_name
	 *
	 * @return string
	 */
	public function template_filter( $template_name ): string {
		if ( is_post_type_archive( $this->postType ) && isset( $_GET['type'] ) && $_GET['type'] === "meeting" && isset( $_GET['join'] ) ) {
			$template_name = vczapi_get_template( 'join-web-browser.php' );
		}

		return $template_name;
	}

}