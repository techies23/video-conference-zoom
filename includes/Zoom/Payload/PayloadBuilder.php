<?php

namespace Codemanas\VczApi\Zoom\Payload;

use Codemanas\VczApi\Zoom\Schema\SchemaManager;
use Codemanas\VczApi\Zoom\Payload\Resource\MeetingPayloadBuilder;
use WP_Error;

/**
 * PayloadBuilder
 *
 * Generic builder that:
 * - Retrieves a schema by operation
 * - Applies compat field mappings and transforms
 * - Performs generic sanitization and validation
 * - Partitions fields by location (path/query/body)
 * - Delegates resource-specific logic to resource builders (e.g., MeetingPayloadBuilder)
 */
class PayloadBuilder {

	/**
	 * Build a payload for a given operation and raw input.
	 *
	 * @param string $operation
	 * @param array  $input
	 *
	 * @return array|WP_Error [
	 *   'path'  => array,
	 *   'query' => array,
	 *   'body'  => array,
	 *   'meta'  => array (optional: warnings, notes),
	 * ]
	 */
	public static function build( $operation, array $input ) {
		$schema = SchemaManager::get( $operation );
		if ( is_wp_error( $schema ) ) {
			return $schema;
		}

		$compat      = isset( $schema['compat'] ) ? (array) $schema['compat'] : array();
		$transforms  = isset( $schema['compat_transform'] ) ? (array) $schema['compat_transform'] : array();
		$fields      = isset( $schema['fields'] ) ? (array) $schema['fields'] : array();

		// Step 1: Apply compat key remapping (legacy_key => new_key)
		$working = self::applyCompatKeyMap( $input, $compat );

		// Step 2: Apply declared compat transforms (e.g., implode array, invert boolean, truncate)
		$working = self::applyCompatTransforms( $working, $transforms );

		// Step 3: Generic sanitize + validate against $fields
		$validation = self::validateAndNormalize( $working, $fields );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}
		$normalized = $validation['normalized'];
		$warnings   = $validation['warnings'];

		// Step 4: Delegate to resource-specific builder if one is available
		$normalized = self::delegateResourceSpecific( $operation, $schema, $normalized, $working, $warnings );
		if ( is_wp_error( $normalized ) ) {
			return $normalized;
		}
		$warnings = $normalized['warnings'];
		unset( $normalized['warnings'] );

		// Step 5: Partition by location for HTTP client usage
		$partitioned = self::partitionByLocation( $normalized, $fields );

		// Include path param substitution metadata for the client/router
		$partitioned['meta'] = array(
			'warnings'    => $warnings,
			'path_params' => isset( $schema['http']['path_params'] ) ? (array) $schema['http']['path_params'] : array(),
			'http'        => isset( $schema['http'] ) ? $schema['http'] : array(),
			'operation'   => isset( $schema['operation'] ) ? $schema['operation'] : $operation,
		);

