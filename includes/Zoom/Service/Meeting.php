<?php

namespace Codemanas\VczApi\Zoom\Service;

use Codemanas\VczApi\Zoom\Http\Client;
use Codemanas\VczApi\Zoom\Payload\PayloadBuilder;
use Codemanas\VczApi\Zoom\Schema\SchemaManager;
use WP_Error;

class Meeting extends BaseService {
	/** @var Client */
	protected $client;

	/**
	 * Optionally inject a client for testing or customization.
	 *
	 * @param Client|null $client
	 */
	public function __construct( Client $client = null ) {
		$this->client = $client ?: new Client();
	}

	/**
	 * List meetings for a user/host.
	 *
	 * @param array $params
	 * @return array|WP_Error
	 */
	public function list( array $params = array() ) {
		$built = PayloadBuilder::build( SchemaManager::MEETING_LIST, $params );
 		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		// Allow final tweaks to query params before request.
		$prepared['query'] = apply_filters( 'vczapi_meetings_list_params', $prepared['query'], $params );

		$result = $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['query'] );

		// Surface payload warnings for observability (non-blocking).
		if ( ! empty( $prepared['warnings'] ) ) {
			do_action( 'vczapi_payload_warnings', $prepared['warnings'], SchemaManager::MEETING_LIST, $params );
		}

		return $result;
	}

	/**
	 * Create a meeting for a user/host.
	 *
	 * @param array $data
	 * @return array|WP_Error
	 */
	public function create( array $data = array() ) {
		$built = PayloadBuilder::build( SchemaManager::MEETING_CREATE, $data );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		// Allow final tweaks to body before request.
		$prepared['body'] = apply_filters( 'vczapi_meetings_create_payload', $prepared['body'], $data );

		$result = $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['body'] );

		// Surface payload warnings for observability (non-blocking).
		if ( ! empty( $prepared['warnings'] ) ) {
			do_action( 'vczapi_payload_warnings', $prepared['warnings'], SchemaManager::MEETING_CREATE, $data );
		}

		return $result;
	}
}