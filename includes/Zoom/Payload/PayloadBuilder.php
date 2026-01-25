<?php

namespace Codemanas\VczApi\Zoom\Payload;

use Codemanas\VczApi\Zoom\Schema\SchemaManager;
use Codemanas\VczApi\Zoom\Payload\Resource\MeetingPayloadBuilder;
use WP_Error;

/**
 * PayloadBuilder
 *
 * Now exposes a clearer two-step API:
 *  - validateArgs($operation, $input): Only checks required fields and basic data types.
 *  - sanitizePayload($operation, $validated): Applies compat transforms + sanitization and partitions by location.
 *
 * build($operation, $input) remains for convenience: validate + sanitize.
 */
class PayloadBuilder {

	/**
	 * Build a payload for a given operation and raw input.
	 *
	 * @param string $operation
	 * @param array  $input
	 *
	 * @return array|WP_Error
	 */
	public static function build( $operation, array $input ) {
		$validated = self::validateArgs( $operation, $input );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		return self::sanitizePayload( $operation, $validated );
	}

	/**
	 * Validate Args
	 *
	 * Responsibilities:
	 * - Load schema
	 * - Apply compat key remapping (legacy → new keys)
	 * - Validate ONLY: required presence and basic data types (string|int|bool|array|object)
	 * - Recurse into nested schemas just for type-shape checks
	 *
	 * No enums, no min/max, no max_len here. No transforms (implode/truncate/etc).
	 *
	 * @param string $operation
	 * @param array  $input
	 * @return array|WP_Error  Normalized array or WP_Error on failure
	 */
	public static function validateArgs( $operation, array $input ) {
		$schema = SchemaManager::get( $operation );
		if ( is_wp_error( $schema ) ) {
			return $schema;
		}

		$compat = isset( $schema['compat'] ) ? (array) $schema['compat'] : array();
		$fields = isset( $schema['fields'] ) ? (array) $schema['fields'] : array();

		// 1) Compat map: legacy_key => new.key.path (no transforms)
		$working = self::applyCompatKeyMap( $input, $compat );

		// 2) Basic validation (required + type only)
		$result = self::validateTypesOnly( $working, $fields );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$normalized = $result;

		// 3) Resource-specific type validations (domain-only checks)
		$opPrefix = strpos( $operation, 'meeting.' ) === 0 ? 'meeting' : null;
		if ( $opPrefix === 'meeting' ) {
			$domainValidated = MeetingPayloadBuilder::validate( $schema, $normalized );
			if ( is_wp_error( $domainValidated ) ) {
				return $domainValidated;
			}
			$normalized = $domainValidated;
		}

		return $normalized;
	}

	/**
	 * Sanitize Payload
	 *
	 * Responsibilities:
	 * - Load schema
	 * - Apply compat transforms (implode, bool_invert, truncate)
	 * - Generic sanitization (trim/strip tags for strings)
	 * - Resource-specific sanitization (domain shaping and last-mile adjustments)
	 * - Partition by location (path/query/body)
	 * - Attach http + path_params metadata
	 *
	 * @param string $operation
	 * @param array  $validated
	 * @return array|WP_Error  ['path'=>[], 'query'=>[], 'body'=>[], 'meta'=>[]]
	 */
	public static function sanitizePayload( $operation, array $validated ) {
		$schema = SchemaManager::get( $operation );
		if ( is_wp_error( $schema ) ) {
			return $schema;
		}

		$transforms = isset( $schema['compat_transform'] ) ? (array) $schema['compat_transform'] : array();
		$fields     = isset( $schema['fields'] ) ? (array) $schema['fields'] : array();

		// 1) Apply compat transforms (implode, invert, truncate) on a copy
		$shaped = self::applyCompatTransforms( $validated, $transforms );

		// 2) Generic sanitization pass for strings and nested structures
		$shaped = self::sanitizeForSending( $shaped, $fields );

		$warnings = array();

		// 3) Resource-specific sanitize (domain shaping + non-fatal adjustments)
		if ( strpos( $operation, 'meeting.' ) === 0 ) {
			$domainSanitized = MeetingPayloadBuilder::sanitize( $schema, $shaped );
			if ( is_wp_error( $domainSanitized ) ) {
				return $domainSanitized;
			}
			$shaped   = $domainSanitized['payload'];
			$warnings = isset( $domainSanitized['warnings'] ) ? (array) $domainSanitized['warnings'] : array();
		}

		// 4) Partition by location
		$partitioned = self::partitionByLocation( $shaped, $fields );

		// 5) Meta
		$partitioned['meta'] = array(
			'warnings'    => $warnings,
			'path_params' => isset( $schema['http']['path_params'] ) ? (array) $schema['http']['path_params'] : array(),
			'http'        => isset( $schema['http'] ) ? $schema['http'] : array(),
			'operation'   => isset( $schema['operation'] ) ? $schema['operation'] : $operation,
		);

		return apply_filters( 'vczapi_payload_built', $partitioned, $operation, $schema, $validated );
	}

