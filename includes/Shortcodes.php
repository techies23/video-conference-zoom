<?php

namespace Codemanas\VczApi;

use Codemanas\VczApi\Shortcodes\Assets;
use Codemanas\VczApi\Shortcodes\Ajax\MeetingListAjax;
use Codemanas\VczApi\Shortcodes\Ajax\RecordingsAjax;
use Codemanas\VczApi\Shortcodes\ShortcodeRegistry;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Shortcodes Bootstrap
 *
 * Wires up the shortcode layer only. The tag map lives in
 * {@see ShortcodeRegistry}, asset registration in {@see Assets}, and the AJAX
 * endpoints in {@see MeetingListAjax} / {@see RecordingsAjax}.
 *
 * @since   3.0.0
 * @author  Deepen
 * @updated 4.7.0
 */
class Shortcodes {

	/**
	 * @var Shortcodes|null
	 */
	private static ?Shortcodes $_instance = null;

	/**
	 * @var ShortcodeRegistry|null
	 */
	private ?ShortcodeRegistry $registry = null;

	public static function get_instance(): Shortcodes {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		Assets::register();

		$this->registry = new ShortcodeRegistry();
		$this->registry->register();

		MeetingListAjax::get_instance();
		RecordingsAjax::get_instance();
	}

	/**
	 * The registered shortcode tags.
	 *
	 * @return array<string,callable>
	 */
	public function get_registry(): array {
		return $this->registry ? $this->registry->all() : [];
	}
}