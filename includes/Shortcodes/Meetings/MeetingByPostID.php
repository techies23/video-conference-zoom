<?php

namespace Codemanas\VczApi\Shortcodes\Meetings;

use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\Config;
use Codemanas\VczApi\Helpers\Templates;
use Codemanas\VczApi\Shortcodes\Assets;
use Codemanas\VczApi\Shortcodes\Utils\AttributeSanitizer;
use Codemanas\VczApi\Shortcodes\Utils\ListingQuery;
use Codemanas\VczApi\Shortcodes\Utils\Support;

class MeetingByPostID {

	public function render( $atts ): string {
		$atts = shortcode_atts(
			[
				'post_id'     => '',
				'countdown'   => true,
				'description' => true,
				'details'     => true,
			],
			$atts,
			'zoom_meeting_post'
		);

		$post_id     = absint( $atts['post_id'] );
		$countdown   = AttributeSanitizer::true_false( $atts['countdown'] );
		$description = AttributeSanitizer::true_false( $atts['description'] );
		$details     = AttributeSanitizer::true_false( $atts['details'] );

		if ( empty( $post_id ) ) {
			return Support::no_id_error( __( 'No meeting id set in the shortcode', 'video-conferencing-with-zoom-api' ) );
		}

		Assets::enqueue();

		$meeting_query = new \WP_Query( [
			'p'         => $post_id,
			'post_type' => Config::get( 'post_type' ),
		] );

		if ( ! $meeting_query->have_posts() ) {
			return '<p>' . esc_html__( 'This post does not exist.', 'video-conferencing-with-zoom-api' ) . '</p>';
		}

		ob_start();

		while ( $meeting_query->have_posts() ) {
			$meeting_query->the_post();

			// Prepare payload without mutating $GLOBALS
			$meeting_data = $this->prepare_zoom_data( get_the_ID(), [
				'post_id'    => get_the_ID(),
				'parameters' => [
					'description' => esc_html( $description ),
					'countdown'   => esc_html( $countdown ),
					'details'     => esc_html( $details ),
				],
			] );

			Templates::getTemplate( 'shortcode/meeting-by-post-id.php', true, false, $meeting_data );
		}

		wp_reset_postdata();

		return (string) ob_get_clean();
	}

	/**
	 * Assemble the meeting payload without globals.
	 */
	private function prepare_zoom_data( int $post_id, array $extra_context = [] ): array {
		$meeting_details = Metastore::getPostMeta( $post_id, 'meeting_zoom_details' );
		$meeting_details = is_array( $meeting_details ) ? $meeting_details : [];

		$meeting_details['host_name'] = get_option( 'zoom_show_author' )
			? vczapi_get_meeting_author( $post_id, $meeting_details )
			: get_the_author();

		$terms = get_the_terms( $post_id, ListingQuery::TAXONOMY );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$meeting_details['terms'] = wp_list_pluck( $terms, 'name' );
		}

		return array_merge( $meeting_details, $extra_context );
	}

	private static ?MeetingByPostID $instance = null;

	public static function get_instance(): ?MeetingByPostID {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}