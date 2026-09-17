<?php

namespace Codemanas\VczApi\Admin\Foundation\PostType;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Server-side validation + normalization for zoom meeting fields.
 *
 * Single source of truth used by both the classic metabox save path and the
 * native Gutenberg (REST) save path.
 *
 * @package Codemanas\VczApi\Admin\Foundation\PostType
 */
class MeetingValidator {

	/**
	 * Boolean-like fields stored as '1' when enabled (matching classic storage).
	 */
	private const BOOLEAN_FIELDS = [
		'disable_waiting_room',
		'waiting_room',
		'meeting_authentication',
		'host_video',
		'join_before_host',
		'participant_video',
		'mute_upon_entry',
		'site_option_logged_in',
		'site_option_browser_join',
		'site_option_enable_debug_log',
		'panelists_video',
		'practice_session',
		'hd_video',
		'allow_multiple_devices',
	];

	public const MIN_DURATION_MINUTES = 1;
	public const MAX_DURATION_MINUTES = 1440;
	public const MAX_PASSWORD_LENGTH = 10;

	/**
	 * Normalize / sanitize a meeting fields array before persistence.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public static function normalize( array $fields ): array {
		$normalized = [];

		foreach ( $fields as $key => $value ) {
			switch ( $key ) {
				case 'type':
				case 'jbh_time':
					$normalized[ $key ] = is_numeric( $value ) ? (int) $value : (int) ( $value ?? 0 );
					break;
				case 'duration':
					$normalized[ $key ] = is_numeric( $value ) ? max( self::MIN_DURATION_MINUTES, (int) $value ) : self::MIN_DURATION_MINUTES;
					break;
				case 'alternative_hosts':
					$normalized[ $key ] = is_array( $value ) ? array_values( array_filter( array_map( 'sanitize_text_field', $value ) ) ) : [];
					break;
				case 'password':
					$normalized[ $key ] = (string) $value;
					break;
				case 'agenda':
					$normalized[ $key ] = sanitize_textarea_field( (string) $value );
					break;
				default:
					if ( in_array( $key, self::BOOLEAN_FIELDS, true ) ) {
						$normalized[ $key ] = self::toBooleanString( $value );
						break;
					}
					$normalized[ $key ] = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : $value;
			}
		}

		return $normalized;
	}

	/**
	 * Validate meeting fields.
	 *
	 * @param array $fields
	 *
	 * @return array Map of field key => human readable error message.
	 */
	public static function validate( array $fields ): array {
		$errors = [];

		$user_id = $fields['user_id'] ?? '';
		if ( empty( $user_id ) ) {
			$errors['user_id'] = __( 'Meeting Host is required to create a meeting.', 'video-conferencing-with-zoom-api' );
		}

		$type = $fields['type'] ?? null;
		if ( ! in_array( (int) $type, [ 1, 2 ], true ) ) {
			$errors['type'] = __( 'Please select a valid Meeting Type.', 'video-conferencing-with-zoom-api' );
		}

		$start_time = $fields['start_time'] ?? '';
		if ( empty( $start_time ) ) {
			$errors['start_time'] = __( 'Start Date/Time is required.', 'video-conferencing-with-zoom-api' );
		} elseif ( self::isValidDateTime( $start_time ) === false ) {
			$errors['start_time'] = __( 'Start Date/Time must be a valid date and time.', 'video-conferencing-with-zoom-api' );
		}

		$timezone = $fields['timezone'] ?? '';
		if ( empty( $timezone ) ) {
			$errors['timezone'] = __( 'Timezone is required.', 'video-conferencing-with-zoom-api' );
		} elseif ( ! in_array( $timezone, DateTimeZone::listIdentifiers(), true ) ) {
			$errors['timezone'] = __( 'Please select a valid timezone.', 'video-conferencing-with-zoom-api' );
		}

		$duration = isset( $fields['duration'] ) ? (int) $fields['duration'] : 0;
		if ( $duration < self::MIN_DURATION_MINUTES || $duration > self::MAX_DURATION_MINUTES ) {
			$errors['duration'] = sprintf(
			/* translators: %1$d: min duration, %2$d: max duration */
				__( 'Meeting duration must be between %1$d and %2$d minutes.', 'video-conferencing-with-zoom-api' ),
				self::MIN_DURATION_MINUTES,
				self::MAX_DURATION_MINUTES
			);
		}

		$password = $fields['password'] ?? '';
		if ( ! empty( $password ) && mb_strlen( $password ) > self::MAX_PASSWORD_LENGTH ) {
			$errors['password'] = sprintf(
			/* translators: %d: max length */
				__( 'Password must be a maximum of %d characters.', 'video-conferencing-with-zoom-api' ),
				self::MAX_PASSWORD_LENGTH
			);
		}

		return $errors;
	}

	/**
	 * Loose datetime validity check (accepts ISO8601 variants + strtotime strings).
	 *
	 * @param string $value
	 *
	 * @return bool
	 */
	public static function isValidDateTime( string $value ): bool {
		if ( strtotime( $value ) === false ) {
			return false;
		}

		try {
			new DateTimeImmutable( $value );
		} catch ( \Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Normalize classic boolean-ish values ('yes','on','1',1,true,'true' => '1').
	 *
	 * @param mixed $value
	 *
	 * @return string|null
	 */
	private static function toBooleanString( $value ): ?string {
		if ( is_null( $value ) || $value === '' || $value === false || $value === 0 || $value === '0' || $value === 'no' || $value === 'off' ) {
			return null;
		}

		return ( in_array( $value, [ 'yes', 'on', '1', 1, true, 'true' ], true ) ) ? '1' : null;
	}
}