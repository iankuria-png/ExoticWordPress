<?php

if (!defined('ABSPATH')) {
    exit;
}

class EFM_Google_Installer
{
    public static function get_catalog()
    {
        $default_catalog = [
            'Inter',
            'Open Sans',
            'Roboto',
            'Lato',
            'Montserrat',
            'Poppins',
            'Merriweather',
            'Playfair Display',
            'Nunito',
            'Source Sans 3',
        ];

        return apply_filters('exotic_font_manager_google_font_families', $default_catalog);
    }

    public static function build_google_css_url($family, $variants)
    {
        $family = trim((string) $family);
        if ($family === '') {
            return '';
        }

        $variants = is_array($variants) ? $variants : [];
        $weights = [];
        foreach ($variants as $variant) {
            $variant = sanitize_text_field((string) $variant);
            if (preg_match('/^[1-9]00$/', $variant)) {
                $weights[] = $variant;
            }
        }

        $weights = array_values(array_unique($weights));
        sort($weights);

        $encoded_family = str_replace(' ', '+', $family);

        if (!empty($weights)) {
            $encoded_family .= ':wght@' . implode(';', $weights);
        }

        return 'https://fonts.googleapis.com/css2?family=' . $encoded_family . '&display=swap';
    }

    public static function create_google_cdn_font($family, $variants)
    {
        $family = sanitize_text_field((string) $family);
        if ($family === '') {
            return null;
        }

        $variants = self::sanitize_weight_list($variants);
        $css_url = self::build_google_css_url($family, $variants);
        if ($css_url === '') {
            return null;
        }

        return EFM_Font_Library::sanitize_font_entry([
            'id' => 'google_cdn_' . sanitize_title($family),
            'label' => sprintf(__('Google CDN: %s', 'exotic-font-manager'), $family),
            'source' => 'google_cdn',
            'stack' => "'" . $family . "', sans-serif",
            'family' => $family,
            'variants' => [],
            'cdn_css_url' => $css_url,
            'google_family' => $family,
            'google_variants' => $variants,
            'status' => 'ready',
        ]);
    }

    public static function install_local_font($family, $variants, &$error = '')
    {
        $family = sanitize_text_field((string) $family);
        if ($family === '') {
            $error = __('Google font family is required.', 'exotic-font-manager');
            return null;
        }

        $variants = self::sanitize_weight_list($variants);
        $css_url = self::build_google_css_url($family, $variants);
        if ($css_url === '') {
            $error = __('Unable to build Google font CSS URL.', 'exotic-font-manager');
            return null;
        }

        $css_response = wp_remote_get($css_url, [
            'timeout' => 20,
            'headers' => [
                // Ensure Google returns browser-compatible CSS with file URLs.
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0 Safari/537.36',
            ],
        ]);

        if (is_wp_error($css_response)) {
            $error = $css_response->get_error_message();
            return null;
        }

        $status_code = wp_remote_retrieve_response_code($css_response);
        if ($status_code < 200 || $status_code >= 300) {
            $error = sprintf(__('Google Fonts request failed with status %d.', 'exotic-font-manager'), $status_code);
            return null;
        }

        $css_body = (string) wp_remote_retrieve_body($css_response);
        if ($css_body === '') {
            $error = __('Google Fonts returned an empty stylesheet.', 'exotic-font-manager');
            return null;
        }

        $parsed_variants = self::parse_google_css_variants($css_body);
        if (empty($parsed_variants)) {
            $error = __('Could not parse downloadable font files from Google Fonts CSS.', 'exotic-font-manager');
            return null;
        }

        $downloads = self::download_variants_locally($family, $parsed_variants, $error);
        if (empty($downloads)) {
            if ($error === '') {
                $error = __('Failed to download Google font files locally.', 'exotic-font-manager');
            }
            return null;
        }

        return EFM_Font_Library::sanitize_font_entry([
            'id' => 'google_local_' . sanitize_title($family),
            'label' => sprintf(__('Google Local: %s', 'exotic-font-manager'), $family),
            'source' => 'google_local',
            'stack' => "'" . $family . "', sans-serif",
            'family' => $family,
            'variants' => $downloads,
            'cdn_css_url' => $css_url,
            'google_family' => $family,
            'google_variants' => $variants,
            'status' => 'ready',
        ]);
    }

