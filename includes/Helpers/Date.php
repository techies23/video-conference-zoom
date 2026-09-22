<?php

namespace Codemanas\VczApi\Helpers;

use Codemanas\VczApi\Admin\Repository\SettingsRepository;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

/**
 * Handles Date functions
 *
 * @since 4.2.2
 * @author Deepen Bajracharya
 */
class Date {

	/**
	 * Map of locale format keys to format patterns
	 */
	private const LOCALE_FORMAT_MAP = [
		'L LT' => 'm/d/Y {time}',
		'l LT' => 'n/j/Y {time}',
		'llll' => 'D, {month} j, Y {time}',
		'lll'  => '{month} j, Y {time}',
		'LLLL' => 'l, {month} j, Y {time}',
	];

	/**
	 * Get Timezone String based on WP Timezone settings
	 *
	 * @return string
	 */
	public static function get_timezone_offset(): string {
		$tz = wp_timezone_string();

		if ( $tz === 'Asia/Katmandu' ) {
			return 'Asia/Kathmandu';
		}

		return $tz;
	}

	/**
	 * Get Timezone List
	 *
	 * @return array<string, string>
	 */
	public static function timezone_list(): array {
		$zones_array = [
			"Pacific/Midway"                 => "(GMT-11:00) Midway Island, Samoa",
			"Pacific/Pago_Pago"              => "(GMT-11:00) Pago Pago",
			"Pacific/Honolulu"               => "(GMT-10:00) Hawaii",
			"America/Anchorage"              => "(GMT-8:00) Alaska",
			"America/Vancouver"              => "(GMT-7:00) Vancouver",
			"America/Los_Angeles"            => "(GMT-7:00) Pacific Time (US and Canada)",
			"America/Tijuana"                => "(GMT-7:00) Tijuana",
			"America/Phoenix"                => "(GMT-7:00) Arizona",
			"America/Edmonton"               => "(GMT-6:00) Edmonton",
			"America/Denver"                 => "(GMT-6:00) Mountain Time (US and Canada)",
			"America/Mazatlan"               => "(GMT-6:00) Mazatlan",
			"America/Regina"                 => "(GMT-6:00) Saskatchewan",
			"America/Guatemala"              => "(GMT-6:00) Guatemala",
			"America/El_Salvador"            => "(GMT-6:00) El Salvador",
			"America/Managua"                => "(GMT-6:00) Managua",
			"America/Costa_Rica"             => "(GMT-6:00) Costa Rica",
			"America/Tegucigalpa"            => "(GMT-6:00) Tegucigalpa",
			"America/Winnipeg"               => "(GMT-5:00) Winnipeg",
			"America/Chicago"                => "(GMT-5:00) Central Time (US and Canada)",
			"America/Mexico_City"            => "(GMT-5:00) Mexico City",
			"America/Panama"                 => "(GMT-5:00) Panama",
			"America/Bogota"                 => "(GMT-5:00) Bogota",
			"America/Lima"                   => "(GMT-5:00) Lima",
			"America/Caracas"                => "(GMT-4:30) Caracas",
			"America/Montreal"               => "(GMT-4:00) Montreal",
			"America/New_York"               => "(GMT-4:00) Eastern Time (US and Canada)",
			"America/Indianapolis"           => "(GMT-4:00) Indiana (East)",
			"America/Puerto_Rico"            => "(GMT-4:00) Puerto Rico",
			"America/Santiago"               => "(GMT-4:00) Santiago",
			"America/Halifax"                => "(GMT-3:00) Halifax",
			"America/Montevideo"             => "(GMT-3:00) Montevideo",
			"America/Araguaina"              => "(GMT-3:00) Brasilia",
			"America/Argentina/Buenos_Aires" => "(GMT-3:00) Buenos Aires, Georgetown",
			"America/Sao_Paulo"              => "(GMT-3:00) Sao Paulo",
			"Canada/Atlantic"                => "(GMT-3:00) Atlantic Time (Canada)",
			"America/St_Johns"               => "(GMT-2:30) Newfoundland and Labrador",
			"America/Godthab"                => "(GMT-2:00) Greenland",
			"Atlantic/Cape_Verde"            => "(GMT-1:00) Cape Verde Islands",
			"Atlantic/Azores"                => "(GMT+0:00) Azores",
			"UTC"                            => "(GMT+0:00) Universal Time UTC",
			"Etc/Greenwich"                  => "(GMT+0:00) Greenwich Mean Time",
			"Atlantic/Reykjavik"             => "(GMT+0:00) Reykjavik",
			"Africa/Nouakchott"              => "(GMT+0:00) Nouakchott",
			"Europe/Dublin"                  => "(GMT+1:00) Dublin",
			"Europe/London"                  => "(GMT+1:00) London",
			"Europe/Lisbon"                  => "(GMT+1:00) Lisbon",
			"Africa/Casablanca"              => "(GMT+1:00) Casablanca",
			"Africa/Bangui"                  => "(GMT+1:00) West Central Africa",
			"Africa/Algiers"                 => "(GMT+1:00) Algiers",
			"Africa/Tunis"                   => "(GMT+1:00) Tunis",
			"Europe/Belgrade"                => "(GMT+2:00) Belgrade, Bratislava, Ljubljana",
			"CET"                            => "(GMT+2:00) Sarajevo, Skopje, Zagreb",
			"Europe/Oslo"                    => "(GMT+2:00) Oslo",
			"Europe/Copenhagen"              => "(GMT+2:00) Copenhagen",
			"Europe/Brussels"                => "(GMT+2:00) Brussels",
			"Europe/Berlin"                  => "(GMT+2:00) Amsterdam, Berlin, Rome, Stockholm, Vienna",
			"Europe/Amsterdam"               => "(GMT+2:00) Amsterdam",
			"Europe/Rome"                    => "(GMT+2:00) Rome",
			"Europe/Stockholm"               => "(GMT+2:00) Stockholm",
			"Europe/Vienna"                  => "(GMT+2:00) Vienna",
			"Europe/Luxembourg"              => "(GMT+2:00) Luxembourg",
			"Europe/Paris"                   => "(GMT+2:00) Paris",
			"Europe/Zurich"                  => "(GMT+2:00) Zurich",
			"Europe/Madrid"                  => "(GMT+2:00) Madrid",
			"Africa/Harare"                  => "(GMT+2:00) Harare, Pretoria",
			"Europe/Warsaw"                  => "(GMT+2:00) Warsaw",
			"Europe/Prague"                  => "(GMT+2:00) Prague Bratislava",
			"Europe/Budapest"                => "(GMT+2:00) Budapest",
			"Africa/Tripoli"                 => "(GMT+2:00) Tripoli",
			"Africa/Cairo"                   => "(GMT+2:00) Cairo",
			"Africa/Johannesburg"            => "(GMT+2:00) Johannesburg",
			"Europe/Helsinki"                => "(GMT+3:00) Helsinki",
			"Africa/Nairobi"                 => "(GMT+3:00) Nairobi",
			"Europe/Sofia"                   => "(GMT+3:00) Sofia",
			"Europe/Istanbul"                => "(GMT+3:00) Istanbul",
			"Europe/Athens"                  => "(GMT+3:00) Athens",
			"Europe/Bucharest"               => "(GMT+3:00) Bucharest",
			"Asia/Nicosia"                   => "(GMT+3:00) Nicosia",
			"Asia/Beirut"                    => "(GMT+3:00) Beirut",
			"Asia/Damascus"                  => "(GMT+3:00) Damascus",
			"Asia/Jerusalem"                 => "(GMT+3:00) Jerusalem",
			"Asia/Amman"                     => "(GMT+3:00) Amman",
			"Europe/Moscow"                  => "(GMT+3:00) Moscow",
			"Asia/Baghdad"                   => "(GMT+3:00) Baghdad",
			"Asia/Kuwait"                    => "(GMT+3:00) Kuwait",
			"Asia/Riyadh"                    => "(GMT+3:00) Riyadh",
			"Asia/Bahrain"                   => "(GMT+3:00) Bahrain",
			"Asia/Qatar"                     => "(GMT+3:00) Qatar",
			"Asia/Aden"                      => "(GMT+3:00) Aden",
			"Africa/Khartoum"                => "(GMT+3:00) Khartoum",
			"Africa/Djibouti"                => "(GMT+3:00) Djibouti",
			"Africa/Mogadishu"               => "(GMT+3:00) Mogadishu",
			"Europe/Kiev"                    => "(GMT+3:00) Kiev",
			"Asia/Dubai"                     => "(GMT+4:00) Dubai",
			"Asia/Muscat"                    => "(GMT+4:00) Muscat",
			"Asia/Tehran"                    => "(GMT+4:30) Tehran",
			"Asia/Kabul"                     => "(GMT+4:30) Kabul",
			"Asia/Baku"                      => "(GMT+5:00) Baku, Tbilisi, Yerevan",
			"Asia/Yekaterinburg"             => "(GMT+5:00) Yekaterinburg",
			"Asia/Tashkent"                  => "(GMT+5:00) Islamabad, Karachi, Tashkent",
			"Asia/Calcutta"                  => "(GMT+5:30) India",
			"Asia/Kolkata"                   => "(GMT+5:30) Mumbai, Kolkata, New Delhi",
			"Asia/Kathmandu"                 => "(GMT+5:45) Kathmandu",
			"Asia/Novosibirsk"               => "(GMT+6:00) Novosibirsk",
			"Asia/Almaty"                    => "(GMT+6:00) Almaty",
			"Asia/Dacca"                     => "(GMT+6:00) Dacca",
			"Asia/Dhaka"                     => "(GMT+6:00) Astana, Dhaka",
			"Asia/Krasnoyarsk"               => "(GMT+7:00) Krasnoyarsk",
			"Asia/Bangkok"                   => "(GMT+7:00) Bangkok",
			"Asia/Saigon"                    => "(GMT+7:00) Vietnam",
			"Asia/Jakarta"                   => "(GMT+7:00) Jakarta",
			"Asia/Irkutsk"                   => "(GMT+8:00) Irkutsk, Ulaanbaatar",
			"Asia/Shanghai"                  => "(GMT+8:00) Beijing, Shanghai",
			"Asia/Hong_Kong"                 => "(GMT+8:00) Hong Kong",
			"Asia/Taipei"                    => "(GMT+8:00) Taipei",
			"Asia/Kuala_Lumpur"              => "(GMT+8:00) Kuala Lumpur",
			"Asia/Singapore"                 => "(GMT+8:00) Singapore",
			"Australia/Perth"                => "(GMT+8:00) Perth",
			"Asia/Yakutsk"                   => "(GMT+9:00) Yakutsk",
			"Asia/Seoul"                     => "(GMT+9:00) Seoul",
			"Asia/Tokyo"                     => "(GMT+9:00) Osaka, Sapporo, Tokyo",
			"Australia/Darwin"               => "(GMT+9:30) Darwin",
			"Australia/Adelaide"             => "(GMT+9:30) Adelaide",
			"Asia/Vladivostok"               => "(GMT+10:00) Vladivostok",
			"Pacific/Port_Moresby"           => "(GMT+10:00) Guam, Port Moresby",
			"Australia/Brisbane"             => "(GMT+10:00) Brisbane",
			"Australia/Sydney"               => "(GMT+10:00) Canberra, Melbourne, Sydney",
			"Australia/Hobart"               => "(GMT+10:00) Hobart",
			"Asia/Magadan"                   => "(GMT+10:00) Magadan",
			"SST"                            => "(GMT+11:00) Solomon Islands",
			"Pacific/Noumea"                 => "(GMT+11:00) New Caledonia",
			"Asia/Kamchatka"                 => "(GMT+12:00) Kamchatka",
			"Pacific/Fiji"                   => "(GMT+12:00) Fiji Islands, Marshall Islands",
			"Pacific/Auckland"               => "(GMT+12:00) Auckland, Wellington",
		];

		return (array) apply_filters( 'vczapi_timezone_list', $zones_array );
	}

