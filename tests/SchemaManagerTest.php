<?php

namespace Codemanas\VczApi\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Codemanas\VczApi\Zoom\Schema\SchemaManager;

// Lightweight stub for WP_Error in isolated unit test environment
if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		protected $code;
		protected $message;
		protected $data;

		public function __construct( $code = '', $message = '', $data = '' ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}

		public function get_error_code() {
			return $this->code;
		}

		public function get_error_message() {
			return $this->message;
		}

		public function get_error_data() {
			return $this->data;
		}
	}
}

/**
 * Subclass to inject fake mappings cleanly for isolated testing.
 */
class TestableSchemaManager extends SchemaManager {
	public static function set_test_map( array $map ): void {
		self::$map = $map;
	}

	public static function reset_test_map(): void {
		self::$map = null;
	}
}

/**
 * Dummy handler class with invalid schema return for testing.
 */
class DummySchemaHandler {
	public static function invalidSchema(): array {
		return [
			'http' => [ 'method' => 'GET' ],
			// Missing required 'fields' key
		];
	}
}

class SchemaManagerTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs( [
			'is_wp_error' => function ( $thing ) {
				return $thing instanceof \WP_Error;
			},
		] );
	}

	protected function tearDown(): void {
		TestableSchemaManager::reset_test_map();
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test isValidOperation returns true for registered operations and false for unknown ones.
	 */
	public function test_is_valid_operation(): void {
		$this->assertTrue( SchemaManager::isValidOperation( SchemaManager::MEETING_LIST ) );
		$this->assertTrue( SchemaManager::isValidOperation( SchemaManager::USER_CREATE ) );
		$this->assertFalse( SchemaManager::isValidOperation( 'invalid.operation' ) );
	}

	/**
	 * Test allOperations returns array containing registered constants.
	 */
	public function test_all_operations_returns_keys(): void {
		$operations = SchemaManager::allOperations();

		$this->assertIsArray( $operations );
		$this->assertContains( SchemaManager::MEETING_LIST, $operations );
		$this->assertContains( SchemaManager::REPORT_DAILY, $operations );
	}

	/**
	 * Test get() returns WP_Error when operation is not registered in the map.
	 */
	public function test_get_returns_wp_error_for_invalid_operation(): void {
		$result = SchemaManager::get( 'non.existent.operation' );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'vczapi_invalid_operation', $result->get_error_code() );
	}

	/**
	 * Test get() returns WP_Error when the target class handler does not exist.
	 */
	public function test_get_returns_wp_error_when_handler_class_does_not_exist(): void {
		TestableSchemaManager::set_test_map( [
			'fake.missing_class' => [
				'schema' => 'Codemanas\VczApi\Zoom\Schema\NonExistentClass',
				'method' => 'someMethod',
			],
		] );

		$result = TestableSchemaManager::get( 'fake.missing_class' );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'vczapi_schema_not_found', $result->get_error_code() );
	}

	/**
	 * Test get() returns WP_Error if handler returns malformed schema (missing http or fields).
	 */
	public function test_get_returns_wp_error_for_invalid_schema_structure(): void {
		TestableSchemaManager::set_test_map( [
			'fake.invalid_schema' => [
				'schema' => DummySchemaHandler::class,
				'method' => 'invalidSchema',
			],
		] );

		$result = TestableSchemaManager::get( 'fake.invalid_schema' );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'vczapi_schema_invalid', $result->get_error_code() );
	}

	/**
	 * Test get() returns actual schema array when production handler class exists and returns valid structure.
	 */
	public function test_get_returns_schema_array_on_success(): void {
		$result = SchemaManager::get( SchemaManager::MEETING_LIST );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'http', $result );
		$this->assertArrayHasKey( 'fields', $result );
	}
}