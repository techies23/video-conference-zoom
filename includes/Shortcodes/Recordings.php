<?php

namespace Codemanas\VczApi\Shortcodes;

class Recordings {

	/**
	 * Instance
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * Create only one instance so that it may not Repeat
	 *
	 * @since 2.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_nopriv_get_recording', array( $this, 'get_recordings' ) );
		add_action( 'wp_ajax_get_recording', array( $this, 'get_recordings' ) );
	}

	/**
	 * Get Recordings via AJAX
	 */
	public function get_recordings() {
		$meeting_id = filter_input( INPUT_GET, 'recording_id' );
		$downloable = filter_input( INPUT_GET, 'downlable' );
		if ( ! empty( $meeting_id ) ) {
			ob_start();
			?>
            <div class="vczapi-modal-content">
                <div class="vczapi-modal-body">
                    <span class="vczapi-modal-close">&times;</span>
					<?php
					$recording = json_decode( zoom_conference()->recordingsByMeeting( $meeting_id ) );
					if ( ! empty( $recording->recording_files ) ) {
						foreach ( $recording->recording_files as $files ) {
							if ( ! apply_filters( 'vczapi_show_recording_chat_file', false ) && isset( $files->recording_type ) && $files->recording_type == 'chat_file' ) {
								continue;
							}
							?>
                            <ul class="vczapi-modal-list vczapi-modal-list-<?php echo $files->id; ?>">
                                <li><strong><?php _e( 'File Type', 'video-conferencing-with-zoom-api' ); ?>: </strong> <?php echo $files->file_type; ?></li>
                                <li><strong><?php _e( 'File Size', 'video-conferencing-with-zoom-api' ); ?>: </strong> <?php echo vczapi_filesize_converter( $files->file_size ); ?></li>
								<?php
								if ( true == apply_filters( 'vczapi_recordings_show_password', false ) && isset( $recording->password ) && ! empty( $recording->password ) ) {
									?>
                                    <li><strong><?php _e( 'Password:', 'video-conferencing-with-zoom-api' ); ?></strong> <?php echo $recording->password; ?></li>
								<?php }
								?>
                                <li><strong><?php _e( 'Play', 'video-conferencing-with-zoom-api' ); ?>: </strong><a href="<?php echo $files->play_url; ?>" target="_blank"><?php _e( 'Play', 'video-conferencing-with-zoom-api' ); ?></a></li>

								<?php if ( ! empty( $downloable ) && $downloable ) { ?>
                                    <li><strong><?php _e( 'Download', 'video-conferencing-with-zoom-api' ); ?>: </strong>
                                        <a href="<?php echo $files->download_url; ?>" target="_blank"><?php _e( 'Download', 'video-conferencing-with-zoom-api' ); ?></a>
                                    </li>
								<?php } ?>
                            </ul>
							<?php
						}
					} else {
						echo "N/A";
					}
					?>
                </div>
            </div>
			<?php
			$result = ob_get_clean();
			wp_send_json_success( $result );
		}

		wp_die();
	}

	/**
	 * Recordings API Shortcode
	 *
	 * @param $atts
	 *
	 * @return bool|false|string
	 */
	public function recordings_by_user( $atts ) {
		$atts = shortcode_atts(
			array(
				'host_id'      => '',
				'per_page'     => 300,
				'downloadable' => 'no',
			),
			$atts,
			'zoom_recordings'
		);

		$downloadable = ( ! empty( $atts['downloadable'] ) && $atts['downloadable'] === "yes" ) ? true : false;
		if ( empty( $atts['host_id'] ) ) {
			echo '<h3 class="no-host-id-defined"><strong style="color:red;">' . __( 'Invalid HOST ID. Please define a host ID to show recordings based on host.', 'video-conferencing-with-zoom-api' ) . '</h3>';

			return false;
		}

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'video-conferencing-with-zoom-api-datable-responsive' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-responsive-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-dt-responsive-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-shortcode-js' );
		wp_localize_script( 'video-conferencing-with-zoom-api-shortcode-js', 'vczapi_recordings_data', array(
			'downloadable' => $downloadable,
			'loading'      => __( 'Loading recordings.. Please wait..', 'video-conferencing-with-zoom-api' ),
		) );

		$postParams = array(
			'page_size' => 300 //$atts['per_page'] disbled for now
		);

		if ( isset( $_GET['fetch_recordings'] ) && isset( $_GET['date'] ) ) {
			$search_date        = strtotime( $_GET['date'] );
			$from               = date( 'Y-m-d', $search_date );
			$to                 = date( 'Y-m-t', $search_date );
			$postParams['from'] = $from;
			$postParams['to']   = $to;

			//Pagination
			if ( isset( $_GET['pg'] ) && isset( $_GET['type'] ) && $_GET['type'] === "recordings" ) {
				$postParams['next_page_token'] = $_GET['pg'];
			}
		}

		$recordings = json_decode( zoom_conference()->listRecording( $atts['host_id'], $postParams ) );

		unset( $GLOBALS['zoom_recordings'] );
		ob_start();
		if ( ! empty( $recordings ) ) {
			if ( ! empty( $recordings->code ) && ! empty( $recordings->message ) ) {
				echo $recordings->message;
			} else {
				$GLOBALS['zoom_recordings']               = $recordings;
				$GLOBALS['zoom_recordings']->downloadable = $downloadable;
				vczapi_get_template( 'shortcode/zoom-recordings.php', true, false );
			}
		} else {
			_e( "No recordings found.", "video-conferencing-with-zoom-api" );
		}

		return ob_get_clean();
	}

