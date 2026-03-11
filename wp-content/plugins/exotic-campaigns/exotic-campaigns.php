<?php
/**
 * Plugin Name: Exotic Campaigns
 * Description: Dynamic ad campaign management for homepage cards with scheduling and analytics.
 * Version: 1.0.0
 * Author: Exotic Online
 * Text Domain: exotic-campaigns
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EXOTIC_CAMPAIGNS_VERSION', '1.0.0');
define('EXOTIC_CAMPAIGNS_PATH', plugin_dir_path(__FILE__));
define('EXOTIC_CAMPAIGNS_URL', plugin_dir_url(__FILE__));

require_once EXOTIC_CAMPAIGNS_PATH . 'includes/class-campaign-post-type.php';
require_once EXOTIC_CAMPAIGNS_PATH . 'includes/class-campaign-renderer.php';

if (file_exists(EXOTIC_CAMPAIGNS_PATH . 'includes/class-campaign-admin-page.php')) {
    require_once EXOTIC_CAMPAIGNS_PATH . 'includes/class-campaign-admin-page.php';
}
if (file_exists(EXOTIC_CAMPAIGNS_PATH . 'includes/class-campaign-rest-campaigns.php')) {
    require_once EXOTIC_CAMPAIGNS_PATH . 'includes/class-campaign-rest-campaigns.php';
}
if (file_exists(EXOTIC_CAMPAIGNS_PATH . 'includes/class-campaign-rest-tracking.php')) {
    require_once EXOTIC_CAMPAIGNS_PATH . 'includes/class-campaign-rest-tracking.php';
}
if (file_exists(EXOTIC_CAMPAIGNS_PATH . 'includes/class-campaign-rest-analytics.php')) {
    require_once EXOTIC_CAMPAIGNS_PATH . 'includes/class-campaign-rest-analytics.php';
}
if (file_exists(EXOTIC_CAMPAIGNS_PATH . 'includes/class-campaign-scheduler.php')) {
    require_once EXOTIC_CAMPAIGNS_PATH . 'includes/class-campaign-scheduler.php';
}
if (file_exists(EXOTIC_CAMPAIGNS_PATH . 'includes/class-campaign-frontend-auth.php')) {
    require_once EXOTIC_CAMPAIGNS_PATH . 'includes/class-campaign-frontend-auth.php';
}

add_action('plugins_loaded', static function () {
    load_plugin_textdomain('exotic-campaigns', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

add_action('init', ['Exotic_Campaign_Post_Type', 'register_post_type']);
add_action('init', ['Exotic_Campaign_Post_Type', 'register_meta_keys']);
add_action('init', ['Exotic_Campaign_Post_Type', 'ensure_roles_and_caps']);
add_action('wp_enqueue_scripts', ['Exotic_Campaign_Renderer', 'enqueue_frontend_assets']);
add_action('after_setup_theme', ['Exotic_Campaign_Renderer', 'register_image_sizes']);

register_activation_hook(__FILE__, static function () {
    Exotic_Campaign_Post_Type::activate();
    if (class_exists('Exotic_Campaign_Scheduler')) {
        Exotic_Campaign_Scheduler::activate();
    }
});

register_deactivation_hook(__FILE__, static function () {
    if (class_exists('Exotic_Campaign_Scheduler')) {
        Exotic_Campaign_Scheduler::deactivate();
    }
    Exotic_Campaign_Post_Type::deactivate();
});

add_action('rest_api_init', static function () {
    $namespace = 'exotic-campaigns/v1';

    if (class_exists('Exotic_Campaign_REST_Campaigns')) {
        (new Exotic_Campaign_REST_Campaigns())->register_routes($namespace);
    }

    if (class_exists('Exotic_Campaign_REST_Tracking')) {
        (new Exotic_Campaign_REST_Tracking())->register_routes($namespace);
    }

    if (class_exists('Exotic_Campaign_REST_Analytics')) {
        (new Exotic_Campaign_REST_Analytics())->register_routes($namespace);
    }
});

add_action('plugins_loaded', static function () {
    if (is_admin() && class_exists('Exotic_Campaign_Admin_Page')) {
        Exotic_Campaign_Admin_Page::register();
    }

    if (class_exists('Exotic_Campaign_Frontend_Auth')) {
        Exotic_Campaign_Frontend_Auth::register();
    }

    if (class_exists('Exotic_Campaign_Scheduler')) {
        Exotic_Campaign_Scheduler::register_hooks();
    }
});
