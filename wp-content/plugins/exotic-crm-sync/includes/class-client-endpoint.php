<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_CRM_Client_Endpoint
{
    private $post_type;

    public function __construct()
    {
        $this->post_type = get_option('taxonomy_profile_url', 'escort');
    }

    public function register_routes($namespace)
    {
        register_rest_route($namespace, '/clients', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_clients'],
            'permission_callback' => [$this, 'check_permissions'],
            'args'                => [
                'per_page' => [
                    'default'           => 50,
                    'sanitize_callback' => 'absint',
                ],
                'page' => [
                    'default'           => 1,
                    'sanitize_callback' => 'absint',
                ],
                'modified_after' => [
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        register_rest_route($namespace, '/clients/(?P<post_id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_client'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);
    }

    public function check_permissions()
    {
        return current_user_can('manage_options');
    }

    public function get_clients($request)
    {
        $per_page       = min($request->get_param('per_page'), 200);
        $page           = $request->get_param('page');
        $modified_after = $request->get_param('modified_after');

        $args = [
            'post_type'      => $this->post_type,
            'post_status'    => ['publish', 'private', 'draft', 'pending'],
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ];

        if ($modified_after) {
            $args['date_query'] = [
                [
                    'column' => 'post_modified_gmt',
                    'after'  => $modified_after,
                ],
            ];
        }

        $query = new WP_Query($args);
        $clients = [];

        foreach ($query->posts as $post) {
            $clients[] = $this->format_client($post);
        }

        return rest_ensure_response([
            'data'  => $clients,
            'total' => (int) $query->found_posts,
            'pages' => (int) $query->max_num_pages,
            'page'  => (int) $page,
        ]);
    }

    public function get_client($request)
    {
        $post_id = (int) $request->get_param('post_id');
        $post    = get_post($post_id);

        if (!$post || $post->post_type !== $this->post_type) {
            return new WP_Error('not_found', 'Client not found', ['status' => 404]);
        }

        return rest_ensure_response($this->format_client($post, true));
    }

    private function format_client($post, $full = false)
    {
        $post_id = $post->ID;
        $author_id = $post->post_author;

        // Get main image
        $main_image = '';
        $thumbnail_id = get_post_thumbnail_id($post_id);
        if ($thumbnail_id) {
            $main_image = wp_get_attachment_url($thumbnail_id);
        }

        $data = [
            'wp_post_id'     => $post_id,
            'wp_user_id'     => (int) $author_id,
            'name'           => $post->post_title,
            'post_status'    => $post->post_status,
            'phone'          => get_post_meta($post_id, 'phone', true),
            'email'          => get_the_author_meta('user_email', $author_id),
            'city'           => $this->resolve_city_name($post_id),
            'premium'        => (bool) get_post_meta($post_id, 'premium', true),
            'premium_expire' => get_post_meta($post_id, 'premium_expire', true) ?: null,
            'featured'       => (bool) get_post_meta($post_id, 'featured', true),
            'featured_expire'=> get_post_meta($post_id, 'featured_expire', true) ?: null,
            'escort_expire'  => get_post_meta($post_id, 'escort_expire', true) ?: null,
            'verified'       => (bool) get_post_meta($post_id, 'verified', true),
            'needs_payment'  => (bool) get_post_meta($post_id, 'needs_payment', true),
            'last_online'    => $this->resolve_last_online($author_id),
            'main_image_url' => $main_image,
            'modified_at'    => $post->post_modified_gmt,
        ];

        if ($full) {
            $cityTerm = $this->resolve_city_term($post_id);
            $meta = $this->collect_profile_meta($post_id);

            $data['subscription_start'] = get_post_meta($post_id, 'subscription_start', true) ?: null;
            $data['subscription_end']   = get_post_meta($post_id, 'subscription_end', true) ?: null;
            $data['notactive']          = (bool) get_post_meta($post_id, 'notactive', true);
            $data['post'] = [
                'id' => (int) $post_id,
                'title' => (string) $post->post_title,
                'status' => (string) $post->post_status,
                'content' => (string) $post->post_content,
                'excerpt' => (string) $post->post_excerpt,
                'author_id' => (int) $author_id,
                'modified_at' => (string) $post->post_modified_gmt,
            ];
            $data['taxonomies'] = [
                'city' => $cityTerm ? [
                    'id' => (int) $cityTerm->term_id,
                    'name' => (string) $cityTerm->name,
                    'slug' => (string) $cityTerm->slug,
                ] : null,
                'tags' => wp_get_post_terms($post_id, 'post_tag', ['fields' => 'names']),
            ];
            $data['meta'] = $meta;
            $data['redacted_keys'] = ['secret', 'ip', 'hostname', 'upload_folder'];
        }

        return $data;
    }

    private function get_first_term($post_id, $taxonomy)
    {
        $terms = wp_get_post_terms($post_id, $taxonomy, ['number' => 1]);
        return (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : '';
    }

    private function resolve_city_name($post_id)
    {
        // Canonical taxonomy for city/location in this WP install.
        $city = $this->get_first_term($post_id, 'escorts-from');
        if ($city !== '') {
            return $city;
        }

        // Legacy fallback: some rows store city as postmeta term ID or raw value.
        $city_meta = get_post_meta($post_id, 'city', true);
        if ($city_meta === null || $city_meta === '') {
            return '';
        }

        if (is_numeric($city_meta)) {
            $term = get_term((int) $city_meta, 'escorts-from');
            if ($term && !is_wp_error($term)) {
                return (string) $term->name;
            }
        }

        $city_meta = sanitize_text_field((string) $city_meta);
        if ($city_meta === '') {
            return '';
        }

        $term = get_term_by('slug', $city_meta, 'escorts-from');
        if ($term && !is_wp_error($term)) {
            return (string) $term->name;
        }

        $term = get_term_by('name', $city_meta, 'escorts-from');
        if ($term && !is_wp_error($term)) {
            return (string) $term->name;
        }

        return $city_meta;
    }

    private function resolve_city_term($post_id)
    {
        $terms = wp_get_post_terms($post_id, 'escorts-from', ['number' => 1]);
        if (!is_wp_error($terms) && !empty($terms)) {
            return $terms[0];
        }

        $city_meta = get_post_meta($post_id, 'city', true);
        if (is_numeric($city_meta)) {
            $term = get_term((int) $city_meta, 'escorts-from');
            if ($term && !is_wp_error($term)) {
                return $term;
            }
        }

        $raw = sanitize_text_field((string) $city_meta);
        if ($raw === '') {
            return null;
        }

        $term = get_term_by('slug', $raw, 'escorts-from');
        if ($term && !is_wp_error($term)) {
            return $term;
        }

        $term = get_term_by('name', $raw, 'escorts-from');
        if ($term && !is_wp_error($term)) {
            return $term;
        }

        return null;
    }

    private function collect_profile_meta($post_id)
    {
        $raw = get_post_meta($post_id);
        $meta = [];
        $redacted = ['secret', 'ip', 'hostname', 'upload_folder'];

        foreach ($raw as $key => $values) {
            if (in_array($key, $redacted, true)) {
                continue;
            }

            if (strpos($key, '_') === 0 && !in_array($key, ['_thumbnail_id'], true)) {
                continue;
            }

            $value = isset($values[0]) ? maybe_unserialize($values[0]) : null;
            if ($value === '' || $value === null) {
                $meta[$key] = null;
                continue;
            }

            $meta[$key] = $value;
        }

        $mainImageId = get_post_meta($post_id, 'main_image_id', true);
        if ($mainImageId) {
            $meta['main_image_id'] = (int) $mainImageId;
        }

        return $meta;
    }

    private function resolve_last_online($author_id)
    {
        $last_online = get_user_meta($author_id, 'last_online', true);
        if ($last_online !== null && $last_online !== '') {
            return $last_online;
        }

        $fallback = get_user_meta($author_id, 'ppmwp_last_activity', true);
        return ($fallback !== null && $fallback !== '') ? $fallback : null;
    }
}
