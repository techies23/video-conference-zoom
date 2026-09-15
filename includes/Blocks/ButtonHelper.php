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
	 * @param mixed $action_type Raw action type.
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
	 * @param mixed $source_type Raw source type.
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
	 * @param mixed $value Raw CSS color value.
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
	 * @param mixed $value Raw CSS size value.
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
				if ( ! empty( $raw['start_url'] ) ) {
					return esc_url_raw( $raw['start_url'] );
				}
				break;
		}

		return '#';
	}
}