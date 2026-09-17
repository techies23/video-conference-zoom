<?php

namespace Codemanas\VczApi\Admin\Controller;

use Codemanas\VczApi\Admin\Service\UsersService;
use Codemanas\VczApi\Admin\Service\UserSyncService;
use Codemanas\VczApi\Data\Datastore;
use Codemanas\VczApi\Data\ZoomUsersTable;
use Codemanas\VczApi\Helpers\Templates;

/**
 * Register user page.
 */
class UserController {

	private static ?UserController $instance = null;

	private UsersService $usersService;

	public static function get_instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->usersService = new UsersService();

		add_action( 'wp_ajax_vczapi_sync_zoom_users', [ UserSyncService::class, 'syncUsers' ] );
		add_action( 'wp_ajax_vczapi_get_user_by_query', [ $this, 'getUsersByQuery' ] );
	}

	/**
	 * List users page.
	 */
	public function list(): void {
		wp_enqueue_script( 'vczapi-script' );
		wp_localize_script( 'vczapi-script', 'vczapi_user_sync', array(
			'i18n' => array(
				'syncing' => __( 'Syncing... this may take a while.', 'video-conferencing-with-zoom-api' ),
				'done'    => __( 'Synced {users} users across {pages} pages.', 'video-conferencing-with-zoom-api' ),
				'syncNow' => __( 'Sync Users from Zoom', 'video-conferencing-with-zoom-api' ),
				'error'   => __( 'Sync failed. Please try again.', 'video-conferencing-with-zoom-api' ),
			),
		) );

		$status       = ( isset( $_GET['status'] ) && $_GET['status'] === 'pending' ) ? 'pending' : 'active';
		$current_page = isset( $_GET['pg'] ) ? absint( $_GET['pg'] ) : 1;

		$data = $this->usersService->list( $current_page, $status );
		$args = array(
			'data'         => $data['data'],
			'error'        => $data['error'],
			'current_page' => $data['page_number'],
			'page_count'   => $data['page_count'],
			'last_synced'  => ZoomUsersTable::get_last_sync_time(),
			'user_count'   => ZoomUsersTable::count_users(),
		);

		if ( $status === 'pending' ) {
			Templates::includeFile( VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/users/pending.php', $args );
		} else {
			Templates::includeFile( VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/users/list.php', $args );
		}
	}

	/**
	 * Get List of users in array for choices.js processing.
	 *
	 * @called from AjaxController.php
	 */
	public function getUsersByQuery(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$search_string = filter_input( INPUT_GET, 'q' );
		$results       = [];

		$zoom_users = ZoomUsersTable::search_for_picker( (string) $search_string );
		if ( ! empty( $zoom_users ) ) {
			$results = array_merge( $results, $zoom_users );
		}

		wp_send_json( array( 'results' => $results ) );
	}
}