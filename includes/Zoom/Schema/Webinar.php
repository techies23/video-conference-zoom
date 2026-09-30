<?php

namespace Codemanas\VczApi\Zoom\Schema;

class Webinar {

	/**
	 * List webinars for a user.
	 * GET /users/{user_id}/webinars
	 */
	public static function list(): array {
		return array(
			'operation' => SchemaManager::WEBINAR_LIST,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/webinars',
			'http'      => array(
				'method'      => 'GET',
				'path'        => '/users/{user_id}/webinars',
				'path_params' => array(
					'user_id' => 'user_id',
				),
			),
			'fields'    => array(
				// Path
				'user_id'         => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'path',
					'trim'     => true,
				),
				// Query
				'page_size'       => array(
					'type'     => 'int',
					'default'  => 30,
					'min'      => 1,
					'max'      => 300,
					'location' => 'query',
				),
				'page_number'     => array(
					'type'     => 'int',
					'min'      => 1,
					'location' => 'query',
				),
				'next_page_token' => array(
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
		);
	}

	/**
	 * Create a webinar.
	 * POST /users/{user_id}/webinars
	 */
	public static function create(): array {
		return array(
			'operation'        => SchemaManager::WEBINAR_CREATE,
			'docs'             => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/webinarCreate',
			'http'             => array(
				'method'      => 'POST',
				'path'        => '/users/{user_id}/webinars',
				'path_params' => array(
					'user_id' => 'user_id',
				),
			),
			'fields'           => array(
				// Path
				'user_id'              => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'path',
					'trim'     => true,
				),
				// Body
				'topic'                => array( 'type' => 'string', 'required' => true, 'location' => 'body', 'max_len' => 200, 'trim' => true ),
				'agenda'               => array( 'type' => 'string', 'location' => 'body', 'max_len' => 2000 ),
				'type'                 => array( 'type' => 'int', 'default' => 5, 'enum' => array( 5, 6, 9 ), 'location' => 'body' ), // 5: Scheduled, 6: Recurring (no fixed time), 9: Recurring (fixed time)
				'start_time'           => array( 'type' => 'string', 'location' => 'body' ),
				'duration'             => array( 'type' => 'int', 'default' => 60, 'min' => 1, 'max' => 1440, 'location' => 'body' ),
				'timezone'             => array( 'type' => 'string', 'location' => 'body' ),
				'password'             => array( 'type' => 'string', 'location' => 'body', 'max_len' => 10 ),
				'default_passcode'     => array( 'type' => 'bool', 'default' => true, 'location' => 'body' ),
				'schedule_for'         => array( 'type' => 'string', 'location' => 'body' ),
				'template_id'          => array( 'type' => 'string', 'location' => 'body' ),
				'tracking_fields'      => array( 'type' => 'array', 'location' => 'body' ),
				'recurrence'           => array(
					'type'     => 'object',
					'location' => 'body',
					'schema'   => array(
						'type'             => array( 'type' => 'int', 'required' => true, 'enum' => array( 1, 2, 3 ) ),
						'repeat_interval'  => array( 'type' => 'int' ),
						'weekly_days'      => array( 'type' => 'string' ),
						'monthly_day'      => array( 'type' => 'int', 'min' => 1, 'max' => 31 ),
						'monthly_week'     => array( 'type' => 'int', 'enum' => array( -1, 1, 2, 3, 4 ) ),
						'monthly_week_day' => array( 'type' => 'int', 'enum' => array( 1, 2, 3, 4, 5, 6, 7 ) ),
						'end_times'        => array( 'type' => 'int', 'default' => 1, 'max' => 60 ),
						'end_date_time'    => array( 'type' => 'string' ),
					),
				),
				'is_simulive'          => array( 'type' => 'bool', 'location' => 'body' ),
				'record_file_id'       => array( 'type' => 'string', 'location' => 'body' ),
				'transition_to_live'   => array( 'type' => 'bool', 'location' => 'body' ),
				'simulive_delay_start' => array(
					'type'     => 'object',
					'location' => 'body',
					'schema'   => array(
						'enable'   => array( 'type' => 'bool' ),
						'time'     => array( 'type' => 'int' ),
						'timeunit' => array( 'type' => 'string', 'default' => 'second', 'enum' => array( 'second', 'minute' ) ),
					),
				),

				'settings'             => array(
					'type'     => 'object',
					'location' => 'body',
					'schema'   => WebinarSettings::schema(),
				),
			),
			'compat'           => array(
				'userId'                 => 'user_id',
				'host_id'                => 'user_id',
				'hostId'                 => 'user_id',
				'webinarTopic'           => 'topic',
				'start_date'             => 'start_time',
				'option_host_video'      => 'settings.host_video',
				'option_panelists_video' => 'settings.panelists_video',
				'option_auto_recording'  => 'settings.auto_recording',
				'alternative_host_ids'   => 'settings.alternative_hosts',
			),
			'compat_transform' => array(
				array( 'from' => 'alternative_host_ids', 'to' => 'settings.alternative_hosts', 'op' => 'implode', 'args' => array( 'separator' => ';' ) ),
				array( 'from' => 'agenda', 'to' => 'agenda', 'op' => 'truncate', 'args' => array( 'max' => 2000 ) ),
				array( 'from' => 'password', 'to' => 'password', 'op' => 'truncate', 'args' => array( 'max' => 10 ) ),
			),
		);
	}

