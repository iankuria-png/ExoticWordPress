<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$campaign_post_type = 'campaign';
$campaign_role = 'campaign_manager';
$campaign_capability = 'manage_campaigns';
$cron_hook = 'exotic_campaigns_check_schedule';

$admin_role = get_role('administrator');
if ($admin_role instanceof WP_Role) {
    $admin_role->remove_cap($campaign_capability);
}

remove_role($campaign_role);

wp_clear_scheduled_hook($cron_hook);

$campaign_ids = get_posts([
    'post_type' => $campaign_post_type,
    'post_status' => 'any',
    'fields' => 'ids',
    'posts_per_page' => -1,
    'no_found_rows' => true,
]);

foreach ($campaign_ids as $campaign_id) {
    wp_delete_post((int) $campaign_id, true);
}

global $wpdb;
$table = $wpdb->prefix . 'campaign_daily_stats';
$wpdb->query("DROP TABLE IF EXISTS {$table}");

delete_option('_exotic_campaigns_migrated');
delete_option('exotic_campaigns_trusted_proxy_header');
