<?php

namespace Codemanas\VczApi\Zoom\Schema;

class Webinar {
	public const WEBINAR_CREATE = 'webinar.create';
	public const WEBINAR_UPDATE = 'webinar.update';
	public const WEBINAR_LIST   = 'webinar.list';
	public const WEBINAR_GET    = 'webinar.get';
	public const WEBINAR_DELETE = 'webinar.delete';

	/**
	 * Returns the schema array for an operation.
	 * An empty array indicates unknown/unsupported operation.
	 */
	public function get(string $operation): array
	{
		switch ($operation) {
			case self::WEBINAR_CREATE:
				return $this->webinarCreate();
			case self::WEBINAR_UPDATE:
				return $this->webinarUpdate();
			case self::WEBINAR_LIST:
				return $this->webinarList();
			case self::WEBINAR_GET:
				return $this->webinarGET();
			case self::WEBINAR_DELETE:
				return $this->webinarDelete();
			default:
				return [];
		}
	}

	private function webinarCreate(): array
	{
		// This schema maps canonical inputs to Zoom's expected webinar payload.
		// The transport layer sends $data as-is for webinars, so we target Zoom field names directly.
		return [
			'meta'   => [
				'operation' => self::WEBINAR_CREATE,
				'version'   => 1,
			],
			'fields' => [
				// Used for endpoint path, not body.
				'userId' => [
					'type'     => 'string',
					'required' => true,
					'mapFrom'  => ['host_id', 'hostId'],
				],

				// Zoom webinar payload fields
				'topic' => [
					'type'      => 'string',
					'required'  => true,
					'maxLength' => 200,
					'sanitize'  => ['trim', 'strip_tags_soft'],
					'target'    => 'topic',
				],
				'agenda' => [
					'type'      => 'string',
					'required'  => false,
					'maxLength' => 2000,
					'sanitize'  => ['trim', 'strip_all_html'],
					'target'    => 'agenda',
				],
				// Accept local date string; builder converts to UTC ISO8601 and writes to start_time
				'start_date' => [
					'type'      => 'datetime-local',
					'required'  => true,
					'sanitize'  => ['trim'],
					'transform' => ['to_zoom_start_time'],
					'target'    => 'start_time',
				],
				'timezone' => [
					'type'     => 'string',
					'required' => true,
					'validate' => ['olson_timezone'],
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
					'default'  => 5, // Zoom webinar default (live webinar)
					'enum'     => [5, 6, 9], // example: live, recurring, recurring-with-fixed-time
					'target'   => 'type',
				],

				// Optional: alternative host IDs -> settings.alternative_hosts as comma-separated
				'alternative_host_ids' => [
					'type'      => 'array[string]',
					'required'  => false,
					'transform' => ['join_commas:settings.alternative_hosts'],
				],

				// Minimal settings example
				'settings.meeting_authentication' => [
					'type'     => 'bool',
					'required' => false,
					'default'  => false,
					'target'   => 'settings.meeting_authentication',
				],
			],
		];
	}

	private function webinarUpdate(): array
	{
		$schema = $this->webinarCreate();
		$schema['meta']['operation'] = self::WEBINAR_UPDATE;

		// webinar_id used in URL, not body.
		$schema['fields']['webinar_id'] = [
			'type'     => 'string',
			'required' => true,
			'mapFrom'  => ['id'],
		];

		// topic optional on update
		$schema['fields']['topic']['required'] = false;

		return $schema;
	}

	private function webinarList(): array
	{
		return [
			'meta'   => [
				'operation' => self::WEBINAR_LIST,
				'version'   => 1,
			],
			'fields' => [
				'userId' => [
					'type'     => 'string',
					'required' => true,
					'mapFrom'  => ['host_id', 'hostId'],
				],
				'page_size' => [
					'type'     => 'int',
					'required' => false,
					'default'  => 300,
					'min'      => 1,
					'target'   => 'page_size',
				],
			],
		];
	}

	private function webinarGET(): array
	{
		return [
			'meta'   => [
				'operation' => self::WEBINAR_GET,
				'version'   => 1,
			],
			'fields' => [
				'webinar_id' => [
					'type'     => 'string',
					'required' => true,
					'mapFrom'  => ['id'],
				],
			],
		];
	}

	private function webinarDelete(): array
	{
		return [
			'meta'   => [
				'operation' => self::WEBINAR_DELETE,
				'version'   => 1,
			],
			'fields' => [
				'webinar_id' => [
					'type'     => 'string',
					'required' => true,
					'mapFrom'  => ['id'],
				],
			],
		];
	}
}