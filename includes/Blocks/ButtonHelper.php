<?php

namespace Codemanas\VczApi\Blocks;

use Codemanas\VczApi\Helpers\Links;
use Codemanas\VczApi\Zoom\Helpers\MeetingHelper;

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
	 * Allows hex colors, rgb()/rgba(), hsl()/hsla(), transparent/currentColor,
	 * and safe CSS custom properties such as var(--wp--preset--color--primary).
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
	 * Allows 0, numeric CSS lengths, percentages, calc(), clamp(), and safe CSS variables.
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
	 * Generates the target URL based on action type, source type, and block attributes.
	 *
	 * @param   string  $action_type  'app' | 'browser' | 'start'
	 * @param   string  $source_type  'current' | 'post_type' | 'custom'
	 * @param   array   $attributes   Block attributes.
	 *
	 * @return string
	 */
	public static function get_url( string $action_type, string $source_type, array $attributes = [] ): string {
		$url = '#';

		if ( 'custom' === $source_type ) {
			$url = self::get_custom_meeting_url( $action_type, $attributes );
		} else {
			$post_id = self::resolve_post_id( $source_type, $attributes );
			if ( $post_id ) {
				$meeting_details = get_post_meta( $post_id, '_meeting_zoom_details', true );
				if ( is_object( $meeting_details ) ) {
					$url = self::get_post_meeting_url( $action_type, $post_id, $meeting_details );
				}
			}
		}

		return ! empty( $url ) ? $url : '#';
	}

	/**
	 * Resolve Post ID based on source type.
	 */
	private static function resolve_post_id( string $source_type, array $attributes ): int {
		if ( 'current' === $source_type ) {
			return (int) get_the_ID();
		}

		if ( 'post_type' === $source_type && ! empty( $attributes['selectedMeetingPostId'] ) ) {
			return absint( $attributes['selectedMeetingPostId'] );
		}

		return 0;
	}

	/**
	 * Build URL for 'current' and 'post_type' sources.
	 */
	private static function get_post_meeting_url( string $action_type, int $post_id, object $meeting_details ): string {
		switch ( $action_type ) {
			case 'app':
				if ( isset( $meeting_details->join_url, $meeting_details->encrypted_password ) ) {
					return esc_url_raw( Links::getPwdEmbeddedJoinLink( $meeting_details->join_url, $meeting_details->encrypted_password ) );
				}
				break;

			case 'browser':
				if ( isset( $meeting_details->id ) ) {
					return esc_url_raw( Links::getJoinViaBrowserJoinLinks( [
						'link_only' => true,
						'post_id'   => $post_id,
						'password'  => $meeting_details->password ?? '',
					], $meeting_details->id ) );
				}
				break;

			case 'start':
				return ! empty( $meeting_details->start_url ) ? esc_url_raw( $meeting_details->start_url ) : '#';
		}

		return '#';
	}

	/**
	 * Build URL for 'custom' API meeting source.
	 */
	private static function get_custom_meeting_url( string $action_type, array $attributes ): string {
		$meeting_id = ! empty( $attributes['meetingId'] ) ? sanitize_text_field( $attributes['meetingId'] ) : '';
		if ( empty( $meeting_id ) ) {
			return '#';
		}

		$meeting = zoom_conference_v2()->meetings()->get( $meeting_id );
		if ( empty( $meeting ) ) {
			return '#';
		}

		switch ( $action_type ) {
			case 'app':
				return MeetingHelper::getJoinUrl( $meeting );

			case 'browser':
				return Links::getJoinViaBrowserJoinLinks( [
					'link_only' => true,
					'password'  => $meeting['password'] ?? '',
				], $meeting_id );

			case 'start':
				return $meeting['start_url'] ?? '#';
		}

		return '#';
	}
}