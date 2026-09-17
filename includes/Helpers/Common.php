<?php

namespace Codemanas\VczApi\Helpers;

use Codemanas\VczApi\Data\Datastore;
use Codemanas\VczApi\Zoom\Auth\S2SOAuth;

/**
 * Common Helpers that would be useful everywhere.
 *
 * @since 4.7.0
 * @updated 4.7.0
 */
class Common {
	/**
	 * Fetches default Zoom Users to fill.
	 * @return array
	 */
	public static function getDefaultHostList(): array {
		$users   = Datastore::getCachedZoomUsers( 10 );
		$options = [];
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$options[ $user->id ] = sprintf( '%s %s (%s)', $user->first_name ?? '', $user->last_name ?? '', $user->email ?? '' );
			}
		}

		return $options;
	}

	/**
	 * Check if Access token exists and plugin is ready to serve zoom contents.
	 *
	 * @return bool
	 */
	public static function validateZoomCredentials(): bool {
		return S2SOAuth::get_instance()->isAccessTokenStored();
	}
}