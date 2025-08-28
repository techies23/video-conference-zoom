<?php

namespace Codemanas\VczApi\Zoom\Schema;

class Meeting {
	/**
	 * Schema for listing a user's/host's meetings.
	 *
	 * GET /users/{user_id}/meetings
	 *
	 * Request:
	 * - Path param: user_id (string) — Zoom user ID, email, or "me" (for user-level apps)
	 * - Query params:
	 *   - type (enum) default: scheduled
	 *   - page_size (int) default: 30, max: 300
	 *   - next_page_token (string)
	 *   - page_number (int) legacy-style pagination
	 *   - from (YYYY-MM-DD)
	 *   - to (YYYY-MM-DD)
	 *   - timezone (string, e.g., America/Los_Angeles)
	 *
	 * Compat mapping notes:
	 * - Legacy code often used "host_id" for the user path segment. Mapped to "user_id".
	 * - CamelCase variants like "userId" (and "hostId") are mapped to "user_id".
	 * - Some integrations may pass "page" for page number. Mapped to "page_number".
	 */
	public static function list(): array {
		return array(
			'operation' => SchemaManager::MEETING_LIST,
			'http'      => array(
				'method'      => 'GET',
				'path'        => '/users/{user_id}/meetings',
				'path_params' => array(
					'user_id' => 'user_id',
				),
			),
			'fields'    => array(
				// Path
				'user_id'         => array(
					'type'      => 'string',
					'required'  => true,
					'location'  => 'path',
					'trim'      => true,
					'nullable'  => false,
					'doc'       => 'Zoom user ID, email, or "me" for user-level apps.',
				),

				// Query
				'type'            => array(
					'type'     => 'string',
					'default'  => 'scheduled',
					'enum'     => array( 'scheduled', 'live', 'upcoming', 'upcoming_meetings', 'previous_meetings' ),
					'location' => 'query',
				),
				'page_size'       => array(
					'type'     => 'int',
					'default'  => 30,
					'min'      => 1,
					'max'      => 300,
					'location' => 'query',
				),
				'next_page_token' => array(
					'type'     => 'string',
					'location' => 'query',
				),
				'page_number'     => array(
					'type'     => 'int',
					'min'      => 1,
					'location' => 'query',
				),
				'from'            => array(
					'type'     => 'string',
					'location' => 'query',
				),
				'to'              => array(
					'type'     => 'string',
					'location' => 'query',
				),
				'timezone'        => array(
					'type'     => 'string',
					'location' => 'query',
				),
			),
			'compat'    => array(
				'host_id' => 'user_id',
				'userId'  => 'user_id',
				'hostId'  => 'user_id',
				'page'    => 'page_number',
			),
			'notes'     => array(
				'pagination' => 'Prefer token-based pagination via next_page_token over page_number.',
			),
		);
	}

	/**
	 * Schema for creating a meeting for a user/host.
	 *
	 * POST /users/{user_id}/meetings
	 *
	 * Required minimal fields:
	 * - user_id (path)
	 * - topic (string, <= 200 chars)
	 * - type (int; default 2)
	 * - Optional: start_time (date-time), timezone (string), duration (int), agenda (<= 2000), password (<= 10)
	 * - Optional: settings.*
	 */

