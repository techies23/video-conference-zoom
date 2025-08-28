<?php

namespace Codemanas\VczApi\Zoom;

use Codemanas\VczApi\Zoom\Service\Meeting as MeetingService;
use WP_Error;

/**
 * Zoom Facade
 *
 * Public entry point for developers. Delegates to Services.
 * Returns raw arrays on success or WP_Error on failure.
 */
class Zoom {
	/** @var MeetingService */
	protected MeetingService $meetingService;

	/**
	 * Optionally, inject services for testing/customization.
	 *
	 * @param MeetingService|null $meetingService
	 */
	public function __construct( MeetingService $meetingService = null ) {
		$this->meetingService = $meetingService ?: new MeetingService();
	}

	/**
	 * List meetings for a user/host.
	 *
		 * @param array $params e.g. ['user_id' => 'user_email@email.com | user_id', 'page_size' => 30, ...]
	 * @return array|WP_Error
	 */
	public function listMeetings( array $params = array() ) {
		return $this->meetingService->list( $params );
	}

	/**
	 * Create a meeting for a user/host.
	 *
	 * @param array $data e.g. [
	 *   'user_id' => 'me',
	 *   'topic' => 'My Meeting',
	 *   'start_time' => '2025-09-01T10:00:00Z',
	 *   'duration' => 30,
	 *   'settings' => ['waiting_room' => true],
	 * ]
	 * @return array|WP_Error
	 */
	public function createMeeting( array $data = array() ) {
		return $this->meetingService->create( $data );
	}

	/**
	 * Expose the Meeting service for advanced use-cases.
	 *
	 * @return MeetingService
	 */
	public function meetings() {
		return $this->meetingService;
	}
}