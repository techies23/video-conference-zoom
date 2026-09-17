<?php

namespace Codemanas\VczApi\Admin\Service;

use Codemanas\VczApi\Admin\Interface\IZoomEvent;
use Codemanas\VczApi\Zoom\Zoom;

class WebinarService implements IZoomEvent {

	/**
	 * Define Webinar Specific fields
	 *
	 * @return array
	 */
	public function getTypeSpecificFields( array $fields ): array {
		return [
			'panelists_video'        => $fields['panelists_video'] ?? '',
			'practice_session'       => $fields['practice_session'] ?? '',
			'hd_video'               => $fields['hd_video'] ?? '',
			'allow_multiple_devices' => $fields['allow_multiple_devices'] ?? '',
		];
	}

	/**
	 * Bleh
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
			$response = zoom_conference_v2()->webinars()->update( $zoom_id, $payload );
		} else {
			$response = zoom_conference_v2()->webinars()->create( $payload );
		}

		return $response;
	}
}