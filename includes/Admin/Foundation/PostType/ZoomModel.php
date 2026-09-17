<?php

namespace Codemanas\VczApi\Admin\Foundation\PostType;

use Codemanas\VczApi\Admin\Interface\IZoomEvent;
use Codemanas\VczApi\Admin\Repository\SettingsRepository;
use Codemanas\VczApi\Admin\Service\MeetingService;
use Codemanas\VczApi\Admin\Service\WebinarService;
use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\Config;
use Codemanas\VczApi\Helpers\MeetingType;
use WP_Error;
use WP_Post;
use WP_REST_Request;

class ZoomModel {

	private const WEBINAR_TYPE = 2;
	private const DEFAULT_DURATION_MINUTES = 40;

	protected string $postType;
	private static ?ZoomModel $instance = null;

	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	public function __construct() {
		$this->postType = Config::get( 'post_type' );

		add_action( "save_post_{$this->postType}", [ $this, 'save' ], 10, 2 );
		add_action( 'admin_notices', [ $this, 'displayValidationNotices' ] );
		add_action( 'before_delete_post', [ $this, 'delete' ] );
		add_filter( "rest_pre_insert_{$this->postType}", [ $this, 'preInsert' ], 10, 3 );
		add_action( "rest_after_insert_{$this->postType}", [ $this, 'restAfterInsert' ], 10, 3 );
	}

	/**
	 * Trigger Save (classic metabox flow).
	 */
	public function save( int $post_id, WP_Post $post ): void {
		if ( ! $this->isSaveRequestValid( $post_id ) ) {
			return;
		}

		$fields = MeetingValidator::normalize( $this->gatherPostFields() );

		if ( ! $this->validateAndNotice( $fields ) ) {
			return;
		}

		$meeting_type = (int) $fields['type'];

		$this->syncWithApiAndPersist( $post_id, $post, $fields, $meeting_type );
	}

	/**
	 * Validate meeting fields before a REST (Gutenberg) save reaches the DB.
	 *
	 * Returning a WP_Error here aborts the save entirely, so the post stays in
	 * its previous status until the meeting details are fixed.
	 *
	 * @param WP_Post       $prepared_post
	 * @param WP_REST_Request $request
	 * @param bool          $creating
	 *
	 * @return WP_Post|WP_Error
	 */
	public function preInsert( $prepared_post, WP_REST_Request $request, bool $creating ) {
		// Block-editor autosaves hit the /autosaves endpoint; never reject them
		// while the user is mid-edit (they are also sync-free).
		if ( strpos( $request->get_route(), '/autosaves' ) !== false ) {
			return $prepared_post;
		}

		$meta           = (array) $request->get_param( 'meta' );
		$meeting_fields = $meta['vczapi_meeting_fields'] ?? null;

		if ( ! is_array( $meeting_fields ) || empty( $meeting_fields ) ) {
			return $prepared_post;
		}

		$errors = MeetingValidator::validate( MeetingValidator::normalize( $meeting_fields ) );
		if ( ! empty( $errors ) ) {
			return new WP_Error(
				'vczapi_meeting_validation',
				__( 'Zoom meeting details could not be saved. Please fix the highlighted fields and try again.', 'video-conferencing-with-zoom-api' ),
				[ 'errors' => $errors ]
			);
		}

		return $prepared_post;
	}

	/**
	 * After a REST (Gutenberg) save, sync the meeting with the Zoom API and
	 * persist the response. Meta (vczapi_meeting_fields) is already written by
	 * the REST controller before this fires.
	 *
	 * @param WP_Post       $post
	 * @param WP_REST_Request $request
	 * @param bool          $creating
	 */
	public function restAfterInsert( WP_Post $post, WP_REST_Request $request, bool $creating ): void {
		if ( $post->post_type !== $this->postType ) {
			return;
		}

		$fields = Metastore::getPostMeta( $post->ID, 'meeting_fields' );
		if ( ! is_array( $fields ) || empty( $fields ) || empty( $fields['user_id'] ) ) {
			return;
		}

		$meeting_type = (int) ( $fields['type'] ?? 1 );
		if ( ! in_array( $meeting_type, [ 1, self::WEBINAR_TYPE ], true ) ) {
			return;
		}

		$this->syncWithApiAndPersist( $post->ID, $post, $fields, $meeting_type );
	}

	/**
	 * Shared build -> save meta -> sync Zoom API -> persist response pipeline.
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 * @param array   $fields
	 * @param int     $meeting_type
	 */
	private function syncWithApiAndPersist( int $post_id, WP_Post $post, array $fields, int $meeting_type ): void {
		$eventHandler = $this->getEventHandler( $meeting_type );
		$meeting_data = $this->buildMeetingData( $post, $fields, $meeting_type );

		do_action( 'vczapi_admin_before_zoom_meeting_is_created', $meeting_data );

		$event_label = ( $meeting_type === self::WEBINAR_TYPE ) ? 'webinar' : 'meeting';
		$this->saveMetaData( $post_id, $meeting_data, $event_label );

		$meeting_data = apply_filters( 'vczapi_admin_meeting_fields', $meeting_data );

		$zoom_id = (string) Metastore::getPostMeta( $post_id, 'meeting_id' );

		// Update meeting type format for API payload
		$meeting_data['type'] = MeetingType::getCptMeetingType( $meeting_type );
		$response             = $eventHandler->syncWithApi( $post, $meeting_data, $zoom_id );

		$this->persistZoomResponse( $post_id, $response );

		if ( $this->isErrorResponse( $response ) ) {
			$this->addAdminNotice( $this->getErrorMessage( $response ) );
		}

		do_action( 'vczapi_admin_after_zoom_meeting_is_created', $post_id, $post );
	}

