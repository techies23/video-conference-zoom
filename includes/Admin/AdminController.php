<?php

namespace Codemanas\VczApi\Admin;

use Codemanas\VczApi\Admin\Models\Zoom;
use Codemanas\VczApi\Admin\PostType\PostTypeController;

/**
 * Class Admin
 * @package Codemanas\Webex\Core\Admin
 *
 * @since 1.0.0
 * @author Codemanas (Deepen)
 */
class AdminController {

	public static string $postType = 'zoom-meetings';

	public function __construct() {
		$this->init();
	}

	public function init(): void {
		PostTypeController::get_instance();
		AjaxController::get_instance();
		Zoom::get_instance();
	}

	private static ?AdminController $instance = null;

	public static function get_instance(): ?AdminController {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}