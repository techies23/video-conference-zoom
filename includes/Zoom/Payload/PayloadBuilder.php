<?php

namespace Codemanas\VczApi\Zoom\Payload;

use Codemanas\VczApi\Zoom\Payload\Resource\MeetingPayloadBuilder;
use Codemanas\VczApi\Zoom\Payload\Resource\RecordingPayloadBuilder;
use Codemanas\VczApi\Zoom\Payload\Resource\ReportPayloadBuilder;
use Codemanas\VczApi\Zoom\Payload\Resource\UserPayloadBuilder;
use Codemanas\VczApi\Zoom\Payload\Resource\WebinarPayloadBuilder;
use Codemanas\VczApi\Zoom\Schema\SchemaManager;
use WP_Error;

/**
 * The `PayloadBuilder` class is responsible for building, validating,
 * and sanitizing payloads for various operations based on predefined schemas.
 * It ensures that input data is validated and transformed according to specific
 * operation types and provides methods for handling compatibility and sanitization.
 */
class PayloadBuilder {

	/**
	 * Builds and validates a payload for a specified operation by validating input arguments
	 * and sanitizing the data accordingly.
	 *
	 * @param   string  $operation  The name of the operation being performed. This determines
	 *                              the validation and sanitization rules that will be applied.
	 * @param   array   $input      The input data to validate and sanitize. The structure and
	 *                              required fields depend on the specific operation.
	 *
	 * @return WP_Error|array Returns an array of validated and sanitized input values if
	 *                        processing is successful. Returns a WP_Error object if validation fails.
	 */
	public static function build( string $operation, array $input ): WP_Error|array {
		$validated = self::validateArgs( $operation, $input );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		return self::sanitizePayload( $operation, $validated );
	}

	/**
	 * Validates the provided input arguments against the schema defined for the specified operation.
	 *
	 * @param   string  $operation  The operation type being performed. This determines the schema and validation process.
	 * @param   array   $input      The input arguments to be validated.
	 *
	 * @return WP_Error|array Returns a WP_Error object if validation fails; otherwise, returns the validated and normalized input array.
	 */
	public static function validateArgs( string $operation, array $input ): WP_Error|array {
		$schema = SchemaManager::get( $operation );
		if ( is_wp_error( $schema ) ) {
			return $schema;
		}

		$compat = isset( $schema['compat'] ) ? (array) $schema['compat'] : array();
		$fields = isset( $schema['fields'] ) ? (array) $schema['fields'] : array();

		$working = self::applyCompatKeyMap( $input, $compat );

		$result = self::validateTypesOnly( $working, $fields );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$normalized = $result;

		if ( strpos( $operation, 'meeting.' ) === 0 ) {
			$domainValidated = MeetingPayloadBuilder::validate( $schema, $normalized );
			if ( is_wp_error( $domainValidated ) ) {
				return $domainValidated;
			}
			$normalized = $domainValidated;
		} elseif ( strpos( $operation, 'webinar.' ) === 0 ) {
			$domainValidated = WebinarPayloadBuilder::validate( $schema, $normalized );
			if ( is_wp_error( $domainValidated ) ) {
				return $domainValidated;
			}
			$normalized = $domainValidated;
		} elseif ( strpos( $operation, 'user.' ) === 0 ) {
			$domainValidated = UserPayloadBuilder::validate( $schema, $normalized );
			if ( is_wp_error( $domainValidated ) ) {
				return $domainValidated;
			}
			$normalized = $domainValidated;
		} elseif ( strpos( $operation, 'report.' ) === 0 ) {
			$domainValidated = ReportPayloadBuilder::validate( $schema, $normalized );
			if ( is_wp_error( $domainValidated ) ) {
				return $domainValidated;
			}
			$normalized = $domainValidated;
		} elseif ( strpos( $operation, 'recording.' ) === 0 ) {
			$domainValidated = RecordingPayloadBuilder::validate( $schema, $normalized );
			if ( is_wp_error( $domainValidated ) ) {
				return $domainValidated;
			}
			$normalized = $domainValidated;
		}

		// Filters allow pro/extensions to intercept validation for specific operations
		$filteredValidation = apply_filters( 'vczapi_payload_validate', $normalized, $operation, $schema );
		if ( is_wp_error( $filteredValidation ) ) {
			return $filteredValidation;
		}

		return $filteredValidation;	}

