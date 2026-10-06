<?php

namespace Codemanas\VczApi\Admin\Foundation;

class Notification {

	private static ?Notification $instance = null;

	public static function get_instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private static string $message = '';
	private static string $messageType = 'error';
	private static bool $isDismissible = true;

	public static function setNotice( string $message, string $type = 'error', bool $dismissible = true ): void {
		self::$message       = $message;
		self::$messageType   = $type;
		self::$isDismissible = $dismissible;
	}

	public function displayNotices(): void {
		if ( empty( self::$message ) ) {
			return;
		}

		$message_classes = [
			'success' => 'notice-success',
			'error'   => 'notice-error',
			'warning' => 'notice-warning',
		];

		$class_type = $message_classes[ self::$messageType ] ?? 'notice-error';
		$classes    = sprintf( 'vczapi-notice notice %s %s', esc_attr( $class_type ), self::$isDismissible ? 'is-dismissible' : '' );

		printf( '<div class="%s"><p>%s</p></div>', $classes, wp_kses_post( self::$message ) );
	}
}