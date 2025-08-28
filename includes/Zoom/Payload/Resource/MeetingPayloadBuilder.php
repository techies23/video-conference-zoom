<?php

namespace Codemanas\VczApi\Zoom\Payload\Resource;

use WP_Error;

/**
 * MeetingPayloadBuilder
 *
 * Applies meeting-specific rules that go beyond generic schema checks:
 * - settings implications (waiting_room vs join_before_host)
 * - recurrence mutual exclusivity (end_date_time XOR end_times)
 * - topic/agenda max lengths (already truncated in compat but double-safety)
 * - alternative_hosts normalization (semicolon-separated)
 * - start_time/timezone coherence hints
 */
class MeetingPayloadBuilder {

	/**
	 * @param array $schema     Operation schema (contains fields and nested schemas).
	 * @param array $normalized Payload after generic normalization (flat by field names).
	 * @param array $raw        Original raw input (post-compat, pre-normalization).
	 *
	 * @return array|WP_Error ['payload' => array, 'warnings' => array]
	 */
	public static function build( array $schema, array $normalized, array $raw ) {
		$warnings = array();

		// Enforce topic/agenda safe lengths (double safety).
		if ( isset( $normalized['topic'] ) && is_string( $normalized['topic'] ) && mb_strlen( $normalized['topic'] ) > 200 ) {
			$normalized['topic'] = mb_substr( $normalized['topic'], 0, 200 );
			$warnings[]          = 'topic truncated to 200 chars';
		}
		if ( isset( $normalized['agenda'] ) && is_string( $normalized['agenda'] ) && mb_strlen( $normalized['agenda'] ) > 2000 ) {
			$normalized['agenda'] = mb_substr( $normalized['agenda'], 0, 2000 );
			$warnings[]           = 'agenda truncated to 2000 chars';
		}

		// Settings-level rules.
		if ( isset( $normalized['settings'] ) && is_array( $normalized['settings'] ) ) {
			$settings = $normalized['settings'];

			// waiting_room true disables join_before_host.
			if ( array_key_exists( 'waiting_room', $settings ) && ! empty( $settings['waiting_room'] ) ) {
				if ( ! empty( $settings['join_before_host'] ) ) {
					$settings['join_before_host'] = false;
					$warnings[] = 'join_before_host disabled because waiting_room is enabled';
				}
			}

			// Normalize alternative_hosts to semicolon-separated string (if array slipped through).
			if ( isset( $settings['alternative_hosts'] ) && is_array( $settings['alternative_hosts'] ) ) {
				$settings['alternative_hosts'] = implode( ';', $settings['alternative_hosts'] );
			}

			// Enforce jbh_time allowed values if set.
			if ( isset( $settings['jbh_time'] ) ) {
				$allowed = array( 0, 5, 10, 15 );
				if ( ! in_array( (int) $settings['jbh_time'], $allowed, true ) ) {
					$settings['jbh_time'] = 0;
					$warnings[]           = 'jbh_time reset to 0 (allowed: 0,5,10,15)';
				}
			}

			// E2EE implications
			if ( isset( $settings['encryption_type'] ) && $settings['encryption_type'] === 'e2ee' ) {
				// Features like cloud recording are incompatible; if set, reset and warn.
				if ( isset( $settings['auto_recording'] ) && $settings['auto_recording'] === 'cloud' ) {
					$settings['auto_recording'] = 'none';
					$warnings[] = 'auto_recording set to none because encryption_type=e2ee';
				}
			}

			$normalized['settings'] = $settings;
		}

		// Recurrence rules: end_times vs end_date_time must not conflict.
		if ( isset( $normalized['recurrence'] ) && is_array( $normalized['recurrence'] ) ) {
			$r = $normalized['recurrence'];
			if ( ! empty( $r['end_times'] ) && ! empty( $r['end_date_time'] ) ) {
				// Drop end_times if both present (pick one deterministically).
				unset( $r['end_times'] );
				$warnings[] = 'recurrence.end_times ignored because end_date_time is provided';
			}
			$normalized['recurrence'] = $r;
		}

		// start_time/timezone coherence: warn if timezone missing for non-Z times.
		if ( isset( $normalized['start_time'] ) && is_string( $normalized['start_time'] ) ) {
			$hasZ = substr( $normalized['start_time'], -1 ) === 'Z';
			if ( ! $hasZ && empty( $normalized['timezone'] ) ) {
				$warnings[] = 'timezone not set; start_time interpreted by Zoom using account timezone';
			}
		}

		return array(
			'payload'  => $normalized,
			'warnings' => $warnings,
		);
	}
}