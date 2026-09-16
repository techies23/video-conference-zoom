<?php

namespace Codemanas\VczApi\Zoom\Helpers;

class MeetingHelper {
	/**
	 * Get the join URL for attendees.
	 *
	 * @param   array  $meeting  Raw API response array.
	 *
	 * @return string
	 */
	public static function getJoinUrl( array $meeting ): string {
		return $meeting['join_url'] ?? '';
	}

	/**
	 * Get the start URL for the host.
	 *
	 * @param   array  $meeting
	 *
	 * @return string
	 */
	public static function getStartUrl( array $meeting ): string {
		return $meeting['start_url'] ?? '';
	}

	/**
	 * Check if a meeting is recurring.
	 * Types: 3 = Recurring (no fixed time), 8 = Recurring (fixed time).
	 *
	 * @param   array  $meeting
	 *
	 * @return bool
	 */
	public static function isRecurring( array $meeting ): bool {
		$type = isset( $meeting['type'] ) ? (int) $meeting['type'] : 0;

		return in_array( $type, array( 3, 8 ), true );
	}

	/**
	 * Safe accessor for meeting password/passcode.
	 *
	 * @param   array  $meeting
	 *
	 * @return string
	 */
	public static function getPassword( array $meeting ): string {
		return $meeting['password'] ?? ( $meeting['encrypted_password'] ?? '' );
	}

	/**
	 * Get list of meetings from ListMeetings Response
	 *
	 * @param   array  $meetings
	 *
	 * @return array
	 */
	public static function getMeetingList( array $meetings ): array {
		if ( ! isset( $meetings['meetings'] ) ) {
			return array();
		}

		return $meetings['meetings'];
	}

	/**
	 * @param   array  $webinars
	 *
	 * @return array
	 */
	public static function getWebinarList( array $webinars ): array {
		if ( ! isset( $webinars['webinars'] ) ) {
			return array();
		}

		return $webinars['webinars'];
	}
}