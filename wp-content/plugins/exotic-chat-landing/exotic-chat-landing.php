<?php
/**
 * Plugin Name: Exotic Chat Landing
 * Description: Plug-and-play modern /chat landing route for Support Board Cloud.
 * Version: 1.1.0
 * Author: Exotic Online
 * Text Domain: exotic-chat-landing
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EXOTIC_CHAT_LANDING_VERSION', '1.1.0');
define('EXOTIC_CHAT_LANDING_PATH', plugin_dir_path(__FILE__));
define('EXOTIC_CHAT_LANDING_URL', plugin_dir_url(__FILE__));

require_once EXOTIC_CHAT_LANDING_PATH . 'includes/class-exotic-chat-country-registry.php';
require_once EXOTIC_CHAT_LANDING_PATH . 'includes/class-exotic-chat-router.php';
require_once EXOTIC_CHAT_LANDING_PATH . 'includes/class-exotic-chat-settings.php';

final class Exotic_Chat_Landing_Plugin
{
    public static function init(): void
    {
        add_action('init', ['Exotic_Chat_Router', 'register_rewrite']);
        add_filter('query_vars', ['Exotic_Chat_Router', 'register_query_vars']);
        add_action('template_redirect', ['Exotic_Chat_Router', 'maybe_render_template']);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [__CLASS__, 'plugin_action_links']);

        if (is_admin()) {
            Exotic_Chat_Settings::register();
        }

        add_action('plugins_loaded', [__CLASS__, 'load_textdomain']);
    }

    public static function load_textdomain(): void
    {
        load_plugin_textdomain('exotic-chat-landing', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public static function activate(): void
    {
        Exotic_Chat_Settings::ensure_bootstrap_settings();
        Exotic_Chat_Router::register_rewrite();
        flush_rewrite_rules();
    }

    public static function deactivate(): void
    {
        flush_rewrite_rules();
    }

    /**
     * @param array<int, string> $links
     * @return array<int, string>
     */
    public static function plugin_action_links(array $links): array
    {
        if (!current_user_can(Exotic_Chat_Settings::settings_capability())) {
            return $links;
        }

        $url = Exotic_Chat_Settings::settings_page_url();
        $settings_link = '<a href="' . esc_url($url) . '">' . esc_html__('Settings', 'exotic-chat-landing') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}

register_activation_hook(__FILE__, ['Exotic_Chat_Landing_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['Exotic_Chat_Landing_Plugin', 'deactivate']);

Exotic_Chat_Landing_Plugin::init();