	/* =========================
	 * Compat helpers
	 * ========================= */

	protected static function applyCompatKeyMap( array $input, array $map ) {
		foreach ( $map as $legacy => $target ) {
			if ( array_key_exists( $legacy, $input ) ) {
				self::setByDotPath( $input, $target, $input[ $legacy ] );
				unset( $input[ $legacy ] );
			}
		}
		return $input;
	}

	protected static function applyCompatTransforms( array $input, array $transforms ) {
		foreach ( $transforms as $rule ) {
			$from = isset( $rule['from'] ) ? $rule['from'] : null;
			$to   = isset( $rule['to'] ) ? $rule['to'] : null;
			$op   = isset( $rule['op'] ) ? $rule['op'] : null;
			$args = isset( $rule['args'] ) ? (array) $rule['args'] : array();

			if ( ! $from || ! $to || ! $op ) {
				continue;
			}
			if ( ! array_key_exists( $from, $input ) ) {
				continue;
			}
			$value = $input[ $from ];

			switch ( $op ) {
				case 'implode':
					$sep   = isset( $args['separator'] ) ? (string) $args['separator'] : ';';
					$value = is_array( $value ) ? implode( $sep, $value ) : $value;
					break;
				case 'bool_invert':
					$value = ! empty( $value ) ? false : true;
					break;
				case 'truncate':
					$max   = isset( $args['max'] ) ? (int) $args['max'] : 0;
					$value = is_string( $value ) && $max > 0 ? mb_substr( $value, 0, $max ) : $value;
					break;
				default:
					break;
			}

			self::setByDotPath( $input, $to, $value );
			unset( $input[ $from ] );
		}

		return $input;
	}

	/* =========================
	 * Validation (types only)
	 * ========================= */

	/**
	 * Types-only validation (and required).
	 * Recurses into nested object/array shapes but only verifies type integrity.
	 *
	 * @param array $input
	 * @param array $fields
	 * @return array|WP_Error
	 */
	protected static function validateTypesOnly( array $input, array $fields ) {
		$out = array();

		foreach ( $fields as $name => $rules ) {
			$required = ! empty( $rules['required'] );
			$hasValue = array_key_exists( $name, $input );

			if ( $required && ! $hasValue && ! array_key_exists( 'default', $rules ) ) {
				return new WP_Error( 'vczapi_payload_required', sprintf( '%s is required', $name ), array( 'field' => $name ) );
			}

			$value = $hasValue ? $input[ $name ] : ( array_key_exists( 'default', $rules ) ? $rules['default'] : null );

			// Skip absent optional
			if ( ! $hasValue && $value === null ) {
				continue;
			}

			// Basic type check and minimal coercion
			$type = isset( $rules['type'] ) ? $rules['type'] : null;

			if ( $type === 'int' ) {
				if ( is_numeric( $value ) ) {
					$value = (int) $value;
				} else {
					return new WP_Error( 'vczapi_type_error', sprintf( '%s must be an integer', $name ) );
				}
			} elseif ( $type === 'bool' ) {
				if ( is_bool( $value ) ) {
					// ok
				} else {
					$coerced = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
					if ( $coerced === null ) {
						return new WP_Error( 'vczapi_type_error', sprintf( '%s must be a boolean', $name ) );
					}
					$value = $coerced;
				}
			} elseif ( $type === 'string' ) {
				if ( ! is_string( $value ) ) {
					$value = (string) $value;
				}
			} elseif ( $type === 'array' ) {
				if ( ! is_array( $value ) ) {
					return new WP_Error( 'vczapi_type_error', sprintf( '%s must be an array', $name ) );
				}
			} elseif ( $type === 'object' ) {
				if ( ! is_array( $value ) ) {
					return new WP_Error( 'vczapi_type_error', sprintf( '%s must be an object', $name ) );
				}
				// Recurse for nested schemas (types-only)
				if ( ! empty( $rules['schema'] ) && is_array( $rules['schema'] ) ) {
					$nested = self::validateTypesOnly( $value, $rules['schema'] );
					if ( is_wp_error( $nested ) ) {
						return $nested;
					}
					$value = $nested;
				}
				// Arrays of objects/items are handled in the parent 'array' branch via 'items' rule
			}

			// Arrays with item schema (types-only)
			if ( isset( $rules['type'] ) && $rules['type'] === 'array' && is_array( $value ) && ! empty( $rules['items'] ) ) {
				$itemSchema = $rules['items'];
				$newArr     = array();

				foreach ( $value as $idx => $itemVal ) {
					if ( isset( $itemSchema['type'] ) && $itemSchema['type'] === 'object' && ! empty( $itemSchema['schema'] ) ) {
						if ( ! is_array( $itemVal ) ) {
							return new WP_Error( 'vczapi_type_error', sprintf( '%s[%d] must be an object', $name, $idx ) );
						}
						$nested = self::validateTypesOnly( $itemVal, $itemSchema['schema'] );
						if ( is_wp_error( $nested ) ) {
							return $nested;
						}
						$newArr[] = $nested;
					} else {
						// basic cast for primitives
						$newArr[] = $itemVal;
					}
				}
				$value = $newArr;
			}

			$out[ $name ] = $value;
		}

		return $out;
	}

