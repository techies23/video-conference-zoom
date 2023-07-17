<?php

namespace Codemanas\VczApi\Data;

/**
 * Class Meta Store
 *
 * Will eventually handle all meta value functions related to Zoom Meetings post type
 *
 * @package Codemanas\VczApi\Data
 * @since 4.2.2
 * @author Deepen Bajracharya
 */
class Metastore {

	/**
	 * Check if direct join via browser is enabled
	 *
	 * @return bool
	 */
	public static function enabledDirectJoinViaBrowser(): bool {
		$enabled = self::get_plugin_settings( 'enable_direct_join_via_browser' );

		return ! empty( $enabled );
	}

	public static function dettachPasswordToLink(): bool {
		$enabled = self::get_plugin_settings( 'embed_pwd_in_join_link' );

		return ! empty( $enabled );
	}

	/**
	 * Check if Join via browser is disabled globally
	 *
	 * @return bool
	 */
	public static function checkDisableJoinViaBrowser(): bool {
		$disabled = self::get_plugin_settings( 'disable_join_via_browser' );

		return ! empty( $disabled );
	}

	/**
	 * Get Zoom Settings
	 *
	 * @param $type
	 *
	 * @return false|mixed
	 */
	public static function get_plugin_settings( $type = '' ) {
		$settings = get_option( '_vczapi_zoom_settings' );
		if ( ! empty( $settings ) && ! empty( $type ) ) {
			return ! empty( $settings[ $type ] ) ? $settings[ $type ] : false;
		}

		return ! empty( $settings ) ? $settings : false;
	}
}