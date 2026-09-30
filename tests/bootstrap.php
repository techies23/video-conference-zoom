<?php

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Plugin files guard against direct access with `if ( ! defined( 'ABSPATH' ) ) exit;`.
// Without ABSPATH defined, requiring one of them kills the whole PHPUnit process
// mid-run with a bare exit and no summary, so declare it up front.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

// Initialize Brain Monkey
Brain\Monkey\setUp();

// Minimal WP_Error stub for isolated unit testing
if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		public string|int $code;
		public string $message;
		public mixed $data;

		public function __construct( string|int $code = '', string $message = '', mixed $data = '' ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}

		public function get_error_code(): string|int {
			return $this->code;
		}

		public function get_error_message(): string {
			return $this->message;
		}

		public function get_error_data(): mixed {
			return $this->data;
		}
	}
}