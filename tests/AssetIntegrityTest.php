<?php

namespace Codemanas\VczApi\Tests\Unit;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Guards against the class of bug where a PHP asset constant points at a
 * directory that the build never produces, so every script/style 404s
 * silently at runtime.
 *
 * This previously broke the whole Join-via-Browser page: the four legacy
 * `assets/` constants pointed at a directory that does not exist, and the
 * Meeting SDK bundle referenced three `externals` globals (React, Redux,
 * ReduxThunk) that were never emitted.
 *
 * @package Video Conferencing with Zoom API
 */
class AssetIntegrityTest extends TestCase {

	/**
	 * Absolute path to the plugin root.
	 *
	 * @var string
	 */
	private static string $root;

	/**
	 * URL -> filesystem path prefix used by the probe.
	 *
	 * @var string
	 */
	private static string $url_prefix = 'https://example.test/wp-content/plugins/video-conferencing-with-zoom-api/';

	/**
	 * Cached probe output.
	 *
	 * @var array<string, string>|null
	 */
	private static ?array $constants = null;

	/**
	 * Resolve the plugin root once.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		self::$root = dirname( __DIR__ );
	}

	/**
	 * Load the plugin bootstrap in an isolated process and return its constants.
	 *
	 * @return array<string, string>
	 */
	private static function constants(): array {
		if ( null !== self::$constants ) {
			return self::$constants;
		}

		$output = [];
		$status = 0;

		exec(
			sprintf( '%s %s 2>&1', escapeshellarg( PHP_BINARY ), escapeshellarg( self::$root . '/tests/asset-probe.php' ) ),
			$output,
			$status
		);

		self::assertSame( 0, $status, 'tests/asset-probe.php failed to run: ' . implode( "\n", $output ) );

		$decoded = json_decode( implode( "\n", $output ), true );

		self::assertIsArray( $decoded, 'Asset probe did not return valid JSON: ' . implode( "\n", $output ) );

		self::$constants = $decoded;

		return self::$constants;
	}

	/**
	 * Convert a plugin URL into an absolute filesystem path.
	 *
	 * @param string $url Plugin URL.
	 * @return string|null Absolute path, or null when the URL is not plugin relative.
	 */
	private static function to_path( string $url ): ?string {
		if ( ! str_starts_with( $url, self::$url_prefix ) ) {
			return null;
		}

		return self::$root . '/' . ltrim( substr( $url, strlen( self::$url_prefix ) ), '/' );
	}

	/**
	 * Constants kept only for backwards compatibility with third party addons.
	 *
	 * They still resolve to URLs the plugin no longer serves, so they must not be
	 * treated as live asset paths. They stay defined because removing a public
	 * constant turns into a fatal error on any addon that references it.
	 */
	private const DEPRECATED_ALIASES = [
		'VCZAPI_PLUGIN_IMAGES_URI',
		'ZVC_PLUGIN_IMAGES_PATH',
	];

	/**
	 * Every URL constant must resolve to a directory that actually exists.
	 *
	 * @return void
	 */
	public function test_asset_url_constants_resolve_to_existing_directories(): void {
		$constants = self::constants();
		$checked   = 0;
		$missing   = [];

		foreach ( $constants as $name => $value ) {
			if ( in_array( $name, self::DEPRECATED_ALIASES, true ) ) {
				continue;
			}

			$path = self::to_path( $value );

			if ( null === $path ) {
				continue;
			}

			++$checked;
			$missing[] = sprintf( '%s => %s', $name, $path );
		}

		$this->assertGreaterThan( 0, $checked, 'Probe returned no plugin relative constants to check.' );
		$this->assertSame(
			[],
			array_values(
				array_filter(
					$missing,
					static function ( $entry ) {
						[$name, $path] = explode( ' => ', $entry, 2 );

						return ! is_dir( $path );
					}
				)
			),
			"Asset constants point at missing directories:\n" . implode( "\n", $missing )
		);
	}

	/**
	 * The four legacy `assets/` constants must no longer be emitted.
	 *
	 * They are kept for backwards compatibility with third party addons, but
	 * reassigned to the real build output under dist/.
	 *
	 * @return void
	 */
	public function test_legacy_asset_constants_do_not_reference_removed_assets_dir(): void {
		$constants = self::constants();

		$legacy = [
			'ZVC_PLUGIN_ADMIN_ASSETS_URL',
			'ZVC_PLUGIN_PUBLIC_ASSETS_URL',
			'ZVC_PLUGIN_VENDOR_ASSETS_URL',
			'ZVC_PLUGIN_IMAGES_PATH',
		];

		foreach ( $legacy as $name ) {
			$this->assertArrayHasKey( $name, $constants, $name . ' should still be defined for backwards compatibility.' );
			$this->assertStringNotContainsString(
				'/assets/',
				$constants[ $name ],
				$name . ' still points at the removed assets/ directory.'
			);
		}
	}

