<?php

namespace Codemanas\VczApi\Zoom\Schema;

class User {

	/**
	 * Schema for listing users.
	 * GET /users
	 */
	public static function list(): array {
		return array(
			'operation' => SchemaManager::USER_LIST,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/users',
			'http'      => array(
				'method' => 'GET',
				'path'   => '/users',
			),
			'fields'    => array(
				'status'         => array(
					'type'     => 'string',
					'default'  => 'active',
					'enum'     => array( 'active', 'inactive', 'pending' ),
					'location' => 'query',
				),
				'page_size'      => array(
					'type'     => 'int',
					'default'  => 30,
					'min'      => 1,
					'max'      => 300,
					'location' => 'query',
				),
				'page_number'    => array(
					'type'     => 'int',
					'min'      => 1,
					'location' => 'query',
				),
				'role_id'        => array(
					'type'     => 'string',
					'location' => 'query',
				),
				'include_fields' => array(
					'type'     => 'string',
					'location' => 'query',
				),
				'next_page_token' => array(
					'type'     => 'string',
					'location' => 'query',
				),
			),
			'compat'    => array(
				'page' => 'page_number',
			),
		);
	}

	/**
	 * Schema for fetching a single user.
	 * GET /users/{user_id}
	 */
	public static function get(): array {
		return array(
			'operation' => SchemaManager::USER_GET,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/user',
			'http'      => array(
				'method'      => 'GET',
				'path'        => '/users/{user_id}',
				'path_params' => array(
					'user_id' => 'user_id',
				),
			),
			'fields'    => array(
				'user_id'        => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'path',
					'trim'     => true,
				),
				'login_type'     => array(
					'type'     => 'int',
					'location' => 'query',
				),
				'include_fields' => array(
					'type'     => 'string',
					'location' => 'query',
				),
			),
			'compat'    => array(
				'userId' => 'user_id',
				'id'     => 'user_id',
			),
		);
	}

	/**
	 * Schema for creating a user.
	 * POST /users
	 */
	public static function create(): array {
		return array(
			'operation' => SchemaManager::USER_CREATE,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/userCreate',
			'http'      => array(
				'method' => 'POST',
				'path'   => '/users',
			),
			'fields'    => array(
				'action'    => array(
					'type'     => 'string',
					'required' => true,
					'default'  => 'create',
					'enum'     => array( 'create', 'autoCreate', 'custCreate', 'ssoCreate' ),
					'location' => 'body',
				),
				'user_info' => array(
					'type'     => 'object',
					'required' => true,
					'location' => 'body',
					'schema'   => array(
						'email'        => array( 'type' => 'string', 'required' => true, 'trim' => true ),
						'type'         => array( 'type' => 'int', 'required' => true, 'default' => 1, 'enum' => array( 1, 2, 3 ) ),
						'first_name'   => array( 'type' => 'string', 'max_len' => 64, 'trim' => true ),
						'last_name'    => array( 'type' => 'string', 'max_len' => 64, 'trim' => true ),
						'display_name' => array( 'type' => 'string', 'max_len' => 128 ),
						'password'     => array( 'type' => 'string' ),
					),
				),
			),
		);
	}

	/**
	 * Schema for updating a user.
	 * PATCH /users/{user_id}
	 */
	public static function update(): array {
		return array(
			'operation' => SchemaManager::USER_UPDATE,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/userUpdate',
			'http'      => array(
				'method'      => 'PATCH',
				'path'        => '/users/{user_id}',
				'path_params' => array(
					'user_id' => 'user_id',
				),
			),
			'fields'    => array(
				'user_id'      => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'path',
					'trim'     => true,
				),
				'first_name'   => array( 'type' => 'string', 'location' => 'body', 'max_len' => 64, 'trim' => true ),
				'last_name'    => array( 'type' => 'string', 'location' => 'body', 'max_len' => 64, 'trim' => true ),
				'display_name' => array( 'type' => 'string', 'location' => 'body', 'max_len' => 128 ),
				'type'         => array( 'type' => 'int', 'location' => 'body', 'enum' => array( 1, 2, 3 ) ),
				'pmi'          => array( 'type' => 'int', 'location' => 'body' ),
				'timezone'     => array( 'type' => 'string', 'location' => 'body' ),
				'dept'         => array( 'type' => 'string', 'location' => 'body' ),
				'vanity_name'  => array( 'type' => 'string', 'location' => 'body' ),
				'host_key'     => array( 'type' => 'string', 'location' => 'body' ),
				'cms_user_id'  => array( 'type' => 'string', 'location' => 'body' ),
				'job_title'    => array( 'type' => 'string', 'location' => 'body', 'max_len' => 128 ),
				'company'      => array( 'type' => 'string', 'location' => 'body' ),
				'location'     => array( 'type' => 'string', 'location' => 'body' ),
				'phone_number' => array( 'type' => 'string', 'location' => 'body' ),
			),
			'compat'    => array(
				'userId' => 'user_id',
				'id'     => 'user_id',
			),
		);
	}

	/**
	 * Schema for deleting or disassociating a user.
	 * DELETE /users/{user_id}
	 */
	public static function delete(): array {
		return array(
			'operation' => SchemaManager::USER_DELETE,
			'docs'      => 'https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/userDelete',
			'http'      => array(
				'method'      => 'DELETE',
				'path'        => '/users/{user_id}',
				'path_params' => array(
					'user_id' => 'user_id',
				),
			),
			'fields'    => array(
				'user_id'            => array(
					'type'     => 'string',
					'required' => true,
					'location' => 'path',
					'trim'     => true,
				),
				'action'             => array(
					'type'     => 'string',
					'default'  => 'disassociate',
					'enum'     => array( 'disassociate', 'delete' ),
					'location' => 'query',
				),
				'transfer_email'     => array( 'type' => 'string', 'location' => 'query' ),
				'transfer_meeting'   => array( 'type' => 'bool', 'location' => 'query' ),
				'transfer_webinar'   => array( 'type' => 'bool', 'location' => 'query' ),
				'transfer_recording' => array( 'type' => 'bool', 'location' => 'query' ),
			),
			'compat'    => array(
				'userId' => 'user_id',
				'id'     => 'user_id',
			),
		);
	}
}