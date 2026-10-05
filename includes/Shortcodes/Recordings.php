<?php

namespace Codemanas\VczApi\Shortcodes;

use Codemanas\VczApi\Shortcodes\Ajax\RecordingsAjax;
use Codemanas\VczApi\Shortcodes\Support\ZoomResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Recording shortcodes.
 *
 * Public callbacks: `recordings_by_user()` and `recordings_by_meeting_id()`.
 * Both AJAX endpoints live in {@see RecordingsAjax}; the methods below delegate
 * to them for back compatibility.
 *
 * @since 3.0.0
 */
class Recordings {

	/**
	 * @var Recordings|null
	 */
	private static ?Recordings $_instance = null;

	/**
	 * @return Recordings
	 */
	public static function get_instance(): ?Recordings {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * List the recordings of a host.
	 *
	 * `[zoom_recordings]`
	 *
	 * Supports the date filter and pagination that `src/public/js/shortcode.js`
	 * drives through the `fetch_recordings`, `date`, `type` and `pg` query args.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function recordings_by_user( $atts ): string {
		$atts = shortcode_atts(
			[
				'host_id'      => '',
				'per_page'     => 300,
				'downloadable' => 'no',
			],
			$atts,
			'zoom_recordings'
		);

		$host_id      = AttributeSanitizer::host_id( $atts['host_id'] );
		$per_page     = AttributeSanitizer::positive_int( $atts['per_page'], 300 );
		$downloadable = AttributeSanitizer::yes_no( $atts['downloadable'], 'no' );

		if ( empty( $host_id ) ) {
			return '<h3 class="no-host-id-defined"><strong style="color:red;">'
				. esc_html__( 'Invalid HOST ID. Please define a host ID to show recordings based on host.', 'video-conferencing-with-zoom-api' )
				. '</strong></h3>';
		}

		Assets::enqueue();

		$response = zoom_conference_v2()->recording()->list( $this->build_list_params( $host_id ) );

		unset( $GLOBALS['zoom_recordings'] );

		$error = Helpers::error_message( $response );
		if ( ! empty( $error ) ) {
			return esc_html( $error );
		}

		$recordings = ZoomResponse::make( $response );
		if ( ! $recordings ) {
			return esc_html__( 'No recordings found.', 'video-conferencing-with-zoom-api' );
		}

		$recordings['downloadable'] = 'yes' === $downloadable;

		$GLOBALS['zoom_recordings'] = $recordings;

		ob_start();
		vczapi_get_template(
			'shortcode/zoom-recordings.php',
			true,
			false,
			[
				'host_id'      => $host_id,
				'per_page'     => $per_page,
				'downloadable' => $downloadable,
			]
		);

		return (string) ob_get_clean();
	}

	/**
	 * Render the AJAX mount point for the recordings of a meeting.
	 *
	 * `[zoom_recordings_by_meeting]`
	 *
	 * The markup is populated by `src/public/js/shortcode.js`, which then calls the
	 * `getRecordingByMeetingID` AJAX endpoint.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function recordings_by_meeting_id( $atts ): string {
		$atts = shortcode_atts(
			[
				'meeting_id'   => '',
				'passcode'     => 'no',
				'downloadable' => 'no',
			],
			$atts,
			'zoom_recordings_by_meeting'
		);

		$meeting_id   = AttributeSanitizer::recording_identifier( $atts['meeting_id'] );
		$passcode     = AttributeSanitizer::passcode( $atts['passcode'] );
		$downloadable = AttributeSanitizer::yes_no( $atts['downloadable'], 'no' );

		if ( empty( $meeting_id ) ) {
			return '<h3 class="no-meeting-id-defined"><strong style="color:red;">'
				. esc_html__( 'Invalid Meeting ID.', 'video-conferencing-with-zoom-api' )
				. '</strong></h3>';
		}

		Assets::enqueue();

		return '<div class="vczapi-recordings-by-meeting-id"'
			. ' data-downloadable="' . esc_attr( $downloadable ) . '"'
			. ' data-meeting="' . esc_attr( $meeting_id ) . '"'
			. ' data-passcode="' . esc_attr( $passcode ) . '"'
			. ' data-loading="' . esc_attr__( 'Loading recordings.. Please wait..', 'video-conferencing-with-zoom-api' ) . '"></div>';
	}

	/**
	 * AJAX: render the files of a single recording into a modal.
	 *
	 * @deprecated 4.7.0 Use {@see RecordingsAjax::get_recording()}.
	 *
	 * @return void
	 */
	public function get_recordings() {
		RecordingsAjax::get_instance()->get_recording();
	}

	/**
	 * AJAX: render every recording of a meeting.
	 *
	 * @deprecated 4.7.0 Use {@see RecordingsAjax::get_recordings_by_meeting_id()}.
	 *
	 * @return void
	 */
	public function getRecordingsByMeetingID() {
		RecordingsAjax::get_instance()->get_recordings_by_meeting_id();
	}

	/**
	 * Build the `GET /users/{host_id}/recordings` parameters.
	 *
	 * The legacy `zoom_conference()->listRecording()` helper injected the two month
	 * default window and applied the `vczapi_listRecording` filter; both now happen
	 * here.
	 *
	 * @param string $host_id Zoom host ID.
	 *
	 * @return array
	 */
	private function build_list_params( string $host_id ): array {
		$params = [
			'user_id'   => $host_id,
			//`per_page` is intentionally not forwarded; the listing has always
			//requested the maximum page size.
			'page_size' => 300,
			'from'      => date( 'Y-m-d', strtotime( '-2 month' ) ),
			'to'        => date( 'Y-m-d' ),
		];

		if ( isset( $_GET['fetch_recordings'] ) ) {
			$params = array_merge( $params, $this->filter_params() );
		}

		return apply_filters( 'vczapi_listRecording', $params );
	}

	/**
	 * Date filter and pagination parameters coming from the shortcode JS.
	 *
	 * @return array
	 */
	private function filter_params(): array {
		$params = [];

		$raw_date = AttributeSanitizer::scalar( filter_input( INPUT_GET, 'date' ) );
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw_date ) ) {
			$search_date = strtotime( $raw_date );

			$params['from'] = date( 'Y-m-d', $search_date );
			$params['to']   = date( 'Y-m-t', $search_date );
		}

		$type          = AttributeSanitizer::scalar( filter_input( INPUT_GET, 'type' ) );
		$next_page     = AttributeSanitizer::scalar( filter_input( INPUT_GET, 'pg' ) );
		if ( ! empty( $next_page ) && 'recordings' === $type ) {
			$params['next_page_token'] = sanitize_text_field( $next_page );
		}

		return $params;
	}
}