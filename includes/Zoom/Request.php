<?php

namespace Codemanas\VczApi\Zoom;

use Codemanas\VczApi\Zoom\Request\Meeting as MeetingRequest;
use Codemanas\VczApi\Zoom\Request\Webinar as WebinarRequest;
use Codemanas\VczApi\Zoom\Request\User as UserRequest;

class Request {
	private bool $useLegacyApi;

	/** @var \Zoom_Video_Conferencing_Api|null */
	private $legacyApi;

	/** @var object|null Future modern client (to be implemented) */
	private $modernClient;

	private SchemaRegistry $schemas;
	private PayloadBuilder $builder;

	private MeetingRequest $meetings;
	private WebinarRequest $webinars;
	private UserRequest $users;

	/**
	 * @param  bool  $useLegacyApi  When true, routes via legacy API class (default).
	 * @param  null|object  $legacyApi  Optional legacy API instance. Defaults to zoom_conference().
	 * @param  null|object  $modernClient  Optional modern client adapter (future).
	 * @param  null|SchemaRegistry  $schemas
	 * @param  null|PayloadBuilder  $builder
	 */
	public function __construct(
		bool $useLegacyApi = true,
		$legacyApi = null,
		$modernClient = null,
		?SchemaRegistry $schemas = null,
		?PayloadBuilder $builder = null
	) {
		$this->useLegacyApi = $useLegacyApi;
		$this->legacyApi    = $legacyApi ?: ( function_exists( 'zoom_conference' ) ? zoom_conference() : null );
		$this->modernClient = $modernClient;
		$this->schemas      = $schemas ?: new SchemaRegistry();
		$this->builder      = $builder ?: new PayloadBuilder();

		$this->meetings = new MeetingRequest( $this->useLegacyApi, $this->legacyApi, $this->modernClient, $this->schemas, $this->builder );
		$this->webinars = new WebinarRequest( $this->useLegacyApi, $this->legacyApi, $this->modernClient, $this->schemas, $this->builder );
		$this->users    = new UserRequest( $this->useLegacyApi, $this->legacyApi, $this->modernClient, $this->schemas, $this->builder );
	}

	/**
	 * Normalize responses:
	 * - If WP_Error, return as-is (short-circuit)
	 * - If JSON string, decode and return decoded value
	 * - Otherwise return response as-is
	 *
	 * @param  mixed  $response
	 *
	 * @return mixed
	 */
	private function normalizeResponse( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( is_string( $response ) ) {
			$trimmed = ltrim( $response );
			if ( $trimmed !== '' && ( $trimmed[0] === '{' || $trimmed[0] === '[' ) ) {
				$decoded = json_decode( $response );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					return $decoded;
				}
			}
		}

		return $response;
	}

	public function createMeeting( array $input ) {
		$result = $this->meetings->createMeeting( $input );

		return $this->normalizeResponse( $result );
	}

	public function updateMeeting( $meetingId, array $input ) {
		$result = $this->meetings->updateMeeting( $meetingId, $input );

		return $this->normalizeResponse( $result );
	}

	public function createWebinar( array $input ) {
		$result = $this->webinars->createWebinar( $input );

		return $this->normalizeResponse( $result );
	}

	public function updateWebinar( $webinarId, array $input ) {
		$result = $this->webinars->updateWebinar( $webinarId, $input );

		return $this->normalizeResponse( $result );
	}

	// New convenience method for listing meetings.
	public function listMeetings( $userId, array $input = [] ) {
		$result = $this->meetings->listMeetings( $userId, $input );

		return $this->normalizeResponse( $result );
	}

	public function getMeetingDetails( $meetingId, array $input = [] ) {
		$result = $this->meetings->getMeeting( $meetingId, $input );

		return $this->normalizeResponse( $result );
	}


}