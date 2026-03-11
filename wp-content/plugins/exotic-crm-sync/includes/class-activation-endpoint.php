<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_CRM_Activation_Endpoint
{
    private $post_type;

    public function __construct()
    {
        $this->post_type = get_option('taxonomy_profile_url', 'escort');
    }

    public function register_routes($namespace)
    {
        register_rest_route($namespace, '/clients/(?P<post_id>\d+)/activate', [
            'methods'             => 'POST',
            'callback'            => [$this, 'activate'],
            'permission_callback' => [$this, 'check_permissions'],
            'args'                => [
                'product_type'  => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
                'duration_days' => ['required' => true, 'sanitize_callback' => 'absint'],
                'crm_deal_id'   => ['required' => false, 'sanitize_callback' => 'absint'],
            ],
        ]);

        register_rest_route($namespace, '/clients/(?P<post_id>\d+)/deactivate', [
            'methods'             => 'POST',
            'callback'            => [$this, 'deactivate'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);

        register_rest_route($namespace, '/clients/(?P<post_id>\d+)/extend', [
            'methods'             => 'POST',
            'callback'            => [$this, 'extend'],
            'permission_callback' => [$this, 'check_permissions'],
            'args'                => [
                'additional_days' => ['required' => true, 'sanitize_callback' => 'absint'],
            ],
        ]);
    }

    public function check_permissions()
    {
        return current_user_can('manage_options');
    }

    public function activate($request)
    {
        $post_id      = (int) $request->get_param('post_id');
        $product_type = $request->get_param('product_type'); // basic, premium, vip
        $duration     = (int) $request->get_param('duration_days');
        $crm_deal_id  = $request->get_param('crm_deal_id');

        $post = get_post($post_id);
        if (!$post || $post->post_type !== $this->post_type) {
            return new WP_Error('not_found', 'Client not found', ['status' => 404]);
        }

        $before_status = $post->post_status;
        $expiry = time() + ($duration * 86400);
        $now = current_time('mysql');

        // Set post to published
        wp_update_post([
            'ID'          => $post_id,
            'post_status' => 'publish',
        ]);

        // Remove payment-required flags
        delete_post_meta($post_id, 'notactive');
        delete_post_meta($post_id, 'needs_payment');

        // Set expiry
        update_post_meta($post_id, 'escort_expire', $expiry);
        update_post_meta($post_id, 'subscription_start', $now);
        update_post_meta($post_id, 'subscription_end', date('Y-m-d H:i:s', $expiry));

        // Set product-type flags
        if ($product_type === 'premium' || $product_type === 'vip') {
            update_post_meta($post_id, 'premium', 1);
            update_post_meta($post_id, 'premium_expire', $expiry);
        }
        if ($product_type === 'vip') {
            update_post_meta($post_id, 'featured', 1);
            update_post_meta($post_id, 'featured_expire', $expiry);
        }
        if ($product_type === 'basic') {
            delete_post_meta($post_id, 'premium');
            delete_post_meta($post_id, 'featured');
        }

        // Track CRM deal
        if ($crm_deal_id) {
            update_post_meta($post_id, 'crm_deal_id', $crm_deal_id);
        }

        return rest_ensure_response([
            'success'       => true,
            'post_id'       => $post_id,
            'before_status' => $before_status,
            'after_status'  => 'publish',
            'escort_expire' => $expiry,
            'product_type'  => $product_type,
        ]);
    }

    public function deactivate($request)
    {
        $post_id = (int) $request->get_param('post_id');

        $post = get_post($post_id);
        if (!$post || $post->post_type !== $this->post_type) {
            return new WP_Error('not_found', 'Client not found', ['status' => 404]);
        }

        $before_status = $post->post_status;

        wp_update_post([
            'ID'          => $post_id,
            'post_status' => 'private',
        ]);

        update_post_meta($post_id, 'notactive', 1);

        return rest_ensure_response([
            'success'       => true,
            'post_id'       => $post_id,
            'before_status' => $before_status,
            'after_status'  => 'private',
        ]);
    }

    public function extend($request)
    {
        $post_id         = (int) $request->get_param('post_id');
        $additional_days = (int) $request->get_param('additional_days');

        $post = get_post($post_id);
        if (!$post || $post->post_type !== $this->post_type) {
            return new WP_Error('not_found', 'Client not found', ['status' => 404]);
        }

        $current_expire = (int) get_post_meta($post_id, 'escort_expire', true);
        $base = max($current_expire, time());
        $new_expire = $base + ($additional_days * 86400);

        update_post_meta($post_id, 'escort_expire', $new_expire);
        update_post_meta($post_id, 'subscription_end', date('Y-m-d H:i:s', $new_expire));

        // Also extend premium/featured if they exist
        $premium_expire = get_post_meta($post_id, 'premium_expire', true);
        if ($premium_expire) {
            $new_premium = max((int) $premium_expire, time()) + ($additional_days * 86400);
            update_post_meta($post_id, 'premium_expire', $new_premium);
        }

        $featured_expire = get_post_meta($post_id, 'featured_expire', true);
        if ($featured_expire) {
            $new_featured = max((int) $featured_expire, time()) + ($additional_days * 86400);
            update_post_meta($post_id, 'featured_expire', $new_featured);
        }

        return rest_ensure_response([
            'success'        => true,
            'post_id'        => $post_id,
            'previous_expire'=> $current_expire,
            'new_expire'     => $new_expire,
            'days_added'     => $additional_days,
        ]);
    }
}
