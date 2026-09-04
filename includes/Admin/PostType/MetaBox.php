<?php

namespace Codemanas\VczApi\Admin\PostType;

class Metabox {

	private string $post_type = 'zoom-meetings';

	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'register' ] );
	}

	private function getMetaboxConfig(): array {
		$config = [
			'zoom-meeting-meta'       => [
				'id'       => 'zoom-meeting-meta',
				'title'    => __( 'Zoom Details', 'video-conferencing-with-zoom-api' ),
				'callback' => [ $this, 'renderMetaBox' ],
				'context'  => 'normal',
				'priority' => 'default',
			],
			'zoom-meeting-meta-side'  => [
				'id'       => 'zoom-meeting-meta-side',
				'title'    => __( 'Meeting Details', 'video-conferencing-with-zoom-api' ),
				'callback' => [ $this, 'renderSideBox' ],
				'context'  => 'side',
				'priority' => 'high',
			],
			'zoom-meeting-debug-meta' => [
				'id'       => 'zoom-meeting-debug-meta',
				'title'    => __( 'Debug Log', 'video-conferencing-with-zoom-api' ),
				'callback' => [ $this, 'renderDebugBox' ],
				'context'  => 'normal',
				'priority' => 'default',
			],
		];

		if ( $this->shouldShowWooPreview() ) {
			$config['zoom-meeting-woo-integration-info'] = [
				'id'       => 'zoom-meeting-woo-integration-info',
				'title'    => __( 'WooCommerce Integration?', 'video-conferencing-with-zoom-api' ),
				'callback' => [ $this, 'renderWooSidebar' ],
				'context'  => 'side',
				'priority' => 'normal',
			];
		}

		return apply_filters( 'vczapi_admin_metabox_config', $config, $this->post_type );
	}

	public function register(): void {
		$metaboxes = $this->getMetaboxConfig();

		foreach ( $metaboxes as $box ) {
			add_meta_box(
				$box['id'],
				$box['title'],
				$box['callback'],
				$this->post_type,
				$box['context'] ?? 'normal',
				$box['priority'] ?? 'default'
			);
		}
	}

	/* -------------------------------------------------------------------------
	 * Render Callbacks
	 * ------------------------------------------------------------------------- */

	public function renderMetaBox( \WP_Post $post ): void {
		wp_nonce_field( '_zvc_meeting_save', '_zvc_nonce' );

		wp_enqueue_script( 'vczapi-flatpickr' );
		wp_enqueue_script( 'vczapi-choices' );
		PostTypeController::renderView( 'post-type/meta-box/tpl-meeting-fields.php', [
			'users'           => video_conferencing_zoom_api_get_user_transients(),
			'post'            => $post,
			'meeting_details' => get_post_meta( $post->ID, '_meeting_zoom_details', true ),
			'meeting_fields'  => get_post_meta( $post->ID, '_meeting_fields', true ),
		] );
	}

	public function renderSideBox( \WP_Post $post ): void {
		wp_nonce_field( '_zvc_meeting_save', '_zvc_nonce' );
		PostTypeController::renderView( 'post-type/meta-box/tpl-meeting-side-box.php', [
			'meeting_details' => get_post_meta( $post->ID, '_meeting_zoom_details', true ),
			'meeting_fields'  => get_post_meta( $post->ID, '_meeting_fields', true ),
		] );
	}

	public function renderDebugBox( \WP_Post $post ): void {
		PostTypeController::renderView( 'post-type/meta-box/tpl-meeting-debug.php', [
			'meeting_details' => get_post_meta( $post->ID, '_meeting_zoom_details', true ),
			'meeting_fields'  => get_post_meta( $post->ID, '_meeting_fields', true ),
		] );
	}

	public function renderWooSidebar(): void {
		echo "<p>Enable this meeting to be purchased by your users ? </p><p>Check out <a href='" . admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-addons' ) . "'>WooCommerce addon</a> for this plugin.</p>";
	}

	private function shouldShowWooPreview(): bool {
		if ( ! function_exists( 'is_plugin_inactive' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_inactive( 'vczapi-woo-addon/vczapi-woo-addon.php' ) && is_plugin_inactive( 'vczapi-woocommerce-addon/vczapi-woocommerce-addon.php' );
	}
}