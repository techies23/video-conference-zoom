<?php
namespace Codemanas\VczApi\Admin\Foundation\Settings;

use Codemanas\VczApi\Data\Logger;

class LogHandler {

	public function handle(): void {
		if ( empty( $_REQUEST['handle'] ) || empty( $_REQUEST['page'] ) || 'zoom-video-conferencing-settings' !== $_REQUEST['page'] ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$nonce = sanitize_key( $_REQUEST['_wpnonce'] ?? '' );
		if ( ! wp_verify_nonce( $nonce, 'remove_log' ) ) {
			wp_die( esc_html__( 'Action failed. Nonce verification failed.', 'video-conferencing-with-zoom-api' ) );
		}

		Logger::remove( sanitize_text_field( wp_unslash( $_REQUEST['handle'] ) ) );

		wp_safe_redirect( esc_url_raw( admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings&tab=debug' ) ) );
		exit;
	}
}