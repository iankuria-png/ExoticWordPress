<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_CRM_Expiry_Endpoint
{
    private $post_type;

    public function __construct()
    {
        $this->post_type = get_option('taxonomy_profile_url', 'escort');
    }

    public function register_routes($namespace)
    {
        register_rest_route($namespace, '/expiring', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_expiring'],
            'permission_callback' => [$this, 'check_permissions'],
            'args'                => [
                'days' => [
                    'default'           => 14,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
    }

    public function check_permissions()
    {
        return current_user_can('manage_options');
    }

    public function get_expiring($request)
    {
        $days      = (int) $request->get_param('days');
        $now       = time();
        $threshold = $now + ($days * 86400);

        $args = [
            'post_type'      => $this->post_type,
            'post_status'    => 'publish',
            'posts_per_page' => 200,
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => 'escort_expire',
                    'value'   => $now,
                    'compare' => '>',
                    'type'    => 'NUMERIC',
                ],
                [
                    'key'     => 'escort_expire',
                    'value'   => $threshold,
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                ],
            ],
            'orderby'  => 'meta_value_num',
            'meta_key' => 'escort_expire',
            'order'    => 'ASC',
        ];

        $query = new WP_Query($args);
        $expiring = [];

        foreach ($query->posts as $post) {
            $expire = (int) get_post_meta($post->ID, 'escort_expire', true);
            $expiring[] = [
                'wp_post_id'    => $post->ID,
                'name'          => $post->post_title,
                'phone'         => get_post_meta($post->ID, 'phone', true),
                'escort_expire' => $expire,
                'days_left'     => max(0, (int) ceil(($expire - $now) / 86400)),
                'premium'       => (bool) get_post_meta($post->ID, 'premium', true),
                'featured'      => (bool) get_post_meta($post->ID, 'featured', true),
            ];
        }

        return rest_ensure_response([
            'data'  => $expiring,
            'total' => count($expiring),
            'days'  => $days,
        ]);
    }
}
