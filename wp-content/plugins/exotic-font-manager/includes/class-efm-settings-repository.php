<?php

if (!defined('ABSPATH')) {
    exit;
}

class EFM_Settings_Repository
{
    const OPTION_SETTINGS = 'exotic_font_manager_settings';
    const OPTION_HISTORY = 'exotic_font_manager_history';

    public static function ensure_bootstrap_settings()
    {
        $settings = get_option(self::OPTION_SETTINGS, null);

        if (!is_array($settings)) {
            update_option(self::OPTION_SETTINGS, self::default_settings(), false);
        } else {
            self::update_settings($settings, false);
        }

        $history = get_option(self::OPTION_HISTORY, null);
        if (!is_array($history)) {
            update_option(self::OPTION_HISTORY, [], false);
        }
    }

    public static function default_settings()
    {
        return [
            'version' => 1,
            'rules' => [],
            'font_library' => EFM_Font_Library::default_fonts(),
            'history_limit' => 20,
            'updated_at' => time(),
        ];
    }

    public static function get_settings()
    {
        $settings = get_option(self::OPTION_SETTINGS, []);

        if (!is_array($settings)) {
            $settings = [];
        }

        return self::sanitize_settings(array_merge(self::default_settings(), $settings));
    }

    public static function update_settings($settings, $snapshot = true)
    {
        $current = self::get_settings();
        $new_settings = self::sanitize_settings($settings);

        if ($snapshot) {
            self::push_history_snapshot($current);
        }

        $new_settings['updated_at'] = time();
        update_option(self::OPTION_SETTINGS, $new_settings, false);

        return $new_settings;
    }

    public static function get_rules()
    {
        $settings = self::get_settings();
        return isset($settings['rules']) && is_array($settings['rules']) ? $settings['rules'] : [];
    }

    public static function save_rule($rule)
    {
        $settings = self::get_settings();
        $rules = self::get_rules();

        $sanitized = self::sanitize_rule($rule);
        if (empty($sanitized['id'])) {
            $sanitized['id'] = self::generate_rule_id();
        }

        $updated = false;
        foreach ($rules as $index => $existing_rule) {
            if (!is_array($existing_rule)) {
                continue;
            }

            if (isset($existing_rule['id']) && $existing_rule['id'] === $sanitized['id']) {
                $rules[$index] = $sanitized;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $rules[] = $sanitized;
        }

        $settings['rules'] = $rules;
        self::update_settings($settings, true);

        return $sanitized;
    }

    public static function delete_rule($rule_id)
    {
        $rule_id = sanitize_key((string) $rule_id);
        if ($rule_id === '') {
            return false;
        }

        $settings = self::get_settings();
        $rules = self::get_rules();
        $new_rules = [];
        $deleted = false;

        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }

            if (isset($rule['id']) && $rule['id'] === $rule_id) {
                $deleted = true;
                continue;
            }

            $new_rules[] = $rule;
        }

        if (!$deleted) {
            return false;
        }