	/**
	 * Get a single webinar.
	 * GET /webinars/{webinar_id}
	 */
	public static function get(): array {
		return array(
			'operation' => SchemaManager::WEBINAR_GET,
			'http'      => array(
				'method'      => 'GET',
				'path'        => '/webinars/{webinar_id}',
				'path_params' => array( 'webinar_id' => 'webinar_id' ),
			),
			'fields'    => array(
				'webinar_id' => array( 'type' => 'string', 'required' => true, 'location' => 'path' ),
			),
			'compat'    => array(
				'webinarId' => 'webinar_id',
				'id'        => 'webinar_id',
			),
		);
	}

	/**
	 * Update a webinar.
	 * PATCH /webinars/{webinar_id}
	 */
	public static function update(): array {
		return array(
			'operation'        => SchemaManager::WEBINAR_UPDATE,
			'docs'             => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/webinarUpdate',
			'http'             => array(
				'method'      => 'PATCH',
				'path'        => '/webinars/{webinar_id}',
				'path_params' => array( 'webinar_id' => 'webinar_id' ),
			),
			'fields'           => array(
				'webinar_id'           => array( 'type' => 'string', 'required' => true, 'location' => 'path', 'trim' => true ),
				'topic'                => array( 'type' => 'string', 'location' => 'body', 'max_len' => 200, 'trim' => true ),
				'agenda'               => array( 'type' => 'string', 'location' => 'body', 'max_len' => 2000 ),
				'type'                 => array( 'type' => 'int', 'enum' => array( 5, 6, 9 ), 'location' => 'body' ),
				'start_time'           => array( 'type' => 'string', 'location' => 'body' ),
				'duration'             => array( 'type' => 'int', 'min' => 1, 'max' => 1440, 'location' => 'body' ),
				'timezone'             => array( 'type' => 'string', 'location' => 'body' ),
				'password'             => array( 'type' => 'string', 'location' => 'body', 'max_len' => 10 ),
				'default_passcode'     => array( 'type' => 'bool', 'location' => 'body' ),
				'schedule_for'         => array( 'type' => 'string', 'location' => 'body' ),
				'template_id'          => array( 'type' => 'string', 'location' => 'body' ),
				'tracking_fields'      => array( 'type' => 'array', 'location' => 'body' ),
				'recurrence'           => array(
					'type'     => 'object',
					'location' => 'body',
					'schema'   => array(
						'type'             => array( 'type' => 'int', 'enum' => array( 1, 2, 3 ) ),
						'repeat_interval'  => array( 'type' => 'int' ),
						'weekly_days'      => array( 'type' => 'string' ),
						'monthly_day'      => array( 'type' => 'int', 'min' => 1, 'max' => 31 ),
						'monthly_week'     => array( 'type' => 'int', 'enum' => array( -1, 1, 2, 3, 4 ) ),
						'monthly_week_day' => array( 'type' => 'int', 'enum' => array( 1, 2, 3, 4, 5, 6, 7 ) ),
						'end_times'        => array( 'type' => 'int', 'max' => 60 ),
						'end_date_time'    => array( 'type' => 'string' ),
					),
				),
				'is_simulive'          => array( 'type' => 'bool', 'location' => 'body' ),
				'record_file_id'       => array( 'type' => 'string', 'location' => 'body' ),
				'transition_to_live'   => array( 'type' => 'bool', 'location' => 'body' ),
				'simulive_delay_start' => array(
					'type'     => 'object',
					'location' => 'body',
					'schema'   => array(
						'enable'   => array( 'type' => 'bool' ),
						'time'     => array( 'type' => 'int' ),
						'timeunit' => array( 'type' => 'string', 'enum' => array( 'second', 'minute' ) ),
					),
				),
				'settings'             => array(
					'type'     => 'object',
					'location' => 'body',
					'schema'   => WebinarSettings::schema( true ),
				),
			),
			'compat'           => array(
				'webinarId'              => 'webinar_id',
				'id'                     => 'webinar_id',
				'start_date'             => 'start_time',
				'option_host_video'      => 'settings.host_video',
				'option_panelists_video' => 'settings.panelists_video',
				'option_auto_recording'  => 'settings.auto_recording',
				'alternative_host_ids'   => 'settings.alternative_hosts',
			),
			'compat_transform' => array(
				array( 'from' => 'alternative_host_ids', 'to' => 'settings.alternative_hosts', 'op' => 'implode', 'args' => array( 'separator' => ';' ) ),
				array( 'from' => 'agenda', 'to' => 'agenda', 'op' => 'truncate', 'args' => array( 'max' => 2000 ) ),
				array( 'from' => 'password', 'to' => 'password', 'op' => 'truncate', 'args' => array( 'max' => 10 ) ),
			),
		);
	}

