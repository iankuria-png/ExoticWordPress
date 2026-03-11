<?php

if (!defined('ABSPATH')) {
    exit;
}

class EFM_Plugin
{
    public static function init()
    {
        add_action('plugins_loaded', [__CLASS__, 'load_textdomain']);
        add_action('wp_enqueue_scripts', ['EFM_CSS_Builder', 'enqueue_dynamic_styles'], 999);
        add_filter('plugin_action_links_' . plugin_basename(EXOTIC_FONT_MANAGER_PATH . 'exotic-font-manager.php'), [__CLASS__, 'plugin_action_links']);

        if (is_admin()) {
            EFM_Admin_Page::register();
        }
    }

    public static function activate()
    {
        EFM_Settings_Repository::ensure_bootstrap_settings();
    }

    public static function deactivate()
    {
        // Keep settings to allow safe re-activation without data loss.
    }

    public static function load_textdomain()
    {
        load_plugin_textdomain('exotic-font-manager', false, dirname(plugin_basename(EXOTIC_FONT_MANAGER_PATH . 'exotic-font-manager.php')) . '/languages');
    }

    public static function plugin_action_links($links)
    {
        if (!current_user_can(EFM_Admin_Page::settings_capability())) {
            return $links;
        }

        $settings_link = '<a href="' . esc_url(EFM_Admin_Page::settings_page_url()) . '">' . esc_html__('Settings', 'exotic-font-manager') . '</a>';
        array_unshift($links, $settings_link);

        return $links;
    }
}
