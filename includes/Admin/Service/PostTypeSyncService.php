<?php

namespace Codemanas\VczApi\Admin\Service;

use Codemanas\VczApi\Admin\Interface\IZoomEvent;
use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\MeetingType;
use WP_Post;

class PostTypeSyncService {

	private const WEBINAR_TYPE = 2;

	public function sync( int $post_id, WP_Post $post, array $fields, int $meeting_type ): void {
		$eventHandler = $this->getEventHandler( $meeting_type );
		$meeting_data = $this->buildMeetingData( $post, $fields, $meeting_type );

		do_action( 'vczapi_admin_before_zoom_meeting_is_created', $meeting_data );

		$event_label = ( $meeting_type === self::WEBINAR_TYPE ) ? 'webinar' : 'meeting';
		$this->saveMetaData( $post_id, $meeting_data, $event_label );

		$meeting_data['type'] = MeetingType::getCptMeetingType( $meeting_type );
		$meeting_data         = apply_filters( 'vczapi_admin_meeting_fields', $meeting_data, $fields );
		$zoom_id              = (string) Metastore::getPostMeta( $post_id, 'meeting_id' );
		$response             = $eventHandler->syncWithApi( $post, $meeting_data, $zoom_id );

		$this->persistZoomResponse( $post_id, $response );

		do_action( 'vczapi_admin_after_zoom_meeting_is_created', $post_id, $post );
	}

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
			'duration'               => (int) ( $fields['duration'] ?? 40 ),
			'password'               => $this->resolveMeetingPassword( $post->ID, $fields['password'] ?? '' ),
			'disable_waiting_room'   => $fields['disable_waiting_room'] ?? '',
			'meeting_authentication' => $fields['meeting_authentication'] ?? '',
			'host_video'             => $fields['host_video'] ?? '',
			'auto_recording'         => $fields['auto_recording'] ?? '',
			'alternative_hosts'      => $fields['alternative_hosts'] ?? [],
		];

		$eventHandler = $this->getEventHandler( $meeting_type );

		return array_merge( $common, $eventHandler->getTypeSpecificFields() );
	}

	private function resolveMeetingPassword( int $post_id, string $password ): string {
		$autoPwd = Metastore::get_plugin_settings( "disable_auto_pwd_generation" );
		if ( ! $autoPwd ) {
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

		$response_array = (array) $response;
		Metastore::setPostMeta( $post_id, 'meeting_zoom_details', $response_array );
		Metastore::setPostMeta( $post_id, 'meeting_join_url', $response_array['join_url'] ?? '' );
		Metastore::setPostMeta( $post_id, 'meeting_start_url', $response_array['start_url'] ?? '' );
		Metastore::setPostMeta( $post_id, 'meeting_id', $response_array['id'] ?? '' );
	}

	private function getEventHandler( int $meeting_type ): IZoomEvent {
		return ( $meeting_type === self::WEBINAR_TYPE ) ? new WebinarService() : new MeetingService();
	}
}