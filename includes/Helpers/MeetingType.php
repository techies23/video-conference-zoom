<?php

namespace Codemanas\VczApi\Helpers;

class MeetingType {
	//https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/meetingCreate
	private static array $MEETING_TYPES = [
		'instant'                 => 1,
		'scheduled'               => 2,
		'recurring_no_fixed_time' => 3,
		'recurring_fixed_time'    => 8,
		'screen_share_only'       => 10
	];

	//https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/webinarCreate
	private static array $WEBINAR_TYPES = [
		'default'                 => 5,
		'recurring_no_fixed_time' => 6,
		'recurring_fixed_time'    => 9
	];

	public static function is_webinar( $meeting_type ) {
		$meeting_type = (int) $meeting_type;
		$values = array_values( self::$WEBINAR_TYPES );
		return in_array( $meeting_type, $values );
	}

	public static function is_meeting( $meeting_type ) {
		$meeting_type = (int) $meeting_type;
		$values = array_values( self::$MEETING_TYPES );
		return in_array( $meeting_type, $values );
	}

	public static function is_recurring_meeting_or_webinar( $meeting_type ) {
		$meeting_type = (int) $meeting_type;
		if ( self::is_recurring_meeting( $meeting_type ) ) {
			return true;
		} elseif ( self::is_recurring_webinar( $meeting_type ) ) {
			return true;
		}
	}

	public static function is_recurring_meeting( int $meeting_type ) {
		return self::$MEETING_TYPES['recurring_fixed_time'] === $meeting_type || self::$MEETING_TYPES['recurring_no_fixed_time'] === $meeting_type;
	}

	public static function is_recurring_webinar( int $meeting_type ) {
		return self::$WEBINAR_TYPES['recurring_fixed_time'] === $meeting_type || self::$WEBINAR_TYPES['recurring_no_fixed_time'] === $meeting_type;
	}


}