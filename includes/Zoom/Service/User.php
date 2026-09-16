<?php

namespace Codemanas\VczApi\Zoom\Service;

use Codemanas\VczApi\Zoom\Http\Client;
use Codemanas\VczApi\Zoom\Payload\PayloadBuilder;
use Codemanas\VczApi\Zoom\Schema\SchemaManager;
use WP_Error;

class User extends BaseService {

	/** @var Client */
	protected Client $client;

	public function __construct( ?Client $client = null ) {
		$this->client = $client ?: new Client();
	}

	public function list( array $params = array() ): WP_Error|array {
		$built = PayloadBuilder::build( SchemaManager::USER_LIST, $params );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$prepared['query'] = apply_filters( 'vczapi_users_list_params', $prepared['query'], $params );

		$result = $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['query'] );

		if ( ! empty( $prepared['warnings'] ) ) {
			do_action( 'vczapi_payload_warnings', $prepared['warnings'], SchemaManager::USER_LIST, $params );
		}

		return $result;
	}

	public function create( array $data = array() ): WP_Error|array {
		$built = PayloadBuilder::build( SchemaManager::USER_CREATE, $data );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$prepared['body'] = apply_filters( 'vczapi_users_create_payload', $prepared['body'], $data );

		$result = $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['body'] );

		if ( ! empty( $prepared['warnings'] ) ) {
			do_action( 'vczapi_payload_warnings', $prepared['warnings'], SchemaManager::USER_CREATE, $data );
		}

		return $result;
	}

	public function get( $userId ): WP_Error|array {
		$params = is_array( $userId ) ? $userId : array( 'userId' => $userId );

		$built = PayloadBuilder::build( SchemaManager::USER_GET, $params );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		return $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['query'] );
	}

	public function update( string $userId, array $data = array() ): WP_Error|array {
		$data['userId'] = $userId;

		$built = PayloadBuilder::build( SchemaManager::USER_UPDATE, $data );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$prepared['body'] = apply_filters( 'vczapi_users_update_payload', $prepared['body'], $data );

		$result = $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['body'] );

		if ( ! empty( $prepared['warnings'] ) ) {
			do_action( 'vczapi_payload_warnings', $prepared['warnings'], SchemaManager::USER_UPDATE, $data );
		}

		return $result;
	}

	public function delete( $userId, string $action = 'disassociate' ): WP_Error|array {
		$params = is_array( $userId ) ? $userId : array( 'userId' => $userId );
		if ( ! isset( $params['action'] ) ) {
			$params['action'] = $action;
		}

		$built = PayloadBuilder::build( SchemaManager::USER_DELETE, $params );
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