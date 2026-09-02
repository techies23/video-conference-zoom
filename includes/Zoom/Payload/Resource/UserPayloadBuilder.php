<?php

namespace Codemanas\VczApi\Zoom\Payload\Resource;

use WP_Error;

/**
 * UserPayloadBuilder
 *
 * Domain-specific logic for "user.*" operations.
 * - validate(): domain validations (email format, user type checks, valid delete actions).
 * - sanitize(): last-mile shaping and safe adjustments (truncation, email normalization, warnings).
 */
class UserPayloadBuilder {

	/**
	 * Domain-specific validations for user operations.
	 *
	 * @param array $schema Operation schema.
	 * @param array $data   Data validated by PayloadBuilder types-only check.
	 * @return array|WP_Error
	 */
	public static function validate( array $schema, array $data ): WP_Error|array {
		// 1. Email format validation (when creating or updating email)
		if ( isset( $data['user_info']['email'] ) ) {
			if ( ! is_email( $data['user_info']['email'] ) ) {
				return new WP_Error(
					'vczapi_invalid_user_email',
					'The provided user email address is invalid.'
				);
			}
		}

		// 2. User type validation (1 = Basic, 2 = Licensed, 3 = On-prem)
		if ( isset( $data['user_info']['type'] ) ) {
			$allowed_types = array( 1, 2, 3 );
			if ( ! in_array( (int) $data['user_info']['type'], $allowed_types, true ) ) {
				return new WP_Error(
					'vczapi_invalid_user_type',
					'User type must be one of: 1 (Basic), 2 (Licensed), or 3 (On-prem).'
				);
			}
		}

		// 3. Delete action validation
		if ( isset( $data['action'] ) && isset( $schema['operation'] ) && strpos( $schema['operation'], 'user.delete' ) === 0 ) {
			$allowed_actions = array( 'disassociate', 'delete' );
			if ( ! in_array( $data['action'], $allowed_actions, true ) ) {
				return new WP_Error(
					'vczapi_invalid_delete_action',
					'Delete action must be either "disassociate" or "delete".'
				);
			}
		}

		return $data;
	}

	/**
	 * Sanitize user payload before sending.
	 * - Lowercase and trim user emails
	 * - Truncate string attributes to Zoom's max lengths
	 * - Emit warnings for modified inputs
	 *
	 * @param array $schema
	 * @param array $data
	 * @return array ['payload' => array, 'warnings' => string[]]
	 */
	public static function sanitize( array $schema, array $data ): array {
		$warnings = array();

		// Handle user creation nested user_info payload shaping
		if ( isset( $data['user_info'] ) && is_array( $data['user_info'] ) ) {
			$info = $data['user_info'];

			// Normalize email
			if ( isset( $info['email'] ) && is_string( $info['email'] ) ) {
				$info['email'] = strtolower( trim( $info['email'] ) );
			}

			// Name length constraints
			if ( isset( $info['first_name'] ) && mb_strlen( $info['first_name'] ) > 64 ) {
				$info['first_name'] = mb_substr( $info['first_name'], 0, 64 );
				$warnings[]         = 'first_name truncated to 64 characters';
			}

			if ( isset( $info['last_name'] ) && mb_strlen( $info['last_name'] ) > 64 ) {
				$info['last_name'] = mb_substr( $info['last_name'], 0, 64 );
				$warnings[]        = 'last_name truncated to 64 characters';
			}

			$data['user_info'] = $info;
		}

		// Direct top-level fields (e.g. PATCH /users/{userId})
		if ( isset( $data['first_name'] ) && mb_strlen( $data['first_name'] ) > 64 ) {
			$data['first_name'] = mb_substr( $data['first_name'], 0, 64 );
			$warnings[]         = 'first_name truncated to 64 characters';
		}

		if ( isset( $data['last_name'] ) && mb_strlen( $data['last_name'] ) > 64 ) {
			$data['last_name'] = mb_substr( $data['last_name'], 0, 64 );
			$warnings[]        = 'last_name truncated to 64 characters';
		}

		if ( isset( $data['job_title'] ) && mb_strlen( $data['job_title'] ) > 128 ) {
			$data['job_title'] = mb_substr( $data['job_title'], 0, 128 );
			$warnings[]        = 'job_title truncated to 128 characters';
		}

		return array(
			'payload'  => $data,
			'warnings' => $warnings,
		);
	}
}