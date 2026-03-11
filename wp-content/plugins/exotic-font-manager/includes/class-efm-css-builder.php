<?php

if (!defined('ABSPATH')) {
    exit;
}

class EFM_CSS_Builder
{
    public static function enqueue_dynamic_styles()
    {
        if (is_admin()) {
            return;
        }

        $settings = EFM_Settings_Repository::get_settings();
        $rules = isset($settings['rules']) && is_array($settings['rules']) ? $settings['rules'] : [];

        if (empty($rules)) {
            return;
        }

        $font_library = EFM_Font_Library::index_by_id(isset($settings['font_library']) ? $settings['font_library'] : []);
        $presets = EFM_Target_Presets::get_presets();

        $css_chunks = [];
        $cdn_urls = [];

        foreach ($rules as $rule) {
            if (empty($rule['enabled'])) {
                continue;
            }

            $target_key = isset($rule['target_key']) ? $rule['target_key'] : '';
            if (!isset($presets[$target_key])) {
                continue;
            }

            $font_id = isset($rule['font_id']) ? $rule['font_id'] : 'system_inter';
            $font = isset($font_library[$font_id]) ? $font_library[$font_id] : null;
            if (!$font) {
                continue;
            }

            $declarations = self::build_font_declarations($rule, $font);
            if ($declarations === '') {
                continue;
            }

            $selectors = self::build_scoped_selectors(
                isset($rule['scope_type']) ? $rule['scope_type'] : 'site',
                isset($rule['scope_value']) ? $rule['scope_value'] : '',
                isset($presets[$target_key]['selectors']) ? $presets[$target_key]['selectors'] : []
            );

            if (empty($selectors)) {
                continue;
            }

            $css_chunks[] = implode(', ', $selectors) . '{' . $declarations . '}';

            $delivery_mode = isset($rule['delivery_mode']) ? $rule['delivery_mode'] : 'auto';
            $needs_cdn = ($delivery_mode === 'cdn') || ($delivery_mode === 'auto' && empty($font['variants']));
            if ($needs_cdn && !empty($font['cdn_css_url'])) {
                $cdn_urls[] = $font['cdn_css_url'];
            }
        }

        if (empty($css_chunks) && empty($cdn_urls)) {
            return;
        }

        $cdn_urls = array_values(array_unique(array_filter($cdn_urls)));
        foreach ($cdn_urls as $cdn_url) {
            wp_enqueue_style('efm-cdn-' . md5($cdn_url), esc_url_raw($cdn_url), [], null);
        }

        $css = self::build_local_font_faces($rules, $font_library) . implode("\n", $css_chunks);
        $css = (string) apply_filters('exotic_font_manager_generated_css', $css, $settings, $rules);

        if (trim($css) === '') {
            return;
        }

        wp_register_style('exotic-font-manager-generated', false, [], EXOTIC_FONT_MANAGER_VERSION);
        wp_enqueue_style('exotic-font-manager-generated');
        wp_add_inline_style('exotic-font-manager-generated', $css);
    }

    private static function build_local_font_faces($rules, $font_library)
    {
        $faces = [];
        $font_modes = [];

        foreach ($rules as $rule) {
            if (empty($rule['enabled']) || empty($rule['font_id'])) {
                continue;
            }
            $font_id = sanitize_key((string) $rule['font_id']);
            if ($font_id === '') {
                continue;
            }

            if (!isset($font_modes[$font_id])) {
                $font_modes[$font_id] = [];
            }

            $mode = isset($rule['delivery_mode']) ? sanitize_key((string) $rule['delivery_mode']) : 'auto';
            if (!in_array($mode, ['auto', 'local', 'cdn'], true)) {
                $mode = 'auto';
            }

            $font_modes[$font_id][] = $mode;
        }

        foreach ($font_modes as $font_id => $modes) {
            if (!isset($font_library[$font_id])) {
                continue;
            }

            $font = $font_library[$font_id];
            $only_cdn = !empty($modes) && count(array_unique($modes)) === 1 && in_array('cdn', $modes, true);
            if ($only_cdn && !empty($font['cdn_css_url'])) {
                continue;
            }

            if (empty($font['variants']) || empty($font['family'])) {
                continue;
            }

            foreach ($font['variants'] as $variant) {
                if (!is_array($variant) || empty($variant['url'])) {
                    continue;
                }

                $format = isset($variant['format']) ? $variant['format'] : 'woff2';
                $weight = isset($variant['weight']) ? absint($variant['weight']) : 400;
                $style = isset($variant['style']) && $variant['style'] === 'italic' ? 'italic' : 'normal';
                $family = self::css_string(isset($font['family']) ? (string) $font['family'] : '');
                $url = self::css_url(isset($variant['url']) ? (string) $variant['url'] : '');
                $format = self::css_identifier($format);
                if ($family === '' || $url === '' || $format === '') {
                    continue;
                }

                $faces[] = "@font-face{font-family:'" . $family . "';src:url('" . $url . "') format('" . $format . "');font-weight:" . $weight . ';font-style:' . $style . ";font-display:swap;}";
            }
        }

        return implode("\n", $faces) . "\n";
    }

