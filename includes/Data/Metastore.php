<?php

namespace Codemanas\VczApi\Data;

/**
 * Class Meta Store
 *
 * Will eventually handle all meta value functions related to Zoom Meetings post type
 *
 * @package Codemanas\VczApi\Data
 * @since   4.2.2
 * @author  Deepen Bajracharya
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

	/**
	 * Set Custom Post Data
	 *
	 * @param $post_id
	 * @param $key
	 * @param $value
	 *
	 * @return void
	 */
	public static function setPostMeta( $post_id, $key, $value ): void {
		update_post_meta( $post_id, "vczapi_{$key}", $value );
	}

	/**
	 * Get custom post meta
	 *
	 * @param         $post_id
	 * @param         $key
	 * @param   bool  $single
	 *
	 * @return mixed
	 */
	public static function getPostMeta( $post_id, $key, bool $single = true ): mixed {
		$post_meta = get_post_meta( $post_id, "vczapi_{$key}", $single );

		if ( empty( $post_meta ) && ( 'meeting_zoom_details' === $key || 'meeting_fields' === $key ) ) {
			return self::backCompatMeta( $post_id, $key, $single );
		}

		return $post_meta;
	}

	/**
	 * Get backwards compatible post-meta
	 *
	 * @param         $post_id
	 * @param         $key
	 * @param   bool  $single
	 *
	 * @return mixed
	 */
	public static function backCompatMeta( $post_id, $key, bool $single = true ): mixed {
		if ( metadata_exists( 'post', $post_id, "vczapi_{$key}" ) ) {
			$data = get_post_meta( $post_id, "vczapi_{$key}", $single );
			if ( 'meeting_fields' === $key && is_array( $data ) ) {
				return self::meetingFieldCompat( $data );
			}

			return $data;
		}

		if ( metadata_exists( 'post', $post_id, "_{$key}" ) ) {
			$data = get_post_meta( $post_id, "_{$key}", $single );
			if ( is_object( $data ) ) {
				$data = (array) $data;
			}

			if ( 'meeting_fields' === $key && is_array( $data ) ) {
				return self::meetingFieldCompat( $data );
			}

			return $data;
		}

		return get_post_meta( $post_id, "vczapi_{$key}", $single );
	}

	/**
	 * Map backwards compatible meeting fields
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function meetingFieldCompat( array $data ): array {
		$map = [
			'userId'                    => 'user_id',
			'meeting_type'              => 'type',
			'start_date'                => 'start_time',
			'option_host_video'         => 'host_video',
			'option_auto_recording'     => 'auto_recording',
			'alternative_host_ids'      => 'alternative_hosts',
			'option_participants_video' => 'participant_video',
			'option_mute_participants'  => 'mute_upon_entry',
			'disable_waiting_room'      => 'waiting_room',
			'option_logged_in'          => 'site_option_logged_in',
			'option_browser_join'       => 'site_option_browser_join',
			'option_enable_debug_logs'  => 'site_option_enable_debug_log',
		];

		$mapped = $data;

		foreach ( $map as $old_key => $new_key ) {
			if ( array_key_exists( $old_key, $data ) && ! array_key_exists( $new_key, $mapped ) ) {
				$mapped[ $new_key ] = $data[ $old_key ];
			}
		}

		$reverse_map = [
			'user_id'                      => 'userId',
			'type'                         => 'meeting_type',
			'start_time'                   => 'start_date',
			'host_video'                   => 'option_host_video',
			'auto_recording'               => 'option_auto_recording',
			'alternative_hosts'            => 'alternative_host_ids',
			'participant_video'            => 'option_participants_video',
			'mute_upon_entry'              => 'option_mute_participants',
			'waiting_room'                 => 'disable_waiting_room',
			'site_option_logged_in'        => 'option_logged_in',
			'site_option_browser_join'     => 'option_browser_join',
			'site_option_enable_debug_log' => 'option_enable_debug_logs',
		];

		foreach ( $reverse_map as $new_key => $old_key ) {
			if ( array_key_exists( $new_key, $mapped ) && ! array_key_exists( $old_key, $mapped ) ) {
				$mapped[ $old_key ] = $mapped[ $new_key ];
			}
		}

		return $mapped;
	}
}