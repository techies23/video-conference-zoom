<?php

namespace Codemanas\VczApi\Blocks;

use Codemanas\VczApi\Helpers\Links;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ButtonHelper {

	/**
	 * Allowed button action types.
	 *
	 * @return string[]
	 */
	private static function get_allowed_action_types(): array {
		return [ 'app', 'browser', 'start' ];
	}

	/**
	 * Allowed button source types.
	 *
	 * @return string[]
	 */
	private static function get_allowed_source_types(): array {
		return [ 'current', 'post_type', 'custom' ];
	}

	/**
	 * Sanitize button action type.
	 *
	 * @param   mixed  $action_type  Raw action type.
	 *
	 * @return string
	 */
	public static function sanitize_action_type( $action_type ): string {
		$action_type = sanitize_key( (string) $action_type );

		if ( ! in_array( $action_type, self::get_allowed_action_types(), true ) ) {
			return 'app';
		}

		return $action_type;
	}

	/**
	 * Sanitize button source type.
	 *
	 * @param   mixed  $source_type  Raw source type.
	 *
	 * @return string
	 */
	public static function sanitize_source_type( $source_type ): string {
		$source_type = sanitize_key( (string) $source_type );

		if ( ! in_array( $source_type, self::get_allowed_source_types(), true ) ) {
			return 'current';
		}

		return $source_type;
	}

	/**
	 * Sanitize a CSS color value used in block inline styles.
	 *
	 * @param   mixed  $value  Raw CSS color value.
	 *
	 * @return string
	 */
	public static function sanitize_css_color( $value ): string {
		$value = trim( (string) $value );

		if ( '' === $value ) {
			return '';
		}

		if ( sanitize_hex_color( $value ) ) {
			return $value;
		}

		if ( in_array( strtolower( $value ), [ 'transparent', 'currentcolor' ], true ) ) {
			return $value;
		}

		if ( preg_match( '/^var\(--[a-zA-Z0-9_-]+\)$/', $value ) ) {
			return $value;
		}

		if ( preg_match( '/^rgba?\(\s*(?:\d{1,3}%?\s*,\s*){2}\d{1,3}%?(?:\s*,\s*(?:0|1|0?\.\d+|[0-9]{1,3}%))?\s*\)$/', $value ) ) {
			return $value;
		}

		if ( preg_match( '/^hsla?\(\s*\d{1,3}(?:deg)?\s*,\s*\d{1,3}%\s*,\s*\d{1,3}%(?:\s*,\s*(?:0|1|0?\.\d+|[0-9]{1,3}%))?\s*\)$/', $value ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * Sanitize a CSS size/spacing value used in block inline styles.
	 *
	 * @param   mixed  $value  Raw CSS size value.
	 *
	 * @return string
	 */
	public static function sanitize_css_size( $value ): string {
		$value = trim( (string) $value );

		if ( '' === $value ) {
			return '';
		}

		if ( '0' === $value ) {
			return '0';
		}

		if ( preg_match( '/^-?\d+(?:\.\d+)?(?:px|em|rem|%|vh|vw|vmin|vmax|ch|ex)$/', $value ) ) {
			return $value;
		}

		if ( preg_match( '/^var\(--[a-zA-Z0-9_-]+\)$/', $value ) ) {
			return $value;
		}

		if ( preg_match( '/^(?:calc|clamp)\([a-zA-Z0-9\s.%+\-*\/(),_-]+\)$/', $value ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * Generates the target URL based on action type and DetailsHelper meeting data.
	 *
	 * @param   string  $action_type  'app' | 'browser' | 'start'
	 * @param   string  $source_type  Source type fallback.
	 * @param   array   $attributes   Block attributes.
	 *
	 * @return string
	 */
	public static function get_url( string $action_type, string $source_type, array $attributes = [] ): string {
		// Ensure attribute keys are normalized for DetailsHelper
		if ( empty( $attributes['sourceType'] ) ) {
			$attributes['sourceType'] = $source_type;
		}
		if ( empty( $attributes['customMeetingId'] ) && ! empty( $attributes['meetingId'] ) ) {
			$attributes['customMeetingId'] = $attributes['meetingId'];
		}

		$meeting_data = DetailsHelper::get_meeting_details( $attributes );

		if ( empty( $meeting_data ) ) {
			return '#';
		}

		$raw     = $meeting_data['raw'] ?? [];
		$post_id = $meeting_data['post_id'] ?? 0;

		switch ( $action_type ) {
			case 'app':
				if ( ! empty( $raw['join_url'] ) ) {
					$password = $raw['encrypted_password'] ?? $raw['password'] ?? '';

					return esc_url_raw( Links::getPwdEmbeddedJoinLink( $raw['join_url'], $password ) );
				}
				break;

			case 'browser':
				if ( ! empty( $meeting_data['id'] ) ) {
					$browser_link = Links::getJoinViaBrowserJoinLinks( [
						'link_only' => true,
						'post_id'   => $post_id,
						'password'  => $raw['password'] ?? '',
					], $meeting_data['id'] );

					if ( ! empty( $browser_link ) ) {
						return esc_url_raw( $browser_link );
					}
				}
				break;

			case 'start':
				if ( ! self::can_start_meeting( $meeting_data, $attributes ) ) {
					return "#";
				}
				if ( ! empty( $raw['start_url'] ) ) {
					return esc_url_raw( $raw['start_url'] );
				}
				break;
		}

		return '#';
	}


	/**
	 * Determines if a meeting can be started by the current user.
	 *
	 * This method checks multiple conditions, such as user permissions, meeting host details,
	 * post ownership, and user roles, to determine whether the current user can start the given meeting.
	 *
	 * @param   array  $meeting_data  An array of meeting data, which may include details like 'post_id', 'raw', 'host_id', and 'host_email'.
	 * @param   array  $attributes    Additional attributes for the meeting (optional).
	 *
	 * @return bool True if the current user can start the meeting, false otherwise.
	 */
	public static function can_start_meeting( array $meeting_data, array $attributes = [] ): bool {
		$current_user_id = get_current_user_id();

		// 1. Guests / logged-out users can never see start URL
		if ( ! $current_user_id ) {
			return false;
		}

		// 2. Site Admins / Managers can start any meeting
		if ( current_user_can( 'manage_options' ) ) {
			return apply_filters( 'vczapi_can_start_meeting', true, $meeting_data, $attributes, $current_user_id );
		}

		$post_id = $meeting_data['post_id'] ?? 0;
		$raw     = $meeting_data['raw'] ?? [];

		// 3. Check WP Post Author / Post capability (for 'current' and 'post_type' sources)
		if ( ! empty( $post_id ) ) {
			if ( vczapi_check_author( $post_id ) || current_user_can( 'edit_post', $post_id ) ) {
				return apply_filters( 'vczapi_can_start_meeting', true, $meeting_data, $attributes, $current_user_id );
			}
		}

		// 4. Check Zoom Host ID / Email mapping (works for 'custom' as well as post types)
		$meeting_host_id    = $raw['host_id'] ?? '';
		$meeting_host_email = $raw['host_email'] ?? '';

		if ( ! empty( $meeting_host_id ) ) {
			$user_zoom_host_id = get_user_meta( $current_user_id, 'user_zoom_hostid', true );
			if ( ! empty( $user_zoom_hostid ) && $user_zoom_host_id === $meeting_host_id ) {
				return apply_filters( 'vczapi_can_start_meeting', true, $meeting_data, $attributes, $current_user_id );
			}
		}

		if ( ! empty( $meeting_host_email ) ) {
			$user_zoom_email = get_user_meta( $current_user_id, 'vczapi_user_zoom_email_address', true );
			$current_user    = wp_get_current_user();

			if ( ( ! empty( $user_zoom_email ) && strtolower( $user_zoom_email ) === strtolower( $meeting_host_email ) ) ||
			     ( ! empty( $current_user->user_email ) && strtolower( $current_user->user_email ) === strtolower( $meeting_host_email ) ) ) {
				return apply_filters( 'vczapi_can_start_meeting', true, $meeting_data, $attributes, $current_user_id );
			}
		}

		return apply_filters( 'vczapi_can_start_meeting', false, $meeting_data, $attributes, $current_user_id );
	}
}