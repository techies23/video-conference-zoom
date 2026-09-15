<?php

namespace Codemanas\VczApi\Admin\Foundation\Settings;

use Codemanas\VczApi\Admin\Repository\SettingsRepository;
use Codemanas\VczApi\Data\Logger;

class SettingsPageView {

	private SettingsRepository $settingsRepo;

	public function __construct( SettingsRepository $settingsRepo ) {
		$this->settingsRepo = $settingsRepo;
	}

	public function render(): void {
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
			$this->renderTabContent( $active_tab );
			?>
        </div>
		<?php
	}

	private function renderTabContent( string $active_tab ): void {
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
				$settings = $this->settingsRepo->getSettings();
				require_once ZVC_PLUGIN_VIEWS_PATH . '/tabs/api-settings.php';
				break;

			case 'support':
				require_once ZVC_PLUGIN_VIEWS_PATH . '/tabs/support.php';
				break;

			case 'debug':
				$settings  = $this->settingsRepo->getSettings();
				$debug_log = $settings['debugger_logs'] ?? false;
				$logs      = Logger::get_log_files();

				$requested_log = sanitize_title( wp_unslash( $_REQUEST['log_file'] ?? '' ) );
				$viewed_log    = $logs[ $requested_log ] ?? ( ! empty( $logs ) ? current( $logs ) : false );

				require_once ZVC_PLUGIN_VIEWS_PATH . '/tabs/debug.php';
				break;
		}
	}
}