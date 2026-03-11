<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_CRM_Wallet_Sync_Endpoint
{
    const CONFIG_OPTION = 'exotic_crm_wallet_config';

    private $post_type;

    public function __construct()
    {
        $this->post_type = get_option('taxonomy_profile_url', 'escort');
    }

    public function register_routes($namespace)
    {
        register_rest_route($namespace, '/clients/(?P<post_id>\d+)/wallet-balance', [
            'methods'             => 'POST',
            'callback'            => [$this, 'sync_wallet_balance'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);

        register_rest_route($namespace, '/clients/(?P<post_id>\d+)/wallet-config', [
            'methods'             => 'POST',
            'callback'            => [$this, 'sync_wallet_config'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);
    }

    public function check_permissions()
    {
        return current_user_can('manage_options');
    }

    public function sync_wallet_balance($request)
    {
        $post = $this->resolve_client_post((int) $request->get_param('post_id'));
        if (is_wp_error($post)) {
            return $post;
        }

        try {
            $payload = $this->resolve_payload($request);
            $balance = $this->normalize_amount($payload['balance'] ?? null, 'balance');
            $currency = $this->normalize_currency($payload['currency'] ?? '');
            $platform_id = $this->normalize_positive_int($payload['platform_id'] ?? null, 'platform_id');
            $mode = $this->normalize_mode($payload['mode'] ?? 'disabled');
            $refreshed_at = $this->normalize_datetime($payload['refreshed_at'] ?? null, 'refreshed_at');
            $wallet_last_synced_at = $this->normalize_datetime(
                $payload['wallet_last_synced_at'] ?? $payload['synced_at'] ?? null,
                'wallet_last_synced_at'
            );
            $transactions = $this->normalize_transactions($payload['transactions'] ?? []);
            $last_topup = $this->normalize_last_topup($payload['last_topup'] ?? null);
        } catch (InvalidArgumentException $exception) {
            return new WP_Error('invalid_wallet_balance_payload', $exception->getMessage(), ['status' => 422]);
        }

        $user_id = (int) $post->post_author;
        if ($user_id <= 0) {
            return new WP_Error('invalid_user', 'Client post has no valid author.', ['status' => 422]);
        }

        $summary = [
            'platform_id' => $platform_id,
            'wp_post_id' => (int) $post->ID,
            'wp_user_id' => $user_id,
            'balance' => $balance,
            'currency' => $currency,
            'mode' => $mode,
            'refreshed_at' => $refreshed_at,
            'wallet_last_synced_at' => $wallet_last_synced_at,
            'last_topup' => $last_topup,
            'transactions' => $transactions,
        ];

        update_user_meta($user_id, 'exotic_crm_wallet_balance', $balance);
        update_user_meta($user_id, 'exotic_crm_wallet_currency', $currency);
        update_user_meta($user_id, 'exotic_crm_wallet_mode', $mode);
        update_user_meta($user_id, 'exotic_crm_wallet_platform_id', $platform_id);
        update_user_meta($user_id, 'exotic_crm_wallet_refreshed_at', $refreshed_at);
        update_user_meta($user_id, 'exotic_crm_wallet_last_synced_at', $wallet_last_synced_at);
        update_user_meta($user_id, 'exotic_crm_wallet_last_topup', $last_topup);
        update_user_meta($user_id, 'exotic_crm_wallet_transactions', $transactions);
        update_user_meta($user_id, 'exotic_crm_wallet_summary', $summary);

        return rest_ensure_response([
            'success' => true,
            'post_id' => (int) $post->ID,
            'user_id' => $user_id,
            'wallet' => $summary,
        ]);
    }

    public function sync_wallet_config($request)
    {
        $post = $this->resolve_client_post((int) $request->get_param('post_id'));
        if (is_wp_error($post)) {
            return $post;
        }

        try {
            $payload = $this->resolve_payload($request);
            $platform_id = $this->normalize_positive_int($payload['platform_id'] ?? null, 'platform_id');
            $mode = $this->normalize_mode($payload['mode'] ?? 'disabled');
            $synced_at = $this->normalize_datetime($payload['synced_at'] ?? null, 'synced_at');
            $config = $payload['config'] ?? null;

            if (!is_array($config) || empty($config)) {
                return new WP_Error('invalid_config', 'config payload is required.', ['status' => 422]);
            }
        } catch (InvalidArgumentException $exception) {
            return new WP_Error('invalid_wallet_config_payload', $exception->getMessage(), ['status' => 422]);
        }

        $stored = [
            'platform_id' => $platform_id,
            'wp_post_id' => (int) $post->ID,
            'mode' => $mode,
            'synced_at' => $synced_at,
            'config' => $this->sanitize_wallet_value($config),
        ];

        update_option(self::CONFIG_OPTION, $stored, false);

        return rest_ensure_response([
            'success' => true,
            'post_id' => (int) $post->ID,
            'config' => $stored,
        ]);
    }

    private function resolve_client_post($post_id)
    {
        if ($post_id <= 0) {
            return new WP_Error('invalid_post_id', 'post_id must be a positive integer.', ['status' => 422]);
        }

        $post = get_post($post_id);
        if (!$post || $post->post_type !== $this->post_type) {
            return new WP_Error('not_found', 'Client not found', ['status' => 404]);
        }

        return $post;
    }

    private function resolve_payload($request)
    {
        $json = $request->get_json_params();
        if (is_array($json) && !empty($json)) {
            return $json;
        }

        $params = $request->get_params();
        unset($params['post_id']);

        return is_array($params) ? $params : [];
    }

    private function normalize_positive_int($value, $field)
    {
        if (!is_numeric($value) || (int) $value <= 0) {
            throw new InvalidArgumentException(sprintf('%s must be a positive integer.', $field));
        }

        return (int) $value;
    }

    private function normalize_amount($value, $field)
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(sprintf('%s must be numeric.', $field));
        }

        $amount = round((float) $value, 2);
        if ($amount < 0) {
            throw new InvalidArgumentException(sprintf('%s cannot be negative.', $field));
        }

        return number_format($amount, 2, '.', '');
    }

    private function normalize_currency($value)
    {
        $currency = strtoupper(trim((string) $value));
        if ($currency === '' || strlen($currency) !== 3) {
            throw new InvalidArgumentException('currency must be a 3-letter code.');
        }

        return $currency;
    }

    private function normalize_mode($value)
    {
        $mode = strtolower(trim((string) $value));
        if (!in_array($mode, ['disabled', 'sandbox', 'production'], true)) {
            throw new InvalidArgumentException('mode must be disabled, sandbox, or production.');
        }

        return $mode;
    }

    private function normalize_datetime($value, $field)
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return current_time('c');
        }

        $timestamp = strtotime($raw);
        if ($timestamp === false) {
            throw new InvalidArgumentException(sprintf('%s must be a valid datetime string.', $field));
        }

        return gmdate('c', $timestamp);
    }

    private function normalize_last_topup($value)
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException('last_topup must be an object or null.');
        }

        return $this->sanitize_wallet_value($value);
    }

    private function normalize_transactions($value)
    {
        if ($value === null) {
            return [];
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException('transactions must be an array.');
        }

        $normalized = [];
        foreach ($value as $transaction) {
            if (!is_array($transaction)) {
                throw new InvalidArgumentException('transactions must contain only objects.');
            }

            $normalized[] = $this->sanitize_wallet_value($transaction);
        }

        return $normalized;
    }

    private function sanitize_wallet_value($value)
    {
        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $key => $item) {
                $safe_key = is_string($key) ? sanitize_key($key) : $key;
                $sanitized[$safe_key] = $this->sanitize_wallet_value($item);
            }

            return $sanitized;
        }

        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        return sanitize_text_field((string) $value);
    }
}
