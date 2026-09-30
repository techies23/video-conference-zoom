<?php

/**
 * @since             1.0.0
 * @package           Video Conferencing with Zoom
 *
 * Plugin Name:       Video Conferencing with Zoom
 * Plugin URI:        https://wordpress.org/plugins/video-conferencing-with-zoom-api/
 * Description:       Video Conferencing with Zoom Meetings and Webinars plugin provides you with great functionality of managing Zoom meetings, Webinar scheduling options, and users directly from your WordPress dashboard.
 * Version:           4.7.0
 * Author:            Deepen Bajracharya
 * Author URI:        https://www.imdpen.com
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       video-conferencing-with-zoom-api
 * Requires PHP:      8.1
 * Domain Path:       /languages
 * Requires at least: 6.8
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve the installed @zoom/meetingsdk version.
 *
 * Falls back to the version range declared in package.json so the constant is
 * still defined when node_modules is absent, for example on a deployment that
 * ships only the built assets.
 *
 * @return string Semantic version, or an empty string when it cannot be read.
 */
function vczapi_zoom_sdk_version() {
	$package = VCZAPI_PLUGIN_DIR_PATH . 'node_modules/@zoom/meetingsdk/package.json';

	if ( is_readable( $package ) ) {
		$decoded = json_decode( file_get_contents( $package ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( ! empty( $decoded['version'] ) ) {
			return (string) $decoded['version'];
		}
	}

	if ( is_readable( VCZAPI_PLUGIN_DIR_PATH . 'package.json' ) ) {
		$decoded = json_decode( file_get_contents( VCZAPI_PLUGIN_DIR_PATH . 'package.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( ! empty( $decoded['dependencies']['@zoom/meetingsdk'] ) ) {
			return trim( (string) $decoded['dependencies']['@zoom/meetingsdk'], '^~ ' );
		}
	}

	return '';
}

defined( 'VCZAPI_PLUGIN_FILE' ) || define( 'VCZAPI_PLUGIN_FILE', __FILE__ );
defined( 'VCZAPI_PLUGIN_ABS_NAME' ) || define( 'VCZAPI_PLUGIN_ABS_NAME', plugin_basename( __FILE__ ) );
defined( 'VCZAPI_PLUGIN_SLUG' ) || define( 'VCZAPI_PLUGIN_SLUG', 'video-conferencing-zoom' );
defined( 'VCZAPI_PLUGIN_VERSION' ) || define( 'VCZAPI_PLUGIN_VERSION', '4.7.0' );
defined( 'VCZAPI_PLUGIN_DIR_URL' ) || define( 'VCZAPI_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
defined( 'VCZAPI_PLUGIN_DIR_PATH' ) || define( 'VCZAPI_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
defined( 'VCZAPI_PLUGIN_ADMIN_ASSET_URI' ) || define( 'VCZAPI_PLUGIN_ADMIN_ASSET_URI', VCZAPI_PLUGIN_DIR_URL . 'dist/admin' );
defined( 'VCZAPI_PLUGIN_PUBLIC_ASSET_URI' ) || define( 'VCZAPI_PLUGIN_PUBLIC_ASSET_URI', VCZAPI_PLUGIN_DIR_URL . 'dist/public' );
defined( 'VCZAPI_PLUGIN_VENDOR_ASSETS_URI' ) || define( 'VCZAPI_PLUGIN_VENDOR_ASSETS_URI', VCZAPI_PLUGIN_DIR_URL . 'dist/vendor' );
defined( 'VCZAPI_PLUGIN_VENDOR_ASSET_URI' ) || define( 'VCZAPI_PLUGIN_VENDOR_ASSET_URI', VCZAPI_PLUGIN_VENDOR_ASSETS_URI );
defined( 'VCZAPI_PLUGIN_IMAGES_URI' ) || define( 'VCZAPI_PLUGIN_IMAGES_URI', VCZAPI_PLUGIN_DIR_URL . 'dist/images' );
defined( 'VCZAPI_PLUGIN_SDK_URI' ) || define( 'VCZAPI_PLUGIN_SDK_URI', VCZAPI_PLUGIN_VENDOR_ASSETS_URI . '/zoom/websdk' );
defined( 'VCZAPI_PLUGIN_ADMIN_VIEWS_PATH' ) || define( 'VCZAPI_PLUGIN_ADMIN_VIEWS_PATH', VCZAPI_PLUGIN_DIR_PATH . 'includes/Admin/Views' );

/**
 * Version of the bundled @zoom/meetingsdk package.
 *
 * Read from the installed package so the constant cannot drift away from what
 * webpack actually compiled into dist/vendor/zoom/websdk/zoom-meeting.bundle.js.
 */
defined( 'VCZAPI_PLUGIN_ZOOM_SDK_VERSION' ) || define( 'VCZAPI_PLUGIN_ZOOM_SDK_VERSION', vczapi_zoom_sdk_version() );

/**
 * Legacy aliases kept for backwards compatibility with third party addons.
 *
 * The asset URLs previously pointed at an `assets/` directory that the build
 * never produced, which made every handle registered against them 404 at
 * runtime. They are now derived from the canonical constants above.
 *
 * @deprecated 4.7.0 Use the VCZAPI_PLUGIN_* constants instead.
 */
defined( 'ZVC_PLUGIN_FILE' ) || define( 'ZVC_PLUGIN_FILE', __FILE__ );
defined( 'ZVC_PLUGIN_SLUG' ) || define( 'ZVC_PLUGIN_SLUG', VCZAPI_PLUGIN_SLUG );
defined( 'ZVC_PLUGIN_VERSION' ) || define( 'ZVC_PLUGIN_VERSION', VCZAPI_PLUGIN_VERSION );
defined( 'ZVC_PLUGIN_DIR_PATH' ) || define( 'ZVC_PLUGIN_DIR_PATH', VCZAPI_PLUGIN_DIR_PATH );
defined( 'ZVC_PLUGIN_DIR_URL' ) || define( 'ZVC_PLUGIN_DIR_URL', VCZAPI_PLUGIN_DIR_URL );
defined( 'ZVC_PLUGIN_ADMIN_ASSETS_URL' ) || define( 'ZVC_PLUGIN_ADMIN_ASSETS_URL', VCZAPI_PLUGIN_ADMIN_ASSET_URI );
defined( 'ZVC_PLUGIN_PUBLIC_ASSETS_URL' ) || define( 'ZVC_PLUGIN_PUBLIC_ASSETS_URL', VCZAPI_PLUGIN_PUBLIC_ASSET_URI );
defined( 'ZVC_PLUGIN_VENDOR_ASSETS_URL' ) || define( 'ZVC_PLUGIN_VENDOR_ASSETS_URL', VCZAPI_PLUGIN_VENDOR_ASSETS_URI );
defined( 'ZVC_PLUGIN_IMAGES_PATH' ) || define( 'ZVC_PLUGIN_IMAGES_PATH', VCZAPI_PLUGIN_IMAGES_URI );
defined( 'ZVC_PLUGIN_ABS_NAME' ) || define( 'ZVC_PLUGIN_ABS_NAME', VCZAPI_PLUGIN_ABS_NAME );
defined( 'ZVC_PLUGIN_LANGUAGE_PATH' ) || define( 'ZVC_PLUGIN_LANGUAGE_PATH', trailingslashit( basename( ZVC_PLUGIN_DIR_PATH ) ) . 'languages/' );

defined( 'ZVC_PLUGIN_VIEWS_PATH' ) || define( 'ZVC_PLUGIN_VIEWS_PATH', ZVC_PLUGIN_DIR_PATH . 'includes/views' );
defined( 'ZVC_PLUGIN_INCLUDES_PATH' ) || define( 'ZVC_PLUGIN_INCLUDES_PATH', ZVC_PLUGIN_DIR_PATH . 'includes' );

$upload_dir = wp_upload_dir( null, false );
define( 'ZVC_LOG_DIR', $upload_dir['basedir'] . '/vczapi-logs/' );

// the main plugin class
require_once ZVC_PLUGIN_INCLUDES_PATH . '/Bootstrap.php';

add_action( 'plugins_loaded', 'Codemanas\VczApi\Bootstrap::instance', 99 );
register_activation_hook( __FILE__, 'Codemanas\VczApi\Bootstrap::activate' );
register_deactivation_hook( __FILE__, 'Codemanas\VczApi\Bootstrap::deactivate' );
