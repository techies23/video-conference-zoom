<?php

namespace Codemanas\VczApi\Zoom\Schema;

/**
 * WebinarSettings
 *
 * Centralized schema for Zoom webinar "settings" object.
 * Referenced by Webinar::create() and Webinar::update().
 */
class WebinarSettings {

	/**
	 * Returns the webinar settings schema array structure.
	 *
	 * @param   bool  $isUpdate  Set to true to strip defaults for PATCH requests.
	 *
	 * @return array
	 */
	public static function schema( bool $isUpdate = false ): array {
		$schema = array(
			'additional_data_center_regions'                      => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
			'allow_multiple_devices'                              => array( 'type' => 'bool' ),
			'alternative_hosts'                                   => array( 'type' => 'string', 'doc' => 'Semicolon-separated emails or IDs.' ),
			'alternative_host_update_polls'                       => array( 'type' => 'bool' ),
			'approval_type'                                       => array( 'type' => 'int', 'enum' => array( 0, 1, 2 ), 'default' => 2 ),
			'attendees_and_panelists_reminder_email_notification' => array(
				'type'   => 'object',
				'schema' => array(
					'enable' => array( 'type' => 'bool' ),
					'type'   => array( 'type' => 'int', 'enum' => array( 0, 1, 2, 3, 4, 5, 6, 7 ) ),
				),
			),
			'audio'                                               => array( 'type' => 'string', 'enum' => array( 'both', 'telephony', 'voip', 'thirdParty' ), 'default' => 'both' ),
			'audio_conference_info'                               => array( 'type' => 'string', 'max_len' => 2048 ),
			'authentication_domains'                              => array( 'type' => 'string' ),
			'authentication_option'                               => array( 'type' => 'string' ),
			'auto_recording'                                      => array( 'type' => 'string', 'enum' => array( 'local', 'cloud', 'none' ), 'default' => 'none' ),
			'close_registration'                                  => array( 'type' => 'bool', 'deprecated' => true ),
			'contact_email'                                       => array( 'type' => 'string' ),
			'contact_name'                                        => array( 'type' => 'string' ),
			'email_language'                                      => array( 'type' => 'string' ),
			'enforce_login'                                       => array( 'type' => 'bool', 'deprecated' => true ),
			'enforce_login_domains'                               => array( 'type' => 'string', 'deprecated' => true ),
			'follow_up_absentees_email_notification'              => array(
				'type'   => 'object',
				'schema' => array(
					'enable' => array( 'type' => 'bool' ),
					'type'   => array( 'type' => 'int', 'enum' => array( 0, 1, 2, 3, 4, 5, 6, 7 ) ),
				),
			),
			'follow_up_attendees_email_notification'              => array(
				'type'   => 'object',
				'schema' => array(
					'enable' => array( 'type' => 'bool' ),
					'type'   => array( 'type' => 'int', 'enum' => array( 0, 1, 2, 3, 4, 5, 6, 7 ) ),
				),
			),
			'global_dial_in_countries'                            => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
			'hd_video'                                            => array( 'type' => 'bool', 'default' => false ),
			'hd_video_for_attendees'                              => array( 'type' => 'bool', 'default' => false ),
			'host_video'                                          => array( 'type' => 'bool' ),
			'language_interpretation'                             => array(
				'type'   => 'object',
				'schema' => array(
					'enable'       => array( 'type' => 'bool' ),
					'interpreters' => array( 'type' => 'array' ),
				),
			),
			'sign_language_interpretation'                        => array(
				'type'   => 'object',
				'schema' => array(
					'enable'       => array( 'type' => 'bool' ),
					'interpreters' => array( 'type' => 'array' ),
				),
			),
			'panelist_authentication'                             => array( 'type' => 'bool' ),
			'meeting_authentication'                              => array( 'type' => 'bool' ),
			'add_watermark'                                       => array( 'type' => 'bool' ),
			'add_audio_watermark'                                 => array( 'type' => 'bool' ),
			'on_demand'                                           => array( 'type' => 'bool', 'default' => false ),
			'panelists_invitation_email_notification'             => array( 'type' => 'bool' ),
			'panelists_video'                                     => array( 'type' => 'bool' ),
			'post_webinar_survey'                                 => array( 'type' => 'bool' ),
			'practice_session'                                    => array( 'type' => 'bool', 'default' => false ),
			'question_and_answer'                                 => array(
				'type'   => 'object',
				'schema' => array(
					'enable'                    => array( 'type' => 'bool' ),
					'allow_submit_questions'    => array( 'type' => 'bool' ),
					'allow_anonymous_questions' => array( 'type' => 'bool' ),
					'answer_questions'          => array( 'type' => 'string', 'enum' => array( 'only', 'all' ) ),
					'attendees_can_comment'     => array( 'type' => 'bool' ),
					'attendees_can_upvote'      => array( 'type' => 'bool' ),
					'allow_auto_reply'          => array( 'type' => 'bool' ),
					'auto_reply_text'           => array( 'type' => 'string' ),
				),
			),
			'registrants_confirmation_email'                      => array( 'type' => 'bool' ),
			'registrants_email_notification'                      => array( 'type' => 'bool' ),
			'registrants_restrict_number'                         => array( 'type' => 'int', 'default' => 0, 'min' => 0, 'max' => 20000 ),
			'registration_type'                                   => array( 'type' => 'int', 'default' => 1, 'enum' => array( 1, 2, 3 ) ),
			'send_1080p_video_to_attendees'                       => array( 'type' => 'bool', 'default' => false ),
			'show_share_button'                                   => array( 'type' => 'bool' ),
			'show_join_info'                                      => array( 'type' => 'bool' ),
			'survey_url'                                          => array( 'type' => 'string' ),
			'enable_session_branding'                             => array( 'type' => 'bool' ),
			'request_permission_to_unmute_participants'           => array( 'type' => 'bool', 'default' => false ),
			'allow_host_control_participant_mute_state'           => array( 'type' => 'bool' ),
			'email_in_attendee_report'                            => array( 'type' => 'bool' ),
		);

		if ( $isUpdate ) {
			foreach ( $schema as $key => $field ) {
				unset( $schema[ $key ]['default'] );
			}
		}

		return $schema;
	}
}