<?php

if (!defined('ABSPATH')) {
    exit;
}

class EFM_Target_Presets
{
    public static function get_presets()
    {
        $presets = [
            'global_body_text' => [
                'label' => __('Global Body Text', 'exotic-font-manager'),
                'selectors' => ['body', '.body', 'p', 'li', 'td', 'th', 'input', 'textarea', 'select', 'button'],
            ],
            'global_headings' => [
                'label' => __('Global Headings', 'exotic-font-manager'),
                'selectors' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', '.section-heading', '.profile-title', '.blog-hero__title', '.blog-article__title'],
            ],
            'navigation' => [
                'label' => __('Navigation Menus', 'exotic-font-manager'),
                'selectors' => ['.header-nav a', '.header-menu a', '.subnav-menu a', '.slider-menu a', '.mobile-account-link'],
            ],
            'buttons_cta' => [
                'label' => __('Buttons and CTAs', 'exotic-font-manager'),
                'selectors' => ['.button', 'button', '.pinkbutton', '.greenbutton', '.redbutton', '.ad-card__cta', '.profile-mobile-cta__btn'],
            ],
            'home_ad_cards' => [
                'label' => __('Homepage Ad Cards', 'exotic-font-manager'),
                'selectors' => ['.ad-card__title', '.ad-card__copy', '.ad-card__badge', '.ad-card__cta'],
            ],
            'home_filter_chips' => [
                'label' => __('Homepage Filter Chips', 'exotic-font-manager'),
                'selectors' => ['.filter-chip', '.filter-results-summary'],
            ],
            'home_section_titles' => [
                'label' => __('Homepage Section Titles', 'exotic-font-manager'),
                'selectors' => [
                    '.homepage-ad-carousel .section-heading',
                    '.online-stories-section .section-heading',
                    '.featured-section .section-heading',
                    '.premium-section .section-heading',
                    '.newlyadded-section .section-heading',
                    '.reviews-section .section-heading',
                ],
            ],
            'blog_typography' => [
                'label' => __('Blog Typography', 'exotic-font-manager'),
                'selectors' => ['.blog-card__body', '.blog-card__meta', '.blog-featured-card__body', '.blog-article__content', '.blog-article__title'],
            ],
            'escort_cards' => [
                'label' => __('Escort Cards', 'exotic-font-manager'),
                'selectors' => [
                    '.escort-card',
                    '.escort-card .girl-name',
                    '.escort-card .girl-desc-location',
                    '.escort-card .phone-number-box',
                    '.escort-card .call-now-box',
                    '.online-story__name',
                ],
            ],
            'profile_typography' => [
                'label' => __('Single Profile Typography', 'exotic-font-manager'),
                'selectors' => ['body.single-escort .profile-page', 'body.single-escort .profile-title', 'body.single-escort .profile-account__title', 'body.single-escort .profile-mobile-cta__name'],
            ],
            'escort_profile_page' => [
                'label' => __('Escort Profile Page', 'exotic-font-manager'),
                'selectors' => [
                    'body.single-escort .profile-title',
                    'body.single-escort .profile-hero__meta',
                    'body.single-escort .profile-account__title',
                    'body.single-escort .profile-account__card-label',
                    'body.single-escort .profile-account__card-value',
                    'body.single-escort .profile-mobile-cta__name',
                    'body.single-escort .contact',
                    'body.single-escort .b-label',
                ],
            ],
        ];

        return apply_filters('exotic_font_manager_target_presets', $presets);
    }

    public static function get_preset($key)
    {
        $presets = self::get_presets();
        $key = sanitize_key((string) $key);

        return isset($presets[$key]) ? $presets[$key] : null;
    }

    public static function get_labels()
    {
        $labels = [];

        foreach (self::get_presets() as $key => $preset) {
            $labels[$key] = isset($preset['label']) ? $preset['label'] : $key;
        }

        return $labels;
    }
}