	/**
	 * Sanitizes the validated input payload based on the schema defined for the specified operation.
	 *
	 * @param   string  $operation  The operation type being performed. Determines the schema and sanitization process.
	 * @param   array   $validated  The input payload that has been validated prior to sanitization.
	 *
	 * @return WP_Error|array Returns a WP_Error object if sanitization fails; otherwise, returns the sanitized and partitioned payload structure with additional metadata.
	 */
	public static function sanitizePayload( $operation, array $validated ): WP_Error|array {
		$schema = SchemaManager::get( $operation );
		if ( is_wp_error( $schema ) ) {
			return $schema;
		}

		$transforms = isset( $schema['compat_transform'] ) ? (array) $schema['compat_transform'] : array();
		$fields     = isset( $schema['fields'] ) ? (array) $schema['fields'] : array();

		$shaped = self::applyCompatTransforms( $validated, $transforms );
		$shaped = self::sanitizeForSending( $shaped, $fields );

		$warnings = array();

		if ( strpos( $operation, 'meeting.' ) === 0 ) {
			$domainSanitized = MeetingPayloadBuilder::sanitize( $schema, $shaped );
			if ( is_wp_error( $domainSanitized ) ) {
				return $domainSanitized;
			}
			$shaped   = $domainSanitized['payload'];
			$warnings = isset( $domainSanitized['warnings'] ) ? (array) $domainSanitized['warnings'] : array();
		} elseif ( strpos( $operation, 'webinar.' ) === 0 ) {
			$domainSanitized = WebinarPayloadBuilder::sanitize( $schema, $shaped );
			if ( is_wp_error( $domainSanitized ) ) {
				return $domainSanitized;
			}
			$shaped   = $domainSanitized['payload'];
			$warnings = isset( $domainSanitized['warnings'] ) ? (array) $domainSanitized['warnings'] : array();
		} elseif ( strpos( $operation, 'user.' ) === 0 ) {
			$domainSanitized = UserPayloadBuilder::sanitize( $schema, $shaped );
			if ( is_wp_error( $domainSanitized ) ) {
				return $domainSanitized;
			}
			$shaped   = $domainSanitized['payload'];
			$warnings = isset( $domainSanitized['warnings'] ) ? (array) $domainSanitized['warnings'] : array();
		} elseif ( strpos( $operation, 'report.' ) === 0 ) {
			$domainSanitized = ReportPayloadBuilder::sanitize( $schema, $shaped );
			if ( is_wp_error( $domainSanitized ) ) {
				return $domainSanitized;
			}
			$shaped   = $domainSanitized['payload'];
			$warnings = isset( $domainSanitized['warnings'] ) ? (array) $domainSanitized['warnings'] : array();
		} elseif ( strpos( $operation, 'recording.' ) === 0 ) {
			$domainSanitized = RecordingPayloadBuilder::sanitize( $schema, $shaped );
			if ( is_wp_error( $domainSanitized ) ) {
				return $domainSanitized;
			}
			$shaped   = $domainSanitized['payload'];
			$warnings = isset( $domainSanitized['warnings'] ) ? (array) $domainSanitized['warnings'] : array();
		}

		$partitioned = self::partitionByLocation( $shaped, $fields );

		$partitioned['meta'] = array(
			'warnings'    => $warnings,
			'path_params' => isset( $schema['http']['path_params'] ) ? (array) $schema['http']['path_params'] : array(),
			'http'        => isset( $schema['http'] ) ? $schema['http'] : array(),
			'operation'   => isset( $schema['operation'] ) ? $schema['operation'] : $operation,
		);

		return apply_filters( 'vczapi_payload_built', $partitioned, $operation, $schema, $validated );
	}

