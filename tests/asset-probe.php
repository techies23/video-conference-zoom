<?php
/**
 * Standalone probe that loads the plugin bootstrap with stubbed WordPress
 * functions and prints every asset/path constant it defines as JSON.
 *
 * Used by AssetIntegrityTest to assert that each asset constant actually
 * points at a directory that exists. Run directly for debugging:
 *
 *     php tests/asset-probe.php
 *
 * @package Video Conferencing with Zoom API
 */

$vczapi_probe_dir = dirname( __DIR__ );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', $vczapi_probe_dir . '/' );
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( $file ) {
		return 'https://example.test/wp-content/plugins/' . basename( dirname( $file ) ) . '/';
	}
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
	function plugin_dir_path( $file ) {
		return trailingslashit( dirname( $file ) );
	}
}

if ( ! function_exists( 'plugin_basename' ) ) {
	function plugin_basename( $file ) {
		return basename( dirname( $file ) ) . '/' . basename( $file );
	}
}

if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( $string ) {
		return rtrim( (string) $string, '/\\' ) . '/';
	}
}

if ( ! function_exists( 'wp_upload_dir' ) ) {
	function wp_upload_dir() {
		return [
			'basedir' => sys_get_temp_dir() . '/vczapi-probe-uploads',
		];
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action() {
		return true;
	}
}

if ( ! function_exists( 'register_activation_hook' ) ) {
	function register_activation_hook() {
		return true;
	}
}

if ( ! function_exists( 'register_deactivation_hook' ) ) {
	function register_deactivation_hook() {
		return true;
	}
}

require_once $vczapi_probe_dir . '/video-conferencing-with-zoom-api.php';

$vczapi_constants = [];

foreach ( get_defined_constants( true )['user'] as $vczapi_name => $vczapi_value ) {
	if ( ! is_string( $vczapi_value ) ) {
		continue;
	}

	// Only collect the plugin's own ZVC/VCZAPI constants.
	if ( ! str_starts_with( $vczapi_name, 'ZVC_' ) && ! str_starts_with( $vczapi_name, 'VCZAPI_' ) ) {
		continue;
	}

	$vczapi_constants[ $vczapi_name ] = $vczapi_value;
}

echo wp_json_encode_fallback( $vczapi_constants );

/**
 * Minimal JSON encoder so the probe does not depend on WordPress.
 *
 * @param mixed $data Data to encode.
 * @return string
 */
function wp_json_encode_fallback( $data ): string {
	return (string) json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
}
