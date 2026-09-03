<?php

namespace Codemanas\VczApi\Zoom\Payload;

use Codemanas\VczApi\Zoom\Payload\Resource\MeetingPayloadBuilder;
use Codemanas\VczApi\Zoom\Payload\Resource\ReportPayloadBuilder;
use Codemanas\VczApi\Zoom\Payload\Resource\UserPayloadBuilder;
use Codemanas\VczApi\Zoom\Payload\Resource\WebinarPayloadBuilder;
use Codemanas\VczApi\Zoom\Schema\SchemaManager;
use WP_Error;

class PayloadBuilder {

	public static function build( string $operation, array $input ): WP_Error|array {
		$validated = self::validateArgs( $operation, $input );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		return self::sanitizePayload( $operation, $validated );
	}

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
		}

		return $normalized;
	}

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

	protected static function applyCompatKeyMap( array $input, array $map ): array {
		foreach ( $map as $legacy => $target ) {
			if ( array_key_exists( $legacy, $input ) ) {
				self::setByDotPath( $input, $target, $input[ $legacy ] );
				unset( $input[ $legacy ] );
			}
		}

		return $input;
	}

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

	protected static function validateTypesOnly( array $input, array $fields ): WP_Error|array {
		$out = array();

		foreach ( $fields as $name => $rules ) {
			$required = ! empty( $rules['required'] );
			$hasValue = array_key_exists( $name, $input );

			if ( $required && ! $hasValue && ! array_key_exists( 'default', $rules ) ) {
				return new WP_Error( 'vczapi_payload_required', sprintf( '%s is required', $name ), array( 'field' => $name ) );
			}

			$value = $hasValue ? $input[ $name ] : ( array_key_exists( 'default', $rules ) ? $rules['default'] : null );

			if ( ! $hasValue && $value === null ) {
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
				if ( ! empty( $rules['schema'] ) && is_array( $rules['schema'] ) ) {
					$nested = self::validateTypesOnly( $value, $rules['schema'] );
					if ( is_wp_error( $nested ) ) {
						return $nested;
					}
					$value = $nested;
				}
			}

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
						$newArr[] = $itemVal;
					}
				}
				$value = $newArr;
			}

			$out[ $name ] = $value;
		}

		return $out;
	}

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

	protected static function sanitizeString( $value ): string {
		return trim( wp_strip_all_tags( (string) $value, true ) );
	}

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