<?php

namespace Codemanas\VczApi\Backend;

class Templates {

	private static string $_template_folder;

	private static string $_template_dir;

	public function __construct() {
		self::$_template_folder = VCZAPI_TEMPLATE_SLUG;
		self::$_template_dir    = VCZAPI_DIR_PATH . '/templates/';
	}

	public static function get_template( $file = '', array $args = [], bool $require_once = false ) {
		$locate_template    = locate_template( self::$_template_folder . '/' . $file );
		$template_file_name = apply_filters( 'vczapi_get_template', $locate_template, $file, $args );
		if ( $template_file_name ) {
			load_template( $template_file_name, $require_once, $args );
		} else {
			$file_path = self::$_template_dir . $file;
			if ( file_exists( $file_path ) ) {
				load_template( $file_path, $require_once, $args );
			}
		}
	}

	/**
	 * Include or require file
	 *
	 * Calling this method does not pass down the variables down to the document
	 *
	 * @param $_template_file_path
	 * @param bool $require_once
	 */
	public static function include_file( $_template_file_path, $require_once = false ) {
		if ( $require_once ) {
			require_once $_template_file_path;
		} else {
			require $_template_file_path;
		}
	}

}