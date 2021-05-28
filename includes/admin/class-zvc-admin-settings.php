<?php

/**
 * Registering the Pages Here
 *
 * @since   2.0.0
 * @author  Deepen
 */
class Zoom_Video_Conferencing_Admin_Views {

	public static $message = '';
	public $settings;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'zoom_video_conference_menus' ) );
	}

	/**
	 * Register Menus
	 *
	 * @since   1.0.0
	 * @updated 3.0.0
	 * @changes in CodeBase
	 * @author  Deepen Bajracharya <dpen.connectify@gmail.com>
	 */
	public function zoom_video_conference_menus() {
		if ( get_option( 'zoom_api_key' ) && get_option( 'zoom_api_secret' ) && video_conferencing_zoom_api_get_user_transients() ) {
			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Live Webinars', 'video-conferencing-with-zoom-api' ), __( 'Live Webinars', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-webinars', array(
				'Zoom_Video_Conferencing_Admin_Webinars',
				'list_webinars'
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Live Meetings', 'video-conferencing-with-zoom-api' ), __( 'Live Meetings', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing', array(
				'Zoom_Video_Conferencing_Admin_Meetings',
				'list_meetings'
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Add Live Meeting', 'video-conferencing-with-zoom-api' ), __( 'Add Live Meeting', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-add-meeting', array(
				'Zoom_Video_Conferencing_Admin_Meetings',
				'add_meeting'
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Zoom Users', 'video-conferencing-with-zoom-api' ), __( 'Zoom Users', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-list-users', array(
				'Zoom_Video_Conferencing_Admin_Users',
				'list_users'
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', 'Add Users', __( 'Add Users', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-add-users', array(
				'Zoom_Video_Conferencing_Admin_Users',
				'add_zoom_users'
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Reports', 'video-conferencing-with-zoom-api' ), __( 'Reports', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-reports', array(
				'Zoom_Video_Conferencing_Reports',
				'zoom_reports'
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Recordings', 'video-conferencing-with-zoom-api' ), __( 'Recordings', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-recordings', array(
				'Zoom_Video_Conferencing_Recordings',
				'zoom_recordings'
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Extensions', 'video-conferencing-with-zoom-api' ), __( 'Extensions', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-addons', array(
				'Zoom_Video_Conferencing_Admin_Addons',
				'render'
			) );

			//Only for developers or PRO version. So this is hidden !
			if ( defined( 'VIDEO_CONFERENCING_HOST_ASSIGN_PAGE' ) ) {
				add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Host to WP Users', 'video-conferencing-with-zoom-api' ), __( 'Host to WP Users', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-host-id-assign', array(
					'Zoom_Video_Conferencing_Admin_Users',
					'assign_host_id'
				) );
			}

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Import', 'video-conferencing-with-zoom-api' ), __( 'Import', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-sync', array(
				'Zoom_Video_Conferencing_Admin_Sync',
				'render'
			) );
		}

		add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Settings', 'video-conferencing-with-zoom-api' ), __( 'Settings', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-settings', array(
			$this,
			'zoom_video_conference_api_zoom_settings'
		) );
	}


	/**
	 * Zoom Settings View File
	 *
	 * @since   1.0.0
	 * @changes in CodeBase
	 * @author  Deepen Bajracharya <dpen.connectify@gmail.com>
	 */
	public function zoom_video_conference_api_zoom_settings() {
		wp_enqueue_script( 'video-conferencing-with-zoom-api-js' );
		wp_enqueue_style( 'video-conferencing-with-zoom-api' );

		video_conferencing_zoom_api_show_like_popup();

		$tab        = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
		$active_tab = isset( $tab ) ? $tab : 'api-settings';
		?>
        <div class="wrap">
            <h1><?php _e( 'Zoom Integration Settings', 'video-conferencing-with-zoom-api' ); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo add_query_arg( array( 'tab' => 'api-settings' ) ); ?>" class="nav-tab <?php echo ( 'api-settings' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
					<?php esc_html_e( 'API Settings', 'video-conferencing-with-zoom-api' ); ?>
                </a>
                <a style="background: #bf5252;color: #fff;" href="<?php echo add_query_arg( array( 'tab' => 'shortcode' ) ); ?>" class="nav-tab <?php echo ( 'shortcode' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
					<?php esc_html_e( 'Shortcode', 'video-conferencing-with-zoom-api' ); ?>
                </a>
                <a href="<?php echo add_query_arg( array( 'tab' => 'support' ) ); ?>" class="nav-tab <?php echo ( 'support' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
					<?php esc_html_e( 'Support', 'video-conferencing-with-zoom-api' ); ?>
                </a>
				<?php do_action( 'vczapi_admin_tabs_heading', $active_tab ); ?>
            </h2>
			<?php
			do_action( 'vczapi_admin_tabs_content', $active_tab );

			if ( 'api-settings' === $active_tab ) {
				if ( isset( $_POST['save_zoom_settings'] ) ) {
					//Nonce
					check_admin_referer( '_zoom_settings_update_nonce_action', '_zoom_settings_nonce' );
					$zoom_api_key                       = sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_key' ) );
					$zoom_api_secret                    = sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_secret' ) );
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

					update_option( 'zoom_api_key', $zoom_api_key );
					update_option( 'zoom_api_secret', $zoom_api_secret );
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
				$zoom_api_key                = get_option( 'zoom_api_key' );
				$zoom_api_secret             = get_option( 'zoom_api_secret' );
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

				//Get Template
				require_once ZVC_PLUGIN_VIEWS_PATH . '/tabs/api-settings.php';
			} else if ( 'shortcode' === $active_tab ) {
				require_once ZVC_PLUGIN_VIEWS_PATH . '/tabs/shortcode.php';
			} else if ( 'support' == $active_tab ) {
				require_once ZVC_PLUGIN_VIEWS_PATH . '/tabs/support.php';
			}
			?>
        </div>
		<?php
	}

	static function get_message() {
		return self::$message;
	}

	static function set_message( $class, $message ) {
		self::$message = '<div class=' . $class . '><p>' . $message . '</p></div>';
	}
}

new Zoom_Video_Conferencing_Admin_Views();
