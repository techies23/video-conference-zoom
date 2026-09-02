<?php

namespace Codemanas\VczApi\Zoom\Service;

use Codemanas\VczApi\Zoom\Http\Client;
use Codemanas\VczApi\Zoom\Payload\PayloadBuilder;
use Codemanas\VczApi\Zoom\Schema\SchemaManager;
use WP_Error;

class Webinar extends BaseService {

	/**
	 * HTTP Client instance.
	 *
	 * @var Client
	 */
	protected Client $client;

	/**
	 * @param Client|null $client
	 */
	public function __construct( Client $client = null ) {
		$this->client = $client ?: new Client();
	}

	/**
	 * List webinars for a user/host.
	 *
	 * @param array $params
	 * @return array|WP_Error
	 */
	public function list( array $params = array() ): WP_Error|array {
		if ( empty( $params['user_id'] ) && empty( $params['userId'] ) && empty( $params['host_id'] ) && empty( $params['hostId'] ) ) {
			$params['user_id'] = 'me';
		}

		$built = PayloadBuilder::build( SchemaManager::WEBINAR_LIST, $params );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$prepared['query'] = apply_filters( 'vczapi_webinars_list_params', $prepared['query'], $params );

		$result = $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['query'] );

		if ( ! empty( $prepared['warnings'] ) ) {
			do_action( 'vczapi_payload_warnings', $prepared['warnings'], SchemaManager::WEBINAR_LIST, $params );
		}

		return $result;
	}

	/**
	 * Create a webinar for a user/host.
	 *
	 * @param array $data
	 * @return array|WP_Error
	 */
	public function create( array $data = array() ): WP_Error|array {
		if ( empty( $data['user_id'] ) && empty( $data['userId'] ) && empty( $data['host_id'] ) && empty( $data['hostId'] ) ) {
			$data['user_id'] = 'me';
		}

		$built = PayloadBuilder::build( SchemaManager::WEBINAR_CREATE, $data );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$prepared['body'] = apply_filters( 'vczapi_webinars_create_payload', $prepared['body'], $data );

		$endpoint = ! empty( $prepared['query'] )
			? add_query_arg( $prepared['query'], $prepared['endpoint'] )
			: $prepared['endpoint'];

		$result = $this->client->request( $prepared['method'], $endpoint, $prepared['body'] );

		if ( ! empty( $prepared['warnings'] ) ) {
			do_action( 'vczapi_payload_warnings', $prepared['warnings'], SchemaManager::WEBINAR_CREATE, $data );
		}

		return $result;
	}

	/**
	 * Get details of a single webinar.
	 *
	 * @param string|int|array $webinarId ID string or array with parameters.
	 * @return array|WP_Error
	 */
	public function get( $webinarId ): WP_Error|array {
		$params = is_array( $webinarId ) ? $webinarId : array( 'webinar_id' => $webinarId );

		$built = PayloadBuilder::build( SchemaManager::WEBINAR_GET, $params );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		return $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['query'] );
	}

	/**
	 * Update an existing webinar.
	 *
	 * @param string|int $webinarId Webinar ID to update.
	 * @param array      $data      Payload parameters to update.
	 * @return array|WP_Error
	 */
	public function update( $webinarId, array $data = array() ): WP_Error|array {
		$data['webinar_id'] = $webinarId;

		$built = PayloadBuilder::build( SchemaManager::WEBINAR_UPDATE, $data );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$prepared['body'] = apply_filters( 'vczapi_webinars_update_payload', $prepared['body'], $data );

		$endpoint = ! empty( $prepared['query'] )
			? add_query_arg( $prepared['query'], $prepared['endpoint'] )
			: $prepared['endpoint'];

		$result = $this->client->request( $prepared['method'], $endpoint, $prepared['body'] );

		if ( ! empty( $prepared['warnings'] ) ) {
			do_action( 'vczapi_payload_warnings', $prepared['warnings'], SchemaManager::WEBINAR_UPDATE, $data );
		}

		return $result;
	}

	/**
	 * Delete a webinar.
	 *
	 * @param string|int|array $webinarId ID string or array with parameters.
	 * @return array|WP_Error
	 */
	public function delete( $webinarId ): WP_Error|array {
		$params = is_array( $webinarId ) ? $webinarId : array( 'webinar_id' => $webinarId );

		$built = PayloadBuilder::build( SchemaManager::WEBINAR_DELETE, $params );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		return $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['query'] );
	}
}