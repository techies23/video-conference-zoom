<?php

namespace Codemanas\VczApi\Admin\Services;

use Codemanas\VczApi\Admin\Interfaces\IZoomEvent;
use Codemanas\VczApi\Zoom\Zoom;

class WebinarService implements IZoomEvent {

	/**
	 * Define Webinar Specific fields
	 *
	 * @return array
	 */
	public function getTypeSpecificFields(): array {
		return [
			'panelists_video'        => filter_input( INPUT_POST, 'panelists_video' ),
			'practice_session'       => filter_input( INPUT_POST, 'practice_session' ),
			'hd_video'               => filter_input( INPUT_POST, 'hd_video' ),
			'allow_multiple_devices' => filter_input( INPUT_POST, 'allow_multiple_devices' ),
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
	public function syncWithApi( \WP_Post $post, array $payload, string $zoom_id ): ?object {
		$is_update = ! empty( $zoom_id );
		$zoomApi = new Zoom();
		if ( $is_update ) {
			$response = $zoomApi->webinars()->update( $zoom_id, $payload );
		} else {
			$response = $zoomApi->webinars()->create( $payload );
		}

		return $response;
	}
}