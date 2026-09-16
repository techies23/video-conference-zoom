<?php

namespace Codemanas\VczApi\Admin\Foundation\Settings;

use Codemanas\VczApi\Admin\Controller\NoticeController;
use Codemanas\VczApi\Admin\Repository\SettingsRepository;

class GeneralSettingsHandler {

	private SettingsRepository $repository;

	public function __construct( SettingsRepository $repository ) {
		$this->repository = $repository;
	}

	public function handle(): void {
		if ( ! isset( $_POST['save_zoom_settings'] ) ) {
			return;
		}

		check_admin_referer( 'vczapi_settings_update_action', 'vczapi_settings_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$field_mappings = [
			'delete_zoom_meeting'                => 'delete_zom_meeting_also',
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
			$posted_data[ $key ] = sanitize_text_field( $_POST[ $post_field ] ?? '' );
		}

		$this->repository->updateSettings( $posted_data );
		video_conferencing_zoom_api_delete_user_cache();

		NoticeController::setNotice( __( 'Settings successfully updated.', 'video-conferencing-with-zoom-api' ), 'success' );
	}
}