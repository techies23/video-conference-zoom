<?php

namespace Codemanas\VczApi\Data;

use Codemanas\VczApi\Helpers\Date;

/**
 * Class Datastore
 *
 * Will eventually handle all database functions
 *
 * @package Codemanas\VczApi\Data
 * @created March 2nd, 2021
 * @author Deepen Bajracharya
 */
class Datastore {

	/**
	 * @var string
	 */
	private static $post_type = 'zoom-meetings';

	/**
	 * @var int
	 */
	protected static $per_page = 10;

	/**
	 * @var int
	 */
	protected static $paged = 1;

	/**
	 * @var string
	 */
	protected static $order = 'DESC';

	/**
	 * Get Meetings
	 *
	 * @param bool $args
	 * @param bool $wp_query
	 *
	 * @return \WP_Query
	 */
	public static function get_meetings( $args = false, $wp_query = true ) {
		$post_arr = array(
			'post_type'      => self::$post_type,
			'posts_per_page' => ! empty( $args['per_page'] ) ? $args['paged'] : self::$per_page,
			'post_status'    => ! empty( $args['status'] ) ? $args['status'] : 'publish',
			'paged'          => ! empty( $args['paged'] ) ? $args['paged'] : self::$paged,
			'order'          => self::$order,
		);

		if ( ! empty( $args['author'] ) ) {
			$post_arr['author'] = absint( $args['author'] );
		}

		//If meeting type is not defined then pull all zoom list regardless of webinar or meeting only.
		if ( ! empty( $args['meeting_type'] ) ) {
			$post_arr['meta_query'] = array(
				array(
					'key'     => '_vczapi_meeting_type',
					'value'   => $args['meeting_type'] === "meeting" ? 'meeting' : 'webinar',
					'compare' => '='
				)
			);
		}

		if ( ! empty( $args['meeting_sort'] ) ) {
			$type                     = ( $args['meeting_sort'] === "upcoming" ) ? '>=' : '<=';
			$post_arr['meta_query'][] = array(
				'key'     => '_meeting_field_start_date_utc',
				'value'   => Date::dateConverter( 'now', 'UTC', 'Y-m-d H:i:s', false ),
				'compare' => $type,
				'type'    => 'DATETIME'
			);
		}

		if ( ! empty( $args['taxonomy'] ) ) {
			$category              = array_map( 'trim', explode( ',', $args['taxonomy'] ) );
			$post_arr['tax_query'] = [
				[
					'taxonomy' => 'zoom-meeting',
					'field'    => 'slug',
					'terms'    => $category,
					'operator' => 'IN'
				]
			];
		}

		$query = apply_filters( 'vczapi_get_posts_query_args', $post_arr );
		if ( $wp_query ) {
			$result = new \WP_Query( $query );
		} else {
			$result = get_posts( $query );
		}

		return $result;
	}
}