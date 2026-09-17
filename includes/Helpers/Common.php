<?php

namespace Codemanas\VczApi\Helpers;

use Codemanas\VczApi\Data\Datastore;

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
}