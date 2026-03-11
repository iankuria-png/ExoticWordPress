<?php

if (!defined('ABSPATH')) {
	exit;
}

final class Exotic_Age_Gate_Site_Presets
{
	/**
	 * @return array<string, array<string, string>>
	 */
	public static function all(): array
	{
		return array(
			'fallback' => array(
				'eyebrow' => __('Adults only', 'exotic-age-gate'),
				'title' => __('Age verification', 'exotic-age-gate'),
				'description' => __('This website may contain nudity and sexuality, and is intended for a mature audience.', 'exotic-age-gate'),
				'details' => __('You must be 18 or older to enter.', 'exotic-age-gate'),
				'accept_label' => __('I\'m 18 or older', 'exotic-age-gate'),
				'leave_label' => __('Leave', 'exotic-age-gate'),
				'accent_color' => '#B31234',
				'safe_exit_url' => 'https://www.google.com/',
			),
			'kenya' => array(
				'eyebrow' => __('Adults only', 'exotic-age-gate'),
				'title' => __('Age verification', 'exotic-age-gate'),
				'description' => __('This website may contain nudity and sexuality, and is intended for a mature audience.', 'exotic-age-gate'),
				'details' => __('You must be 18 or older to enter.', 'exotic-age-gate'),
				'accept_label' => __('I\'m 18 or older', 'exotic-age-gate'),
				'leave_label' => __('Leave', 'exotic-age-gate'),
				'accent_color' => '#B31234',
				'safe_exit_url' => 'https://www.google.com/',
			),
			'ethiopia' => array(
				'eyebrow' => __('Adults only', 'exotic-age-gate'),
				'title' => __('Age verification', 'exotic-age-gate'),
				'description' => __('This website may contain nudity and sexuality, and is intended for a mature audience.', 'exotic-age-gate'),
				'details' => __('You must be 18 or older to enter.', 'exotic-age-gate'),
				'accept_label' => __('I\'m 18 or older', 'exotic-age-gate'),
				'leave_label' => __('Leave', 'exotic-age-gate'),
				'accent_color' => '#B31234',
				'safe_exit_url' => 'https://www.google.com/',
			),
			'tanzania' => array(
				'eyebrow' => __('Adults only', 'exotic-age-gate'),
				'title' => __('Age verification', 'exotic-age-gate'),
				'description' => __('This website may contain nudity and sexuality, and is intended for a mature audience.', 'exotic-age-gate'),
				'details' => __('You must be 18 or older to enter.', 'exotic-age-gate'),
				'accept_label' => __('I\'m 18 or older', 'exotic-age-gate'),
				'leave_label' => __('Leave', 'exotic-age-gate'),
				'accent_color' => '#B31234',
				'safe_exit_url' => 'https://www.google.com/',
			),
			'ghana' => array(
				'eyebrow' => __('Adults only', 'exotic-age-gate'),
				'title' => __('Age verification', 'exotic-age-gate'),
				'description' => __('This website may contain nudity and sexuality, and is intended for a mature audience.', 'exotic-age-gate'),
				'details' => __('You must be 18 or older to enter.', 'exotic-age-gate'),
				'accept_label' => __('I\'m 18 or older', 'exotic-age-gate'),
				'leave_label' => __('Leave', 'exotic-age-gate'),
				'accent_color' => '#B31234',
				'safe_exit_url' => 'https://www.google.com/',
			),
		);
	}

	public static function detect_preset_key(string $host): string
	{
		$host = strtolower(trim($host));

		if ($host === '') {
			return 'fallback';
		}

		$map = array(
			'exotic.local' => 'kenya',
			'exotickenya.com' => 'kenya',
			'www.exotickenya.com' => 'kenya',
			'exoticethiopia.com' => 'ethiopia',
			'www.exoticethiopia.com' => 'ethiopia',
			'exotictz.com' => 'tanzania',
			'www.exotictz.com' => 'tanzania',
			'exoticghana.com' => 'ghana',
			'www.exoticghana.com' => 'ghana',
		);

		if (isset($map[$host])) {
			return $map[$host];
		}

		foreach ($map as $domain => $preset) {
			if ($domain !== '' && strpos($host, $domain) !== false) {
				return $preset;
			}
		}

		return 'fallback';
	}

	/**
	 * @return array<string, string>
	 */
	public static function for_host(string $host): array
	{
		$all = self::all();
		$preset_key = self::detect_preset_key($host);

		if (!isset($all[$preset_key])) {
			$preset_key = 'fallback';
		}

		return $all[$preset_key];
	}
}
