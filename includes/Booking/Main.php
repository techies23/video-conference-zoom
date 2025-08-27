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

	public function init() {
		$zoomRequest = new Request();

		//public 'uuid' => string 'gY99+E7xQsiXmkz8gB7qhw==' (length=24)
		//          public 'id' => int 88497814999
		//          public 'host_id' => string 'L4cQ5GQkScK1egn91Vv8iw' (length=22)
		//          public 'topic' => string 'test meeting'
		$result      = $zoomRequest->getMeetingDetails( '88497814999'  );
		var_dump($result); die;
	}
}