<?php

namespace Codemanas\VczApi\Admin\Controller;

use Codemanas\VczApi\Admin\Service\MeetingImportService;
use Codemanas\VczApi\Helpers\Common;
use Codemanas\VczApi\Helpers\Templates;

/**
 * Import live Zoom meetings as posts.
 */
class SyncController {

	private static ?SyncController $instance = null;

	private MeetingImportService $meetingImportService;

	public static function get_instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->meetingImportService = new MeetingImportService();

		add_action( 'wp_ajax_vczapi_sync_user', [ $this, 'sync' ] );
		add_action( 'in_admin_header', [ $this, 'removeNotices' ], 999 );
	}

	/**
	 * Remove all unnecessary notifications from this page.
	 */
	public function removeNotices(): void {
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'zoom-meetings' && isset( $_GET['page'] ) && $_GET['page'] === 'zoom-video-conferencing-sync' ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}

	/**
	 * Render the Import screen.
	 */
	public function list(): void {
		$isActive = Common::validateZoomCredentials();
		if ( ! $isActive ) {
			echo '<p>' . esc_html__( 'API keys are not configured properly ! Please configure them before syncing.', 'video-conferencing-with-zoom-api' ) . '</p>';

			return;
		}

		wp_enqueue_script( 'vczapi-vendors-js' );
		wp_enqueue_script( 'vczapi-script' );
		wp_localize_script( 'vczapi-script', 'vczapi_sync', array(
			'i18n' => array(
				'beforeSync'         => __( 'Fetching data.. Please wait this might take some time depending on how many Zoom Meetings you have.', 'video-conferencing-with-zoom-api' ),
				'totalRecordsFound'  => __( 'Total Records Found', 'video-conferencing-with-zoom-api' ),
				'totalNotSynced'     => __( 'Total Not Synced Records', 'video-conferencing-with-zoom-api' ),
				'selectPlaceholder'  => __( 'Choose meeting from here to sync.', 'video-conferencing-with-zoom-api' ),
				'syncBtn'            => __( 'Sync Now', 'video-conferencing-with-zoom-api' ),
				'syncStart'          => __( 'Starting sync process.. Please wait for some time. Do not close this window until completed.', 'video-conferencing-with-zoom-api' ),
				'syncError'          => __( 'Opps ! You have not selected any meeting to sync yet. Select one or two and this section will be filled by happiness for you !!', 'video-conferencing-with-zoom-api' ),
				'syncCompleted'      => __( 'Meeting sync has been completed !', 'video-conferencing-with-zoom-api' ),
				'noMeetingsFound'    => __( 'No Meetings Found !', 'video-conferencing-with-zoom-api' ),
				'fetchMeetings'      => __( 'Fetch Meetings', 'video-conferencing-with-zoom-api' ),
				'selectUser'         => __( 'Select a User', 'video-conferencing-with-zoom-api' ),
				'chooseUser'         => __( 'Choose a Zoom User', 'video-conferencing-with-zoom-api' ),
				'noMeetingSelected'  => __( 'No meeting is selected or selected meeting already exists', 'video-conferencing-with-zoom-api' ),
				'importedMeetingMsg' => __( 'Successfully imported meeting with ID', 'video-conferencing-with-zoom-api' ),
				'importFailedMsg'    => __( 'Failed to import meeting with ID', 'video-conferencing-with-zoom-api' ),
				'topic'              => __( 'Topic', 'video-conferencing-with-zoom-api' ),
				'startTime'          => __( 'Start Time', 'video-conferencing-with-zoom-api' ),
				'error'              => __( 'Unable to import meetings. Please try again.', 'video-conferencing-with-zoom-api' ),
				'filterPlaceholder'  => __( 'Filter by meeting ID or topic...', 'video-conferencing-with-zoom-api' ),
				'findByIdPlaceholder'=> __( 'Search by meeting ID...', 'video-conferencing-with-zoom-api' ),
				'findBtn'            => __( 'Find Meeting', 'video-conferencing-with-zoom-api' ),
				'syncSelected'       => __( 'Sync Selected', 'video-conferencing-with-zoom-api' ),
				'selectAll'          => __( 'Select All', 'video-conferencing-with-zoom-api' ),
				'importingProgress'  => __( 'Importing %1$d of %2$d...', 'video-conferencing-with-zoom-api' ),
				'bulkCompleted'      => __( 'Import complete: %1$d imported, %2$d failed.', 'video-conferencing-with-zoom-api' ),
				'noMeetingFoundForId'=> __( 'No meeting found for that ID.', 'video-conferencing-with-zoom-api' ),
				'nSelected'          => __( '%d selected', 'video-conferencing-with-zoom-api' ),
				'searchNoResults'    => __( 'No meetings match your filter.', 'video-conferencing-with-zoom-api' ),
				'alreadyImported'    => __( 'Already imported', 'video-conferencing-with-zoom-api' ),
				'noSelection'        => __( 'Select at least one meeting to sync.', 'video-conferencing-with-zoom-api' ),
			),
		) );

		$args = array(
			'users' => Common::getDefaultHostList()
		);

		Templates::includeFile( VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/sync/index.php', $args );
	}

	/**
	 * Handle live meeting sync/import AJAX requests.
	 */
	public function sync(): void {
		check_ajax_referer( '_nonce_vczapi_security', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'video-conferencing-with-zoom-api' ) ), 403 );
		}

		if ( ! vczapi_is_oauth_active() ) {
			wp_send_json_error( __( 'API keys are not configured properly ! Please configure them before syncing.', 'video-conferencing-with-zoom-api' ) );
		}

		$type = sanitize_text_field( filter_input( INPUT_POST, 'type' ) );

		if ( $type === 'check' ) {
			$this->handleCheck();

			return;
		}

		if ( $type === 'find' ) {
			$this->handleFind();

			return;
		}

		if ( $type === 'sync' ) {
			$this->handleSync();
		}
	}

	/**
	 * Fetch available meetings for a user.
	 */
	private function handleCheck(): void {
		$user_id = sanitize_text_field( filter_input( INPUT_POST, 'user_id' ) );
		$result  = $this->meetingImportService->listAvailable( $user_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Look up a single meeting by id so it can be located even when it is not
	 * part of the user's fetched list.
	 */
	private function handleFind(): void {
		$meeting_id = sanitize_text_field( filter_input( INPUT_POST, 'meeting_id' ) );
		if ( empty( $meeting_id ) ) {
			wp_send_json_error( array(
				'msg'        => __( 'Please provide a valid Zoom meeting ID.', 'video-conferencing-with-zoom-api' ),
				'meeting_id' => $meeting_id,
			) );
		}

		$meeting = $this->meetingImportService->getMeeting( $meeting_id );
		if ( is_wp_error( $meeting ) ) {
			wp_send_json_error( array(
				'msg'        => $meeting->get_error_message(),
				'meeting_id' => $meeting_id,
			) );
		}

		wp_send_json_success( array(
			'meeting'          => $meeting,
			'meeting_id'       => $meeting_id,
			'already_imported' => $this->meetingImportService->isMeetingImported( $meeting_id ),
		) );
	}

	/**
	 * Import a single selected meeting.
	 */
	private function handleSync(): void {
		$meeting_id = sanitize_text_field( filter_input( INPUT_POST, 'meeting_id' ) );

		if ( empty( $meeting_id ) ) {
			$this->sendSyncError( $meeting_id );
		}

		if ( $this->meetingImportService->isMeetingImported( $meeting_id ) ) {
			$this->sendSyncError( $meeting_id );
		}

		$meeting = $this->meetingImportService->getMeeting( $meeting_id );
		if ( is_wp_error( $meeting ) ) {
			wp_send_json_error( array(
				'msg'        => $meeting->get_error_message(),
				'meeting_id' => $meeting_id,
			) );
		}

		$post_id = $this->meetingImportService->createMeeting( $meeting );
		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( array(
				'msg'        => $post_id->get_error_message(),
				'meeting_id' => $meeting_id,
			) );
		}

		wp_send_json_success( array(
			'msg'        => __( 'Successfully imported meeting with ID', 'video-conferencing-with-zoom-api' ) . ': <strong>' . $meeting_id . '</strong>',
			'meeting_id' => $meeting_id,
		) );
	}

	private function sendSyncError( string $meeting_id ): void {
		wp_send_json_error( array(
			'msg'        => __( 'No meeting is selected or selected meeting already exists', 'video-conferencing-with-zoom-api' ) . ': <strong>' . $meeting_id . '</strong>',
			'meeting_id' => $meeting_id,
		) );
	}
}