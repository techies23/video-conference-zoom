<?php

namespace Codemanas\VczApi\Admin\Controller;

use Codemanas\VczApi\Admin\Model\Zoom;
use Codemanas\VczApi\Admin\Controller\PostType\PostTypeController;

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
		Zoom::get_instance();
	}
}