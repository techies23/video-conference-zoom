<?php

use Codemanas\VczApi\Data\Logger;

/**
 * Registering the Pages Here
 *
 * @since   2.0.0
 * @author  Deepen
 */
class Zoom_Video_Conferencing_Admin_Views {

	public static $message = '';
	//either error warning or success
	public static $messageType = 'error';
	public static $isDismissible = true;
	public $settings;

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'zoom_video_conference_menus' ] );
		add_action( 'admin_init', [ $this, 'zoomConnectHandler' ] );
		add_action( 'admin_notices', [ $this, 'maybeShowMessage' ] );
		$this->migration_notice();
	}

	/**
	 * @return void
	 * @since 4.0.0
	 *        Show migration notice if JWT keys are still being shown
	 */
	public function migration_notice() {
		$is_jwt_active   = vczapi_is_jwt_active();
		$is_oauth_active = vczapi_is_oauth_active();
		$is_sdk_active   = vczapi_is_sdk_enabled();

		$sdk_not_active_notice_dismissed = get_option( 'vczapi_dismiss_sdk_not_active_notice' );

		self::$isDismissible = true;
		self::$messageType   = 'error';

		if ( ( $is_oauth_active && ! $is_sdk_active ) && ! $sdk_not_active_notice_dismissed ) {
			$admin_page_url  = esc_url( add_query_arg( [
				'post_type' => 'zoom-meetings',
				'page'      => 'zoom-video-conferencing-settings',
			],
				admin_url( 'edit.php' ),
			) );
			$admin_page_link = '<a href="' . $admin_page_url . '">here</a>';

			$dismiss_button = '<a href="#" class="vczapi-dismiss-admin-notice" data-id="vczapi_dismiss_sdk_not_active_notice" data-security="' . wp_create_nonce( 'vczapi-dismiss-nonce' ) . '" >don\'t show this message again</a>.';
			self::$message  = '<strong>Video Conferencing Zoom: </strong>' . sprintf( __( 'The SDK App credentials have not been added, without SDK app credentials - Join via Browser functionality will not work, to add SDK app credentials click %s. If you understand and don\'t want the to see this message click %s' ), $admin_page_link, $dismiss_button );
			wp_enqueue_script( 'video-conferencing-with-zoom-api-js' );

			return;
		} elseif ( ! apply_filters( 'vczapi_show_jwt_keys', ( $is_jwt_active ) ) || $sdk_not_active_notice_dismissed ) {
			return;
		}

		$depreciationLink = '<a href="' . esc_url( 'https://marketplace.zoom.us/docs/guides/build/jwt-app/jwt-faq/#jwt-app-type-deprecation-faq--omit-in-toc-' ) . '"
target="_blank" rel="noreferrer noopener">' . __( 'JWT App Type Depreciation FAQ', 'video-conferencing-with-zoom-api' ) . '</a>';

		$migration_wizard_url  = esc_url( add_query_arg(
			[
				'post_type' => 'zoom-meetings',
				'page'      => 'zoom-video-conferencing-settings',
				'migrate'   => 'now',
			],
			admin_url( 'edit.php' )
		) );
		$migration_wizard_link = '<a href="' . $migration_wizard_url . '">migration wizard</a>';
		$message               = sprintf( __( 'Zoom is deprecating their JWT app from June of 2023, please see %s for more details, Until the deadline all your current settings will work, however to ensure a smooth transition to the new Server to Server OAuth system + New App SDK (required for Join Via Browser) - we recommend that you migrate as soon as possible. Run the %s now to complete the migration process in 2 easy steps ', 'video-conferencing-with-zoom-api' ), $depreciationLink, $migration_wizard_link );


		self::$message = $message;
	}

	/**
	 * @return void
	 * @since 4.0.0
	 *        Show message if any messages are active
	 *        hooked admin_notices
	 */
	public function maybeShowMessage() {
		if ( empty( self::$message ) ) {
			return;
		}
		$message_classes   = [
			'success' => 'notice-success',
			'error'   => 'notice-error',
			'warning' => 'notice-warning',
		];
		$additionalClasses = $message_classes[ self::$messageType ] . ' ' . ( self::$isDismissible ? 'is-dismissible' : '' );
		?>
        <div class="vczapi-notice notice <?php _e( $additionalClasses ) ?>">
            <p><?php _e( self::$message ); ?></p>
        </div>
		<?php
	}

	/**
	 * @return void
	 * @since 4.0.0
	 *        Handles saving of Connection tab Zoom credentials
	 */
	public function zoomConnectHandler() {
		$nonce = filter_input( INPUT_POST, 'vczapi_zoom_connect_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		} elseif ( ! wp_verify_nonce( $nonce, 'verify_vczapi_zoom_connect' ) ) {
			return;
		}

		$vczapi_oauth_account_id    = sanitize_text_field( filter_input( INPUT_POST, 'vczapi_oauth_account_id' ) );
		$vczapi_oauth_client_id     = sanitize_text_field( filter_input( INPUT_POST, 'vczapi_oauth_client_id' ) );
		$vczapi_oauth_client_secret = sanitize_text_field( filter_input( INPUT_POST, 'vczapi_oauth_client_secret' ) );
		$vczapi_sdk_key             = sanitize_text_field( filter_input( INPUT_POST, 'vczapi_sdk_key' ) );
		$vczapi_sdk_secret_key      = sanitize_text_field( filter_input( INPUT_POST, 'vczapi_sdk_secret_key' ) );
		$zoom_api_key               = sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_key' ) );
		$zoom_api_secret            = sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_secret' ) );
		$delete_jwt_keys            = sanitize_text_field( filter_input( INPUT_POST, 'vczapi-delete-jwt-keys' ) );


		//added for Oauth S2S
		update_option( 'vczapi_oauth_account_id', $vczapi_oauth_account_id );
		update_option( 'vczapi_oauth_client_id', $vczapi_oauth_client_id );
		update_option( 'vczapi_oauth_client_secret', $vczapi_oauth_client_secret );
		//sdk app credentials
		update_option( 'vczapi_sdk_key', $vczapi_sdk_key );
		update_option( 'vczapi_sdk_secret_key', $vczapi_sdk_secret_key );

		//jwt keys update
		update_option( 'zoom_api_key', $zoom_api_key );
		update_option( 'zoom_api_secret', $zoom_api_secret );

		$OAuth_access_token = \vczapi\S2SOAuth::get_instance()->generateAndSaveAccessToken( $vczapi_oauth_account_id, $vczapi_oauth_client_id, $vczapi_oauth_client_secret, );

		if ( is_wp_error( $OAuth_access_token ) ) {
			self::$message     = sprintf( __( 'Zoom Oauth Error Code: "%s"  -  %s ', 'video-conferencing-with-zoom-api' ), $OAuth_access_token->get_error_code(), $OAuth_access_token->get_error_message() );
			self::$messageType = 'error';

			video_conferencing_zoom_api_delete_user_cache();
			delete_option( 'vczapi_global_oauth_data' );
			//error has not been displayed yet.
		} elseif ( $delete_jwt_keys == 'on' ) {
			delete_option( 'zoom_api_key' );
			delete_option( 'zoom_api_secret' );
		} else {
			//probably need a helper function or code to save keys on save differently
			$decoded_users = json_decode( zoom_conference()->listUsers() );
			if ( ! empty( $decoded_users->code ) ) {
				if ( is_admin() ) {
					add_action( 'admin_notices', 'vczapi_check_connection_error' );
				}
			} else {
				$users = ! empty( $decoded_users->users ) ? $decoded_users->users : false;
				vczapi_set_cache( '_zvc_user_lists', $users, 108000 );
			}
			//vczapi_set_cache( '_zvc_user_lists', $users, 108000 );
			self::$message     = __( 'Zoom: Credentials successfully verified and saved ', 'video-conferencing-with-zoom-api' );
			self::$messageType = 'success';
			video_conferencing_zoom_api_get_user_transients();
		}
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
		if ( vczapi_is_zoom_activated() ) {
			if ( apply_filters( 'vczapi_show_live_meetings', true ) ) {
				add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Live Webinars', 'video-conferencing-with-zoom-api' ), __( 'Live Webinars', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-webinars', array(
					'Zoom_Video_Conferencing_Admin_Webinars',
					'list_webinars',
				) );

				add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Live Meetings', 'video-conferencing-with-zoom-api' ), __( 'Live Meetings', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing', array(
					'Zoom_Video_Conferencing_Admin_Meetings',
					'list_meetings',
				) );

				add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Add Live Meeting', 'video-conferencing-with-zoom-api' ), __( 'Add Live Meeting', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-add-meeting', array(
					'Zoom_Video_Conferencing_Admin_Meetings',
					'add_meeting',
				) );
			}

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Zoom Users', 'video-conferencing-with-zoom-api' ), __( 'Zoom Users', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-list-users', array(
				'Zoom_Video_Conferencing_Admin_Users',
				'list_users',
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', 'Add Users', __( 'Add Users', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-add-users', array(
				'Zoom_Video_Conferencing_Admin_Users',
				'add_zoom_users',
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Reports', 'video-conferencing-with-zoom-api' ), __( 'Reports', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-reports', array(
				'Zoom_Video_Conferencing_Reports',
				'zoom_reports',
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Recordings', 'video-conferencing-with-zoom-api' ), __( 'Recordings', 'video-conferencing-with-zoom-api' ), apply_filters( 'vczapi_admin_settings_capabilities', 'edit_published_posts' ), 'zoom-video-conferencing-recordings', array(
				'Zoom_Video_Conferencing_Recordings',
				'zoom_recordings',
			) );

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Extensions', 'video-conferencing-with-zoom-api' ), __( 'Extensions', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-addons', array(
				'Zoom_Video_Conferencing_Admin_Addons',
				'render',
			) );

			//Only for developers or PRO version. So this is hidden !
			if ( defined( 'VIDEO_CONFERENCING_HOST_ASSIGN_PAGE' ) ) {
				add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Host to WP Users', 'video-conferencing-with-zoom-api' ), __( 'Host to WP Users', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-host-id-assign', array(
					'Zoom_Video_Conferencing_Admin_Users',
					'assign_host_id',
				) );
			}

			add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Import', 'video-conferencing-with-zoom-api' ), __( 'Import', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-sync', array(
				'Zoom_Video_Conferencing_Admin_Sync',
				'render',
			) );
		}

		add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'Settings', 'video-conferencing-with-zoom-api' ), __( 'Settings', 'video-conferencing-with-zoom-api' ), 'manage_options', 'zoom-video-conferencing-settings', array(
			$this,
			'zoom_video_conference_api_zoom_settings',
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

		$tab        = filter_input( INPUT_GET, 'tab' );
		$active_tab = $tab ?? 'connect';
		?>
        <div class="wrap">
            <h1><?php _e( 'Zoom Integration Settings', 'video-conferencing-with-zoom-api' ); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_url( add_query_arg(
					[
						'post_type' => 'zoom-meetings',
						'page'      => 'zoom-video-conferencing-settings',
					],
					admin_url( 'edit.php' )
				) ); ?>"
                   class="nav-tab <?php echo ( 'connect' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
					<?php esc_html_e( 'Connect', 'video-conferencing-with-zoom-api' ); ?>
                </a>
                <a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'api-settings' ) ) ); ?>"
                   class="nav-tab <?php echo ( 'api-settings' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
					<?php esc_html_e( 'Settings', 'video-conferencing-with-zoom-api' ); ?>
                </a>
                <a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'support' ) ) ); ?>"
                   class="nav-tab <?php echo ( 'support' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
					<?php esc_html_e( 'Support', 'video-conferencing-with-zoom-api' ); ?>
                </a>
                <a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'debug' ) ) ); ?>"
                   class="nav-tab <?php echo ( 'debug' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
					<?php esc_html_e( 'Logs', 'video-conferencing-with-zoom-api' ); ?>
                </a>
				<?php do_action( 'vczapi_admin_tabs_heading', $active_tab ); ?>
            </h2>
			<?php
			do_action( 'vczapi_admin_tabs_content', $active_tab );

			if ( 'connect' === $active_tab ) {
				//Defining Varaibles
				$vczapi_oauth_account_id    = get_option( 'vczapi_oauth_account_id' );
				$vczapi_oauth_client_id     = get_option( 'vczapi_oauth_client_id' );
				$vczapi_oauth_client_secret = get_option( 'vczapi_oauth_client_secret' );
				//app sdk
				$vczapi_sdk_key        = get_option( 'vczapi_sdk_key' );
				$vczapi_sdk_secret_key = get_option( 'vczapi_sdk_secret_key' );

				$zoom_api_key    = get_option( 'zoom_api_key' );
				$zoom_api_secret = get_option( 'zoom_api_secret' );
				require_once ZVC_PLUGIN_VIEWS_PATH . '/tabs/connect.php';
			} elseif ( 'api-settings' === $active_tab ) {
				if ( isset( $_POST['save_zoom_settings'] ) ) {
					//Nonce
					check_admin_referer( '_zoom_settings_update_nonce_action', '_zoom_settings_nonce' );

					$posted_data = [
						'vanity_url'                         => esc_url_raw( filter_input( INPUT_POST, 'vanity_url' ) ),
						'delete_zoom_meeting'                => filter_input( INPUT_POST, 'donot_delete_zom_meeting_also' ),
						'join_links'                         => filter_input( INPUT_POST, 'meeting_end_join_link' ),
						'zoom_author_show'                   => filter_input( INPUT_POST, 'meeting_show_zoom_author_original' ),
						'disable_countdown_timer'            => filter_input( INPUT_POST, 'disable_countdown_timer' ),
						'going_to_start'                     => sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_meeting_goingtostart_text' ) ),
						'ended_mtg'                          => sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_meeting_ended_text' ) ),
						'locale_format'                      => sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_date_time_format' ) ),
						'custom_date_time_format'            => sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_custom_date_time_format' ) ),
						'twentyfour_format'                  => sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_twenty_fourhour_format' ) ),
						'full_month_format'                  => sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_full_month_format' ) ),
						'embed_pwd_in_join_link'             => sanitize_text_field( filter_input( INPUT_POST, 'embed_password_join_link' ) ),
						'hide_join_links_non_loggedin_users' => sanitize_text_field( filter_input( INPUT_POST, 'hide_join_links_non_loggedin_users' ) ),
						'hide_email_jvb'                     => sanitize_text_field( filter_input( INPUT_POST, 'meeting_show_email_field' ) ),
						'vczapi_disable_invite'              => sanitize_text_field( filter_input( INPUT_POST, 'vczapi_disable_invite' ) ),
						'disable_join_via_browser'           => sanitize_text_field( filter_input( INPUT_POST, 'meeting_disable_join_via_browser' ) ),
						'join_via_browser_default_lang'      => sanitize_text_field( filter_input( INPUT_POST, 'meeting-lang' ) ),
						'disable_auto_pwd_generation'        => sanitize_text_field( filter_input( INPUT_POST, 'disable_auto_pwd_generation' ) ),
						'debugger_logs'                      => sanitize_text_field( filter_input( INPUT_POST, 'zoom_api_debugger_logs' ) ),
						'enable_direct_join_via_browser'     => sanitize_text_field( filter_input( INPUT_POST, 'vczapi_enable_direct_join' ) )
					];

					/**
					 * New way to save
					 * @added in 4.1.0
					 */
					update_option( '_vczapi_zoom_settings', $posted_data );

					//Legacy Approach - To be removed soon
					update_option( 'zoom_vanity_url', $posted_data['vanity_url'] );
					update_option( 'zoom_api_donot_delete_on_zoom', $posted_data['delete_zoom_meeting'] );
					update_option( 'zoom_past_join_links', $posted_data['join_links'] );
					update_option( 'zoom_show_author', $posted_data['zoom_author_show'] );
					update_option( 'zoom_going_tostart_meeting_text', $posted_data['going_to_start'] );
					update_option( 'zoom_ended_meeting_text', $posted_data['ended_mtg'] );
					update_option( 'zoom_api_date_time_format', $posted_data['locale_format'] );
					update_option( 'zoom_api_custom_date_time_format', $posted_data['custom_date_time_format'] );
					update_option( 'zoom_api_full_month_format', $posted_data['full_month_format'] );
					update_option( 'zoom_api_twenty_fourhour_format', $posted_data['twentyfour_format'] );
					update_option( 'zoom_api_embed_pwd_join_link', $posted_data['embed_pwd_in_join_link'] );
					update_option( 'zoom_api_hide_shortcode_join_links', $posted_data['hide_join_links_non_loggedin_users'] );
					update_option( 'zoom_api_hide_in_jvb', $posted_data['hide_email_jvb'] );
					update_option( 'vczapi_disable_invite', $posted_data['vczapi_disable_invite'] );
					update_option( 'zoom_api_disable_jvb', $posted_data['disable_join_via_browser'] );
					update_option( 'zoom_api_default_lang_jvb', $posted_data['join_via_browser_default_lang'] );
					update_option( 'zoom_api_disable_auto_meeting_pwd', $posted_data['disable_auto_pwd_generation'] );

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

				//Legacy Approach - To be removed soon
				$zoom_vanity_url             = get_option( 'zoom_vanity_url' );
				$past_join_links             = get_option( 'zoom_past_join_links' );
				$zoom_author_show            = get_option( 'zoom_show_author' );
				$zoom_going_to_start         = get_option( 'zoom_going_tostart_meeting_text' );
				$zoom_ended                  = get_option( 'zoom_ended_meeting_text' );
				$locale_format               = get_option( 'zoom_api_date_time_format' );
				$custom_date_time_format     = get_option( 'zoom_api_custom_date_time_format' );
				$twentyfour_format           = get_option( 'zoom_api_twenty_fourhour_format' );
				$full_month_format           = get_option( 'zoom_api_full_month_format' );
				$embed_password_join_link    = get_option( 'zoom_api_embed_pwd_join_link' );
				$hide_join_link_nloggedusers = get_option( 'zoom_api_hide_shortcode_join_links' );
				$hide_email_jvb              = get_option( 'zoom_api_hide_in_jvb' );
				$vczapi_disable_invite       = get_option( 'vczapi_disable_invite' );
				$disable_jvb                 = get_option( 'zoom_api_disable_jvb' );
				$default_jvb_lang            = get_option( 'zoom_api_default_lang_jvb' );
				$disable_auto_pwd_generation = get_option( 'zoom_api_disable_auto_meeting_pwd' );
				$donot_delete_zoom           = get_option( 'zoom_api_donot_delete_on_zoom' );
				$debug_logs                  = get_option( 'zoom_api_enable_debug_log' );

				/**
				 * New Method
				 * @added in 4.1.0
				 */
				$settings = get_option( '_vczapi_zoom_settings' );
				$settings = ! empty( $settings ) ? $settings : false;

				//Get Template
				require_once ZVC_PLUGIN_VIEWS_PATH . '/tabs/api-settings.php';
			} elseif ( 'support' == $active_tab ) {
				require_once ZVC_PLUGIN_VIEWS_PATH . '/tabs/support.php';
			} elseif ( 'debug' == $active_tab ) {
				$settings  = get_option( '_vczapi_zoom_settings' );
				$debug_log = ! empty( $settings['debugger_logs'] ) ? $settings['debugger_logs'] : false;
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

				require_once ZVC_PLUGIN_VIEWS_PATH . '/tabs/debug.php';
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
