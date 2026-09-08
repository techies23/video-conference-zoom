<?php

namespace Codemanas\VczApi\Admin\Services;

use Codemanas\VczApi\Admin\Interfaces\IZoomEvent;

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
	 * @return object|mixed|null
	 */
	public function syncWithApi( \WP_Post $post, array $payload, string $zoom_id ): ?object {
		$api       = zoom_conference();
		$is_update = ! empty( $zoom_id );

		$params = \Zoom_Video_Conferencing_Admin_Webinars::prepare_webinar( $payload, $post );
		if ( $is_update ) {
			$response = json_decode( $api->updateWebinar( $zoom_id, $params ) );

			if ( empty( $response->code ) ) {
				$response = json_decode( $api->getWebinarInfo( $zoom_id ) );
			}
		} else {
			$response = json_decode( $api->createAWebinar( $payload['userId'], $params ) );
		}

		return $response;
	}
}