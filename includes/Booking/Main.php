<?php

namespace Codemanas\VczApi\Booking;

use Codemanas\VczApi\Zoom\Request;

class Main {
	private static $_instance = null;

	public static function get_instance() {
		return ( self::$_instance == null ) ? self::$_instance = new self() : self::$_instance;
	}

	private function __construct() {
		Frontend::get_instance();
		CPT::get_instance();
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {}
}