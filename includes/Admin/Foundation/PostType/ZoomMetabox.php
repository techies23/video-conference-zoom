<?php

namespace Codemanas\VczApi\Admin\Foundation\PostType;

class ZoomMetabox {

	private string $postType;
	private ZoomMetaboxViewRenderer $renderer;

	public function __construct( string $postType ) {
		$this->postType = $postType;
		$this->renderer = new ZoomMetaboxViewRenderer();
	}

	private function getMetaboxConfig(): array {
		$config = [
			'vczapi-admin-meeting-fields'  => [
				'id'       => 'vczapi-admin-meeting-fields-meta',
				'title'    => __( 'Zoom Details', 'video-conferencing-with-zoom-api' ),
				'callback' => [ $this->renderer, 'renderMetaBox' ],
				'context'  => 'normal',
				'priority' => 'default',
			],
			'vczapi-admin-meeting-details' => [
				'id'       => 'vczapi-admin-meeting-details-meta',
				'title'    => __( 'Details', 'video-conferencing-with-zoom-api' ),
				'callback' => [ $this->renderer, 'renderSideBox' ],
				'context'  => 'side',
				'priority' => 'high',
			],
		];

		if ( $this->shouldShowWooPreview() ) {
			$config['zoom-meeting-woo-integration-info'] = [
				'id'       => 'zoom-meeting-woo-integration-info',
				'title'    => __( 'WooCommerce Integration?', 'video-conferencing-with-zoom-api' ),
				'callback' => [ $this->renderer, 'renderWooSidebar' ],
				'context'  => 'side',
				'priority' => 'normal',
			];
		}

		return apply_filters( 'vczapi_admin_metabox_config', $config, $this->postType );
	}

	public function register(): void {
		$metaboxes = $this->getMetaboxConfig();

		foreach ( $metaboxes as $box ) {
			add_meta_box(
				$box['id'],
				$box['title'],
				$box['callback'],
				$this->postType,
				$box['context'] ?? 'normal',
				$box['priority'] ?? 'default'
			);
		}
	}

	private function shouldShowWooPreview(): bool {
		if ( ! function_exists( 'is_plugin_inactive' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_inactive( 'vczapi-woo-addon/vczapi-woo-addon.php' ) && is_plugin_inactive( 'vczapi-woocommerce-addon/vczapi-woocommerce-addon.php' );
	}
}