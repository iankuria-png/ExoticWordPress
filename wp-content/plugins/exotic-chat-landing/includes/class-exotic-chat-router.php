<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Exotic_Chat_Router
{
    public const QUERY_VAR = 'exotic_chat_landing';
    public const OPTION_KEY = 'exotic_chat_landing_settings';

    public static function register_rewrite(): void
    {
        $slug = self::get_route_slug();
        $slug_regex = trim($slug, '/');
        if ($slug_regex === '') {
            $slug_regex = 'chat';
        }

        add_rewrite_rule('^' . preg_quote($slug_regex, '#') . '/?$', 'index.php?' . self::QUERY_VAR . '=1', 'top');
    }

    /**
     * @param array<int, string> $vars
     * @return array<int, string>
     */
    public static function register_query_vars(array $vars): array
    {
        $vars[] = self::QUERY_VAR;
        return $vars;
    }

    public static function maybe_render_template(): void
    {
        if (!self::is_chat_route()) {
            return;
        }

        $settings = self::get_settings();
        if (empty($settings['enabled'])) {
            return;
        }

        self::enqueue_assets($settings);
        self::send_noindex_headers();

        global $wp_query;
        if ($wp_query instanceof WP_Query) {
            $wp_query->is_404 = false;
        }

        status_header(200);

        $chat_context = self::build_chat_context($settings);

        $template_path = EXOTIC_CHAT_LANDING_PATH . 'templates/chat-landing.php';
        if (file_exists($template_path)) {
            include $template_path;
            exit;
        }
    }

    public static function is_chat_route(): bool
    {
        return (string) get_query_var(self::QUERY_VAR, '') === '1';
    }

    public static function get_route_slug(): string
    {
        $settings = self::get_settings();
        $slug = isset($settings['route_slug']) ? sanitize_title((string) $settings['route_slug']) : 'chat';
        return $slug !== '' ? $slug : 'chat';
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private static function build_chat_context(array $settings): array
    {
        $host = self::get_request_host();
        $country_map = self::parse_country_map((string) ($settings['country_map_json'] ?? ''));
        $resolved = Exotic_Chat_Country_Registry::resolve_from_host(
            $host,
            $country_map,
            (int) ($settings['default_department_id'] ?? 0)
        );
        $country_code = isset($resolved['country_code']) ? (string) $resolved['country_code'] : '';
        $department_id = isset($resolved['department_id']) ? (int) $resolved['department_id'] : 0;

        $signed_department = self::resolve_signed_override_department($settings, $host);
        if ($signed_department !== null) {
            $department_id = $signed_department;
        }

        return [
            'chat_id' => self::resolve_chat_id($settings),
            'department_id' => max(0, $department_id),
            'country_code' => $country_code,
            'host' => $host,
        ];
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function resolve_signed_override_department(array $settings, string $host): ?int
    {
        if (empty($settings['allow_signed_override'])) {
            return null;
        }

        if (!isset($_GET['dept'], $_GET['exp'], $_GET['sig'])) {
            return null;
        }

        $department_id = max(0, (int) wp_unslash($_GET['dept']));
        if ($department_id < 1) {
            return null;
        }

        $exp = (int) wp_unslash($_GET['exp']);
        if ($exp < time()) {
            return null;
        }

        $signature = strtolower(trim((string) wp_unslash($_GET['sig'])));
        if ($signature === '') {
            return null;
        }

        $secret = defined('EXOTIC_CHAT_ROUTE_SECRET') ? (string) EXOTIC_CHAT_ROUTE_SECRET : '';
        if ($secret === '') {
            return null;
        }

        $payload = $department_id . '|' . $exp . '|' . $host;
        $expected = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expected, $signature)) {
            return null;
        }

        return $department_id;
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function resolve_chat_id(array $settings): string
    {
        $sbcloud_raw = get_option('sbcloud-settings', '');
        $sbcloud = [];

        if (is_string($sbcloud_raw) && $sbcloud_raw !== '') {
            $decoded = json_decode($sbcloud_raw, true);
            if (is_array($decoded)) {
                $sbcloud = $decoded;
            }
        } elseif (is_array($sbcloud_raw)) {
            $sbcloud = $sbcloud_raw;
        }

        if (!empty($sbcloud['chat-id'])) {
            $chat_id = preg_replace('/[^0-9]/', '', (string) $sbcloud['chat-id']);
            if (is_string($chat_id) && $chat_id !== '') {
                return $chat_id;
            }
        }

        $fallback = preg_replace('/[^0-9]/', '', (string) ($settings['fallback_chat_id'] ?? ''));
        if (is_string($fallback) && $fallback !== '') {
            return $fallback;
        }

        return '1369683147';
    }

    private static function get_request_host(): string
    {
        $host = isset($_SERVER['HTTP_HOST']) ? strtolower(trim((string) wp_unslash($_SERVER['HTTP_HOST']))) : '';
        if ($host === '') {
            return '';
        }

        $host = preg_replace('/:[0-9]+$/', '', $host);
        return is_string($host) ? $host : '';
    }

    /**
     * @return array<string, mixed>
     */
    private static function parse_country_map(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    private static function send_noindex_headers(): void
    {
        header('X-Robots-Tag: noindex, nofollow', true);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function enqueue_assets(array $settings): void
    {
        wp_enqueue_style(
            'exotic-chat-landing-style',
            EXOTIC_CHAT_LANDING_URL . 'assets/css/chat-landing.css',
            [],
            EXOTIC_CHAT_LANDING_VERSION
        );

        wp_enqueue_script(
            'exotic-chat-landing-script',
            EXOTIC_CHAT_LANDING_URL . 'assets/js/chat-landing.js',
            ['jquery'],
            EXOTIC_CHAT_LANDING_VERSION,
            true
        );

        wp_localize_script(
            'exotic-chat-landing-script',
            'exoticChatLandingConfig',
            [
                'autoOpenDelayMs' => max(0, (int) ($settings['auto_open_delay_ms'] ?? 1500)),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function get_settings(): array
    {
        if (class_exists('Exotic_Chat_Settings')) {
            return Exotic_Chat_Settings::get_settings();
        }

        $settings = get_option(self::OPTION_KEY, []);
        return is_array($settings) ? $settings : [];
    }
}
