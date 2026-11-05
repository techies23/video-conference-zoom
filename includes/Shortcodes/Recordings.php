<?php

namespace Codemanas\VczApi\Shortcodes;

use Codemanas\VczApi\Helpers\MeetingType;
use Codemanas\VczApi\Helpers\RecordingToken;
use Codemanas\VczApi\Requests\Zoom;

class Recordings {
	private static ?Recordings $_instance = null;

	/**
	 * Create only one instance so that it may not Repeat
	 *
	 * @since 2.0.0
	 */
	public static function get_instance(): ?Recordings {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_nopriv_get_recording', array( $this, 'get_recordings' ) );
		add_action( 'wp_ajax_get_recording', array( $this, 'get_recordings' ) );

		//Ajax fetch for Meeting by ID
		add_action( 'wp_ajax_nopriv_getRecordingByMeetingID', [ $this, 'getRecordingsByMeetingID' ] );
		add_action( 'wp_ajax_getRecordingByMeetingID', [ $this, 'getRecordingsByMeetingID' ] );
	}

	/**
	 * Get a scalar value.
	 *
	 * @param mixed  $value   Value to normalize.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	private function get_scalar_value( $value, string $default = '' ): string {
		if ( is_array( $value ) || is_object( $value ) ) {
			return $default;
		}

		return (string) $value;
	}

	/**
	 * Normalize a yes/no value.
	 *
	 * @param mixed  $value   Value to normalize.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	private function sanitize_yes_no_attribute( $value, string $default = 'no' ): string {
		$value = strtolower( $this->get_scalar_value( $value, $default ) );

		return in_array( $value, [ 'yes', 'no' ], true ) ? $value : $default;
	}

	/**
	 * Sanitize a Zoom host identifier.
	 *
	 * Zoom host IDs are expected to be alphanumeric API identifiers or email-like
	 * identifiers. This intentionally strips shortcode/HTML syntax.
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return string
	 */
	private function sanitize_zoom_host_id( $value ): string {
		return preg_replace( '/[^A-Za-z0-9_\-@.]/', '', $this->get_scalar_value( $value ) );
	}

	/**
	 * Sanitize a Zoom meeting/recording identifier.
	 *
	 * Recording lookup may use numeric meeting IDs or Zoom UUID-like values. Zoom
	 * UUIDs can contain characters such as slash, plus, equals, underscore, and
	 * hyphen, so this is intentionally broader than numeric meeting ID handling.
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return string
	 */
	private function sanitize_zoom_recording_identifier( $value ): string {
		$value = $this->get_scalar_value( $value );

		return preg_replace( '/[^A-Za-z0-9_\-@.\/+=]/', '', $value );
	}

	/**
	 * Sanitize a Zoom meeting passcode.
	 *
	 * Zoom meeting passcodes are limited to 10 characters and may contain
	 * alphanumeric characters and special characters. Avoid display-oriented
	 * sanitizers because passcodes are credentials, not HTML display text.
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return string
	 */
	private function sanitize_zoom_passcode( $value ): string {
		$passcode = $this->get_scalar_value( $value );

		$passcode = str_replace(
			[ "\r", "\n", "\t", ']' ],
			'',
			$passcode
		);

		return function_exists( 'mb_substr' ) ? mb_substr( $passcode, 0, 10 ) : substr( $passcode, 0, 10 );
	}

	/**
	 * Sanitize an integer shortcode/ajax value.
	 *
	 * @param mixed $value   Value to sanitize.
	 * @param int   $default Default value.
	 *
	 * @return int
	 */
	private function sanitize_positive_int( $value, int $default = 0 ): int {
		$value = absint( $value );

		return ! empty( $value ) ? $value : $default;
	}

	/**
	 * Generic error used for every rejected recording request.
	 *
	 * Using one message keeps the response from revealing whether a meeting,
	 * recording or Zoom API resource exists.
	 *
	 * @return string
	 */
	private function generic_error(): string {
		return esc_html__( 'Invalid request.', 'video-conferencing-with-zoom-api' );
	}

