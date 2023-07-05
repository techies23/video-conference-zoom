<?php

namespace Codemanas\VczApi\Helpers;

/**
 * Handles encryption functions
 *
 * @since 4.2.2
 * @author Deepen Bajracharya
 */
class Encryption {

	private static string $encryption_method = 'AES-256-CBC';

	private static string $iv = 'vlUmigHXwc1ryadBi2WYUI7JbsgARgjUtgddJymlrgnIa088jf1BxFNQUIr2ZGd5RIMGmGo3yPSnFXtrp0Jwbw';

	private static string $site_key;

	protected function __construct() {
		self::$site_key = get_site_url();
	}

	/**
	 * Encrypts the provided string
	 *
	 * @param $string
	 * @param $conversion_key
	 *
	 * @return string
	 */
	public static function encrypt( $string, $conversion_key = null ): string {
		$key = ! empty( $conversion_key ) ? $conversion_key : self::$site_key;

		// hash
		$key = hash( 'sha256', $key );

		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr( hash( 'sha256', self::$iv ), 0, 16 );

		$output = openssl_encrypt( $string, self::$encryption_method, $key, 0, $iv );

		return base64_encode( $output );
	}

	/**
	 * Decrypts the provided string
	 *
	 * @param $string
	 * @param $conversion_key
	 *
	 * @return false|string
	 */
	public static function decrypt( $string, $conversion_key = null ) {
		$key = ! empty( $conversion_key ) ? $conversion_key : self::$site_key;

		// hash
		$key = hash( 'sha256', $key );

		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr( hash( 'sha256', self::$iv ), 0, 16 );

		return openssl_decrypt( base64_decode( $string ), self::$encryption_method, $key, 0, $iv );
	}

	public static $instance = null;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}