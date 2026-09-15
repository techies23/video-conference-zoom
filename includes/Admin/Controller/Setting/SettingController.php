<?php

namespace Codemanas\VczApi\Admin\Controller\Setting;

use Codemanas\VczApi\Data\Logger;

/**
 * Admin Settings Controller Class
 *
 * @since   2.0.0
 * @updated 4.7.0
 */
class SettingController {

    private static ?SettingController $instance = null;

    public const SETTINGS_OPTION_KEY = '_vczapi_zoom_settings';

    private static string $message = '';
    private static string $messageType = 'error';
    private static bool $isDismissible = true;

    public static function get_instance(): self {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct() {
        $this->register_hooks();
    }

    private function register_hooks(): void {
        add_action( 'admin_menu', [ $this, 'registerAdminMenus' ] );
        add_action( 'admin_init', [ $this, 'handleZoomConnectSave' ] );
        add_action( 'admin_init', [ $this, 'handleSettingsSave' ] );
        add_action( 'admin_init', [ $this, 'handleLogDeletion' ] );
        add_action( 'admin_notices', [ $this, 'maybeShowAdminNotices' ] );
    }

    /**
     * Retrieve options under unified key with legacy auto-migration fallback
     */
    public function getSettings(): array {
        $settings = get_option( self::SETTINGS_OPTION_KEY, null );

        if ( is_array( $settings ) ) {
            return $settings;
        }

        // Migration Routine: Fallback to legacy single keys if unified array is empty
        $legacy_mapping = [
                'vanity_url'                         => 'zoom_vanity_url',
                'delete_zoom_meeting'                => 'zoom_api_donot_delete_on_zoom',
                'join_links'                         => 'zoom_past_join_links',
                'zoom_author_show'                   => 'zoom_show_author',
                'going_to_start'                     => 'zoom_going_tostart_meeting_text',
                'ended_mtg'                          => 'zoom_ended_meeting_text',
                'locale_format'                      => 'zoom_api_date_time_format',
                'custom_date_time_format'            => 'zoom_api_custom_date_time_format',
                'full_month_format'                  => 'zoom_api_full_month_format',
                'twentyfour_format'                  => 'zoom_api_twenty_fourhour_format',
                'embed_pwd_in_join_link'             => 'zoom_api_embed_pwd_join_link',
                'hide_join_links_non_loggedin_users' => 'zoom_api_hide_shortcode_join_links',
                'hide_email_jvb'                     => 'zoom_api_hide_in_jvb',
                'vczapi_disable_invite'              => 'vczapi_disable_invite',
                'disable_join_via_browser'           => 'zoom_api_disable_jvb',
                'join_via_browser_default_lang'      => 'zoom_api_default_lang_jvb',
                'disable_auto_pwd_generation'        => 'zoom_api_disable_auto_meeting_pwd',
                'debugger_logs'                      => 'zoom_api_enable_debug_log',
        ];

        $migrated_settings = array_map( function ( $legacy_key ) {
            return get_option( $legacy_key, '' );
        }, $legacy_mapping );

        update_option( self::SETTINGS_OPTION_KEY, $migrated_settings );

        return $migrated_settings;
    }

    public function maybeShowAdminNotices(): void {
        if ( empty( self::$message ) ) {
            return;
        }

        $message_classes = [
                'success' => 'notice-success',
                'error'   => 'notice-error',
                'warning' => 'notice-warning',
        ];

        $class_type = $message_classes[ self::$messageType ] ?? 'notice-error';
        $classes    = sprintf( 'vczapi-notice notice %s %s', esc_attr( $class_type ), self::$isDismissible ? 'is-dismissible' : '' );

        printf( '<div class="%s"><p>%s</p></div>', $classes, wp_kses_post( self::$message ) );
    }

    public function handleZoomConnectSave(): void {
        if ( ! isset( $_POST['vczapi_zoom_connect_nonce'] ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( sanitize_key( $_POST['vczapi_zoom_connect_nonce'] ), 'verify_vczapi_zoom_connect' ) ) {
            return;
        }

        $credentials = [
                'vczapi_oauth_account_id'    => sanitize_text_field( $_POST['vczapi_oauth_account_id'] ?? '' ),
                'vczapi_oauth_client_id'     => sanitize_text_field( $_POST['vczapi_oauth_client_id'] ?? '' ),
                'vczapi_oauth_client_secret' => sanitize_text_field( $_POST['vczapi_oauth_client_secret'] ?? '' ),
                'vczapi_sdk_key'             => sanitize_text_field( $_POST['vczapi_sdk_key'] ?? '' ),
                'vczapi_sdk_secret_key'      => sanitize_text_field( $_POST['vczapi_sdk_secret_key'] ?? '' ),
        ];

        foreach ( $credentials as $option_name => $value ) {
            update_option( $option_name, $value );
        }

        $access_token = \vczapi\S2SOAuth::get_instance()->generateAndSaveAccessToken(
                $credentials['vczapi_oauth_account_id'],
                $credentials['vczapi_oauth_client_id'],
                $credentials['vczapi_oauth_client_secret']
        );

        if ( is_wp_error( $access_token ) ) {
            self::$message     = sprintf( esc_html__( 'Zoom OAuth Error Code: "%s" - %s', 'video-conferencing-with-zoom-api' ), esc_html( $access_token->get_error_code() ), esc_html( $access_token->get_error_message() ) );
            self::$messageType = 'error';

            video_conferencing_zoom_api_delete_user_cache();
            delete_option( 'vczapi_global_oauth_data' );

            return;
        }

        if ( 'on' === sanitize_text_field( $_POST['vczapi-delete-jwt-keys'] ?? '' ) ) {
            delete_option( 'zoom_api_key' );
            delete_option( 'zoom_api_secret' );
        }

        $decoded_users = json_decode( zoom_conference()->listUsers() );
        if ( ! empty( $decoded_users->code ) && is_admin() ) {
            add_action( 'admin_notices', 'vczapi_check_connection_error' );
        } else {
            vczapi_set_cache( '_zvc_user_lists', $decoded_users->users ?? false, 108000 );
        }

        self::$message     = __( 'Zoom: Credentials successfully verified and saved.', 'video-conferencing-with-zoom-api' );
        self::$messageType = 'success';
        video_conferencing_zoom_api_get_user_transients();
    }

    public function handleSettingsSave(): void {
        if ( ! isset( $_POST['save_zoom_settings'] ) ) {
            return;
        }

        check_admin_referer( '_zoom_settings_update_nonce_action', '_zoom_settings_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $field_mappings = [
                'vanity_url'                         => 'esc_url_raw',
                'delete_zoom_meeting'                => 'donot_delete_zom_meeting_also',
                'join_links'                         => 'meeting_end_join_link',
                'zoom_author_show'                   => 'meeting_show_zoom_author_original',
                'disable_countdown_timer'            => 'disable_countdown_timer',
                'going_to_start'                     => 'zoom_api_meeting_goingtostart_text',
                'ended_mtg'                          => 'zoom_api_meeting_ended_text',
                'locale_format'                      => 'zoom_api_date_time_format',
                'custom_date_time_format'            => 'zoom_api_custom_date_time_format',
                'twentyfour_format'                  => 'zoom_api_twenty_fourhour_format',
                'full_month_format'                  => 'zoom_api_full_month_format',
                'embed_pwd_in_join_link'             => 'embed_password_join_link',
                'hide_join_links_non_loggedin_users' => 'hide_join_links_non_loggedin_users',
                'hide_email_jvb'                     => 'meeting_show_email_field',
                'vczapi_disable_invite'              => 'vczapi_disable_invite',
                'disable_join_via_browser'           => 'meeting_disable_join_via_browser',
                'join_via_browser_default_lang'      => 'meeting-lang',
                'disable_auto_pwd_generation'        => 'disable_auto_pwd_generation',
                'debugger_logs'                      => 'zoom_api_debugger_logs',
                'enable_direct_join_via_browser'     => 'vczapi_enable_direct_join',
        ];

        $posted_data = [];
        foreach ( $field_mappings as $key => $post_field ) {
            if ( 'esc_url_raw' === $post_field ) {
                $posted_data[ $key ] = esc_url_raw( $_POST['vanity_url'] ?? '' );
            } else {
                $posted_data[ $key ] = sanitize_text_field( $_POST[ $post_field ] ?? '' );
            }
        }

        // Batch update to unified setting key
        update_option( self::SETTINGS_OPTION_KEY, $posted_data );
        video_conferencing_zoom_api_delete_user_cache();

        self::$message     = __( 'Settings successfully updated.', 'video-conferencing-with-zoom-api' );
        self::$messageType = 'success';
    }

    public function handleLogDeletion(): void {
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

    /**
     * Optimized loop registration for submenu pages
     */
    public function registerAdminMenus(): void {
        $parent_slug = 'edit.php?post_type=zoom-meetings';

        if ( vczapi_is_zoom_activated() ) {
            $submenus = [];

            if ( apply_filters( 'vczapi_show_live_meetings', true ) ) {
                $submenus[] = [ 'page_title' => __( 'Live Webinars', 'video-conferencing-with-zoom-api' ), 'menu_title' => __( 'Live Webinars', 'video-conferencing-with-zoom-api' ), 'capability' => 'manage_options', 'menu_slug' => 'zoom-video-conferencing-webinars', 'callback' => [ 'Zoom_Video_Conferencing_Admin_Webinars', 'list_webinars' ] ];
                $submenus[] = [ 'page_title' => __( 'Live Meetings', 'video-conferencing-with-zoom-api' ), 'menu_title' => __( 'Live Meetings', 'video-conferencing-with-zoom-api' ), 'capability' => 'manage_options', 'menu_slug' => 'zoom-video-conferencing', 'callback' => [ 'Zoom_Video_Conferencing_Admin_Meetings', 'list_meetings' ] ];
                $submenus[] = [ 'page_title' => __( 'Add Live Meeting', 'video-conferencing-with-zoom-api' ), 'menu_title' => __( 'Add Live Meeting', 'video-conferencing-with-zoom-api' ), 'capability' => 'manage_options', 'menu_slug' => 'zoom-video-conferencing-add-meeting', 'callback' => [ 'Zoom_Video_Conferencing_Admin_Meetings', 'add_meeting' ] ];
            }

            $submenus[] = [ 'page_title' => __( 'Zoom Users', 'video-conferencing-with-zoom-api' ), 'menu_title' => __( 'Zoom Users', 'video-conferencing-with-zoom-api' ), 'capability' => 'manage_options', 'menu_slug' => 'zoom-video-conferencing-list-users', 'callback' => [ 'Zoom_Video_Conferencing_Admin_Users', 'list_users' ] ];
            $submenus[] = [ 'page_title' => __( 'Add Users', 'video-conferencing-with-zoom-api' ), 'menu_title' => __( 'Add Users', 'video-conferencing-with-zoom-api' ), 'capability' => 'manage_options', 'menu_slug' => 'zoom-video-conferencing-add-users', 'callback' => [ 'Zoom_Video_Conferencing_Admin_Users', 'add_zoom_users' ] ];
            $submenus[] = [ 'page_title' => __( 'Reports', 'video-conferencing-with-zoom-api' ), 'menu_title' => __( 'Reports', 'video-conferencing-with-zoom-api' ), 'capability' => 'manage_options', 'menu_slug' => 'zoom-video-conferencing-reports', 'callback' => [ 'Zoom_Video_Conferencing_Reports', 'zoom_reports' ] ];
            $submenus[] = [ 'page_title' => __( 'Recordings', 'video-conferencing-with-zoom-api' ), 'menu_title' => __( 'Recordings', 'video-conferencing-with-zoom-api' ), 'capability' => apply_filters( 'vczapi_admin_settings_capabilities', 'edit_published_posts' ), 'menu_slug' => 'zoom-video-conferencing-recordings', 'callback' => [ 'Zoom_Video_Conferencing_Recordings', 'zoom_recordings' ] ];
            $submenus[] = [ 'page_title' => __( 'Extensions', 'video-conferencing-with-zoom-api' ), 'menu_title' => __( 'Extensions', 'video-conferencing-with-zoom-api' ), 'capability' => 'manage_options', 'menu_slug' => 'zoom-video-conferencing-addons', 'callback' => [ 'Zoom_Video_Conferencing_Admin_Addons', 'render' ] ];

            if ( defined( 'VIDEO_CONFERENCING_HOST_ASSIGN_PAGE' ) ) {
                $submenus[] = [ 'page_title' => __( 'Host to WP Users', 'video-conferencing-with-zoom-api' ), 'menu_title' => __( 'Host to WP Users', 'video-conferencing-with-zoom-api' ), 'capability' => 'manage_options', 'menu_slug' => 'zoom-video-conferencing-host-id-assign', 'callback' => [ 'Zoom_Video_Conferencing_Admin_Users', 'assign_host_id' ] ];
            }

            $submenus[] = [ 'page_title' => __( 'Import', 'video-conferencing-with-zoom-api' ), 'menu_title' => __( 'Import', 'video-conferencing-with-zoom-api' ), 'capability' => 'manage_options', 'menu_slug' => 'zoom-video-conferencing-sync', 'callback' => [ 'Zoom_Video_Conferencing_Admin_Sync', 'render' ] ];

            $this->register_submenu_pages( $parent_slug, $submenus );
        }

        add_submenu_page(
                $parent_slug,
                __( 'Settings', 'video-conferencing-with-zoom-api' ),
                __( 'Settings', 'video-conferencing-with-zoom-api' ),
                'manage_options',
                'zoom-video-conferencing-settings',
                [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Helper loop for adding submenu items
     */
    private function register_submenu_pages( string $parent_slug, array $submenus ): void {
        foreach ( $submenus as $menu ) {
            add_submenu_page(
                    $parent_slug,
                    $menu['page_title'],
                    $menu['menu_title'],
                    $menu['capability'],
                    $menu['menu_slug'],
                    $menu['callback']
            );
        }
    }

    public function render_settings_page(): void {
        wp_enqueue_script( 'video-conferencing-with-zoom-api-js' );
        wp_enqueue_style( 'video-conferencing-with-zoom-api' );

        video_conferencing_zoom_api_show_like_popup();

        $active_tab = sanitize_key( $_GET['tab'] ?? 'connect' );
        $tabs       = [
                'connect'      => __( 'Connect', 'video-conferencing-with-zoom-api' ),
                'api-settings' => __( 'Settings', 'video-conferencing-with-zoom-api' ),
                'support'      => __( 'Support', 'video-conferencing-with-zoom-api' ),
                'debug'        => __( 'Logs', 'video-conferencing-with-zoom-api' ),
        ];
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Zoom Integration Settings', 'video-conferencing-with-zoom-api' ); ?></h1>
            <h2 class="nav-tab-wrapper">
                <?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( [ 'post_type' => 'zoom-meetings', 'page' => 'zoom-video-conferencing-settings', 'tab' => $tab_key ], admin_url( 'edit.php' ) ) ); ?>"
                       class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html( $tab_label ); ?>
                    </a>
                <?php endforeach; ?>
                <?php do_action( 'vczapi_admin_tabs_heading', $active_tab ); ?>
            </h2>

            <?php
            do_action( 'vczapi_admin_tabs_content', $active_tab );
            $this->render_tab_content( $active_tab );
            ?>
        </div>
        <?php
    }

    private function render_tab_content( string $active_tab ): void {
        switch ( $active_tab ) {
            case 'connect':
                $vczapi_oauth_account_id    = get_option( 'vczapi_oauth_account_id' );
                $vczapi_oauth_client_id     = get_option( 'vczapi_oauth_client_id' );
                $vczapi_oauth_client_secret = get_option( 'vczapi_oauth_client_secret' );
                $vczapi_sdk_key             = get_option( 'vczapi_sdk_key' );
                $vczapi_sdk_secret_key      = get_option( 'vczapi_sdk_secret_key' );

                require_once VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/settings/connect.php';
                break;

            case 'api-settings':
                $settings = $this->getSettings();
                require_once ZVC_PLUGIN_VIEWS_PATH . '/tabs/api-settings.php';
                break;

            case 'support':
                require_once ZVC_PLUGIN_VIEWS_PATH . '/tabs/support.php';
                break;

            case 'debug':
                $settings  = $this->getSettings();
                $debug_log = $settings['debugger_logs'] ?? false;
                $logs      = Logger::get_log_files();

                $requested_log = sanitize_title( wp_unslash( $_REQUEST['log_file'] ?? '' ) );
                $viewed_log    = $logs[ $requested_log ] ?? ( ! empty( $logs ) ? current( $logs ) : false );

                require_once ZVC_PLUGIN_VIEWS_PATH . '/tabs/debug.php';
                break;
        }
    }
}