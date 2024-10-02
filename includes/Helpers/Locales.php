<?php

namespace Codemanas\VczApi\Helpers;

class Locales {

	public static function getSupportedTranslationsForWeb(): array {
		return [
			'en-US' => 'English',
			'de-DE' => 'German - Deutsch',
			'es-ES' => 'Spanish - Español',
			'fr-FR' => 'French - Français',
			'id-ID' => 'Indonesian - Bahasa Indonesia',
			'jp-JP' => 'Japanese - 日本語',
			'pt-PT' => 'Portuguese - Portuguese',
			'ru-RU' => 'Russian - Русский',
			'zh-CN' => 'Simplified Chinese - 简体中文',
			'zh-TW' => 'Traditional Chinese - 繁体中文',
			'ko-KO' => 'Korean - 한국어',
			'vi-VN' => 'Vietnamese - Tiếng Việt',
			'it-IT' => 'Italian - italiano',
			'nl-NL' => 'Dutch - Nederlands',
			'pl-PL' => 'Polish - Polska',
			'sv-SE' => 'Swedish - Svenska',
			'tr-TR' => 'Turkish - Türkçe'
		];
	}
}