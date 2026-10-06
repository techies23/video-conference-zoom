<?php

namespace Codemanas\VczApi\Shortcodes\Meetings;

use Codemanas\VczApi\Helpers\Config;
use Codemanas\VczApi\Helpers\Templates;
use Codemanas\VczApi\Shortcodes\Assets;
use Codemanas\VczApi\Shortcodes\Utils\AttributeSanitizer;
use Codemanas\VczApi\Shortcodes\Utils\ListingQuery;

class MeetingListView {

	/**
	 * Render the meeting CPT list.
	 *
	 * @param array|string $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function render( $atts ): string {
		$atts = ListingQuery::sanitize_atts(
			shortcode_atts(
				[
					'author'       => '',
					'per_page'     => 5,
					'category'     => '',
					'order'        => 'DESC',
					'type'         => '',
					'filter'       => 'yes',
					'show_on_past' => 'yes',
					'cols'         => 3,
				],
				$atts,
				'zoom_list_meetings'
			)
		);

		Assets::enqueue();

		$query_args    = apply_filters( 'vczapi_meeting_list_query_args', $this->list_query( $atts ) );
		$meeting_query = new \WP_Query( $query_args );
		dump($query_args);

		// Attach layout metadata to the query object for template consumption
		$meeting_query->columns = absint( $atts['cols'] ?? 3 );

		// Provide backwards-compatibility for third-party templates expecting $GLOBALS['zoom_meetings']
		$GLOBALS['zoom_meetings'] = $meeting_query;

		ob_start();

		Templates::getTemplate(
			'meeting-list-view.php',
			true,
			false,
			array_merge( $atts, [
				'query' => $meeting_query,
			] )
		);

		return (string) ob_get_clean();
	}

	private static function current_page(): int {
		$var = is_front_page() ? 'page' : 'paged';

		return max( 1, absint( get_query_var( $var ) ) );
	}

	private function list_query( array $atts ): array {
		return ( new ListingQuery( ListingQuery::TYPE_MEETINGS ) )->build(
			$atts,
			[
				'paged' => self::current_page(),
				'terms' => AttributeSanitizer::category_slugs( $atts['category'] ?? '' ),
			]
		);
	}

	private static ?MeetingListView $instance = null;

	public static function get_instance(): MeetingListView {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}