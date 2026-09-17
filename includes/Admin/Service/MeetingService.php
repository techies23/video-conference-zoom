<?php

namespace Codemanas\VczApi\Admin\Service;

use Codemanas\VczApi\Admin\Interface\IZoomEvent;
use Codemanas\VczApi\Zoom\Zoom;

class MeetingService implements IZoomEvent {

	/**
	 * Define meeting specific fields
	 * @return array
	 */
	public function getTypeSpecificFields( array $fields ): array {
		return [
			'join_before_host'  => $fields['join_before_host'] ?? '',
			'jbh_time'          => isset( $fields['jbh_time'] ) ? absint( $fields['jbh_time'] ) : 0,
			'participant_video' => $fields['participant_video'] ?? '',
			'mute_upon_entry'   => $fields['mute_upon_entry'] ?? '',
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
		if ( $is_update ) {
			$response = zoom_conference_v2()->meetings()->update( $zoom_id, $payload );
		} else {
			$response = zoom_conference_v2()->meetings()->create( $payload );
		}

		return $response;
	}
}