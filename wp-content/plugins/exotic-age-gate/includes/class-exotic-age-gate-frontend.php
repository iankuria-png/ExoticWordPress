<?php

if (!defined('ABSPATH')) {
	exit;
}

final class Exotic_Age_Gate_Frontend
{
	private static $rendered = false;
	private static $theme_patch_ready = null;

	public static function register(): void
	{
		add_filter('escortwp_age_gate_should_render', array(__CLASS__, 'suppress_legacy_theme_gate'), 10, 2);
		add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
		add_action('wp_body_open', array(__CLASS__, 'render'));
		add_action('wp_footer', array(__CLASS__, 'render_footer_fallback'), 1);
	}

	public static function theme_patch_present(): bool
	{
		if (is_bool(self::$theme_patch_ready)) {
			return self::$theme_patch_ready;
		}

		if (!function_exists('escortwp_age_gate_should_render')) {
			self::$theme_patch_ready = false;
			return self::$theme_patch_ready;
		}

		$footer_template = self::active_footer_template_path();
		if ($footer_template === '' || !is_readable($footer_template)) {
			self::$theme_patch_ready = false;
			return self::$theme_patch_ready;
		}

		$contents = file_get_contents($footer_template);
		if (!is_string($contents)) {
			self::$theme_patch_ready = false;
			return self::$theme_patch_ready;
		}

		self::$theme_patch_ready = strpos($contents, 'escortwp_age_gate_should_render(') !== false;
		return self::$theme_patch_ready;
	}

	/**
	 * @param mixed $should_render
	 * @param mixed $context
	 */
	public static function suppress_legacy_theme_gate($should_render, $context = null): bool
	{
		if (!self::theme_patch_present()) {
			return (bool) $should_render;
		}

		if (!self::is_enabled()) {
			return (bool) $should_render;
		}

		return false;
	}

	public static function enqueue_assets(): void
	{
		if (!self::should_render_current_request()) {
			return;
		}

		$css_file = EXOTIC_AGE_GATE_PATH . 'assets/css/frontend.css';
		$js_file = EXOTIC_AGE_GATE_PATH . 'assets/js/frontend.js';

		wp_enqueue_style(
			'exotic-age-gate',
			EXOTIC_AGE_GATE_URL . 'assets/css/frontend.css',
			array(),
			file_exists($css_file) ? filemtime($css_file) : EXOTIC_AGE_GATE_VERSION
		);

		wp_enqueue_script(
			'exotic-age-gate',
			EXOTIC_AGE_GATE_URL . 'assets/js/frontend.js',
			array(),
			file_exists($js_file) ? filemtime($js_file) : EXOTIC_AGE_GATE_VERSION,
			true
		);

		$settings = Exotic_Age_Gate_Settings::get_settings();
		wp_add_inline_style(
			'exotic-age-gate',
			':root{--exotic-age-gate-accent:' . esc_attr((string) $settings['accent_color']) . ';}'
		);
		wp_localize_script(
			'exotic-age-gate',
			'exoticAgeGateConfig',
			array(
				'cookieName' => (string) $settings['cookie_name'],
				'cookieDays' => (int) $settings['cookie_days'],
				'exitUrl' => (string) $settings['safe_exit_url'],
			)
		);
	}

