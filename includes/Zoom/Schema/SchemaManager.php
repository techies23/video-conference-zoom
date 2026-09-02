<?php

namespace Codemanas\VczApi\Zoom\Schema;

use WP_Error;

/**
 * SchemaManager
 *
 * Registry/entry point for all Zoom API schemas.
 * - Defines operation constants to avoid magic strings.
 * - Maps operations to resource schema handlers (e.g., Meeting).
 * - Exposes get(), allOperations(), isValidOperation().
 *
 * Expected schema shape returned by handlers (array example):
 * [
 *   'operation' => SchemaManager::MEETING_LIST,
 *   'http'      => [
 *     'method'      => 'GET',
 *     'path'        => '/users/{user_id}/meetings',
 *     'path_params' => [ 'user_id' => 'user_id' ], // input->placeholder mapping
 *   ],
 *   'fields'    => [
 *     // Field rules by name:
 *     // - type: string|int|bool|array
 *     // - required: bool
 *     // - default: mixed
 *     // - enum: string[]
 *     // - min|max: number (for int), length constraints for strings if needed
 *     // - location: 'path'|'query'|'body' (where PayloadBuilder should place it)
 *     'user_id'         => [ 'type' => 'string', 'required' => true, 'location' => 'path' ],
 *     'page_size'       => [ 'type' => 'int', 'default' => 30, 'min' => 1, 'max' => 300, 'location' => 'query' ],
 *     'next_page_token' => [ 'type' => 'string', 'location' => 'query' ],
 *     'type'            => [ 'type' => 'string', 'enum' => [ 'scheduled', 'live', 'upcoming' ], 'location' => 'query' ],
 *   ],
 *   'compat'    => [
 *     // Back-compat mappings: old_field => new_field
 *     // e.g., 'meetingTopic' => 'topic'
 *   ],
 * ]
 */
class SchemaManager {
	// Meetings
	const MEETING_LIST = 'meeting.list';
	const MEETING_CREATE = 'meeting.create';
	public const MEETING_GET = 'meeting.get';
	public const MEETING_DELETE = 'meeting.delete';
	const MEETING_UPDATE = 'meeting.update';
	const MEETING_STATUS = 'meeting.status';


	// Webinars (reserved for later)
	const WEBINAR_LIST = 'webinar.list';
	const WEBINAR_CREATE = 'webinar.create';
	const WEBINAR_GET = 'webinar.get';
	const WEBINAR_DELETE = 'webinar.delete';
	const WEBINAR_UPDATE = 'webinar.update';
	const WEBINAR_STATUS = 'webinar.status';
	// Users (reserved for later)
	// const USER_GET  = 'user.get';
	// const USER_LIST = 'user.list';

	// Recordings (reserved for later)
	// const RECORDING_LIST_BY_USER    = 'recording.listByUser';
	// const RECORDING_LIST_BY_MEETING = 'recording.listByMeeting';

	/**
	 * Lazy-initialized map of operation => [ 'schema' => class, 'method' => method ].
	 *
	 * @var array|null
	 */
	protected static ?array $map = null;

	/**
	 * Build the operation map once.
	 *
	 * @return array|null
	 */
	protected static function map(): ?array {
		if ( self::$map === null ) {
			self::$map = array(
				// Meetings
				self::MEETING_LIST   => array( 'schema' => __NAMESPACE__ . '\\Meeting', 'method' => 'list' ),
				self::MEETING_CREATE => array( 'schema' => __NAMESPACE__ . '\\Meeting', 'method' => 'create' ),
				self::MEETING_GET    => array( 'schema' => __NAMESPACE__ . '\\Meeting', 'method' => 'get' ),
				self::MEETING_DELETE => array( 'schema' => __NAMESPACE__ . '\\Meeting', 'method' => 'delete' ),

				// Add more operations here as you implement schemas.
				// self::WEBINAR_LIST   => array( 'schema' => __NAMESPACE__ . '\\Webinar', 'method' => 'list' ),
				// self::USER_GET       => array( 'schema' => __NAMESPACE__ . '\\User', 'method' => 'get' ),
				// self::RECORDING_LIST_BY_MEETING => array( 'schema' => __NAMESPACE__ . '\\Recording', 'method' => 'listByMeeting' ),
			);
		}

		return self::$map;
	}

	/**
	 * @param   string  $operation
	 *
	 * @return array|WP_Error
	 */
	public static function get( string $operation ): WP_Error|array {
		if ( ! self::isValidOperation( $operation ) ) {
			return new WP_Error(
				'vczapi_invalid_operation',
				sprintf( 'Unknown schema operation: %s', $operation )
			);
		}

		$entry  = self::map()[ $operation ];
		$class  = $entry['schema'];
		$method = $entry['method'];

		if ( ! class_exists( $class ) || ! method_exists( $class, $method ) ) {
			return new WP_Error(
				'vczapi_schema_not_found',
				sprintf( 'Schema handler missing for %s (%s::%s)', $operation, $class, $method )
			);
		}

		// Expect schema's static method to return an associative array definition.
		$schema = call_user_func( array( $class, $method ) );

		// Basic validation safety net: must be an array with http + fields.
		if ( ! is_array( $schema ) || empty( $schema['http'] ) || empty( $schema['fields'] ) ) {
			return new WP_Error(
				'vczapi_schema_invalid',
				sprintf( 'Schema returned by %s is invalid or incomplete.', $operation ),
				array( 'schema' => $schema )
			);
		}

		return $schema;
	}

	/**
	 * List all supported operation strings.
	 *
	 * @return string[]
	 */
	public static function allOperations(): array {
		return array_keys( self::map() );
	}

	/**
	 * Check if an operation is supported.
	 *
	 * @param   string  $operation
	 *
	 * @return bool
	 */
	public static function isValidOperation( string $operation ): bool {
		$map = self::map();

		return isset( $map[ $operation ] );
	}
}