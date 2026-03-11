<?php
/**
 * Plugin Name: Exotic Font Manager
 * Description: Non-technical typography controls for site-wide, page-level, and section-level font management.
 * Version: 1.0.0
 * Author: Exotic Online
 * Text Domain: exotic-font-manager
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EXOTIC_FONT_MANAGER_VERSION', '1.0.0');
define('EXOTIC_FONT_MANAGER_PATH', plugin_dir_path(__FILE__));
define('EXOTIC_FONT_MANAGER_URL', plugin_dir_url(__FILE__));

require_once EXOTIC_FONT_MANAGER_PATH . 'includes/class-efm-font-library.php';
require_once EXOTIC_FONT_MANAGER_PATH . 'includes/class-efm-target-presets.php';
require_once EXOTIC_FONT_MANAGER_PATH . 'includes/class-efm-settings-repository.php';
require_once EXOTIC_FONT_MANAGER_PATH . 'includes/class-efm-google-installer.php';
require_once EXOTIC_FONT_MANAGER_PATH . 'includes/class-efm-css-builder.php';
require_once EXOTIC_FONT_MANAGER_PATH . 'includes/class-efm-admin-page.php';
require_once EXOTIC_FONT_MANAGER_PATH . 'includes/class-efm-plugin.php';

register_activation_hook(__FILE__, ['EFM_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['EFM_Plugin', 'deactivate']);

EFM_Plugin::init();
