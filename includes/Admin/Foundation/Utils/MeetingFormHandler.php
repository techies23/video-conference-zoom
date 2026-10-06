<?php

namespace Codemanas\VczApi\Admin\Foundation\Utils;

class MeetingFormHandler {

	/**
	 * Collect and sanitize POST request fields.
	 */
	public static function gatherFromPost(): array {
		$fields = [];
		$keys   = [
			'user_id',
			'agenda',
			'start_time',
			'timezone',
			'password',
			'disable_waiting_room',
			'waiting_room',
			'meeting_authentication',
			'host_video',
			'auto_recording',
			'join_before_host',
			'participant_video',
			'mute_upon_entry',
			'panelists_video',
			'practice_session',
			'hd_video',
			'allow_multiple_devices',
		];

		foreach ( $keys as $key ) {
			$fields[ $key ] = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		}

		$fields['type']              = isset( $_POST['type'] ) ? (string) $_POST['type'] : "meeting";
		$fields['jbh_time']          = isset( $_POST['jbh_time'] ) ? absint( $_POST['jbh_time'] ) : 0;
		$fields['alternative_hosts'] = isset( $_POST['alternative_hosts'] ) && is_array( $_POST['alternative_hosts'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['alternative_hosts'] ) )
			: [];

		// Handle composite hours/minutes duration
		$hours    = isset( $_POST['hour'] ) ? absint( $_POST['hour'] ) : ( isset( $_POST['option_duration_hour'] ) ? absint( $_POST['option_duration_hour'] ) : 0 );
		$minutes  = isset( $_POST['minute'] ) ? absint( $_POST['minute'] ) : ( isset( $_POST['option_duration_minutes'] ) ? absint( $_POST['option_duration_minutes'] ) : 0 );
		$duration = ( $hours * 60 ) + $minutes;

		$fields['duration'] = max( MeetingValidator::MIN_DURATION_MINUTES, $duration );

		return apply_filters( 'vczapi_post_fields', MeetingValidator::normalize( $fields ) );
	}
}