	/**
	 * Calculate Time based on Timezone
	 *
	 * @param string|int|DateTimeImmutable $start_time
	 * @param string|null $tz
	 * @param string $format
	 * @param bool $defaults
	 *
	 * @return DateTimeImmutable|string
	 */
	public static function dateConverter(
		mixed $start_time,
		?string $tz,
		string $format = 'F j, Y, g:i a ( T )',
		bool $defaults = true
	): DateTimeImmutable|string {
		try {
			$timezone_name = ! empty( $tz ) ? $tz : 'America/Los_Angeles';
			$target_tz     = new DateTimeZone( $timezone_name );

			if ( $start_time instanceof DateTimeImmutable ) {
				$date = $start_time->setTimezone( $target_tz );
			} else {
				$date = ( new DateTimeImmutable( $start_time ) )->setTimezone( $target_tz );
			}

			if ( ! $format ) {
				return $date;
			}

			if ( ! $defaults ) {
				return $date->format( $format );
			}

			$locale      = get_locale();
			$date_format = SettingsRepository::getSetting( 'locale_format' );

			if ( ! empty( $locale ) && ! empty( $date_format ) ) {
				return self::formatLocalizedDate( $date, $date_format, $format );
			}

			return $date->format( $format );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * Internal helper to handle localized formatting via wp_date()
	 */
	private static function formatLocalizedDate( DateTimeImmutable $date, string $dateFormatKey, string $defaultFormat ): string {
		if ( $dateFormatKey === 'custom' ) {
			$custom_format = SettingsRepository::getSetting( 'custom_date_time_format' );
			$target_format = ! empty( $custom_format ) ? $custom_format : $defaultFormat;
		} else {
			$target_format = self::LOCALE_FORMAT_MAP[ $dateFormatKey ] ?? $defaultFormat;

			$time_indicator       = SettingsRepository::getSetting( 'twentyfour_format' ) ? 'H:i' : 'h:i A';
			$full_month_indicator = SettingsRepository::getSetting( 'full_month_format' ) ? 'F' : 'M';

			$target_format = str_replace(
				[ '{time}', '{month}' ],
				[ $time_indicator, $full_month_indicator ],
				$target_format
			);
		}

		return wp_date( $target_format, $date->getTimestamp(), $date->getTimezone() );
	}
}