    private static function sanitize_weight_list($variants)
    {
        if (!is_array($variants)) {
            $variants = preg_split('/\s*,\s*/', (string) $variants);
        }

        $clean = [];
        foreach ($variants as $variant) {
            $variant = sanitize_text_field((string) $variant);
            if (!preg_match('/^[1-9]00$/', $variant)) {
                continue;
            }
            $clean[] = $variant;
        }

        $clean = array_values(array_unique($clean));
        sort($clean);

        if (empty($clean)) {
            $clean = ['400', '500', '700'];
        }

        return $clean;
    }

    private static function parse_google_css_variants($css_body)
    {
        $variants = [];

        if (!preg_match_all('/@font-face\s*{[^}]*}/i', $css_body, $face_blocks)) {
            return $variants;
        }

        foreach ($face_blocks[0] as $face_block) {
            $weight = 400;
            $style = 'normal';
            $url = '';
            $format = 'woff2';

            if (preg_match('/font-weight:\s*([0-9]{3})/i', $face_block, $weight_match)) {
                $weight = absint($weight_match[1]);
            }

            if (preg_match('/font-style:\s*(normal|italic)/i', $face_block, $style_match)) {
                $style = strtolower($style_match[1]) === 'italic' ? 'italic' : 'normal';
            }

            if (preg_match("/url\((['\"]?)([^)'\"\\s]+)\\1\)\s*format\((['\"]?)([^)'\"]+)\\3\)/i", $face_block, $src_match)) {
                $url = esc_url_raw($src_match[2]);
                $format = sanitize_key($src_match[4]);
            } elseif (preg_match("/url\((['\"]?)([^)'\"\\s]+)\\1\)/i", $face_block, $url_match)) {
                $url = esc_url_raw($url_match[2]);
            }

            if ($url === '') {
                continue;
            }

            if (!in_array($format, ['woff2', 'woff', 'ttf', 'otf'], true)) {
                $format = 'woff2';
            }

            $variants[] = [
                'weight' => $weight,
                'style' => $style,
                'url' => $url,
                'format' => $format,
            ];
        }

        return $variants;
    }

    private static function download_variants_locally($family, $variants, &$error)
    {
        $upload_dir = wp_upload_dir();
        if (!empty($upload_dir['error'])) {
            $error = (string) $upload_dir['error'];
            return [];
        }

        $family_slug = sanitize_title($family);
        $base_dir = trailingslashit($upload_dir['basedir']) . 'exotic-font-manager/fonts/' . $family_slug . '/';
        $base_url = trailingslashit($upload_dir['baseurl']) . 'exotic-font-manager/fonts/' . $family_slug . '/';

        if (!wp_mkdir_p($base_dir)) {
            $error = __('Could not create local font directory.', 'exotic-font-manager');
            return [];
        }

        $downloads = [];

        foreach ($variants as $variant) {
            $weight = isset($variant['weight']) ? absint($variant['weight']) : 400;
            $style = isset($variant['style']) && $variant['style'] === 'italic' ? 'italic' : 'normal';
            $remote_url = isset($variant['url']) ? esc_url_raw((string) $variant['url']) : '';
            $format = isset($variant['format']) ? sanitize_key((string) $variant['format']) : 'woff2';

            if ($remote_url === '') {
                continue;
            }

            if (!in_array($format, ['woff2', 'woff', 'ttf', 'otf'], true)) {
                $format = 'woff2';
            }

            $filename = sprintf('%s-%s-%s.%s', $family_slug, $weight, $style, $format);
            $local_path = $base_dir . $filename;
            $local_url = $base_url . $filename;

            $response = wp_remote_get($remote_url, ['timeout' => 30]);
            if (is_wp_error($response)) {
                continue;
            }

            $status_code = wp_remote_retrieve_response_code($response);
            if ($status_code < 200 || $status_code >= 300) {
                continue;
            }

            $body = wp_remote_retrieve_body($response);
            if (!is_string($body) || $body === '') {
                continue;
            }

            $saved = file_put_contents($local_path, $body);
            if ($saved === false) {
                continue;
            }

            $downloads[] = [
                'weight' => $weight,
                'style' => $style,
                'format' => $format,
                'url' => esc_url_raw($local_url),
                'attachment_id' => 0,
            ];
        }

        return $downloads;
    }
}
