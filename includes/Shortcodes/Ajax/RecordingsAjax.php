<?php

namespace Codemanas\VczApi\Shortcodes\Ajax;

use Codemanas\VczApi\Helpers\MeetingType;
use Codemanas\VczApi\Requests\Zoom;
use Codemanas\VczApi\Shortcodes\AttributeSanitizer;
use Codemanas\VczApi\Shortcodes\Support\ZoomResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * AJAX endpoints behind the recording shortcodes.
 *
 * @package Codemanas\VczApi\Shortcodes\Ajax
 */
class RecordingsAjax {

	private static ?RecordingsAjax $_instance = null;

	/**
	 * @return RecordingsAjax
	 */
	public static function get_instance(): RecordingsAjax {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_get_recording', [ $this, 'get_recording' ] );
		add_action( 'wp_ajax_nopriv_get_recording', [ $this, 'get_recording' ] );

		add_action( 'wp_ajax_getRecordingByMeetingID', [ $this, 'get_recordings_by_meeting_id' ] );
		add_action( 'wp_ajax_nopriv_getRecordingByMeetingID', [ $this, 'get_recordings_by_meeting_id' ] );
	}

	/**
	 * Render the file list of a single recording into a modal.
	 *
	 * @return void
	 */
	public function get_recording(): void {
		$meeting_id   = AttributeSanitizer::recording_identifier( filter_input( INPUT_GET, 'recording_id' ) );
		$downloadable = AttributeSanitizer::yes_no( filter_input( INPUT_GET, 'downloadable' ), 'no' );

		if ( empty( $meeting_id ) ) {
			wp_die();
		}

		$recording = zoom_conference_v2()->recording()->get( $meeting_id );
		if ( is_wp_error( $recording ) ) {
			wp_send_json_error( esc_html( $recording->get_error_message() ) );
		}

		ob_start();
		vczapi_get_template( 'shortcode/recording-files.php', true, false, [
			'recording'    => $recording,
			'downloadable' => $downloadable,
		] );

		wp_send_json_success( ob_get_clean() );
	}

	/**
	 * Render every recording of a meeting, following recurring meeting instances.
	 *
	 * @return void
	 */
	public function get_recordings_by_meeting_id(): void {
		$meeting_id   = AttributeSanitizer::recording_identifier( filter_input( INPUT_GET, 'meeting_id' ) );
		$passcode     = AttributeSanitizer::passcode( filter_input( INPUT_GET, 'passcode' ) );
		$downloadable = AttributeSanitizer::yes_no( filter_input( INPUT_GET, 'downloadable' ), 'no' );

		if ( empty( $meeting_id ) ) {
			wp_send_json_error( esc_html__( 'Meeting ID is not specified', 'video-conferencing-with-zoom-api' ) );
		}

		$meeting_info = zoom_conference_v2()->meetings()->get( $meeting_id );
		if ( is_wp_error( $meeting_info ) ) {
			wp_send_json_error( esc_html( $meeting_info->get_error_message() ) );
		}

		$recordings = $this->collect_recordings( $meeting_id, $meeting_info['type'] ?? null );

		if ( empty( $recordings ) ) {
			wp_send_json_success( esc_html__( 'No recordings found.', 'video-conferencing-with-zoom-api' ) );
		}

		foreach ( $recordings as $recording ) {
			if ( is_wp_error( $recording ) ) {
				wp_send_json_error( esc_html( $recording->get_error_message() ) );
			}
		}

		ob_start();
		vczapi_get_template( 'shortcode/zoom-recordings-by-meeting.php', true, false, [
			'recordings'   => ZoomResponse::makeList( $recordings ),
			'passcode'     => $passcode,
			'downloadable' => $downloadable,
		] );

		wp_send_json_success( ob_get_clean() );
	}

	/**
	 * Collect the recording payloads of a meeting.
	 *
	 * If it's a regular meeting or webinar the meeting id is used as it seems it's
	 * more reliable (https://devforum.zoom.us/t/recording-api-issue/102992). For a
	 * recurring meeting / webinar we have to look up the past instances first.
	 *
	 * @param string      $meeting_id  Meeting ID or UUID.
	 * @param string|null $meeting_type Zoom meeting type, as returned by the API.
	 *
	 * @return array
	 */
	private function collect_recordings( string $meeting_id, $meeting_type ): array {
		if ( ! empty( $meeting_type ) && MeetingType::is_scheduled_meeting_or_webinar( $meeting_type ) ) {
			return [ zoom_conference_v2()->recording()->get( $meeting_id ) ];
		}

		$instances = $this->get_past_meeting_instances( $meeting_id );

		if ( empty( $instances['meetings'] ) ) {
			return [ zoom_conference_v2()->recording()->get( $meeting_id ) ];
		}

		$recordings = [];
		foreach ( $instances['meetings'] as $instance ) {
			if ( ! empty( $instance['uuid'] ) ) {
				$recordings[] = zoom_conference_v2()->recording()->get( $instance['uuid'] );
			}
		}

		return $recordings;
	}

	/**
	 * List the completed instances of a recurring meeting or webinar.
	 *
	 * This is the one call that still uses the transitional `Requests\Zoom` client:
	 * `GET /past_meetings/{id}/instances` has no schema in `Zoom\SchemaManager`
	 * and therefore no `Zoom\Zoom` facade equivalent yet.
	 *
	 * @param string $meeting_id Meeting ID.
	 *
	 * @return self[] Empty array on failure or when the meeting had no instances.
	 */
	private function get_past_meeting_instances( string $meeting_id ): array {
		$instances = Zoom::instance()->getPastMeetingDetails( $meeting_id );

		if ( is_wp_error( $instances ) || ! empty( $instances->code ) ) {
			return [];
		}

		// The transitional client returns stdClass items; normalize to arrays.
		return ZoomResponse::makeList( $instances->meetings ?? [] ) ?? [];
	}
}