	/* =========================
	 * Generic sanitization
	 * ========================= */

	/**
	 * Trim/strip strings across the structure per declared fields.
	 *
	 * @param array $data
	 * @param array $fields
	 * @return array
	 */
	protected static function sanitizeForSending( array $data, array $fields ) {
		$out = array();

		foreach ( $fields as $name => $rules ) {
			if ( ! array_key_exists( $name, $data ) ) {
				continue;
			}
			$value = $data[ $name ];
			$type  = isset( $rules['type'] ) ? $rules['type'] : null;

			if ( $type === 'string' && is_string( $value ) ) {
				$out[ $name ] = self::sanitizeString( $value );
				continue;
			}

			if ( $type === 'object' && is_array( $value ) && ! empty( $rules['schema'] ) ) {
				$out[ $name ] = self::sanitizeForSending( $value, $rules['schema'] );
				continue;
			}

			if ( $type === 'array' && is_array( $value ) && ! empty( $rules['items'] ) ) {
				$itemsSchema = $rules['items'];
				$newArr      = array();
				foreach ( $value as $item ) {
					if ( isset( $itemsSchema['type'] ) && $itemsSchema['type'] === 'object' && ! empty( $itemsSchema['schema'] ) && is_array( $item ) ) {
						$newArr[] = self::sanitizeForSending( $item, $itemsSchema['schema'] );
					} elseif ( isset( $itemsSchema['type'] ) && $itemsSchema['type'] === 'string' && is_string( $item ) ) {
						$newArr[] = self::sanitizeString( $item );
					} else {
						$newArr[] = $item;
					}
				}
				$out[ $name ] = $newArr;
				continue;
			}

			$out[ $name ] = $value;
		}

		return $out;
	}

	protected static function sanitizeString( $value ) {
		$value = wp_strip_all_tags( (string) $value, true );
		$value = trim( $value );
		return $value;
	}

	/* =========================
	 * Partitioning + utilities
	 * ========================= */

	protected static function partitionByLocation( array $normalized, array $fields ) {
		$out = array( 'path' => array(), 'query' => array(), 'body' => array() );

		foreach ( $normalized as $name => $value ) {
			$location = isset( $fields[ $name ]['location'] ) ? $fields[ $name ]['location'] : 'query';
			if ( $location === 'path' ) {
				$out['path'][ $name ] = $value;
			} elseif ( $location === 'body' ) {
				$out['body'][ $name ] = $value;
			} else {
				$out['query'][ $name ] = $value;
			}
		}

		return $out;
	}

	/**
	 * Set by dot-path (e.g., settings.waiting_room).
	 */
	protected static function setByDotPath( array &$arr, $path, $value ) {
		$parts = explode( '.', $path );
		$ref   = &$arr;
		foreach ( $parts as $i => $key ) {
			if ( $i === count( $parts ) - 1 ) {
				$ref[ $key ] = $value;
			} else {
				if ( ! isset( $ref[ $key ] ) || ! is_array( $ref[ $key ] ) ) {
					$ref[ $key ] = array();
				}
				$ref = &$ref[ $key ];
			}
		}
	}
}