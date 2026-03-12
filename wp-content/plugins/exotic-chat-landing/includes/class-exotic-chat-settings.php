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

        add_settings_section(
            'ecl_languages',
            __('Country Language Profiles', 'exotic-chat-landing'),
            [__CLASS__, 'render_language_intro'],
            self::PAGE_SLUG
        );

        add_settings_field(
            'language_profiles',
            __('Per-country language controls', 'exotic-chat-landing'),
            [__CLASS__, 'render_language_profiles_field'],
            self::PAGE_SLUG,
            'ecl_languages'
        );
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
        $existing = self::get_settings();

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
            $country_map_raw = (string) $defaults['country_map_json'];
        }

        json_decode($country_map_raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            add_settings_error(self::OPTION_KEY, 'invalid-country-map', __('Country map JSON is invalid. Previous valid value kept.', 'exotic-chat-landing'));
            $country_map_raw = (string) ($existing['country_map_json'] ?? '{}');
        }
        $sanitized['country_map_json'] = $country_map_raw;

        $sanitized['allow_signed_override'] = !empty($input['allow_signed_override']) ? 1 : 0;

        $delay = (int) ($input['auto_open_delay_ms'] ?? 1500);
        $sanitized['auto_open_delay_ms'] = min(15000, max(0, $delay));

        $input_profiles = $input['language_profiles'] ?? [];
        $sanitized['language_profiles'] = self::sanitize_language_profiles($input_profiles, $existing);

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

        $merged = array_merge($defaults, $settings);
        $merged['language_profiles'] = self::normalize_language_profiles($merged['language_profiles'] ?? []);
        return $merged;
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
            'language_profiles' => [],
        ];
    }

    public static function render_page(): void
    {
        if (!current_user_can(self::settings_capability())) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Exotic Chat Landing', 'exotic-chat-landing') . '</h1>';
        echo '<style>';
        echo '.ecl-market-panels{display:grid;gap:16px;}';
        echo '.ecl-market-panel,.ecl-language-panel{border:1px solid #dcdcde;border-radius:10px;background:#fff;}';
        echo '.ecl-market-panel summary,.ecl-language-panel summary{cursor:pointer;font-weight:600;padding:14px 16px;}';
        echo '.ecl-market-meta{display:flex;gap:16px;flex-wrap:wrap;padding:0 16px 12px;color:#50575e;}';
        echo '.ecl-market-body{padding:0 16px 16px;}';
        echo '.ecl-language-grid{display:grid;gap:12px;}';
        echo '.ecl-language-checkboxes{display:flex;gap:16px;flex-wrap:wrap;}';
        echo '.ecl-language-card{border-top:1px solid #f0f0f1;padding-top:12px;margin-top:12px;}';
        echo '.ecl-language-card:first-child{border-top:0;padding-top:0;margin-top:0;}';
        echo '.ecl-field-grid{display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));}';
        echo '.ecl-field-grid .ecl-field--full{grid-column:1 / -1;}';
        echo '.ecl-field-grid label{display:block;font-weight:600;margin-bottom:6px;}';
        echo '.ecl-field-grid input[type="text"],.ecl-field-grid textarea,.ecl-field-grid select{width:100%;}';
        echo '.ecl-note{margin-top:8px;color:#646970;}';
        echo '</style>';
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

        $language_profiles = self::normalize_language_profiles($settings['language_profiles'] ?? []);
        $seed_countries = [$detected_country];
        foreach ($country_map as $country_map_entry) {
            if (!is_array($country_map_entry) || empty($country_map_entry['country_code'])) {
                continue;
            }
            $seed_countries[] = strtoupper(substr((string) $country_map_entry['country_code'], 0, 3));
        }

        foreach (array_unique($seed_countries) as $country_code) {
            if ($country_code === '') {
                continue;
            }
            if (!isset($language_profiles[$country_code]) || !is_array($language_profiles[$country_code])) {
                $language_profiles[$country_code] = self::bootstrap_market_profile($country_code);
                $updated = true;
                continue;
            }

            $upgraded_profile = self::upgrade_bootstrap_market_profile($country_code, $language_profiles[$country_code]);
            if ($upgraded_profile !== $language_profiles[$country_code]) {
                $language_profiles[$country_code] = $upgraded_profile;
                $updated = true;
            }
        }

        if (!$updated) {
            return;
        }

        $settings['country_map_json'] = self::encode_country_map($country_map);
        $settings['language_profiles'] = $language_profiles;
        update_option(self::OPTION_KEY, $settings);
    }

    public static function render_general_intro(): void
    {
        echo '<p>' . esc_html__('Configure the /chat landing route and Support Board Cloud defaults.', 'exotic-chat-landing') . '</p>';
    }

    public static function render_language_intro(): void
    {
        echo '<p>' . esc_html__('Language is market-specific and conservative by default. Each market keeps its configured default language unless you explicitly allow URL, WordPress, or browser-based switching.', 'exotic-chat-landing') . '</p>';
        echo '<p>' . esc_html__('Landing-shell language and Support Board widget language can be configured separately per market. Use this when the landing page should be Kiswahili but the embedded widget should remain in English.', 'exotic-chat-landing') . '</p>';
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
        echo '<p class="description">' . esc_html__('Used only if Support Board Cloud setting is missing.', 'exotic-chat-landing') . '</p>';
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
        echo '<p class="description">' . esc_html__('Map hostname to country_code and department_id. The language panels below use the countries detected here or on the current site.', 'exotic-chat-landing') . '</p>';
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

    public static function render_language_profiles_field(): void
    {
        $settings = self::get_settings();
        $markets = self::editor_markets($settings);
        $current_market = self::current_market_code($settings);

        echo '<div class="ecl-market-panels">';
        foreach ($markets as $country_code => $market) {
            $profile = self::profile_for_market($settings, $country_code);
            $department_id = isset($market['department_id']) ? (int) $market['department_id'] : Exotic_Chat_Country_Registry::default_department_for_country($country_code);
            $is_open = $country_code === $current_market ? ' open' : '';

            echo '<details class="ecl-market-panel"' . $is_open . '>';
            echo '<summary>' . esc_html(($market['name'] ?? $country_code) . ' (' . $country_code . ')') . '</summary>';
            echo '<div class="ecl-market-meta">';
            echo '<span>' . esc_html(sprintf(__('Department ID: %d', 'exotic-chat-landing'), $department_id)) . '</span>';
            echo '<span>' . esc_html(sprintf(__('Default language: %s', 'exotic-chat-landing'), Exotic_Chat_Language_Registry::label_for((string) $profile['default_language']))) . '</span>';
            echo '</div>';
            echo '<div class="ecl-market-body">';

            $base_name = self::OPTION_KEY . '[language_profiles][' . $country_code . ']';
            $supported_languages = Exotic_Chat_Language_Registry::supported_languages();

            echo '<div class="ecl-field-grid">';
            echo '<div>';
            echo '<label for="ecl-default-language-' . esc_attr($country_code) . '">' . esc_html__('Default language', 'exotic-chat-landing') . '</label>';
            echo '<select id="ecl-default-language-' . esc_attr($country_code) . '" name="' . esc_attr($base_name . '[default_language]') . '">';
            foreach ($supported_languages as $language_code => $language) {
                echo '<option value="' . esc_attr($language_code) . '" ' . selected((string) $profile['default_language'], $language_code, false) . '>' . esc_html($language['label']) . '</option>';
            }
            echo '</select>';
            echo '</div>';

            echo '<div class="ecl-field--full">';
            echo '<label>' . esc_html__('Enabled languages', 'exotic-chat-landing') . '</label>';
            echo '<div class="ecl-language-checkboxes">';
            foreach ($supported_languages as $language_code => $language) {
                echo '<label>';
                echo '<input type="checkbox" name="' . esc_attr($base_name . '[enabled_languages][]') . '" value="' . esc_attr($language_code) . '" ' . checked(in_array($language_code, (array) $profile['enabled_languages'], true), true, false) . ' /> ';
                echo esc_html($language['label']);
                echo '</label>';
            }
            echo '</div>';
            echo '</div>';

            echo '<div>';
            echo '<label><input type="checkbox" name="' . esc_attr($base_name . '[allow_query_override]') . '" value="1" ' . checked((int) $profile['allow_query_override'], 1, false) . ' /> ' . esc_html__('Allow ?lang= override', 'exotic-chat-landing') . '</label>';
            echo '</div>';

            echo '<div>';
            echo '<label><input type="checkbox" name="' . esc_attr($base_name . '[use_wp_language]') . '" value="1" ' . checked((int) $profile['use_wp_language'], 1, false) . ' /> ' . esc_html__('Use WordPress language', 'exotic-chat-landing') . '</label>';
            echo '</div>';

            echo '<div>';
            echo '<label><input type="checkbox" name="' . esc_attr($base_name . '[use_browser_language]') . '" value="1" ' . checked((int) $profile['use_browser_language'], 1, false) . ' /> ' . esc_html__('Use browser language', 'exotic-chat-landing') . '</label>';
            echo '</div>';
            echo '</div>';

            echo '<p class="ecl-note">' . esc_html__('Admin overrides are stored per market and per language. Leave a field empty to use the packaged plugin translation.', 'exotic-chat-landing') . '</p>';

            foreach ($supported_languages as $language_code => $language) {
                $translation_values = isset($profile['translations'][$language_code]) && is_array($profile['translations'][$language_code]) ? $profile['translations'][$language_code] : [];
                $selected_widget_language = isset($profile['widget_languages'][$language_code]) ? (string) $profile['widget_languages'][$language_code] : $language_code;
                echo '<details class="ecl-language-panel">';
                echo '<summary>' . esc_html(sprintf(__('%s overrides', 'exotic-chat-landing'), $language['label'])) . '</summary>';
                echo '<div class="ecl-market-body ecl-language-card">';
                echo '<div class="ecl-field-grid">';
                echo '<div>';
                echo '<label for="' . esc_attr('ecl-widget-language-' . strtolower($country_code . '-' . $language_code)) . '">' . esc_html__('Support Board widget language', 'exotic-chat-landing') . '</label>';
                echo '<select id="' . esc_attr('ecl-widget-language-' . strtolower($country_code . '-' . $language_code)) . '" name="' . esc_attr($base_name . '[widget_languages][' . $language_code . ']') . '">';
                foreach ($supported_languages as $widget_language_code => $widget_language) {
                    echo '<option value="' . esc_attr($widget_language_code) . '" ' . selected($selected_widget_language, $widget_language_code, false) . '>' . esc_html($widget_language['label']) . '</option>';
                }
                echo '</select>';
                echo '</div>';
                echo '</div>';
                echo '<div class="ecl-field-grid">';
                foreach (Exotic_Chat_Language_Registry::translation_fields() as $field_key => $field_config) {
                    $field_id = 'ecl-' . strtolower($country_code . '-' . $language_code . '-' . $field_key);
                    $field_name = $base_name . '[translations][' . $language_code . '][' . $field_key . ']';
                    $field_value = isset($translation_values[$field_key]) ? (string) $translation_values[$field_key] : '';
                    $field_class = $field_config['type'] === 'textarea' ? 'ecl-field--full' : '';

                    echo '<div class="' . esc_attr($field_class) . '">';
                    echo '<label for="' . esc_attr($field_id) . '">' . esc_html($field_config['label']) . '</label>';
                    if ($field_config['type'] === 'textarea') {
                        echo '<textarea id="' . esc_attr($field_id) . '" rows="3" name="' . esc_attr($field_name) . '">' . esc_textarea($field_value) . '</textarea>';
                    } else {
                        echo '<input type="text" id="' . esc_attr($field_id) . '" name="' . esc_attr($field_name) . '" value="' . esc_attr($field_value) . '" />';
                    }
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
                echo '</details>';
            }

            echo '</div>';
            echo '</details>';
        }
        echo '</div>';
    }

    /**
     * @param mixed $input_profiles
     * @param array<string, mixed> $settings
     * @return array<string, array<string, mixed>>
     */
    private static function sanitize_language_profiles($input_profiles, array $settings): array
    {
        $existing_profiles = self::normalize_language_profiles($settings['language_profiles'] ?? []);
        if (!is_array($input_profiles)) {
            return $existing_profiles;
        }

        $country_codes = array_unique(array_merge(
            array_keys($existing_profiles),
            array_map('strtoupper', array_keys($input_profiles))
        ));

        $sanitized = [];
        foreach ($country_codes as $country_code) {
            if (!is_string($country_code) || $country_code === '') {
                continue;
            }
            $country_code = strtoupper(substr($country_code, 0, 3));
            $raw_profile = isset($input_profiles[$country_code]) && is_array($input_profiles[$country_code]) ? $input_profiles[$country_code] : [];
            $existing_profile = isset($existing_profiles[$country_code]) && is_array($existing_profiles[$country_code]) ? $existing_profiles[$country_code] : [];
            $sanitized[$country_code] = Exotic_Chat_Language_Registry::sanitize_market_profile($raw_profile, $existing_profile);
        }

        ksort($sanitized);
        return $sanitized;
    }

    /**
     * @param mixed $profiles
     * @return array<string, array<string, mixed>>
     */
    private static function normalize_language_profiles($profiles): array
    {
        $normalized = [];
        if (!is_array($profiles)) {
            return $normalized;
        }

        foreach ($profiles as $country_code => $profile) {
            if (!is_string($country_code) || !is_array($profile)) {
                continue;
            }
            $country_code = strtoupper(substr($country_code, 0, 3));
            if ($country_code === '') {
                continue;
            }
            $normalized[$country_code] = Exotic_Chat_Language_Registry::sanitize_market_profile($profile);
        }

        ksort($normalized);
        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    private static function bootstrap_market_profile(string $country_code): array
    {
        $country_code = strtoupper(trim($country_code));
        $profile = Exotic_Chat_Language_Registry::default_market_profile();

        if ($country_code === 'TZ') {
            $profile['enabled_languages'] = ['en', 'sw'];
            $profile['default_language'] = 'en';
            $profile['widget_languages'] = [
                'en' => 'en',
                'sw' => 'en',
            ];
        } elseif ($country_code === 'KE') {
            $profile['enabled_languages'] = ['en'];
            $profile['default_language'] = 'en';
            $profile['widget_languages'] = [
                'en' => 'en',
            ];
        }

        return Exotic_Chat_Language_Registry::sanitize_market_profile($profile);
    }

    /**
     * @param array<string, mixed> $profile
     * @return array<string, mixed>
     */
    private static function upgrade_bootstrap_market_profile(string $country_code, array $profile): array
    {
        $country_code = strtoupper(trim($country_code));
        $profile = Exotic_Chat_Language_Registry::sanitize_market_profile($profile);

        if ($country_code !== 'TZ') {
            return $profile;
        }

        $is_legacy_default = $profile['default_language'] === 'en'
            && (array) $profile['enabled_languages'] === ['en']
            && (int) $profile['allow_query_override'] === 0
            && (int) $profile['use_wp_language'] === 0
            && (int) $profile['use_browser_language'] === 0
            && empty($profile['translations'])
            && isset($profile['widget_languages']['en'])
            && count((array) $profile['widget_languages']) === 1
            && $profile['widget_languages']['en'] === 'en';

        if (!$is_legacy_default) {
            return $profile;
        }

        return self::bootstrap_market_profile($country_code);
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, array<string, mixed>>
     */
    private static function editor_markets(array $settings): array
    {
        $markets = [];
        $profiles = self::normalize_language_profiles($settings['language_profiles'] ?? []);
        $country_map = self::decode_country_map((string) ($settings['country_map_json'] ?? '{}'));
        $known_markets = Exotic_Chat_Country_Registry::available_markets();

        foreach (array_keys($profiles) as $country_code) {
            if (isset($known_markets[$country_code])) {
                $markets[$country_code] = $known_markets[$country_code];
            }
        }

        foreach ($country_map as $entry) {
            if (!is_array($entry) || empty($entry['country_code'])) {
                continue;
            }
            $country_code = strtoupper(substr((string) $entry['country_code'], 0, 3));
            if ($country_code === '' || isset($markets[$country_code])) {
                continue;
            }
            $markets[$country_code] = $known_markets[$country_code] ?? [
                'country_code' => $country_code,
                'name' => Exotic_Chat_Country_Registry::country_name($country_code),
                'department_id' => Exotic_Chat_Country_Registry::default_department_for_country($country_code),
            ];
        }

        $current_market = self::current_market_code($settings);
        if ($current_market !== '' && !isset($markets[$current_market])) {
            $markets[$current_market] = $known_markets[$current_market] ?? [
                'country_code' => $current_market,
                'name' => Exotic_Chat_Country_Registry::country_name($current_market),
                'department_id' => Exotic_Chat_Country_Registry::default_department_for_country($current_market),
            ];
        }

        uasort($markets, static function (array $left, array $right): int {
            return strcmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
        });

        return $markets;
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function current_market_code(array $settings): string
    {
        $host = wp_parse_url(home_url('/'), PHP_URL_HOST);
        $host = is_string($host) ? strtolower(trim($host)) : '';
        if ($host === '') {
            return '';
        }

        $country_map = self::decode_country_map((string) ($settings['country_map_json'] ?? '{}'));
        $resolved = Exotic_Chat_Country_Registry::resolve_from_host($host, $country_map, (int) ($settings['default_department_id'] ?? 0));
        return isset($resolved['country_code']) && is_string($resolved['country_code']) ? strtoupper($resolved['country_code']) : '';
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private static function profile_for_market(array $settings, string $country_code): array
    {
        $profiles = self::normalize_language_profiles($settings['language_profiles'] ?? []);
        if (isset($profiles[$country_code]) && is_array($profiles[$country_code])) {
            return $profiles[$country_code];
        }

        return Exotic_Chat_Language_Registry::default_market_profile();
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
