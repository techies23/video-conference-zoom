<?php

namespace Codemanas\VczApi\Admin\Repository;

class SettingsRepository {

	public const SETTINGS_OPTION_KEY = '_vczapi_zoom_settings';

	public static function getSetting( $key ): string {
		$settings = get_option( self::SETTINGS_OPTION_KEY, null );

		return ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
	}

	/**
	 * Retrieve options under unified key with legacy auto-migration fallback.
	 */
	public function getSettings(): array {
		$settings = get_option( self::SETTINGS_OPTION_KEY, null );

		if ( is_array( $settings ) ) {
			return $settings;
		}

		return $this->migrateLegacySettings();
	}

	/**
	 * Handles batch update of settings.
	 */
	public function updateSettings( array $data ): bool {
		return update_option( self::SETTINGS_OPTION_KEY, $data );
	}

	private function migrateLegacySettings(): array {
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

		$this->updateSettings( $migrated_settings );

		return $migrated_settings;
	}
}