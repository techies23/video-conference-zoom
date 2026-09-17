<?php

namespace Codemanas\VczApi\Admin\Controller;

use Codemanas\VczApi\Admin\Service\RecordingService;
use Codemanas\VczApi\Data\Datastore;
use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Data\ZoomUsersTable;
use Codemanas\VczApi\Helpers\Common;
use Codemanas\VczApi\Helpers\Templates;

/**
 * Register the main post type.
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
	}

	public function list(): void {
		wp_enqueue_script( 'vczapi-vendors-js' );
		wp_enqueue_script( 'vczapi-script' );

		$current_page = isset( $_GET['pg'] ) ? absint( $_GET['pg'] ) : 1;

		$currentUserId     = get_current_user_id();
		$zoom_user_host_id = Metastore::getUserMeta( $currentUserId, 'user_host_id' );
		$users             = Common::getDefaultHostList();
		if ( isset( $_GET['host_id'] ) ) {
			$host_id = $_GET['host_id'];
		} else if ( ! empty( $zoom_user_host_id ) ) {
			$host_id = $zoom_user_host_id;
		}

		$args = [
			'data'          => [],
			'error'         => [],
			'current_page'  => [],
			'page_count'    => [],
			'default_users' => $users
		];

		if ( ! empty( $host_id ) ) {
			$data                 = $this->recordingService->list( $current_page );
			$args['data']         = $data['data'];
			$args['error']        = $data['error'];
			$args['current_page'] = $data['current_page'];
			$args['page_count']   = $data['page_count'];

			if ( isset( $_POST['check-recordings'] ) && isset( $_POST['date'] ) ) {
				$search_date        = strtotime( $_POST['date'] );
				$from               = date( 'Y-m-d', $search_date );
				$to                 = date( 'Y-m-t', $search_date );
				$postParams['from'] = $from;
				$postParams['to']   = $to;
				$recordings         = json_decode( zoom_conference()->listRecording( $host_id, $postParams ) );
			} else {
				$recordings = json_decode( zoom_conference()->listRecording( $host_id ) );
			}
		}

		if ( ! empty( $recordings ) && ! empty( $recordings->code ) ) {
			echo '<p>' . $recordings->message . '</p>';
		} else {
			Templates::includeFile( VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/recordings/list.php', $args );
		}
	}
}