		/**
		 * Allow mutation of the final payload.
		 *
		 * @param array  $partitioned
		 * @param string $operation
		 * @param array  $schema
		 * @param array  $input
		 */
		return apply_filters( 'vczapi_payload_built', $partitioned, $operation, $schema, $input );
	}

	/* =========================
	 * Compat helpers
	 * ========================= */

	protected static function applyCompatKeyMap( array $input, array $map ) {
		// Map simple legacy keys to new keys (supports dot-path for target)
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
					// No-op; unknown transform.
					break;
			}

			self::setByDotPath( $input, $to, $value );
			unset( $input[ $from ] );
		}

		return $input;
	}

	/* =========================
	 * Generic validation
	 * ========================= */

	protected static function validateAndNormalize( array $input, array $fields ) {
		$normalized = array();
		$warnings   = array();

		foreach ( $fields as $name => $rules ) {
			$location   = isset( $rules['location'] ) ? $rules['location'] : 'query';
			$required   = ! empty( $rules['required'] );
			$hasValue   = self::hasValue( $input, $name );
			$defaultSet = array_key_exists( 'default', $rules );

			// Required
			if ( $required && ! $hasValue && ! $defaultSet ) {
				return new WP_Error( 'vczapi_payload_required', sprintf( '%s is required', $name ), array( 'field' => $name ) );
			}

			$value = $hasValue ? self::getValue( $input, $name ) : ( $defaultSet ? $rules['default'] : null );

			// Skip absent optional fields
			if ( $value === null ) {
				continue;
			}

			// Sanitize basic strings
			if ( isset( $rules['type'] ) && $rules['type'] === 'string' && is_string( $value ) ) {
				$value = self::sanitizeString( $value );
			}

			// Coerce type
			$coerced = self::coerceType( $value, $rules );
			if ( $coerced['warning'] ) {
				$warnings[] = $coerced['warning'];
			}
			$value = $coerced['value'];

			// Constrain: enum, min, max, max_len, etc.
			$viol = self::checkConstraints( $name, $value, $rules );
			if ( is_wp_error( $viol ) ) {
				return $viol;
			}
			if ( $viol ) {
				$warnings[] = $viol;
			}

			// Nested object schema
			if ( isset( $rules['type'] ) && $rules['type'] === 'object' && is_array( $value ) && ! empty( $rules['schema'] ) ) {
				$nested = self::validateAndNormalize( $value, $rules['schema'] );
				if ( is_wp_error( $nested ) ) {
					return $nested;
				}
				$value     = $nested['normalized'];
				$warnings  = array_merge( $warnings, $nested['warnings'] );
			}

			// Arrays with item schema
			if ( isset( $rules['type'] ) && $rules['type'] === 'array' && is_array( $value ) && ! empty( $rules['items'] ) ) {
				$itemsSchema = $rules['items'];
				$maxItems    = isset( $rules['max_items'] ) ? (int) $rules['max_items'] : 0;
				if ( $maxItems > 0 && count( $value ) > $maxItems ) {
					$value      = array_slice( $value, 0, $maxItems );
					$warnings[] = sprintf( '%s truncated to %d items', $name, $maxItems );
				}

				$newArr = array();
				foreach ( $value as $idx => $item ) {
					if ( isset( $itemsSchema['type'] ) && $itemsSchema['type'] === 'object' && ! empty( $itemsSchema['schema'] ) && is_array( $item ) ) {
						$nested = self::validateAndNormalize( $item, $itemsSchema['schema'] );
						if ( is_wp_error( $nested ) ) {
							return $nested;
						}
						$newArr[]  = $nested['normalized'];
						$warnings  = array_merge( $warnings, $nested['warnings'] );
					} else {
						$newArr[] = $item;
					}
				}
				$value = $newArr;
			}

			// Set
			$normalized[ $name ] = $value;
		}

		return array(
			'normalized' => $normalized,
			'warnings'   => $warnings,
		);
	}

	protected static function hasValue( array $arr, $key ) {
		return array_key_exists( $key, $arr ) && $arr[ $key ] !== null;
	}

	protected static function getValue( array $arr, $key ) {
		return array_key_exists( $key, $arr ) ? $arr[ $key ] : null;
	}

	protected static function sanitizeString( $value ) {
		// Remove tags, trim whitespace, preserve ASCII + UTF-8
		$value = wp_strip_all_tags( $value, true );
		$value = trim( $value );
		return $value;
	}

	protected static function coerceType( $value, array $rules ) {
		$warning = null;
		$type    = isset( $rules['type'] ) ? $rules['type'] : null;

		if ( $type === 'int' ) {
			if ( ! is_int( $value ) ) {
				if ( is_numeric( $value ) ) {
					$value = (int) $value;
				} else {
					$warning = 'Coercion failed: expected int';
				}
			}
		} elseif ( $type === 'bool' ) {
			if ( ! is_bool( $value ) ) {
				$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
				if ( $value === null ) {
					$warning = 'Coercion failed: expected bool';
					$value   = false;
				}
			}
		} elseif ( $type === 'array' ) {
			if ( ! is_array( $value ) ) {
				$value   = (array) $value;
				$warning = 'Coercion: cast to array';
			}
		} elseif ( $type === 'object' ) {
			if ( ! is_array( $value ) ) {
				$value   = (array) $value;
				$warning = 'Coercion: cast to object (array)';
			}
		} elseif ( $type === 'string' ) {
			if ( ! is_string( $value ) ) {
				$value   = (string) $value;
				$warning = 'Coercion: cast to string';
			}
		}

		return array( 'value' => $value, 'warning' => $warning );
	}

	protected static function checkConstraints( $name, $value, array $rules ) {
		// enum
		if ( isset( $rules['enum'] ) && is_array( $rules['enum'] ) ) {
			if ( ! in_array( $value, $rules['enum'], true ) ) {
				return new WP_Error( 'vczapi_payload_enum', sprintf( '%s must be one of: %s', $name, implode( ', ', $rules['enum'] ) ) );
			}
		}
		// min/max for ints
		if ( isset( $rules['type'] ) && $rules['type'] === 'int' ) {
			if ( isset( $rules['min'] ) && is_int( $value ) && $value < $rules['min'] ) {
				return sprintf( '%s clamped to min %d', $name, $rules['min'] );
			}
			if ( isset( $rules['max'] ) && is_int( $value ) && $value > $rules['max'] ) {
				return sprintf( '%s clamped to max %d', $name, $rules['max'] );
			}
		}
		// max_len for strings
		if ( isset( $rules['type'] ) && $rules['type'] === 'string' && isset( $rules['max_len'] ) && is_string( $value ) ) {
			if ( mb_strlen( $value ) > (int) $rules['max_len'] ) {
				return sprintf( '%s truncated to %d chars', $name, (int) $rules['max_len'] );
			}
		}
		return null;
	}

	/* =========================
	 * Resource delegation
	 * ========================= */

	protected static function delegateResourceSpecific( $operation, array $schema, array $normalized, array $raw, array $warnings ) {
		$resourceWarnings = array();

		// Route by operation prefix (meeting.*, user.*, webinar.*, etc.)
		if ( strpos( $operation, 'meeting.' ) === 0 ) {
			$result = MeetingPayloadBuilder::build( $schema, $normalized, $raw );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
			$normalized        = $result['payload'];
			$resourceWarnings  = isset( $result['warnings'] ) ? (array) $result['warnings'] : array();
		}

		return array(
			'warnings' => array_merge( $warnings, $resourceWarnings ),
			// Return merged normalized payload fields (flat).
			'payload'  => $normalized,
		);
	}

	/* =========================
	 * Partitioning + utilities
	 * ========================= */

	protected static function partitionByLocation( array $normalized, array $fields ) {
		$out = array(
			'path'  => array(),
			'query' => array(),
			'body'  => array(),
		);

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
	 * Set value into $arr by dot-path (e.g., settings.waiting_room).
	 *
	 * @param array  $arr
	 * @param string $path
	 * @param mixed  $value
	 *
	 * @return void
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