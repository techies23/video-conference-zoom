<?php

namespace Codemanas\VczApi\Admin\Service;

/**
 * Recordings service.
 *
 * Wraps the Zoom API v2 recording endpoints for use by the admin recordings screen.
 *
 * @since 4.7.0
 */
class RecordingService {

	/**
	 * List cloud recordings for a given Zoom user/host.
	 *
	 * Uses the new schema-driven API via zoom_conference_v2()->recording()->list().
	 *
	 * @param string $host_id   Zoom host/user id (id, email or "me").
	 * @param string $from      Start date in YYYY-MM-DD format.
	 * @param string $to        End date in YYYY-MM-DD format.
	 * @param int    $page_size Records per page. Max 300.
	 *
	 * @return array {
	 *     @type array   $data            List of meeting recording objects.
	 *     @type string  $error           Error message if any.
	 *     @type int     $total_records   Total number of recordings returned.
	 *     @type string  $next_page_token Next page token (if more results available).
	 *     @type string  $from            Start date used for the API call.
	 *     @type string  $to              End date used for the API call.
	 * }
	 */
	public function list( string $host_id, string $from = '', string $to = '', int $page_size = 300 ): array {
		$page_size = min( 300, max( 1, $page_size ) );

		if ( empty( $host_id ) ) {
			return array(
				'data'            => array(),
				'error'           => __( 'Please select a host to load recordings.', 'video-conferencing-with-zoom-api' ),
				'total_records'   => 0,
				'next_page_token' => null,
				'from'            => $from,
				'to'              => $to,
			);
		}

		$params = array(
			'user_id'   => $host_id,
			'page_size' => $page_size,
		);

		if ( ! empty( $from ) ) {
			$params['from'] = $from;
		}

		if ( ! empty( $to ) ) {
			$params['to'] = $to;
		}

		$response = zoom_conference_v2()->recording()->list( $params );

		if ( is_wp_error( $response ) ) {
			return array(
				'data'            => array(),
				'error'           => $response->get_error_message(),
				'total_records'   => 0,
				'next_page_token' => null,
				'from'            => $from,
				'to'              => $to,
			);
		}

		$meetings = ! empty( $response['meetings'] ) && is_array( $response['meetings'] ) ? $response['meetings'] : array();

		return array(
			'data'            => $meetings,
			'error'           => null,
			'total_records'   => isset( $response['total_records'] ) ? (int) $response['total_records'] : count( $meetings ),
			'next_page_token' => ! empty( $response['next_page_token'] ) ? $response['next_page_token'] : null,
			'from'            => $from,
			'to'              => $to,
		);
	}
}