	/**
	 * Resolve the post that is currently rendering a recording shortcode.
	 *
	 * @return int
	 */
	private function get_current_post_id(): int {
		$post_id = get_the_ID();

		if ( empty( $post_id ) ) {
			$queried = get_queried_object();
			if ( $queried instanceof \WP_Post ) {
				$post_id = $queried->ID;
			}
		}

		if ( empty( $post_id ) && ! empty( $GLOBALS['post']->ID ) ) {
			$post_id = (int) $GLOBALS['post']->ID;
		}

		return absint( $post_id );
	}

	/**
	 * Verify a recording token and its bound page.
	 *
	 * The token proves that the recording shortcode was rendered on a page.
	 * The page is re-checked on every request so unpublished, password
	 * protected or login-only pages stop working immediately.
	 *
	 * @param string $token  Token supplied by the browser.
	 * @param string $scope  Required token scope.
	 * @param string $id     Required meeting/recording identifier.
	 *
	 * @return array|false Verified payload on success.
	 */
	private function verify_recording_token( string $token, string $scope, string $id ) {
		$payload = RecordingToken::verify( $token );

		if ( empty( $payload ) || $scope !== $payload['scope'] || $id !== (string) $payload['id'] ) {
			return false;
		}

		$post_id = ! empty( $payload['post'] ) ? absint( $payload['post'] ) : 0;
		if ( empty( $post_id ) ) {
			return false;
		}

		$post = get_post( $post_id );
		if ( empty( $post ) || 'trash' === $post->post_status ) {
			return false;
		}

		// Draft, pending and private content is only readable by capable users.
		if ( 'publish' !== $post->post_status && ! current_user_can( 'read_post', $post->ID ) ) {
			return false;
		}

		// Password protected pages must not disclose recordings via AJAX.
		if ( post_password_required( $post ) ) {
			return false;
		}

		// Honour the per-meeting "must be logged in" option.
		if ( 'zoom-meetings' === $post->post_type ) {
			$meeting_fields = get_post_meta( $post->ID, '_meeting_fields', true );
			if ( ! empty( $meeting_fields['site_option_logged_in'] ) && ! is_user_logged_in() ) {
				return false;
			}
		}

		return $payload;
	}

