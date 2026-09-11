<?php

namespace Codemanas\VczApi\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class DetailsHelper
 *
 * Centralized utility for resolving Zoom meeting data based on block context/attributes.
 */
class DetailsHelper {

	/**
	 * Static cache to store resolved meeting details within a single request.
	 *
	 * @var array
	 */
	private static $cache = [];

	/**
	 * Get meeting details based on block context attributes.
	 *
	 * @param   array  $attributes  Block attributes (sourceType, customMeetingId, selectedMeetingPostId).
	 *
	 * @return array|false Normalized array of meeting details or false if unavailable.
	 */
	public static function get_meeting_details( array $attributes ): false|array {
		$source_type              = $attributes['sourceType'] ?? 'current';
		$custom_meeting_id        = $attributes['customMeetingId'] ?? '';
		$selected_meeting_post_id = $attributes['selectedMeetingPostId'] ?? 0;
		// Generate a unique cache key for this request
		$cache_key = md5( $source_type . '_' . $custom_meeting_id . '_' . $selected_meeting_post_id );

		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		$meeting_data = false;

		switch ( $source_type ) {
			case 'post_type':
				if ( ! empty( $selected_meeting_post_id ) ) {
					$meeting_data = self::get_details_by_post_id( $selected_meeting_post_id );
				}
				break;

			case 'custom':
				if ( ! empty( $custom_meeting_id ) ) {
					$meeting_data = self::get_details_by_meeting_id( $custom_meeting_id );
				}
				break;

			case 'current':
			default:
				$post_id = get_the_ID();
				if ( $post_id ) {
					$meeting_data = self::get_details_by_post_id( $post_id );
				}
				break;
		}

		self::$cache[ $cache_key ] = $meeting_data;

		return $meeting_data;
	}

	/**
	 * Fetch meeting details stored in a post's custom meta fields.
	 *
	 * @param   int  $post_id
	 *
	 * @return array|false
	 */
	public static function get_details_by_post_id( int $post_id ): false|array {
		if ( get_post_type( $post_id ) !== 'zoom-meetings' ) {
			// Fallback check if meta exists directly on other post types
			$meeting_id = get_post_meta( $post_id, '_meeting_zoom_meeting_id', true );
			if ( empty( $meeting_id ) ) {
				return false;
			}
		}

		$api_data = get_post_meta( $post_id, '_meeting_zoom_details', true );
		if ( ! empty( $api_data ) && is_array( $api_data ) ) {
			return self::format_meeting_data( $api_data, $post_id );
		}else if( !empty($api_data) && is_object($api_data))
		{
			return self::format_meeting_data( (array)$api_data, $post_id );
		}

		// If post meta isn't cached, attempt API fetch using meeting ID meta
		$meeting_id = get_post_meta( $post_id, '_meeting_zoom_meeting_id', true );
		if ( $meeting_id ) {
			return self::get_details_by_meeting_id( $meeting_id, $post_id );
		}

		return false;
	}

	/**
	 * Fetch meeting details via Zoom API using meeting ID.
	 *
	 * @param   string  $meeting_id
	 * @param   int     $post_id  Optional post ID context.
	 *
	 * @return array|false
	 */
	public static function get_details_by_meeting_id( string $meeting_id, int $post_id = 0 ): false|array {
		$details = zoom_conference_v2()->meetings()->get( $meeting_id );

		if ( ! is_wp_error( $details ) && ! empty( $details ) ) {
			$data = json_decode( json_encode( $details ), true );

			return self::format_meeting_data( $data, $post_id );
		}

		return false;
	}

	/**
	 * Format and normalize meeting object structure.
	 *
	 * @param   array  $data
	 * @param   int    $post_id
	 *
	 * @return array
	 */
	private static function format_meeting_data( array $data, int $post_id = 0 ): array {
		$data =  [
			'post_id'    => $post_id,
			'id'         => $data['id'] ?? '',
			'topic'      => $data['topic'] ?? ( $post_id ? get_the_title( $post_id ) : '' ),
			'start_time' => $data['start_time'] ?? '',
			'timezone'   => $data['timezone'] ?? '',
			'duration'   => $data['duration'] ?? 0,
			'agenda'     => $data['agenda'] ?? '',
			'raw'        => $data,
		];
		return $data;
	}
}