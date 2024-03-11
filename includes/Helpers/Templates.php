<?php

namespace Codemanas\VczApi\Helpers;

/**
 * Generate Template URLs and related data
 *
 * @since 4.2.2
 * @author Deepen Bajracharya
 */
class Templates {

	/**
	 * Fetch Template
	 *
	 * @param $template_name
	 * @param  bool  $load
	 * @param  bool  $require_once
	 * @param  array  $args
	 *
	 * @return false|mixed|null
	 */
	public static function getTemplate( $template_name, bool $load = false, bool $require_once = true, array $args = [] ) {
		if ( empty( $template_name ) ) {
			return false;
		}

		$located = false;
		if ( file_exists( get_stylesheet_directory() . '/' . ZVC_PLUGIN_SLUG . '/' . $template_name ) ) {
			$located = get_stylesheet_directory() . '/' . ZVC_PLUGIN_SLUG . '/' . $template_name;
		} elseif ( file_exists( get_template_directory() . '/' . ZVC_PLUGIN_SLUG . '/' . $template_name ) ) {
			$located = get_template_directory() . '/' . ZVC_PLUGIN_SLUG . '/' . $template_name;
		} elseif ( file_exists( ZVC_PLUGIN_DIR_PATH . 'templates/' . $template_name ) ) {
			$located = ZVC_PLUGIN_DIR_PATH . 'templates/' . $template_name;
		}

		// Allow 3rd party plugin filter template file from their plugin.
		$located = apply_filters( 'vczapi_get_template', $located, $template_name );
		if ( $load && ! empty( $located ) && file_exists( $located ) ) {
			load_template( $located, $require_once, $args );
		}

		return $located;
	}

	/**
	 * Get certain part of the template
	 *
	 * @param $slug
	 * @param  string  $name
	 *
	 * @return void
	 */
	public static function getTemplatePart( $slug, string $name = '' ) {
		$template = false;
		if ( $name ) {
			$template = locate_template( array(
				"{$slug}-{$name}.php",
				ZVC_PLUGIN_SLUG . '/' . "{$slug}-{$name}.php",
			) );

			if ( ! $template ) {
				$fallback = ZVC_PLUGIN_DIR_PATH . "templates/{$slug}-{$name}.php";
				$template = file_exists( $fallback ) ? $fallback : '';
			}
		}

		if ( ! $template ) {
			$template = locate_template( array(
				"{$slug}-{$name}.php",
				ZVC_PLUGIN_SLUG . '/' . "{$slug}-{$name}.php",
			) );
		}

		// Allow 3rd party plugins to filter template file from their plugin.
		$template = apply_filters( 'vcz_get_template_part', $template, $slug, $name );

		if ( $template ) {
			load_template( $template, false );
		}
	}
}