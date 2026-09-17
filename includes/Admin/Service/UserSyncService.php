<?php

namespace Codemanas\VczApi\Admin\Service;

use Codemanas\VczApi\Data\ZoomUsersTable;

/**
 * Class UserSyncService
 *
 * Orchestrates pulling Zoom users from the API into the custom
 * {prefix}vczapi_zoom_users cache table. Used by the manual "Sync Users"
 * button on the Users screen and by the daily WP-Cron job.
 *
 * @package Codemanas\VczApi\Admin\Service
 * @since   4.8.0
 */
class UserSyncService {

	const STATUSES = array( 'active', 'pending', 'inactive' );

	const MAX_PAGES_PER_STATUS = 500;

	/**
	 * Release the lock.
	 *
	 * @return void
	 */
	private static function release_lock(): void {
		delete_transient( 'vczapi_user_sync_lock' );
	}

	/**
	 * Try to acquire the sync lock.
	 *
	 * Prevents concurrent syncs (AJAX + cron running at the same time).
	 *
	 * @return bool
	 */
	private static function acquire_lock(): bool {
		return set_transient( 'vczapi_user_sync_lock', time(), MINUTE_IN_SECONDS * 15 );
	}

	/**
	 * Run a full sync of all Zoom users into the cache table.
	 *
	 * Pulls every status (active, pending, inactive) and follows the
	 * next_page_token pagination until all users have been fetched.
	 *
	 * @return array|\WP_Error {
	 * @type int $synced Number of user rows written.
	 * @type int $pages Number of API pages fetched.
	 * }
	 */
	public static function run_full_sync(): \WP_Error|array {
		if ( ! self::acquire_lock() ) {
			return new \WP_Error( 'vczapi_user_sync_in_progress', __( 'A Zoom user sync is already in progress.', 'video-conferencing-with-zoom-api' ) );
		}

		$synced_at = current_time( 'mysql' );
		$total     = 0;
		$pages     = 0;

		foreach ( self::STATUSES as $status ) {
			$next_page_token = null;

			do {
				$params = array(
					'status'    => $status,
					'page_size' => 300,
				);

				if ( ! empty( $next_page_token ) ) {
					$params['next_page_token'] = $next_page_token;
				}

				$response = zoom_conference_v2()->users()->list( $params );

				if ( is_wp_error( $response ) ) {
					self::release_lock();

					return $response;
				}
				$pages ++;

				if ( ! empty( $response['users'] ) && is_array( $response['users'] ) ) {
					$total += ZoomUsersTable::upsert_users( $response['users'] );
				}

				$next_page_token = ! empty( $response['next_page_token'] ) ? $response['next_page_token'] : null;

				if ( $pages > ( count( self::STATUSES ) * self::MAX_PAGES_PER_STATUS ) ) {
					self::release_lock();

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					return new \WP_Error( 'vczapi_user_sync_loop', sprintf( __( 'User sync stopped after exceeding the page limit (%d pages).', 'video-conferencing-with-zoom-api' ), self::MAX_PAGES_PER_STATUS ) );
				}
			} while ( ! empty( $next_page_token ) );
		}

		// Only remove stale rows after the entire sync completed successfully.
		ZoomUsersTable::delete_stale( $synced_at );

		self::release_lock();

		return array(
			'synced'      => $total,
			'pages'       => $pages,
			'synced_at'   => $synced_at,
			'total_users' => ZoomUsersTable::count_users(),
		);
	}

	/**
	 * AJAX handler for the manual "Sync Users" button.
	 *
	 * This is called from AJAX Controller AjaxController::get_instance();
	 *
	 * @return void
	 */
	public static function syncUsers(): void {
		check_ajax_referer( '_nonce_vczapi_security', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'video-conferencing-with-zoom-api' ) ), 403 );
		}

		$result = self::run_full_sync();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 );
		}

		wp_send_json_success( $result );
	}
}