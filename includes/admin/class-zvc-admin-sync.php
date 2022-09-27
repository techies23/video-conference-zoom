<?php

/**
 * Class for Syncing Meeting/Webinars from live to WordPress
 *
 * @since 3.5.3
 * @author Deepen Bajracharya
 */
class Zoom_Video_Conferencing_Admin_Sync {

	public function __construct() {
		//Sync Live Zoom Meetings
		add_action( 'wp_ajax_vczapi_sync_user', array( $this, 'sync' ) );
		add_action( 'in_admin_header', [ $this, 'remove_notices' ], 999 );
	}

	/**
	 * Remove all unnessary notifications from this page.
	 */
	public function remove_notices() {
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === "zoom-meetings" && isset( $_GET['page'] ) && $_GET['page'] === "zoom-video-conferencing-sync" ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}

	/**
	 * Render HTML
	 */
	public static function render() {
		if ( ! vczapi_is_oauth_active() ) {
			echo '<p>' . __( 'API keys are not configured properly ! Please configure them before syncing.', 'video-conferencing-with-zoom-api' ) . '</p>';

			return;
		}

		wp_enqueue_script( 'video-conferencing-with-zoom-api-js' );
		wp_localize_script( 'video-conferencing-with-zoom-api-js', 'vczapi_sync_i10n', array(
			'before_sync'              => __( "Fetching data.. Please wait this might take some time depending on how many Zoom Meetings you have.", "video-conferencing-with-zoom-api" ),
			'total_records_found'      => __( "Total Records Found", "video-conferencing-with-zoom-api" ),
			'total_not_synced_records' => __( "Total Not Synced Records", "video-conferencing-with-zoom-api" ),
			'select2_placeholder'      => __( "Choose meeting from here to sync.", "video-conferencing-with-zoom-api" ),
			'sync_btn'                 => __( "Sync Now", "video-conferencing-with-zoom-api" ),
			'sync_start'               => __( 'Starting sync process.. Please wait for some time. Do not close this window until completed.', "video-conferencing-with-zoom-api" ),
			'sync_error'               => __( 'Opps ! You have not selected any meeting to sync yet. Select one or two and this section will be filled by happiness for you !!', "video-conferencing-with-zoom-api" ),
			'sync_completed'           => __( 'Meeting sync has been completed !', "video-conferencing-with-zoom-api" ),
		) );

		require_once ZVC_PLUGIN_VIEWS_PATH . '/sync.php';
	}

	/**
	 * Sync Live Zoom Meetings / Webinars to Dashboard
	 */
	public function sync() {
		$type = filter_input( INPUT_POST, 'type' );

		if ( ! vczapi_is_oauth_active() ) {
			wp_send_json_error( __( 'API keys are not configured properly ! Please configure them before syncing.', 'video-conferencing-with-zoom-api' ) );
		}

		//Sync Meetings here
		if ( $type === "check" ) {
			$user_id  = filter_input( INPUT_POST, 'user_id' );
			$meetings = json_decode( zoom_conference()->listMeetings( $user_id ), true );
			if ( ! empty( $meetings ) ) {
				//Capture Error
				if ( ! empty( $meetings['code'] ) ) {
					wp_send_json_error( $meetings['message'] );
				}

				if ( ! empty( $meetings['meetings'] ) ) {
					$db_meetings = $this->get_existing_meetings();
					foreach ( $meetings['meetings'] as $k => $meeting ) {
						if ( $meeting['type'] !== 2 ) {
							unset( $meetings['meetings'][ $k ] );
						}

						//Only Keep Meetings which are not currently synced
						if ( ! empty( $db_meetings ) && in_array( $meeting['id'], $db_meetings ) ) {
							unset( $meetings['meetings'][ $k ] );
						}
					}

					$meetings['meetings'] = array_values( $meetings['meetings'] );
					update_option( '_vczapi_sync_meetings', json_encode( $meetings ) );
					wp_send_json_success( $meetings );
				} else {
					wp_send_json_error( __( "No Meetings Found !", 'video-conferencing-with-zoom-api' ) );
				}
			} else {
				wp_send_json_error( __( "No Meetings Found !", 'video-conferencing-with-zoom-api' ) );
			}
		}

		//Sync process started = 1
		if ( $type === "sync" ) {
			$meeting_id  = filter_input( INPUT_POST, 'meeting_id' );
			$db_meetings = $this->get_existing_meetings();
			if ( ! empty( $meeting_id ) && ! in_array( $meeting_id, $db_meetings ) ) {
				$cached_meetings = json_decode( get_option( '_vczapi_sync_meetings' ) );
				if ( ! empty( $cached_meetings ) ) {
					foreach ( $cached_meetings->meetings as $k => $meeting ) {
						//Check for the sent meeting ID
						if ( $meeting->id == $meeting_id ) {
							$meeting = json_decode( zoom_conference()->getMeetingInfo( $meeting_id ) );
							//If ERROR
							if ( ! empty( $meeting->code ) ) {
								wp_send_json_error( $meeting->message );
							}

							//Create Meeting in WordPress based on this meeting ID.
							$this->create_meeting( $meeting );
							break;
						}
					}
				}
			} else {
				$data = array(
					'msg'        => __( "No meeting is selected or selected meeting already exists", 'video-conferencing-with-zoom-api' ) . ': <strong>' . $meeting_id . '</strong>',
					'meeting_id' => $meeting_id
				);
				wp_send_json_error( $data );
			}
		}

		wp_die();
	}