	/**
	 * Validate fields and surface errors via transient admin notices (classic path).
	 *
	 * @param array $fields
	 *
	 * @return bool
	 */
	private function validateAndNotice( array $fields ): bool {
		$errors = MeetingValidator::validate( $fields );

		if ( empty( $errors ) ) {
			return true;
		}

		foreach ( $errors as $error ) {
			$this->addAdminNotice( $error );
		}

		return false;
	}

	private function isSaveRequestValid( int $post_id ): bool {
		$nonce = $_POST['_vczapi_nonce'] ?? '';

		return wp_verify_nonce( $nonce, 'vczapi_save_meeting_meta' )
		       && current_user_can( 'edit_post', $post_id )
		       && ! wp_is_post_autosave( $post_id )
		       && ! wp_is_post_revision( $post_id );
	}

	/**
	 * Collect all meeting fields from the classic metabox $_POST request.
	 *
	 * @return array
	 */
	private function gatherPostFields(): array {
		$fields = [];

		foreach ( [
			'user_id',
			'agenda',
			'start_time',
			'timezone',
			'password',
			'disable_waiting_room',
			'waiting_room',
			'meeting_authentication',
			'host_video',
			'auto_recording',
			'join_before_host',
			'participant_video',
			'mute_upon_entry',
			'panelists_video',
			'practice_session',
			'hd_video',
			'allow_multiple_devices',
		] as $key ) {
			$value     = filter_input( INPUT_POST, $key );
			$fields[ $key ] = ( $value === null ) ? '' : sanitize_text_field( $value );
		}

		$fields['type']                = filter_input( INPUT_POST, 'type', FILTER_VALIDATE_INT );
		$fields['jbh_time']            = absint( filter_input( INPUT_POST, 'jbh_time' ) );
		$fields['alternative_hosts']   = filter_input( INPUT_POST, 'alternative_hosts', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ?? [];
		$fields['option_duration_hour']    = $this->getPostInput( 'option_duration_hour' );
		$fields['option_duration_minutes'] = $this->getPostInput( 'option_duration_minutes' );

		return $fields;
	}

	/**
	 * Build the Zoom API payload from a normalized fields array.
	 *
	 * Works for both the classic ($_POST) and REST (meta) sources.
	 *
	 * @param WP_Post $post
	 * @param array   $fields
	 * @param int     $meeting_type
	 *
	 * @return array
	 */
	private function buildMeetingData( WP_Post $post, array $fields, int $meeting_type ): array {
		$raw_start_time = $fields['start_time'] ?? '';
		$start_time     = $raw_start_time ? gmdate( "Y-m-d\TH:i:s", strtotime( $raw_start_time ) ) : '';

		$common = [
			'topic'                  => esc_html( $post->post_title ),
			'user_id'                => (string) ( $fields['user_id'] ?? '' ),
			'agenda'                 => $fields['agenda'] ?? '',
			'type'                   => $meeting_type,
			'start_time'             => $start_time,
			'timezone'               => $fields['timezone'] ?? '',
			'duration'               => $this->calculateDuration( $fields ),
			'password'               => $this->resolveMeetingPassword( $post->ID, $fields['password'] ?? '' ),
			'disable_waiting_room'   => $fields['disable_waiting_room'] ?? '',
			'meeting_authentication' => $fields['meeting_authentication'] ?? '',
			'host_video'             => $fields['host_video'] ?? '',
			'auto_recording'         => $fields['auto_recording'] ?? '',
			'alternative_hosts'      => $fields['alternative_hosts'] ?? [],
		];

		$eventHandler = $this->getEventHandler( $meeting_type );

		return array_merge( $common, $eventHandler->getTypeSpecificFields( $fields ) );
	}

	private function calculateDuration( array $fields ): int {
		if ( isset( $fields['duration'] ) && is_numeric( $fields['duration'] ) ) {
			return max( 1, (int) $fields['duration'] );
		}

		$hours   = $fields['option_duration_hour'] ?? '';
		$minutes = $fields['option_duration_minutes'] ?? '';

		if ( ! empty( $hours ) || ! empty( $minutes ) ) {
			return vczapi_convert_to_minutes( $hours, $minutes );
		}

		return self::DEFAULT_DURATION_MINUTES;
	}

	private function resolveMeetingPassword( int $post_id, string $password ): string {
		if ( ! get_option( 'zoom_api_disable_auto_meeting_pwd' ) ) {
			return ! empty( $password ) ? $password : (string) $post_id;
		}

		return $password;
	}

	private function saveMetaData( int $post_id, array $meeting_data, string $type ): void {
		Metastore::setPostMeta( $post_id, 'meeting_fields', $meeting_data );
		Metastore::setPostMeta( $post_id, 'meeting_type', $type );

		$start_utc = '';
		if ( ! empty( $meeting_data['start_time'] ) && ! empty( $meeting_data['timezone'] ) ) {
			try {
				$dt        = new \DateTimeImmutable( $meeting_data['start_time'], new \DateTimeZone( $meeting_data['timezone'] ) );
				$start_utc = $dt->setTimezone( new \DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );
			} catch ( \Exception $e ) {
				$start_utc = $e->getMessage();
			}
		}

		Metastore::setPostMeta( $post_id, 'meeting_start_date_utc', $start_utc );
	}

	private function persistZoomResponse( int $post_id, $response ): void {
		if ( empty( $response ) ) {
			return;
		}

		// Standardize output handling whether response is object or array
		$response_array = (array) $response;
		Metastore::setPostMeta( $post_id, 'meeting_zoom_details', $response_array );

		if ( $this->isErrorResponse( $response ) ) {
			return;
		}

		Metastore::setPostMeta( $post_id, 'meeting_join_url', $response_array['join_url'] ?? '' );
		Metastore::setPostMeta( $post_id, 'meeting_start_url', $response_array['start_url'] ?? '' );
		Metastore::setPostMeta( $post_id, 'meeting_id', $response_array['id'] ?? '' );
	}

	/**
	 * Determine whether a Zoom API response represents an error.
	 *
	 * @param mixed $response
	 *
	 * @return bool
	 */
	private function isErrorResponse( $response ): bool {
		if ( empty( $response ) || ! is_object( $response ) && ! is_array( $response ) ) {
			return false;
		}

		return is_object( $response ) ? ! empty( $response->code ) : ! empty( $response['code'] );
	}

	/**
	 * Extract a human readable Zoom API error message.
	 *
	 * @param mixed $response
	 *
	 * @return string
	 */
	private function getErrorMessage( $response ): string {
		$message = is_object( $response ) ? ( $response->message ?? '' ) : ( $response['message'] ?? '' );

		/* translators: %s: Zoom API error message */
		return sprintf( esc_html__( 'Zoom Error: %s', 'video-conferencing-with-zoom-api' ), esc_html( $message ) );
	}

	public function displayValidationNotices(): void {
		$transient_key = 'vczapi_admin_notice_' . get_current_user_id();
		$notice        = get_transient( $transient_key );

		if ( ! $notice ) {
			return;
		}

		$messages = is_array( $notice['messages'] ?? null ) ? $notice['messages'] : [ $notice['message'] ?? '' ];

		delete_transient( $transient_key );

		$type = esc_attr( $notice['type'] ?? 'error' );

		foreach ( $messages as $message ) {
			printf(
				'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
				$type,
				esc_html( $message )
			);
		}
	}

	public function delete( int $post_id ): void {
		if ( get_post_type( $post_id ) !== $this->postType ) {
			return;
		}

		if ( ! empty( SettingsRepository::getSetting( 'delete_zoom_meeting' ) ) ) {
			return;
		}

		$meeting_id      = Metastore::getPostMeta( $post_id, 'meeting_id' );
		$meeting_details = Metastore::getPostMeta( $post_id, 'meeting_zoom_details' );

		if ( empty( $meeting_id ) ) {
			return;
		}

		do_action( 'vczapi_before_delete_meeting', $meeting_id );

		$is_webinar = is_array( $meeting_details ) && isset( $meeting_details['meeting_type'] ) && $meeting_details['meeting_type'] === self::WEBINAR_TYPE;

		if ( $is_webinar ) {
			zoom_conference_v2()->webinars()->delete( $meeting_id );
		} else {
			zoom_conference_v2()->meetings()->delete( $meeting_id );
		}

		do_action( 'vczapi_after_delete_meeting' );
	}

	private function getEventHandler( int $meeting_type ): IZoomEvent {
		return ( $meeting_type === self::WEBINAR_TYPE ) ? new WebinarService() : new MeetingService();
	}

	private function addAdminNotice( string $message ): void {
		$transient_key = 'vczapi_admin_notice_' . get_current_user_id();
		$existing      = get_transient( $transient_key );
		$messages      = ! empty( $existing['messages'] ) && is_array( $existing['messages'] ) ? $existing['messages'] : [];

		if ( ! empty( $existing['message'] ) && ! in_array( $existing['message'], $messages, true ) ) {
			$messages[] = $existing['message'];
		}
		$messages[] = $message;

		set_transient( $transient_key, [
			'messages' => array_values( array_unique( $messages ) ),
			'type'     => 'error',
		], 45 );
	}

	private function getPostInput( string $key ): string {
		return sanitize_text_field( filter_input( INPUT_POST, $key ) ?? '' );
	}
}