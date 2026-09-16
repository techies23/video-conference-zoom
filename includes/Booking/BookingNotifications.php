<?php

namespace Codemanas\VczApi\Booking;

class BookingNotifications {
	private static ?BookingNotifications $_instance = null;

	public static function get_instance(): ?BookingNotifications {
		return ( self::$_instance == null ) ? self::$_instance = new self() : self::$_instance;
	}

	private function __construct() {
		add_action('vczapi_meeting_booker_booking_created',[$this,'on_meeting_created']);
	}

	public function on_meeting_created(  ) {

	}
}