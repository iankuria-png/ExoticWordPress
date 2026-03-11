<?php

if (!defined('ABSPATH')) {
	exit;
}

final class Exotic_Age_Gate_Settings
{
	public const OPTION_KEY = 'exotic_age_gate_settings';
	private const GROUP_KEY = 'exotic_age_gate';
	private const PAGE_SLUG = 'exotic-age-gate';

	public static function register(): void
	{
		add_action('admin_menu', array(__CLASS__, 'add_menu'));
		add_action('admin_init', array(__CLASS__, 'register_settings'));
		add_action('admin_init', array(__CLASS__, 'ensure_bootstrap_settings'));
		add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
	}

	public static function settings_capability(): string
	{
		$capability = apply_filters('exotic_age_gate_settings_capability', 'manage_options');
		return is_string($capability) && $capability !== '' ? $capability : 'manage_options';
	}

	public static function settings_page_url(): string
	{
		return admin_url('options-general.php?page=' . self::PAGE_SLUG);
	}

	public static function add_menu(): void
	{
		add_options_page(
			__('Exotic Age Gate', 'exotic-age-gate'),
			__('Exotic Age Gate', 'exotic-age-gate'),
			self::settings_capability(),
			self::PAGE_SLUG,
			array(__CLASS__, 'render_page')
		);
	}

	public static function enqueue_admin_assets(string $hook): void
	{
		if ($hook !== 'settings_page_' . self::PAGE_SLUG) {
			return;
		}

		$css_file = EXOTIC_AGE_GATE_PATH . 'assets/css/admin.css';
		$js_file = EXOTIC_AGE_GATE_PATH . 'assets/js/admin.js';

		wp_enqueue_media();
		wp_enqueue_style(
			'exotic-age-gate-admin',
			EXOTIC_AGE_GATE_URL . 'assets/css/admin.css',
			array(),
			file_exists($css_file) ? filemtime($css_file) : EXOTIC_AGE_GATE_VERSION
		);
		wp_enqueue_script(
			'exotic-age-gate-admin',
			EXOTIC_AGE_GATE_URL . 'assets/js/admin.js',
			array(),
			file_exists($js_file) ? filemtime($js_file) : EXOTIC_AGE_GATE_VERSION,
			true
		);
		wp_localize_script(
			'exotic-age-gate-admin',
			'exoticAgeGateAdmin',
			array(
				'frameTitle' => __('Choose logo', 'exotic-age-gate'),
				'frameButton' => __('Use this logo', 'exotic-age-gate'),
				'emptyLabel' => __('No logo selected.', 'exotic-age-gate'),
			)
		);
	}

