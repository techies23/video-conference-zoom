<?php

namespace Codemanas\VczApi\Zoom\Service;

use WP_Error;

abstract class BaseService {
	/**
	 * Prepare method, endpoint, query, body and warnings from a built payload.
	 *
	 * @param array $built The result from PayloadBuilder::build()
	 * @return array|WP_Error ['method','endpoint','query','body','warnings']
	 */
	protected function prepareFromBuilt( array $built ) {
		$http       = isset( $built['meta']['http'] ) ? (array) $built['meta']['http'] : array();
		$pathParams = isset( $built['meta']['path_params'] ) ? (array) $built['meta']['path_params'] : array();

		$endpoint = $this->resolveEndpoint(
			$http,
			isset( $built['path'] ) ? (array) $built['path'] : array(),
			$pathParams,
			isset( $built['query'] ) ? (array) $built['query'] : array()
		);

		if ( is_wp_error( $endpoint ) ) {
			return $endpoint;
		}

		return array(
			'method'   => isset( $http['method'] ) ? $http['method'] : 'GET',
			'endpoint' => $endpoint,
			'query'    => isset( $built['query'] ) ? (array) $built['query'] : array(),
			'body'     => isset( $built['body'] ) ? (array) $built['body'] : array(),
			'warnings' => isset( $built['meta']['warnings'] ) ? (array) $built['meta']['warnings'] : array(),
		);
	}

	/**
	 * Resolve an endpoint path by replacing placeholders with path values.
	 *
	 * @param array $httpSchema  ['path' => string, ...]
	 * @param array $pathValues  normalized path fields from PayloadBuilder
	 * @param array $pathParams  mapping: input field => placeholder name
	 * @param array $queryValues normalized query fields (fallback for path if needed)
	 * @return string|WP_Error
	 */
	protected function resolveEndpoint( array $httpSchema, array $pathValues, array $pathParams, array $queryValues = array() ) {
		if ( empty( $httpSchema['path'] ) ) {
			return new WP_Error( 'vczapi_missing_path', 'HTTP path is not defined in schema.' );
		}

		$path = $httpSchema['path'];
		// Replace placeholders using provided path params mapping.
		foreach ( $pathParams as $inputField => $placeholder ) {
			// Fallback if value wasn't partitioned into path (defensive).
			if ( ! array_key_exists( $inputField, $pathValues ) && array_key_exists( $inputField, $queryValues ) ) {
				$pathValues[ $inputField ] = $queryValues[ $inputField ];
				unset( $queryValues[ $inputField ] );
			}

			if ( ! array_key_exists( $inputField, $pathValues ) ) {
				return new WP_Error( 'vczapi_missing_path_value', sprintf( 'Missing required path value: %s', $inputField ) );
			}

			$path = str_replace( '{' . $placeholder . '}', rawurlencode( (string) $pathValues[ $inputField ] ), $path );
		}

		return $path;
	}
}