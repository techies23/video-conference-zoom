<?php

namespace Codemanas\VczApi\Admin\Service;

/**
 * Users service.
 *
 * Wraps the Zoom API v2 user endpoints for use by the admin users screens.
 *
 * @since 4.7.0
 */
class UsersService {

	/**
	 * List users from the Zoom API v2 endpoint.
	 *
	 * @param int $page Page number.
	 * @param string $status User status. active|inactive|pending.
	 * @param int $page_size Number of records per page. Max 300.
	 *
	 * @return array
	 */
	public function list( int $page = 1, string $status = 'active', int $page_size = 300 ): array {
		$page      = max( 1, $page );
		$page_size = min( 300, max( 1, $page_size ) );

		$response = zoom_conference_v2()->users()->list( array(
			'status'      => $status,
			'page_number' => $page,
			'page_size'   => 1,
		) );

		if ( is_wp_error( $response ) ) {
			return array(
				'data'          => array(),
				'error'         => $response->get_error_message(),
				'page_count'    => 1,
				'total_records' => 0,
				'page_number'   => $page,
				'page_size'     => $page_size,
			);
		}

		$users = array();
		if ( ! empty( $response['users'] ) && is_array( $response['users'] ) ) {
			foreach ( $response['users'] as $user ) {
				$users[] = (object) $user;
			}
		}

		return array(
			'data'          => $users,
			'error'         => null,
			'page_count'    => (int) ( $response['page_count'] ?? 1 ),
			'total_records' => (int) ( $response['total_records'] ?? count( $users ) ),
			'page_number'   => (int) ( $response['page_number'] ?? $page ),
			'page_size'     => $page_size,
		);
	}

	/**
	 * Create a new Zoom user via the Zoom API v2 endpoint.
	 *
	 * @param array $data
	 *
	 * @return array|\WP_Error
	 */
	public function create( array $data = array() ): \WP_Error|array {
		$payload = array(
			'action'    => ! empty( $data['action'] ) ? $data['action'] : 'create',
			'user_info' => array(
				'email'      => $data['email'] ?? '',
				'type'       => isset( $data['type'] ) ? (int) $data['type'] : 1,
				'first_name' => $data['first_name'] ?? '',
				'last_name'  => $data['last_name'] ?? '',
			),
		);

		return zoom_conference_v2()->users()->create( $payload );
	}
}