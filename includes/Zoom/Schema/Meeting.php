<?php

namespace Codemanas\VczApi\Zoom\Schema;

class Meeting {
	public const MEETING_CREATE = 'meeting.create';
	public const MEETING_UPDATE = 'meeting.update';
	public const MEETING_LIST   = 'meeting.list';
	public const MEETING_GET    = 'meeting.get';
	public const MEETING_DELETE = 'meeting.delete';

	/**
	 * Returns the schema array for an operation.
	 * An empty array indicates unknown/unsupported operation.
	 */
	public function get(string $operation): array
	{
		switch ($operation) {
			case self::MEETING_CREATE:
				return $this->meetingCreate();
			case self::MEETING_UPDATE:
				return $this->meetingUpdate();
			case self::MEETING_LIST:
				return $this->meetingList();
			case self::MEETING_GET:
				return $this->meetingGET();
			case self::MEETING_DELETE:
				return $this->meetingDelete();
			default:
				return [];
		}
	}

	private function meetingCreate(): array
	{
		// Canonical inputs -> mapped to legacy field names expected by your API layer.
		// Mapping is driven by 'target' and optional 'invertOnWrite' flags.
		return [
			'meta'   => [
				'operation' => self::MEETING_CREATE,
				'version'   => 1,
			],
			'fields' => [
				// Used for endpoint path, not body (kept as-is).
				'userId' => [
					'type'     => 'string',
					'required' => true,
					'mapFrom'  => ['host_id', 'hostId'],
					// target omitted: caller extracts userId to build the endpoint
				],

				// Top-level fields
				'topic' => [
					'type'      => 'string',
					'required'  => true,
					'maxLength' => 200,
					'sanitize'  => ['trim', 'strip_tags_soft'],
					'mapFrom'   => ['meetingTopic', 'title'],
					'target'    => 'meetingTopic', // legacy field name expected by API class
				],
				'agenda' => [
					'type'      => 'string',
					'required'  => false,
					'maxLength' => 2000,
					'sanitize'  => ['trim', 'strip_all_html'],
					'target'    => 'agenda',
				],
				// We keep start_date as provided (the legacy class converts it to UTC internally)
				'start_date' => [
					'type'      => 'datetime-local',
					'required'  => true,
					'sanitize'  => ['trim'],
					'mapFrom'   => ['start_time', 'date'],
					'target'    => 'start_date',
				],
				'timezone' => [
					'type'     => 'string',
					'required' => true,
					'validate' => ['olson_timezone'],
					'mapFrom'  => ['tz'],
					'target'   => 'timezone',
				],
				'duration' => [
					'type'     => 'int',
					'required' => false,
					'default'  => 60,
					'min'      => 1,
					'target'   => 'duration',
				],
				'password' => [
					'type'      => 'string',
					'required'  => false,
					'maxLength' => 10,
					'sanitize'  => ['trim'],
					'target'    => 'password',
				],
				'type' => [
					'type'     => 'int',
					'required' => false,
					'default'  => 2, // Scheduled
					'enum'     => [1, 2, 3, 8],
					'target'   => 'type',
				],
				// Legacy create method accepts array and joins internally; pass through as array
				'alternative_host_ids' => [
					'type'     => 'array[string]',
					'required' => false,
					'target'   => 'alternative_host_ids',
				],

				// Settings (canonical) mapped to legacy flat option_* field names
				'settings.meeting_authentication' => [
					'type'     => 'bool',
					'required' => false,
					'default'  => false,
					'mapFrom'  => ['meeting_authentication'],
					'target'   => 'meeting_authentication',
				],
				'settings.join_before_host' => [
					'type'     => 'bool',
					'required' => false,
					'default'  => false,
					'mapFrom'  => ['join_before_host', 'jbh'],
					'target'   => 'join_before_host',
				],
				'settings.jbh_time' => [
					'type'     => 'int',
					'required' => false,
					'default'  => 0,
					'min'      => 0,
					'target'   => 'jbh_time',
				],
				'settings.host_video' => [
					'type'     => 'bool',
					'required' => false,
					'default'  => false,
					'mapFrom'  => ['option_host_video'],
					'target'   => 'option_host_video',
				],
				'settings.participant_video' => [
					'type'     => 'bool',
					'required' => false,
					'default'  => false,
					'mapFrom'  => ['option_participants_video', 'participant_video'],
					'target'   => 'option_participants_video',
				],
				'settings.mute_upon_entry' => [
					'type'     => 'bool',
					'required' => false,
					'default'  => false,
					'mapFrom'  => ['option_mute_participants'],
					'target'   => 'option_mute_participants',
				],
				'settings.auto_recording' => [
					'type'     => 'string',
					'required' => false,
					'default'  => 'none',
					'enum'     => ['none', 'local', 'cloud'],
					'mapFrom'  => ['option_auto_recording'],
					'target'   => 'option_auto_recording',
				],
				// Canonical waiting_room -> legacy disable_waiting_room (inverted)
				'settings.waiting_room' => [
					'type'          => 'bool',
					'required'      => false,
					'default'       => true,
					'mapFrom'       => ['waiting_room'],
					'target'        => 'disable_waiting_room',
					'invertOnWrite' => true, // write inverted boolean to target
				],
			],
		];
	}

