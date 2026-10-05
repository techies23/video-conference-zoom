<?php

namespace Codemanas\VczApi\Shortcodes;

use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\Config;
use Codemanas\VczApi\Shortcodes\Ajax\MeetingListAjax;
use Codemanas\VczApi\Shortcodes\Support\ZoomResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Meeting shortcodes.
 *
 * Public callbacks: `show_meeting_by_ID()`, `show_meeting_by_postTypeID()`,
 * `list_cpt_meetings()`, `list_live_host_meetings()`. They are a public API and
 * are also reachable through `ShortcodeRegistry`.
 *
 * @since 3.0.0
 */
class Meetings {

	/**
	 * @var Meetings|null
	 */
	private static ?Meetings $_instance = null;

	/**
	 * Post type holding Zoom meetings.
	 *
	 * @var string
	 */
	private string $postType;

	/**
	 * @return Meetings
	 */
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
	 * Show a single meeting by Zoom meeting ID.
	 *
	 * `[zoom_api_link]`
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function show_meeting_by_ID( $atts ): string {
		Assets::enqueue();

		$atts = shortcode_atts(
			[
				'meeting_id' => '',
				'link_only'  => 'no',
			],
			$atts,
			'zoom_api_link'
		);

		$meeting_id = AttributeSanitizer::numeric_id( $atts['meeting_id'] );
		$link_only  = AttributeSanitizer::yes_no( $atts['link_only'], 'no' );

		unset( $GLOBALS['vanity_uri'], $GLOBALS['zoom_meetings'] );

		if ( empty( $meeting_id ) ) {
			return $this->no_id_error( __( 'No meeting id set in the shortcode', 'video-conferencing-with-zoom-api' ) );
		}

		if ( $this->meeting_ended( $meeting_id ) ) {
			return '<h3>' . esc_html__( 'This meeting has been ended by host.', 'video-conferencing-with-zoom-api' ) . '</h3>';
		}

		$response = zoom_conference_v2()->meetings()->get( $meeting_id );
		$error    = Helpers::error_message( $response );
		$meeting  = ZoomResponse::make( $response );

		$GLOBALS['vanity_uri']    = get_option( 'zoom_vanity_url' );
		$GLOBALS['zoom_meetings'] = $meeting;

		if ( ! empty( $error ) ) {
			return '<p class="dpn-error dpn-mtg-not-found">' . esc_html( $error ) . '</p>';
		}

		ob_start();

		if ( 'yes' === $link_only ) {
			Helpers::generate_link_only();
		} elseif ( $meeting ) {
			vczapi_get_template( 'shortcode/zoom-shortcode.php', true, false );
		} else {
			printf(
				/* translators: %d: Zoom meeting ID */
				esc_html__( 'Please try again ! Some error occured while trying to fetch meeting with id: %d', 'video-conferencing-with-zoom-api' ),
				absint( $meeting_id )
			);
		}

		return (string) ob_get_clean();
	}