	/**
	 * Transforms the keys of the provided array based on a mapping of legacy keys to target keys.
	 * If a legacy key exists in the input array, its value is reassigned to the target key,
	 * and the legacy key is removed from the input array.
	 *
	 * @param   array  $input  The array to process, containing the legacy keys to be mapped.
	 * @param   array  $map    An associative array mapping legacy keys to their corresponding target keys.
	 *                         The keys represent the legacy keys, and the values represent the new keys.
	 *
	 * @return array The resulting array with legacy keys replaced by their target keys.
	 */
	protected static function applyCompatKeyMap( array $input, array $map ): array {
		foreach ( $map as $legacy => $target ) {
			if ( array_key_exists( $legacy, $input ) ) {
				self::setByDotPath( $input, $target, $input[ $legacy ] );
				unset( $input[ $legacy ] );
			}
		}

		return $input;
	}

	/**
	 * Applies a set of transformation rules to the provided array. Each rule defines a source field ('from'),
	 * a target field ('to'), a transformation operation ('op'), and optional arguments ('args'). If the source
	 * field exists in the input array, the specified transformation is applied, and the result is assigned to
	 * the target field. The source field is removed from the array after processing.
	 *
	 * Supported operations:
	 * - 'implode': Joins an array into a string using a specified separator.
	 * - 'bool_invert': Inverts the boolean value of the field (empty becomes true, non-empty becomes false).
	 * - 'truncate': Truncates a string to a maximum length.
	 *
	 * @param   array  $input       The array to be transformed. It contains the initial data and the fields to be processed.
	 * @param   array  $transforms  A list of transformation rules. Each rule is an associative array with the following keys:
	 *                              - 'from' (string): The key of the source field in the input array.
	 *                              - 'to' (string): The key of the target field to assign the transformed value.
	 *                              - 'op' (string): The transformation to apply ('implode', 'bool_invert', 'truncate').
	 *                              - 'args' (array): Optional arguments specific to the transformation operation.
	 *
	 * @return array The transformed array with the specified changes applied to the data.
	 */
	protected static function applyCompatTransforms( array $input, array $transforms ): array {
		foreach ( $transforms as $rule ) {
			$from = isset( $rule['from'] ) ? $rule['from'] : null;
			$to   = isset( $rule['to'] ) ? $rule['to'] : null;
			$op   = isset( $rule['op'] ) ? $rule['op'] : null;
			$args = isset( $rule['args'] ) ? (array) $rule['args'] : array();

			if ( ! $from || ! $to || ! $op || ! array_key_exists( $from, $input ) ) {
				continue;
			}
			$value = $input[ $from ];

			switch ( $op ) {
				case 'implode':
					$sep   = isset( $args['separator'] ) ? (string) $args['separator'] : ';';
					$value = is_array( $value ) ? implode( $sep, $value ) : $value;
					break;
				case 'bool_invert':
					$value = empty( $value );
					break;
				case 'truncate':
					$max   = isset( $args['max'] ) ? (int) $args['max'] : 0;
					$value = is_string( $value ) && $max > 0 ? mb_substr( $value, 0, $max ) : $value;
					break;
			}

			self::setByDotPath( $input, $to, $value );
			unset( $input[ $from ] );
		}

		return $input;
	}

