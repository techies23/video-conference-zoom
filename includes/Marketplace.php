<?php

namespace Codemanas\VczApi;

class Marketplace {
// Single instance of the class
	private static $instance = null;

	// Constructor
	private function __construct() {
		add_filter( 'install_plugins_tabs', array( $this, 'add_custom_install_plugin_tab' ) );
		add_action( 'load-plugin-install.php', array( $this, 'handle_custom_install_plugin_tab_redirect' ) );
		add_action( 'admin_print_styles-plugin-install.php', array( $this, 'add_styles' ) );
	}

	// Get the single instance of the class
	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	// Add a custom tab to the install plugins page
	public function add_custom_install_plugin_tab( $tabs ) {
		$tabs['redirect_to_zoom_addons'] = __( 'Zoom', 'video-conferencing-with-zoom-api' );

		return $tabs;
	}

	// Handle the redirection when the custom tab is selected
	public function handle_custom_install_plugin_tab_redirect() {
		if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'redirect_to_zoom_addons' ) {
			// Get current site URL
			$site_url = get_site_url();
			// Construct the redirect URL
			$redirect_url = admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-addons' );
			// Redirect to the specified URL
			wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	public function add_styles() {
		?>
        <style>
            .plugin-install-redirect_to_zoom_addons > a::after {
                content: "";
                display: inline-block;
                background-image: url("data:image/svg+xml,%3Csvg width='16' height='16' viewBox='0 0 16 16' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M8.33321 3H12.9999V7.66667H11.9999V4.70711L8.02009 8.68689L7.31299 7.97978L11.2928 4H8.33321V3Z' fill='%23646970'/%3E%3Cpath d='M6.33333 4.1665H4.33333C3.8731 4.1665 3.5 4.5396 3.5 4.99984V11.6665C3.5 12.1267 3.8731 12.4998 4.33333 12.4998H11C11.4602 12.4998 11.8333 12.1267 11.8333 11.6665V9.6665' stroke='%23646970'/%3E%3C/svg%3E%0A");
                width: 16px;
                height: 16px;
                background-repeat: no-repeat;
                vertical-align: text-top;
                margin-left: 2px;
            }

            .plugin-install-redirect_to_zoom_addons:hover > a::after {
                background-image: url("data:image/svg+xml,%3Csvg width='16' height='16' viewBox='0 0 16 16' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M8.33321 3H12.9999V7.66667H11.9999V4.70711L8.02009 8.68689L7.31299 7.97978L11.2928 4H8.33321V3Z' fill='%23135E96'/%3E%3Cpath d='M6.33333 4.1665H4.33333C3.8731 4.1665 3.5 4.5396 3.5 4.99984V11.6665C3.5 12.1267 3.8731 12.4998 4.33333 12.4998H11C11.4602 12.4998 11.8333 12.1267 11.8333 11.6665V9.6665' stroke='%23135E96'/%3E%3C/svg%3E%0A");
            }
        </style>
		<?php
	}
}