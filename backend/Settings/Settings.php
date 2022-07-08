<?php

namespace Codemanas\VczApi\Backend\Settings;

use Codemanas\VczApi\Includes\Api\OAuth;
use Codemanas\VczApi\Includes\Data\Logger;

/**
 * Registering the Pages Here
 *
 * @since   4.0.0
 * @author  Deepen
 */
class Settings {

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

	/**
	 * Zoom Settings View File
	 *
	 * @since   4.0.0
	 * @author  Deepen Bajracharya <dpen.connectify@gmail.com>
	 */
	public function render_settings() {
		wp_enqueue_script( 'video-conferencing-with-zoom-api-js' );
		wp_enqueue_style( 'video-conferencing-with-zoom-api' );

		video_conferencing_zoom_api_show_like_popup();

		$oauth = OAuth::instance();
		?>
        <div class="wrap">
			<?php
			if ( ! empty( $oauth->userData ) ) {
				$tab        = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
				$active_tab = isset( $tab ) ? $tab : 'api-connection';
				require_once VCZAPI_BACKEND_PATH . '/Settings/views/tabs.php';

				do_action( 'vczapi_admin_tabs_content', $active_tab );

				if ( 'api-connection' === $active_tab ) {
					$this->api_connection();
				} else if ( 'general' === $active_tab ) {
					$this->general_settings();
				} else if ( 'support' == $active_tab ) {
					require_once VCZAPI_BACKEND_PATH . '/Settings/views/support.php';
				} else if ( 'debug' == $active_tab ) {
					$this->debug();
				}

			} else {
				$this->api_connection();
			}
			?>
        </div>
		<?php
	}

	public function api_connection() {
		require_once VCZAPI_BACKEND_PATH . '/Settings/views/api-connection.php';
	}