	/**
	 * Show a meeting saved as a custom post.
	 *
	 * `[zoom_meeting_post]`
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
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

		unset( $GLOBALS['zoom'] );

		if ( empty( $post_id ) ) {
			return $this->no_id_error( __( 'No post id set in the shortcode', 'video-conferencing-with-zoom-api' ) );
		}

		Assets::enqueue();

		$this->localize_event_text();

		$meeting_query = new \WP_Query(
			[
				'p'         => $post_id,
				'post_type' => $this->postType,
			]
		);

		ob_start();

		if ( ! $meeting_query->have_posts() ) {
			return '<p>' . esc_html__( 'This post does not exist.', 'video-conferencing-with-zoom-api' ) . '</p>' . (string) ob_get_clean();
		}

		while ( $meeting_query->have_posts() ) {
			$meeting_query->the_post();

			$zoom = $this->prepare_zoom_global();

			//Set flag that this is coming from shortcode instance
			$zoom['shortcode']  = true;
			$zoom['post_id']    = get_the_id();
			$zoom['parameters'] = [
				'description' => esc_html( $description ),
				'countdown'   => esc_html( $countdown ),
				'details'     => esc_html( $details ),
			];

			$GLOBALS['zoom'] = $zoom;

			if ( vczapi_pro_version_active() && $this->has_registration_url( $zoom ) ) {
				wp_enqueue_script( 'vczapi-pro' );
			}

			if ( 'boxed' === $template ) {
				$GLOBALS['zoom']['shortcode_post_by_id'] = true;
				vczapi_get_template( 'shortcode/meeting-by-post-id.php', true, false );
			} else {
				vczapi_get_template_part( 'content', 'single-meeting' );
			}
		}

		wp_reset_postdata();

		return (string) ob_get_clean();
	}

	/**
	 * Expose the meeting status strings to the frontend bundle.
	 *
	 * Public hook since 3.0.0. Before 4.7.0 this was localized on the
	 * `video-conferencing-with-zoom-api` handle, which was never registered as a
	 * script, so the payload never reached the browser. It is now localized on the
	 * real shortcode bundle.
	 *
	 * @return void
	 */
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

	/**
	 * List meetings from the meeting custom post type.
	 *
	 * `[zoom_list_meetings]`
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function list_cpt_meetings( $atts ): string {
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

		$query = apply_filters( 'vczapi_meeting_list_query_args', $this->list_query( $atts ) );

		$this->export_meeting_listing( new \WP_Query( $query ), $atts );

		ob_start();
		vczapi_get_template( 'shortcode-listing.php', true, false, $atts );

		return (string) ob_get_clean();
	}

	/**
	 * List upcoming meetings of a Zoom host.
	 *
	 * `[zoom_list_host_meetings]`
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function list_live_host_meetings( $atts ): string {
		$atts = shortcode_atts(
			[ 'host' => '' ],
			$atts,
			'zoom_list_host_meetings'
		);

		$host = AttributeSanitizer::host_id( $atts['host'] );

		if ( empty( $host ) ) {
			return esc_html__( 'Host ID should be given when defining this shortcode.', 'video-conferencing-with-zoom-api' );
		}

		Assets::enqueue();

		$meetings = Helpers::host_listing(
			'vczapi_user_meetings_for_' . $host,
			'meetings',
			__( 'Could not retrieve meetings, check Host ID', 'video-conferencing-with-zoom-api' ),
			static function () use ( $host ) {
				return zoom_conference_v2()->meetings()->list(
					//Kept for back compat with the legacy `listMeetings()` argument filter.
					apply_filters( 'vczapi_listMeetings', [
						'user_id'   => $host,
						'page_size' => 300,
					] )
				);
			}
		);

		if ( is_wp_error( $meetings ) ) {
			return self::api_error_notice( $meetings );
		}

		ob_start();
		vczapi_get_template( 'shortcode/list-meetings-host.php', true, false, ZoomResponse::makeList( $meetings ) ?? [] );

		return (string) ob_get_clean();
	}

	/**
	 * AJAX handler for the listing pagination and filter form.
	 *
	 * @deprecated 4.7.0 Use {@see MeetingListAjax::handle()}. Kept because add-ons
	 *             have called this method directly since 3.8.5.
	 *
	 * @return void
	 */
	public function list_meeting_ajax_handler() {
		MeetingListAjax::get_instance()->handle();
	}

	/**
	 * Build the listing query for the given attributes.
	 *
	 * @param array $atts Sanitized attributes.
	 *
	 * @return array
	 */
	private function list_query( array $atts ): array {
		return ( new ListingQuery( ListingQuery::TYPE_MEETINGS ) )->build(
			$atts,
			[
				'paged' => self::current_page(),
				'terms' => AttributeSanitizer::category_slugs( $atts['category'] ?? '' ),
			]
		);
	}

