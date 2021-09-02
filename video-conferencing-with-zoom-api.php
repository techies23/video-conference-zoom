<?php
/**
 * @link              https://www.deepenbajracharya.com.np
 * @since             1.0.0
 * @package           Video Conferencing with Zoom
 *
 * Plugin Name:       Video Conferencing with Zoom
 * Plugin URI:        https://wordpress.org/plugins/video-conferencing-with-zoom-api/
 * Description:       Video Conferencing with Zoom Meetings and Webinars plugin provides you with great functionality of managing Zoom meetings, Webinar scheduling options, and users directly from your WordPress dashboard.
 * Version:           3.8.13
 * Author:            Deepen Bajracharya
 * Author URI:        https://www.deepenbajracharya.com.np
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       video-conferencing-with-zoom-api
 * Requires PHP:      7.0
 * Domain Path:       /languages
 * Requires at least: 5.5.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define('ZVC_PLUGIN_FILE',__FILE__);
define( 'ZVC_PLUGIN_SLUG', 'video-conferencing-zoom' );
define( 'ZVC_PLUGIN_VERSION', '3.8.13' );
define( 'ZVC_ZOOM_WEBSDK_VERSION', '1.9.8' );
define( 'ZVC_PLUGIN_AUTHOR', 'https://deepenbajracharya.com.np' );
define( 'ZVC_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'ZVC_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'ZVC_PLUGIN_ADMIN_ASSETS_URL', ZVC_PLUGIN_DIR_URL . 'assets/admin' );
define( 'ZVC_PLUGIN_PUBLIC_ASSETS_URL', ZVC_PLUGIN_DIR_URL . 'assets/public' );
define( 'ZVC_PLUGIN_VENDOR_ASSETS_URL', ZVC_PLUGIN_DIR_URL . 'assets/vendor' );
define( 'ZVC_PLUGIN_VIEWS_PATH', ZVC_PLUGIN_DIR_PATH . 'includes/views' );
define( 'ZVC_PLUGIN_INCLUDES_PATH', ZVC_PLUGIN_DIR_PATH . 'includes' );
define( 'ZVC_PLUGIN_IMAGES_PATH', ZVC_PLUGIN_DIR_URL . 'assets/images' );
define( 'ZVC_PLUGIN_LANGUAGE_PATH', trailingslashit( basename( ZVC_PLUGIN_DIR_PATH ) ) . 'languages/' );
define( 'ZVC_PLUGIN_ABS_NAME', plugin_basename( __FILE__ ) );

// the main plugin class
require_once ZVC_PLUGIN_INCLUDES_PATH . '/Bootstrap.php';

add_action( 'plugins_loaded', 'Codemanas\VczApi\Bootstrap::instance', 99 );
register_activation_hook( __FILE__, 'Codemanas\VczApi\Bootstrap::activate' );
register_deactivation_hook( __FILE__, 'Codemanas\VczApi\Bootstrap::deactivate' );