    private static function build_font_declarations($rule, $font)
    {
        $stack = isset($font['stack']) ? trim((string) $font['stack']) : '';
        if ($stack === '') {
            return '';
        }

        $declarations = 'font-family:' . $stack . ';';

        if (!empty($rule['font_weight'])) {
            $declarations .= 'font-weight:' . self::css_identifier($rule['font_weight']) . ';';
        }

        if (!empty($rule['font_style'])) {
            $declarations .= 'font-style:' . self::css_identifier($rule['font_style']) . ';';
        }

        return $declarations;
    }

    private static function build_scoped_selectors($scope_type, $scope_value, $selectors)
    {
        if (!is_array($selectors) || empty($selectors)) {
            return [];
        }

        $prefixes = self::resolve_scope_prefixes($scope_type, $scope_value);

        $resolved = [];
        foreach ($selectors as $selector) {
            $selector = trim((string) $selector);
            if ($selector === '') {
                continue;
            }

            if (empty($prefixes)) {
                $resolved[] = $selector;
                continue;
            }

            foreach ($prefixes as $prefix) {
                $resolved[] = self::combine_scope_selector($prefix, $selector);
            }
        }

        return array_values(array_unique($resolved));
    }

    private static function resolve_scope_prefixes($scope_type, $scope_value)
    {
        $scope_type = sanitize_key((string) $scope_type);
        $scope_value = trim((string) $scope_value);

        switch ($scope_type) {
            case 'site':
                return [];

            case 'front_page':
                return ['body.home', 'body.front-page'];

            case 'post_type_single':
                $post_type = sanitize_key($scope_value);
                return $post_type !== '' ? ['body.single-' . $post_type] : ['body.single'];

            case 'post_type_archive':
                $post_type = sanitize_key($scope_value);
                return $post_type !== '' ? ['body.post-type-archive-' . $post_type] : ['body.archive'];

            case 'page':
                $id = absint($scope_value);
                return $id > 0 ? ['body.page-id-' . $id] : [];

            case 'post':
                $id = absint($scope_value);
                return $id > 0 ? ['body.postid-' . $id] : [];

            case 'taxonomy':
                $slug = sanitize_title($scope_value);
                return $slug !== '' ? ['body.tax-' . $slug] : ['body.tax'];

            default:
                return [];
        }
    }

    private static function combine_scope_selector($prefix, $selector)
    {
        $prefix = trim((string) $prefix);
        $selector = trim((string) $selector);

        if ($prefix === '') {
            return $selector;
        }

        if ($selector === 'body') {
            return $prefix;
        }

        if (strpos($selector, 'body.') === 0) {
            return $prefix . substr($selector, 4);
        }

        if (strpos($selector, 'body ') === 0) {
            return $prefix . substr($selector, 4);
        }

        return $prefix . ' ' . $selector;
    }

    private static function css_string($value)
    {
        $value = (string) $value;
        return str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
    }

    private static function css_url($value)
    {
        $value = esc_url_raw((string) $value);
        return str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
    }

    private static function css_identifier($value)
    {
        $value = preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $value);
        return is_string($value) ? $value : '';
    }
}