	/**
	 * The page number of the current archive listing.
	 *
	 * @return int
	 */
	public static function current_page(): int {
		$var = is_front_page() ? 'page' : 'paged';

		return max( 1, absint( get_query_var( $var ) ) );
	}

	/**
	 * Expose a listing query to the listing templates.
	 *
	 * `$GLOBALS['zoom_meetings']` is a WP_Query here (as opposed to the API
	 * payload it holds in `show_meeting_by_ID()`), and the column count is read
	 * back by `templates/shortcode/zoom-listing.php`.
	 *
	 * @param \WP_Query $query Listing query.
	 * @param array     $atts  Sanitized attributes.
	 *
	 * @return void
	 */
	private function export_meeting_listing( \WP_Query $query, array $atts ): void {
		unset( $GLOBALS['zoom_meetings'] );

		$query->columns = absint( $atts['cols'] );

		$GLOBALS['zoom_meetings'] = $query;
	}

	/**
	 * Assemble the meeting payload consumed by the single meeting templates.
	 *
	 * Reads through {@see Metastore} so that meetings saved by the 4.7.0 admin
	 * (`vczapi_meeting_fields`) resolve as well as meetings saved by older
	 * releases (`_meeting_fields`). Mirrors
	 * `vczapi_prepare_single_meeting()` in `includes/template-functions.php`.
	 *
	 * @return array
	 */
	private function prepare_zoom_global(): array {
		$post_id         = get_the_id();
		$zoom            = Metastore::getPostMeta( $post_id, 'meeting_fields' );
		$meeting_details = Metastore::getPostMeta( $post_id, 'meeting_zoom_details' );

		$zoom = is_array( $zoom ) ? $zoom : [];

		$zoom['host_name'] = get_option( 'zoom_show_author' )
			? vczapi_get_meeting_author( $post_id, $meeting_details )
			: get_the_author();

		if ( ! empty( $meeting_details ) ) {
			//Legacy meta is stored as stdClass, 4.7.0 meta as an array. Wrapping it keeps
			//both `$zoom['api']['type']` and `$zoom['api']->type` working for templates.
			$zoom['api'] = ZoomResponse::make( $meeting_details );
		}

		$terms = get_the_terms( $post_id, ListingQuery::TAXONOMY );
		if ( ! empty( $terms ) ) {
			$zoom['terms'] = wp_list_pluck( $terms, 'name' );
		}

		return $zoom;
	}

	/**
	 * Whether the host already ended the meeting.
	 *
	 * @param string $meeting_id Meeting ID.
	 *
	 * @return bool
	 */
	private function meeting_ended( string $meeting_id ): bool {
		$meeting_states = get_option( 'zoom_api_meeting_options' );

		return isset( $meeting_states[ $meeting_id ]['state'] ) && 'ended' === $meeting_states[ $meeting_id ]['state'];
	}

	/**
	 * Whether the meeting details carry a pro registration URL.
	 *
	 * @param array $zoom Meeting payload.
	 *
	 * @return bool
	 */
	private function has_registration_url( array $zoom ): bool {
		$api = ZoomResponse::make( $zoom['api'] ?? [] );

		return $api && ! empty( $api['registration_url'] );
	}

	/**
	 * The "you forgot the id" markup shared by every id-based shortcode.
	 *
	 * @param string $message Reason.
	 *
	 * @return string
	 */
	private static function no_id_error( string $message ): string {
		return '<h4 class="no-meeting-id"><strong style="color:red;">'
			. esc_html__( 'ERROR: ', 'video-conferencing-with-zoom-api' )
			. '</strong>' . esc_html( $message ) . '</h4>';
	}

	/**
	 * Markup shown when the Zoom API returns an error.
	 *
	 * @param \WP_Error $error API error.
	 *
	 * @return string
	 */
	private static function api_error_notice( \WP_Error $error ): string {
		return '<strong>' . esc_html__( 'Zoom API Error:', 'video-conferencing-with-zoom-api' ) . '</strong>'
			. esc_html( $error->get_error_message() );
	}
}