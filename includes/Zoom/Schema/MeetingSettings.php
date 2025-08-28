<?php

namespace Codemanas\VczApi\Zoom\Schema;

/**
 * MeetingSettings
 *
 * Centralized schema for Zoom meeting "settings" object.
 * This is referenced by Meeting::create() and can be reused for update, templates, etc.
 *
 * Notes:
 * - Booleans default only where Zoom indicates a default that is safe to assume.
 * - Many nested objects are provided as "object" with a sub-schema for extensibility.
 * - PayloadBuilder should enforce constraints like max_len, enums, and perform coercions.
 */
class MeetingSettings {
	/**
	 * Returns the settings schema array structure.
	 *
	 * @return array
	 */
	public static function schema(): array {
		return array(
			// Simple, common toggles and primitives
			'allow_multiple_devices'                   => array( 'type' => 'bool' ),
			'alternative_hosts'                        => array( 'type' => 'string', 'doc' => 'Semicolon-separated emails or IDs.' ),
			'alternative_hosts_email_notification'     => array( 'type' => 'bool', 'default' => true ),
			'alternative_host_update_polls'            => array( 'type' => 'bool' ),
			'approval_type'                            => array( 'type' => 'int', 'enum' => array( 0, 1, 2 ), 'default' => 2 ),
			'audio'                                    => array( 'type' => 'string', 'enum' => array( 'both', 'telephony', 'voip', 'thirdParty' ), 'default' => 'both' ),
			'audio_conference_info'                    => array( 'type' => 'string', 'max_len' => 2048 ),
			'authentication_domains'                   => array( 'type' => 'string' ),
			'auto_recording'                           => array( 'type' => 'string', 'enum' => array( 'local', 'cloud', 'none' ), 'default' => 'none' ),
			'calendar_type'                            => array( 'type' => 'int', 'enum' => array( 1, 2 ) ),
			'close_registration'                       => array( 'type' => 'bool', 'default' => false ),
			'contact_email'                            => array( 'type' => 'string' ),
			'contact_name'                             => array( 'type' => 'string' ),
			'email_notification'                       => array( 'type' => 'bool', 'default' => true ),
			'encryption_type'                          => array( 'type' => 'string', 'enum' => array( 'enhanced_encryption', 'e2ee' ) ),
			'focus_mode'                               => array( 'type' => 'bool' ),
			'global_dial_in_countries'                 => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
			'host_video'                               => array( 'type' => 'bool' ),

			// Join before host and timing
			'join_before_host'                         => array( 'type' => 'bool', 'default' => false ),
			'jbh_time'                                 => array( 'type' => 'int', 'enum' => array( 0, 5, 10, 15 ), 'default' => 0 ),

			'meeting_authentication'                   => array( 'type' => 'bool' ),
			'mute_upon_entry'                          => array( 'type' => 'bool', 'default' => false ),
			'participant_video'                        => array( 'type' => 'bool' ),
			'private_meeting'                          => array( 'type' => 'bool' ),

			// Registration-related toggles
			'registrants_confirmation_email'           => array( 'type' => 'bool' ),
			'registrants_email_notification'           => array( 'type' => 'bool' ),
			'registration_type'                        => array( 'type' => 'int', 'enum' => array( 1, 2, 3 ), 'default' => 1 ),

			'show_share_button'                        => array( 'type' => 'bool' ),

			// PMI usage (only applies to specific meeting types)
			'use_pmi'                                  => array( 'type' => 'bool', 'default' => false ),

			'waiting_room'                             => array( 'type' => 'bool', 'default' => false ),
			'watermark'                                => array( 'type' => 'bool' ),
			'host_save_video_order'                    => array( 'type' => 'bool' ),
			'internal_meeting'                         => array( 'type' => 'bool', 'default' => false ),

			// Invitees array (flat)
			'meeting_invitees'                         => array(
				'type'  => 'array',
				'items' => array(
					'type'   => 'object',
					'schema' => array(
						'email' => array( 'type' => 'string' ),
					),
				),
			),

			// Authentication exceptions (bypass auth)
			'authentication_exception'                  => array(
				'type'  => 'array',
				'items' => array(
					'type'   => 'object',
					'schema' => array(
						'email'                 => array( 'type' => 'string' ),
						'name'                  => array( 'type' => 'string' ),
						'join_url'              => array( 'type' => 'string' ),
						'authentication_name'   => array( 'type' => 'string' ),
						'authentication_option' => array( 'type' => 'string' ),
					),
				),
			),

			// Breakout rooms pre-assign
			'breakout_room'                            => array(
				'type'   => 'object',
				'schema' => array(
					'enable' => array( 'type' => 'bool' ),
					'rooms'  => array(
						'type'  => 'array',
						'items' => array(
							'type'   => 'object',
							'schema' => array(
								'name'         => array( 'type' => 'string' ),
								'participants' => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
							),
						),
					),
				),
			),

			// Approved/denied regions
			'approved_or_denied_countries_or_regions'  => array(
				'type'   => 'object',
				'schema' => array(
					'enable'      => array( 'type' => 'bool' ),
					'method'      => array( 'type' => 'string', 'enum' => array( 'approve', 'deny' ) ),
					'approved_list'=> array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
					'denied_list'  => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
				),
			),

			// Global dial-in numbers (shape varies; keep generic to avoid over-constraining)
			'global_dial_in_numbers'                   => array(
				'type'  => 'array',
				'items' => array( 'type' => 'object' ),
			),

			// Q&A toggles (commonly used subset)
			'question_and_answer'                      => array(
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

			// Language interpretation (kept generic for now)
			'language_interpretation'                  => array(
				'type'   => 'object',
				'schema' => array(
					'enable'      => array( 'type' => 'bool' ),
					'interpreters'=> array( 'type' => 'array', 'items' => array( 'type' => 'object' ) ),
				),
			),
			'sign_language_interpretation'             => array(
				'type'   => 'object',
				'schema' => array(
					'enable'      => array( 'type' => 'bool' ),
					'interpreters'=> array( 'type' => 'array', 'items' => array( 'type' => 'object' ) ),
				),
			),

			// Custom keys (limited number, with key/value length caps)
			'custom_keys'                              => array(
				'type'       => 'array',
				'max_items'  => 10,
				'items'      => array(
					'type'   => 'object',
					'schema' => array(
						'key'   => array( 'type' => 'string', 'max_len' => 64 ),
						'value' => array( 'type' => 'string', 'max_len' => 256 ),
					),
				),
			),

			// Continuous meeting chat (subset)
			'continuous_meeting_chat'                  => array(
				'type'   => 'object',
				'schema' => array(
					'enable'               => array( 'type' => 'bool' ),
					'who_is_added'         => array( 'type' => 'string', 'enum' => array( 'all_users', 'org_invitees_and_participants', 'org_invitees' ) ),
					'auto_add_invited_external_users' => array( 'type' => 'bool' ),
					'auto_add_meeting_participants'  => array( 'type' => 'bool' ),
				),
			),

			'participant_focused_meeting'              => array( 'type' => 'bool', 'default' => false ),
			'push_change_to_calendar'                  => array( 'type' => 'bool', 'default' => false ),

			// Resources (e.g., whiteboard)
			'resources'                                => array(
				'type'  => 'array',
				'items' => array(
					'type'   => 'object',
					'schema' => array(
						'resource_type'   => array( 'type' => 'string', 'enum' => array( 'whiteboard' ) ),
						'resource_id'     => array( 'type' => 'string' ),
						'permission_level'=> array( 'type' => 'string', 'enum' => array( 'editor', 'commenter', 'viewer' ), 'default' => 'editor' ),
					),
				),
			),

			// AI/summary
			'auto_start_meeting_summary'               => array( 'type' => 'bool' ),
			'who_will_receive_summary'                 => array( 'type' => 'int', 'enum' => array( 1, 2, 3, 4 ) ),
			'auto_start_ai_companion_questions'        => array( 'type' => 'bool' ),
			'who_can_ask_questions'                    => array( 'type' => 'int', 'enum' => array( 1, 2, 3, 4, 5 ) ),
			'summary_template_id'                      => array( 'type' => 'string' ),

			// Device/testing & controls
			'device_testing'                           => array( 'type' => 'bool', 'default' => false ),
			'allow_host_control_participant_mute_state'=> array( 'type' => 'bool' ),
			'disable_participant_video'                => array( 'type' => 'bool', 'default' => false ),
			'email_in_attendee_report'                 => array( 'type' => 'bool' ),
		);
	}
}