<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_Campaign_REST_Campaigns
{
    public function register_routes($namespace)
    {
        register_rest_route($namespace, '/campaigns', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'list_campaigns'],
                'permission_callback' => [$this, 'permissions_check'],
                'args' => [
                    'status' => [
                        'sanitize_callback' => [Exotic_Campaign_Post_Type::class, 'sanitize_status'],
                    ],
                    'per_page' => [
                        'default' => 20,
                        'sanitize_callback' => 'absint',
                    ],
                    'page' => [
                        'default' => 1,
                        'sanitize_callback' => 'absint',
                    ],
                ],
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_campaign'],
                'permission_callback' => [$this, 'permissions_check'],
                'args' => $this->campaign_args(),
            ],
        ]);

        register_rest_route($namespace, '/campaigns/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_campaign'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_campaign'],
                'permission_callback' => [$this, 'permissions_check'],
                'args' => $this->campaign_args(),
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_campaign'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        register_rest_route($namespace, '/campaigns/(?P<id>\d+)/status', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'change_status'],
                'permission_callback' => [$this, 'permissions_check'],
                'args' => [
                    'status' => [
                        'required' => true,
                        'sanitize_callback' => [Exotic_Campaign_Post_Type::class, 'sanitize_status'],
                    ],
                ],
            ],
        ]);
    }

    public function permissions_check()
    {
        return Exotic_Campaign_Post_Type::user_can_manage_campaigns();
    }

    public function list_campaigns(WP_REST_Request $request)
    {
        $status = (string) $request->get_param('status');
        $per_page = max(1, min(100, absint($request->get_param('per_page'))));
        $page = max(1, absint($request->get_param('page')));

        $args = [
            'post_type' => Exotic_Campaign_Post_Type::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => [
                'meta_value_num' => 'ASC',
                'date' => 'DESC',
            ],
            'meta_key' => '_campaign_priority',
        ];

        if (in_array($status, ['active', 'scheduled', 'paused', 'expired'], true)) {
            $args['meta_query'] = [
                [
                    'key' => '_campaign_status',
                    'value' => $status,
                    'compare' => '=',
                ],
            ];
        }

        $query = new WP_Query($args);
        $items = [];

        foreach ($query->posts as $post) {
            $items[] = $this->map_campaign($post);
        }

        return rest_ensure_response([
            'items' => $items,
            'total' => (int) $query->found_posts,
            'pages' => (int) $query->max_num_pages,
            'page' => $page,
        ]);
    }

    public function get_campaign(WP_REST_Request $request)
    {
        $campaign = $this->resolve_campaign(absint($request['id']));
        if (is_wp_error($campaign)) {
            return $campaign;
        }

        return rest_ensure_response($this->map_campaign($campaign));
    }

    public function create_campaign(WP_REST_Request $request)
    {
        $payload = $this->extract_payload($request);

        $title = isset($payload['post_title']) ? sanitize_text_field((string) $payload['post_title']) : '';
        if ($title === '') {
            return new WP_Error('invalid_title', 'post_title is required.', ['status' => 422]);
        }

        $campaign_id = wp_insert_post([
            'post_type' => Exotic_Campaign_Post_Type::POST_TYPE,
            'post_status' => 'publish',
            'post_title' => $title,
        ], true);

        if (is_wp_error($campaign_id)) {
            return new WP_Error('campaign_create_failed', $campaign_id->get_error_message(), ['status' => 422]);
        }

        $this->save_payload_to_meta((int) $campaign_id, $payload, false);

        $campaign = get_post((int) $campaign_id);
        return rest_ensure_response($this->map_campaign($campaign));
    }

    public function update_campaign(WP_REST_Request $request)
    {
        $campaign = $this->resolve_campaign(absint($request['id']));
        if (is_wp_error($campaign)) {
            return $campaign;
        }

        $payload = $this->extract_payload($request);

        if (isset($payload['post_title'])) {
            $title = sanitize_text_field((string) $payload['post_title']);
            if ($title !== '') {
                wp_update_post([
                    'ID' => (int) $campaign->ID,
                    'post_title' => $title,
                ]);
            }
        }

        $this->save_payload_to_meta((int) $campaign->ID, $payload, true);

        $campaign = get_post((int) $campaign->ID);
        return rest_ensure_response($this->map_campaign($campaign));
    }

    public function delete_campaign(WP_REST_Request $request)
    {
        $campaign = $this->resolve_campaign(absint($request['id']));
        if (is_wp_error($campaign)) {
            return $campaign;
        }

        $deleted = wp_delete_post((int) $campaign->ID, true);

        if (!$deleted) {
            return new WP_Error('campaign_delete_failed', 'Campaign could not be deleted.', ['status' => 500]);
        }

        return rest_ensure_response([
            'success' => true,
            'deleted_id' => (int) $campaign->ID,
        ]);
    }

    public function change_status(WP_REST_Request $request)
    {
        $campaign = $this->resolve_campaign(absint($request['id']));
        if (is_wp_error($campaign)) {
            return $campaign;
        }

        $status = Exotic_Campaign_Post_Type::sanitize_status((string) $request->get_param('status'));
        update_post_meta((int) $campaign->ID, '_campaign_status', $status);

        return rest_ensure_response($this->map_campaign(get_post((int) $campaign->ID)));
    }

    private function resolve_campaign($campaign_id)
    {
        $campaign_id = absint($campaign_id);
        $post = get_post($campaign_id);

        if (!$post || $post->post_type !== Exotic_Campaign_Post_Type::POST_TYPE) {
            return new WP_Error('campaign_not_found', 'Campaign not found.', ['status' => 404]);
        }

        return $post;
    }

    private function extract_payload(WP_REST_Request $request)
    {
        $json = $request->get_json_params();
        if (is_array($json) && !empty($json)) {
            return $json;
        }

        $params = $request->get_params();
        return is_array($params) ? $params : [];
    }

    private function save_payload_to_meta($campaign_id, array $payload, $is_update)
    {
        $campaign_id = absint($campaign_id);
        $existing = $this->get_existing_meta($campaign_id);

        $format = isset($payload['_campaign_format'])
            ? Exotic_Campaign_Post_Type::sanitize_format((string) $payload['_campaign_format'])
            : $existing['_campaign_format'];

        $values = [
            '_campaign_format' => $format,
            '_campaign_badge_text' => array_key_exists('_campaign_badge_text', $payload)
                ? Exotic_Campaign_Post_Type::sanitize_badge((string) $payload['_campaign_badge_text'])
                : $existing['_campaign_badge_text'],
            '_campaign_description' => array_key_exists('_campaign_description', $payload)
                ? Exotic_Campaign_Post_Type::sanitize_description((string) $payload['_campaign_description'])
                : $existing['_campaign_description'],
            '_campaign_icon_class' => array_key_exists('_campaign_icon_class', $payload)
                ? Exotic_Campaign_Post_Type::sanitize_icon_class((string) $payload['_campaign_icon_class'])
                : $existing['_campaign_icon_class'],
            '_campaign_color_primary' => array_key_exists('_campaign_color_primary', $payload)
                ? Exotic_Campaign_Post_Type::sanitize_hex_color((string) $payload['_campaign_color_primary'])
                : $existing['_campaign_color_primary'],
            '_campaign_color_secondary' => array_key_exists('_campaign_color_secondary', $payload)
                ? Exotic_Campaign_Post_Type::sanitize_hex_color_optional((string) $payload['_campaign_color_secondary'])
                : $existing['_campaign_color_secondary'],
            '_campaign_image_id' => array_key_exists('_campaign_image_id', $payload)
                ? absint($payload['_campaign_image_id'])
                : $existing['_campaign_image_id'],
            '_campaign_image_alt' => array_key_exists('_campaign_image_alt', $payload)
                ? Exotic_Campaign_Post_Type::sanitize_alt((string) $payload['_campaign_image_alt'])
                : $existing['_campaign_image_alt'],
            '_campaign_cta_text' => array_key_exists('_campaign_cta_text', $payload)
                ? Exotic_Campaign_Post_Type::sanitize_cta_text((string) $payload['_campaign_cta_text'])
                : $existing['_campaign_cta_text'],
            '_campaign_cta_url' => array_key_exists('_campaign_cta_url', $payload)
                ? Exotic_Campaign_Post_Type::sanitize_http_url((string) $payload['_campaign_cta_url'])
                : $existing['_campaign_cta_url'],
            '_campaign_cta_visible' => array_key_exists('_campaign_cta_visible', $payload)
                ? (!empty($payload['_campaign_cta_visible']) ? '1' : '0')
                : $existing['_campaign_cta_visible'],
            '_campaign_start_date' => array_key_exists('_campaign_start_date', $payload)
                ? Exotic_Campaign_Post_Type::sanitize_datetime_optional($this->normalize_datetime_input((string) $payload['_campaign_start_date']))
                : $existing['_campaign_start_date'],
            '_campaign_end_date' => array_key_exists('_campaign_end_date', $payload)
                ? Exotic_Campaign_Post_Type::sanitize_datetime_optional($this->normalize_datetime_input((string) $payload['_campaign_end_date']))
                : $existing['_campaign_end_date'],
            '_campaign_status' => array_key_exists('_campaign_status', $payload)
                ? Exotic_Campaign_Post_Type::sanitize_status((string) $payload['_campaign_status'])
                : $existing['_campaign_status'],
            '_campaign_priority' => array_key_exists('_campaign_priority', $payload)
                ? max(1, absint($payload['_campaign_priority']))
                : max(1, absint($existing['_campaign_priority'])),
        ];

        if ($format === 'card') {
            $values['_campaign_image_id'] = 0;
            $values['_campaign_image_alt'] = '';
            $values['_campaign_cta_visible'] = '1';
        }

        foreach ($values as $meta_key => $meta_value) {
            if ($is_update && !array_key_exists($meta_key, $payload) && $meta_key !== '_campaign_format') {
                continue;
            }

            update_post_meta($campaign_id, $meta_key, $meta_value);
        }

        Exotic_Campaign_Post_Type::seed_counter_meta($campaign_id);
    }

    private function get_existing_meta($campaign_id)
    {
        $defaults = [
            '_campaign_format' => 'card',
            '_campaign_badge_text' => '',
            '_campaign_description' => '',
            '_campaign_icon_class' => 'fa fa-bullhorn',
            '_campaign_color_primary' => '#AB1C2F',
            '_campaign_color_secondary' => '',
            '_campaign_image_id' => 0,
            '_campaign_image_alt' => '',
            '_campaign_cta_text' => '',
            '_campaign_cta_url' => '',
            '_campaign_cta_visible' => '1',
            '_campaign_start_date' => '',
            '_campaign_end_date' => '',
            '_campaign_status' => 'scheduled',
            '_campaign_priority' => 10,
        ];

        foreach (array_keys($defaults) as $meta_key) {
            $value = get_post_meta($campaign_id, $meta_key, true);
            if ($value !== '') {
                $defaults[$meta_key] = $value;
            }
        }

        return $defaults;
    }

    private function normalize_datetime_input($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (strpos($value, 'T') !== false) {
            $value = str_replace('T', ' ', $value);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }

        return $value;
    }

    private function campaign_args()
    {
        return [
            'post_title' => [
                'sanitize_callback' => 'sanitize_text_field',
            ],
            '_campaign_format' => [
                'sanitize_callback' => [Exotic_Campaign_Post_Type::class, 'sanitize_format'],
            ],
            '_campaign_badge_text' => [
                'sanitize_callback' => [Exotic_Campaign_Post_Type::class, 'sanitize_badge'],
            ],
            '_campaign_description' => [
                'sanitize_callback' => [Exotic_Campaign_Post_Type::class, 'sanitize_description'],
            ],
            '_campaign_icon_class' => [
                'sanitize_callback' => [Exotic_Campaign_Post_Type::class, 'sanitize_icon_class'],
            ],
            '_campaign_color_primary' => [
                'sanitize_callback' => [Exotic_Campaign_Post_Type::class, 'sanitize_hex_color'],
            ],
            '_campaign_color_secondary' => [
                'sanitize_callback' => [Exotic_Campaign_Post_Type::class, 'sanitize_hex_color_optional'],
            ],
            '_campaign_image_id' => [
                'sanitize_callback' => 'absint',
            ],
            '_campaign_image_alt' => [
                'sanitize_callback' => [Exotic_Campaign_Post_Type::class, 'sanitize_alt'],
            ],
            '_campaign_cta_text' => [
                'sanitize_callback' => [Exotic_Campaign_Post_Type::class, 'sanitize_cta_text'],
            ],
            '_campaign_cta_url' => [
                'sanitize_callback' => [Exotic_Campaign_Post_Type::class, 'sanitize_http_url'],
            ],
            '_campaign_cta_visible' => [
                'sanitize_callback' => static function ($value) {
                    return !empty($value) ? '1' : '0';
                },
            ],
            '_campaign_start_date' => [
                'sanitize_callback' => [Exotic_Campaign_Post_Type::class, 'sanitize_datetime_optional'],
            ],
            '_campaign_end_date' => [
                'sanitize_callback' => [Exotic_Campaign_Post_Type::class, 'sanitize_datetime_optional'],
            ],
            '_campaign_status' => [
                'sanitize_callback' => [Exotic_Campaign_Post_Type::class, 'sanitize_status'],
            ],
            '_campaign_priority' => [
                'sanitize_callback' => 'absint',
            ],
        ];
    }

    private function map_campaign($post)
    {
        $campaign_id = (int) $post->ID;

        $impressions = (int) get_post_meta($campaign_id, '_campaign_impressions', true);
        $clicks = (int) get_post_meta($campaign_id, '_campaign_clicks', true);

        return [
            'id' => $campaign_id,
            'title' => (string) $post->post_title,
            'format' => (string) get_post_meta($campaign_id, '_campaign_format', true),
            'badge_text' => (string) get_post_meta($campaign_id, '_campaign_badge_text', true),
            'description' => (string) get_post_meta($campaign_id, '_campaign_description', true),
            'icon_class' => (string) get_post_meta($campaign_id, '_campaign_icon_class', true),
            'color_primary' => (string) get_post_meta($campaign_id, '_campaign_color_primary', true),
            'color_secondary' => (string) get_post_meta($campaign_id, '_campaign_color_secondary', true),
            'image_id' => absint(get_post_meta($campaign_id, '_campaign_image_id', true)),
            'image_alt' => (string) get_post_meta($campaign_id, '_campaign_image_alt', true),
            'cta_text' => (string) get_post_meta($campaign_id, '_campaign_cta_text', true),
            'cta_url' => (string) get_post_meta($campaign_id, '_campaign_cta_url', true),
            'cta_visible' => (string) get_post_meta($campaign_id, '_campaign_cta_visible', true) === '1',
            'status' => (string) get_post_meta($campaign_id, '_campaign_status', true),
            'priority' => (int) get_post_meta($campaign_id, '_campaign_priority', true),
            'start_date' => (string) get_post_meta($campaign_id, '_campaign_start_date', true),
            'end_date' => (string) get_post_meta($campaign_id, '_campaign_end_date', true),
            'impressions' => $impressions,
            'clicks' => $clicks,
            'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0,
        ];
    }
}
