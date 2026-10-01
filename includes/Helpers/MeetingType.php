<?php

namespace Codemanas\VczApi\Helpers;

class MeetingType {
	// Meeting type constants: https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/meetingCreate
	public const TYPE_INSTANT                 = 1;
	public const TYPE_SCHEDULED               = 2;
	public const TYPE_RECURRING_NO_FIXED_TIME = 3;
	public const TYPE_PMI                     = 4;
	public const TYPE_RECURRING_FIXED_TIME    = 8;
	public const TYPE_SCREEN_SHARE_ONLY       = 10;

	// Webinar type constants: https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/webinarCreate
	public const TYPE_WEBINAR_DEFAULT                 = 5;
	public const TYPE_WEBINAR_RECURRING_NO_FIXED_TIME = 6;
	public const TYPE_WEBINAR_RECURRING_FIXED_TIME    = 9;

	private static array $MEETING_TYPES = [
		'instant'                 => self::TYPE_INSTANT,
		'scheduled'               => self::TYPE_SCHEDULED,
		'recurring_no_fixed_time' => self::TYPE_RECURRING_NO_FIXED_TIME,
		'pmi'                     => self::TYPE_PMI,
		'recurring_fixed_time'    => self::TYPE_RECURRING_FIXED_TIME,
		'screen_share_only'       => self::TYPE_SCREEN_SHARE_ONLY,
	];

	private static array $WEBINAR_TYPES = [
		'default'                 => self::TYPE_WEBINAR_DEFAULT,
		'recurring_no_fixed_time' => self::TYPE_WEBINAR_RECURRING_NO_FIXED_TIME,
		'recurring_fixed_time'    => self::TYPE_WEBINAR_RECURRING_FIXED_TIME,
	];

	public static function getCptMeetingType( string $type ): int {
		$types = [
			1 => self::$MEETING_TYPES['scheduled'], //Meeting
			2 => self::$WEBINAR_TYPES['default'] //Webinar
		];

		return $types[ $type ];
	}

	/**
	 * Determines if the given meeting type is a Personal Meeting ID (PMI).
	 *
	 * @param string|int $meeting_type The type of the meeting. Expected values include:
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
	 * @param string|int $meeting_type The type of the meeting. Expected values include:
	 *
	 * @return bool Returns true if the meeting type is a webinar, false otherwise.
	 */
	public static function is_webinar( $meeting_type ): bool {
		return in_array( self::toInteger( $meeting_type ), array_values( self::$WEBINAR_TYPES ) );
	}


	/**
	 * Determines if the given meeting type is a meeting.
	 *
	 * @param string|int $meeting_type The type of the meeting. Expected values include:
	 *
	 * @return bool Returns true if the meeting type is a meeting, false otherwise.
	 */
	public static function is_meeting( $meeting_type ): bool {
		return in_array( self::toInteger( $meeting_type ), array_values( self::$MEETING_TYPES ) );
	}

	/**
	 * Checks if the given meeting type is a recurring meeting.
	 *
	 * @param string|int $meeting_type The meeting type to be checked.
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
	 * @param string|int $meeting_type The type of the meeting. Expected values include:
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
	 * @param string|int $meeting_type The type of the meeting. Expected values include:
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
	 * @param string|int $meeting_type The type of the meeting. Expected values include:
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
	 * @param string|int $meeting_type The type of the meeting. Expected values include:
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
	 * @param string|int $meeting_type The type of the meeting. Expected values include:
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
	 * @param string|int $meeting_type The type of the meeting. Expected values include:
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
	 * @param string|int $meeting_type
	 *
	 * @return bool
	 */
	public static function is_scheduled_meeting( $meeting_type ): bool {
		return self::$MEETING_TYPES['scheduled'] === self::toInteger( $meeting_type );
	}

	/**
	 * @param string|int $meeting_type
	 *
	 * @return bool
	 */
	public static function is_scheduled_webinar( $meeting_type ): bool {
		return self::$WEBINAR_TYPES['default'] === self::toInteger( $meeting_type );
	}

	/**
	 * @param string|int $meeting_type
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
	 * @param mixed $value The value to be converted to an integer.
	 *
	 * @return int The integer representation of the given value.
	 */
	private static function toInteger( $value ): int {
		return (int) $value;
	}

	/**
	 * Returns all available meeting types map.
	 *
	 * @return array<string, int>
	 */
	public static function get_meeting_types(): array {
		return self::$MEETING_TYPES;
	}

	/**
	 * Returns all available webinar types map.
	 *
	 * @return array<string, int>
	 */
	public static function get_webinar_types(): array {
		return self::$WEBINAR_TYPES;
	}

	/**
	 * Retrieves the integer meeting type for a given meeting type key or integer ID.
	 *
	 * @param string|int $meeting_type The meeting type key (e.g. 'scheduled', 'pmi') or integer ID.
	 * @param int|null   $default      Default fallback value if key/type is not found.
	 *
	 * @return int|null Returns the meeting type integer, or default/null if not found.
	 */
	public static function get_meeting_type( string|int $meeting_type, ?int $default = null ): ?int {
		if ( is_string( $meeting_type ) && isset( self::$MEETING_TYPES[ $meeting_type ] ) ) {
			return self::$MEETING_TYPES[ $meeting_type ];
		}

		$int_type = self::toInteger( $meeting_type );
		if ( in_array( $int_type, self::$MEETING_TYPES, true ) ) {
			return $int_type;
		}

		return $default;
	}

	/**
	 * Retrieves the integer webinar type for a given webinar type key or integer ID.
	 *
	 * @param string|int $webinar_type The webinar type key (e.g. 'default', 'recurring_fixed_time') or integer ID.
	 * @param int|null   $default      Default fallback value if key/type is not found.
	 *
	 * @return int|null Returns the webinar type integer, or default/null if not found.
	 */
	public static function get_webinar_type( string|int $webinar_type, ?int $default = null ): ?int {
		if ( is_string( $webinar_type ) && isset( self::$WEBINAR_TYPES[ $webinar_type ] ) ) {
			return self::$WEBINAR_TYPES[ $webinar_type ];
		}

		$int_type = self::toInteger( $webinar_type );
		if ( in_array( $int_type, self::$WEBINAR_TYPES, true ) ) {
			return $int_type;
		}

		return $default;
	}

	/**
	 * Retrieves the string key/name for a given meeting type integer ID.
	 *
	 * @param string|int $meeting_type Meeting type integer ID or numeric string.
	 *
	 * @return string|null Returns the type name (e.g. 'scheduled'), or null if not found.
	 */
	public static function get_meeting_type_name( string|int $meeting_type ): ?string {
		$type = self::toInteger( $meeting_type );
		$key  = array_search( $type, self::$MEETING_TYPES, true );

		return false !== $key ? $key : null;
	}

	/**
	 * Retrieves the string key/name for a given webinar type integer ID.
	 *
	 * @param string|int $webinar_type Webinar type integer ID or numeric string.
	 *
	 * @return string|null Returns the type name (e.g. 'default'), or null if not found.
	 */
	public static function get_webinar_type_name( string|int $webinar_type ): ?string {
		$type = self::toInteger( $webinar_type );
		$key  = array_search( $type, self::$WEBINAR_TYPES, true );

		return false !== $key ? $key : null;
	}

}