	/**
	 * Production code must not reference the dead image directory.
	 *
	 * The block editor previews, the meeting status icons and the listings
	 * spinner all pointed at files under dist/images/ that were never committed
	 * to the repository, so every one of them rendered broken. They now use CSS
	 * placeholders instead.
	 *
	 * @return void
	 */
	public function test_production_code_does_not_reference_the_dead_image_directory(): void {
		$offenders = [];

		foreach ( [ 'includes', 'templates', 'src' ] as $dir ) {
			$base = self::$root . '/' . $dir;

			if ( ! is_dir( $base ) ) {
				continue;
			}

			$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $base, FilesystemIterator::SKIP_DOTS ) );

			foreach ( $iterator as $file ) {
				$real = realpath( $file->getPathname() );

				if ( false === $real || ! preg_match( '/\.(php|js|jsx|ts|tsx)$/', $real ) ) {
					continue;
				}

				$contents = file_get_contents( $real );

				if ( false === $contents ) {
					continue;
				}

				foreach ( [ 'VCZAPI_PLUGIN_IMAGES_URI', 'ZVC_PLUGIN_IMAGES_PATH' ] as $constant ) {
					if ( false !== strpos( $contents, $constant ) ) {
						$offenders[] = sprintf( '%s uses %s', str_replace( self::$root . '/', '', $real ), $constant );
					}
				}
			}
		}

		$this->assertSame( [], $offenders, "Code still references the dead image directory:\n" . implode( "\n", $offenders ) );
	}

	/**
	 * The legacy version constant must not drift from the canonical one.
	 *
	 * @return void
	 */
	public function test_legacy_version_constant_matches_canonical_version(): void {
		$constants = self::constants();

		$this->assertSame(
			$constants['VCZAPI_PLUGIN_VERSION'],
			$constants['ZVC_PLUGIN_VERSION'],
			'ZVC_PLUGIN_VERSION has drifted from VCZAPI_PLUGIN_VERSION.'
		);
	}

	/**
	 * The Zoom Meeting SDK is bundled from npm, so there is no CDN fallback
	 * branch and therefore no pinned CDN version to keep in sync.
	 *
	 * @return void
	 */
	public function test_zoom_websdk_version_constants_are_removed(): void {
		$constants = self::constants();

		$this->assertArrayNotHasKey(
			'ZVC_ZOOM_WEBSDK_VERSION',
			$constants,
			'ZVC_ZOOM_WEBSDK_VERSION is a stale 4.0.0 CDN pin; the SDK is bundled from npm.'
		);
		$this->assertArrayNotHasKey(
			'VCZAPI_PLUGIN_ZOOM_WEBSDK_VERSION',
			$constants,
			'VCZAPI_PLUGIN_ZOOM_WEBSDK_VERSION is a stale 4.0.0 CDN pin; the SDK is bundled from npm.'
		);
	}

	/**
	 * The build must emit every asset the PHP layer registers.
	 *
	 * @return void
	 */
	public function test_build_emits_all_expected_assets(): void {
		$expected = [
			'admin/css/style.min.css',
			'admin/js/editor.min.js',
			'admin/js/scripts.min.js',
			'admin/js/vendors.min.js',
			'public/css/style.min.css',
			'public/js/public.min.js',
			'public/js/shortcode.min.js',
			'public/js/vendors.min.js',
			'vendor/zoom/websdk/zoom-meeting.bundle.js',
			'vendor/zoom/websdk/jvb-bootstrap.bundle.js',
			'vendor/zoom/websdk/helper.html',
		];

		$missing = [];

		foreach ( $expected as $relative ) {
			if ( ! file_exists( self::$root . '/dist/' . $relative ) ) {
				$missing[] = 'dist/' . $relative;
			}
		}

		$this->assertSame( [], $missing, "Build did not emit expected assets. Run `pnpm run build`.\n" . implode( "\n", $missing ) );
	}

	/**
	 * The Meeting SDK bundle must not reference externals any more.
	 *
	 * The SDK UMD build externalises react/redux/redux-thunk, so webpack must
	 * bundle them rather than emit bare global lookups that 404 at runtime.
	 *
	 * @return void
	 */
	public function test_websdk_bundle_does_not_depend_on_global_externals(): void {
		$bundle = self::$root . '/dist/vendor/zoom/websdk/zoom-meeting.bundle.js';

		$this->assertFileExists( $bundle, 'Run `pnpm run build` first.' );

		$contents = (string) file_get_contents( $bundle );

		foreach ( [ 'React', 'Redux', 'ReduxThunk' ] as $global ) {
			$this->assertStringNotContainsString(
				'exports=' . $global,
				$contents,
				sprintf( 'The SDK bundle still expects a global "%s" external. React/Redux/redux-thunk must be bundled instead.', $global )
			);
		}
	}
}
