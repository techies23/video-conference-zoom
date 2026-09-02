<?php

namespace Codemanas\VczApi\Zoom\Schema;

class User {

	public static function list(): array {
		return array(
			'method'   => 'GET',
			'endpoint' => '/users',
			'query'    => array(
				'status'         => array( 'type' => 'string', 'default' => 'active' ),
				'page_size'      => array( 'type' => 'int' ),
				'page_number'    => array( 'type' => 'int' ),
				'role_id'        => array( 'type' => 'string' ),
				'include_fields' => array( 'type' => 'string' ),
			),
		);
	}

	public static function get(): array {
		return array(
			'method'   => 'GET',
			'endpoint' => '/users/{userId}',
			'path'     => array(
				'userId' => array( 'type' => 'string', 'required' => true, 'alias' => array( 'user_id', 'id' ) ),
			),
			'query'    => array(
				'login_type'     => array( 'type' => 'int' ),
				'include_fields' => array( 'type' => 'string' ),
			),
		);
	}

	public static function create(): array {
		return array(
			'method'   => 'POST',
			'endpoint' => '/users',
			'body'     => array(
				'action'    => array( 'type' => 'string', 'required' => true, 'default' => 'create' ),
				'user_info' => array(
					'type'     => 'array',
					'required' => true,
					'schema'   => array(
						'email'        => array( 'type' => 'string', 'required' => true ),
						'type'         => array( 'type' => 'int', 'required' => true, 'default' => 1 ),
						'first_name'   => array( 'type' => 'string' ),
						'last_name'    => array( 'type' => 'string' ),
						'display_name' => array( 'type' => 'string' ),
						'password'     => array( 'type' => 'string' ),
					),
				),
			),
		);
	}

	public static function update(): array {
		return array(
			'method'   => 'PATCH',
			'endpoint' => '/users/{userId}',
			'path'     => array(
				'userId' => array( 'type' => 'string', 'required' => true, 'alias' => array( 'user_id', 'id' ) ),
			),
			'body'     => array(
				'first_name'   => array( 'type' => 'string' ),
				'last_name'    => array( 'type' => 'string' ),
				'display_name' => array( 'type' => 'string' ),
				'type'         => array( 'type' => 'int' ),
				'pmi'          => array( 'type' => 'int' ),
				'timezone'     => array( 'type' => 'string' ),
				'dept'         => array( 'type' => 'string' ),
				'vanity_name'  => array( 'type' => 'string' ),
				'host_key'     => array( 'type' => 'string' ),
				'cms_user_id'  => array( 'type' => 'string' ),
				'job_title'    => array( 'type' => 'string' ),
				'company'      => array( 'type' => 'string' ),
				'location'     => array( 'type' => 'string' ),
				'phone_number' => array( 'type' => 'string' ),
			),
		);
	}

	public static function delete(): array {
		return array(
			'method'   => 'DELETE',
			'endpoint' => '/users/{userId}',
			'path'     => array(
				'userId' => array( 'type' => 'string', 'required' => true, 'alias' => array( 'user_id', 'id' ) ),
			),
			'query'    => array(
				'action'             => array( 'type' => 'string', 'default' => 'disassociate' ),
				'transfer_email'     => array( 'type' => 'string' ),
				'transfer_meeting'   => array( 'type' => 'bool' ),
				'transfer_webinar'   => array( 'type' => 'bool' ),
				'transfer_recording' => array( 'type' => 'bool' ),
			),
		);
	}
}