	public static function create(): array {
		return array(
			'operation' => SchemaManager::MEETING_CREATE,
			'http'      => array(
				'method'      => 'POST',
				'path'        => '/users/{user_id}/meetings',
				'path_params' => array(
					'user_id' => 'user_id',
				),
			),
			'fields'    => array(
				// Path
				'user_id' => array(
					'type'      => 'string',
					'required'  => true,
					'location'  => 'path',
					'trim'      => true,
					'nullable'  => false,
				),

				// Body (top-level)
				'topic'        => array( 'type' => 'string', 'required' => true, 'location' => 'body', 'max_len' => 200, 'trim' => true ),
				'agenda'       => array( 'type' => 'string', 'location' => 'body', 'max_len' => 2000 ),
				'type'         => array( 'type' => 'int', 'default' => 2, 'enum' => array( 1, 2, 3, 8, 10 ), 'location' => 'body' ),
				'start_time'   => array( 'type' => 'string', 'location' => 'body' ),
				'timezone'     => array( 'type' => 'string', 'location' => 'body' ),
				'duration'     => array( 'type' => 'int', 'default' => 60, 'min' => 1, 'max' => 1440, 'location' => 'body' ),
				'password'     => array( 'type' => 'string', 'location' => 'body', 'max_len' => 10 ),
				'default_password' => array( 'type' => 'bool', 'default' => true, 'location' => 'body' ),
				'pre_schedule'     => array( 'type' => 'bool', 'default' => false, 'location' => 'body' ),
				'schedule_for'     => array( 'type' => 'string', 'location' => 'body' ),

				// Recurrence
				'recurrence' => array(
					'type'     => 'object',
					'location' => 'body',
					'schema'   => array(
						'type'            => array( 'type' => 'int', 'enum' => array( 1, 2, 3 ) ),
						'repeat_interval' => array( 'type' => 'int' ),
						'end_date_time'   => array( 'type' => 'string' ),
						'end_times'       => array( 'type' => 'int', 'max' => 60, 'default' => 1 ),
						'weekly_days'     => array( 'type' => 'string' ),
						'monthly_day'     => array( 'type' => 'int', 'min' => 1, 'max' => 31, 'default' => 1 ),
						'monthly_week'    => array( 'type' => 'int', 'enum' => array( -1, 1, 2, 3, 4 ) ),
						'monthly_week_day'=> array( 'type' => 'int', 'enum' => array( 1, 2, 3, 4, 5, 6, 7 ) ),
					),
				),

				// Settings now centralized
				'settings' => array(
					'type'     => 'object',
					'location' => 'body',
					'schema'   => MeetingSettings::schema(),
				),

				// Tracking fields
				'tracking_fields' => array(
					'type'     => 'array',
					'location' => 'body',
					'items'    => array(
						'type'   => 'object',
						'schema' => array(
							'field' => array( 'type' => 'string', 'required' => true ),
							'value' => array( 'type' => 'string' ),
						),
					),
				),

				'template_id' => array( 'type' => 'string', 'location' => 'body' ),
			),
			// Backward-compat mappings: old_field => new_field (supports dotted paths for nested targets)
			'compat' => array(
				'userId'             => 'user_id',
				'host_id'            => 'user_id',
				'hostId'             => 'user_id',
				'meetingTopic'       => 'topic',
				'start_date'         => 'start_time',
				'meeting_authentication'    => 'settings.meeting_authentication',
				'join_before_host'          => 'settings.join_before_host',
				'jbh_time'                  => 'settings.jbh_time',
				'option_host_video'         => 'settings.host_video',
				'option_participants_video' => 'settings.participant_video',
				'option_mute_participants'  => 'settings.mute_upon_entry',
				'option_auto_recording'     => 'settings.auto_recording',
				'alternative_host_ids'      => 'settings.alternative_hosts',
				'disable_waiting_room'      => 'settings.waiting_room',
			),
			'compat_transform' => array(
				array( 'from' => 'alternative_host_ids', 'to' => 'settings.alternative_hosts', 'op' => 'implode', 'args' => array( 'separator' => ';' ) ),
				array( 'from' => 'disable_waiting_room', 'to' => 'settings.waiting_room', 'op' => 'bool_invert' ),
				array( 'from' => 'agenda', 'to' => 'agenda', 'op' => 'truncate', 'args' => array( 'max' => 2000 ) ),
				array( 'from' => 'password', 'to' => 'password', 'op' => 'truncate', 'args' => array( 'max' => 10 ) ),
			),
			'notes' => array(
				'start_time' => 'If start_time is omitted for type=2, Zoom may convert it to an instant meeting.',
				'settings'   => 'If waiting_room=true, join_before_host is effectively disabled by Zoom.',
			),
		);
	}
}