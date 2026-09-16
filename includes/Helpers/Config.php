<?php

namespace Codemanas\VczApi\Helpers;

class Config {

	private static array $configs = [
		'post_type' => 'zoom-meetings'
	];

	public static function get( $key ): string {
		return self::$configs[ $key ] ?? "";
	}
}
