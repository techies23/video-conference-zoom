<?php

namespace Codemanas\VczApi\Admin\Foundation\PostType;

use Codemanas\VczApi\Admin\Schema\MeetingFieldSchema;
use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\Templates;

class ZoomMetaboxViewRenderer {

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
		echo "<p>Enable this meeting to be purchased by your users ? </p><p>Check out <a href='" . esc_url( admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-addons' ) ) . "'>WooCommerce addon</a> for this plugin.</p>";
	}
}