	public static function register_settings(): void
	{
		register_setting(
			self::GROUP_KEY,
			self::OPTION_KEY,
			array(
				'type' => 'array',
				'sanitize_callback' => array(__CLASS__, 'sanitize_settings'),
				'default' => self::default_settings(),
			)
		);

		add_settings_section(
			'eag_general',
			__('General settings', 'exotic-age-gate'),
			array(__CLASS__, 'render_general_intro'),
			self::PAGE_SLUG
		);

		add_settings_field('enabled', __('Enable age gate', 'exotic-age-gate'), array(__CLASS__, 'render_enabled_field'), self::PAGE_SLUG, 'eag_general');
		add_settings_field('coverage_mode', __('Coverage', 'exotic-age-gate'), array(__CLASS__, 'render_coverage_mode_field'), self::PAGE_SLUG, 'eag_general');
		add_settings_field('include_paths', __('Include paths', 'exotic-age-gate'), array(__CLASS__, 'render_include_paths_field'), self::PAGE_SLUG, 'eag_general');
		add_settings_field('exclude_paths', __('Exclude paths', 'exotic-age-gate'), array(__CLASS__, 'render_exclude_paths_field'), self::PAGE_SLUG, 'eag_general');
		add_settings_field('cookie_days', __('Cookie lifetime (days)', 'exotic-age-gate'), array(__CLASS__, 'render_cookie_days_field'), self::PAGE_SLUG, 'eag_general');
		add_settings_field('safe_exit_url', __('Safe exit URL', 'exotic-age-gate'), array(__CLASS__, 'render_safe_exit_url_field'), self::PAGE_SLUG, 'eag_general');

		add_settings_section(
			'eag_content',
			__('Modal content', 'exotic-age-gate'),
			array(__CLASS__, 'render_content_intro'),
			self::PAGE_SLUG
		);

		add_settings_field('show_logo', __('Logo visibility', 'exotic-age-gate'), array(__CLASS__, 'render_show_logo_field'), self::PAGE_SLUG, 'eag_content');
		add_settings_field('logo_url', __('Logo', 'exotic-age-gate'), array(__CLASS__, 'render_logo_url_field'), self::PAGE_SLUG, 'eag_content');
		add_settings_field('eyebrow', __('Eyebrow', 'exotic-age-gate'), array(__CLASS__, 'render_eyebrow_field'), self::PAGE_SLUG, 'eag_content');
		add_settings_field('title', __('Title', 'exotic-age-gate'), array(__CLASS__, 'render_title_field'), self::PAGE_SLUG, 'eag_content');
		add_settings_field('description', __('Description', 'exotic-age-gate'), array(__CLASS__, 'render_description_field'), self::PAGE_SLUG, 'eag_content');
		add_settings_field('details', __('Details', 'exotic-age-gate'), array(__CLASS__, 'render_details_field'), self::PAGE_SLUG, 'eag_content');
		add_settings_field('accept_label', __('Accept button label', 'exotic-age-gate'), array(__CLASS__, 'render_accept_label_field'), self::PAGE_SLUG, 'eag_content');
		add_settings_field('leave_label', __('Leave button label', 'exotic-age-gate'), array(__CLASS__, 'render_leave_label_field'), self::PAGE_SLUG, 'eag_content');
		add_settings_field('accent_color', __('Accent color', 'exotic-age-gate'), array(__CLASS__, 'render_accent_color_field'), self::PAGE_SLUG, 'eag_content');
	}

