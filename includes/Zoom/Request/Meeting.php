<?php

namespace Codemanas\VczApi\Zoom\Request;

use Codemanas\VczApi\Zoom\PayloadBuilder;
use Codemanas\VczApi\Zoom\Schema\Meeting as MeetingSchema;
use Codemanas\VczApi\Zoom\SchemaRegistry;
use WP_Error;

class Meeting {
	private bool $useLegacyApi;

	/** @var \Zoom_Video_Conferencing_Api|null */
	private $legacyApi;

	/** @var object|null Future modern client (to be implemented) */
	private $modernClient;

	private SchemaRegistry $schemas;
	private PayloadBuilder $builder;

	public function __construct(
		bool $useLegacyApi,
		$legacyApi,
		$modernClient,
		SchemaRegistry $schemas,
		PayloadBuilder $builder
	) {
		$this->useLegacyApi = $useLegacyApi;
		$this->legacyApi    = $legacyApi;
		$this->modernClient = $modernClient;
		$this->schemas      = $schemas;
		$this->builder      = $builder;
	}

	/**
	 * Create a Zoom meeting.
	 * - Legacy path: pass mapped payload directly to legacy createAMeeting()
	 */
	public function createMeeting( array $input ) {
		$payload = $this->builder->build( 'meeting', MeetingSchema::MEETING_CREATE, $input );
		if ( $payload instanceof WP_Error ) {
			return $payload;
		}

		if ( $this->useLegacyApi ) {
			if ( ! $this->legacyApi || ! method_exists( $this->legacyApi, 'createAMeeting' ) ) {
				return new WP_Error( 'legacy_api_unavailable', 'Legacy API client is not available.' );
			}

			return $this->legacyApi->createAMeeting( $payload );
		}

		return new WP_Error( 'not_implemented', 'Modern client for createMeeting is not implemented yet.' );
	}

	public function updateMeeting( $meetingId, array $input ) {
		$input = [ 'meeting_id' => (string) $meetingId ] + $input;

		$payload = $this->builder->build( 'meeting', MeetingSchema::MEETING_UPDATE, $input );
		if ( $payload instanceof WP_Error ) {
			return $payload;
		}

		if ( $this->useLegacyApi ) {
			if ( ! $this->legacyApi || ! method_exists( $this->legacyApi, 'updateMeetingInfo' ) ) {
				return new WP_Error( 'legacy_api_unavailable', 'Legacy API client is not available.' );
			}

			return $this->legacyApi->updateMeetingInfo( $payload );
		}

		return new WP_Error( 'not_implemented', 'Modern client for updateMeeting is not implemented yet.' );
	}

	/**
	 * Get meeting details by ID.
	 *
	 * @param  string|int  $meetingId  Meeting ID
	 *
	 * @param  string  $userId  Zoom user ID
	 * @param  array  $input
	 *
	 * @return mixed|WP_Error Meeting list or error
	 */
	public function listMeetings( $userId, array $input = [] ) {
		// Ensure userId is included for builder/schema validation.
		$input = [ 'userId' => (string) $userId ] + $input;

		$payload = $this->builder->build( 'meeting', MeetingSchema::MEETING_LIST, $input );
		if ( $payload instanceof WP_Error ) {
			return $payload;
		}

		if ( $this->useLegacyApi ) {
			if ( ! $this->legacyApi || ! method_exists( $this->legacyApi, 'listMeetings' ) ) {
				return new WP_Error( 'legacy_api_unavailable', 'Legacy API client is not available.' );
			}
			// Legacy signature: listMeetings($host_id, $args)
			$hostId = $payload['userId'];
			unset( $payload['userId'] );

			return $this->legacyApi->listMeetings( $hostId, $payload );
		}

		return new WP_Error( 'not_implemented', 'Modern client for listMeetings is not implemented yet.' );
	}

	public function getMeeting( $meetingId, array $input ) {
		// Ensure userId is included for builder/schema validation.
		$input   = [ 'meeting_id' => (string) $meetingId ] + $input;
		$payload = $this->builder->build( 'meeting', MeetingSchema::MEETING_GET, $input );
		if ( $payload instanceof WP_Error ) {
			return $payload;
		}

		if ( $this->useLegacyApi ) {
			if ( ! $this->legacyApi || ! method_exists( $this->legacyApi, 'getMeetingInfo' ) ) {
				return new WP_Error( 'legacy_api_unavailable', 'Legacy API client is not available.' );
			}
			// Legacy signature: listMeetings($host_id, $args)
			$meetingId = $payload['meeting_id'];
			unset( $payload['meeting_id'] );

			return $this->legacyApi->getMeetingInfo( $meetingId, $payload );
		}

		return new WP_Error( 'not_implemented', 'Modern client for listMeetings is not implemented yet.' );
	}
}