<?php

namespace Codemanas\VczApi\Shortcodes;

use Codemanas\VczApi\Helpers\Config;
use Codemanas\VczApi\Shortcodes\Support\ZoomResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Webinar shortcodes.
 *
 * Public callbacks: `show_webinar_by_ID()`, `list_cpt_webinars()`,
 * `list_live_host_webinars()`.
 *
 * Webinars share the meeting custom post type and the `zoom-meeting` taxonomy
 * with meetings; they are separated by the `_vczapi_meeting_type` post meta.
 *
 * @since 3.0.0
 */
class Webinars {

	/**
	 * @var Webinars|null
	 */
	private static ?Webinars $_instance = null;

	/**
	 * Post type holding Zoom webinars.
	 *
	 * @var string
	 */
	private string $post_type;

	/**
	 * @return Webinars
	 */
	public static function get_instance(): Webinars {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		$this->post_type = Config::get( 'post_type' );
	}

	/**
	 * Show a single webinar by Zoom webinar ID.
	 *
	 * `[zoom_api_webinar]`
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function show_webinar_by_ID( $atts ): string {
		Assets::enqueue();

		$atts = shortcode_atts(
			[
				'webinar_id' => '',
				'link_only'  => 'no',
			],
			$atts,
			'zoom_api_webinar'
		);

		$webinar_id = AttributeSanitizer::numeric_id( $atts['webinar_id'] );
		$link_only  = AttributeSanitizer::yes_no( $atts['link_only'], 'no' );

		unset( $GLOBALS['vanity_uri'], $GLOBALS['zoom_webinars'] );

		if ( empty( $webinar_id ) ) {
			return '<h4 class="no-meeting-id"><strong style="color:red;">'
				. esc_html__( 'ERROR: ', 'video-conferencing-with-zoom-api' )
				. '</strong>' . esc_html__( 'No webinar id set in the shortcode', 'video-conferencing-with-zoom-api' )
				. '</h4>';
		}

		$response = zoom_conference_v2()->webinars()->get( $webinar_id );
		$error    = Helpers::error_message( $response );
		$webinar  = ZoomResponse::make( $response );

		$GLOBALS['vanity_uri']    = get_option( 'zoom_vanity_url' );
		$GLOBALS['zoom_webinars'] = $webinar;

		if ( ! empty( $error ) ) {
			return '<p class="dpn-error dpn-mtg-not-found">' . esc_html( $error ) . '</p>';
		}

		ob_start();

		if ( 'yes' === $link_only ) {
			Helpers::generate_link_only();
		} elseif ( $webinar ) {
			vczapi_get_template( 'shortcode/zoom-webinar.php', true, false );
		} else {
			printf(
				/* translators: %d: Zoom webinar ID */
				esc_html__( 'Please try again ! Some error occured while trying to fetch webinar with id:  %d', 'video-conferencing-with-zoom-api' ),
				absint( $webinar_id )
			);
		}

		return (string) ob_get_clean();
	}

	/**
	 * List upcoming webinars of a Zoom host.
	 *
	 * `[zoom_list_host_webinars]`
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function list_live_host_webinars( $atts ): string {
		$atts = shortcode_atts(
			[ 'host' => '' ],
			$atts,
			'zoom_list_host_webinars'
		);

		$host = AttributeSanitizer::host_id( $atts['host'] );

		if ( empty( $host ) ) {
			return esc_html__( 'Host ID should be given when defining this shortcode.', 'video-conferencing-with-zoom-api' );
		}

		Assets::enqueue();

		$webinars = Helpers::host_listing(
			'_vczapi_user_webinars_for_' . $host,
			'webinars',
			__( 'Could not retrieve webinars, check Host ID', 'video-conferencing-with-zoom-api' ),
			static function () use ( $host ) {
				return zoom_conference_v2()->webinars()->list(
					//Kept for back compat with the legacy `listWebinar()` argument filter.
					apply_filters( 'vczapi_listWebinar', [
						'user_id'   => $host,
						'page_size' => 300,
					] )
				);
			}
		);

		if ( is_wp_error( $webinars ) ) {
			return '<strong>' . esc_html__( 'Zoom API Error:', 'video-conferencing-with-zoom-api' ) . '</strong>'
				. esc_html( $webinars->get_error_message() );
		}

		ob_start();
		vczapi_get_template( 'shortcode/list-webinars-host.php', true, false, ZoomResponse::makeList( $webinars ) ?? [] );

		return (string) ob_get_clean();
	}

	/**
	 * List webinars from the webinar custom post type.
	 *
	 * `[zoom_list_webinars]`
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function list_cpt_webinars( $atts ): string {
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
				'zoom_list_webinars'
			)
		);

		Assets::enqueue();

		//The webinar listing has always shared the meeting query filter.
		$query = apply_filters( 'vczapi_meeting_list_query_args', $this->list_query( $atts ) );

		$this->export_webinar_listing( new \WP_Query( $query ), $atts );

		ob_start();
		vczapi_get_template( 'shortcode-listing.php', true, false, $atts );

		return (string) ob_get_clean();
	}

	/**
	 * Build the listing query for the given attributes.
	 *
	 * @param array $atts Sanitized attributes.
	 *
	 * @return array
	 */
	private function list_query( array $atts ): array {
		return ( new ListingQuery( ListingQuery::TYPE_WEBINARS ) )->build(
			$atts,
			[
				'paged' => Meetings::current_page(),
				'terms' => AttributeSanitizer::category_slugs( $atts['category'] ?? '' ),
			]
		);
	}

	/**
	 * Expose a listing query to the listing templates.
	 *
	 * `$GLOBALS['zoom_meetings']` is deliberately shared with
	 * {@see Meetings::list_cpt_meetings()}: `templates/shortcode-listing.php`
	 * and `templates/shortcode/zoom-listing.php` read it for both entity types.
	 *
	 * @param \WP_Query $query Listing query.
	 * @param array     $atts  Sanitized attributes.
	 *
	 * @return void
	 */
	private function export_webinar_listing( \WP_Query $query, array $atts ): void {
		unset( $GLOBALS['zoom_meetings'] );

		$query->columns = absint( $atts['cols'] );

		$GLOBALS['zoom_meetings'] = $query;
	}
}