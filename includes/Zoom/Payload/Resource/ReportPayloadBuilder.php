<?php

namespace Codemanas\VczApi\Zoom\Payload\Resource;

use WP_Error;

class ReportPayloadBuilder {

	/**
	 * Validate report payload parameters.
	 *
	 * @param array $schema
	 * @param array $data
	 * @return array|WP_Error
	 */
	public static function validate( array $schema, array $data ): WP_Error|array {
		$operation = $schema['operation'] ?? '';

		// Validate date parameters for operations requiring 'from' and 'to'
		if ( in_array( $operation, array( 'report.userMeetings', 'report.userWebinars' ), true ) ) {
			if ( ! empty( $data['from'] ) ) {
				if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $data['from'] ) ) {
					return new WP_Error(
						'vczapi_invalid_report_date',
						'The "from" parameter must be a valid date in YYYY-MM-DD format.'
					);
				}
			}

			if ( ! empty( $data['to'] ) ) {
				if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $data['to'] ) ) {
					return new WP_Error(
						'vczapi_invalid_report_date',
						'The "to" parameter must be a valid date in YYYY-MM-DD format.'
					);
				}
			}

			// Validate maximum date range window (1 month max)
			if ( ! empty( $data['from'] ) && ! empty( $data['to'] ) ) {
				$fromTS = strtotime( $data['from'] );
				$toTS   = strtotime( $data['to'] );

				if ( $fromTS && $toTS ) {
					if ( $fromTS > $toTS ) {
						return new WP_Error(
							'vczapi_invalid_date_range',
							'The "from" date cannot be after the "to" date.'
						);
					}

					// Month length in seconds (31 days max)
					if ( ( $toTS - $fromTS ) > ( 31 * 86400 ) ) {
						return new WP_Error(
							'vczapi_date_range_exceeded',
							'The date range for reports cannot exceed 1 month (31 days).'
						);
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Sanitize report payload parameters.
	 *
	 * @param array $schema
	 * @param array $data
	 * @return array ['payload' => array, 'warnings' => string[]]
	 */
	public static function sanitize( array $schema, array $data ): array {
		$warnings = array();

		// Clean path IDs
		foreach ( array( 'user_id', 'meeting_id', 'webinar_id' ) as $idField ) {
			if ( isset( $data[ $idField ] ) && is_string( $data[ $idField ] ) ) {
				$data[ $idField ] = trim( $data[ $idField ], " \t\n\r\0\x0B/" );
			}
		}

		// Clean report date queries
		if ( isset( $data['from'] ) && is_string( $data['from'] ) ) {
			$data['from'] = trim( $data['from'] );
		}

		if ( isset( $data['to'] ) && is_string( $data['to'] ) ) {
			$data['to'] = trim( $data['to'] );
		}

		// Sanitize 'type' query field for user meetings/webinars report
		if ( isset( $data['type'] ) && is_string( $data['type'] ) ) {
			$allowedTypes = array( 'past', 'pastOne' );
			if ( ! in_array( $data['type'], $allowedTypes, true ) ) {
				$data['type'] = 'past';
				$warnings[]   = 'Invalid report type provided; defaulted to "past".';
			}
		}

		return array(
			'payload'  => $data,
			'warnings' => $warnings,
		);
	}
}