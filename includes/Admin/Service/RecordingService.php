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
	 * @param string $host_id Zoom host/user id.
	 * @param string $from Start date in YYYY-MM-DD format.
	 * @param string $to End date in YYYY-MM-DD format.
	 * @param int $page_size Records per page. Max 300.
	 * @param string $next_page_token Next page token from Zoom.
	 * @param string $search Filter search string.
	 * @param string $sort_by Field to sort by.
	 * @param string $sort_order Ascending or descending order.
	 *
	 * @return array
	 */
	public function list(
		string $host_id,
		string $from = '',
		string $to = '',
		int $page_size = 300,
		string $next_page_token = '',
		string $search = '',
		string $sort_by = 'start_time',
		string $sort_order = 'desc'
	): array {
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

		if ( ! empty( $next_page_token ) ) {
			$params['next_page_token'] = $next_page_token;
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

		// Apply client-side search filtering on the Zoom results if a search term was passed
		if ( ! empty( $search ) ) {
			$search_lower = strtolower( $search );
			$meetings     = array_values( array_filter( $meetings, function ( $meeting ) use ( $search_lower ) {
				$topic = isset( $meeting['topic'] ) ? strtolower( $meeting['topic'] ) : '';
				$id    = isset( $meeting['id'] ) ? (string) $meeting['id'] : '';

				return strpos( $topic, $search_lower ) !== false || strpos( $id, $search_lower ) !== false;
			} ) );
		}

		// Apply client-side sorting on the returned meetings array
		if ( ! empty( $sort_by ) && ! empty( $meetings ) ) {
			usort( $meetings, function ( $a, $b ) use ( $sort_by, $sort_order ) {
				$valA = $a[ $sort_by ] ?? '';
				$valB = $b[ $sort_by ] ?? '';

				if ( $valA === $valB ) {
					return 0;
				}

				$res = ( $valA < $valB ) ? - 1 : 1;

				return ( strtolower( $sort_order ) === 'desc' ) ? - $res : $res;
			} );
		}

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