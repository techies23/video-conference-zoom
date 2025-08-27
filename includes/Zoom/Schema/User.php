<?php

namespace Codemanas\VczApi\Zoom\Schema;

class User {
	public const USER_CREATE = 'user.create';
	public const USER_LIST   = 'user.list';
	public const USER_GET    = 'user.get';
	public const USER_DELETE = 'user.delete';

	/**
	 * Returns the schema array for an operation.
	 * An empty array indicates unknown/unsupported operation.
	 */
	public function get(string $operation): array
	{
	switch ($operation) {
		case self::USER_CREATE:
			return $this->userCreate();
		case self::USER_LIST:
			return $this->userList();
		case self::USER_GET:
			return $this->userGet();
		case self::USER_DELETE:
			return $this->userDelete();
		default:
			return [];
	}
	}

	private function userCreate(): array
	{
		// Maps canonical inputs to the structure expected by createAUser()
		// which requires: action, and user_info{ email, type, first_name, last_name }
		return [
			'meta'   => [
				'operation' => self::USER_CREATE,
				'version'   => 1,
			],
			'fields' => [
				'action' => [
					'type'     => 'string',
					'required' => false,
					'default'  => 'create',
					'target'   => 'action',
				],
				'email' => [
					'type'     => 'string',
					'required' => true,
					'sanitize' => ['trim'],
					'target'   => 'user_info.email',
				],
				'type' => [
					'type'     => 'int',
					'required' => true,
					// Zoom user types: 1=Basic, 2=Licensed, 3=On-Prem (legacy)
					'enum'     => [1, 2, 3],
					'target'   => 'user_info.type',
				],
				'first_name' => [
					'type'     => 'string',
					'required' => false,
					'sanitize' => ['trim'],
					'target'   => 'user_info.first_name',
				],
				'last_name' => [
					'type'     => 'string',
					'required' => false,
					'sanitize' => ['trim'],
					'target'   => 'user_info.last_name',
				],
			],
		];
	}

	private function userList(): array
	{
		// listUsers expects page_size and page_number
		return [
			'meta'   => [
				'operation' => self::USER_LIST,
				'version'   => 1,
			],
			'fields' => [
				'page_size' => [
					'type'     => 'int',
					'required' => false,
					'default'  => 300,
					'min'      => 1,
					'target'   => 'page_size',
				],
				'page_number' => [
					'type'     => 'int',
					'required' => false,
					'default'  => 1,
					'min'      => 1,
					'target'   => 'page_number',
				],
			],
		];
	}

	private function userGet(): array
	{
		// getUserInfo uses user_id in URL, not body
		return [
			'meta'   => [
				'operation' => self::USER_GET,
				'version'   => 1,
			],
			'fields' => [
				'user_id' => [
					'type'     => 'string',
					'required' => true,
					'mapFrom'  => ['id'],
				],
			],
		];
	}

	private function userDelete(): array
	{
		// deleteAUser uses userid in URL, body is false
		return [
			'meta'   => [
				'operation' => self::USER_DELETE,
				'version'   => 1,
			],
			'fields' => [
				'user_id' => [
					'type'     => 'string',
					'required' => true,
					'mapFrom'  => ['id'],
				],
			],
		];
	}
}