<?php

namespace Codemanas\VczApi\Admin\Foundation\PostType;

use Codemanas\VczApi\Admin\Interface\IZoomEvent;
use Codemanas\VczApi\Admin\Repository\SettingsRepository;
use Codemanas\VczApi\Admin\Service\MeetingService;
use Codemanas\VczApi\Admin\Service\WebinarService;
use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\Config;
use Codemanas\VczApi\Helpers\MeetingType;

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
	}

	/**
	 * Trigger Save
	 */
	public function save( int $post_id, \WP_Post $post ): void {
		if ( ! $this->isSaveRequestValid( $post_id ) ) {
			return;
		}

		$user_id      = $this->getPostInput( 'user_id' );
		$meeting_type = filter_input( INPUT_POST, 'type', FILTER_VALIDATE_INT );

		if ( ! $this->validateRequiredFields( $user_id, $meeting_type ) ) {
			return;
		}

		$meeting_type_int = (int) $meeting_type;
		$eventHandler     = $this->getEventHandler( $meeting_type_int );

		$meeting_data = array_merge(
			$this->prepareCommonFields( $post_id, $post, $meeting_type_int, $user_id ),
			$eventHandler->getTypeSpecificFields()
		);

		do_action( 'vczapi_admin_before_zoom_meeting_is_created', $meeting_data );

		$event_label = ( $meeting_type_int === self::WEBINAR_TYPE ) ? 'webinar' : 'meeting';
		$this->saveMetaData( $post_id, $meeting_data, $event_label );

		$meeting_data = apply_filters( 'vczapi_admin_meeting_fields', $meeting_data );

		$zoom_id = (string) Metastore::getPostMeta( $post_id, 'meeting_id' );

		// Update meeting type format for API payload
		$meeting_data['type'] = MeetingType::getCptMeetingType( $meeting_type_int );
		$response             = $eventHandler->syncWithApi( $post, $meeting_data, $zoom_id );

		$this->persistZoomResponse( $post_id, $response );

		do_action( 'vczapi_admin_after_zoom_meeting_is_created', $post_id, $post );
	}

	/**
	 * Validates presence of essential input values.
	 */
	private function validateRequiredFields( ?string $user_id, $meeting_type ): bool {
		if ( empty( $user_id ) ) {
			$this->addAdminNotice( __( 'Zoom Error: Meeting Host is required to create a meeting.', 'video-conferencing-with-zoom-api' ) );

			return false;
		}

		if ( $meeting_type === false || $meeting_type === null ) {
			$this->addAdminNotice( __( 'Zoom Error: Please select a valid Meeting Type.', 'video-conferencing-with-zoom-api' ) );

			return false;
		}

		return true;
	}

	private function isSaveRequestValid( int $post_id ): bool {
		$nonce = $_POST['_vczapi_nonce'] ?? '';

		return wp_verify_nonce( $nonce, 'vczapi_save_meeting_meta' )
		       && current_user_can( 'edit_post', $post_id )
		       && ! wp_is_post_autosave( $post_id )
		       && ! wp_is_post_revision( $post_id );
	}

	private function prepareCommonFields( int $post_id, \WP_Post $post, int $meeting_type, string $user_id ): array {
		$raw_start_time = $this->getPostInput( 'start_time' );
		$start_time     = $raw_start_time ? gmdate( "Y-m-d\TH:i:s", strtotime( $raw_start_time ) ) : '';

		return [
			'topic'                  => esc_html( $post->post_title ),
			'user_id'                => $user_id,
			'agenda'                 => $this->getPostInput( 'agenda' ),
			'type'                   => $meeting_type,
			'start_time'             => $start_time,
			'timezone'               => $this->getPostInput( 'timezone' ),
			'duration'               => $this->calculateDuration(),
			'password'               => $this->resolveMeetingPassword( $post_id ),
			'disable_waiting_room'   => $this->getPostInput( 'disable_waiting_room' ),
			'meeting_authentication' => $this->getPostInput( 'meeting_authentication' ),
			'host_video'             => $this->getPostInput( 'host_video' ),
			'auto_recording'         => $this->getPostInput( 'auto_recording' ),
			'alternative_hosts'      => filter_input( INPUT_POST, 'alternative_hosts', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ?? [],
		];
	}

	private function calculateDuration(): int {
		$hours   = $this->getPostInput( 'option_duration_hour' );
		$minutes = $this->getPostInput( 'option_duration_minutes' );

		if ( ! empty( $hours ) || ! empty( $minutes ) ) {
			return vczapi_convert_to_minutes( $hours, $minutes );
		}

		return self::DEFAULT_DURATION_MINUTES;
	}

	private function resolveMeetingPassword( int $post_id ): string {
		$password = $this->getPostInput( 'password' );

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

		$has_error = is_object( $response ) ? ! empty( $response->code ) : ! empty( $response['code'] );

		if ( ! $has_error ) {
			Metastore::setPostMeta( $post_id, 'meeting_join_url', $response_array['join_url'] ?? '' );
			Metastore::setPostMeta( $post_id, 'meeting_start_url', $response_array['start_url'] ?? '' );
			Metastore::setPostMeta( $post_id, 'meeting_id', $response_array['id'] ?? '' );
		}
	}

	public function displayValidationNotices(): void {
		$transient_key = 'vczapi_admin_notice_' . get_current_user_id();
		$notice        = get_transient( $transient_key );

		if ( ! $notice ) {
			return;
		}

		delete_transient( $transient_key );

		$type    = esc_attr( $notice['type'] ?? 'error' );
		$message = esc_html( $notice['message'] ?? '' );

		printf(
			'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
			$type,
			$message
		);
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
		set_transient( 'vczapi_admin_notice_' . get_current_user_id(), [
			'message' => $message,
			'type'    => 'error',
		], 45 );
	}

	private function getPostInput( string $key ): string {
		return sanitize_text_field( filter_input( INPUT_POST, $key ) ?? '' );
	}
}