	/**
	 * Validates an input array against a set of defined rules for each field, ensuring type conformity
	 * and applying default values where necessary.
	 *
	 * @param   array  $input   The input array containing field-value pairs to validate.
	 * @param   array  $fields  An array defining validation rules for each field. Each field's rules may include:
	 *                          - 'required' (bool): Whether the field is required.
	 *                          - 'default' (mixed): The default value if the field is not present.
	 *                          - 'type' (string): Expected data type ('int', 'bool', 'string', 'array', 'object').
	 *                          - 'schema' (array): Recursive rules for validating nested arrays or objects (for 'object' type).
	 *                          - 'items' (array): Rules for array items (for 'array' type).
	 *
	 * @return WP_Error|array Returns an array of validated and formatted input values if validation succeeds.
	 *                        Returns a WP_Error object if validation fails.
	 */
	protected static function validateTypesOnly( array $input, array $fields ): WP_Error|array {
		$out = array();

		foreach ( $fields as $name => $rules ) {
			$isRequired = ! empty( $rules['required'] );
			$hasDefault  = array_key_exists( 'default', $rules );
			$hasValue    = array_key_exists( $name, $input );

			// 1. Check required fields that lack both input and a default
			if ( $isRequired && ! $hasValue && ! $hasDefault ) {
				return new WP_Error(
					'vczapi_payload_required',
					sprintf( '%s is required', $name ),
					array( 'field' => $name )
				);
			}

			// 2. Assign value from input, OR fallback ONLY if both required AND default are set
			if ( $hasValue ) {
				$value = $input[ $name ];
			} elseif ( $isRequired && $hasDefault ) {
				$value = $rules['default'];
			} else {
				// Field is omitted and not strictly forced by required + default
				continue;
			}

			$type = isset( $rules['type'] ) ? $rules['type'] : null;

			if ( $type === 'int' ) {
				if ( is_numeric( $value ) ) {
					$value = (int) $value;
				} else {
					return new WP_Error( 'vczapi_type_error', sprintf( '%s must be an integer', $name ) );
				}
			} elseif ( $type === 'bool' ) {
				if ( ! is_bool( $value ) ) {
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
				// Loop through schema recursively for nested structures like recurrence or settings
				if ( ! empty( $rules['schema'] ) && is_array( $rules['schema'] ) ) {
					$nested = self::validateTypesOnly( $value, $rules['schema'] );
					if ( is_wp_error( $nested ) ) {
						return $nested;
					}
					$value = $nested;
				}
			}

			// Handle array of objects (e.g. tracking_fields)
			if ( $type === 'array' && is_array( $value ) && ! empty( $rules['items'] ) ) {
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
						$newArr[] = $itemVal;
					}
				}
				$value = $newArr;
			}

			$out[ $name ] = $value;
		}

		return $out;
	}

	/**
	 * Sanitizes an input array for sending by applying specific rules to its fields.
	 * Each field is processed according to the provided schema, ensuring that values
	 * match their expected types and formats. Nested objects and arrays are recursively
	 * sanitized based on their respective schemas.
	 *
	 * @param   array  $data    The input array containing the data to be sanitized.
	 *                          Keys represent field names, and values are their corresponding data.
	 * @param   array  $fields  An associative array defining the schema for the input data.
	 *                          The schema specifies the expected type and rules for each field.
	 *
	 * @return array The sanitized array with only the fields and values that conform
	 *               to the specified schema.
	 */
	protected static function sanitizeForSending( array $data, array $fields ): array {
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

	/**
	 * Cleanses a string by removing all HTML and PHP tags and trimming whitespace
	 * from the beginning and end of the string.
	 *
	 * @param   mixed  $value  The input value to sanitize. It will be cast to a string
	 *                         before processing.
	 *
	 * @return string The sanitized string with tags removed and whitespace trimmed.
	 */
	protected static function sanitizeString( $value ): string {
		return trim( wp_strip_all_tags( (string) $value, true ) );
	}

	/**
	 * Partitions the normalized input array into separate arrays based on their designated location.
	 * Each input field is categorized into one of three location groups: 'path', 'query', or 'body'.
	 *
	 * @param   array  $normalized  An associative array where keys represent field names and values
	 *                              are the corresponding values to be partitioned.
	 * @param   array  $fields      An associative array specifying metadata for each field. Each key
	 *                              corresponds to a field name, and its value is an array containing
	 *                              the 'location' key, which determines where the field should reside
	 *                              ('path', 'query', or 'body').
	 *
	 * @return array An associative array with three keys: 'path', 'query', and 'body', each containing
	 *               the fields that belong to their respective location group.
	 */
	protected static function partitionByLocation( array $normalized, array $fields ): array {
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
	 * Sets a value in a multidimensional array using a dot-delimited path to specify the nested keys.
	 * Intermediate arrays are created as needed if they do not exist.
	 *
	 * @param   array   $arr    The reference to the array in which the value will be set.
	 * @param   string  $path   A dot-delimited string representing the path to the target key.
	 * @param   mixed   $value  The value to set at the specified path within the array.
	 *
	 * @return  void
	 */
	protected static function setByDotPath( array &$arr, $path, $value ): void {
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