	private function meetingUpdate(): array
	{
		$schema = $this->meetingCreate();
		$schema['meta']['operation'] = self::MEETING_UPDATE;

		// meeting_id is required on update (used in URL, not body)
		$schema['fields']['meeting_id'] = [
			'type'     => 'string',
			'required' => true,
			'mapFrom'  => ['id'],
			// no target; it will be removed from body and used in endpoint
		];

		// userId not required on update; topic optional
		$schema['fields']['userId']['required'] = false;
		$schema['fields']['topic']['required']  = false;

		return $schema;
	}

	private function meetingList(): array
	{
		// Host userId is required by legacy listMeetings(); the rest are optional filters/pagination.
		return [
			'meta'   => [
				'operation' => self::MEETING_LIST,
				'version'   => 1,
			],
			'fields' => [
				'userId' => [
					'type'     => 'string',
					'required' => true,
					'mapFrom'  => ['host_id', 'hostId'],
				],
				// The type of meeting list to return.
				'type' => [
					'type'      => 'string',
					'required'  => false,
					'default'   => 'scheduled',
					'enum'      => ['scheduled', 'live', 'upcoming', 'upcoming_meetings', 'previous_meetings'],
					'target'    => 'type',
				],
				// Pagination size: default 30, max 300.
				'page_size' => [
					'type'     => 'int',
					'required' => false,
					'default'  => 30,
					'min'      => 1,
					'max'      => 300,
					'target'   => 'page_size',
				],
				// Cursor-based pagination token.
				'next_page_token' => [
					'type'     => 'string',
					'required' => false,
					'target'   => 'next_page_token',
				],
				// Page number (used by some endpoints/clients).
				'page_number' => [
					'type'     => 'int',
					'required' => false,
					'default'  => 1,
					'min'      => 1,
					'target'   => 'page_number',
				],
				// Date range filters (YYYY-MM-DD).
				'from' => [
					'type'     => 'date',
					'required' => false,
					'target'   => 'from',
				],
				'to' => [
					'type'     => 'date',
					'required' => false,
					'target'   => 'to',
				],
				// Timezone to interpret 'from' and 'to'.
				'timezone' => [
					'type'     => 'string',
					'required' => false,
					'validate' => ['olson_timezone'],
					'target'   => 'timezone',
				],
			],
		];
	}

	private function meetingGET(): array
	{
		return [
			'meta'   => [
				'operation' => self::MEETING_GET,
				'version'   => 1,
			],
			'fields' => [
				'meeting_id' => [
					'type'     => 'string',
					'required' => true,
					'mapFrom'  => ['id'],
				],
			],
		];
	}

	private function meetingDelete(): array
	{
		return [
			'meta'   => [
				'operation' => self::MEETING_DELETE,
				'version'   => 1,
			],
			'fields' => [
				'meeting_id' => [
					'type'     => 'string',
					'required' => true,
					'mapFrom'  => ['id'],
				],
			],
		];
	}
}