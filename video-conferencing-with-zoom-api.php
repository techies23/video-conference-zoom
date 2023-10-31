<?php
/**
 * @since             1.0.0
 * @package           Video Conferencing with Zoom
 *
 * Plugin Name:       Video Conferencing with Zoom
 * Plugin URI:        https://wordpress.org/plugins/video-conferencing-with-zoom-api/
 * Description:       Video Conferencing with Zoom Meetings and Webinars plugin provides you with great functionality of managing Zoom meetings, Webinar scheduling options, and users directly from your WordPress dashboard.
 * Version:           4.3.3
 * Author:            Deepen Bajracharya
 * Author URI:        https://www.imdpen.com
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       video-conferencing-with-zoom-api
 * Requires PHP:      7.4
 * Domain Path:       /languages
 * Requires at least: 5.5.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

defined( 'ZVC_PLUGIN_FILE' ) || define( 'ZVC_PLUGIN_FILE', __FILE__ );
defined( 'ZVC_PLUGIN_SLUG' ) || define( 'ZVC_PLUGIN_SLUG', 'video-conferencing-zoom' );
defined( 'ZVC_PLUGIN_VERSION' ) || define( 'ZVC_PLUGIN_VERSION', '4.3.3' );
defined( 'ZVC_ZOOM_WEBSDK_VERSION' ) || define( 'ZVC_ZOOM_WEBSDK_VERSION', '2.16.0' );
defined( 'ZVC_PLUGIN_DIR_PATH' ) || define( 'ZVC_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
defined( 'ZVC_PLUGIN_DIR_URL' ) || define( 'ZVC_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
defined( 'ZVC_PLUGIN_ADMIN_ASSETS_URL' ) || define( 'ZVC_PLUGIN_ADMIN_ASSETS_URL', ZVC_PLUGIN_DIR_URL . 'assets/admin' );
defined( 'ZVC_PLUGIN_PUBLIC_ASSETS_URL' ) || define( 'ZVC_PLUGIN_PUBLIC_ASSETS_URL', ZVC_PLUGIN_DIR_URL . 'assets/public' );
defined( 'ZVC_PLUGIN_VENDOR_ASSETS_URL' ) || define( 'ZVC_PLUGIN_VENDOR_ASSETS_URL', ZVC_PLUGIN_DIR_URL . 'assets/vendor' );
defined( 'ZVC_PLUGIN_VIEWS_PATH' ) || define( 'ZVC_PLUGIN_VIEWS_PATH', ZVC_PLUGIN_DIR_PATH . 'includes/views' );
defined( 'ZVC_PLUGIN_INCLUDES_PATH' ) || define( 'ZVC_PLUGIN_INCLUDES_PATH', ZVC_PLUGIN_DIR_PATH . 'includes' );
defined( 'ZVC_PLUGIN_IMAGES_PATH' ) || define( 'ZVC_PLUGIN_IMAGES_PATH', ZVC_PLUGIN_DIR_URL . 'assets/images' );
defined( 'ZVC_PLUGIN_LANGUAGE_PATH' ) || define( 'ZVC_PLUGIN_LANGUAGE_PATH', trailingslashit( basename( ZVC_PLUGIN_DIR_PATH ) ) . 'languages/' );
defined( 'ZVC_PLUGIN_ABS_NAME' ) || define( 'ZVC_PLUGIN_ABS_NAME', plugin_basename( __FILE__ ) );

$upload_dir = wp_upload_dir( null, false );
define( 'ZVC_LOG_DIR', $upload_dir['basedir'] . '/vczapi-logs/' );

// the main plugin class
require_once ZVC_PLUGIN_INCLUDES_PATH . '/Bootstrap.php';

add_action( 'plugins_loaded', 'Codemanas\VczApi\Bootstrap::instance', 99 );
register_activation_hook( __FILE__, 'Codemanas\VczApi\Bootstrap::activate' );
register_deactivation_hook( __FILE__, 'Codemanas\VczApi\Bootstrap::deactivate' );