<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_CRM_Client_Update_Endpoint
{
    private $post_type;

    private $blocked_fields = [
        'premium',
        'premium_expire',
        'featured',
        'featured_expire',
        'escort_expire',
        'needs_payment',
        'notactive',
        'profile_status',
    ];

    private $blocked_meta_fields = [
        'secret',
        'ip',
        'hostname',
        'upload_folder',
    ];

    public function __construct()
    {
        $this->post_type = get_option('taxonomy_profile_url', 'escort');
    }

    public function register_routes($namespace)
    {
        register_rest_route($namespace, '/clients/(?P<post_id>\d+)/update', [
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => [$this, 'update_client'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);
    }

    public function check_permissions()
    {
        return current_user_can('manage_options');
    }

    public function update_client($request)
    {
        $post_id = (int) $request->get_param('post_id');
        $post = get_post($post_id);

        if (!$post || $post->post_type !== $this->post_type) {
            return new WP_Error('not_found', 'Client not found', ['status' => 404]);
        }

        $json = $request->get_json_params();
        $params = is_array($json) ? $json : $request->get_params();
        $fields = isset($params['fields']) && is_array($params['fields']) ? $params['fields'] : [];

        if (empty($fields)) {
            return new WP_Error('invalid_fields', 'fields payload is required.', ['status' => 422]);
        }

        $attemptedBlocked = [];
        foreach (array_merge($this->blocked_fields, $this->blocked_meta_fields) as $blockedKey) {
            if (array_key_exists($blockedKey, $fields)) {
                $attemptedBlocked[] = $blockedKey;
            }
        }

        if (!empty($attemptedBlocked)) {
            return new WP_Error(
                'blocked_fields',
                'Subscription, activation, and sensitive fields are not editable via profile updates.',
                [
                    'status' => 422,
                    'blocked_fields' => $attemptedBlocked,
                ]
            );
        }

        $postUpdate = [
            'ID' => $post_id,
        ];

        if (array_key_exists('name', $fields) || array_key_exists('post_title', $fields)) {
            $postUpdate['post_title'] = sanitize_text_field((string) ($fields['name'] ?? $fields['post_title']));
        }

        if (array_key_exists('content', $fields) || array_key_exists('post_content', $fields)) {
            $postUpdate['post_content'] = wp_kses_post((string) ($fields['content'] ?? $fields['post_content']));
        }

        if (array_key_exists('excerpt', $fields) || array_key_exists('post_excerpt', $fields)) {
            $postUpdate['post_excerpt'] = sanitize_textarea_field((string) ($fields['excerpt'] ?? $fields['post_excerpt']));
        }

        if (array_key_exists('post_status', $fields)) {
            $status = sanitize_text_field((string) $fields['post_status']);
            if (in_array($status, ['publish', 'private', 'draft', 'pending'], true)) {
                $postUpdate['post_status'] = $status;
            }
        }

        if (count($postUpdate) > 1) {
            $updateResult = wp_update_post($postUpdate, true);
            if (is_wp_error($updateResult)) {
                return new WP_Error('wp_update_failed', $updateResult->get_error_message(), ['status' => 422]);
            }
        }

        if (array_key_exists('city', $fields)) {
            $cityValue = $fields['city'];
            $cityTerm = $this->resolve_city_term($cityValue);

            if ((string) $cityValue !== '' && !$cityTerm) {
                return new WP_Error('invalid_city', 'City term does not exist in escorts-from taxonomy.', ['status' => 422]);
            }

            if ($cityTerm) {
                wp_set_post_terms($post_id, [(int) $cityTerm->term_id], 'escorts-from', false);
                update_post_meta($post_id, 'city', (int) $cityTerm->term_id);
            } else {
                wp_set_post_terms($post_id, [], 'escorts-from', false);
                delete_post_meta($post_id, 'city');
            }
        }

        foreach ($fields as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            if (in_array($key, ['name', 'post_title', 'content', 'post_content', 'excerpt', 'post_excerpt', 'post_status', 'city'], true)) {
                continue;
            }

            if (in_array($key, $this->blocked_fields, true)) {
                continue;
            }

            if (in_array($key, $this->blocked_meta_fields, true)) {
                continue;
            }

            if (strpos($key, '_') === 0) {
                continue;
            }

            if ($value === null || $value === '') {
                delete_post_meta($post_id, $key);
                continue;
            }

            if (is_array($value)) {
                update_post_meta($post_id, $key, array_map(function ($item) {
                    return is_scalar($item) ? sanitize_text_field((string) $item) : $item;
                }, $value));
                continue;
            }

            if (is_bool($value) || is_int($value) || is_float($value)) {
                update_post_meta($post_id, $key, $value);
                continue;
            }

            update_post_meta($post_id, $key, sanitize_text_field((string) $value));
        }

        if (array_key_exists('email', $fields)) {
            $email = sanitize_email((string) $fields['email']);
            if ($email !== '') {
                wp_update_user([
                    'ID' => (int) $post->post_author,
                    'user_email' => $email,
                ]);
            }
        }

        $clientEndpoint = new Exotic_CRM_Client_Endpoint();
        $profileRequest = new WP_REST_Request('GET', '/');
        $profileRequest->set_param('post_id', $post_id);
        $profileResponse = $clientEndpoint->get_client($profileRequest);

        $profileData = $profileResponse;
        if ($profileResponse instanceof WP_REST_Response) {
            $profileData = $profileResponse->get_data();
        }

        return rest_ensure_response([
            'success' => true,
            'post_id' => $post_id,
            'updated_fields' => array_keys($fields),
            'profile' => $profileData,
        ]);
    }

    private function resolve_city_term($value)
    {
        if (is_numeric($value)) {
            $term = get_term((int) $value, 'escorts-from');
            if ($term && !is_wp_error($term)) {
                return $term;
            }
        }

        $raw = sanitize_text_field((string) $value);
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
}
