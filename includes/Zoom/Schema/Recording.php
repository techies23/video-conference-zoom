<?php

namespace Codemanas\VczApi\Zoom\Schema;

class Recording {

	/**
	 * Schema for listing all cloud recordings of a user.
	 * GET /users/{user_id}/recordings
	 */
	public static function list(): array {
		return array(
			'operation' => SchemaManager::RECORDING_LIST,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/recordingsList',
			'http'      => array(
				'method'      => 'GET',
				'path'        => '/users/{user_id}/recordings',
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
					'doc'      => 'Zoom user ID, email, or "me".',
				),

				// Query
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
				'from'            => array(
					'type'     => 'string',
					'location' => 'query',
					'doc'      => 'Start date in YYYY-MM-DD format.',
				),
				'to'              => array(
					'type'     => 'string',
					'location' => 'query',
					'doc'      => 'End date in YYYY-MM-DD format.',
				),
				'mc'              => array(
					'type'     => 'string',
					'default'  => 'false',
					'location' => 'query',
					'doc'      => 'Query Master Account recordings.',
				),
				'trash'           => array(
					'type'     => 'bool',
					'default'  => false,
					'location' => 'query',
					'doc'      => 'Query trash recordings.',
				),
				'trash_type'      => array(
					'type'     => 'string',
					'default'  => 'meeting_recordings',
					'enum'     => array( 'meeting_recordings', 'recording_file' ),
					'location' => 'query',
				),
			),
			'compat'    => array(
				'userId' => 'user_id',
				'host_id' => 'user_id',
				'hostId' => 'user_id',
			),
		);
	}

	/**
	 * Schema for getting all recording files of a specific meeting.
	 * GET /meetings/{meeting_id}/recordings
	 */
	public static function get(): array {
		return array(
			'operation' => SchemaManager::RECORDING_GET,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/recordingGet',
			'http'      => array(
				'method'      => 'GET',
				'path'        => '/meetings/{meeting_id}/recordings',
				'path_params' => array(
					'meeting_id' => 'meeting_id',
				),
			),
			'fields'    => array(
				// Path
				'meeting_id'            => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'path',
					'trim'     => true,
					'doc'      => 'Meeting ID or double-URL-encoded Meeting UUID.',
				),

				// Query
				'include_fields'        => array(
					'type'     => 'string',
					'location' => 'query',
					'doc'      => 'Pass "download_access_token" to retrieve download access token.',
				),
				'ttl'                   => array(
					'type'     => 'int',
					'default'  => 300,
					'location' => 'query',
					'doc'      => 'Time to live for download_access_token in seconds.',
				),
			),
			'compat'    => array(
				'meetingId' => 'meeting_id',
				'id'        => 'meeting_id',
			),
		);
	}

	/**
	 * Schema for deleting/trashing all recording files of a meeting.
	 * DELETE /meetings/{meeting_id}/recordings
	 */
	public static function delete(): array {
		return array(
			'operation' => SchemaManager::RECORDING_DELETE,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/recordingDelete',
			'http'      => array(
				'method'      => 'DELETE',
				'path'        => '/meetings/{meeting_id}/recordings',
				'path_params' => array(
					'meeting_id' => 'meeting_id',
				),
			),
			'fields'    => array(
				// Path
				'meeting_id' => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'path',
					'trim'     => true,
				),

				// Query
				'action'     => array(
					'type'     => 'string',
					'default'  => 'trash',
					'enum'     => array( 'trash', 'delete' ),
					'location' => 'query',
					'doc'      => 'trash (move to trash) or delete (permanently delete).',
				),
			),
			'compat'    => array(
				'meetingId' => 'meeting_id',
				'id'        => 'meeting_id',
			),
		);
	}

	/**
	 * Schema for deleting/trashing a single recording file from a meeting.
	 * DELETE /meetings/{meeting_id}/recordings/{file_id}
	 */
	public static function deleteFile(): array {
		return array(
			'operation' => SchemaManager::RECORDING_FILE_DELETE,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/recordingDeleteOne',
			'http'      => array(
				'method'      => 'DELETE',
				'path'        => '/meetings/{meeting_id}/recordings/{file_id}',
				'path_params' => array(
					'meeting_id' => 'meeting_id',
					'file_id'    => 'file_id',
				),
			),
			'fields'    => array(
				// Path
				'meeting_id' => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'path',
					'trim'     => true,
				),
				'file_id'    => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'path',
					'trim'     => true,
				),

				// Query
				'action'     => array(
					'type'     => 'string',
					'default'  => 'trash',
					'enum'     => array( 'trash', 'delete' ),
					'location' => 'query',
				),
			),
			'compat'    => array(
				'meetingId' => 'meeting_id',
				'fileId'    => 'file_id',
			),
		);
	}

	/**
	 * Schema for recovering/restoring meeting recordings from trash.
	 * PUT /meetings/{meeting_id}/recordings/status
	 */
	public static function recover(): array {
		return array(
			'operation' => SchemaManager::RECORDING_RECOVER,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/recordingStatusUpdate',
			'http'      => array(
				'method'      => 'PUT',
				'path'        => '/meetings/{meeting_id}/recordings/status',
				'path_params' => array(
					'meeting_id' => 'meeting_id',
				),
			),
			'fields'    => array(
				// Path
				'meeting_id' => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'path',
					'trim'     => true,
				),

				// Body
				'action'     => array(
					'type'     => 'string',
					'required' => true,
					'default'  => 'recover',
					'enum'     => array( 'recover' ),
					'location' => 'body',
				),
			),
			'compat'    => array(
				'meetingId' => 'meeting_id',
			),
		);
	}
}