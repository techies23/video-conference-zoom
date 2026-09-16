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
			return get_post_meta( $post_id, "vczapi_{$key}", $single );
		} elseif ( metadata_exists( 'post', $post_id, "_{$key}" ) ) {
			$data = get_post_meta( $post_id, "_{$key}", $single );
			if ( is_object( $data ) ) {
				$data = (array) $data;
			}

			if ( 'meeting_fields' === $key && is_array( $data ) ) {
				$data = self::meetingFieldCompat( $data, (int) $post_id );
			}

			return $data;
		}

		return get_post_meta( $post_id, "vczapi_{$key}", $single );
	}

	/**
	 * Map backwards compatible meeting fields.
	 *
	 * Field-by-Field Mapping Table:
	 * ---------------------------------------------------------------------------------------------------------------------
	 * | Legacy Field (`_meeting_fields`)                      | New Field (`vczapi_meeting_fields`)  | Type Conversion / Transformation                  |
	 * |-------------------------------------------------------|--------------------------------------|---------------------------------------------------|
	 * | (None - Post Title)                                   | topic                                | $data['topic'] ?? get_the_title( $post_id )       |
	 * | userId / host_id                                      | user_id                              | (string) $val                                     |
	 * | agenda                                                | agenda                               | (string) ( $val ?? '' )                           |
	 * | meeting_type                                          | type                                 | (int) ( $val ?? 1 )                               |
	 * | start_date                                            | start_time                           | date( 'Y-m-d\TH:i:s', strtotime( $val ) )         |
	 * | timezone                                              | timezone                             | (string) $val                                     |
	 * | duration                                              | duration                             | (int) ( $val ?? 40 )                              |
	 * | password                                              | password                             | (string) $val                                     |
	 * | disable_waiting_room                                  | disable_waiting_room / waiting_room  | 'yes' / 'on' / '1' / 1 -> '1', else null          |
	 * | meeting_authentication                                | meeting_authentication               | 'yes' / 'on' / '1' / 1 -> '1', else null          |
	 * | option_host_video                                     | host_video                           | 'yes' / 'on' / '1' / 1 -> '1', else null          |
	 * | option_auto_recording                                 | auto_recording                       | (string) ( $val ?? 'none' )                       |
	 * | alternative_host_ids                                  | alternative_hosts                    | Array of IDs, or false / null                     |
	 * | join_before_host                                      | join_before_host                     | 'yes' / 'on' / '1' / 1 -> '1', else null          |
	 * | jbh_time                                              | jbh_time                             | (int) ( $val ?? 0 )                               |
	 * | option_participants_video                             | participant_video                    | 'yes' / 'on' / '1' / 1 -> '1', else null          |
	 * | option_mute_participants                              | mute_upon_entry                      | 'yes' / 'on' / '1' / 1 -> '1', else null          |
	 * | site_option_logged_in / option_logged_in               | site_option_logged_in                | 'yes' / 'on' / '1' / 1 -> '1', else null          |
	 * | site_option_browser_join / option_browser_join         | site_option_browser_join             | 'yes' / 'on' / '1' / 1 -> '1', else null          |
	 * | site_option_enable_debug_log / option_enable_debug_logs | site_option_enable_debug_log         | 'yes' / 'on' / '1' / 1 -> '1', else null          |
	 * ---------------------------------------------------------------------------------------------------------------------
	 *
	 * @param   array  $data     Legacy or new meeting fields data.
	 * @param   int    $post_id  Optional post ID for fallback values (such as post title).
	 *
	 * @return array
	 */
	public static function meetingFieldCompat( array $data, int $post_id = 0 ): array {
		// Helper to normalize checkboxes ('yes', 'on', '1', 1, true, 'true' => '1', otherwise null)
		$parse_bool = function ( $val ): ?string {
			if ( is_null( $val ) || $val === '' || $val === false || $val === 0 || $val === '0' || $val === 'no' || $val === 'off' ) {
				return null;
			}

			return ( in_array( $val, [ 'yes', 'on', '1', 1, true, 'true' ], true ) ) ? '1' : null;
		};

		// Format start time to ISO 8601 (Y-m-d\TH:i:s)
		$start_time = '';
		$raw_start  = $data['start_time'] ?? ( $data['start_date'] ?? '' );
		if ( ! empty( $raw_start ) ) {
			$timestamp  = strtotime( $raw_start );
			$start_time = $timestamp ? date( 'Y-m-d\TH:i:s', $timestamp ) : (string) $raw_start;
		}

		// Alternative hosts (array or false)
		$alt_hosts = $data['alternative_hosts'] ?? ( $data['alternative_host_ids'] ?? null );
		if ( is_string( $alt_hosts ) && ! empty( trim( $alt_hosts ) ) ) {
			$alt_hosts = array_values( array_filter( array_map( 'trim', explode( ',', str_replace( ';', ',', $alt_hosts ) ) ) ) );
		} elseif ( empty( $alt_hosts ) ) {
			$alt_hosts = false;
		}

		$disable_waiting_room = $parse_bool( $data['disable_waiting_room'] ?? ( $data['waiting_room'] ?? null ) );
		$host_video           = $parse_bool( $data['host_video'] ?? ( $data['option_host_video'] ?? null ) );
		$participant_video    = $parse_bool( $data['participant_video'] ?? ( $data['option_participants_video'] ?? null ) );
		$mute_upon_entry      = $parse_bool( $data['mute_upon_entry'] ?? ( $data['option_mute_participants'] ?? null ) );
		$meeting_auth         = $parse_bool( $data['meeting_authentication'] ?? null );
		$join_before_host     = $parse_bool( $data['join_before_host'] ?? null );
		$site_logged_in       = $parse_bool( $data['site_option_logged_in'] ?? ( $data['option_logged_in'] ?? null ) );
		$site_browser_join    = $parse_bool( $data['site_option_browser_join'] ?? ( $data['option_browser_join'] ?? null ) );
		$site_debug_log       = $parse_bool( $data['site_option_enable_debug_log'] ?? ( $data['option_enable_debug_logs'] ?? null ) );

		$topic = ! empty( $data['topic'] ) ? (string) $data['topic'] : ( ( ! empty( $post_id ) && function_exists( 'get_the_title' ) ) ? (string) get_the_title( $post_id ) : '' );

		$user_id  = (string) ( $data['user_id'] ?? ( $data['userId'] ?? ( $data['host_id'] ?? '' ) ) );
		$agenda   = (string) ( $data['agenda'] ?? '' );
		$type     = (int) ( $data['type'] ?? ( $data['meeting_type'] ?? 1 ) );
		$timezone = (string) ( $data['timezone'] ?? '' );
		$duration = isset( $data['duration'] ) ? (int) $data['duration'] : 40;
		$password = isset( $data['password'] ) ? (string) $data['password'] : '';
		$auto_rec = (string) ( $data['auto_recording'] ?? ( $data['option_auto_recording'] ?? 'none' ) );
		$jbh_time = isset( $data['jbh_time'] ) ? absint( $data['jbh_time'] ) : 0;

		$mapped = [
			'topic'                        => $topic,
			'user_id'                      => $user_id,
			'agenda'                       => $agenda,
			'type'                         => $type,
			'start_time'                   => $start_time,
			'timezone'                     => $timezone,
			'duration'                     => $duration,
			'password'                     => $password,
			'disable_waiting_room'         => $disable_waiting_room,
			'waiting_room'                 => $disable_waiting_room,
			'meeting_authentication'       => $meeting_auth,
			'host_video'                   => $host_video,
			'auto_recording'               => $auto_rec,
			'alternative_hosts'            => $alt_hosts,
			'site_option_logged_in'        => $site_logged_in,
			'site_option_browser_join'     => $site_browser_join,
			'site_option_enable_debug_log' => $site_debug_log,
			'join_before_host'             => $join_before_host,
			'jbh_time'                     => $jbh_time,
			'participant_video'            => $participant_video,
			'mute_upon_entry'              => $mute_upon_entry,
		];

		// Backwards compatibility legacy aliases
		$mapped['userId']                    = $user_id;
		$mapped['meeting_type']              = $type;
		$mapped['start_date']                = $start_time;
		$mapped['option_host_video']         = $host_video;
		$mapped['option_auto_recording']     = $auto_rec;
		$mapped['alternative_host_ids']      = is_array( $alt_hosts ) ? implode( ',', $alt_hosts ) : null;
		$mapped['option_participants_video'] = $participant_video;
		$mapped['option_mute_participants']  = $mute_upon_entry;
		$mapped['option_logged_in']          = $site_logged_in;
		$mapped['option_browser_join']       = $site_browser_join;
		$mapped['option_enable_debug_logs']  = $site_debug_log;

		// Preserve extra/webinar-specific fields
		foreach ( [ 'panelists_video', 'practice_session', 'hd_video', 'allow_multiple_devices' ] as $webinar_bool_field ) {
			if ( isset( $data[ $webinar_bool_field ] ) ) {
				$mapped[ $webinar_bool_field ] = $parse_bool( $data[ $webinar_bool_field ] );
			}
		}

		foreach ( $data as $k => $v ) {
			if ( ! array_key_exists( $k, $mapped ) ) {
				$mapped[ $k ] = $v;
			}
		}

		return $mapped;
	}
}