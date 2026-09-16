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

	public static function getCachedZoomUsers() {
		require_once VCZAPI_PLUGIN_DIR_PATH . 'includes/Data/ZoomUsersTable.php';
		$users = \Codemanas\VczApi\Data\ZoomUsersTable::get_all_as_objects();

		return apply_filters( 'vczapi_users_list', $users );
	}
}