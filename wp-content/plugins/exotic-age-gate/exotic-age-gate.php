<?php
/**
 * Plugin Name: Exotic Age Gate
 * Description: Centralized age-verification modal for Exotic country sites with clean theme handoff.
 * Version: 1.0.0
 * Author: Exotic Online
 * Text Domain: exotic-age-gate
 */

if (!defined('ABSPATH')) {
	exit;
}

define('EXOTIC_AGE_GATE_VERSION', '1.0.0');
define('EXOTIC_AGE_GATE_PATH', plugin_dir_path(__FILE__));
define('EXOTIC_AGE_GATE_URL', plugin_dir_url(__FILE__));

require_once EXOTIC_AGE_GATE_PATH . 'includes/class-exotic-age-gate-site-presets.php';
require_once EXOTIC_AGE_GATE_PATH . 'includes/class-exotic-age-gate-settings.php';
require_once EXOTIC_AGE_GATE_PATH . 'includes/class-exotic-age-gate-frontend.php';
require_once EXOTIC_AGE_GATE_PATH . 'includes/class-exotic-age-gate-plugin.php';

register_activation_hook(__FILE__, array('Exotic_Age_Gate_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('Exotic_Age_Gate_Plugin', 'deactivate'));

Exotic_Age_Gate_Plugin::init();