	/**
	 * Update a webinar survey.
	 * PATCH /webinars/{webinar_id}/survey
	 */
	public static function updateSurvey(): array {
		return array(
			'operation' => SchemaManager::WEBINAR_SURVEY_UPDATE,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/webinarSurveyUpdate',
			'http'      => array(
				'method'      => 'PATCH',
				'path'        => '/webinars/{webinar_id}/survey',
				'path_params' => array( 'webinar_id' => 'webinar_id' ),
			),
			'fields'    => array(
				'webinar_id'                 => array( 'type' => 'string', 'required' => true, 'location' => 'path', 'trim' => true ),
				'show_in_the_browser'        => array( 'type' => 'bool', 'default' => true, 'location' => 'body' ),
				'show_in_the_follow_up_email' => array( 'type' => 'bool', 'default' => false, 'location' => 'body' ),
				'third_party_survey'         => array( 'type' => 'string', 'max_len' => 64, 'location' => 'body' ),
				'custom_survey'              => array(
					'type'     => 'object',
					'location' => 'body',
					'schema'   => array(
						'title'              => array( 'type' => 'string', 'max_len' => 64 ),
						'anonymous'          => array( 'type' => 'bool', 'default' => false ),
						'numbered_questions' => array( 'type' => 'bool', 'default' => false ),
						'show_question_type' => array( 'type' => 'bool', 'default' => false ),
						'feedback'           => array( 'type' => 'string', 'max_len' => 320 ),
						'questions'          => array(
							'type'  => 'array',
							'items' => array(
								'type'   => 'object',
								'schema' => array(
									'name'                 => array( 'type' => 'string', 'max_len' => 420 ),
									'type'                 => array( 'type' => 'string', 'enum' => array( 'single', 'multiple', 'matching', 'rank_order', 'short_answer', 'long_answer', 'fill_in_the_blank', 'rating_scale' ) ),
									'answer_required'      => array( 'type' => 'bool', 'default' => false ),
									'show_as_dropdown'     => array( 'type' => 'bool', 'default' => false ),
									'answers'              => array( 'type' => 'array', 'min_items' => 2, 'max_items' => 50 ),
									'prompts'              => array( 'type' => 'array', 'min_items' => 2, 'max_items' => 10 ),
									'answer_min_character' => array( 'type' => 'int', 'min' => 1 ),
									'answer_max_character' => array( 'type' => 'int' ),
									'rating_min_value'     => array( 'type' => 'int', 'min' => 0 ),
									'rating_max_value'     => array( 'type' => 'int', 'max' => 10 ),
									'rating_min_label'     => array( 'type' => 'string', 'max_len' => 50 ),
									'rating_max_label'     => array( 'type' => 'string', 'max_len' => 50 ),
								),
							),
						),
					),
				),
			),
			'compat'    => array(
				'webinarId' => 'webinar_id',
				'id'        => 'webinar_id',
			),
		);
	}

	/**
	 * Delete a webinar.
	 * DELETE /webinars/{webinar_id}
	 */
	public static function delete(): array {
		return array(
			'operation' => SchemaManager::WEBINAR_DELETE,
			'http'      => array(
				'method'      => 'DELETE',
				'path'        => '/webinars/{webinar_id}',
				'path_params' => array( 'webinar_id' => 'webinar_id' ),
			),
			'fields'    => array(
				'webinar_id'              => array( 'type' => 'string', 'required' => true, 'location' => 'path' ),
				'cancel_webinar_reminder' => array( 'type' => 'string', 'location' => 'query' ),
			),
			'compat'    => array(
				'webinarId' => 'webinar_id',
				'id'        => 'webinar_id',
			),
		);
	}
}