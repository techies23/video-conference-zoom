<?php

namespace Codemanas\VczApi\Shortcodes;

use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\Config;
use Codemanas\VczApi\Shortcodes\Ajax\MeetingListAjax;
use Codemanas\VczApi\Shortcodes\Utils\AttributeSanitizer;
use Codemanas\VczApi\Shortcodes\Utils\ListingQuery;
use Codemanas\VczApi\Shortcodes\Utils\Support;

/**
 * Meeting shortcodes.
 *
 * @since 3.0.0
 */
class Meetings {

	private static ?Meetings $_instance = null;
	private string $postType;

	public static function get_instance(): Meetings {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		$this->postType = Config::get( 'post_type' );
	}

	/**
	 * Show a meeting saved as a custom post.
	 * `[zoom_meeting_post]`
	 */
	public function show_meeting_by_postTypeID( $atts ): string {
		$atts = shortcode_atts(
			[
				'post_id'     => '',
				'template'    => '',
				'countdown'   => true,
				'description' => true,
				'details'     => true,
			],
			$atts,
			'zoom_meeting_post'
		);

		$post_id     = absint( $atts['post_id'] );
		$template    = AttributeSanitizer::template_name( $atts['template'] );
		$countdown   = AttributeSanitizer::true_false( $atts['countdown'] );
		$description = AttributeSanitizer::true_false( $atts['description'] );
		$details     = AttributeSanitizer::true_false( $atts['details'] );

		if ( empty( $post_id ) ) {
			return $this->no_id_error( __( 'No post id set in the shortcode', 'video-conferencing-with-zoom-api' ) );
		}

		Assets::enqueue();
		$this->localize_event_text();

		$meeting_query = new \WP_Query( [
			'p'         => $post_id,
			'post_type' => $this->postType,
		] );

		if ( ! $meeting_query->have_posts() ) {
			return '<p>' . esc_html__( 'This post does not exist.', 'video-conferencing-with-zoom-api' ) . '</p>';
		}

		ob_start();

		while ( $meeting_query->have_posts() ) {
			$meeting_query->the_post();

			// Prepare payload without mutating $GLOBALS
			$meeting_data = $this->prepare_zoom_data( get_the_ID(), [
				'shortcode'  => true,
				'post_id'    => get_the_ID(),
				'parameters' => [
					'description' => esc_html( $description ),
					'countdown'   => esc_html( $countdown ),
					'details'     => esc_html( $details ),
				],
			] );

			if ( 'boxed' === $template ) {
				$meeting_data['shortcode_post_by_id'] = true;

				// Pass $meeting_data directly into template args
				vczapi_get_template(
					'shortcode/meeting-by-post-id.php',
					true,
					false,
					[ 'zoom' => $meeting_data ]
				);
			} else {
				vczapi_get_template_part( 'content', 'single-meeting' );
			}
		}

		wp_reset_postdata();

		return (string) ob_get_clean();
	}

	/**
	 * Assemble the meeting payload without globals.
	 */
	private function prepare_zoom_data( int $post_id, array $extra_context = [] ): array {
		$zoom            = Metastore::getPostMeta( $post_id, 'meeting_fields' );
		$meeting_details = Metastore::getPostMeta( $post_id, 'meeting_zoom_details' );

		$zoom = is_array( $zoom ) ? $zoom : [];

		$zoom['host_name'] = get_option( 'zoom_show_author' )
			? vczapi_get_meeting_author( $post_id, $meeting_details )
			: get_the_author();

		$terms = get_the_terms( $post_id, ListingQuery::TAXONOMY );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$zoom['terms'] = wp_list_pluck( $terms, 'name' );
		}

		return array_merge( $zoom, $extra_context );
	}

	private function has_registration_url( array $zoom ): bool {
		$api = ZoomResponse::make( $zoom['api'] ?? [] );

		return $api && ! empty( $api['registration_url'] );
	}

	private function localize_event_text(): void {
		$date_format = get_option( 'zoom_api_date_time_format' );

		if ( 'custom' === $date_format ) {
			$date_format = vczapi_convertPHPToMomentFormat( (string) get_option( 'zoom_api_custom_date_time_format' ) );
		}

		$starting_text = get_option( 'zoom_going_tostart_meeting_text' );
		$ended_text    = get_option( 'zoom_ended_meeting_text' );

		$strings = apply_filters( 'vczapi_meeting_event_text', [
			'meeting_starting' => ! empty( $starting_text ) ? $starting_text : __( 'Click join button below to join the meeting now !', 'video-conferencing-with-zoom-api' ),
			'meeting_ended'    => ! empty( $ended_text ) ? $ended_text : __( 'This meeting has been ended by the host.', 'video-conferencing-with-zoom-api' ),
			'date_format'      => $date_format,
		] );

		wp_localize_script( Assets::SCRIPT_HANDLE, 'zvc_strings', (array) $strings );
	}

	private static function no_id_error( string $message ): string {
		return '<h4 class="no-meeting-id"><strong style="color:red;">'
		       . esc_html__( 'ERROR: ', 'video-conferencing-with-zoom-api' )
		       . '</strong>' . esc_html( $message ) . '</h4>';
	}
}