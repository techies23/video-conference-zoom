<?php

namespace Codemanas\VczApi\Booking;

use Codemanas\VczApi\Zoom\Zoom;

class Main {
	private static $_instance = null;

	public static function get_instance() {
		return ( self::$_instance == null ) ? self::$_instance = new self() : self::$_instance;
	}

	private function __construct() {
		return;
		Frontend::get_instance();
		CPT::get_instance();
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		$vczapiZoom = new Zoom();

		//this works
		/*$response = $vczapiZoom->listMeetings([
			'user_id' => 'codemanas17@gmail.com',
			'page_size' => 10,
			'next_page_token' => 'nKEJSytJ2dBkAtssj9XH8pBVlrBFKeHSYf2'
		]);*/

		$result = $vczapiZoom->createMeeting([
//			'user_id' => 'codemanas17@gmail.com',
			'topic' => 'Digthis - JBH Test  ',
			'start_time' =>gmdate( 'Y-m-d\TH:i:s', strtotime( '2025-10-11 13:30:00' ) ),
			'settings' => [
				'join_before_host' => 'digthis'
			]
		]);
		var_dump($result); die;

	}
}