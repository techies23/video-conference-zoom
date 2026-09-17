<?php

namespace Codemanas\VczApi\Data;

/**
 * Class Datastore
 *
 * Will eventually handle all database functions
 *
 * @package Codemanas\VczApi\Data
 * @created March 2nd, 2021
 * @author Deepen Bajracharya
 */
class Datastore {

	/**
	 * Get Cached Zoom User
	 *
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function getCachedZoomUsers( int $limit = 0 ): array {
		$users = ZoomUsersTable::get_all_as_objects( $limit );

		return apply_filters( 'vczapi_users_list', $users );
	}
}