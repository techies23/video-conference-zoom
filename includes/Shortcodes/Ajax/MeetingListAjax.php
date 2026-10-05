<?php

namespace Codemanas\VczApi\Shortcodes\Ajax;

use Codemanas\VczApi\Shortcodes\AttributeSanitizer;
use Codemanas\VczApi\Shortcodes\Helpers;
use Codemanas\VczApi\Shortcodes\ListingQuery;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * AJAX handler behind the meeting/webinar listing filter form and pagination.
 *
 * Registered for `meetings` and `webinars` alike; the listing type travels in
 * the `data[meeting_type]` payload written by `templates/shortcode-listing.php`.
 *
 * @package Codemanas\VczApi\Shortcodes\Ajax
 */
class MeetingListAjax {

	private static ?MeetingListAjax $_instance = null;

	/**
	 * @return MeetingListAjax
	 */
	public static function get_instance(): MeetingListAjax {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_vczapi_list_meeting_shortcode_ajax_handler', [ $this, 'handle' ] );
		add_action( 'wp_ajax_nopriv_vczapi_list_meeting_shortcode_ajax_handler', [ $this, 'handle' ] );
	}

	/**
	 * Re-render the listing for a new page number or filter selection.
	 *
	 * @return void
	 */
	public function handle(): void {
		//will be provided on both filter form change or pagination
		$data = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		//will only be provided on filter form submit
		$form_data = filter_input( INPUT_POST, 'form_data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		$data      = is_array( $data ) ? $data : [];
		$form_data = is_array( $form_data ) ? $form_data : [];

		$atts = ListingQuery::sanitize_atts( shortcode_atts(
			[
				'author'       => '',
				'per_page'     => 5,
				'category'     => '',
				'order'        => 'DESC',
				'type'         => '',
				'filter'       => 'yes',
				'show_on_past' => 'yes',
				'cols'         => 3,
				'page_num'     => 1,
				'base_url'     => '',
				'meeting_type' => ListingQuery::TYPE_MEETINGS,
			],
			$data,
			'zoom_list_meetings'
		) );

		$atts['page_num']     = AttributeSanitizer::positive_int( $data['page_num'] ?? 1, 1 );
		$atts['base_url']     = ! empty( $data['base_url'] ) ? esc_url_raw( AttributeSanitizer::scalar( $data['base_url'] ) ) : '';
		$atts['meeting_type'] = ListingQuery::normalize_type( $data['meeting_type'] ?? '' );

		$query = ( new ListingQuery( $atts['meeting_type'] ) )->build( $atts, [
			'paged'  => $atts['page_num'],
			'terms'  => $this->resolve_terms( $form_data, $atts ),
			'order'  => $this->resolve_order( $form_data ),
			'search' => AttributeSanitizer::scalar( $form_data['search'] ?? '' ),
		] );

		$zoom_meetings = new \WP_Query( apply_filters( 'vczapi_meeting_list_ajax_query_args', $query, $form_data ) );

		unset( $GLOBALS['zoom_meetings'] );
		$GLOBALS['zoom_meetings']          = $zoom_meetings;
		$GLOBALS['zoom_meetings']->columns = absint( $atts['cols'] );

		ob_start();
		if ( $zoom_meetings->have_posts() ) {
			while ( $zoom_meetings->have_posts() ) {
				$zoom_meetings->the_post();
				do_action( 'vczapi_main_content_post_loop' );
				vczapi_get_template_part( 'shortcode/zoom', 'listing' );
			}
			wp_reset_postdata();
		} else {
			echo "<p class='vczapi-no-meeting-found'>" . esc_html__( 'No Meetings found.', 'video-conferencing-with-zoom-api' ) . "</p>";
		}
		$content = ob_get_clean();

		ob_start();
		Helpers::pagination( $zoom_meetings, $atts['page_num'], $atts['base_url'] );
		$pagination = ob_get_clean();

		wp_send_json( [
			'content'    => $content,
			'pagination' => $pagination,
		] );
	}

	/**
	 * The filter form takes precedence over the shortcode's `category` attribute.
	 *
	 * @param array $form_data Submitted filter form.
	 * @param array $atts      Sanitized shortcode attributes.
	 *
	 * @return array
	 */
	private function resolve_terms( array $form_data, array $atts ): array {
		if ( ! empty( $form_data['taxonomy'] ) && 'category_order' !== $form_data['taxonomy'] ) {
			return is_array( $form_data['taxonomy'] )
				? AttributeSanitizer::category_slug_array( $form_data['taxonomy'] )
				: AttributeSanitizer::category_slugs( $form_data['taxonomy'] );
		}

		return AttributeSanitizer::category_slugs( $atts['category'] ?? '' );
	}

	/**
	 * Translate the "upcoming / past / show all" filter form select into an order.
	 *
	 * @param array $form_data Submitted filter form.
	 *
	 * @return string Empty string when the shortcode order should be kept.
	 */
	private function resolve_order( array $form_data ): string {
		if ( empty( $form_data['orderby'] ) || 'show_all' === $form_data['orderby'] ) {
			return '';
		}

		return 'past' === $form_data['orderby'] ? 'ASC' : 'DESC';
	}
}