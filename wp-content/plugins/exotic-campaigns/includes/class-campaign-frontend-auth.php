<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_Campaign_Frontend_Auth
{
    public static function register()
    {
        add_shortcode('exotic_campaigns_dashboard', [__CLASS__, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'maybe_enqueue_assets'], 100);
    }

    public static function maybe_enqueue_assets()
    {
        if (!is_singular()) {
            return;
        }

        $post = get_post();
        if (!$post instanceof WP_Post) {
            return;
        }

        if (!has_shortcode($post->post_content, 'exotic_campaigns_dashboard')) {
            return;
        }

        if (!is_user_logged_in() || !Exotic_Campaign_Post_Type::user_can_manage_campaigns()) {
            return;
        }

        add_filter('script_loader_src', [__CLASS__, 'filter_conflicting_script_src'], 9999, 2);
        add_filter('script_loader_tag', [__CLASS__, 'filter_conflicting_script_tag'], 9999, 3);
        self::enqueue_assets();
    }

    public static function filter_conflicting_script_src($src, $handle)
    {
        $blocked_src_fragments = [
            'assets.anytrack.io',
            'cdn.webpushr.com',
            '/themes/escortwp/js/dolceescort.js',
        ];

        foreach ($blocked_src_fragments as $fragment) {
            if (is_string($src) && strpos($src, $fragment) !== false) {
                return '';
            }
        }

        return $src;
    }

    public static function filter_conflicting_script_tag($tag, $handle, $src)
    {
        $blocked_handles = [
            'dolcejs',
            'dolcejs-js',
            'webpushr-jssdk',
            'webpushr-script',
        ];

        if (in_array((string) $handle, $blocked_handles, true)) {
            return '';
        }

        $blocked_src_fragments = [
            'assets.anytrack.io',
            'cdn.webpushr.com',
            '/themes/escortwp/js/dolceescort.js',
        ];

        foreach ($blocked_src_fragments as $fragment) {
            if (is_string($src) && strpos($src, $fragment) !== false) {
                return '';
            }
        }

        return $tag;
    }

    public static function render_shortcode()
    {
        if (!is_user_logged_in()) {
            $login_url = wp_login_url(get_permalink());
            return sprintf(
                '<div class="exotic-campaign-message"><p>%s <a href="%s">%s</a></p></div>',
                esc_html__('Please log in to manage campaigns.', 'exotic-campaigns'),
                esc_url($login_url),
                esc_html__('Log in', 'exotic-campaigns')
            );
        }

        if (!Exotic_Campaign_Post_Type::user_can_manage_campaigns()) {
            return sprintf(
                '<div class="exotic-campaign-message"><p>%s</p></div>',
                esc_html__('Your account does not have campaign access. Contact an administrator.', 'exotic-campaigns')
            );
        }

        ob_start();
        include EXOTIC_CAMPAIGNS_PATH . 'frontend/views/dashboard.php';
        return ob_get_clean();
    }

    private static function enqueue_assets()
    {
        $css_file = EXOTIC_CAMPAIGNS_PATH . 'frontend/css/campaign-dashboard.css';
        $js_file = EXOTIC_CAMPAIGNS_PATH . 'frontend/js/campaign-dashboard.js';

        // Theme script expects checkator to exist. Provide a safe no-op on dashboard pages.
        wp_add_inline_script(
            'jquery',
            'window.jQuery=window.jQuery||window.$;if(window.jQuery&&!window.jQuery.fn.checkator){window.jQuery.fn.checkator=function(){return this;};}',
            'after'
        );

        wp_enqueue_style(
            'exotic-campaign-dashboard',
            EXOTIC_CAMPAIGNS_URL . 'frontend/css/campaign-dashboard.css',
            [],
            file_exists($css_file) ? filemtime($css_file) : EXOTIC_CAMPAIGNS_VERSION
        );

        wp_enqueue_script(
            'exotic-campaign-dashboard',
            EXOTIC_CAMPAIGNS_URL . 'frontend/js/campaign-dashboard.js',
            [],
            file_exists($js_file) ? filemtime($js_file) : EXOTIC_CAMPAIGNS_VERSION,
            true
        );

        wp_enqueue_media();

        wp_localize_script('exotic-campaign-dashboard', 'exoticCampaignDashboard', [
            'apiRoot' => rest_url('exotic-campaigns/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'messages' => [
                'deleteConfirm' => __('Delete this campaign?', 'exotic-campaigns'),
                'saveSuccess' => __('Campaign saved.', 'exotic-campaigns'),
                'deleteSuccess' => __('Campaign deleted.', 'exotic-campaigns'),
                'errorGeneric' => __('Something went wrong. Please try again.', 'exotic-campaigns'),
            ],
        ]);
    }
}
