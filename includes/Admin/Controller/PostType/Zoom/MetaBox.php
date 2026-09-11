<?php

namespace Codemanas\VczApi\Admin\Controller\PostType\Zoom;

use Codemanas\VczApi\Admin\Controller\PostType\Schema\MeetingFieldSchema;
use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\Config;
use Codemanas\VczApi\Helpers\Templates;

class Metabox {

	private string $postType;

	public function __construct() {
		$this->postType = Config::get( 'post_type' );
		add_action( 'add_meta_boxes', [ $this, 'register' ] );
	}

	private function getMetaboxConfig(): array {
		$config = [
			'vczapi-admin-meeting-fields'  => [
				'id'       => 'vczapi-admin-meeting-fields-meta',
				'title'    => __( 'Zoom Details', 'video-conferencing-with-zoom-api' ),
				'callback' => [ $this, 'renderMetaBox' ],
				'context'  => 'normal',
				'priority' => 'default',
			],
			'vczapi-admin-meeting-details' => [
				'id'       => 'vczapi-admin-meeting-details-meta',
				'title'    => __( 'Meeting Details', 'video-conferencing-with-zoom-api' ),
				'callback' => [ $this, 'renderSideBox' ],
				'context'  => 'side',
				'priority' => 'high',
			],
			'vczapi-admin-meeting-debug'   => [
				'id'       => 'vczapi-admin-meeting-debug-meta',
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

	/* -------------------------------------------------------------------------
	 * Render Callbacks
	 * ------------------------------------------------------------------------- */
	public function renderMetaBox( \WP_Post $post ): void {
		wp_nonce_field( 'vczapi_save_meeting_meta', '_vczapi_nonce' );

		wp_enqueue_script( 'vczapi-admin-editor' );
		wp_enqueue_script( 'vczapi-flatpickr' );
		wp_enqueue_script( 'vczapi-choices' );

		$meeting_fields  = Metastore::getPostMeta( $post->ID, 'meeting_fields' );
		$meeting_details = Metastore::getPostMeta( $post->ID, 'meeting_zoom_details' );
		$users           = video_conferencing_zoom_api_get_user_transients();

		Templates::includeFile( VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/post-type/meta-box/tpl-meeting-fields.php', [
			'post'            => $post,
			'meeting_details' => $meeting_details,
			'meeting_fields'  => is_array( $meeting_fields ) ? $meeting_fields : [],
			'field_sections'  => MeetingFieldSchema::getMeetingFieldsSchema( $post, $meeting_details, $users ),
		] );
	}

	public function renderSideBox( \WP_Post $post ): void {
		Templates::includeFile( VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/post-type/meta-box/tpl-meeting-side-box.php', [
			'meeting_details' => Metastore::getPostMeta( $post->ID, 'meeting_zoom_details' ),
			'meeting_fields'  => Metastore::getPostMeta( $post->ID, 'meeting_fields' ),
		] );
	}

	public function renderDebugBox( \WP_Post $post ): void {
		Templates::includeFile( VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/post-type/meta-box/tpl-meeting-debug.php', [
			'meeting_details' => Metastore::getPostMeta( $post->ID, 'meeting_zoom_details' ),
			'meeting_fields'  => Metastore::getPostMeta( $post->ID, 'meeting_fields' ),
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