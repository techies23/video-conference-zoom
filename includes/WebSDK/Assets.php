<?php

namespace Codemanas\VczApi\WebSDK;

/**
 * Registers and prints the Join-via-Browser assets.
 *
 * @package     Video Conferencing with Zoom API
 * @subpackage  WebSDK
 * @author      Deepen Bajracharya
 * @since 4.7.0
 */
final class Assets {

	/**
	 * Handle for the bootstrap bundle, which drives the join form.
	 */
	public const HANDLE_BOOTSTRAP = 'vczapi-websdk-bootstrap';

	/**
	 * Handle for the SDK client bundle, loaded on demand.
	 */
	public const HANDLE_CLIENT = 'vczapi-websdk-client';

	/**
	 * Handle for the join page stylesheet.
	 */
	public const HANDLE_STYLE = 'vczapi-websdk-style';

	/**
	 * Global the inline configuration is published under.
	 */
	public const CONFIG_OBJECT = 'vczapiWebSDK';

	/**
	 * Not instantiable.
	 */
	private function __construct() {
	}

	/**
	 * Register the assets without enqueueing them.
	 *
	 * Registration is separated from printing so `maybe_render()` can register
	 * first, decide which template to load, and only then enqueue with the
	 * matching configuration.
	 */
	public static function register(): void {
		wp_register_style(
			self::HANDLE_STYLE,
			VCZAPI_PLUGIN_PUBLIC_ASSET_URI . '/css/style.min.css',
			array(),
			self::asset_version( VCZAPI_PLUGIN_PUBLIC_ASSET_URI . '/css/style.min.css' )
		);

		wp_register_script(
			self::HANDLE_BOOTSTRAP,
			self::client_url( 'websdk-bootstrap.bundle.js' ),
			array(),
			self::asset_version( self::local_client_path( 'jvb-bootstrap.bundle.js' ) ),
			true
		);

		// The SDK bundle is registered but never enqueued here. It is fetched
		// lazily by the bootstrap bundle once the visitor submits the form, so a
		// visitor who never joins never downloads 5.7MB of JavaScript.
		wp_register_script(
			self::HANDLE_CLIENT,
			self::client_url(),
			array(),
			self::asset_version( self::local_client_path( 'websdk-client.bundle.js' ) ),
			true
		);
	}

	/**
	 * Enqueue the join page assets and publish the client configuration.
	 *
	 * The configuration is attached as an inline script *before* the bootstrap
	 * bundle, so the bundle can read it synchronously at module scope. That
	 * removes the need for the bundle to poll for a global that may not exist
	 * yet.
	 *
	 * @param array $config Client configuration.
	 */
	public static function enqueue( array $config ): void {
		/**
		 * Filter the Join-via-Browser client configuration before it is printed.
		 *
		 * @param array $config Client configuration.
		 *
		 * @since 4.9.0
		 */
		$config = (array) apply_filters( 'vczapi_join_via_browser_client_config', $config );

		wp_enqueue_style( self::HANDLE_STYLE );
		wp_enqueue_script( self::HANDLE_BOOTSTRAP );

		wp_add_inline_script(
			self::HANDLE_BOOTSTRAP,
			'window.' . self::CONFIG_OBJECT . ' = ' . wp_json_encode( $config ) . ';',
			'before'
		);
	}

	/**
	 * Enqueue just the stylesheet.
	 *
	 * The error page is rendered without `enqueue()` because it has no client
	 * configuration to publish, but it still needs to be styled. Enqueueing
	 * explicitly keeps that page from depending on `wp_print_styles()` printing
	 * a handle that was only ever registered.
	 */
	public static function enqueue_styles(): void {
		wp_enqueue_style( self::HANDLE_STYLE );
	}

	/**
	 * Print the registered stylesheets.
	 *
	 * The join page does not call `wp_head()`, so the enqueued stylesheet has to
	 * be printed explicitly.
	 */
	public static function print_styles(): void {
		wp_print_styles( array( self::HANDLE_STYLE ) );
	}

	/**
	 * Print the bootstrap bundle and its inline configuration.
	 *
	 * Called at the end of the document body so the form markup already exists
	 * when the bundle binds its listeners.
	 */
	public static function print_scripts(): void {
		wp_print_scripts( array( self::HANDLE_BOOTSTRAP ) );
	}

	/**
	 * URL of a bundle in the SDK asset directory.
	 *
	 * @param string $file File name.
	 */
	public static function client_url( string $file = 'websdk-client.bundle.js' ): string {
		return VCZAPI_PLUGIN_SDK_URI . '/' . $file;
	}

	/**
	 * Absolute path of a bundle in the SDK asset directory.
	 *
	 * @param string $file File name.
	 */
	public static function local_client_path( string $file ): string {
		return VCZAPI_PLUGIN_DIR_PATH . 'dist/vendor/zoom/websdk/' . $file;
	}

	/**
	 * Cache busting version for a built asset.
	 *
	 * `dist/` is a build artifact and is not versioned with the plugin, so the
	 * plugin version alone would leave browsers and CDNs serving a stale bundle
	 * after a deploy. The file's modification time changes whenever webpack
	 * rewrites it, which makes it the most accurate version string available.
	 *
	 * @param string $absolute_path Absolute path to the built file.
	 */
	private static function asset_version( string $absolute_path ): ?string {
		if ( is_readable( $absolute_path ) ) {
			$mtime = filemtime( $absolute_path );

			if ( false !== $mtime ) {
				return (string) $mtime;
			}
		}

		return defined( 'VCZAPI_PLUGIN_VERSION' ) ? VCZAPI_PLUGIN_VERSION : null;
	}

	/**
	 * URL of the SDK's cross-origin helper page.
	 *
	 * The Meeting SDK needs this to open the media permission prompt. It is
	 * served from this origin, which is why the page is sent with COEP
	 * `require-corp`.
	 */
	public static function helper_url(): string {
		return VCZAPI_PLUGIN_SDK_URI . '/helper.html';
	}
}
