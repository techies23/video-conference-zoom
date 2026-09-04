<?php

namespace Codemanas\VczApi\Zoom\Payload\Resource;

use WP_Error;

/**
 * RecordingPayloadBuilder
 *
 * Domain-specific logic for "recording.*" operations.
 * - validate(): domain-level parameter rules and strict format validation.
 * - sanitize(): path formatting (UUID encoding) and payload adjustments.
 */
class RecordingPayloadBuilder {

	/**
	 * Validate recording-specific parameters.
	 *
	 * @param array $schema
	 * @param array $data
	 * @return array|WP_Error
	 */
	public static function validate( array $schema, array $data ): WP_Error|array {
		// Validate YYYY-MM-DD date format for listing recordings
		foreach ( array( 'from', 'to' ) as $dateField ) {
			if ( ! empty( $data[ $dateField ] ) && ! self::isValidDate( $data[ $dateField ] ) ) {
				return new WP_Error(
					'vczapi_invalid_date_format',
					sprintf( '%s must be a valid date in YYYY-MM-DD format.', $dateField )
				);
			}
		}

		// Ensure 'to' is not earlier than 'from' if both are provided
		if ( ! empty( $data['from'] ) && ! empty( $data['to'] ) ) {
			if ( strtotime( $data['from'] ) > strtotime( $data['to'] ) ) {
				return new WP_Error(
					'vczapi_invalid_date_range',
					'The "from" date cannot be later than the "to" date.'
				);
			}
		}

		// Validate TTL range for download token requests (if set)
		if ( isset( $data['ttl'] ) && ( $data['ttl'] < 60 || $data['ttl'] > 86400 ) ) {
			return new WP_Error(
				'vczapi_invalid_ttl',
				'ttl (time to live) must be between 60 and 86400 seconds.'
			);
		}

		return $data;
	}

	/**
	 * Sanitize recording payload before sending.
	 * - Double URL-encodes meeting_id if it's a UUID (contains '/' or starts with '/').
	 *
	 * @param array $schema
	 * @param array $data
	 * @return array ['payload' => array, 'warnings' => string[]]
	 */
	public static function sanitize( array $schema, array $data ): array {
		$warnings = array();

		// Zoom requires double URL-encoding for Meeting UUIDs containing slashes
		if ( isset( $data['meeting_id'] ) && is_string( $data['meeting_id'] ) ) {
			if ( str_contains( $data['meeting_id'], '/' ) || str_contains( $data['meeting_id'], '=' ) ) {
				$data['meeting_id'] = urlencode( urlencode( $data['meeting_id'] ) );
				$warnings[]         = 'meeting_id double URL encoded per Zoom API requirement for UUIDs';
			}
		}

		return array(
			'payload'  => $data,
			'warnings' => $warnings,
		);
	}

	/**
	 * Check if a date string is in YYYY-MM-DD format.
	 *
	 * @param string $date
	 * @return bool
	 */
	protected static function isValidDate( string $date ): bool {
		$d = \DateTime::createFromFormat( 'Y-m-d', $date );
		return $d && $d->format( 'Y-m-d' ) === $date;
	}
}