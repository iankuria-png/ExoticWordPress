<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Exotic_Chat_Language_Registry
{
    public const DEFAULT_LANGUAGE = 'en';

    /**
     * @return array<string, array<string, string>>
     */
    public static function supported_languages(): array
    {
        return [
            'en' => [
                'label' => __('English', 'exotic-chat-landing'),
                'html_lang' => 'en',
                'sb_lang' => 'en',
            ],
            'sw' => [
                'label' => __('Kiswahili', 'exotic-chat-landing'),
                'html_lang' => 'sw',
                'sb_lang' => 'sw',
            ],
            'fr' => [
                'label' => __('French', 'exotic-chat-landing'),
                'html_lang' => 'fr',
                'sb_lang' => 'fr',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function supported_language_codes(): array
    {
        return array_keys(self::supported_languages());
    }

    public static function label_for(string $language_code): string
    {
        $languages = self::supported_languages();
        return isset($languages[$language_code]['label']) ? $languages[$language_code]['label'] : strtoupper($language_code);
    }

    public static function html_lang_for(string $language_code): string
    {
        $languages = self::supported_languages();
        return isset($languages[$language_code]['html_lang']) ? $languages[$language_code]['html_lang'] : self::DEFAULT_LANGUAGE;
    }

    public static function support_board_lang_for(string $language_code): string
    {
        $languages = self::supported_languages();
        return isset($languages[$language_code]['sb_lang']) ? $languages[$language_code]['sb_lang'] : self::DEFAULT_LANGUAGE;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function translation_fields(): array
    {
        return [
            'page_title_suffix' => ['label' => __('Browser title suffix', 'exotic-chat-landing'), 'type' => 'text'],
            'eyebrow' => ['label' => __('Eyebrow label', 'exotic-chat-landing'), 'type' => 'text'],
            'title' => ['label' => __('Headline', 'exotic-chat-landing'), 'type' => 'text'],
            'intro' => ['label' => __('Intro copy', 'exotic-chat-landing'), 'type' => 'textarea'],
            'badge_privacy' => ['label' => __('Privacy badge', 'exotic-chat-landing'), 'type' => 'text'],
            'badge_hours' => ['label' => __('24/7 badge', 'exotic-chat-landing'), 'type' => 'text'],
            'badge_live' => ['label' => __('Live badge', 'exotic-chat-landing'), 'type' => 'text'],
            'status_initializing' => ['label' => __('Initializing status', 'exotic-chat-landing'), 'type' => 'text'],
            'status_loading' => ['label' => __('Loading status', 'exotic-chat-landing'), 'type' => 'text'],
            'status_opening' => ['label' => __('Opening status', 'exotic-chat-landing'), 'type' => 'text'],
            'status_connected' => ['label' => __('Connected status', 'exotic-chat-landing'), 'type' => 'text'],
            'status_live' => ['label' => __('Live status', 'exotic-chat-landing'), 'type' => 'text'],
            'status_minimized' => ['label' => __('Minimized status', 'exotic-chat-landing'), 'type' => 'text'],
            'status_delayed' => ['label' => __('Delayed status', 'exotic-chat-landing'), 'type' => 'text'],
            'status_still_loading' => ['label' => __('Still loading status', 'exotic-chat-landing'), 'type' => 'text'],
            'status_reopening' => ['label' => __('Reopening status', 'exotic-chat-landing'), 'type' => 'text'],
            'status_focus_prompt' => ['label' => __('Focus prompt status', 'exotic-chat-landing'), 'type' => 'text'],
            'cta_focus' => ['label' => __('Focus CTA label', 'exotic-chat-landing'), 'type' => 'text'],
            'cta_open' => ['label' => __('Open chat CTA label', 'exotic-chat-landing'), 'type' => 'text'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function default_market_profile(): array
    {
        return [
            'default_language' => self::DEFAULT_LANGUAGE,
            'enabled_languages' => [self::DEFAULT_LANGUAGE],
            'allow_query_override' => 0,
            'use_wp_language' => 0,
            'use_browser_language' => 0,
            'widget_languages' => [
                self::DEFAULT_LANGUAGE => self::DEFAULT_LANGUAGE,
            ],
            'translations' => [],
        ];
    }

    /**
     * @param array<string, mixed> $raw_profile
     * @param array<string, mixed> $existing_profile
     * @return array<string, mixed>
     */
    public static function sanitize_market_profile(array $raw_profile, array $existing_profile = []): array
    {
        $profile = array_merge(self::default_market_profile(), $existing_profile);

        $enabled_languages = $raw_profile['enabled_languages'] ?? ($existing_profile['enabled_languages'] ?? [self::DEFAULT_LANGUAGE]);
        $profile['enabled_languages'] = self::sanitize_enabled_languages($enabled_languages);

        $default_language = self::normalize_language_code((string) ($raw_profile['default_language'] ?? ($existing_profile['default_language'] ?? self::DEFAULT_LANGUAGE)));
        if (!in_array($default_language, $profile['enabled_languages'], true)) {
            $default_language = $profile['enabled_languages'][0] ?? self::DEFAULT_LANGUAGE;
        }
        $profile['default_language'] = $default_language;

        $profile['allow_query_override'] = !empty($raw_profile['allow_query_override']) ? 1 : 0;
        $profile['use_wp_language'] = !empty($raw_profile['use_wp_language']) ? 1 : 0;
        $profile['use_browser_language'] = !empty($raw_profile['use_browser_language']) ? 1 : 0;
        $widget_languages = $raw_profile['widget_languages'] ?? ($existing_profile['widget_languages'] ?? []);
        $profile['widget_languages'] = self::sanitize_widget_languages($widget_languages, $profile['enabled_languages']);

        $translations = $raw_profile['translations'] ?? ($existing_profile['translations'] ?? []);
        $profile['translations'] = self::sanitize_translations($translations);

        return $profile;
    }

    /**
     * @param mixed $languages
     * @return array<int, string>
     */
    public static function sanitize_enabled_languages($languages): array
    {
        $enabled = [];
        $supported = self::supported_language_codes();

        if (is_string($languages)) {
            $languages = [$languages];
        }

        if (!is_array($languages)) {
            $languages = [];
        }

        foreach ($languages as $language_code) {
            $normalized = self::normalize_language_code((string) $language_code);
            if ($normalized === '' || !in_array($normalized, $supported, true)) {
                continue;
            }
            $enabled[] = $normalized;
        }

        $enabled = array_values(array_unique($enabled));
        if (empty($enabled)) {
            $enabled[] = self::DEFAULT_LANGUAGE;
        }

        return $enabled;
    }

    /**
     * @param mixed $widget_languages
     * @param array<int, string> $enabled_languages
     * @return array<string, string>
     */
    public static function sanitize_widget_languages($widget_languages, array $enabled_languages): array
    {
        $sanitized = [];
        if (is_array($widget_languages)) {
            foreach ($widget_languages as $landing_language => $widget_language) {
                $landing_language = self::normalize_language_code((string) $landing_language);
                if ($landing_language === '' || !in_array($landing_language, $enabled_languages, true)) {
                    continue;
                }

                $normalized_widget = self::normalize_language_code((string) $widget_language);
                $sanitized[$landing_language] = $normalized_widget !== '' ? $normalized_widget : self::DEFAULT_LANGUAGE;
            }
        }

        foreach ($enabled_languages as $landing_language) {
            if (!isset($sanitized[$landing_language])) {
                $sanitized[$landing_language] = $landing_language;
            }
        }

        return $sanitized;
    }

    public static function normalize_language_code(string $language_code): string
    {
        $language_code = strtolower(trim($language_code));
        if ($language_code === '') {
            return '';
        }

        $language_code = str_replace('_', '-', $language_code);
        if (preg_match('/^[a-z]{2}/', $language_code, $matches)) {
            $language_code = $matches[0];
        }

        return in_array($language_code, self::supported_language_codes(), true) ? $language_code : '';
    }

    /**
     * @param array<string, mixed> $profile
     * @param array<string, mixed> $context
     */
    public static function resolve_language(array $profile, array $context = []): string
    {
        $profile = self::sanitize_market_profile($profile);
        $enabled = $profile['enabled_languages'];

        $query_language = '';
        if (!empty($profile['allow_query_override'])) {
            $query_language = self::normalize_language_code((string) ($context['query_language'] ?? ''));
        }
        if ($query_language !== '' && in_array($query_language, $enabled, true)) {
            return $query_language;
        }

        $wp_language = '';
        if (!empty($profile['use_wp_language'])) {
            $wp_language = self::detect_wordpress_language();
        }
        if ($wp_language !== '' && in_array($wp_language, $enabled, true)) {
            return $wp_language;
        }

        $browser_language = '';
        if (!empty($profile['use_browser_language'])) {
            $browser_language = self::detect_browser_language((string) ($context['browser_languages'] ?? ''));
        }
        if ($browser_language !== '' && in_array($browser_language, $enabled, true)) {
            return $browser_language;
        }

        $default_language = self::normalize_language_code((string) ($profile['default_language'] ?? self::DEFAULT_LANGUAGE));
        if ($default_language !== '' && in_array($default_language, $enabled, true)) {
            return $default_language;
        }

        if (in_array(self::DEFAULT_LANGUAGE, $enabled, true)) {
            return self::DEFAULT_LANGUAGE;
        }

        return $enabled[0] ?? self::DEFAULT_LANGUAGE;
    }

    public static function detect_wordpress_language(): string
    {
        if (defined('ICL_LANGUAGE_CODE') && is_string(ICL_LANGUAGE_CODE) && ICL_LANGUAGE_CODE !== '') {
            $normalized = self::normalize_language_code(ICL_LANGUAGE_CODE);
            if ($normalized !== '') {
                return $normalized;
            }
        }

        if (has_filter('wpml_current_language')) {
            $current_language = apply_filters('wpml_current_language', null);
            if (is_string($current_language) && $current_language !== '') {
                $normalized = self::normalize_language_code($current_language);
                if ($normalized !== '') {
                    return $normalized;
                }
            }
        }

        if (function_exists('pll_current_language')) {
            $current_language = pll_current_language('slug');
            if (!is_string($current_language) || $current_language === '') {
                $current_language = pll_current_language();
            }
            if (is_string($current_language) && $current_language !== '') {
                $normalized = self::normalize_language_code($current_language);
                if ($normalized !== '') {
                    return $normalized;
                }
            }
        }

        $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
        return self::normalize_language_code((string) $locale);
    }

    public static function detect_browser_language(string $header): string
    {
        $header = trim($header);
        if ($header === '') {
            return '';
        }

        $parts = explode(',', $header);
        foreach ($parts as $part) {
            $candidate = trim((string) strtok($part, ';'));
            $normalized = self::normalize_language_code($candidate);
            if ($normalized !== '') {
                return $normalized;
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $profile
     */
    public static function resolve_widget_language(array $profile, string $landing_language): string
    {
        $profile = self::sanitize_market_profile($profile);
        $landing_language = self::normalize_language_code($landing_language);
        if ($landing_language === '') {
            return self::DEFAULT_LANGUAGE;
        }

        $widget_languages = isset($profile['widget_languages']) && is_array($profile['widget_languages']) ? $profile['widget_languages'] : [];
        $widget_language = isset($widget_languages[$landing_language]) ? self::normalize_language_code((string) $widget_languages[$landing_language]) : '';

        return $widget_language !== '' ? $widget_language : self::DEFAULT_LANGUAGE;
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, string>
     */
    public static function resolve_strings(string $language_code, array $overrides = []): array
    {
        $english = self::packaged_strings(self::DEFAULT_LANGUAGE);
        $localized = self::packaged_strings($language_code);
        return array_merge($english, $localized, self::sanitize_translation_values($overrides));
    }

    /**
     * @return array<string, string>
     */
    public static function packaged_strings(string $language_code): array
    {
        switch ($language_code) {
            case 'sw':
                return [
                    'page_title_suffix' => 'Msaada wa Gumzo',
                    'eyebrow' => 'Msaada',
                    'title' => 'Karibu Exotic Chat',
                    'intro' => 'Tunaandaa kikao chako cha msaada.',
                    'badge_privacy' => 'Faragha',
                    'badge_hours' => '24/7',
                    'badge_live' => 'Moja kwa moja',
                    'status_initializing' => 'Tunaanzisha gumzo...',
                    'status_loading' => 'Gumzo linapakia...',
                    'status_opening' => 'Tunafungua gumzo...',
                    'status_connected' => 'Imeunganishwa. Tunaandaa sehemu ya kuandika...',
                    'status_live' => 'Gumzo liko tayari.',
                    'status_minimized' => 'Gumzo limepunguzwa. Gusa Fungua gumzo.',
                    'status_delayed' => 'Gumzo linachelewa kufunguka. Gusa kitufe kilicho hapa chini.',
                    'status_still_loading' => 'Bado tunapakia gumzo...',
                    'status_reopening' => 'Tunafungua gumzo tena...',
                    'status_focus_prompt' => 'Gusa kitufe kilicho hapa chini uanze kuandika.',
                    'cta_focus' => 'Gusa kuanza kuandika',
                    'cta_open' => 'Fungua gumzo',
                ];
            case 'fr':
                return [
                    'page_title_suffix' => 'Assistance Chat',
                    'eyebrow' => 'Assistance',
                    'title' => 'Bienvenue sur Exotic Chat',
                    'intro' => 'Nous préparons votre session de support.',
                    'badge_privacy' => 'Confidentialité',
                    'badge_hours' => '24/7',
                    'badge_live' => 'En direct',
                    'status_initializing' => 'Initialisation du chat...',
                    'status_loading' => 'Le chat se charge...',
                    'status_opening' => 'Ouverture du chat...',
                    'status_connected' => 'Connecté. Préparation du champ de saisie...',
                    'status_live' => 'Le chat est en direct.',
                    'status_minimized' => 'Le chat est réduit. Appuyez sur Ouvrir le chat.',
                    'status_delayed' => 'Le chat met plus de temps que prévu. Appuyez sur le bouton ci-dessous.',
                    'status_still_loading' => 'Le chat continue de se charger...',
                    'status_reopening' => 'Réouverture du chat...',
                    'status_focus_prompt' => 'Appuyez sur le bouton ci-dessous pour commencer à écrire.',
                    'cta_focus' => 'Appuyez pour commencer à écrire',
                    'cta_open' => 'Ouvrir le chat',
                ];
            case 'en':
            default:
                return [
                    'page_title_suffix' => 'Chat Support',
                    'eyebrow' => 'Support',
                    'title' => 'Welcome to Exotic Chat',
                    'intro' => 'We are preparing your support session.',
                    'badge_privacy' => 'Privacy',
                    'badge_hours' => '24/7',
                    'badge_live' => 'Live',
                    'status_initializing' => 'Initializing chat...',
                    'status_loading' => 'Chat is loading...',
                    'status_opening' => 'Opening chat...',
                    'status_connected' => 'Connected. Preparing input...',
                    'status_live' => 'Chat is live.',
                    'status_minimized' => 'Chat minimized. Tap Open chat.',
                    'status_delayed' => 'Chat is taking longer than expected. Tap the button below.',
                    'status_still_loading' => 'Still loading chat...',
                    'status_reopening' => 'Reopening chat...',
                    'status_focus_prompt' => 'Tap the button below to start typing.',
                    'cta_focus' => 'Tap to start typing',
                    'cta_open' => 'Open chat',
                ];
        }
    }

    /**
     * @param mixed $translations
     * @return array<string, array<string, string>>
     */
    public static function sanitize_translations($translations): array
    {
        $sanitized = [];
        if (!is_array($translations)) {
            return $sanitized;
        }

        foreach (self::supported_language_codes() as $language_code) {
            $translation_values = $translations[$language_code] ?? null;
            if (!is_array($translation_values)) {
                continue;
            }

            $cleaned = self::sanitize_translation_values($translation_values);
            if (!empty($cleaned)) {
                $sanitized[$language_code] = $cleaned;
            }
        }

        return $sanitized;
    }

    /**
     * @param array<string, mixed> $translation_values
     * @return array<string, string>
     */
    public static function sanitize_translation_values(array $translation_values): array
    {
        $sanitized = [];
        foreach (self::translation_fields() as $field_key => $field_config) {
            if (!array_key_exists($field_key, $translation_values)) {
                continue;
            }

            $value = (string) $translation_values[$field_key];
            $value = $field_config['type'] === 'textarea' ? sanitize_textarea_field($value) : sanitize_text_field($value);
            if ($value !== '') {
                $sanitized[$field_key] = $value;
            }
        }

        return $sanitized;
    }
}