	/**
	 * Get Recordings via AJAX
	 */
	public function get_recordings() {
		$requested_id = $this->get_scalar_value( filter_input( INPUT_GET, 'recording_id' ) );
		$meeting_id   = $this->sanitize_zoom_recording_identifier( $requested_id );
		$token        = $this->get_scalar_value( filter_input( INPUT_GET, 'token' ) );

		// A valid token is required before the Zoom API is contacted.
		if ( empty( $meeting_id ) || empty( $token ) ) {
			wp_send_json_error( $this->generic_error() );
		}

		$payload = $this->verify_recording_token( $token, 'recording', trim( $requested_id ) );
		if ( empty( $payload ) ) {
			wp_send_json_error( $this->generic_error() );
		}

		// The token is authoritative for display options.
		$downloadable = $this->sanitize_yes_no_attribute( $payload['downloadable'] ?? 'no', 'no' );

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

							$file_type    = ! empty( $files->file_type ) ? $files->file_type : '';
							$file_id      = ! empty( $files->id ) ? $files->id : '';
							$file_size    = ! empty( $files->file_size ) ? $files->file_size : 0;
							$play_url     = ! empty( $files->play_url ) ? $files->play_url : '';
							$download_url = ! empty( $files->download_url ) ? $files->download_url : '';
							?>
                            <ul class="vczapi-modal-list vczapi-modal-list__<?php echo esc_attr( strtolower( $file_type ) ); ?> vczapi-modal-list-<?php echo esc_attr( $file_id ); ?>">
                                <li><strong><?php esc_html_e( 'File Type', 'video-conferencing-with-zoom-api' ); ?>: </strong> <?php echo esc_html( $file_type ); ?></li>
                                <li><strong><?php esc_html_e( 'File Size', 'video-conferencing-with-zoom-api' ); ?>: </strong> <?php echo esc_html( vczapi_filesize_converter( $file_size ) ); ?></li>
								<?php
								if ( true == apply_filters( 'vczapi_recordings_show_password', false ) && isset( $recording->password ) && ! empty( $recording->password ) ) {
									?>
                                    <li><strong><?php esc_html_e( 'Password:', 'video-conferencing-with-zoom-api' ); ?></strong> <?php echo esc_html( $recording->password ); ?></li>
								<?php }
								?>
                                <li><strong><?php esc_html_e( 'Play', 'video-conferencing-with-zoom-api' ); ?>: </strong>
                                    <a href="<?php echo esc_url( $play_url ); ?>"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="vczapi-recording__play-link"
                                    ><?php esc_html_e( 'Play', 'video-conferencing-with-zoom-api' ); ?></a></li>

								<?php if ( 'yes' === $downloadable ) { ?>
                                    <li><strong><?php esc_html_e( 'Download', 'video-conferencing-with-zoom-api' ); ?>: </strong>
                                        <a href="<?php echo esc_url( $download_url ); ?>"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="vczapi-recording__download-link"
                                        ><?php esc_html_e( 'Download', 'video-conferencing-with-zoom-api' ); ?></a>
                                    </li>
								<?php } ?>
                            </ul>
							<?php
						}
					} else {
						echo esc_html__( 'N/A', 'video-conferencing-with-zoom-api' );
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

		$host_id      = $this->sanitize_zoom_host_id( $atts['host_id'] );
		$per_page     = $this->sanitize_positive_int( $atts['per_page'], 300 );
		$downloadable = $this->sanitize_yes_no_attribute( $atts['downloadable'], 'no' );
		$is_downloadable = 'yes' === $downloadable;

		if ( empty( $host_id ) ) {
			echo '<h3 class="no-host-id-defined"><strong style="color:red;">' . esc_html__( 'Invalid HOST ID. Please define a host ID to show recordings based on host.', 'video-conferencing-with-zoom-api' ) . '</strong></h3>';

			return false;
		}

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'video-conferencing-with-zoom-api-datable-responsive' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-responsive-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-dt-responsive-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-shortcode-js' );

		$postParams = array(
			'page_size' => 300 //$per_page disabled for now
		);

		if ( isset( $_GET['fetch_recordings'] ) && isset( $_GET['date'] ) ) {
			$raw_date = $this->get_scalar_value( filter_input( INPUT_GET, 'date' ) );

			if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw_date ) ) {
				$search_date        = strtotime( $raw_date );
				$from               = date( 'Y-m-d', $search_date );
				$to                 = date( 'Y-m-t', $search_date );
				$postParams['from'] = $from;
				$postParams['to']   = $to;
			}

			//Pagination
			$type = $this->get_scalar_value( filter_input( INPUT_GET, 'type' ) );
			$pg   = $this->get_scalar_value( filter_input( INPUT_GET, 'pg' ) );
			if ( ! empty( $pg ) && $type === "recordings" ) {
				$postParams['next_page_token'] = sanitize_text_field( $pg );
			}
		}

		$recordings = json_decode( zoom_conference()->listRecording( $host_id, $postParams ) );

		unset( $GLOBALS['zoom_recordings'] );
		ob_start();
		if ( ! empty( $recordings ) ) {
			if ( ! empty( $recordings->code ) && ! empty( $recordings->message ) ) {
				echo esc_html( $recordings->message );
			} else {
				$GLOBALS['zoom_recordings']               = $recordings;
				$GLOBALS['zoom_recordings']->downloadable = $is_downloadable;
				vczapi_get_template(
					'shortcode/zoom-recordings.php',
					true,
					false,
					[
						'post_id'      => $this->get_current_post_id(),
						'host_id'      => $host_id,
						'per_page'     => $per_page,
						'downloadable' => $downloadable,
					]
				);
			}
		} else {
			esc_html_e( "No recordings found.", "video-conferencing-with-zoom-api" );
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
		$atts = shortcode_atts(
			array(
				'meeting_id'   => '',
				'passcode'     => 'no',
				'downloadable' => 'no'
			),
			$atts,
			'zoom_recordings'
		);

		$meeting_id   = $this->sanitize_zoom_recording_identifier( $atts['meeting_id'] );
		$passcode     = $this->sanitize_zoom_passcode( $atts['passcode'] );
		$downloadable = $this->sanitize_yes_no_attribute( $atts['downloadable'], 'no' );

		if ( empty( $meeting_id ) ) {
			echo '<h3 class="no-meeting-id-defined"><strong style="color:red;">' . esc_html__( 'Invalid Meeting ID.', 'video-conferencing-with-zoom-api' ) . '</strong></h3>';

			return false;
		}

		wp_enqueue_script( 'video-conferencing-with-zoom-api-shortcode-js' );

		$token = RecordingToken::issue(
			'meeting',
			$meeting_id,
			$this->get_current_post_id(),
			[
				'passcode'     => 'yes' === $passcode ? 'yes' : 'no',
				'downloadable' => $downloadable,
			]
		);

		ob_start();
		$loading_text = esc_html__( "Loading recordings.. Please wait..", "video-conferencing-with-zoom-api" );
		echo '<div class="vczapi-recordings-by-meeting-id" data-downloadable="' . esc_attr( $downloadable ) . '" data-meeting="' . esc_attr( $meeting_id ) . '" data-passcode="' . esc_attr( $passcode ) . '" data-token="' . esc_attr( $token ) . '" data-loading="' . esc_attr( $loading_text ) . '"></div>';

		return ob_get_clean();
	}

	/**
	 * Get Meeting recording ajax call function
	 *
	 * @return void
	 */
	public function getRecordingsByMeetingID() {
		$recordings = [];

		$meeting_id = $this->sanitize_zoom_recording_identifier( filter_input( INPUT_GET, 'meeting_id' ) );
		$token      = $this->get_scalar_value( filter_input( INPUT_GET, 'token' ) );

		// A valid token is required before the Zoom API is contacted.
		if ( empty( $meeting_id ) || empty( $token ) ) {
			wp_send_json_error( $this->generic_error() );
		}

		$payload = $this->verify_recording_token( $token, 'meeting', $meeting_id );
		if ( empty( $payload ) ) {
			wp_send_json_error( $this->generic_error() );
		}

		// The token is authoritative for display options.
		$passcode     = $this->sanitize_yes_no_attribute( $payload['passcode'] ?? 'no', 'no' );
		$downloadable = $this->sanitize_yes_no_attribute( $payload['downloadable'] ?? 'no', 'no' );

		$zoomObj      = Zoom::instance();
		$meeting_info = json_decode( zoom_conference()->getMeetingInfo( $meeting_id ) );

		if ( ! empty( $meeting_info->code ) && ! empty( $meeting_info->message ) ) {
			wp_send_json_error( $this->generic_error() );
		}

		//if it's a regular meeting or webinar use the meeting id as it seems it's more reliable
		//https://devforum.zoom.us/t/recording-api-issue/102992
		if ( ! empty( $meeting_info->type ) && MeetingType::is_scheduled_meeting_or_webinar( $meeting_info->type ) ) {
			$recordings[] = $zoomObj->recordingsByMeeting( $meeting_id );
		} else {
			//if it's a recurring meeting / webinar we're going to need to get pass meeting details
			$all_past_meetings = $zoomObj->getPastMeetingDetails( $meeting_id );
			if ( ! empty( $all_past_meetings->meetings ) && ! isset( $all_past_meetings->code ) ) {
				//loop through all instance of past / completed meetings and get recordings
				foreach ( $all_past_meetings->meetings as $meeting ) {
					if ( ! empty( $meeting->uuid ) ) {
						$recordings[] = $zoomObj->recordingsByMeeting( $this->sanitize_zoom_recording_identifier( $meeting->uuid ) );
					}
				}
			} else {
				$recordings[] = $zoomObj->recordingsByMeeting( $meeting_id );
			}
		}


		if ( ! empty( $recordings ) ) {
			if ( ! empty( $recordings[0]->code ) && ! empty( $recordings[0]->message ) ) {
				wp_send_json_error( $this->generic_error() );
			} else {
				$template = '';
				ob_start();
				vczapi_get_template( 'shortcode/zoom-recordings-by-meeting.php', true, false, [
					'recordings'   => $recordings,
					'post_id'      => absint( $payload['post'] ),
					'passcode'     => $passcode,
					'downloadable' => $downloadable
				] );
				$template .= ob_get_clean();
				wp_send_json_success( $template );
			}
		} else {
			wp_send_json_success( esc_html__( "No recordings found.", "video-conferencing-with-zoom-api" ) );
		}
	}
}