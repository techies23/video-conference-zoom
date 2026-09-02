<?php

namespace Codemanas\VczApi\Zoom\Payload\Resource;

use WP_Error;

/**
 * WebinarPayloadBuilder
 *
 * Domain-specific logic for "webinar.*" operations.
 * - validate(): domain validations (ISO8601 start_time format check).
 * - sanitize(): last-mile shaping (truncating lengths, array normalization, timezone warnings).
 */
class WebinarPayloadBuilder {

	/**
	 * Domain-specific validation before sanitization.
	 *
	 * @param array $schema Operation schema.
	 * @param array $data   Normalized input data.
	 * @return array|WP_Error
	 */
	public static function validate( array $schema, array $data ): WP_Error|array {
		// Strict start_time validation if provided
		if ( isset( $data['start_time'] ) ) {
			if ( ! is_string( $data['start_time'] ) || ! self::isZuluIso8601( $data['start_time'] ) ) {
				return new WP_Error(
					'vczapi_invalid_start_time_format',
					'start_time must use UTC format: yyyy-MM-ddTHH:mm:ssZ (example: 2026-04-28T13:00:00Z).'
				);
			}
		}

		// Password length validation
		if ( isset( $data['password'] ) && is_string( $data['password'] ) && strlen( $data['password'] ) > 10 ) {
			return new WP_Error(
				'vczapi_invalid_password_length',
				'Webinar password cannot exceed 10 characters.'
			);
		}

		return $data;
	}

	/**
	 * Sanitize the webinar payload before sending.
	 *
	 * @param array $schema
	 * @param array $data
	 * @return array ['payload' => array, 'warnings' => string[]]
	 */
	public static function sanitize( array $schema, array $data ): array {
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

			// Normalize alternative_hosts array to semicolon-delimited string
			if ( isset( $settings['alternative_hosts'] ) && is_array( $settings['alternative_hosts'] ) ) {
				$settings['alternative_hosts'] = implode( ';', $settings['alternative_hosts'] );
			}

			$data['settings'] = $settings;
		}

		// Redundant timezone notice (if Z-format used)
		if ( isset( $data['start_time'] ) && is_string( $data['start_time'] ) ) {
			if ( str_ends_with( $data['start_time'], 'Z' ) && ! empty( $data['timezone'] ) ) {
				$warnings[] = 'timezone is ignored when start_time is in UTC (Zulu) format';
			}
		}

		return array(
			'payload'  => $data,
			'warnings' => $warnings,
		);
	}

	/**
	 * Validate UTC ISO8601 with seconds: yyyy-MM-ddTHH:mm:ssZ
	 *
	 * @param string $value
	 * @return bool
	 */
	protected static function isZuluIso8601( string $value ): bool {
		return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $value );
	}
}