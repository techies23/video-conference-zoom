<?php

namespace Codemanas\VczApi\Admin\Controller;

/**
 * Admin Controller Class
 *
 * @added 4.7.0
 */
class AdminController {

	private static ?AdminController $instance = null;

	public static function get_instance(): ?AdminController {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->init();
	}

	public function init(): void {
		PostTypeController::get_instance();
		SettingController::get_instance();
		UserController::get_instance();
	}
}