	public function debug() {
		$debug_log = get_option( 'zoom_api_enable_debug_log' );
		$logs      = Logger::get_log_files();

		if ( ! empty( $_REQUEST['log_file'] ) && isset( $logs[ sanitize_title( wp_unslash( $_REQUEST['log_file'] ) ) ] ) ) {
			$viewed_log = $logs[ sanitize_title( wp_unslash( $_REQUEST['log_file'] ) ) ];
		} elseif ( ! empty( $logs ) ) {
			$viewed_log = current( $logs );
		}

		if ( ! empty( $_REQUEST['handle'] ) ) { // WPCS: input var ok, CSRF ok.
			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'remove_log' ) ) { // WPCS: input var ok, sanitization ok.
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'video-conferencing-with-zoom-api' ) );
			}

			if ( ! empty( $_REQUEST['handle'] ) ) {  // WPCS: input var ok.
				Logger::remove( wp_unslash( $_REQUEST['handle'] ) ); // WPCS: input var ok, sanitization ok.
			}

			wp_safe_redirect( esc_url_raw( admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings&tab=debug' ) ) );
			exit();
		}

		require_once VCZAPI_BACKEND_PATH . '/Settings/views/debug.php';
	}

	public function general_settings() {
		if ( isset( $_POST['save_zoom_settings'] ) ) {
			//Legacy Method
			check_admin_referer( '_zoom_settings_update_nonce_action', '_zoom_settings_nonce' );
			$vanity_url                         = esc_url_raw( filter_input( INPUT_POST, 'vanity_url' ) );
			$delete_zoom_meeting                = filter_input( INPUT_POST, 'donot_delete_zom_meeting_also' );
			$join_links                         = filter_input( INPUT_POST, 'meeting_end_join_link' );
			$zoom_author_show                   = filter_input( INPUT_POST, 'meeting_show_zoom_author_original' );
			$started_mtg                        = sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_meeting_started_text' ) );
			$going_to_start                     = sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_meeting_goingtostart_text' ) );
			$ended_mtg                          = sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_meeting_ended_text' ) );
			$locale_format                      = sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_date_time_format' ) );
			$custom_date_time_format            = sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_custom_date_time_format' ) );
			$twentyfour_format                  = sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_twenty_fourhour_format' ) );
			$full_month_format                  = sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_full_month_format' ) );
			$embed_pwd_in_join_link             = sanitize_text_field( filter_input( INPUT_POST, 'embed_password_join_link' ) );
			$hide_join_links_non_loggedin_users = sanitize_text_field( filter_input( INPUT_POST, 'hide_join_links_non_loggedin_users' ) );
			$hide_email_jvb                     = sanitize_text_field( filter_input( INPUT_POST, 'meeting_show_email_field' ) );
			$vczapi_disable_invite              = sanitize_text_field( filter_input( INPUT_POST, 'vczapi_disable_invite' ) );
			$disable_join_via_browser           = sanitize_text_field( filter_input( INPUT_POST, 'meeting_disable_join_via_browser' ) );
			$join_via_browser_default_lang      = sanitize_text_field( filter_input( INPUT_POST, 'meeting-lang' ) );
			$disable_auto_pwd_generation        = sanitize_text_field( filter_input( INPUT_POST, 'disable_auto_pwd_generation' ) );
			$debugger_logs                      = sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_debugger_logs' ) );

			update_option( 'zoom_vanity_url', $vanity_url );
			update_option( 'zoom_api_donot_delete_on_zoom', $delete_zoom_meeting );
			update_option( 'zoom_past_join_links', $join_links );
			update_option( 'zoom_show_author', $zoom_author_show );
			update_option( 'zoom_started_meeting_text', $started_mtg );
			update_option( 'zoom_going_tostart_meeting_text', $going_to_start );
			update_option( 'zoom_ended_meeting_text', $ended_mtg );
			update_option( 'zoom_api_date_time_format', $locale_format );
			update_option( 'zoom_api_custom_date_time_format', $custom_date_time_format );
			update_option( 'zoom_api_full_month_format', $full_month_format );
			update_option( 'zoom_api_twenty_fourhour_format', $twentyfour_format );
			update_option( 'zoom_api_embed_pwd_join_link', $embed_pwd_in_join_link );
			update_option( 'zoom_api_hide_shortcode_join_links', $hide_join_links_non_loggedin_users );
			update_option( 'zoom_api_hide_in_jvb', $hide_email_jvb );
			update_option( 'vczapi_disable_invite', $vczapi_disable_invite );
			update_option( 'zoom_api_disable_jvb', $disable_join_via_browser );
			update_option( 'zoom_api_default_lang_jvb', $join_via_browser_default_lang );
			update_option( 'zoom_api_disable_auto_meeting_pwd', $disable_auto_pwd_generation );
			update_option( 'zoom_api_enable_debug_log', $debugger_logs );

			//After user has been created delete this transient in order to fetch latest Data.
			video_conferencing_zoom_api_delete_user_cache();
			?>
            <div id="message" class="notice notice-success is-dismissible">
                <p><?php _e( 'Successfully Updated. Please refresh this page.', 'video-conferencing-with-zoom-api' ); ?></p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'video-conferencing-with-zoom-api' ); ?></span>
                </button>
            </div>
			<?php
		}

		//Defining Varaibles
		$zoom_vanity_url             = get_option( 'zoom_vanity_url' );
		$past_join_links             = get_option( 'zoom_past_join_links' );
		$zoom_author_show            = get_option( 'zoom_show_author' );
		$zoom_started                = get_option( 'zoom_started_meeting_text' );
		$zoom_going_to_start         = get_option( 'zoom_going_tostart_meeting_text' );
		$zoom_ended                  = get_option( 'zoom_ended_meeting_text' );
		$locale_format               = get_option( 'zoom_api_date_time_format' );
		$custom_date_time_format     = get_option( 'zoom_api_custom_date_time_format' );
		$twentyfour_format           = get_option( 'zoom_api_twenty_fourhour_format' );
		$full_month_format           = get_option( 'zoom_api_full_month_format' );
		$embed_password_join_link    = get_option( 'zoom_api_embed_pwd_join_link' );
		$embed_password_join_link    = get_option( 'zoom_api_embed_pwd_join_link' );
		$hide_join_link_nloggedusers = get_option( 'zoom_api_hide_shortcode_join_links' );
		$hide_email_jvb              = get_option( 'zoom_api_hide_in_jvb' );
		$vczapi_disable_invite       = get_option( 'vczapi_disable_invite' );
		$disable_jvb                 = get_option( 'zoom_api_disable_jvb' );
		$default_jvb_lang            = get_option( 'zoom_api_default_lang_jvb' );
		$disable_auto_pwd_generation = get_option( 'zoom_api_disable_auto_meeting_pwd' );
		$donot_delete_zoom           = get_option( 'zoom_api_donot_delete_on_zoom' );
		$debug_logs                  = get_option( 'zoom_api_enable_debug_log' );

		//Get Template
		require_once VCZAPI_BACKEND_PATH . '/Settings/views/general.php';
	}

	public function __construct() {
	}
}
