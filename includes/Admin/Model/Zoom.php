<?php

namespace Codemanas\VczApi\Admin\Model;

use Codemanas\VczApi\Admin\AdminController;
use Codemanas\VczApi\Admin\Interface\IZoomEvent;
use Codemanas\VczApi\Admin\Service\MeetingService;
use Codemanas\VczApi\Admin\Service\WebinarService;
use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\MeetingType;

class Zoom {

	protected string $postType;

	private static ?Zoom $instance = null;

	public static function get_instance(): ?Zoom {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->postType = AdminController::$postType;

		add_action( "save_post_{$this->postType}", [ $this, 'save' ], 10, 2 );
		add_action( 'admin_notices', [ $this, 'displayValidationNotices' ] );
	}

	/**
	 * Trigger Save
	 *
	 * @param int $post_id
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	public function save( int $post_id, \WP_Post $post ): void {
		if ( ! $this->isSaveRequestValid( $post_id ) ) {
			return;
		}

		$user_id      = sanitize_text_field( filter_input( INPUT_POST, 'user_id' ) );
		$meeting_type = filter_input( INPUT_POST, 'type', FILTER_VALIDATE_INT );
		if ( ! $this->validateRequiredFields( $user_id, $meeting_type, $post_id ) ) {
			return;
		}

		$handler      = $this->getEventHandler( (int) $meeting_type );
		$meeting_data = array_merge(
			$this->prepareCommonFields( $post_id, $post, (int) $meeting_type, $user_id ),
			$handler->getTypeSpecificFields()
		);

		do_action( 'vczapi_admin_before_zoom_meeting_is_created', $meeting_data );

		$event_label = ( $meeting_type === 2 ) ? 'webinar' : 'meeting';
		$this->saveMetaData( $post_id, $meeting_data, $event_label );

		$meeting_data = apply_filters( 'vczapi_admin_meeting_fields', $meeting_data );

		$zoom_id  = (string) Metastore::getPostMeta( $post_id, 'meeting_id' );

		//Change meeting type data to WEbinar or Meeting for API call.
		$meeting_data['type'] = MeetingType::getCptMeetingType( $meeting_type );
		$response = $handler->syncWithApi( $post, $meeting_data, $zoom_id );
		$this->persistZoomResponse( $post_id, $response );

		do_action( 'vczapi_admin_after_zoom_meeting_is_created', $post_id, $post );
	}

	/**
	 * Validates presence of essential input values.
	 */
	private function validateRequiredFields( ?string $user_id, $meeting_type, int $post_id ): bool {
		// Validate User ID presence
		if ( empty( $user_id ) ) {
			$this->addAdminNotice( __( 'Zoom Error: Meeting Host is required to create a meeting.', 'video-conferencing-with-zoom-api' ) );

			return false;
		}

		// Validate Meeting Type presence and valid integer range
		if ( false === $meeting_type || null === $meeting_type ) {
			$this->addAdminNotice( __( 'Zoom Error: Please select a valid Meeting Type.', 'video-conferencing-with-zoom-api' ) );

			return false;
		}

		return true;
	}

	/**
	 * Utility method to send WordPress admin notices on save failure.
	 */
	private function addAdminNotice( string $message ): void {
		set_transient( 'vczapi_admin_notice_' . get_current_user_id(), [
			'message' => $message,
			'type'    => 'error',
		], 45 );
	}

	/**
	 * Factory pattern to return the right strategy instance.
	 */
	private function getEventHandler( int $meeting_type ): IZoomEvent {
		return ( $meeting_type === 2 ) ? new WebinarService() : new MeetingService();
	}

	private function isSaveRequestValid( int $post_id ): bool {
		$nonce = $_POST['_vczapi_nonce'] ?? '';

		return wp_verify_nonce( $nonce, 'vczapi_save_meeting_meta' )
		       && current_user_can( 'edit_post', $post_id )
		       && ! wp_is_post_autosave( $post_id )
		       && ! wp_is_post_revision( $post_id );
	}