	public static function render(): void
	{
		if (self::$rendered || !self::should_render_current_request()) {
			return;
		}

		self::$rendered = true;
		$settings = Exotic_Age_Gate_Settings::get_settings();
		$site_name = get_bloginfo('name');
		$logo_url = (string) $settings['logo_url'];
		$show_logo = !empty($settings['show_logo']) && $logo_url !== '';
		?>
		<div class="exotic-age-gate" data-exotic-age-gate>
			<div class="exotic-age-gate__backdrop" aria-hidden="true"></div>
			<div class="exotic-age-gate__dialog" role="dialog" aria-modal="true" aria-labelledby="exotic-age-gate-title" aria-describedby="exotic-age-gate-description" tabindex="-1">
				<div class="exotic-age-gate__card<?php echo $show_logo ? '' : ' exotic-age-gate__card--no-logo'; ?>">
					<p class="exotic-age-gate__eyebrow">
						<span class="exotic-age-gate__badge" aria-hidden="true">
							<span class="exotic-age-gate__badge-number">18</span>
							<span class="exotic-age-gate__badge-plus">+</span>
						</span>
						<span><?php echo esc_html((string) $settings['eyebrow']); ?></span>
					</p>
					<?php if ($show_logo) : ?>
						<div class="exotic-age-gate__logo">
							<img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" />
						</div>
					<?php endif; ?>
					<h2 id="exotic-age-gate-title" class="exotic-age-gate__title"><?php echo esc_html((string) $settings['title']); ?></h2>
					<p id="exotic-age-gate-description" class="exotic-age-gate__description"><?php echo esc_html((string) $settings['description']); ?></p>
					<p class="exotic-age-gate__details"><?php echo esc_html((string) $settings['details']); ?></p>
					<div class="exotic-age-gate__actions">
						<button type="button" class="exotic-age-gate__button exotic-age-gate__button--primary" data-age-gate-accept><?php echo esc_html((string) $settings['accept_label']); ?></button>
						<button type="button" class="exotic-age-gate__button exotic-age-gate__button--secondary" data-age-gate-leave><?php echo esc_html((string) $settings['leave_label']); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function render_footer_fallback(): void
	{
		if (did_action('wp_body_open') > 0) {
			return;
		}

		self::render();
	}

	public static function is_enabled(): bool
	{
		$settings = Exotic_Age_Gate_Settings::get_settings();
		return !empty($settings['enabled']);
	}

	public static function should_render_current_request(): bool
	{
		if (!self::theme_patch_present() || !self::is_enabled()) {
			return false;
		}

		if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
			return false;
		}

		if ((defined('REST_REQUEST') && REST_REQUEST) || (function_exists('wp_is_json_request') && wp_is_json_request())) {
			return false;
		}

		if (is_feed() || is_embed() || (function_exists('is_customize_preview') && is_customize_preview())) {
			return false;
		}

		$settings = Exotic_Age_Gate_Settings::get_settings();
		$cookie_name = (string) ($settings['cookie_name'] ?? 'tos18');
		if ($cookie_name !== '' && isset($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name] === 'yes') {
			return false;
		}

		$path = self::current_path();
		$coverage_mode = (string) ($settings['coverage_mode'] ?? 'all_public');
		$include_paths = (array) ($settings['include_paths'] ?? array());
		$exclude_paths = (array) ($settings['exclude_paths'] ?? array());

		foreach ($exclude_paths as $exclude_path) {
			if (self::path_matches($path, (string) $exclude_path)) {
				return false;
			}
		}

		$should_render = false;
		if ($coverage_mode === 'all_public') {
			$should_render = true;
		} elseif ($coverage_mode === 'custom') {
			foreach ($include_paths as $include_path) {
				if (self::path_matches($path, (string) $include_path)) {
					$should_render = true;
					break;
				}
			}
		} else {
			$should_render = is_front_page() || (is_home() && !is_paged());
		}

		return (bool) apply_filters(
			'exotic_age_gate_should_render',
			$should_render,
			array(
				'path' => $path,
				'coverage_mode' => $coverage_mode,
			)
		);
	}

	private static function current_path(): string
	{
		$request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
		$path = parse_url('https://local.test' . $request_uri, PHP_URL_PATH);
		$path = is_string($path) ? $path : '/';
		$path = untrailingslashit($path);

		return $path === '' ? '/' : $path;
	}

	private static function active_footer_template_path(): string
	{
		$stylesheet_footer = trailingslashit(get_stylesheet_directory()) . 'footer.php';
		if (is_readable($stylesheet_footer)) {
			return $stylesheet_footer;
		}

		$template_footer = trailingslashit(get_template_directory()) . 'footer.php';
		if (is_readable($template_footer)) {
			return $template_footer;
		}

		return '';
	}

	private static function path_matches(string $current_path, string $candidate): bool
	{
		$candidate = untrailingslashit($candidate);
		$candidate = $candidate === '' ? '/' : $candidate;

		if ($candidate === '/') {
			return $current_path === '/';
		}

		return strpos($current_path, $candidate) === 0;
	}
}
