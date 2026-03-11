<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_Campaign_Post_Type
{
    const POST_TYPE = 'campaign';
    const ROLE = 'campaign_manager';
    const CAPABILITY = 'manage_campaigns';
    const MIGRATION_OPTION = '_exotic_campaigns_migrated';

    public static function user_can_manage_campaigns($user_id = 0)
    {
        $user_id = absint($user_id);

        if ($user_id > 0) {
            return user_can($user_id, self::CAPABILITY)
                || user_can($user_id, 'manage_options')
                || user_can($user_id, 'level_10');
        }

        return current_user_can(self::CAPABILITY)
            || current_user_can('manage_options')
            || current_user_can('level_10');
    }

    public static function register_post_type()
    {
        $labels = [
            'name' => __('Campaigns', 'exotic-campaigns'),
            'singular_name' => __('Campaign', 'exotic-campaigns'),
            'add_new' => __('Add Campaign', 'exotic-campaigns'),
            'add_new_item' => __('Add New Campaign', 'exotic-campaigns'),
            'edit_item' => __('Edit Campaign', 'exotic-campaigns'),
            'new_item' => __('New Campaign', 'exotic-campaigns'),
            'view_item' => __('View Campaign', 'exotic-campaigns'),
            'search_items' => __('Search Campaigns', 'exotic-campaigns'),
            'not_found' => __('No campaigns found.', 'exotic-campaigns'),
            'not_found_in_trash' => __('No campaigns found in Trash.', 'exotic-campaigns'),
            'menu_name' => __('Campaigns', 'exotic-campaigns'),
        ];

        $capabilities = [
            'edit_post' => self::CAPABILITY,
            'read_post' => self::CAPABILITY,
            'delete_post' => self::CAPABILITY,
            'edit_posts' => self::CAPABILITY,
            'edit_others_posts' => self::CAPABILITY,
            'publish_posts' => self::CAPABILITY,
            'read_private_posts' => self::CAPABILITY,
            'read' => 'read',
            'delete_posts' => self::CAPABILITY,
            'delete_private_posts' => self::CAPABILITY,
            'delete_published_posts' => self::CAPABILITY,
            'delete_others_posts' => self::CAPABILITY,
            'edit_private_posts' => self::CAPABILITY,
            'edit_published_posts' => self::CAPABILITY,
            'create_posts' => self::CAPABILITY,
        ];

        register_post_type(self::POST_TYPE, [
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'supports' => ['title'],
            'menu_position' => 56,
            'menu_icon' => 'dashicons-megaphone',
            'capability_type' => ['campaign', 'campaigns'],
            'capabilities' => $capabilities,
            'map_meta_cap' => true,
            'rewrite' => false,
            'has_archive' => false,
            'exclude_from_search' => true,
        ]);
    }

    public static function register_meta_keys()
    {
        $meta_keys = [
            '_campaign_format' => [
                'type' => 'string',
                'sanitize_callback' => [__CLASS__, 'sanitize_format'],
                'default' => 'card',
            ],
            '_campaign_badge_text' => [
                'type' => 'string',
                'sanitize_callback' => [__CLASS__, 'sanitize_badge'],
                'default' => '',
            ],
            '_campaign_description' => [
                'type' => 'string',
                'sanitize_callback' => [__CLASS__, 'sanitize_description'],
                'default' => '',
            ],
            '_campaign_icon_class' => [
                'type' => 'string',
                'sanitize_callback' => [__CLASS__, 'sanitize_icon_class'],
                'default' => 'fa fa-bullhorn',
            ],
            '_campaign_color_primary' => [
                'type' => 'string',
                'sanitize_callback' => [__CLASS__, 'sanitize_hex_color'],
                'default' => '#ab1c2f',
            ],
            '_campaign_color_secondary' => [
                'type' => 'string',
                'sanitize_callback' => [__CLASS__, 'sanitize_hex_color_optional'],
                'default' => '',
            ],
            '_campaign_image_id' => [
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => 0,
            ],
            '_campaign_image_alt' => [
                'type' => 'string',
                'sanitize_callback' => [__CLASS__, 'sanitize_alt'],
                'default' => '',
            ],
            '_campaign_cta_text' => [
                'type' => 'string',
                'sanitize_callback' => [__CLASS__, 'sanitize_cta_text'],
                'default' => '',
            ],
            '_campaign_cta_url' => [
                'type' => 'string',
                'sanitize_callback' => [__CLASS__, 'sanitize_http_url'],
                'default' => '',
            ],
            '_campaign_cta_visible' => [
                'type' => 'string',
                'sanitize_callback' => [__CLASS__, 'sanitize_bool_string'],
                'default' => '1',
            ],
            '_campaign_start_date' => [
                'type' => 'string',
                'sanitize_callback' => [__CLASS__, 'sanitize_datetime'],
                'default' => '',
            ],
            '_campaign_end_date' => [
                'type' => 'string',
                'sanitize_callback' => [__CLASS__, 'sanitize_datetime_optional'],
                'default' => '',
            ],
            '_campaign_status' => [
                'type' => 'string',
                'sanitize_callback' => [__CLASS__, 'sanitize_status'],
                'default' => 'scheduled',
            ],
            '_campaign_priority' => [
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => 10,
            ],
            '_campaign_impressions' => [
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => 0,
            ],
            '_campaign_clicks' => [
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => 0,
            ],
        ];

        foreach ($meta_keys as $meta_key => $args) {
            register_post_meta(self::POST_TYPE, $meta_key, [
                'single' => true,
                'show_in_rest' => false,
                'type' => $args['type'],
                'sanitize_callback' => $args['sanitize_callback'],
                'auth_callback' => static function () {
                    return self::user_can_manage_campaigns();
                },
                'default' => $args['default'],
            ]);
        }
    }

    public static function ensure_roles_and_caps()
    {
        $role = get_role(self::ROLE);

        if (!$role) {
            $role = add_role(self::ROLE, __('Campaign Manager', 'exotic-campaigns'), [
                'read' => true,
                'upload_files' => true,
                self::CAPABILITY => true,
            ]);
        }

        if ($role instanceof WP_Role) {
            $role->add_cap('read');
            $role->add_cap('upload_files');
            $role->add_cap(self::CAPABILITY);
        }

        $admin = get_role('administrator');
        if ($admin instanceof WP_Role) {
            $admin->add_cap(self::CAPABILITY);
            $admin->add_cap('upload_files');
        }
    }

    public static function activate()
    {
        self::register_post_type();
        self::register_meta_keys();
        self::ensure_roles_and_caps();
        self::create_daily_stats_table();
        self::migrate_static_cards();
        self::dedupe_counter_meta_rows();
        flush_rewrite_rules();
    }

    public static function deactivate()
    {
        flush_rewrite_rules();
    }

    public static function create_daily_stats_table()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table = $wpdb->prefix . 'campaign_daily_stats';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            campaign_id BIGINT UNSIGNED NOT NULL,
            stat_date DATE NOT NULL,
            impressions INT UNSIGNED NOT NULL DEFAULT 0,
            clicks INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY campaign_date (campaign_id, stat_date),
            KEY stat_date (stat_date),
            KEY campaign_id (campaign_id)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    public static function migrate_static_cards()
    {
        if ((int) get_option(self::MIGRATION_OPTION, 0) === 1) {
            return;
        }

        $existing = get_posts([
            'post_type' => self::POST_TYPE,
            'post_status' => ['publish', 'draft', 'private', 'pending'],
            'posts_per_page' => 1,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        if (!empty($existing)) {
            update_option(self::MIGRATION_OPTION, 1, false);
            return;
        }

        $register_url = get_permalink((int) get_option('member_register_page_id')) ?: home_url('/');
        $premium_url = get_permalink((int) get_option('all_premium_profiles_page_id')) ?: home_url('/');
        $contact_url = get_permalink((int) get_option('contact_page_id')) ?: home_url('/contact/');

        $cards = [
            [
                'title' => __('Adult Spa', 'exotic-campaigns'),
                'badge' => __('Adult Fun', 'exotic-campaigns'),
                'description' => __('Ultimate relaxation with a happy ending. Satisfaction guaranteed.', 'exotic-campaigns'),
                'icon' => 'fa fa-diamond',
                'cta_text' => __('Book Session', 'exotic-campaigns'),
                'cta_url' => $contact_url,
                'priority' => 10,
                'primary' => '#c71585',
                'secondary' => '#ff1493',
            ],
            [
                'title' => __('Sex Enhancement', 'exotic-campaigns'),
                'badge' => __('Power Up', 'exotic-campaigns'),
                'description' => __('Harder, stronger, longer. Pills to keep you going all night.', 'exotic-campaigns'),
                'icon' => 'fa fa-bolt',
                'cta_text' => __('Buy Now', 'exotic-campaigns'),
                'cta_url' => $premium_url,
                'priority' => 20,
                'primary' => '#008cb4',
                'secondary' => '#00bfff',
            ],
            [
                'title' => __('Play & Win', 'exotic-campaigns'),
                'badge' => __('Jackpot', 'exotic-campaigns'),
                'description' => __('Feeling lucky? Bet big and win massive jackpots today.', 'exotic-campaigns'),
                'icon' => 'fa fa-trophy',
                'cta_text' => __('Play Now', 'exotic-campaigns'),
                'cta_url' => $register_url,
                'priority' => 30,
                'primary' => '#dca11a',
                'secondary' => '#ffd84d',
            ],
        ];

        foreach ($cards as $card) {
            $campaign_id = wp_insert_post([
                'post_type' => self::POST_TYPE,
                'post_title' => $card['title'],
                'post_status' => 'publish',
                'post_content' => '',
                'post_author' => get_current_user_id() ?: 1,
                'menu_order' => (int) $card['priority'],
            ]);

            if (is_wp_error($campaign_id) || $campaign_id < 1) {
                continue;
            }

            update_post_meta($campaign_id, '_campaign_format', 'card');
            update_post_meta($campaign_id, '_campaign_badge_text', $card['badge']);
            update_post_meta($campaign_id, '_campaign_description', $card['description']);
            update_post_meta($campaign_id, '_campaign_icon_class', $card['icon']);
            update_post_meta($campaign_id, '_campaign_color_primary', $card['primary']);
            update_post_meta($campaign_id, '_campaign_color_secondary', $card['secondary']);
            update_post_meta($campaign_id, '_campaign_cta_text', $card['cta_text']);
            update_post_meta($campaign_id, '_campaign_cta_url', esc_url_raw($card['cta_url']));
            update_post_meta($campaign_id, '_campaign_cta_visible', '1');
            update_post_meta($campaign_id, '_campaign_start_date', current_time('mysql'));
            update_post_meta($campaign_id, '_campaign_end_date', '');
            update_post_meta($campaign_id, '_campaign_status', 'active');
            update_post_meta($campaign_id, '_campaign_priority', (int) $card['priority']);

            self::seed_counter_meta($campaign_id);
        }

        update_option(self::MIGRATION_OPTION, 1, false);
    }

    public static function seed_counter_meta($campaign_id)
    {
        $campaign_id = (int) $campaign_id;

        if ($campaign_id < 1) {
            return;
        }

        $keys = ['_campaign_impressions', '_campaign_clicks'];

        foreach ($keys as $meta_key) {
            if (!metadata_exists('post', $campaign_id, $meta_key)) {
                add_post_meta($campaign_id, $meta_key, '0', true);
            }
        }
    }

    public static function dedupe_counter_meta_rows()
    {
        global $wpdb;

        $keys = ['_campaign_impressions', '_campaign_clicks'];

        foreach ($keys as $meta_key) {
            $dupes = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT post_id
                    FROM {$wpdb->postmeta}
                    WHERE meta_key = %s
                    GROUP BY post_id
                    HAVING COUNT(*) > 1",
                    $meta_key
                )
            );

            if (empty($dupes)) {
                continue;
            }

            foreach ($dupes as $post_id) {
                $rows = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT meta_id, meta_value
                        FROM {$wpdb->postmeta}
                        WHERE post_id = %d AND meta_key = %s
                        ORDER BY meta_id ASC",
                        (int) $post_id,
                        $meta_key
                    ),
                    ARRAY_A
                );

                if (empty($rows)) {
                    continue;
                }

                $sum = 0;
                $keep_meta_id = 0;
                $delete_ids = [];

                foreach ($rows as $index => $row) {
                    $sum += absint($row['meta_value']);
                    if ($index === 0) {
                        $keep_meta_id = (int) $row['meta_id'];
                    } else {
                        $delete_ids[] = (int) $row['meta_id'];
                    }
                }

                if ($keep_meta_id > 0) {
                    $wpdb->update(
                        $wpdb->postmeta,
                        ['meta_value' => (string) $sum],
                        ['meta_id' => $keep_meta_id],
                        ['%s'],
                        ['%d']
                    );
                }

                if (!empty($delete_ids)) {
                    $ids_sql = implode(',', array_map('absint', $delete_ids));
                    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_id IN ({$ids_sql})");
                }
            }
        }
    }

    public static function sanitize_format($value)
    {
        $value = sanitize_text_field((string) $value);
        return in_array($value, ['card', 'image'], true) ? $value : 'card';
    }

    public static function sanitize_badge($value)
    {
        return mb_substr(sanitize_text_field((string) $value), 0, 20);
    }

    public static function sanitize_description($value)
    {
        return mb_substr(sanitize_textarea_field((string) $value), 0, 200);
    }

    public static function sanitize_cta_text($value)
    {
        return mb_substr(sanitize_text_field((string) $value), 0, 30);
    }

    public static function sanitize_alt($value)
    {
        return mb_substr(sanitize_text_field((string) $value), 0, 125);
    }

    public static function sanitize_icon_class($value)
    {
        $value = sanitize_text_field((string) $value);
        return preg_match('/^fa fa-[a-z0-9-]+$/', $value) ? $value : 'fa fa-bullhorn';
    }

    public static function sanitize_hex_color($value)
    {
        $value = sanitize_text_field((string) $value);
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            return strtoupper($value);
        }
        return '#AB1C2F';
    }

    public static function sanitize_hex_color_optional($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        return self::sanitize_hex_color($value);
    }

    public static function sanitize_http_url($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $scheme = strtolower((string) wp_parse_url($value, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return '';
        }

        return esc_url_raw($value, ['http', 'https']);
    }

    public static function sanitize_bool_string($value)
    {
        return !empty($value) ? '1' : '0';
    }

    public static function sanitize_status($value)
    {
        $value = sanitize_text_field((string) $value);
        $allowed = ['active', 'scheduled', 'paused', 'expired'];
        return in_array($value, $allowed, true) ? $value : 'scheduled';
    }

    public static function sanitize_datetime($value)
    {
        $value = sanitize_text_field((string) $value);
        $dt = date_create($value);

        if (!$dt) {
            return current_time('mysql');
        }

        return $dt->format('Y-m-d H:i:s');
    }

    public static function sanitize_datetime_optional($value)
    {
        $value = sanitize_text_field((string) $value);

        if ($value === '') {
            return '';
        }

        $dt = date_create($value);
        if (!$dt) {
            return '';
        }

        return $dt->format('Y-m-d H:i:s');
    }
}
