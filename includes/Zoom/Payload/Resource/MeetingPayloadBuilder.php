<?php

namespace Codemanas\VczApi\Zoom\Payload\Resource;

use WP_Error;

/**
 * MeetingPayloadBuilder
 *
 * Domain-specific logic for "meeting.*" operations.
 * - validate(): domain validations (human-readable errors), no sanitization.
 * - sanitize(): last-mile shaping and safe adjustments (may emit warnings).
 */
class MeetingPayloadBuilder {

	/**
	 * @param array $schema     Operation schema (contains fields and nested schemas).
	 * @param array $normalized Payload after generic normalization (flat by field names).
	 * @param array $raw        Original raw input (post-compat, pre-normalization).
	 *
	 * @return array|WP_Error
	 */
	public static function validate( array $schema, array $data ) {
		// start_time strict validation (create operation)
		if ( isset( $data['start_time'] ) ) {
			if ( ! is_string( $data['start_time'] ) || ! self::isZuluIso8601( $data['start_time'] ) ) {
				return new WP_Error(
					'vczapi_invalid_start_time_format',
					'start_time must use UTC format: yyyy-MM-ddTHH:mm:ssZ (example: 2025-04-28T13:00:00Z).'
				);
			}
		}

		// Recurrence mutual exclusivity
		if ( isset( $data['recurrence'] ) && is_array( $data['recurrence'] ) ) {
			$hasEndDate  = ! empty( $data['recurrence']['end_date_time'] );
			$hasEndTimes = isset( $data['recurrence']['end_times'] ) && $data['recurrence']['end_times'] !== null;

			if ( $hasEndDate && $hasEndTimes ) {
				return new WP_Error(
					'vczapi_recurrence_conflict',
					'Provide either recurrence.end_date_time or recurrence.end_times, not both.'
				);
			}
		}

		// settings.jbh_time allowed values if provided
		if ( isset( $data['settings'] ) && is_array( $data['settings'] ) && array_key_exists( 'jbh_time', $data['settings'] ) ) {
			$allowed = array( 0, 5, 10, 15 );
			$val     = $data['settings']['jbh_time'];
			if ( $val !== null ) {
				$val = is_numeric( $val ) ? (int) $val : $val;
				if ( ! in_array( $val, $allowed, true ) ) {
					return new WP_Error(
						'vczapi_invalid_jbh_time',
						'settings.jbh_time must be one of: 0, 5, 10, 15.'
					);
				}
			}
		}

		return $data;
	}

	/**
	 * Sanitize meeting payload before sending.
	 * - Truncate topic/agenda to limits
	 * - Normalize alternative_hosts to semicolon-delimited
	 * - Enforce waiting_room disables join_before_host
	 * - If encryption_type=e2ee and auto_recording=cloud, set to none (warning)
	 * - Warn if timezone is redundant with Zulu time
	 *
	 * @param array $schema
	 * @param array $data
	 * @return array ['payload'=>array, 'warnings'=>string[]]
	 */
	public static function sanitize( array $schema, array $data ) {
		$warnings = array();

		// Topic/agenda length constraints
		if ( isset( $data['topic'] ) && is_string( $data['topic'] ) && mb_strlen( $data['topic'] ) > 200 ) {
			$data['topic'] = mb_substr( $data['topic'], 0, 200 );
			$warnings[]    = 'topic truncated to 200 characters';
		}
		if ( isset( $data['agenda'] ) && is_string( $data['agenda'] ) && mb_strlen( $data['agenda'] ) > 2000 ) {
			$data['agenda'] = mb_substr( $data['agenda'], 0, 2000 );
			$warnings[]     = 'agenda truncated to 2000 characters';
		}

		// Settings shaping
		if ( isset( $data['settings'] ) && is_array( $data['settings'] ) ) {
			$settings = $data['settings'];

			// waiting_room disables join_before_host
			if ( array_key_exists( 'waiting_room', $settings ) && ! empty( $settings['waiting_room'] ) ) {
				if ( ! empty( $settings['join_before_host'] ) ) {
					$settings['join_before_host'] = false;
					$warnings[] = 'join_before_host disabled because waiting_room is enabled';
				}
			}

			// Normalize alternative_hosts
			if ( isset( $settings['alternative_hosts'] ) && is_array( $settings['alternative_hosts'] ) ) {
				$settings['alternative_hosts'] = implode( ';', $settings['alternative_hosts'] );
			}

			// Clamp jbh_time to supported values if still off
			if ( isset( $settings['jbh_time'] ) ) {
				$allowed = array( 0, 5, 10, 15 );
				$val     = is_numeric( $settings['jbh_time'] ) ? (int) $settings['jbh_time'] : $settings['jbh_time'];
				if ( ! in_array( $val, $allowed, true ) ) {
					$settings['jbh_time'] = 0;
					$warnings[]            = 'jbh_time reset to 0 (allowed: 0, 5, 10, 15)';
				}
			}

			// E2EE implications
			if ( isset( $settings['encryption_type'] ) && $settings['encryption_type'] === 'e2ee' ) {
				if ( isset( $settings['auto_recording'] ) && $settings['auto_recording'] === 'cloud' ) {
					$settings['auto_recording'] = 'none';
					$warnings[]                  = 'auto_recording set to none because encryption_type is e2ee';
				}
			}

			$data['settings'] = $settings;
		}

		// Redundant timezone notice (if Z-format used)
		if ( isset( $data['start_time'] ) && is_string( $data['start_time'] ) ) {
			if ( substr( $data['start_time'], -1 ) === 'Z' && ! empty( $data['timezone'] ) ) {
				$warnings[] = 'timezone is ignored when start_time is in UTC (Zulu) format';
			}
		}

		return array(
			'payload'  => $data,
			'warnings' => $warnings,
		);
	}

	/**
	 * Validate UTC/Zulu ISO8601 with seconds: yyyy-MM-ddTHH:mm:ssZ
	 *
	 * @param string $value
	 * @return bool
	 */
	protected static function isZuluIso8601( $value ) {
		return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $value );
	}
}