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
		add_action( 'wp_ajax_vczapi_get_user_list', [ $this, 'getUserList' ] );
	}

	public function getUserList(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'video-conferencing-with-zoom-api' ) ), 403 );
		}

		$page       = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;
		$per_page   = isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 10;
		$search     = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
		$status     = ( isset( $_GET['status'] ) && $_GET['status'] === 'pending' ) ? 'pending' : 'active';
		$sort_by    = isset( $_GET['sort_by'] ) ? sanitize_sql_orderby( $_GET['sort_by'] ) : 'id';
		$sort_order = ( isset( $_GET['sort_order'] ) && strtolower( $_GET['sort_order'] ) === 'desc' ) ? 'DESC' : 'ASC';
		$result = $this->usersService->list( $page, $per_page, $search, $status, $sort_by, $sort_order );

		if ( ! empty( $result['error'] ) ) {
			wp_send_json_error( array(
				'message' => $result['error']
			), 400 );
		}

		// 4. Send JSON Response
		wp_send_json_success( array(
			'users'       => $result['data'] ?? [],
			'total_count' => $result['total_records'] ?? 0,
			'page_number' => $result['page_number'] ?? $page,
			'page_count'  => $result['page_count'] ?? 1,
			'last_synced' => ZoomUsersTable::get_last_sync_time(),
			'user_count'  => ZoomUsersTable::count_users(),
		) );
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

		$args = array(
			'last_synced'  => ZoomUsersTable::get_last_sync_time(),
			'user_count'   => ZoomUsersTable::count_users(),
		);
		Templates::includeFile( VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/users/list.php', $args );
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