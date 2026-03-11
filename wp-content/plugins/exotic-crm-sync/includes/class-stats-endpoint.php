<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_CRM_Stats_Endpoint
{
    private $post_type;

    public function __construct()
    {
        $this->post_type = get_option('taxonomy_profile_url', 'escort');
    }

    public function register_routes($namespace)
    {
        register_rest_route($namespace, '/stats', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_stats'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);
    }

    public function check_permissions()
    {
        return current_user_can('manage_options');
    }

    public function get_stats($request)
    {
        global $wpdb;

        $post_type = $this->post_type;

        // Count by status
        $counts = wp_count_posts($post_type);

        // Needs payment count
        $needs_payment = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT p.ID)
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = %s
             AND p.post_status IN ('private', 'draft')
             AND pm.meta_key = 'needs_payment'
             AND pm.meta_value = '1'",
            $post_type
        ));

        // Premium count
        $premium_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT p.ID)
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = %s
             AND p.post_status = 'publish'
             AND pm.meta_key = 'premium'
             AND pm.meta_value = '1'",
            $post_type
        ));

        // Featured count
        $featured_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT p.ID)
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = %s
             AND p.post_status = 'publish'
             AND pm.meta_key = 'featured'
             AND pm.meta_value = '1'",
            $post_type
        ));

        // Verified count
        $verified_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT p.ID)
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = %s
             AND pm.meta_key = 'verified'
             AND pm.meta_value = '1'",
            $post_type
        ));

        return rest_ensure_response([
            'total'          => (int) ($counts->publish + $counts->private + $counts->draft + $counts->pending),
            'active'         => (int) $counts->publish,
            'private'        => (int) $counts->private,
            'draft'          => (int) $counts->draft,
            'needs_payment'  => $needs_payment,
            'premium'        => $premium_count,
            'featured'       => $featured_count,
            'verified'       => $verified_count,
        ]);
    }
}
