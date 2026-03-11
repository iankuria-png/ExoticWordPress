<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Exotic_Chat_Settings
{
    public const OPTION_KEY = 'exotic_chat_landing_settings';
    private const GROUP_KEY = 'exotic_chat_landing';
    private const PAGE_SLUG = 'exotic-chat-landing';
    private const MENU_SLUG = 'exotic-chat-landing';

    public static function register(): void
    {
        add_action('admin_menu', [__CLASS__, 'add_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('admin_init', [__CLASS__, 'ensure_bootstrap_settings']);
        add_action('update_option_' . self::OPTION_KEY, [__CLASS__, 'on_settings_updated'], 10, 2);
    }

    public static function add_menu(): void
    {
        $capability = self::settings_capability();

        add_menu_page(
            __('Exotic Chat Landing', 'exotic-chat-landing'),
            __('Exotic Chat', 'exotic-chat-landing'),
            $capability,
            self::MENU_SLUG,
            [__CLASS__, 'render_page']
        );

        add_submenu_page(
            self::MENU_SLUG,
            __('Exotic Chat Landing', 'exotic-chat-landing'),
            __('Settings', 'exotic-chat-landing'),
            $capability,
            self::MENU_SLUG,
            [__CLASS__, 'render_page']
        );

        add_options_page(
            __('Exotic Chat Landing', 'exotic-chat-landing'),
            __('Exotic Chat Landing', 'exotic-chat-landing'),
            $capability,
            self::PAGE_SLUG,
            [__CLASS__, 'render_page']
        );
    }

    public static function register_settings(): void
    {
        register_setting(
            self::GROUP_KEY,
            self::OPTION_KEY,
            [
                'type' => 'array',
                'sanitize_callback' => [__CLASS__, 'sanitize_settings'],
                'default' => self::default_settings(),
            ]
        );

        add_settings_section(
            'ecl_general',
            __('Route and Chat Settings', 'exotic-chat-landing'),
            [__CLASS__, 'render_general_intro'],
            self::PAGE_SLUG
        );

        add_settings_field('enabled', __('Enable plugin route', 'exotic-chat-landing'), [__CLASS__, 'render_enabled_field'], self::PAGE_SLUG, 'ecl_general');
        add_settings_field('route_slug', __('Route slug', 'exotic-chat-landing'), [__CLASS__, 'render_route_slug_field'], self::PAGE_SLUG, 'ecl_general');
        add_settings_field('fallback_chat_id', __('Fallback chat ID', 'exotic-chat-landing'), [__CLASS__, 'render_fallback_chat_id_field'], self::PAGE_SLUG, 'ecl_general');
        add_settings_field('default_department_id', __('Default department ID', 'exotic-chat-landing'), [__CLASS__, 'render_default_department_id_field'], self::PAGE_SLUG, 'ecl_general');
        add_settings_field('country_map_json', __('Country map JSON', 'exotic-chat-landing'), [__CLASS__, 'render_country_map_json_field'], self::PAGE_SLUG, 'ecl_general');
        add_settings_field('allow_signed_override', __('Allow signed override', 'exotic-chat-landing'), [__CLASS__, 'render_allow_signed_override_field'], self::PAGE_SLUG, 'ecl_general');
        add_settings_field('auto_open_delay_ms', __('Auto-open delay (ms)', 'exotic-chat-landing'), [__CLASS__, 'render_auto_open_delay_ms_field'], self::PAGE_SLUG, 'ecl_general');
    }

    public static function on_settings_updated($old_value, $new_value): void
    {
        if (!is_array($old_value)) {
            $old_value = [];
        }
        if (!is_array($new_value)) {
            $new_value = [];
        }

        $old_slug = isset($old_value['route_slug']) ? (string) $old_value['route_slug'] : 'chat';
        $new_slug = isset($new_value['route_slug']) ? (string) $new_value['route_slug'] : 'chat';

        if ($old_slug !== $new_slug) {
            Exotic_Chat_Router::register_rewrite();
            flush_rewrite_rules(false);
        }
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

        $route_slug = sanitize_title((string) ($input['route_slug'] ?? 'chat'));
        $sanitized['route_slug'] = $route_slug !== '' ? $route_slug : 'chat';

        $chat_id = preg_replace('/[^0-9]/', '', (string) ($input['fallback_chat_id'] ?? ''));
        $sanitized['fallback_chat_id'] = is_string($chat_id) ? substr($chat_id, 0, 32) : '';

        $department_id = (int) ($input['default_department_id'] ?? 0);
        $sanitized['default_department_id'] = max(0, $department_id);

        $country_map_raw = trim((string) ($input['country_map_json'] ?? ''));
        if ($country_map_raw === '') {
            $country_map_raw = $defaults['country_map_json'];
        }

        json_decode($country_map_raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            add_settings_error(self::OPTION_KEY, 'invalid-country-map', __('Country map JSON is invalid. Previous valid value kept.', 'exotic-chat-landing'));
            $existing = self::get_settings();
            $country_map_raw = (string) $existing['country_map_json'];
        }
        $sanitized['country_map_json'] = $country_map_raw;

        $sanitized['allow_signed_override'] = !empty($input['allow_signed_override']) ? 1 : 0;

        $delay = (int) ($input['auto_open_delay_ms'] ?? 1500);
        $sanitized['auto_open_delay_ms'] = min(15000, max(0, $delay));

        return $sanitized;
    }

    /**
     * @return array<string, mixed>
     */
    public static function get_settings(): array
    {
        $defaults = self::default_settings();
        $settings = get_option(self::OPTION_KEY, []);

        if (!is_array($settings)) {
            $settings = [];
        }

        return array_merge($defaults, $settings);
    }

    /**
     * @return array<string, mixed>
     */
    private static function default_settings(): array
    {
        return [
            'enabled' => 1,
            'route_slug' => 'chat',
            'fallback_chat_id' => '',
            'default_department_id' => 0,
            'country_map_json' => '{}',
            'allow_signed_override' => 1,
            'auto_open_delay_ms' => 1500,
        ];
    }

    public static function render_page(): void
    {
        if (!current_user_can(self::settings_capability())) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Exotic Chat Landing', 'exotic-chat-landing') . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields(self::GROUP_KEY);
        do_settings_sections(self::PAGE_SLUG);
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    public static function settings_capability(): string
    {
        $capability = apply_filters('exotic_chat_landing_settings_capability', 'activate_plugins');
        return is_string($capability) && $capability !== '' ? $capability : 'activate_plugins';
    }

    public static function settings_page_url(): string
    {
        return admin_url('admin.php?page=' . self::MENU_SLUG);
    }

    public static function ensure_bootstrap_settings(): void
    {
        $settings = self::get_settings();
        $host = wp_parse_url(home_url('/'), PHP_URL_HOST);
        $host = is_string($host) ? strtolower(trim($host)) : '';
        if ($host === '') {
            return;
        }

        $country_map = self::decode_country_map((string) ($settings['country_map_json'] ?? ''));
        $detected_country = Exotic_Chat_Country_Registry::detect_country_code_from_host($host);
        if ($detected_country === '') {
            return;
        }

        $updated = false;
        $default_department = max(0, (int) ($settings['default_department_id'] ?? 0));
        if ($default_department < 1) {
            $inferred_department = Exotic_Chat_Country_Registry::default_department_for_country($detected_country);
            if ($inferred_department > 0) {
                $settings['default_department_id'] = $inferred_department;
                $default_department = $inferred_department;
                $updated = true;
            }
        }

        $host_keys = [$host];
        if (strpos($host, 'www.') !== 0) {
            $host_keys[] = 'www.' . $host;
        }

        foreach ($host_keys as $host_key) {
            if (!isset($country_map[$host_key]) || !is_array($country_map[$host_key])) {
                $country_map[$host_key] = [
                    'country_code' => $detected_country,
                    'department_id' => $default_department,
                ];
                $updated = true;
                continue;
            }

            if (empty($country_map[$host_key]['country_code'])) {
                $country_map[$host_key]['country_code'] = $detected_country;
                $updated = true;
            }

            if (empty($country_map[$host_key]['department_id']) && $default_department > 0) {
                $country_map[$host_key]['department_id'] = $default_department;
                $updated = true;
            }
        }

        if (!$updated) {
            return;
        }

        $settings['country_map_json'] = self::encode_country_map($country_map);
        update_option(self::OPTION_KEY, $settings);
    }

    public static function render_general_intro(): void
    {
        echo '<p>' . esc_html__('Configure the /chat landing route and Support Board Cloud defaults.', 'exotic-chat-landing') . '</p>';
    }

    public static function render_enabled_field(): void
    {
        $settings = self::get_settings();
        printf(
            '<label><input type="checkbox" name="%1$s[enabled]" value="1" %2$s /> %3$s</label>',
            esc_attr(self::OPTION_KEY),
            checked((int) $settings['enabled'], 1, false),
            esc_html__('Serve the chat landing route.', 'exotic-chat-landing')
        );
    }

    public static function render_route_slug_field(): void
    {
        $settings = self::get_settings();
        printf(
            '<input type="text" class="regular-text" name="%1$s[route_slug]" value="%2$s" />',
            esc_attr(self::OPTION_KEY),
            esc_attr((string) $settings['route_slug'])
        );
        echo '<p class="description">' . esc_html__('Default: chat. Route becomes /<slug>.', 'exotic-chat-landing') . '</p>';
    }

    public static function render_fallback_chat_id_field(): void
    {
        $settings = self::get_settings();
        printf(
            '<input type="text" class="regular-text" name="%1$s[fallback_chat_id]" value="%2$s" />',
            esc_attr(self::OPTION_KEY),
            esc_attr((string) $settings['fallback_chat_id'])
        );
        echo '<p class="description">' . esc_html__('Used only if support-board-cloud setting is missing.', 'exotic-chat-landing') . '</p>';
    }

    public static function render_default_department_id_field(): void
    {
        $settings = self::get_settings();
        printf(
            '<input type="number" min="0" step="1" class="small-text" name="%1$s[default_department_id]" value="%2$d" />',
            esc_attr(self::OPTION_KEY),
            (int) $settings['default_department_id']
        );
    }

    public static function render_country_map_json_field(): void
    {
        $settings = self::get_settings();
        printf(
            '<textarea name="%1$s[country_map_json]" rows="10" class="large-text code">%2$s</textarea>',
            esc_attr(self::OPTION_KEY),
            esc_textarea((string) $settings['country_map_json'])
        );
        echo '<p class="description">' . esc_html__('Map hostname to country_code and department_id.', 'exotic-chat-landing') . '</p>';
    }

    public static function render_allow_signed_override_field(): void
    {
        $settings = self::get_settings();
        printf(
            '<label><input type="checkbox" name="%1$s[allow_signed_override]" value="1" %2$s /> %3$s</label>',
            esc_attr(self::OPTION_KEY),
            checked((int) $settings['allow_signed_override'], 1, false),
            esc_html__('Allow signed ?dept= override links.', 'exotic-chat-landing')
        );
    }

    public static function render_auto_open_delay_ms_field(): void
    {
        $settings = self::get_settings();
        printf(
            '<input type="number" min="0" max="15000" step="100" class="small-text" name="%1$s[auto_open_delay_ms]" value="%2$d" />',
            esc_attr(self::OPTION_KEY),
            (int) $settings['auto_open_delay_ms']
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function decode_country_map(string $raw): array
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

    /**
     * @param array<string, array<string, mixed>> $country_map
     */
    private static function encode_country_map(array $country_map): string
    {
        $json = wp_json_encode($country_map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return is_string($json) && $json !== '' ? $json : '{}';
    }
}