        $settings['rules'] = $new_rules;
        self::update_settings($settings, true);
        return true;
    }

    public static function get_font_library()
    {
        $settings = self::get_settings();
        return isset($settings['font_library']) && is_array($settings['font_library']) ? $settings['font_library'] : [];
    }

    public static function save_font($font)
    {
        $font = EFM_Font_Library::sanitize_font_entry($font);
        if ($font === null) {
            return null;
        }

        $settings = self::get_settings();
        $library = self::get_font_library();

        $saved = false;
        foreach ($library as $index => $existing_font) {
            if (!is_array($existing_font) || !isset($existing_font['id'])) {
                continue;
            }

            if ($existing_font['id'] === $font['id']) {
                $library[$index] = $font;
                $saved = true;
                break;
            }
        }

        if (!$saved) {
            $library[] = $font;
        }

        $settings['font_library'] = $library;
        self::update_settings($settings, true);
        return $font;
    }

    public static function delete_font($font_id)
    {
        $font_id = sanitize_key((string) $font_id);
        if ($font_id === '') {
            return false;
        }

        $library = self::get_font_library();
        $settings = self::get_settings();
        $new_library = [];
        $deleted = false;

        foreach ($library as $font) {
            if (!is_array($font) || !isset($font['id'])) {
                continue;
            }

            if ($font['id'] === $font_id) {
                $deleted = true;
                continue;
            }

            $new_library[] = $font;
        }

        if (!$deleted) {
            return false;
        }

        $settings['font_library'] = $new_library;

        $rules = self::get_rules();
        foreach ($rules as $index => $rule) {
            if (!is_array($rule)) {
                continue;
            }

            if (!isset($rule['font_id']) || $rule['font_id'] !== $font_id) {
                continue;
            }

            $rules[$index]['font_id'] = 'system_inter';
        }

        $settings['rules'] = $rules;
        self::update_settings($settings, true);

        return true;
    }

    public static function get_history()
    {
        $history = get_option(self::OPTION_HISTORY, []);
        return is_array($history) ? $history : [];
    }

    public static function rollback_latest()
    {
        $history = self::get_history();
        if (empty($history)) {
            return false;
        }

        $snapshot = array_pop($history);
        if (!is_array($snapshot) || !isset($snapshot['settings']) || !is_array($snapshot['settings'])) {
            update_option(self::OPTION_HISTORY, $history, false);
            return false;
        }

        $settings = self::sanitize_settings($snapshot['settings']);
        update_option(self::OPTION_SETTINGS, $settings, false);
        update_option(self::OPTION_HISTORY, $history, false);

        return true;
    }

    public static function reset_overrides()
    {
        $settings = self::get_settings();
        $settings['rules'] = [];
        self::update_settings($settings, true);
    }

    public static function export_profile()
    {
        $settings = self::get_settings();

        return [
            'exported_at' => gmdate('c'),
            'plugin' => 'exotic-font-manager',
            'version' => EXOTIC_FONT_MANAGER_VERSION,
            'site' => home_url('/'),
            'theme' => [
                'template' => get_option('template'),
                'stylesheet' => get_option('stylesheet'),
            ],
            'settings' => $settings,
        ];
    }

    public static function import_profile($raw_json, &$error = '')
    {
        $payload = json_decode((string) $raw_json, true);
        if (!is_array($payload)) {
            $error = __('Import file is not valid JSON.', 'exotic-font-manager');
            return false;
        }

        if (!isset($payload['settings']) || !is_array($payload['settings'])) {
            $error = __('Import file is missing settings data.', 'exotic-font-manager');
            return false;
        }

        self::update_settings($payload['settings'], true);

        return true;
    }

    private static function sanitize_settings($settings)
    {
        if (!is_array($settings)) {
            $settings = [];
        }

        $defaults = self::default_settings();
        $clean = array_merge($defaults, $settings);

        $clean['version'] = absint($clean['version']);
        if ($clean['version'] < 1) {
            $clean['version'] = 1;
        }

        $clean['history_limit'] = absint($clean['history_limit']);
        if ($clean['history_limit'] < 5 || $clean['history_limit'] > 100) {
            $clean['history_limit'] = 20;
        }

        $clean['updated_at'] = absint($clean['updated_at']);

        $clean['rules'] = self::sanitize_rules(isset($clean['rules']) ? $clean['rules'] : []);
        $clean['font_library'] = self::sanitize_font_library(isset($clean['font_library']) ? $clean['font_library'] : []);

        return $clean;
    }

    private static function sanitize_rules($rules)
    {
        if (!is_array($rules)) {
            return [];
        }

        $clean = [];

        foreach ($rules as $rule) {
            $sanitized = self::sanitize_rule($rule);
            if ($sanitized !== null) {
                $clean[] = $sanitized;
            }
        }

        return $clean;
    }

    private static function sanitize_rule($rule)
    {
        if (!is_array($rule)) {
            return null;
        }

        $scope_type = sanitize_key(isset($rule['scope_type']) ? $rule['scope_type'] : 'site');
        $allowed_scopes = ['site', 'front_page', 'post_type_single', 'post_type_archive', 'page', 'post', 'taxonomy'];
        if (!in_array($scope_type, $allowed_scopes, true)) {
            $scope_type = 'site';
        }

        $font_style = sanitize_key(isset($rule['font_style']) ? $rule['font_style'] : 'normal');
        if (!in_array($font_style, ['normal', 'italic'], true)) {
            $font_style = 'normal';
        }

        $delivery_mode = sanitize_key(isset($rule['delivery_mode']) ? $rule['delivery_mode'] : 'auto');
        if (!in_array($delivery_mode, ['auto', 'local', 'cdn'], true)) {
            $delivery_mode = 'auto';
        }

        $rule_id = sanitize_key(isset($rule['id']) ? $rule['id'] : '');

        return [
            'id' => $rule_id,
            'label' => sanitize_text_field(isset($rule['label']) ? $rule['label'] : ''),
            'enabled' => !empty($rule['enabled']) ? 1 : 0,
            'target_key' => sanitize_key(isset($rule['target_key']) ? $rule['target_key'] : 'global_body_text'),
            'scope_type' => $scope_type,
            'scope_value' => sanitize_text_field(isset($rule['scope_value']) ? $rule['scope_value'] : ''),
            'font_id' => sanitize_key(isset($rule['font_id']) ? $rule['font_id'] : 'system_inter'),
            'font_weight' => self::sanitize_weight(isset($rule['font_weight']) ? $rule['font_weight'] : ''),
            'font_style' => $font_style,
            'delivery_mode' => $delivery_mode,
            'updated_at' => time(),
        ];
    }

    private static function sanitize_weight($weight)
    {
        $weight = trim((string) $weight);
        if ($weight === '') {
            return '';
        }

        if (!preg_match('/^[1-9]00$/', $weight)) {
            return '';
        }

        return $weight;
    }

    private static function sanitize_font_library($library)
    {
        if (!is_array($library)) {
            $library = [];
        }

        $indexed = [];
        foreach ($library as $font) {
            $sanitized = EFM_Font_Library::sanitize_font_entry($font);
            if ($sanitized === null) {
                continue;
            }
            $indexed[$sanitized['id']] = $sanitized;
        }

        foreach (EFM_Font_Library::default_fonts() as $default_font) {
            if (!isset($indexed[$default_font['id']])) {
                $indexed[$default_font['id']] = $default_font;
            }
        }

        return array_values($indexed);
    }

    private static function push_history_snapshot($settings)
    {
        $history = self::get_history();
        $settings = self::sanitize_settings($settings);

        $history[] = [
            'created_at' => time(),
            'settings' => $settings,
        ];

        $limit = isset($settings['history_limit']) ? absint($settings['history_limit']) : 20;
        if ($limit < 5) {
            $limit = 20;
        }

        if (count($history) > $limit) {
            $history = array_slice($history, -1 * $limit);
        }

        update_option(self::OPTION_HISTORY, $history, false);
    }

    private static function generate_rule_id()
    {
        return 'rule_' . strtolower(wp_generate_password(10, false, false));
    }
}
