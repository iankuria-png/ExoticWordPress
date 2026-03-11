<?php
/**
 * Plugin Name: Exotic CRM Sync
 * Description: REST API endpoints for Exotic Sales CRM integration
 * Version: 1.0.0
 * Author: Ian Kuria - Product Manager - Exotic Online
 * Text Domain: exotic-crm-sync
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EXOTIC_CRM_SYNC_VERSION', '1.0.0');
define('EXOTIC_CRM_SYNC_PATH', plugin_dir_path(__FILE__));

// Load endpoint classes
require_once EXOTIC_CRM_SYNC_PATH . 'includes/class-client-endpoint.php';
require_once EXOTIC_CRM_SYNC_PATH . 'includes/class-client-update-endpoint.php';
require_once EXOTIC_CRM_SYNC_PATH . 'includes/class-activation-endpoint.php';
require_once EXOTIC_CRM_SYNC_PATH . 'includes/class-expiry-endpoint.php';
require_once EXOTIC_CRM_SYNC_PATH . 'includes/class-media-endpoint.php';
require_once EXOTIC_CRM_SYNC_PATH . 'includes/class-stats-endpoint.php';
require_once EXOTIC_CRM_SYNC_PATH . 'includes/class-wallet-sync-endpoint.php';
require_once EXOTIC_CRM_SYNC_PATH . 'includes/class-wallet-settings-page.php';

/**
 * Register all REST API routes
 */
add_action('rest_api_init', function () {
    $namespace = 'exotic-crm-sync/v1';

    // Client endpoints
    $clients = new Exotic_CRM_Client_Endpoint();
    $clients->register_routes($namespace);

    $clientUpdate = new Exotic_CRM_Client_Update_Endpoint();
    $clientUpdate->register_routes($namespace);

    // Activation endpoints
    $activation = new Exotic_CRM_Activation_Endpoint();
    $activation->register_routes($namespace);

    // Expiry endpoints
    $expiry = new Exotic_CRM_Expiry_Endpoint();
    $expiry->register_routes($namespace);

    $media = new Exotic_CRM_Media_Endpoint();
    $media->register_routes($namespace);

    // Stats endpoints
    $stats = new Exotic_CRM_Stats_Endpoint();
    $stats->register_routes($namespace);

    // Wallet sync endpoints
    $walletSync = new Exotic_CRM_Wallet_Sync_Endpoint();
    $walletSync->register_routes($namespace);
});

add_action('plugins_loaded', function () {
    if (is_admin()) {
        new Exotic_CRM_Wallet_Settings_Page();
    }
});
