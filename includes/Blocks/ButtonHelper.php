<?php
namespace Codemanas\VczApi\Blocks;

use Codemanas\VczApi\Helpers\Links;
use Codemanas\VczApi\Zoom\Helpers\MeetingHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ButtonHelper {

	/**
	 * Generates the target URL based on action type, source type, and block attributes.
	 *
	 * @param string $action_type 'app' | 'browser' | 'start'
	 * @param string $source_type 'current' | 'post_type' | 'custom'
	 * @param array  $attributes  Block attributes.
	 * @return string
	 */
	public static function get_url( string $action_type, string $source_type, array $attributes = [] ): string {
		if ( 'custom' === $source_type ) {
			return self::get_custom_meeting_url( $action_type, $attributes );
		}

		$post_id = self::resolve_post_id( $source_type, $attributes );
		if ( ! $post_id ) {
			return '#';
		}

		$meeting_details = get_post_meta( $post_id, '_meeting_zoom_details', true );
		if ( ! is_object( $meeting_details ) ) {
			return '#';
		}

		return self::get_post_meeting_url( $action_type, $post_id, $meeting_details );
	}

	/**
	 * Resolve Post ID based on source type.
	 */
	private static function resolve_post_id( string $source_type, array $attributes ): int {
		if ( 'current' === $source_type ) {
			return (int) get_the_ID();
		}

		if ( 'post_type' === $source_type && ! empty( $attributes['selectedMeetingPostId'] ) ) {
			return (int) sanitize_key( $attributes['selectedMeetingPostId'] );
		}

		return 0;
	}

	/**
	 * Build URL for 'current' and 'post_type' sources.
	 */
	private static function get_post_meeting_url( string $action_type, int $post_id, object $meeting_details ): string {
		if ( 'app' === $action_type ) {
			if ( isset( $meeting_details->join_url, $meeting_details->encrypted_password ) ) {
				return Links::getPwdEmbeddedJoinLink( $meeting_details->join_url, $meeting_details->encrypted_password );
			}
		} elseif ( 'browser' === $action_type ) {
			if ( isset( $meeting_details->id ) ) {
				return Links::getJoinViaBrowserJoinLinks( [
					'link_only' => true,
					'post_id'   => $post_id,
					'password'  => $meeting_details->password ?? '',
				], $meeting_details->id );
			}
		}

		return '#';
	}

	/**
	 * Build URL for 'custom' API meeting source.
	 */
	private static function get_custom_meeting_url( string $action_type, array $attributes ): string {
		$meeting_id = ! empty( $attributes['meetingId'] ) ? sanitize_text_field( $attributes['meetingId'] ) : '';
		if ( empty( $meeting_id ) ) {
			return '';
		}

		$meeting = zoom_conference_v2()->meetings()->get( $meeting_id );
		if ( empty( $meeting ) ) {
			return '';
		}

		if ( 'app' === $action_type ) {
			return MeetingHelper::getJoinUrl( $meeting );
		}

		if ( 'browser' === $action_type ) {
			return Links::getJoinViaBrowserJoinLinks( [
				'link_only' => true,
				'password'  => $meeting['password'] ?? '',
			], $meeting_id );
		}

		return '#';
	}
}