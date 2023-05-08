<?php

use \Firebase\JWT\JWT;
use Codemanas\VczApi\Data\Logger;

/**
 * Class Connecting Zoom API V2
 *
 * @since   2.0
 * @author  Deepen
 * @modifiedn
 */
if ( ! class_exists( 'Zoom_Video_Conferencing_Api' ) ) {

	class Zoom_Video_Conferencing_Api {

		public static $OAuth_revalidate_attempts = 0;

		/**
		 * Zoom API KEY
		 *
		 * @var
		 */
		public $zoom_api_key;

		/**
		 * Zoom API Secret
		 *
		 * @var
		 */
		public $zoom_api_secret;

		/**
		 * Hold my instance
		 *
		 * @var
		 */
		protected static $_instance;

		/**
		 * API endpoint base
		 *
		 * @var string
		 */
		private $api_url = 'https://api.zoom.us/v2/';

		/**
		 * Create only one instance so that it may not Repeat
		 *
		 * @since 2.0.0
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Zoom_Video_Conferencing_Api constructor.
		 *
		 * @param $zoom_api_key
		 * @param $zoom_api_secret
		 */
		public function __construct( $zoom_api_key = '', $zoom_api_secret = '' ) {
			$this->zoom_api_key    = $zoom_api_key;
			$this->zoom_api_secret = $zoom_api_secret;
		}

		/**
		 * Send request to API
		 *
		 * @param        $calledFunction
		 * @param        $data
		 * @param string $request
		 *
		 * @return array|bool|string|WP_Error
		 */
		protected function sendRequest( $calledFunction, $data, $request = "GET" ) {
			$initialRequest = $request;
			$request_url    = $this->api_url . $calledFunction;
			$bearerToken    = $this->getBearerToken();

			$args = array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $bearerToken,
					'Content-Type'  => 'application/json',
				),
			);

			if ( $request == "GET" ) {
				$args['body'] = ! empty( $data ) ? $data : array();
				$request      = wp_remote_get( $request_url, $args );
			} elseif ( $request == "DELETE" ) {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "DELETE";
				$request        = wp_remote_request( $request_url, $args );
			} elseif ( $request == "PATCH" ) {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "PATCH";
				$request        = wp_remote_request( $request_url, $args );
			} elseif ( $request == "PUT" ) {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "PUT";
				$request        = wp_remote_request( $request_url, $args );
			} else {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "POST";
				$request        = wp_remote_post( $request_url, $args );
			}

			if ( is_wp_error( $request ) ) {
				$this->logMessage( $request->get_error_message(), $request->get_error_code(), $request );

				return false; // Bail early
			} else {
				$responseCode = wp_remote_retrieve_response_code( $request );
				$responseBody = wp_remote_retrieve_body( $request );
				$debug_log    = get_option( 'zoom_api_enable_debug_log' );

				if ( $responseCode == 401 && vczapi_is_oauth_active() ) {
					//only regenerate access token if it's already active;
					\vczapi\S2SOAuth::get_instance()->regenerateAccessTokenAndSave();
					//only retry twice;
					if ( self::$OAuth_revalidate_attempts <= 2 ) {
						self::$OAuth_revalidate_attempts ++;

						//resend the request after regenerating access token
						return $this->sendRequest( $calledFunction, $data, $initialRequest );
					} else {
						self::$OAuth_revalidate_attempts = 0;
						if ( ! empty( $debug_log ) ) {
							$this->logMessage( $responseBody, $responseCode, $request );
						}
					}
				}

				//If Debug log is enabled.
				if ( ! empty( $debug_log ) ) {
					if ( $responseCode == 400 ) {
						$this->logMessage( $responseBody, $responseCode, $request );
					} elseif ( $responseCode == 401 ) {
						$this->logMessage( $responseBody, $responseCode, $request );
					} elseif ( $responseCode == 403 ) {
						$this->logMessage( $responseBody, $responseCode, $request );
					} elseif ( $responseCode == 404 ) {
						$this->logMessage( $responseBody, $responseCode, $request );
					} elseif ( $responseCode == 409 ) {
						$this->logMessage( $responseBody, $responseCode, $request );
					} elseif ( $responseCode == 429 ) {
						$this->logMessage( $responseBody, $responseCode, $request );
					}
				}
			}

			return $responseBody;
		}

		/**
		 * Check is given string a correct json object
		 *
		 * @param $string
		 *
		 * @return bool
		 */
		public function isJson( $string ) {
			json_decode( $string );

			return json_last_error() === JSON_ERROR_NONE;
		}

		/**
		 * Check is Valid XML
		 *
		 * @param $xml
		 *
		 * @return bool
		 */
		public function isValidXML( $xml ) {
			$doc = @simplexml_load_string( $xml );
			if ( $doc ) {
				return true; //this is valid
			} else {
				return false; //this is not valid
			}
		}

		/**
		 * Just log the message for now because of backwards incompatibility issues
		 *
		 * @param $responseBody
		 * @param $responseCode
		 * @param $request
		 *
		 * @author Deepen Bajracharya
		 *
		 * @since  3.8.18
		 */
		public function logMessage( $responseBody, $responseCode, $request ) {
			$message = $responseCode . ' ::: ';
			$message .= wp_remote_retrieve_response_message( $request );
			/*$error_data['date']       = ! empty( $request['headers'] ) && ! empty( $request['headers']->offsetGet( 'date' ) ) ? $request['headers']->offsetGet( 'date' ) : '';
			$error_data['rate_limit'] = ! empty( $request['headers'] ) && ! empty( $request['headers']->offsetGet( 'x-ratelimit-category' ) ) ? $request['headers']->offsetGet( 'x-ratelimit-category' ) : '';
			if ( ! empty( $responseBody ) ) {
				$responseBody          = json_decode( $responseBody );
				$error_data['message'] = ! empty( $responseBody ) && ! empty( $responseBody->message ) ? $responseBody->message : '';
			}*/

			if ( ! empty( $responseBody ) ) {

				//Response body validation
				if ( $this->isValidXML( $responseBody ) ) {
					$responseBody = simplexml_load_string( $responseBody );
				} elseif ( $this->isJson( $responseBody ) ) {
					$responseBody = json_decode( $responseBody );
				}

				if ( ! empty( $responseBody ) && ! empty( $responseBody->message ) ) {
					$message .= ' ::: MESSAGE => ' . $responseBody->message;
				} elseif ( ! empty( $responseBody ) && is_string( $responseBody ) ) {
					$message .= ' ::: MESSAGE => ' . $responseBody;
				}

				if ( ! empty( $responseBody ) && ! empty( $responseBody->errors ) && is_object( $responseBody->errors ) && ! empty( $responseBody->errors->message ) ) {
					$message .= ' ::: ERRORS => ' . $responseBody->errors->message;
				}
			}

//			$error = new \WP_Error( $responseCode, $message, $error_data );
			$logger = new Logger();
			$logger->error( $message );
		}

		private function getBearerToken() {
			//@todo this will need to be modified for each user scenario
			$OauthData = get_option( 'vczapi_global_oauth_data' );
			if ( ! empty( $OauthData ) ) {
				return $OauthData->access_token;
			} else {
				return $this->generateJWTKey();
			}

		}

		/**
		 * Generate JWT key
		 *
		 * @return string
		 */
		private function generateJWTKey() {
			$key    = $this->zoom_api_key;
			$secret = $this->zoom_api_secret;

			$token = array(
				"iss" => $key,
				"exp" => time() + 3600, //60 seconds as suggested
			);

			if ( empty( $secret ) ) {
				return false;
			}

			return JWT::encode( $token, $secret, 'HS256' );
		}

		/**
		 * Creates a User
		 *
		 * @param $postedData
		 *
		 * @return array|bool|string
		 */
		public function createAUser( $postedData = array() ) {
			$createAUserArray              = array();
			$createAUserArray['action']    = $postedData['action'];
			$createAUserArray['user_info'] = array(
				'email'      => $postedData['email'],
				'type'       => $postedData['type'],
				'first_name' => $postedData['first_name'],
				'last_name'  => $postedData['last_name'],
			);
			$createAUserArray              = apply_filters( 'vczapi_createAUser', $createAUserArray );

			return $this->sendRequest( 'users', $createAUserArray, "POST" );
		}

		/**
		 * User Function to List
		 *
		 * @param $page
		 * @param $args
		 *
		 * @return array
		 */
		public function listUsers( $page = 1, $args = array() ) {
			$defaults = array(
				'page_size'   => 300,
				'page_number' => absint( $page ),
			);

			// Parse incoming $args into an array and merge it with $defaults
			$args           = wp_parse_args( $args, $defaults );
			$listUsersArray = apply_filters( 'vczapi_listUsers', $args );

			return $this->sendRequest( 'users', $listUsersArray, "GET" );
		}

		/**
		 * Get A users info by user Id
		 *
		 * @param $user_id
		 *
		 * @return array|bool|string
		 */
		public function getUserInfo( $user_id ) {
			$getUserInfoArray = array();
			$getUserInfoArray = apply_filters( 'vczapi_getUserInfo', $getUserInfoArray );

			return $this->sendRequest( 'users/' . $user_id, $getUserInfoArray );
		}

		/**
		 * Delete a User
		 *
		 * @param $userid
		 *
		 * @return array|bool|string
		 */
		public function deleteAUser( $userid ) {
			$deleteAUserArray       = array();
			$deleteAUserArray['id'] = $userid;

			return $this->sendRequest( 'users/' . $userid, false, "DELETE" );
		}

		/**
		 * Get Meetings
		 *
		 * @param $host_id
		 * @param $args
		 *
		 * @return array
		 */
		public function listMeetings( $host_id, $args = false ) {
			$defaults = array(
				'page_size' => 300,
			);

			// Parse incoming $args into an array and merge it with $defaults
			$args = wp_parse_args( $args, $defaults );
			$args = apply_filters( 'vczapi_listMeetings', $args );

			return $this->sendRequest( 'users/' . $host_id . '/meetings', $args, "GET" );
		}

		/**
		 * Create A meeting API
		 *
		 * @param array $data
		 *
		 * @return array|bool|string|void|WP_Error
		 */
		public function createAMeeting( $data = array() ) {
			$post_time  = $data['start_date'];
			$start_time = gmdate( "Y-m-d\TH:i:s", strtotime( $post_time ) );

			$createAMeetingArray = array();

			if ( ! empty( $data['alternative_host_ids'] ) ) {
				if ( count( $data['alternative_host_ids'] ) > 1 ) {
					$alternative_host_ids = implode( ",", $data['alternative_host_ids'] );
				} else {
					$alternative_host_ids = $data['alternative_host_ids'][0];
				}
			}

			$createAMeetingArray['topic']      = $data['meetingTopic'];
			$createAMeetingArray['agenda']     = ! empty( $data['agenda'] ) ? wp_strip_all_tags( $data['agenda'], true ) : "";
			$createAMeetingArray['type']       = ! empty( $data['type'] ) ? $data['type'] : 2; //Scheduled
			$createAMeetingArray['start_time'] = $start_time;
			$createAMeetingArray['timezone']   = $data['timezone'];
			$createAMeetingArray['password']   = ! empty( $data['password'] ) ? $data['password'] : "";
			$createAMeetingArray['duration']   = ! empty( $data['duration'] ) ? $data['duration'] : 60;
			$createAMeetingArray['settings']   = array(
				'meeting_authentication' => ! empty( $data['meeting_authentication'] ) ? true : false,
				'join_before_host'       => ! empty( $data['join_before_host'] ) ? true : false,
				'host_video'             => ! empty( $data['option_host_video'] ) ? true : false,
				'participant_video'      => ! empty( $data['option_participants_video'] ) ? true : false,
				'mute_upon_entry'        => ! empty( $data['option_mute_participants'] ) ? true : false,
				'auto_recording'         => ! empty( $data['option_auto_recording'] ) ? $data['option_auto_recording'] : "none",
				'alternative_hosts'      => isset( $alternative_host_ids ) ? $alternative_host_ids : "",
				'waiting_room'           => isset( $data['disable_waiting_room'] ) && ( $data['disable_waiting_room'] == 'yes' ) ? false : true,
			);

			$createAMeetingArray = apply_filters( 'vczapi_createAmeeting', $createAMeetingArray );
			if ( ! empty( $createAMeetingArray ) ) {
				return $this->sendRequest( 'users/' . $data['userId'] . '/meetings', $createAMeetingArray, "POST" );
			} else {
				return;
			}
		}

		/**
		 * Updating Meeting Info
		 *
		 * @param array $data
		 *
		 * @return array|bool|string|void|WP_Error
		 */
		public function updateMeetingInfo( $data = array() ) {
			$post_time  = $data['start_date'];
			$start_time = gmdate( "Y-m-d\TH:i:s", strtotime( $post_time ) );

			$updateMeetingInfoArray = array();

			if ( ! empty( $data['alternative_host_ids'] ) ) {
				if ( count( $data['alternative_host_ids'] ) > 1 ) {
					$alternative_host_ids = implode( ",", $data['alternative_host_ids'] );
				} else {
					$alternative_host_ids = $data['alternative_host_ids'][0];
				}
			}

			$updateMeetingInfoArray['topic']      = $data['topic'];
			$updateMeetingInfoArray['agenda']     = ! empty( $data['agenda'] ) ? $data['agenda'] : "";
			$updateMeetingInfoArray['type']       = ! empty( $data['type'] ) ? $data['type'] : 2; //Scheduled
			$updateMeetingInfoArray['start_time'] = $start_time;
			$updateMeetingInfoArray['timezone']   = $data['timezone'];
			$updateMeetingInfoArray['password']   = ! empty( $data['password'] ) ? $data['password'] : "";
			$updateMeetingInfoArray['duration']   = ! empty( $data['duration'] ) ? $data['duration'] : 60;
			$updateMeetingInfoArray['settings']   = array(
				'meeting_authentication' => ! empty( $data['meeting_authentication'] ) ? true : false,
				'join_before_host'       => ! empty( $data['join_before_host'] ) ? true : false,
				'host_video'             => ! empty( $data['option_host_video'] ) ? true : false,
				'participant_video'      => ! empty( $data['option_participants_video'] ) ? true : false,
				'mute_upon_entry'        => ! empty( $data['option_mute_participants'] ) ? true : false,
				'auto_recording'         => ! empty( $data['option_auto_recording'] ) ? $data['option_auto_recording'] : "none",
				'alternative_hosts'      => isset( $alternative_host_ids ) ? $alternative_host_ids : "",
				'waiting_room'           => isset( $data['disable_waiting_room'] ) && ( $data['disable_waiting_room'] == 'yes' ) ? false : true,
			);

			$updateMeetingInfoArray = apply_filters( 'vczapi_updateMeetingInfo', $updateMeetingInfoArray );
			if ( ! empty( $updateMeetingInfoArray ) ) {
				return $this->sendRequest( 'meetings/' . $data['meeting_id'], $updateMeetingInfoArray, "PATCH" );
			} else {
				return;
			}
		}

		/**
		 * Get a Meeting Info
		 *
		 * @param  [INT] $id
		 * @param  $args
		 *
		 * @return array
		 */
		public function getMeetingInfo( $id, $args = array() ) {
			$getMeetingInfoArray = apply_filters( 'vczapi_getMeetingInfo', $args );

			return $this->sendRequest( 'meetings/' . $id, $getMeetingInfoArray, "GET" );
		}

		/**
		 * @param $meetingid
		 *
		 * @return array|bool|string|WP_Error
		 */
		public function getPastMeetingDetails( $meetingid ) {
			return $this->sendRequest( 'past_meetings/' . $meetingid . '/instances', false, 'GET' );
		}

		/**
		 * Delete A Meeting
		 *
		 * @param $meeting_id
		 *
		 * @return array|bool|string|WP_Error
		 */
		public function deleteAMeeting( $meeting_id ) {
			return $this->sendRequest( 'meetings/' . $meeting_id, false, "DELETE" );
		}

		/**
		 * Delete a Webinar
		 *
		 * @param $webinar_id
		 *
		 * @return array|bool|string|WP_Error
		 */
		public function deleteAWebinar( $webinar_id ) {
			return $this->sendRequest( 'webinars/' . $webinar_id, false, "DELETE" );
		}

		/*Functions for management of reports*/
		/**
		 * Get daily account reports by month
		 *
		 * @param $month
		 * @param $year
		 *
		 * @return bool|mixed
		 */
		public function getDailyReport( $month, $year ) {
			$getDailyReportArray          = array();
			$getDailyReportArray['year']  = $year;
			$getDailyReportArray['month'] = $month;
			$getDailyReportArray          = apply_filters( 'vczapi_getDailyReport', $getDailyReportArray );

			return $this->sendRequest( 'report/daily', $getDailyReportArray, "GET" );
		}

		/**
		 * Get ACcount Reports
		 *
		 * @param $zoom_account_from
		 * @param $zoom_account_to
		 *
		 * @return array
		 */
		public function getAccountReport( $zoom_account_from, $zoom_account_to ) {
			$getAccountReportArray              = array();
			$getAccountReportArray['from']      = $zoom_account_from;
			$getAccountReportArray['to']        = $zoom_account_to;
			$getAccountReportArray['page_size'] = 300;
			$getAccountReportArray              = apply_filters( 'vczapi_getAccountReport', $getAccountReportArray );

			return $this->sendRequest( 'report/users', $getAccountReportArray, "GET" );
		}

		/**
		 * Register Webinar Participants
		 *
		 * @param $webinar_id
		 * @param $first_name
		 * @param $last_name
		 * @param $email
		 *
		 * @return array|bool|string|WP_Error
		 */
		public function registerWebinarParticipants( $webinar_id, $first_name, $last_name, $email ) {
			$postData               = array();
			$postData['first_name'] = $first_name;
			$postData['last_name']  = $last_name;
			$postData['email']      = $email;

			return $this->sendRequest( 'webinars/' . $webinar_id . '/registrants', $postData, "POST" );
		}

		/**
		 * List webinars
		 *
		 * @param $userId
		 * @param $args
		 *
		 * @return bool|mixed
		 */
		public function listWebinar( $userId, $args = array() ) {
			$defaults = array(
				'page_size' => 300,
			);

			// Parse incoming $args into an array and merge it with $defaults
			$args = wp_parse_args( $args, $defaults );
			$args = apply_filters( 'vczapi_listWebinar', $args );

			return $this->sendRequest( 'users/' . $userId . '/webinars', $args, "GET" );
		}

		/**
		 * Create Webinar
		 *
		 * @param       $userID
		 * @param array $data
		 *
		 * @return array|bool|string|void|WP_Error
		 */
		public function createAWebinar( $userID, $data = array() ) {
			$postData = apply_filters( 'vczapi_createAwebinar', $data );

			return $this->sendRequest( 'users/' . $userID . '/webinars', $postData, "POST" );
		}

		/**
		 * Update Webinar
		 *
		 * @param       $webinar_id
		 * @param array $data
		 *
		 * @return array|bool|string|void|WP_Error
		 */
		public function updateWebinar( $webinar_id, $data = array() ) {
			$postData = apply_filters( 'vczapi_updateWebinar', $data );
			//https://devforum.zoom.us/t/is-there-a-size-limit-for-the-agenda-field/11199
			//data sanitization for agenda field - remove html tags and make sure it's only 2000 characters.
			$agenda         = strip_tags( html_entity_decode( $data['agenda'] ), null );
			$data['agenda'] = substr( $agenda, 0, 1999 );

			return $this->sendRequest( 'webinars/' . $webinar_id, $postData, "PATCH" );
		}

		/**
		 * Get Webinar Info
		 *
		 * @param $id
		 *
		 * @return array|bool|string|WP_Error
		 */
		public function getWebinarInfo( $id ) {
			$getMeetingInfoArray = apply_filters( 'vczapi_getWebinarInfo', array() );

			return $this->sendRequest( 'webinars/' . $id, $getMeetingInfoArray, "GET" );
		}

		/**
		 * List Webinar Participants
		 *
		 * @param $webinarId
		 * @param $args
		 *
		 * @return bool|mixed
		 */
		public function listWebinarParticipants( $webinarId, $args = array() ) {
			$defaults = array(
				'page_size' => 300,
			);

			// Parse incoming $args into an array and merge it with $defaults
			$args = wp_parse_args( $args, $defaults );
			$args = apply_filters( 'vczapi_listWebinarParticipants', $args );

			return $this->sendRequest( 'webinars/' . $webinarId . '/registrants', $args, "GET" );
		}

		/**
		 * Get recording by meeting ID
		 *
		 * @param $meetingId
		 *
		 * @return bool|mixed
		 */
		public function recordingsByMeeting( $meetingId ) {
			return $this->sendRequest( 'meetings/' . $meetingId . '/recordings', false, "GET" );
		}

		/**
		 * Get all recordings by USER ID ( REQUIRES PRO USER )
		 *
		 * @param $host_id
		 * @param $data array
		 *
		 * @return bool|mixed
		 */
		public function listRecording( $host_id, $data = array() ) {
			$from = date( 'Y-m-d', strtotime( '-2 month', time() ) );
			$to   = date( 'Y-m-d' );

			$data['from'] = ! empty( $data['from'] ) ? $data['from'] : $from;
			$data['to']   = ! empty( $data['to'] ) ? $data['to'] : $to;
			$data         = apply_filters( 'vczapi_listRecording', $data );

			return $this->sendRequest( 'users/' . $host_id . '/recordings', $data, "GET" );
		}

		/**
		 * Will end meeting via the meeting status end point
		 * https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/meetingStatus
		 *
		 * @param $meetingID
		 *
		 * @return array|bool|string|WP_Error
		 */
		public function end_meeting( $meetingID ) {
			return $this->sendRequest( "/meetings/" . $meetingID . "/status", [ 'action' => 'end' ], 'PUT' );
		}
	}

	function zoom_conference() {
		return Zoom_Video_Conferencing_Api::instance();
	}

	zoom_conference();
}