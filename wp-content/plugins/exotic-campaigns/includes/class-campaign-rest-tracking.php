<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_Campaign_REST_Tracking
{
    const IMPRESSION_LIMIT_PER_HOUR = 20;
    const SESSION_COOKIE = 'exotic_camp_sid';

    public function register_routes($namespace)
    {
        register_rest_route($namespace, '/track/impression', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'track_impression'],
                'permission_callback' => [$this, 'public_nonce_permission_check'],
                'args' => [
                    'campaign_ids' => [
                        'required' => true,
                    ],
                ],
            ],
        ]);

        register_rest_route($namespace, '/track/click', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'track_click'],
                'permission_callback' => [$this, 'public_nonce_permission_check'],
                'args' => [
                    'campaign_id' => [
                        'required' => true,
                        'sanitize_callback' => 'absint',
                    ],
                ],
            ],
        ]);
    }

    public function public_nonce_permission_check(WP_REST_Request $request)
    {
        $nonce = (string) $request->get_header('x_wp_nonce');
        if ($nonce === '') {
            $nonce = (string) $request->get_param('_wpnonce');
        }

        if ($nonce === '' || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('invalid_nonce', 'Invalid or expired tracking nonce.', ['status' => 403]);
        }

        return true;
    }

    public function track_impression(WP_REST_Request $request)
    {
        if ($this->is_bot_user_agent()) {
            return rest_ensure_response(['success' => true, 'ignored' => 'bot']);
        }

        $rate_limit_check = $this->check_rate_limit();
        if (is_wp_error($rate_limit_check)) {
            return $rate_limit_check;
        }

        $campaign_ids = (array) $request->get_param('campaign_ids');
        $campaign_ids = array_values(array_filter(array_map('absint', $campaign_ids)));

        if (empty($campaign_ids)) {
            return new WP_Error('invalid_campaign_ids', 'campaign_ids must contain at least one valid campaign ID.', ['status' => 422]);
        }

        $session_id = $this->get_or_create_session_id();
        $dedupe_key = 'exotic_campaign_seen_' . md5($session_id);
        $seen_ids = get_transient($dedupe_key);
        if (!is_array($seen_ids)) {
            $seen_ids = [];
        }

        $tracked = 0;
        $newly_seen = $seen_ids;

        foreach ($campaign_ids as $campaign_id) {
            if (in_array($campaign_id, $seen_ids, true)) {
                continue;
            }

            if (!$this->is_trackable_campaign($campaign_id)) {
                continue;
            }

            $this->increment_counters($campaign_id, 'impressions');
            $newly_seen[] = $campaign_id;
            $tracked++;
        }

        $newly_seen = array_values(array_unique($newly_seen));
        set_transient($dedupe_key, $newly_seen, HOUR_IN_SECONDS);

        return rest_ensure_response([
            'success' => true,
            'tracked' => $tracked,
            'requested' => count($campaign_ids),
        ]);
    }

    public function track_click(WP_REST_Request $request)
    {
        if ($this->is_bot_user_agent()) {
            return rest_ensure_response(['success' => true, 'ignored' => 'bot']);
        }

        $campaign_id = absint($request->get_param('campaign_id'));

        if ($campaign_id < 1) {
            return new WP_Error('invalid_campaign_id', 'campaign_id must be a valid campaign ID.', ['status' => 422]);
        }

        if (!$this->is_trackable_campaign($campaign_id)) {
            return new WP_Error('campaign_not_trackable', 'Campaign is not trackable.', ['status' => 404]);
        }

        $this->increment_counters($campaign_id, 'clicks');

        return rest_ensure_response([
            'success' => true,
            'tracked' => 1,
        ]);
    }

    private function is_trackable_campaign($campaign_id)
    {
        $campaign_id = absint($campaign_id);
        if ($campaign_id < 1) {
            return false;
        }

        $post = get_post($campaign_id);
        if (!$post || $post->post_type !== Exotic_Campaign_Post_Type::POST_TYPE || $post->post_status !== 'publish') {
            return false;
        }

        $status = get_post_meta($campaign_id, '_campaign_status', true);
        return $status === 'active';
    }

    private function check_rate_limit()
    {
        $ip = $this->resolve_client_ip();
        $bucket = gmdate('YmdH');
        $key = 'exotic_campaign_rate_' . md5($ip . '|' . $bucket);

        $count = (int) get_transient($key);
        if ($count >= self::IMPRESSION_LIMIT_PER_HOUR) {
            return new WP_Error('rate_limited', 'Impression rate limit exceeded.', ['status' => 429]);
        }

        set_transient($key, $count + 1, HOUR_IN_SECONDS + 120);

        return true;
    }

    private function resolve_client_ip()
    {
        $trusted_header = defined('EXOTIC_CAMPAIGNS_TRUSTED_PROXY_HEADER')
            ? (string) EXOTIC_CAMPAIGNS_TRUSTED_PROXY_HEADER
            : (string) get_option('exotic_campaigns_trusted_proxy_header', '');

        $allowed_headers = [
            'CF-Connecting-IP',
            'X-Forwarded-For',
            'X-Real-IP',
        ];

        if (in_array($trusted_header, $allowed_headers, true)) {
            $server_key = 'HTTP_' . strtoupper(str_replace('-', '_', $trusted_header));
            $raw = isset($_SERVER[$server_key]) ? (string) $_SERVER[$server_key] : '';

            if ($raw !== '') {
                if ($trusted_header === 'X-Forwarded-For') {
                    $parts = array_map('trim', explode(',', $raw));
                    $raw = (string) reset($parts);
                }

                if (filter_var($raw, FILTER_VALIDATE_IP)) {
                    return $raw;
                }
            }
        }

        $remote = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
        if (filter_var($remote, FILTER_VALIDATE_IP)) {
            return $remote;
        }

        return '0.0.0.0';
    }

    private function is_bot_user_agent()
    {
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower((string) $_SERVER['HTTP_USER_AGENT']) : '';
        if ($ua === '') {
            return false;
        }

        $patterns = ['bot', 'spider', 'crawl', 'slurp', 'headless', 'facebookexternalhit', 'preview'];

        foreach ($patterns as $pattern) {
            if (strpos($ua, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private function get_or_create_session_id()
    {
        $cookie = isset($_COOKIE[self::SESSION_COOKIE]) ? sanitize_text_field((string) $_COOKIE[self::SESSION_COOKIE]) : '';
        if ($cookie !== '') {
            return $cookie;
        }

        try {
            $session_id = bin2hex(random_bytes(16));
        } catch (Exception $exception) {
            $session_id = wp_hash(uniqid('camp', true));
        }

        $cookie_path = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';
        $cookie_domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';

        setcookie(self::SESSION_COOKIE, $session_id, [
            'expires' => time() + HOUR_IN_SECONDS,
            'path' => $cookie_path,
            'domain' => $cookie_domain,
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        $_COOKIE[self::SESSION_COOKIE] = $session_id;

        return $session_id;
    }

    private function increment_counters($campaign_id, $metric)
    {
        global $wpdb;

        $campaign_id = absint($campaign_id);
        $metric = $metric === 'clicks' ? 'clicks' : 'impressions';

        Exotic_Campaign_Post_Type::seed_counter_meta($campaign_id);

        $table = $wpdb->prefix . 'campaign_daily_stats';
        $today = current_time('Y-m-d');

        if ($metric === 'impressions') {
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO {$table} (campaign_id, stat_date, impressions, clicks)
                    VALUES (%d, %s, 1, 0)
                    ON DUPLICATE KEY UPDATE impressions = impressions + 1",
                    $campaign_id,
                    $today
                )
            );
            $meta_key = '_campaign_impressions';
        } else {
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO {$table} (campaign_id, stat_date, impressions, clicks)
                    VALUES (%d, %s, 0, 1)
                    ON DUPLICATE KEY UPDATE clicks = clicks + 1",
                    $campaign_id,
                    $today
                )
            );
            $meta_key = '_campaign_clicks';
        }

        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->postmeta}
                SET meta_value = CAST(meta_value AS UNSIGNED) + 1
                WHERE post_id = %d AND meta_key = %s",
                $campaign_id,
                $meta_key
            )
        );

        if ((int) $updated < 1) {
            add_post_meta($campaign_id, $meta_key, '0', true);

            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->postmeta}
                    SET meta_value = CAST(meta_value AS UNSIGNED) + 1
                    WHERE post_id = %d AND meta_key = %s",
                    $campaign_id,
                    $meta_key
                )
            );
        }
    }
}
