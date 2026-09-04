<?php

namespace Codemanas\VczApi\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Codemanas\VczApi\Zoom\Payload\PayloadBuilder;
use Codemanas\VczApi\Zoom\Schema\SchemaManager;
use PHPUnit\Framework\TestCase;

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

class TestablePayloadBuilder extends PayloadBuilder {
	public static function publicApplyCompatKeyMap( array $input, array $map ): array {
		return self::applyCompatKeyMap( $input, $map );
	}

	public static function publicApplyCompatTransforms( array $input, array $transforms ): array {
		return self::applyCompatTransforms( $input, $transforms );
	}

	public static function publicValidateTypesOnly( array $input, array $fields ): \WP_Error|array {
		return self::validateTypesOnly( $input, $fields );
	}

	public static function publicPartitionByLocation( array $normalized, array $fields ): array {
		return self::partitionByLocation( $normalized, $fields );
	}
}

class TestableSchemaManagerForPayload extends SchemaManager {
	public static function set_map( array $map ): void {
		self::$map = $map;
	}

	public static function reset_map(): void {
		self::$map = null;
	}
}

class DummyTestableSchema {
	public static function mockSchema(): array {
		return [
			'operation' => 'test.mock',
			'http'      => [
				'method'      => 'GET',
				'path'        => '/users/{user_id}/test',
				'path_params' => [ 'user_id' => 'user_id' ],
			],
			'fields'    => [
				'user_id'   => [ 'type' => 'string', 'required' => true, 'location' => 'path' ],
				'page_size' => [ 'type' => 'int', 'default' => 30, 'location' => 'query' ],
				'topic'     => [ 'type' => 'string', 'location' => 'body' ],
			],
		];
	}
}

class PayloadBuilderTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs( [
			'is_wp_error'       => function ( $thing ) {
				return $thing instanceof \WP_Error;
			},
			'wp_strip_all_tags' => function ( $string ) {
				return strip_tags( $string );
			},
		] );
	}

	protected function tearDown(): void {
		TestableSchemaManagerForPayload::reset_map();
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test legacy key mapping (applyCompatKeyMap)
	 */
	public function test_apply_compat_key_map_maps_legacy_keys(): void {
		$input = [
			'meetingTopic' => 'My Zoom Meeting',
			'userId'       => 'user123',
		];

		$map = [
			'meetingTopic' => 'topic',
			'userId'       => 'user_id',
		];

		$result = TestablePayloadBuilder::publicApplyCompatKeyMap( $input, $map );

		$this->assertArrayHasKey( 'topic', $result );
		$this->assertArrayHasKey( 'user_id', $result );
		$this->assertArrayNotHasKey( 'meetingTopic', $result );
		$this->assertEquals( 'My Zoom Meeting', $result['topic'] );
	}

	/**
	 * Test compatibility transformations (implode, bool_invert, truncate)
	 */
	public function test_apply_compat_transforms_handles_operations(): void {
		$input = [
			'hosts_arr'   => [ 'host1@test.com', 'host2@test.com' ],
			'is_disabled' => false,
			'long_title'  => 'This title is too long for Zoom API limit',
		];

		$transforms = [
			[ 'from' => 'hosts_arr', 'to' => 'alternative_hosts', 'op' => 'implode', 'args' => [ 'separator' => ';' ] ],
			[ 'from' => 'is_disabled', 'to' => 'enabled', 'op' => 'bool_invert' ],
			[ 'from' => 'long_title', 'to' => 'short_title', 'op' => 'truncate', 'args' => [ 'max' => 10 ] ],
		];

		$result = TestablePayloadBuilder::publicApplyCompatTransforms( $input, $transforms );

		$this->assertEquals( 'host1@test.com;host2@test.com', $result['alternative_hosts'] );
		$this->assertTrue( $result['enabled'] );
		$this->assertEquals( 'This title', $result['short_title'] );
	}

	/**
	 * Test validateTypesOnly returns WP_Error when required fields are missing
	 */
	public function test_validate_types_only_returns_error_for_missing_required_field(): void {
		$input  = [];
		$fields = [
			'user_id' => [ 'type' => 'string', 'required' => true ],
		];

		$result = TestablePayloadBuilder::publicValidateTypesOnly( $input, $fields );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'vczapi_payload_required', $result->get_error_code() );
	}

	/**
	 * Test validateTypesOnly handles type coercion (string numeric -> int, string bool -> bool)
	 */
	public function test_validate_types_only_coerces_and_validates_types(): void {
		$input = [
			'page_size' => '50',
			'active'    => 'true',
		];

		$fields = [
			'page_size' => [ 'type' => 'int' ],
			'active'    => [ 'type' => 'bool' ],
		];

		$result = TestablePayloadBuilder::publicValidateTypesOnly( $input, $fields );

		$this->assertIsInt( $result['page_size'] );
		$this->assertEquals( 50, $result['page_size'] );
		$this->assertIsBool( $result['active'] );
		$this->assertTrue( $result['active'] );
	}

	/**
	 * Test partitionByLocation separates fields into path, query, and body arrays
	 */
	public function test_partition_by_location_groups_fields(): void {
		$normalized = [
			'user_id'   => 'me',
			'page_size' => 30,
			'topic'     => 'Test Topic',
		];

		$fields = [
			'user_id'   => [ 'location' => 'path' ],
			'page_size' => [ 'location' => 'query' ],
			'topic'     => [ 'location' => 'body' ],
		];

		$partitioned = TestablePayloadBuilder::publicPartitionByLocation( $normalized, $fields );

		$this->assertEquals( [ 'user_id' => 'me' ], $partitioned['path'] );
		$this->assertEquals( [ 'page_size' => 30 ], $partitioned['query'] );
		$this->assertEquals( [ 'topic' => 'Test Topic' ], $partitioned['body'] );
	}

	/**
	 * Test full build flow triggers vczapi_payload_built filter
	 */
	public function test_build_applies_payload_built_filter(): void {
		TestableSchemaManagerForPayload::set_map( [
			'test.mock' => [
				'schema' => DummyTestableSchema::class,
				'method' => 'mockSchema',
			],
		] );

		Filters\expectApplied( 'vczapi_payload_built' )
			->once();

		$input = [
			'user_id'   => 'me',
			'page_size' => 20,
			'topic'     => 'Sample Meeting',
		];

		$result = PayloadBuilder::build( 'test.mock', $input );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertEquals( 'test.mock', $result['meta']['operation'] );
	}
}