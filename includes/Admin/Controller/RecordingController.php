<?php

namespace Codemanas\VczApi\Admin\Controller;

use Codemanas\VczApi\Admin\Service\RecordingService;
use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\Common;
use Codemanas\VczApi\Helpers\Templates;

/**
 * Recordings admin screen controller.
 */
class RecordingController {

	private static ?RecordingController $instance = null;
	private RecordingService $recordingService;

	public static function get_instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->recordingService = new RecordingService();
		add_action( 'wp_ajax_vczapi_list_recordings', [ $this, 'getRecordings' ] );
	}

	public function list(): void {
		wp_enqueue_script( 'vczapi-vendors-js' );
		wp_enqueue_script( 'vczapi-script' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		$currentUserId     = get_current_user_id();
		$zoom_user_host_id = Metastore::getUserMeta( $currentUserId, 'user_host_id' );

		if ( isset( $_GET['host_id'] ) ) {
			$host_id = sanitize_text_field( wp_unslash( $_GET['host_id'] ) );
		} else if ( ! empty( $zoom_user_host_id ) ) {
			$host_id = $zoom_user_host_id;
		} else {
			$host_id = '';
		}

		$users = Common::getDefaultHostList();

		wp_localize_script( 'vczapi-script', 'vczapi_recordings', array(
			'host_id' => $host_id,
			'i18n'    => array(
				'loading'         => __( 'Loading recordings...', 'video-conferencing-with-zoom-api' ),
				'noRecordings'    => __( 'No recordings found.', 'video-conferencing-with-zoom-api' ),
				'noHost'          => __( 'Please select a host to load recordings.', 'video-conferencing-with-zoom-api' ),
				'error'           => __( 'Unable to load recordings. Please try again.', 'video-conferencing-with-zoom-api' ),
				'viewRecordings'  => __( 'View Recordings', 'video-conferencing-with-zoom-api' ),
				'fileType'        => __( 'File Type', 'video-conferencing-with-zoom-api' ),
				'fileSize'        => __( 'File Size', 'video-conferencing-with-zoom-api' ),
				'play'            => __( 'Play', 'video-conferencing-with-zoom-api' ),
				'download'        => __( 'Download', 'video-conferencing-with-zoom-api' ),
				'formatBytes'     => __( 'B', 'video-conferencing-with-zoom-api' ),
				'formatKiloBytes' => __( 'KB', 'video-conferencing-with-zoom-api' ),
				'formatMegaBytes' => __( 'MB', 'video-conferencing-with-zoom-api' ),
				'formatGigaBytes' => __( 'GB', 'video-conferencing-with-zoom-api' ),
				'formatTeraBytes' => __( 'TB', 'video-conferencing-with-zoom-api' ),
			),
		) );

		$args = array(
			'host_id'       => $host_id,
			'default_users' => $users,
		);

		Templates::includeFile( VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/recordings/list.php', $args );
	}

	/**
	 * AJAX handler compatible with VanillaDataTable component.
	 */
	public function getRecordings(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'video-conferencing-with-zoom-api' ) ), 403 );
		}

		$nonce = sanitize_text_field( filter_input( INPUT_GET, 'nonce' ) );
		if ( $nonce && ! wp_verify_nonce( $nonce, 'vczapi-nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'video-conferencing-with-zoom-api' ) ), 403 );
		}

		$host_id = sanitize_text_field( filter_input( INPUT_GET, 'host_id' ) );
		$date    = sanitize_text_field( filter_input( INPUT_GET, 'date' ) );

		// If host_id is missing, return empty dataset with 200 OK status instead of 400 error
		if ( empty( $host_id ) ) {
			wp_send_json_success( array(
				'items'       => [],
				'meetings'    => [],
				'total'       => 0,
				'total_count' => 0,
				'message'     => __( 'Please select a host to load recordings.', 'video-conferencing-with-zoom-api' ),
			) );
		}

		if ( ! empty( $date ) ) {
			$from = date( 'Y-m-01', strtotime( $date ) );
			$to   = date( 'Y-m-t', strtotime( $date ) );
		} else {
			$from = date( 'Y-m-d', strtotime( '-2 month', time() ) );
			$to   = date( 'Y-m-d' );
		}

		$per_page        = isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 300;
		$next_page_token = isset( $_GET['next_page_token'] ) ? sanitize_text_field( $_GET['next_page_token'] ) : '';
		$search          = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
		$sort_by         = isset( $_GET['sort_by'] ) ? sanitize_text_field( $_GET['sort_by'] ) : 'start_time';
		$sort_order      = isset( $_GET['sort_order'] ) ? sanitize_text_field( $_GET['sort_order'] ) : 'desc';

		$result = $this->recordingService->list( $host_id, $from, $to, $per_page, $next_page_token, $search, $sort_by, $sort_order );

		if ( ! empty( $result['error'] ) ) {
			wp_send_json_error( array( 'message' => $result['error'] ), 500 );
		}

		$meetings      = $result['data'] ?? [];
		$total_records = $result['total_records'] ?? count( $meetings );

		wp_send_json_success( array(
			'items'           => $meetings,
			'meetings'        => $meetings,
			'total'           => $total_records,
			'total_count'     => $total_records,
			'next_page_token' => $result['next_page_token'] ?? null,
			'from'            => $from,
			'to'              => $to,
		) );
	}
}