<?php

if (!defined('ABSPATH')) {
    exit;
}

class EFM_Font_Library
{
    public static function default_fonts()
    {
        return [
            self::system_font('system_inter', __('Inter (System Stack)', 'exotic-font-manager'), "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif"),
            self::system_font('system_open_sans', __('Open Sans', 'exotic-font-manager'), "'Open Sans', 'Inter', sans-serif"),
            self::system_font('system_georgia', __('Georgia Serif', 'exotic-font-manager'), "Georgia, 'Times New Roman', serif"),
            self::system_font('system_mono', __('System Monospace', 'exotic-font-manager'), "ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', monospace"),
        ];
    }

    public static function sanitize_font_entry($entry)
    {
        if (!is_array($entry)) {
            return null;
        }

        $id = sanitize_key(isset($entry['id']) ? $entry['id'] : '');
        if ($id === '') {
            $id = 'font_' . substr(md5(wp_json_encode($entry)), 0, 12);
        }

        $allowed_sources = ['system', 'upload', 'google_local', 'google_cdn', 'custom_cdn'];
        $source = sanitize_key(isset($entry['source']) ? $entry['source'] : 'system');
        if (!in_array($source, $allowed_sources, true)) {
            $source = 'system';
        }

        $label = sanitize_text_field(isset($entry['label']) ? $entry['label'] : $id);
        $stack = self::sanitize_font_stack(isset($entry['stack']) ? $entry['stack'] : '');

        if ($stack === '') {
            $stack = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
        }

        $variants = [];
        if (!empty($entry['variants']) && is_array($entry['variants'])) {
            foreach ($entry['variants'] as $variant) {
                $sanitized_variant = self::sanitize_variant($variant);
                if ($sanitized_variant !== null) {
                    $variants[] = $sanitized_variant;
                }
            }
        }

        $font = [
            'id' => $id,
            'label' => $label,
            'source' => $source,
            'stack' => $stack,
            'family' => sanitize_text_field(isset($entry['family']) ? $entry['family'] : ''),
            'variants' => $variants,
            'cdn_css_url' => isset($entry['cdn_css_url']) ? esc_url_raw((string) $entry['cdn_css_url']) : '',
            'google_family' => sanitize_text_field(isset($entry['google_family']) ? $entry['google_family'] : ''),
            'google_variants' => self::sanitize_google_variants(isset($entry['google_variants']) ? $entry['google_variants'] : []),
            'status' => self::sanitize_status(isset($entry['status']) ? $entry['status'] : 'ready'),
        ];

        return $font;
    }

    public static function index_by_id($font_library)
    {
        $indexed = [];

        if (!is_array($font_library)) {
            return $indexed;
        }

        foreach ($font_library as $font) {
            $sanitized = self::sanitize_font_entry($font);
            if ($sanitized === null) {
                continue;
            }

            $indexed[$sanitized['id']] = $sanitized;
        }

        return $indexed;
    }

    private static function sanitize_status($status)
    {
        $status = sanitize_key((string) $status);
        $allowed = ['ready', 'pending', 'error'];
        return in_array($status, $allowed, true) ? $status : 'ready';
    }

    private static function sanitize_google_variants($variants)
    {
        if (!is_array($variants)) {
            return [];
        }

        $clean = [];
        foreach ($variants as $variant) {
            $variant = sanitize_text_field((string) $variant);
            if ($variant === '') {
                continue;
            }
            $clean[] = $variant;
        }

        return array_values(array_unique($clean));
    }

    private static function sanitize_font_stack($stack)
    {
        $stack = trim((string) $stack);
        if ($stack === '') {
            return '';
        }

        // Allow only common font-stack characters.
        if (!preg_match('/^[a-zA-Z0-9\s\"\'\-_,.()]+$/', $stack)) {
            return '';
        }

        return $stack;
    }

    private static function sanitize_variant($variant)
    {
        if (!is_array($variant)) {
            return null;
        }

        $weight = isset($variant['weight']) ? absint($variant['weight']) : 400;
        if ($weight < 100 || $weight > 900) {
            $weight = 400;
        }

        $style = sanitize_key(isset($variant['style']) ? $variant['style'] : 'normal');
        if (!in_array($style, ['normal', 'italic'], true)) {
            $style = 'normal';
        }

        $format = sanitize_key(isset($variant['format']) ? $variant['format'] : 'woff2');
        if (!in_array($format, ['woff2', 'woff', 'ttf', 'otf'], true)) {
            $format = 'woff2';
        }

        $url = esc_url_raw(isset($variant['url']) ? (string) $variant['url'] : '');

        return [
            'weight' => $weight,
            'style' => $style,
            'format' => $format,
            'url' => $url,
            'attachment_id' => isset($variant['attachment_id']) ? absint($variant['attachment_id']) : 0,
        ];
    }

    private static function system_font($id, $label, $stack)
    {
        return [
            'id' => $id,
            'label' => $label,
            'source' => 'system',
            'stack' => $stack,
            'family' => '',
            'variants' => [],
            'cdn_css_url' => '',
            'google_family' => '',
            'google_variants' => [],
            'status' => 'ready',
        ];
    }
}
