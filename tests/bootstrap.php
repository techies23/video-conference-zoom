<?php

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

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