	/**
	 * @param mixed $input
	 * @return array<string, mixed>
	 */
	public static function sanitize_settings($input): array
	{
		$defaults = self::default_settings();
		$sanitized = $defaults;

		if (!is_array($input)) {
			return $sanitized;
		}

		$sanitized['enabled'] = !empty($input['enabled']) ? 1 : 0;

		$coverage_mode = sanitize_key((string) ($input['coverage_mode'] ?? $defaults['coverage_mode']));
		$sanitized['coverage_mode'] = in_array($coverage_mode, array('homepage_only', 'all_public', 'custom'), true)
			? $coverage_mode
			: $defaults['coverage_mode'];

		$sanitized['include_paths'] = self::sanitize_paths($input['include_paths'] ?? array());
		$sanitized['exclude_paths'] = self::sanitize_paths($input['exclude_paths'] ?? array());

		$cookie_days = (int) ($input['cookie_days'] ?? $defaults['cookie_days']);
		$sanitized['cookie_days'] = min(365, max(1, $cookie_days));

		$cookie_name = sanitize_key((string) ($input['cookie_name'] ?? $defaults['cookie_name']));
		$sanitized['cookie_name'] = $cookie_name !== '' ? $cookie_name : 'tos18';

		$safe_exit_url = esc_url_raw((string) ($input['safe_exit_url'] ?? ''));
		$sanitized['safe_exit_url'] = $safe_exit_url !== '' ? $safe_exit_url : $defaults['safe_exit_url'];

		$sanitized['show_logo'] = !empty($input['show_logo']) ? 1 : 0;

		$logo_url = esc_url_raw((string) ($input['logo_url'] ?? ''));
		$sanitized['logo_url'] = $logo_url !== '' ? $logo_url : '';

		$sanitized['eyebrow'] = sanitize_text_field((string) ($input['eyebrow'] ?? $defaults['eyebrow']));
		$sanitized['title'] = sanitize_text_field((string) ($input['title'] ?? $defaults['title']));
		$sanitized['description'] = sanitize_textarea_field((string) ($input['description'] ?? $defaults['description']));
		$sanitized['details'] = sanitize_textarea_field((string) ($input['details'] ?? $defaults['details']));
		$sanitized['accept_label'] = sanitize_text_field((string) ($input['accept_label'] ?? $defaults['accept_label']));
		$sanitized['leave_label'] = sanitize_text_field((string) ($input['leave_label'] ?? $defaults['leave_label']));

		$accent_color = sanitize_text_field((string) ($input['accent_color'] ?? $defaults['accent_color']));
		$sanitized['accent_color'] = self::sanitize_hex_color($accent_color, (string) $defaults['accent_color']);

		$sanitized['preset_key'] = sanitize_key((string) ($input['preset_key'] ?? $defaults['preset_key']));

		return $sanitized;
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_settings(): array
	{
		$defaults = self::default_settings();
		$settings = get_option(self::OPTION_KEY, array());

		if (!is_array($settings)) {
			$settings = array();
		}

		$settings = array_merge($defaults, $settings);
		$settings['include_paths'] = self::sanitize_paths($settings['include_paths'] ?? array());
		$settings['exclude_paths'] = self::sanitize_paths($settings['exclude_paths'] ?? array());
		$settings['cookie_days'] = min(365, max(1, (int) ($settings['cookie_days'] ?? 60)));
		$settings['cookie_name'] = sanitize_key((string) ($settings['cookie_name'] ?? 'tos18'));
		$settings['show_logo'] = !empty($settings['show_logo']) ? 1 : 0;
		$settings['accent_color'] = self::sanitize_hex_color((string) ($settings['accent_color'] ?? '#B31234'), '#B31234');

		return apply_filters('exotic_age_gate_settings', $settings);
	}

	public static function ensure_bootstrap_settings(): void
	{
		$defaults = self::default_settings();
		$current = get_option(self::OPTION_KEY, null);

		if (!is_array($current)) {
			update_option(self::OPTION_KEY, $defaults);
			return;
		}

		$merged = array_merge($defaults, $current);
		if ($merged !== $current) {
			update_option(self::OPTION_KEY, $merged);
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function default_settings(): array
	{
		$host = wp_parse_url(home_url('/'), PHP_URL_HOST);
		$host = is_string($host) ? strtolower(trim($host)) : '';
		$preset_key = Exotic_Age_Gate_Site_Presets::detect_preset_key($host);
		$preset = Exotic_Age_Gate_Site_Presets::for_host($host);
		$logo_url = (string) get_option('sitelogo');

		return array(
			'enabled' => get_option('tos18') == '1' ? 1 : 0,
			'preset_key' => $preset_key,
			'coverage_mode' => 'all_public',
			'include_paths' => array(),
			'exclude_paths' => array(),
			'cookie_name' => 'tos18',
			'cookie_days' => 60,
			'safe_exit_url' => (string) ($preset['safe_exit_url'] ?? 'https://www.google.com/'),
			'show_logo' => 0,
			'logo_url' => $logo_url !== '' ? esc_url_raw($logo_url) : '',
			'eyebrow' => (string) ($preset['eyebrow'] ?? __('Adults only', 'exotic-age-gate')),
			'title' => (string) ($preset['title'] ?? __('Age verification', 'exotic-age-gate')),
			'description' => (string) ($preset['description'] ?? __('This website may contain nudity and sexuality, and is intended for a mature audience.', 'exotic-age-gate')),
			'details' => (string) ($preset['details'] ?? __('You must be 18 or older to enter.', 'exotic-age-gate')),
			'accept_label' => (string) ($preset['accept_label'] ?? __('I\'m 18 or older', 'exotic-age-gate')),
			'leave_label' => (string) ($preset['leave_label'] ?? __('Leave', 'exotic-age-gate')),
			'accent_color' => (string) ($preset['accent_color'] ?? '#B31234'),
		);
	}

	public static function render_page(): void
	{
		if (!current_user_can(self::settings_capability())) {
			return;
		}

		$settings = self::get_settings();
		$theme_patch_ready = Exotic_Age_Gate_Frontend::theme_patch_present();

		echo '<div class="wrap eag-settings-page">';
		echo '<h1>' . esc_html__('Exotic Age Gate', 'exotic-age-gate') . '</h1>';

		if (!$theme_patch_ready) {
			echo '<div class="notice notice-warning"><p>';
			echo esc_html__('Theme handoff patch not detected. The plugin will stay dormant until the shared EscortWP footer patch is deployed.', 'exotic-age-gate');
			echo '</p></div>';
		}

		echo '<p>' . esc_html__('This plugin replaces the legacy theme age gate only when the theme handoff patch is available and the plugin is enabled.', 'exotic-age-gate') . '</p>';
		echo '<form method="post" action="options.php">';
		settings_fields(self::GROUP_KEY);
		echo '<input type="hidden" name="' . esc_attr(self::OPTION_KEY) . '[preset_key]" value="' . esc_attr((string) $settings['preset_key']) . '" />';
		do_settings_sections(self::PAGE_SLUG);
		submit_button();
		echo '</form>';
		echo '</div>';
	}

	public static function render_general_intro(): void
	{
		echo '<p>' . esc_html__('Keep rollout safe by using the same tos18 cookie and an all-public default coverage that mirrors the legacy gate.', 'exotic-age-gate') . '</p>';
	}

	public static function render_content_intro(): void
	{
		echo '<p>' . esc_html__('Use simple structured copy so each site can override branding without editing templates.', 'exotic-age-gate') . '</p>';
	}

	public static function render_enabled_field(): void
	{
		$settings = self::get_settings();
		echo '<label><input type="checkbox" name="' . esc_attr(self::OPTION_KEY) . '[enabled]" value="1" ' . checked((int) $settings['enabled'], 1, false) . ' /> ';
		echo esc_html__('Enable plugin takeover for the age gate.', 'exotic-age-gate') . '</label>';
	}

	public static function render_coverage_mode_field(): void
	{
		$settings = self::get_settings();
		$options = array(
			'all_public' => __('All public pages (legacy default)', 'exotic-age-gate'),
			'homepage_only' => __('Homepage only', 'exotic-age-gate'),
			'custom' => __('Custom include/exclude paths', 'exotic-age-gate'),
		);

		echo '<select name="' . esc_attr(self::OPTION_KEY) . '[coverage_mode]">';
		foreach ($options as $value => $label) {
			echo '<option value="' . esc_attr($value) . '" ' . selected((string) $settings['coverage_mode'], $value, false) . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Custom mode uses path prefixes such as /, /escorts/, or /city/addis-ababa/.', 'exotic-age-gate') . '</p>';
	}

	public static function render_include_paths_field(): void
	{
		$settings = self::get_settings();
		echo '<textarea name="' . esc_attr(self::OPTION_KEY) . '[include_paths]" rows="4" cols="60">' . esc_textarea(implode("\n", (array) $settings['include_paths'])) . '</textarea>';
		echo '<p class="description">' . esc_html__('One path prefix per line. Used only in Custom coverage mode.', 'exotic-age-gate') . '</p>';
	}

	public static function render_exclude_paths_field(): void
	{
		$settings = self::get_settings();
		echo '<textarea name="' . esc_attr(self::OPTION_KEY) . '[exclude_paths]" rows="4" cols="60">' . esc_textarea(implode("\n", (array) $settings['exclude_paths'])) . '</textarea>';
		echo '<p class="description">' . esc_html__('One path prefix per line. Excludes always win.', 'exotic-age-gate') . '</p>';
	}

	public static function render_cookie_days_field(): void
	{
		$settings = self::get_settings();
		echo '<input type="number" min="1" max="365" name="' . esc_attr(self::OPTION_KEY) . '[cookie_days]" value="' . esc_attr((string) $settings['cookie_days']) . '" />';
	}

	public static function render_safe_exit_url_field(): void
	{
		$settings = self::get_settings();
		echo '<input type="url" class="regular-text" name="' . esc_attr(self::OPTION_KEY) . '[safe_exit_url]" value="' . esc_attr((string) $settings['safe_exit_url']) . '" />';
	}

	public static function render_show_logo_field(): void
	{
		$settings = self::get_settings();
		echo '<label><input type="checkbox" name="' . esc_attr(self::OPTION_KEY) . '[show_logo]" value="1" ' . checked((int) $settings['show_logo'], 1, false) . ' /> ';
		echo esc_html__('Show the selected logo in the modal.', 'exotic-age-gate') . '</label>';
		echo '<p class="description">' . esc_html__('Hidden by default so the modal can start as a cleaner, text-first experience.', 'exotic-age-gate') . '</p>';
	}

	public static function render_logo_url_field(): void
	{
		$settings = self::get_settings();
		$logo_url = (string) $settings['logo_url'];
		$has_logo = $logo_url !== '';

		echo '<div class="eag-media-field" data-age-gate-logo-field>';
		echo '<input type="hidden" name="' . esc_attr(self::OPTION_KEY) . '[logo_url]" value="' . esc_attr($logo_url) . '" data-age-gate-logo-input />';
		echo '<div class="eag-media-field__preview' . ($has_logo ? '' : ' is-empty') . '" data-age-gate-logo-preview>';
		if ($has_logo) {
			echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr__('Selected logo preview', 'exotic-age-gate') . '" data-age-gate-logo-image />';
		} else {
			echo '<span data-age-gate-logo-empty>' . esc_html__('No logo selected.', 'exotic-age-gate') . '</span>';
		}
		echo '</div>';
		echo '<div class="eag-media-field__actions">';
		echo '<button type="button" class="button button-secondary" data-age-gate-media-select>' . esc_html__('Select logo', 'exotic-age-gate') . '</button>';
		echo '<button type="button" class="button-link-delete" data-age-gate-media-remove' . ($has_logo ? '' : ' hidden') . '>' . esc_html__('Remove', 'exotic-age-gate') . '</button>';
		echo '</div>';
		echo '<p class="description">' . esc_html__('Pick an asset from the WordPress media library. Leave it empty if you want the modal to run without branding.', 'exotic-age-gate') . '</p>';
		echo '</div>';
	}

	public static function render_eyebrow_field(): void
	{
		$settings = self::get_settings();
		echo '<input type="text" class="regular-text" name="' . esc_attr(self::OPTION_KEY) . '[eyebrow]" value="' . esc_attr((string) $settings['eyebrow']) . '" />';
	}

	public static function render_title_field(): void
	{
		$settings = self::get_settings();
		echo '<input type="text" class="regular-text" name="' . esc_attr(self::OPTION_KEY) . '[title]" value="' . esc_attr((string) $settings['title']) . '" />';
	}

	public static function render_description_field(): void
	{
		$settings = self::get_settings();
		echo '<textarea name="' . esc_attr(self::OPTION_KEY) . '[description]" rows="3" cols="60">' . esc_textarea((string) $settings['description']) . '</textarea>';
	}

	public static function render_details_field(): void
	{
		$settings = self::get_settings();
		echo '<textarea name="' . esc_attr(self::OPTION_KEY) . '[details]" rows="3" cols="60">' . esc_textarea((string) $settings['details']) . '</textarea>';
	}

	public static function render_accept_label_field(): void
	{
		$settings = self::get_settings();
		echo '<input type="text" class="regular-text" name="' . esc_attr(self::OPTION_KEY) . '[accept_label]" value="' . esc_attr((string) $settings['accept_label']) . '" />';
	}

	public static function render_leave_label_field(): void
	{
		$settings = self::get_settings();
		echo '<input type="text" class="regular-text" name="' . esc_attr(self::OPTION_KEY) . '[leave_label]" value="' . esc_attr((string) $settings['leave_label']) . '" />';
	}

	public static function render_accent_color_field(): void
	{
		$settings = self::get_settings();
		echo '<input type="text" class="regular-text" name="' . esc_attr(self::OPTION_KEY) . '[accent_color]" value="' . esc_attr((string) $settings['accent_color']) . '" />';
		echo '<p class="description">' . esc_html__('Hex color only. Example: #B31234', 'exotic-age-gate') . '</p>';
	}

	/**
	 * @param mixed $paths
	 * @return array<int, string>
	 */
	private static function sanitize_paths($paths): array
	{
		if (is_string($paths)) {
			$paths = preg_split('/\r\n|\r|\n/', $paths);
		}

		if (!is_array($paths)) {
			return array();
		}

		$normalized = array();
		foreach ($paths as $path) {
			$path = trim((string) $path);
			if ($path === '') {
				continue;
			}

			$parsed = wp_parse_url($path, PHP_URL_PATH);
			if (is_string($parsed) && $parsed !== '') {
				$path = $parsed;
			}

			if ($path[0] !== '/') {
				$path = '/' . $path;
			}

			$path = untrailingslashit($path);
			if ($path === '') {
				$path = '/';
			}

			$normalized[] = $path;
		}

		return array_values(array_unique($normalized));
	}

	private static function sanitize_hex_color(string $color, string $fallback): string
	{
		$sanitized = sanitize_hex_color($color);
		return is_string($sanitized) && $sanitized !== '' ? $sanitized : $fallback;
	}
}
