<?php
/**
 * @package     Video Conferencing with Zoom API
 * @subpackage  Tests
 */

namespace Codemanas\VczApi\Tests\Unit;

/**
 * Verifies the PHP to date-fns format translation.
 *
 * The frontend renders every meeting timestamp through this converter, so a
 * mistranslated token shows up as a wrong date on every meeting page rather
 * than as an obvious error.
 */
class DateFnsFormatTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Load the converter without pulling in all of helpers.php.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		require_once __DIR__ . '/../includes/helpers.php';
	}

	/**
	 * @dataProvider provide_formats
	 *
	 * @param string $php      PHP date format.
	 * @param string $expected date-fns pattern.
	 * @return void
	 */
	public function test_converts_php_format_to_date_fns( string $php, string $expected ): void {
		$this->assertSame( $expected, vczapi_convert_php_to_date_fns_format( $php ) );
	}

	/**
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function provide_formats(): array {
		return [
			'empty'                => [ '', '' ],
			'month day year'       => [ 'M j, Y', 'MMM d, yyyy' ],
			'long month'           => [ 'F j, Y', 'MMMM d, yyyy' ],
			'twelve hour clock'    => [ 'g:i a', 'h:mm a' ],
			'meridiem spelling'    => [ 'g:i A', 'h:mm a' ],
			'twenty four clock'    => [ 'H:i:s', 'HH:mm:ss' ],
			'iso like'             => [ 'Y-m-d H:i:s', 'yyyy-MM-dd HH:mm:ss' ],
			'rfc day and month'    => [ 'D, d M Y H:i:s', 'EEE, dd MMM yyyy HH:mm:ss' ],
			'no padding'           => [ 'n/j/y', 'M/d/yy' ],
			'weekday long'         => [ 'l, F j, Y', 'EEEE, MMMM d, yyyy' ],
			'weekday short'        => [ 'D, M j', 'EEE, MMM d' ],
			'iso week'             => [ 'W', 'w' ],
			'day of year'          => [ 'z', 'D' ],
			'timezone identifier'  => [ 'e', 'VV' ],
			'timezone offset'      => [ 'P', 'xxx' ],
			'unix timestamp'       => [ 'U', 't' ],
			'iso 8601'             => [ 'c', "yyyy-MM-dd'T'HH:mm:ssxxx" ],
			'rfc 2822'             => [ 'r', 'EEE, dd MMM yyyy HH:mm:ss xx' ],
			'ordinal dropped'      => [ 'l jS F Y', 'EEEE d MMMM yyyy' ],
			// `t` and `L` have no date-fns token and vanish entirely.
			'days in month'        => [ 'j t', 'd ' ],
			'day of month'         => [ 'j', 'd' ],
			// A backslash escapes the next character, which stays literal even
			// when it happens to be a format letter: \a -> 'a', \t -> 't'.
			'backslash literals'   => [ "\\a\\t g:i A", "'a''t' h:mm a" ],
			// Untouched characters pass through.
			'punctuation'          => [ 'M j, Y / H:i', 'MMM d, yyyy / HH:mm' ],
		];
	}

	/**
	 * The week-year token must never survive: date-fns reads `Y` as an ISO
	 * week-year, which silently renders dates near new year on the wrong year.
	 *
	 * @return void
	 */
	public function test_week_year_token_is_never_emitted(): void {
		$this->assertStringNotContainsString( 'Y', vczapi_convert_php_to_date_fns_format( 'Y' ) );
		$this->assertStringNotContainsString( 'Y', vczapi_convert_php_to_date_fns_format( 'o Y' ) );
	}

	/**
	 * Escaped literals must survive so text such as "at" is not re-tokenised.
	 *
	 * @return void
	 */
	public function test_backslash_escaped_literals_are_preserved(): void {
		$this->assertSame( "'a''t' h:mm a", vczapi_convert_php_to_date_fns_format( "\\a\\t g:i A" ) );
	}

	/**
	 * Apostrophe-quoted literals must be preserved verbatim.
	 *
	 * @return void
	 */
	public function test_quoted_literals_are_preserved(): void {
		$this->assertSame( "h:mm a 'o''clock'", vczapi_convert_php_to_date_fns_format( "g:i a 'o''clock'" ) );
	}

	/**
	 * The DateTime Format presets are stored as Moment tokens and must keep
	 * rendering the way the settings screen documents them once handed to date-fns.
	 *
	 * @return void
	 */
	public function test_moment_presets_match_their_documented_examples(): void {
		$presets = [
			'LLLL'   => 'PPPPpppp',
			'lll'    => 'MMM d, yyyy hh:mm a',
			'llll'   => 'EEE, MMM d, yyyy hh:mm a',
			'L LT'   => 'P h:mm a',
			'l LT'   => 'M/d/yyyy h:mm a',
		];

		foreach ( $presets as $moment => $expected ) {
			$this->assertSame(
				$expected,
				vczapi_convert_moment_to_date_fns_format( $moment ),
				"Moment preset '{$moment}' did not convert as expected."
			);
		}
	}

	/**
	 * Multi-character tokens must win over the single characters inside them,
	 * otherwise `LLLL` would decode as four standalone months.
	 *
	 * @return void
	 */
	public function test_moment_tokens_are_matched_longest_first(): void {
		$this->assertSame( 'PPPPpppp', vczapi_convert_moment_to_date_fns_format( 'LLLL' ) );
		$this->assertSame( 'PPppp', vczapi_convert_moment_to_date_fns_format( 'LLL' ) );
		$this->assertSame( 'PPpp', vczapi_convert_moment_to_date_fns_format( 'LL' ) );
		$this->assertSame( 'P', vczapi_convert_moment_to_date_fns_format( 'L' ) );
		$this->assertSame( 'EEEE', vczapi_convert_moment_to_date_fns_format( 'dddd' ) );
		$this->assertSame( 'EEE', vczapi_convert_moment_to_date_fns_format( 'ddd' ) );
	}

	/**
	 * `L` and `l` are date-only in Moment, so `L LT` must not repeat the time.
	 *
	 * @return void
	 */
	public function test_moment_date_only_tokens_combine_with_lt(): void {
		$this->assertSame( 'P h:mm a', vczapi_convert_moment_to_date_fns_format( 'L LT' ) );
		$this->assertSame( 'M/d/yyyy h:mm a', vczapi_convert_moment_to_date_fns_format( 'l LT' ) );
	}

	/**
	 * An unset option must not turn into a broken pattern; public.js falls back
	 * to PPPPpp when it receives an empty string.
	 *
	 * @return void
	 */
	public function test_empty_input_returns_an_empty_pattern(): void {
		$this->assertSame( '', vczapi_convert_php_to_date_fns_format( '' ) );
		$this->assertSame( '', vczapi_convert_moment_to_date_fns_format( '' ) );
		$this->assertSame( '', vczapi_convert_moment_to_date_fns_format( false ) );
	}
}
