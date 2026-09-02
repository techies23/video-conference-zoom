<?php

namespace Codemanas\VczApi\Zoom;

use Codemanas\VczApi\Zoom\Service\Meeting as MeetingService;
use Codemanas\VczApi\Zoom\Service\Webinar as WebinarService;

/**
 * Zoom Facade
 *
 * Main entry point for the Zoom API wrapper.
 * Access individual domain services via accessor methods.
 */
class Zoom {

	/** @var MeetingService */
	protected MeetingService $meetingService;

	/** @var WebinarService */
	protected WebinarService $webinarService;

	/**
	 * Optionally inject services for testing or custom implementations.
	 *
	 * @param MeetingService|null $meetingService
	 * @param WebinarService|null $webinarService
	 */
	public function __construct( ?MeetingService $meetingService = null, ?WebinarService $webinarService = null ) {
		$this->meetingService = $meetingService ?: new MeetingService();
		$this->webinarService = $webinarService ?: new WebinarService();
	}

	/**
	 * Access Meeting service operations.
	 *
	 * @return MeetingService
	 */
	public function meetings(): MeetingService {
		return $this->meetingService;
	}

	/**
	 * Access Webinar service operations.
	 *
	 * @return WebinarService
	 */
	public function webinars(): WebinarService {
		return $this->webinarService;
	}
}