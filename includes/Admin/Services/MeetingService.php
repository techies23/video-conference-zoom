<?php

namespace Codemanas\VczApi\Admin\Services;

use Codemanas\VczApi\Admin\Interfaces\IZoomEvent;
use Codemanas\VczApi\Zoom\Zoom;

class MeetingService implements IZoomEvent {

	/**
	 * Define meeting specific fields
	 * @return array
	 */
	public function getTypeSpecificFields(): array {
		return [
			'join_before_host'  => filter_input( INPUT_POST, 'join_before_host' ),
			'jbh_time'          => absint( filter_input( INPUT_POST, 'jbh_time' ) ),
			'participant_video' => filter_input( INPUT_POST, 'participant_video' ),
			'mute_upon_entry'   => filter_input( INPUT_POST, 'mute_upon_entry' ),
		];
	}

	/**
	 * Sync data with APi
	 *
	 * @param \WP_Post $post
	 * @param array $payload
	 * @param string $zoom_id
	 *
	 * @return object|null
	 */
	public function syncWithApi( \WP_Post $post, array $payload, string $zoom_id ): ?object {
		$api       = zoom_conference();
		$is_update = ! empty( $zoom_id );
		$zoomApi   = new Zoom();
		if ( $is_update ) {
			$params   = \Zoom_Video_Conferencing_Admin_Meetings::prepare_update( $zoom_id, $payload, $post );
			$response = json_decode( $api->updateMeetingInfo( $params ) );

			if ( empty( $response->code ) ) {
				$response = json_decode( $api->getMeetingInfo( $zoom_id ) );
			}
		} else {
			$response = $zoomApi->meetings()->create( $payload );
		}

		return $response;
	}
}