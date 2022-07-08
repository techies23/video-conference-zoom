<?php

namespace Codemanas\VczApi\Backend;

/**
 * Class Common
 * @package Codemanas\VczApi\Backend
 */
class Common {

	private static $_instance = null;

	/**
	 * Create only one instance so that it may not Repeat
	 *
	 * @since 2.0.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		add_action( 'in_plugin_update_message-' . ZVC_PLUGIN_ABS_NAME, function ( $plugin_data ) {
			$this->version_update_warning( ZVC_PLUGIN_VERSION, $plugin_data['new_version'] );
		} );
	}

	/**
	 * Major Version Upgrade Notice
	 *
	 * @since 3.9.4
	 *
	 * @param $current_version
	 * @param $new_version
	 */
	public function version_update_warning( $current_version, $new_version ) {
		$current_version_minor_part = explode( '.', $current_version )[1];
		$new_version_minor_part     = explode( '.', $new_version )[1];

		if ( $current_version_minor_part === $new_version_minor_part ) {
			return;
		}
		?>
		<hr class="vczapi-major-update-warning__separator"/>
		<div class="vczapi-major-update-warning">
			<div class="vczapi-major-update-warning__icon">
				<span class="dashicons dashicons-info-outline"></span>
			</div>
			<div class="vczapi-major-update-warning_wrapper">
				<div class="vczapi-major-update-warning__title">
					<?php esc_html_e( 'Heads up, Please backup before upgrade!', 'video-conferencing-with-zoom-api' ); ?>
				</div>
				<div class="vczapi-major-update-warning__message">
					<?php
					esc_html_e( 'The latest update includes some substantial changes across different areas of the plugin. We highly recommend you backup your site before upgrading, and make sure you first update in a staging environment', 'video-conferencing-with-zoom-api' );
					?>
				</div>
			</div>
		</div>
		<?php
	}
}