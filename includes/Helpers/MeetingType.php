<?php

namespace Codemanas\VczApi\Helpers;

class MeetingType {
	//https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/meetingCreate
	private static array $MEETING_TYPES = [
		'instant'                 => 1,
		'scheduled'               => 2,
		'recurring_no_fixed_time' => 3,
		'pmi'                     => 4,
		'recurring_fixed_time'    => 8,
		'screen_share_only'       => 10
	];
	//https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/webinarCreate
	private static array $WEBINAR_TYPES = [
		'default'                 => 5,
		'recurring_no_fixed_time' => 6,
		'recurring_fixed_time'    => 9
	];


	/**
	 * Determines if the given meeting type is a Personal Meeting ID (PMI).
	 *
	 * @param  string|int  $meeting_type  The type of the meeting. Expected values include:
	 *                                    - 'pmi': Personal Meeting ID
	 *                                    - 'scheduled': Scheduled Meeting
	 *                                    - 'webinar': Webinar Meeting
	 *
	 * @return bool Returns true if the meeting type is a Personal Meeting ID (PMI),
	 *              false otherwise.
	 */
	public static function is_pmi( $meeting_type ): bool {
		return self::$MEETING_TYPES['pmi'] === self::toInteger( $meeting_type );
	}

	/**
	 * Determines if the given meeting type is a webinar.
	 *
	 * @param  string|int  $meeting_type  The type of the meeting. Expected values include:
	 *
	 * @return bool Returns true if the meeting type is a webinar, false otherwise.
	 */
	public static function is_webinar( $meeting_type ): bool {
		return in_array( self::toInteger( $meeting_type ), array_values( self::$WEBINAR_TYPES ) );
	}


	/**
	 * Determines if the given meeting type is a meeting.
	 *
	 * @param  string|int  $meeting_type  The type of the meeting. Expected values include:
	 *
	 * @return bool Returns true if the meeting type is a meeting, false otherwise.
	 */
	public static function is_meeting( $meeting_type ): bool {
		return in_array( self::toInteger( $meeting_type ), array_values( self::$MEETING_TYPES ) );
	}

	/**
	 * Checks if the given meeting type is a recurring meeting.
	 *
	 * @param  string|int  $meeting_type  The meeting type to be checked.
	 *
	 * @return bool Returns true if the meeting type is a recurring meeting, false otherwise.
	 */
	public static function is_recurring_meeting( $meeting_type ): bool {
		$meeting_type = self::toInteger( $meeting_type );

		return self::$MEETING_TYPES['recurring_fixed_time'] === $meeting_type || self::$MEETING_TYPES['recurring_no_fixed_time'] === $meeting_type;
	}

	/**
	 * Determines if the given meeting type is a recurring webinar.
	 *
	 * @param  string|int  $meeting_type  The type of the meeting. Expected values include:
	 *
	 * @return bool Returns true if the meeting type is a recurring webinar,
	 *              false otherwise.
	 */
	public static function is_recurring_webinar( int $meeting_type ): bool {
		$meeting_type = self::toInteger( $meeting_type );

		return self::$WEBINAR_TYPES['recurring_fixed_time'] === $meeting_type || self::$WEBINAR_TYPES['recurring_no_fixed_time'] === $meeting_type;
	}

	/**
	 * Determines if the given meeting type is a recurring meeting or webinar.
	 *
	 * @param  string|int  $meeting_type  The type of the meeting. Expected values include:
	 *
	 * @return bool Returns true if the meeting type is a recurring meeting or webinar,
	 *              false otherwise.
	 */
	public static function is_recurring_meeting_or_webinar( $meeting_type ): bool {
		return self::is_recurring_meeting( $meeting_type ) || self::is_recurring_webinar( $meeting_type );
	}


	/**
	 * Determines if the given meeting type is a recurring fixed time meeting.
	 *
	 * @param  string|int  $meeting_type  The type of the meeting. Expected values include:
	 *
	 * @return bool Returns true if the meeting type is a recurring fixed time meeting,
	 *              false otherwise.
	 */
	public static function is_recurring_fixed_time_meeting( $meeting_type ): bool {
		return self::$MEETING_TYPES['recurring_fixed_time'] === self::toInteger( $meeting_type );
	}


	/**
	 * Determines if the given meeting type is a recurring no fixed time meeting.
	 *
	 * @param  string|int  $meeting_type  The type of the meeting. Expected values include:
	 *
	 * @return bool Returns true if the meeting type is a recurring no fixed time meeting,
	 *              false otherwise.
	 */
	public static function is_recurring_no_fixed_time_meeting( $meeting_type ): bool {
		return self::$MEETING_TYPES['recurring_no_fixed_time'] === self::toInteger( $meeting_type );
	}

	/**
	 * Determines if the given meeting type is a recurring fixed time webinar.
	 *
	 * @param  string|int  $meeting_type  The type of the meeting. Expected values include:
	 *
	 * @return bool Returns true if the meeting type is a recurring fixed time webinar,
	 *              false otherwise.
	 */
	public static function is_recurring_fixed_time_webinar( $meeting_type ): bool {
		return self::$WEBINAR_TYPES['recurring_fixed_time'] === self::toInteger( $meeting_type );
	}

	/**
	 * Determines if the given meeting type is a recurring no fixed time webinar.
	 *
	 * @param  string|int  $meeting_type  The type of the meeting. Expected values include:
	 *
	 * @return bool Returns true if the meeting type is a recurring no fixed time webinar,
	 **/
	public static function is_recurring_no_fixed_time_webinar( $meeting_type ): bool {
		return self::$WEBINAR_TYPES['recurring_no_fixed_time'] === self::toInteger( $meeting_type );
	}

	public static function is_recurring_fixed_time_webinar_or_meeting( $meeting_type ) {
		$meeting_type = self::toInteger( $meeting_type );

		return self::is_recurring_fixed_time_meeting( $meeting_type ) || self::is_recurring_fixed_time_webinar( $meeting_type );
	}

	/**
	 * @param  string|int  $meeting_type
	 *
	 * @return bool
	 */
	public static function is_scheduled_meeting( $meeting_type ): bool {
		return self::$MEETING_TYPES['scheduled'] === self::toInteger( $meeting_type );
	}

	/**
	 * @param  string|int  $meeting_type
	 *
	 * @return bool
	 */
	public static function is_scheduled_webinar( $meeting_type ): bool {
		return self::$WEBINAR_TYPES['default'] === self::toInteger( $meeting_type );
	}

	/**
	 * @param  string|int  $meeting_type
	 *
	 * @return bool
	 */
	public static function is_scheduled_meeting_or_webinar( $meeting_type ): bool {
		$meeting_type = self::toInteger( $meeting_type );

		return self::is_scheduled_meeting( $meeting_type ) || self::is_scheduled_webinar( $meeting_type );
	}

	/**
	 * Converts a value to an integer.
	 *
	 * @param  mixed  $value  The value to be converted to an integer.
	 *
	 * @return int The integer representation of the given value.
	 */
	private static function toInteger( $value ): int {
		return (int) $value;
	}

}