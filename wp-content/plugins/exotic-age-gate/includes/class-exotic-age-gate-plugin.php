<?php

if (!defined('ABSPATH')) {
	exit;
}

final class Exotic_Age_Gate_Plugin
{
	public static function init(): void
	{
		add_action('plugins_loaded', array(__CLASS__, 'load_textdomain'));
		add_filter('plugin_action_links_' . plugin_basename(EXOTIC_AGE_GATE_PATH . 'exotic-age-gate.php'), array(__CLASS__, 'plugin_action_links'));

		if (is_admin()) {
			Exotic_Age_Gate_Settings::register();
			add_action('admin_notices', array(__CLASS__, 'render_admin_notice'));
		}

		Exotic_Age_Gate_Frontend::register();
	}

	public static function activate(): void
	{
		Exotic_Age_Gate_Settings::ensure_bootstrap_settings();
	}

	public static function deactivate(): void
	{
	}

	public static function load_textdomain(): void
	{
		load_plugin_textdomain('exotic-age-gate', false, dirname(plugin_basename(EXOTIC_AGE_GATE_PATH . 'exotic-age-gate.php')) . '/languages');
	}

	/**
	 * @param array<int, string> $links
	 * @return array<int, string>
	 */
	public static function plugin_action_links(array $links): array
	{
		if (!current_user_can(Exotic_Age_Gate_Settings::settings_capability())) {
			return $links;
		}

		array_unshift(
			$links,
			'<a href="' . esc_url(Exotic_Age_Gate_Settings::settings_page_url()) . '">' . esc_html__('Settings', 'exotic-age-gate') . '</a>'
		);

		return $links;
	}

	public static function render_admin_notice(): void
	{
		if (!current_user_can(Exotic_Age_Gate_Settings::settings_capability())) {
			return;
		}

		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		$settings = Exotic_Age_Gate_Settings::get_settings();
		if (empty($settings['enabled']) || Exotic_Age_Gate_Frontend::theme_patch_present()) {
			return;
		}

		if ($screen && strpos((string) $screen->id, 'exotic-age-gate') === false && strpos((string) $screen->id, 'plugins') === false) {
			return;
		}

		echo '<div class="notice notice-warning"><p>';
		echo esc_html__('Exotic Age Gate is enabled, but the required EscortWP theme handoff patch was not detected. Deploy the shared theme patch before enabling takeover on a site.', 'exotic-age-gate');
		echo '</p><p>';
		echo esc_html__('Required patch: the theme must route its legacy age-gate condition through escortwp_age_gate_should_render() in the parent and child footers.', 'exotic-age-gate');
		echo '</p></div>';
	}
}