	private function prepareCommonFields( int $post_id, \WP_Post $post, int $meeting_type, string $user_id ): array {
		$pwd = sanitize_text_field( filter_input( INPUT_POST, 'password' ) );
		if ( ! get_option( 'zoom_api_disable_auto_meeting_pwd' ) ) {
			$pwd = ! empty( $pwd ) ? $pwd : (string) $post_id;
		}

		$duration_hour    = sanitize_text_field( filter_input( INPUT_POST, 'option_duration_hour' ) );
		$duration_minutes = sanitize_text_field( filter_input( INPUT_POST, 'option_duration_minutes' ) );
		$start_time       = gmdate( "Y-m-d\TH:i:s", strtotime( filter_input( INPUT_POST, 'start_time' ) ) );

		return [
			'topic'                        => esc_html( $post->post_title ),
			'user_id'                      => $user_id,
			'agenda'                       => wp_strip_all_tags( get_the_excerpt( $post ), true ),
			'type'                         => $meeting_type,
			'start_time'                   => sanitize_text_field( $start_time ),
			'timezone'                     => sanitize_text_field( filter_input( INPUT_POST, 'timezone' ) ),
			'duration'                     => ( ! empty( $duration_hour ) || ! empty( $duration_minutes ) ) ? vczapi_convert_to_minutes( $duration_hour, $duration_minutes ) : 40,
			'password'                     => $pwd,
			'disable_waiting_room'         => filter_input( INPUT_POST, 'disable_waiting_room' ),
			'meeting_authentication'       => filter_input( INPUT_POST, 'meeting_authentication' ),
			'host_video'                   => filter_input( INPUT_POST, 'host_video' ),
			'auto_recording'               => filter_input( INPUT_POST, 'auto_recording' ),
			'alternative_hosts'            => filter_input( INPUT_POST, 'alternative_hosts', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ),
			'site_option_logged_in'        => filter_input( INPUT_POST, 'option_logged_in' ),
			'site_option_browser_join'     => filter_input( INPUT_POST, 'option_browser_join' ),
			'site_option_enable_debug_log' => filter_input( INPUT_POST, 'option_enable_debug_logs' ),
		];
	}

	private function saveMetaData( int $post_id, array $meeting_data, string $type ): void {
		Metastore::setPostMeta( $post_id, 'meeting_fields', $meeting_data );
		Metastore::setPostMeta( $post_id, 'meeting_type', $type );

		try {
			$dt = new \DateTime( $meeting_data['start_time'], new \DateTimeZone( $meeting_data['timezone'] ) );
			$dt->setTimezone( new \DateTimeZone( 'UTC' ) );
			$start_utc = $dt->format( 'Y-m-d H:i:s' );
		} catch ( \Exception $e ) {
			$start_utc = $e->getMessage();
		}

		Metastore::setPostMeta( $post_id, 'meeting_start_date_utc', $start_utc );
	}

	private function persistZoomResponse( int $post_id, ?array $response ): void {
		if ( empty( $response ) ) {
			return;
		}

		Metastore::setPostMeta( $post_id, 'meeting_details', $response );

		if ( empty( $response->code ) ) {
			Metastore::setPostMeta( $post_id, 'meeting_join_url', $response['join_url'] ?? '' );
			Metastore::setPostMeta( $post_id, 'meeting_start_url', $response['start_url'] ?? '' );
			Metastore::setPostMeta( $post_id, 'meeting_id', $response['id'] ?? '' );
		}
	}

	/**
	 * Displays validation error messages stored during post save.
	 */
	public function displayValidationNotices(): void {
		$transient_key = 'vczapi_admin_notice_' . get_current_user_id();
		$notice        = get_transient( $transient_key );

		if ( ! $notice ) {
			return;
		}

		// Delete the transient so it only displays once
		delete_transient( $transient_key );

		$type    = esc_attr( $notice['type'] ?? 'error' );
		$message = esc_html( $notice['message'] ?? '' );

		printf(
			'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
			$type,
			$message
		);
	}
}