	/**
	 * Create Actual Zoom Meeting POST TYPES
	 *
	 * @param $meeting_obj
	 */
	private function create_meeting( $meeting_obj ) {
		//Update Post Meta Values
		$post_arr = array(
			'post_title'   => $meeting_obj->topic,
			'post_content' => ! empty( $meeting_obj->agenda ) ? $meeting_obj->agenda : '',
			'post_status'  => 'publish',
			'post_type'    => 'zoom-meetings'
		);
		$post_id  = wp_insert_post( $post_arr );
		if ( ! empty( $post_id ) ) {
			//Prepare Meeting Insert Data
			$mtg_param = array(
				'userId'                    => esc_html( $meeting_obj->host_id ),
				'meeting_type'              => absint( 1 ),
				'start_date'                => vczapi_dateConverter( $meeting_obj->start_time, $meeting_obj->timezone, 'Y-m-d H:i', false ),
				'timezone'                  => esc_html( $meeting_obj->timezone ),
				'duration'                  => esc_html( $meeting_obj->duration ),
				'password'                  => ! empty( $meeting_obj->password ) ? esc_html( $meeting_obj->password ) : false,
				'meeting_authentication'    => ! empty( $meeting_obj->settings->meeting_authentication ) ? absint( $meeting_obj->settings->meeting_authentication ) : false,
				'join_before_host'          => ! empty( $meeting_obj->settings->join_before_host ) ? absint( $meeting_obj->settings->join_before_host ) : false,
				'option_host_video'         => ! empty( $meeting_obj->settings->host_video ) ? absint( $meeting_obj->settings->host_video ) : false,
				'option_participants_video' => ! empty( $meeting_obj->settings->participant_video ) ? absint( $meeting_obj->settings->participant_video ) : false,
				'option_mute_participants'  => ! empty( $meeting_obj->settings->mute_upon_entry ) ? absint( $meeting_obj->settings->mute_upon_entry ) : false,
				'option_auto_recording'     => ! empty( $meeting_obj->settings->auto_recording ) ? esc_html( $meeting_obj->settings->auto_recording ) : 'none',
				'alternative_host_ids'      => $meeting_obj->settings->alternative_hosts
			);

			update_post_meta( $post_id, '_meeting_fields', $mtg_param );
			try {
				//converted saved time from the timezone provided for meeting to UTC timezone so meetings can be better queried
				$savedDateTime     = new DateTime( $mtg_param['start_date'], new DateTimeZone( $mtg_param['timezone'] ) );
				$startDateTimezone = $savedDateTime->setTimezone( new DateTimeZone( 'UTC' ) );
				update_post_meta( $post_id, '_meeting_field_start_date_utc', $startDateTimezone->format( 'Y-m-d H:i:s' ) );
			} catch ( Exception $e ) {
				update_post_meta( $post_id, '_meeting_field_start_date_utc', $e->getMessage() );
			}

			update_post_meta( $post_id, '_meeting_zoom_details', $meeting_obj );
			update_post_meta( $post_id, '_meeting_zoom_join_url', $meeting_obj->join_url );
			update_post_meta( $post_id, '_meeting_zoom_start_url', $meeting_obj->start_url );
			update_post_meta( $post_id, '_meeting_zoom_meeting_id', $meeting_obj->id );

			//Call this action after the Zoom Meeting completion created.
			#do_action( 'vczapi_admin_after_zoom_meeting_is_created', $post_id, false );

			$data = array(
				'msg'        => __( "Successfully imported meeting with ID", 'video-conferencing-with-zoom-api' ) . ': <strong>' . $meeting_obj->id . '</strong>',
				'meeting_id' => $meeting_obj->id
			);
			wp_send_json_success( $data );
		} else {
			$data = array(
				'msg'        => __( "Failed to import meeting with ID", 'video-conferencing-with-zoom-api' ) . ': <strong>' . $meeting_obj->id . '</strong>',
				'meeting_id' => $meeting_obj->id
			);
			wp_send_json_success( $data );
		}
	}

	/**
	 * Get Existing Meeting IDs
	 *
	 * @return array
	 */
	private function get_existing_meetings() {
		$query_args           = array(
			'post_type'      => 'zoom-meetings',
			'posts_per_page' => - 1,
			'post_status'    => [ 'pending', 'draft', 'future', 'publish' ]
		);
		$meetings             = get_posts( $query_args );
		$existing_meeting_ids = array();
		if ( ! empty( $meetings ) ) {
			foreach ( $meetings as $meeting ) {
				$meeting_id             = get_post_meta( $meeting->ID, '_meeting_zoom_meeting_id', true );
				$existing_meeting_ids[] = $meeting_id;
			}
		}

		return $existing_meeting_ids;
	}
}

new Zoom_Video_Conferencing_Admin_Sync();