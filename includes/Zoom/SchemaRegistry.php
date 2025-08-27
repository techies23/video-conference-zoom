<?php

namespace Codemanas\VczApi\Zoom;

use Codemanas\VczApi\Zoom\Schema\Meeting;
use Codemanas\VczApi\Zoom\Schema\User;
use Codemanas\VczApi\Zoom\Schema\Webinar;

class SchemaRegistry {
	/**
	 * @var array<string, class-string|object>
	 */
	private array $schemas = [
		'meeting' => Meeting::class,
		'webinar' => Webinar::class,
		'user'    => User::class,
	];

	/**
	 * @var array<string, object>
	 */
	private array $instances = [];

	/**
	 * Register or override a schema provider.
	 *
	 * @param  string  $key
	 * @param  class-string|object  $provider  Class name or an instance with a public get(string $operation): array method.
	 */
	public function register( string $key, $provider ): void {
		$this->schemas[ $key ] = $provider;
		unset( $this->instances[ $key ] );
	}

	/**
	 * Retrieve a schema for a resource + operation.
	 * Returns an empty array if schema or operation is unknown.
	 *
	 * @param  string  $schemaKey  e.g., 'meeting', 'webinar', 'user'
	 * @param  string  $operation  e.g., Meeting::MEETING_CREATE
	 */
	public function get( string $schemaKey, string $operation ): array {
		$provider = $this->resolveProvider( $schemaKey );
		if ( ! $provider || ! method_exists( $provider, 'get' ) ) {
			return [];
		}

		$result = $provider->get( $operation );

		return is_array( $result ) ? $result : [];
	}

	/**
	 * Resolve provider instance for a given key, lazily instantiating if needed.
	 *
	 * @return object|null
	 */
	private function resolveProvider( string $schemaKey ) {
		if ( isset( $this->instances[ $schemaKey ] ) ) {
			return $this->instances[ $schemaKey ];
		}

		if ( ! isset( $this->schemas[ $schemaKey ] ) ) {
			return null;
		}

		$provider = $this->schemas[ $schemaKey ];

		// Instantiate if it's a class-string.
		if ( is_string( $provider ) ) {
			if ( ! class_exists( $provider ) ) {
				return null;
			}
			$instance = new $provider();
		} else {
			$instance = $provider;
		}

		// Soft contract: must expose get().
		if ( ! method_exists( $instance, 'get' ) ) {
			return null;
		}

		$this->instances[ $schemaKey ] = $instance;

		return $this->instances[ $schemaKey ];
	}
}