	/**
	 * Show recordings based on Meeting ID
	 *
	 * @param $atts
	 *
	 * @return bool|false|string
	 */
	public function recordings_by_meeting_id( $atts ) {
		$atts    = shortcode_atts(
			array(
				'meeting_id'   => '',
				'downloadable' => 'no',
				'cache'        => 'true',
			),
			$atts,
			'zoom_recordings'
		);
		$post_id = get_the_ID();

		if ( empty( $atts['meeting_id'] ) ) {
			echo '<h3 class="no-meeting-id-defined"><strong style="color:red;">' . __( 'Invalid Meeting ID.', 'video-conferencing-with-zoom-api' ) . '</h3>';

			return false;
		}

		$meeting_id = $atts['meeting_id'];
		wp_enqueue_style( 'video-conferencing-with-zoom-api-datable-responsive' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-responsive-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-dt-responsive-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-shortcode-js' );

		$recordings        = [];
		$flush_cache       = filter_input( INPUT_GET, 'flush_cache' );
		$cached_recordings = ! empty( $atts['cache'] ) && $atts['cache'] == "true" ? Helpers::get_post_cache( $post_id, '_vczapi_shortcode_recordings_by_meeting_id' ) : false;

		ob_start();
		unset( $GLOBALS['zoom_recordings'] );
		unset( $GLOBALS['zoom_recordings_is_downloadable'] );

		if ( ! empty( $cached_recordings ) && isset( $cached_recordings[ $meeting_id ] ) && $flush_cache != 'yes' ) {
			//if cached recordings exist use that
			$recordings = $cached_recordings[ $meeting_id ];
		} else {
			$all_past_meetings = json_decode( zoom_conference()->getPastMeetingDetails( $meeting_id ) );
			if ( isset( $all_past_meetings->meetings ) && ! empty( $all_past_meetings->meetings ) && ! isset( $all_past_meetings->code ) ) {
				//loop through all instance of past / completed meetings and get recordings
				foreach ( $all_past_meetings->meetings as $meeting ) {
					$recordings[] = json_decode( zoom_conference()->recordingsByMeeting( $meeting->uuid ) );
				}
				Helpers::set_post_cache( $post_id, '_vczapi_shortcode_recordings_by_meeting_id', [ $meeting_id => $recordings ], 86400 );
			} else {
				$recordings[] = json_decode( zoom_conference()->recordingsByMeeting( $meeting_id ) );
				Helpers::set_post_cache( $post_id, '_vczapi_shortcode_recordings_by_meeting_id', [ $meeting_id => $recordings ], 86400 );
			}
		}

		if ( ! empty( $recordings ) ) {
			if ( ! empty( $recordings->code ) && ! empty( $recordings->message ) ) {
				echo $recordings->message;
			} else {
				foreach ( $recordings as $recording ) {
					if ( ! empty( $recording->recording_files ) ) {
						$GLOBALS['zoom_recordings'][]               = $recording;
						$GLOBALS['zoom_recordings_is_downloadable'] = ( ! empty( $atts['downloadable'] ) && $atts['downloadable'] === "yes" ) ? true : false;
					}
				}
			}
		}

		if ( ! empty( $GLOBALS['zoom_recordings'] ) ) {
			vczapi_get_template( 'shortcode/zoom-recordings-by-meeting.php', true, false );
		} else {
			_e( "No recordings found.", "video-conferencing-with-zoom-api" );
			?>
            <a href="<?php echo esc_url( add_query_arg( [ 'flush_cache' => 'yes' ], get_the_permalink() ) ); ?>"><?php _e( 'Check for latest' ); ?></a>
			<?php
		}


		return ob_get_clean();
	}
}