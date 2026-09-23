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
	 * @param int $page_size Number of records per page. Max 300.
	 * @param string $search
	 * @param string $status User status. active|pending|inactive.
	 * @param string $sortBy
	 * @param string $sortOrder
	 *
	 * @return array
	 */
	public function list(
		int $page = 1,
		int $page_size = 20,
		string $search = '',
		string $status = 'active',
		string $sortBy = 'email',
		string $sortOrder = 'ASC'
	): array {
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
			'search'    => $search,
			'order_by'  => $sortBy,
			'order'     => $sortOrder,
		) );
	}
}