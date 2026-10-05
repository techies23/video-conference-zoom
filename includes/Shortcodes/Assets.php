<?php

namespace Codemanas\VczApi\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Frontend asset registration for the shortcode layer.
 *
 * Only handles with a real build output are registered here. DataTables and the
 * jQuery UI datepicker are genuine runtime dependencies of
 * `src/public/js/shortcode.js` (`.DataTable()` and `.datepicker()`), so they are
 * declared as dependencies of the shortcode bundle rather than enqueued blindly.
 *
 * The `dist/vendor` files are produced by `pnpm run copy:vendors` from
 * `node_modules`; webpack never emits them.
 *
 * @package Codemanas\VczApi\Shortcodes
 */
class Assets {

	const STYLE_HANDLE = 'video-conferencing-with-zoom-api';

	const SCRIPT_HANDLE = 'video-conferencing-with-zoom-api-shortcode-js';

	const DATATABLES_STYLE_HANDLE = 'video-conferencing-with-zoom-api-datable';

	const DATATABLES_RESPONSIVE_STYLE_HANDLE = 'video-conferencing-with-zoom-api-datable-responsive';

	const DATATABLES_SCRIPT_HANDLE = 'video-conferencing-with-zoom-api-datable-js';

	const DATATABLES_RESPONSIVE_SCRIPT_HANDLE = 'video-conferencing-with-zoom-api-datable-responsive-js';

	const DATEPICKER_HANDLE = 'jquery-ui-datepicker';

	/**
	 * Global JS payload localized on the shortcode bundle.
	 *
	 * Read by `src/public/js/shortcode.js`.
	 */
	const JS_GLOBAL = 'vczapi_ajax';

	/**
	 * DataTables i18n payload localized on the DataTables bundle.
	 *
	 * Read by `src/public/js/shortcode.js` as the `language` option.
	 */
	const DATATABLES_I18N_GLOBAL = 'vczapi_dt_i18n';

	/**
	 * Register every frontend handle the shortcodes rely on.
	 *
	 * Called from the `Shortcodes` constructor so the handles exist regardless of
	 * which shortcode runs first.
	 *
	 * @return void
	 */
	public static function register(): void {
		// webpack only ever emits the minified filenames, so there is no
		// SCRIPT_DEBUG variant to fall back to.
		$version = defined( 'VCZAPI_PLUGIN_VERSION' ) ? VCZAPI_PLUGIN_VERSION : false;

		wp_register_style(
			self::STYLE_HANDLE,
			VCZAPI_PLUGIN_PUBLIC_ASSET_URI . '/css/style.min.css',
			[],
			$version
		);

		wp_register_style(
			self::DATATABLES_STYLE_HANDLE,
			VCZAPI_PLUGIN_VENDOR_ASSETS_URI . '/datatables.net-dt/css/dataTables.dataTables.min.css',
			[],
			$version
		);

		wp_register_style(
			self::DATATABLES_RESPONSIVE_STYLE_HANDLE,
			VCZAPI_PLUGIN_VENDOR_ASSETS_URI . '/datatables.net-responsive-dt/css/responsive.dataTables.min.css',
			[ self::DATATABLES_STYLE_HANDLE ],
			$version
		);

		wp_register_script(
			self::DATATABLES_SCRIPT_HANDLE,
			VCZAPI_PLUGIN_VENDOR_ASSETS_URI . '/datatables.net/js/dataTables.min.js',
			[ 'jquery' ],
			$version,
			true
		);

		wp_register_script(
			self::DATATABLES_RESPONSIVE_SCRIPT_HANDLE,
			VCZAPI_PLUGIN_VENDOR_ASSETS_URI . '/datatables.net-responsive/js/dataTables.responsive.min.js',
			[ self::DATATABLES_SCRIPT_HANDLE ],
			$version,
			true
		);

		wp_register_script(
			self::SCRIPT_HANDLE,
			VCZAPI_PLUGIN_PUBLIC_ASSET_URI . '/js/shortcode.min.js',
			[
				'jquery',
				self::DATEPICKER_HANDLE,
				self::DATATABLES_SCRIPT_HANDLE,
				self::DATATABLES_RESPONSIVE_SCRIPT_HANDLE,
			],
			$version,
			true
		);

		wp_localize_script( self::SCRIPT_HANDLE, self::JS_GLOBAL, [
			'ajaxurl'            => admin_url( 'admin-ajax.php' ),
			'loading_recordings' => __( 'Loading recordings.. Please wait..', 'video-conferencing-with-zoom-api' ),
		] );

		wp_localize_script( self::DATATABLES_SCRIPT_HANDLE, self::DATATABLES_I18N_GLOBAL, [
			'emptyTable'     => __( 'No data available in table', 'video-conferencing-with-zoom-api' ),
			'info'           => sprintf( __( 'Showing %s to %s of %s entries', 'video-conferencing-with-zoom-api' ), '_START_', '_END_', '_TOTAL_' ),
			'infoEmpty'      => __( '', 'video-conferencing-with-zoom-api' ),
			'infoFiltered'   => sprintf( __( 'filtered from %s total entries', 'video-conferencing-with-zoom-api' ), '_MAX_' ),
			'lengthMenu'     => sprintf( __( 'Show %s entries', 'video-conferencing-with-zoom-api' ), '_MENU_' ),
			'loadingRecords' => __( 'Loading', 'video-conferencing-with-zoom-api' ),
			'processing'     => __( 'Processing', 'video-conferencing-with-zoom-api' ),
			'search'         => __( 'Search', 'video-conferencing-with-zoom-api' ),
			'zeroRecords'    => __( 'No matching records found', 'video-conferencing-with-zoom-api' ),
			'paginate'       => [
				'first'    => __( 'First', 'video-conferencing-with-zoom-api' ),
				'last'     => __( 'Last', 'video-conferencing-with-zoom-api' ),
				'next'     => __( 'Next', 'video-conferencing-with-zoom-api' ),
				'previous' => __( 'Previous', 'video-conferencing-with-zoom-api' ),
			],
		] );
	}

	/**
	 * Load the shortcode bundle and stylesheet.
	 *
	 * DataTables and the datepicker arrive automatically as script dependencies.
	 *
	 * @return void
	 */
	public static function enqueue(): void {
		wp_enqueue_style( self::STYLE_HANDLE );
		wp_enqueue_style( self::DATATABLES_STYLE_HANDLE );
		wp_enqueue_style( self::DATATABLES_RESPONSIVE_STYLE_HANDLE );
		wp_enqueue_script( self::SCRIPT_HANDLE );
	}
}