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
	 * @param bool $isUpdate Set to true to strip defaults for PATCH requests.
	 * @return array
	 */
	public static function schema( bool $isUpdate = false ): array {
		$schema = array(
			'allow_multiple_devices'                 => array( 'type' => 'bool' ),
			'alternative_hosts'                      => array( 'type' => 'string', 'doc' => 'Semicolon-separated emails or IDs.' ),
			'alternative_hosts_email_notification'   => array( 'type' => 'bool', 'default' => true ),
			'approval_type'                          => array( 'type' => 'int', 'enum' => array( 0, 1, 2 ), 'default' => 2 ),
			'attendees_and_panelists_reminder_email' => array(
				'type'   => 'object',
				'schema' => array(
					'enable' => array( 'type' => 'bool' ),
					'type'   => array( 'type' => 'int', 'enum' => array( 0, 1, 2, 3, 4, 5, 6, 7 ) ),
				),
			),
			'audio'                                  => array( 'type' => 'string', 'enum' => array( 'both', 'telephony', 'voip', 'thirdParty' ), 'default' => 'both' ),
			'audio_conference_info'                  => array( 'type' => 'string', 'max_len' => 2048 ),
			'auto_recording'                         => array( 'type' => 'string', 'enum' => array( 'local', 'cloud', 'none' ), 'default' => 'none' ),
			'close_registration'                     => array( 'type' => 'bool', 'default' => false ),
			'contact_email'                          => array( 'type' => 'string' ),
			'contact_name'                           => array( 'type' => 'string' ),
			'email_language'                         => array( 'type' => 'string' ),
			'panelists_video'                        => array( 'type' => 'bool' ),
			'hd_video'                               => array( 'type' => 'bool', 'default' => false ),
			'hd_video_for_attendees'                 => array( 'type' => 'bool', 'default' => false ),
			'host_video'                             => array( 'type' => 'bool' ),
			'on_demand'                              => array( 'type' => 'bool', 'default' => false ),
			'page_size'                              => array( 'type' => 'int', 'default' => 30 ),
			'post_webinar_email'                     => array(
				'type'   => 'object',
				'schema' => array(
					'enable' => array( 'type' => 'bool' ),
					'type'   => array( 'type' => 'int', 'enum' => array( 1, 2, 3, 4, 5, 6, 7 ) ),
				),
			),
			'practice_session'                       => array( 'type' => 'bool', 'default' => false ),
			'question_and_answer'                    => array(
				'type'   => 'object',
				'schema' => array(
					'enable'                    => array( 'type' => 'bool' ),
					'allow_submit_questions'    => array( 'type' => 'bool' ),
					'allow_anonymous_questions' => array( 'type' => 'bool' ),
					'question_visibility'       => array( 'type' => 'string', 'enum' => array( 'answered', 'all' ) ),
					'attendees_can_comment'     => array( 'type' => 'bool' ),
					'attendees_can_upvote'      => array( 'type' => 'bool' ),
				),
			),
			'registrants_confirmation_email'         => array( 'type' => 'bool' ),
			'registrants_email_notification'         => array( 'type' => 'bool' ),
			'registration_type'                      => array( 'type' => 'int', 'enum' => array( 1, 2, 3 ), 'default' => 1 ),
			'show_share_button'                      => array( 'type' => 'bool' ),
			'show_browser_join_link'                 => array( 'type' => 'bool' ),
			'survey_url'                             => array( 'type' => 'string' ),
		);

		if ( $isUpdate ) {
			foreach ( $schema as $key => &$field ) {
				unset( $field['default'] );
			}
			unset( $field );
		}

		return $schema;
	}
}