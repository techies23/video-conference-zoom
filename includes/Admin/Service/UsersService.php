<?php

namespace Codemanas\VczApi\Admin\Service;

use Codemanas\VczApi\Data\ZoomUsersTable;

/**
 * Users service.
 *
 * Reads Zoom users from the custom {prefix}vczapi_zoom_users cache table.
 *
 * @since 4.7.0
 */
class UsersService {

	/**
	 * List users from the custom zoom users cache table.
	 *
	 * @param int $page Page number.
	 * @param string $status User status. active|pending|inactive.
	 * @param int $page_size Number of records per page. Max 300.
	 *
	 * @return array
	 */
	public function list( int $page = 1, string $status = 'active', int $page_size = 20 ): array {
		if ( ! ZoomUsersTable::table_exists() ) {
			ZoomUsersTable::create_table();
		}

		if ( ! in_array( $status, array( 'active', 'pending', 'inactive' ), true ) ) {
			$status = 'active';
		}

		return ZoomUsersTable::get_users( array(
			'page'      => $page,
			'page_size' => $page_size,
			'status'    => $status,
			'order_by'  => 'email',
			'order'     => 'ASC',
		) );
	}
}