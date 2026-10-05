<?php

namespace Codemanas\VczApi\Shortcodes;

use Codemanas\VczApi\Helpers\Config;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Shared WP_Query builder for the meeting and webinar listing shortcodes.
 *
 * `[zoom_list_meetings]`, `[zoom_list_webinars]` and the listing AJAX handler
 * all needed the same argument array; the only difference is whether the
 * `_vczapi_meeting_type` clause selects `meeting` (plus pre-4.7 posts that have
 * no type at all) or `webinar`.
 *
 * @package Codemanas\VczApi\Shortcodes
 */
class ListingQuery {

	const TAXONOMY      = 'zoom-meeting';
	const TYPE_MEETINGS = 'meetings';
	const TYPE_WEBINARS = 'webinars';

	private const TYPE_META_KEY      = '_vczapi_meeting_type';
	private const START_DATE_META_KEY = '_meeting_field_start_date_utc';

	/**
	 * `meetings` or `webinars`.
	 *
	 * @var string
	 */
	private string $type;

	/**
	 * Post type holding Zoom meetings.
	 *
	 * @var string
	 */
	private string $postType;

	/**
	 * @param string $type Listing type.
	 */
	public function __construct( string $type = self::TYPE_MEETINGS ) {
		$this->postType = Config::get( 'post_type' );
		$this->type     = self::normalize_type( $type );
	}

	/**
	 * @return string
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function get_post_type(): string {
		return $this->postType;
	}

	/**
	 * Coerce a listing type to a known value.
	 *
	 * @param mixed $type Value to normalize.
	 *
	 * @return string
	 */
	public static function normalize_type( $type ): string {
		return self::TYPE_WEBINARS === strtolower( AttributeSanitizer::scalar( $type ) )
			? self::TYPE_WEBINARS
			: self::TYPE_MEETINGS;
	}

	/**
	 * Normalize the shared attributes of the listing shortcodes.
	 *
	 * @param array $atts Shortcode or AJAX attributes.
	 *
	 * @return array
	 */
	public static function sanitize_atts( array $atts ): array {
		$atts['author']       = ! empty( $atts['author'] ) ? absint( $atts['author'] ) : '';
		$atts['per_page']     = AttributeSanitizer::positive_int( $atts['per_page'] ?? 0, 5 );
		$atts['category']     = implode( ',', AttributeSanitizer::category_slugs( $atts['category'] ?? '' ) );
		$atts['order']        = AttributeSanitizer::order( $atts['order'] ?? 'DESC' );
		$atts['type']         = AttributeSanitizer::list_type( $atts['type'] ?? '' );
		$atts['filter']       = AttributeSanitizer::yes_no( $atts['filter'] ?? 'yes', 'yes' );
		$atts['show_on_past'] = AttributeSanitizer::yes_no( $atts['show_on_past'] ?? 'yes', 'yes' );
		$atts['cols']         = AttributeSanitizer::positive_int( $atts['cols'] ?? 0, 3 );

		return $atts;
	}

	/**
	 * Build the WP_Query arguments for a listing.
	 *
	 * @param array $atts      Sanitized attributes from {@see self::sanitize_atts()}.
	 * @param array $overrides Optional. `paged` (int), `terms` (array|null), `order` (string), `search` (string).
	 *
	 * @return array
	 */
	public function build( array $atts, array $overrides = [] ): array {
		$args = array(
			'post_type'      => $this->postType,
			'posts_per_page' => absint( $atts['per_page'] ),
			'post_status'    => 'publish',
			'paged'          => max( 1, absint( $overrides['paged'] ?? 1 ) ),
			'orderby'        => 'meta_value',
			'meta_key'       => self::START_DATE_META_KEY,
			'order'          => $atts['order'],
			'caller'         => 'yes' === $atts['filter'] ? 'vczapi' : false,
			'meta_query'     => $this->type_meta_query(),
		);

		if ( ! empty( $atts['author'] ) ) {
			$args['author'] = absint( $atts['author'] );
		}

		$this->apply_time_threshold( $args, $atts );

		$terms = $overrides['terms'] ?? null;
		if ( is_array( $terms ) && ! empty( $terms ) ) {
			$args['tax_query'] = [
				[
					'taxonomy' => self::TAXONOMY,
					'field'    => 'slug',
					'terms'    => $terms,
					'operator' => 'IN',
				],
			];
		}

		if ( ! empty( $overrides['order'] ) ) {
			$args['order'] = $overrides['order'];
		}

		if ( ! empty( $overrides['search'] ) ) {
			$args['s'] = sanitize_text_field( $overrides['search'] );
		}

		return $args;
	}

	/**
	 * Restrict results to the meeting or webinar posts of this listing type.
	 *
	 * Meetings also match posts saved before `_vczapi_meeting_type` existed.
	 *
	 * @return array
	 */
	private function type_meta_query(): array {
		$group = [
			'relation' => 'OR',
			[
				'key'     => self::TYPE_META_KEY,
				'value'   => self::TYPE_WEBINARS === $this->type ? 'webinar' : 'meeting',
				'compare' => '=',
			],
		];

		if ( self::TYPE_WEBINARS !== $this->type ) {
			$group[] = [
				'key'     => self::TYPE_META_KEY,
				'compare' => 'NOT EXISTS',
			];
		}

		return [
			'relation' => 'AND',
			$group,
		];
	}

	/**
	 * Add the upcoming/past start date filter, when requested.
	 *
	 * @param array $args Query arguments, modified in place.
	 * @param array $atts Sanitized attributes.
	 *
	 * @return void
	 */
	private function apply_time_threshold( array &$args, array $atts ): void {
		if ( empty( $atts['type'] ) ) {
			return;
		}

		//NOTE !!!! When using this filter please correctly send minutes or hours otherwise it will output error
		$threshold_limit = apply_filters( 'vczapi_list_cpt_meetings_threshold', '30 minutes' );

		if ( 'yes' === $atts['show_on_past'] && ! empty( $threshold_limit ) ) {
			$direction = 'upcoming' === $atts['type'] ? 'now -' : 'now +';
			$threshold = vczapi_dateConverter( $direction . $threshold_limit, 'UTC', 'Y-m-d H:i:s', false );
		} else {
			$threshold = vczapi_dateConverter( 'now', 'UTC', 'Y-m-d H:i:s', false );
		}

		$args['meta_query'][] = [
			'key'     => self::START_DATE_META_KEY,
			'value'   => $threshold,
			'compare' => 'upcoming' === $atts['type'] ? '>=' : '<=',
			'type'    => 'DATETIME',
		];
	}
}