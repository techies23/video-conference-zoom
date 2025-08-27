<?php

namespace Codemanas\VczApi\Zoom\Request;

use Codemanas\VczApi\Zoom\PayloadBuilder;
use Codemanas\VczApi\Zoom\Schema\User as UserSchema;
use Codemanas\VczApi\Zoom\SchemaRegistry;
use WP_Error;

class User {
	private bool $useLegacyApi;

	/** @var \Zoom_Video_Conferencing_Api|null */
	private $legacyApi;

	/** @var object|null Future modern client (to be implemented) */
	private $modernClient;

	private SchemaRegistry $schemas;
	private PayloadBuilder $builder;

	public function __construct(
		bool $useLegacyApi,
		$legacyApi,
		$modernClient,
		SchemaRegistry $schemas,
		PayloadBuilder $builder
	) {
		$this->useLegacyApi = $useLegacyApi;
		$this->legacyApi    = $legacyApi;
		$this->modernClient = $modernClient;
		$this->schemas      = $schemas;
		$this->builder      = $builder;
	}

	public function create( array $input ) {
		$payload = $this->builder->build( 'user', UserSchema::USER_CREATE, $input );
		if ( $payload instanceof WP_Error ) {
			return $payload;
		}

		if ( $this->useLegacyApi ) {
			if ( ! $this->legacyApi || ! method_exists( $this->legacyApi, 'createAUser' ) ) {
				return new WP_Error( 'legacy_api_unavailable', 'Legacy API client is not available.' );
			}
			// Flatten user_info.* fields for legacy createAUser signature
			$data = [
				'action'     => $payload['action'] ?? 'create',
				'email'      => $payload['user_info']['email'] ?? '',
				'type'       => $payload['user_info']['type'] ?? null,
				'first_name' => $payload['user_info']['first_name'] ?? '',
				'last_name'  => $payload['user_info']['last_name'] ?? '',
			];

			return $this->legacyApi->createAUser( $data );
		}

		return new WP_Error( 'not_implemented', 'Modern client for create user is not implemented yet.' );
	}

	public function list( array $input = [] ) {
		$payload = $this->builder->build( 'user', UserSchema::USER_LIST, $input );
		if ( $payload instanceof WP_Error ) {
			return $payload;
		}

		if ( $this->useLegacyApi ) {
			if ( ! $this->legacyApi || ! method_exists( $this->legacyApi, 'listUsers' ) ) {
				return new WP_Error( 'legacy_api_unavailable', 'Legacy API client is not available.' );
			}
			// Legacy listUsers($page, $args)
			$page = isset( $payload['page_number'] ) ? (int) $payload['page_number'] : 1;

			return $this->legacyApi->listUsers( $page, $payload );
		}

		return new WP_Error( 'not_implemented', 'Modern client for list users is not implemented yet.' );
	}

	public function get( $userId ) {
		$payload = $this->builder->build( 'user', UserSchema::USER_GET, [ 'user_id' => (string) $userId ] );
		if ( $payload instanceof WP_Error ) {
			return $payload;
		}

		if ( $this->useLegacyApi ) {
			if ( ! $this->legacyApi || ! method_exists( $this->legacyApi, 'getUserInfo' ) ) {
				return new WP_Error( 'legacy_api_unavailable', 'Legacy API client is not available.' );
			}

			return $this->legacyApi->getUserInfo( $payload['user_id'] );
		}

		return new WP_Error( 'not_implemented', 'Modern client for get user is not implemented yet.' );
	}

	public function delete( $userId ) {
		$payload = $this->builder->build( 'user', UserSchema::USER_DELETE, [ 'user_id' => (string) $userId ] );
		if ( $payload instanceof WP_Error ) {
			return $payload;
		}

		if ( $this->useLegacyApi ) {
			if ( ! $this->legacyApi || ! method_exists( $this->legacyApi, 'deleteAUser' ) ) {
				return new WP_Error( 'legacy_api_unavailable', 'Legacy API client is not available.' );
			}

			return $this->legacyApi->deleteAUser( $payload['user_id'] );
		}

		return new WP_Error( 'not_implemented', 'Modern client for delete user is not implemented yet.' );
	}
}