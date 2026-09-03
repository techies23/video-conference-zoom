<?php

namespace Codemanas\VczApi\Zoom\Schema;

class Report {

	/**
	 * Schema for getting a daily usage report.
	 * GET /report/daily
	 */
	public static function daily(): array {
		return array(
			'operation' => SchemaManager::REPORT_DAILY,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/reportDaily',
			'http'      => array(
				'method' => 'GET',
				'path'   => '/report/daily',
			),
			'fields'    => array(
				'year'  => array(
					'type'     => 'int',
					'location' => 'query',
				),
				'month' => array(
					'type'     => 'int',
					'min'      => 1,
					'max'      => 12,
					'location' => 'query',
				),
			),
		);
	}

	/**
	 * Schema for getting a user report (meetings/webinars held by a user in a timeframe).
	 * GET /report/users/{user_id}/meetings
	 */
	public static function userMeetings(): array {
		return array(
			'operation' => SchemaManager::REPORT_USER_MEETINGS,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/reportUserMeetings',
			'http'      => array(
				'method'      => 'GET',
				'path'        => '/report/users/{user_id}/meetings',
				'path_params' => array(
					'user_id' => 'user_id',
				),
			),
			'fields'    => array(
				'user_id'         => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'path',
					'trim'     => true,
				),
				'from'            => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'query',
				),
				'to'              => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'query',
				),
				'type'            => array(
					'type'     => 'string',
					'default'  => 'past',
					'enum'     => array( 'past', 'pastOne' ),
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
			),
			'compat'    => array(
				'userId' => 'user_id',
				'id'     => 'user_id',
			),
		);
	}

	/**
	 * Schema for getting a meeting participant report.
	 * GET /report/meetings/{meeting_id}/participants
	 */
	public static function meetingParticipants(): array {
		return array(
			'operation' => SchemaManager::REPORT_MEETING_PARTICIPANTS,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/reportMeetingParticipants',
			'http'      => array(
				'method'      => 'GET',
				'path'        => '/report/meetings/{meeting_id}/participants',
				'path_params' => array(
					'meeting_id' => 'meeting_id',
				),
			),
			'fields'    => array(
				'meeting_id'      => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'path',
					'trim'     => true,
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
				'include_fields'  => array(
					'type'     => 'string',
					'location' => 'query',
				),
			),
			'compat'    => array(
				'meetingId' => 'meeting_id',
				'id'        => 'meeting_id',
			),
		);
	}

	/**
	 * Schema for getting a meeting detail report.
	 * GET /report/meetings/{meeting_id}
	 */
	public static function meetingDetails(): array {
		return array(
			'operation' => SchemaManager::REPORT_MEETING_DETAILS,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/reportMeetingDetails',
			'http'      => array(
				'method'      => 'GET',
				'path'        => '/report/meetings/{meeting_id}',
				'path_params' => array(
					'meeting_id' => 'meeting_id',
				),
			),
			'fields'    => array(
				'meeting_id' => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'path',
					'trim'     => true,
				),
			),
			'compat'    => array(
				'meetingId' => 'meeting_id',
				'id'        => 'meeting_id',
			),
		);
	}
}