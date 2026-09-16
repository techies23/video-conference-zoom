<?php

namespace Codemanas\VczApi\Admin\Service;

use Codemanas\VczApi\Admin\Interface\IZoomEvent;
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
	public function syncWithApi( \WP_Post $post, array $payload, string $zoom_id ): ?array {
		$is_update = ! empty( $zoom_id );
		$zoomApi   = new Zoom();
		if ( $is_update ) {
			$response = $zoomApi->meetings()->update( $zoom_id, $payload );
		} else {
			$response = $zoomApi->meetings()->create( $payload );
		}

		return $response;
	}
}