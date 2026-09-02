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
				'user_id'     => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'path',
					'trim'     => true,
				),
				// Body
				'topic'       => array( 'type' => 'string', 'required' => true, 'location' => 'body', 'max_len' => 200, 'trim' => true ),
				'agenda'      => array( 'type' => 'string', 'location' => 'body', 'max_len' => 2000 ),
				'type'        => array( 'type' => 'int', 'default' => 5, 'enum' => array( 5, 6, 9 ), 'location' => 'body' ), // 5: Scheduled, 6: Recurring (no fixed time), 9: Recurring (fixed time)
				'start_time'  => array( 'type' => 'string', 'location' => 'body', 'required' => true ),
				'duration'    => array( 'type' => 'int', 'default' => 60, 'min' => 1, 'max' => 1440, 'location' => 'body' ),
				'timezone'    => array( 'type' => 'string', 'location' => 'body' ),
				'password'    => array( 'type' => 'string', 'location' => 'body', 'max_len' => 10 ),

				'settings'    => array(
					'type'     => 'object',
					'location' => 'body',
					'schema'   => array(
						'host_video'                  => array( 'type' => 'bool' ),
						'panelists_video'             => array( 'type' => 'bool' ),
						'practice_session'            => array( 'type' => 'bool', 'default' => false ),
						'hd_video'                    => array( 'type' => 'bool' ),
						'approval_type'               => array( 'type' => 'int', 'enum' => array( 0, 1, 2 ), 'default' => 2 ),
						'registration_type'           => array( 'type' => 'int', 'enum' => array( 1, 2, 3 ), 'default' => 1 ),
						'audio'                       => array( 'type' => 'string', 'enum' => array( 'both', 'telephony', 'voip', 'thirdParty' ), 'default' => 'both' ),
						'auto_recording'              => array( 'type' => 'string', 'enum' => array( 'local', 'cloud', 'none' ), 'default' => 'none' ),
						'allow_multiple_devices'      => array( 'type' => 'bool' ),
						'alternative_hosts'           => array( 'type' => 'string' ),
					),
				),
			),
			'compat'           => array(
				'userId'                    => 'user_id',
				'host_id'                   => 'user_id',
				'hostId'                    => 'user_id',
				'webinarTopic'              => 'topic',
				'start_date'                => 'start_time',
				'option_host_video'         => 'settings.host_video',
				'option_panelists_video'    => 'settings.panelists_video',
				'option_auto_recording'     => 'settings.auto_recording',
				'alternative_host_ids'      => 'settings.alternative_hosts',
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
				'webinar_id' => array( 'type' => 'string', 'required' => true, 'location' => 'path' ),
				'cancel_webinar_reminder' => array( 'type' => 'string', 'location' => 'query' ),
			),
			'compat'    => array(
				'webinarId' => 'webinar_id',
				'id'        => 'webinar_id',
			),
		);
	}
}
