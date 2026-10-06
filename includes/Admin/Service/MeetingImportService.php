<?php

namespace Codemanas\VczApi\Admin\Service;

use Codemanas\VczApi\Data\Metastore;
use WP_Error;

/**
 * Meeting import service.
 *
 * Orchestrates pulling live Zoom meetings from the API and creating them as
 * "zoom-meetings" posts within WordPress. Used by the Import admin screen.
 *
 * @package Codemanas\VczApi\Admin\Service
 * @since   4.8.0
 */
class MeetingImportService {

	const CACHE_OPTION = '_vczapi_sync_meetings';

	const SCHEDULED_MEETING_TYPE = 2;

	/**
	 * Cache key used to remember the previously fetched, unsynced meetings.
	 */
	const MEETING_POST_TYPE = 'zoom-meetings';

	/**
	 * List scheduled meetings for a user that have not been imported yet.
	 *
	 * Results are cached in the sync option so the "sync" step can validate
	 * the selected meeting without re-fetching the full list.
	 *
	 * @param string $user_id Zoom host/user id.
	 *
	 * @return WP_Error|array {
	 * @type array $meetings List of meetings.
	 * }
	 */
	public function listAvailable( string $user_id ): WP_Error|array {
		$response = zoom_conference_v2()->meetings()->list( array(
			'user_id'   => $user_id,
			'type'      => 'scheduled',
			'page_size' => 300,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( empty( $response['meetings'] ) || ! is_array( $response['meetings'] ) ) {
			return new WP_Error( 'vczapi_sync_no_meetings', __( 'No Meetings Found !', 'video-conferencing-with-zoom-api' ) );
		}

		$existing = $this->getExistingMeetingIds();

		$meetings = array();
		foreach ( $response['meetings'] as $meeting ) {
			if ( (int) ( $meeting['type'] ?? 0 ) !== self::SCHEDULED_MEETING_TYPE ) {
				continue;
			}

			//Only keep meetings which are not currently synced.
			if ( ! empty( $existing ) && in_array( (string) $meeting['id'], $existing, true ) ) {
				continue;
			}

			$meetings[] = $meeting;
		}

		$result = array( 'meetings' => array_values( $meetings ) );

		update_option( self::CACHE_OPTION, wp_json_encode( $result ) );

		return $result;
	}

	/**
	 * Fetch full details for a single meeting by id.
	 *
	 * @param string $meeting_id
	 *
	 * @return WP_Error|array
	 */
	public function getMeeting( string $meeting_id ): WP_Error|array {
		$response = zoom_conference_v2()->meetings()->get( $meeting_id );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! empty( $response['code'] ) ) {
			return new WP_Error( 'vczapi_sync_meeting_error', $response['message'] ?? __( 'Unable to fetch meeting details.', 'video-conferencing-with-zoom-api' ) );
		}

		return $response;
	}

	/**
	 * Create a "zoom-meetings" post from a Zoom meeting API response.
	 *
	 * @param array $meeting Meeting details returned by the Zoom API.
	 *
	 * @return int|WP_Error Post ID on success, error on failure.
	 */
	public function createMeeting( array $meeting ): int|WP_Error {
		$post_id = wp_insert_post( array(
			'post_title'   => ! empty( $meeting['topic'] ) ? $meeting['topic'] : '',
			'post_content' => ! empty( $meeting['agenda'] ) ? $meeting['agenda'] : '',
			'post_status'  => 'publish',
			'post_type'    => self::MEETING_POST_TYPE,
		) );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		if ( empty( $post_id ) ) {
			return new WP_Error( 'vczapi_sync_insert_failed', __( 'Failed to import meeting.', 'video-conferencing-with-zoom-api' ) );
		}

		$this->persistMeeting( $post_id, $meeting );

		return $post_id;
	}

	/**
	 * Persist meeting meta for an imported meeting using the current meta scheme.
	 *
	 * @param int   $post_id
	 * @param array $meeting
	 */
	private function persistMeeting( int $post_id, array $meeting ): void {
		$settings = $meeting['settings'] ?? array();

		$fields = array(
			'topic'                  => $meeting['topic'] ?? '',
			'agenda'                 => $meeting['agenda'] ?? '',
			'user_id'                => (string) ( $meeting['host_id'] ?? '' ),
			'type'                   => 1,
			'start_time'             => $this->formatStartTime( $meeting ),
			'timezone'               => (string) ( $meeting['timezone'] ?? '' ),
			'duration'               => (int) ( $meeting['duration'] ?? 40 ),
			'password'               => (string) ( $meeting['password'] ?? '' ),
			'meeting_authentication' => $this->asDropdownValue( $settings['meeting_authentication'] ?? null ),
			'join_before_host'       => $this->asDropdownValue( $settings['join_before_host'] ?? null ),
			'host_video'             => $this->asDropdownValue( $settings['host_video'] ?? null ),
			'participant_video'      => $this->asDropdownValue( $settings['participant_video'] ?? null ),
			'mute_upon_entry'        => $this->asDropdownValue( $settings['mute_upon_entry'] ?? null ),
			'auto_recording'         => (string) ( $settings['auto_recording'] ?? 'none' ),
			'alternative_hosts'      => ! empty( $settings['alternative_hosts'] ) ? $this->normalizeAlternativeHosts( $settings['alternative_hosts'] ) : array(),
		);

		Metastore::setPostMeta( $post_id, 'meeting_fields', $fields );
		Metastore::setPostMeta( $post_id, 'meeting_type', 'meeting' );
		Metastore::setPostMeta( $post_id, 'meeting_start_date_utc', $this->toStartDateUtc( $fields ) );
		Metastore::setPostMeta( $post_id, 'meeting_zoom_details', $meeting );
		Metastore::setPostMeta( $post_id, 'meeting_join_url', (string) ( $meeting['join_url'] ?? '' ) );
		Metastore::setPostMeta( $post_id, 'meeting_start_url', (string) ( $meeting['start_url'] ?? '' ) );
		Metastore::setPostMeta( $post_id, 'meeting_id', (string) ( $meeting['id'] ?? '' ) );
	}

	/**
	 * Normalize the Zoom start time to the ISO 8601 format used by the schema.
	 */
	private function formatStartTime( array $meeting ): string {
		if ( empty( $meeting['start_time'] ) ) {
			return '';
		}

		$timestamp = strtotime( $meeting['start_time'] );

		return $timestamp ? date( 'Y-m-d\TH:i:s', $timestamp ) : (string) $meeting['start_time'];
	}

	/**
	 * Convert the saved start time into UTC for better querying.
	 */
	private function toStartDateUtc( array $fields ): string {
		if ( empty( $fields['start_time'] ) || empty( $fields['timezone'] ) ) {
			return '';
		}

		try {
			$dt       = new \DateTimeImmutable( $fields['start_time'], new \DateTimeZone( $fields['timezone'] ) );
			$utc      = $dt->setTimezone( new \DateTimeZone( 'UTC' ) );

			return $utc->format( 'Y-m-d H:i:s' );
		} catch ( \Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * Normalize boolean-ish API settings to the dropdown value used by the metabox.
	 */
	private function asDropdownValue( mixed $value ): ?string {
		if ( empty( $value ) ) {
			return null;
		}

		return in_array( $value, array( 'yes', 'on', '1', 1, true, 'true' ), true ) ? '1' : null;
	}

	/**
	 * Normalize alternative hosts to a list of host ids.
	 */
	private function normalizeAlternativeHosts( mixed $hosts ): array {
		if ( is_string( $hosts ) && ! empty( trim( $hosts ) ) ) {
			return array_values( array_filter( array_map( 'trim', explode( ',', str_replace( ';', ',', $hosts ) ) ) ) );
		}

		if ( is_array( $hosts ) ) {
			return array_values( array_filter( $hosts ) );
		}

		return array();
	}

	/**
	 * Get the Zoom meeting ids that have already been imported.
	 *
	 * Detects both the legacy `_meeting_zoom_meeting_id` meta and the current
	 * `vczapi_meeting_id` meta so previously imported meetings are not duplicated.
	 *
	 * @return array
	 */
	public function getExistingMeetingIds(): array {
		$meetings = get_posts( array(
			'post_type'      => self::MEETING_POST_TYPE,
			'posts_per_page' => - 1,
			'post_status'    => array( 'pending', 'draft', 'future', 'publish' ),
			'fields'         => 'ids',
		) );

		$existing = array();
		foreach ( $meetings as $post_id ) {
			$meeting_id = get_post_meta( $post_id, '_meeting_zoom_meeting_id', true );
			if ( empty( $meeting_id ) ) {
				$meeting_id = get_post_meta( $post_id, 'vczapi_meeting_id', true );
			}

			if ( ! empty( $meeting_id ) ) {
				$existing[] = (string) $meeting_id;
			}
		}

		return $existing;
	}

	/**
	 * Get the meetings cached during the last "check" request.
	 *
	 * @return array
	 */
	public function getCachedMeetings(): array {
		$cached = json_decode( (string) get_option( self::CACHE_OPTION, '' ), true );

		return is_array( $cached ) && ! empty( $cached['meetings'] ) ? $cached['meetings'] : array();
	}

	/**
	 * Check whether a Zoom meeting id has already been imported.
	 *
	 * @param string $meeting_id
	 *
	 * @return bool
	 */
	public function isMeetingImported( string $meeting_id ): bool {
		return in_array( $meeting_id, $this->getExistingMeetingIds(), true );
	}
}