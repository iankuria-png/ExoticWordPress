<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Exotic_Campaign_Admin_List_Table extends WP_List_Table
{
    private $status_filter = '';

    public function __construct()
    {
        parent::__construct([
            'singular' => 'campaign',
            'plural' => 'campaigns',
            'ajax' => false,
        ]);
    }

    public function prepare_items()
    {
        $per_page = 20;
        $paged = max(1, absint((string) filter_input(INPUT_GET, 'paged')));
        $status = sanitize_text_field((string) filter_input(INPUT_GET, 'status'));

        if (!in_array($status, ['active', 'scheduled', 'paused', 'expired'], true)) {
            $status = '';
        }

        $this->status_filter = $status;

        $orderby = sanitize_text_field((string) filter_input(INPUT_GET, 'orderby'));
        $order = strtolower(sanitize_text_field((string) filter_input(INPUT_GET, 'order'))) === 'asc' ? 'ASC' : 'DESC';

        $args = [
            'post_type' => Exotic_Campaign_Post_Type::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $paged,
            'orderby' => 'date',
            'order' => $order,
        ];

        if ($status !== '') {
            $args['meta_query'] = [
                [
                    'key' => '_campaign_status',
                    'value' => $status,
                    'compare' => '=',
                ],
            ];
        }

        if (in_array($orderby, ['title', 'priority', 'impressions', 'clicks', 'status', 'start_date'], true)) {
            switch ($orderby) {
                case 'title':
                    $args['orderby'] = 'title';
                    break;
                case 'priority':
                    $args['meta_key'] = '_campaign_priority';
                    $args['orderby'] = 'meta_value_num';
                    break;
                case 'impressions':
                    $args['meta_key'] = '_campaign_impressions';
                    $args['orderby'] = 'meta_value_num';
                    break;
                case 'clicks':
                    $args['meta_key'] = '_campaign_clicks';
                    $args['orderby'] = 'meta_value_num';
                    break;
                case 'status':
                    $args['meta_key'] = '_campaign_status';
                    $args['orderby'] = 'meta_value';
                    break;
                case 'start_date':
                    $args['meta_key'] = '_campaign_start_date';
                    $args['orderby'] = 'meta_value';
                    break;
            }
        }

        $query = new WP_Query($args);

        $this->items = $query->posts;

        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

        $this->set_pagination_args([
            'total_items' => (int) $query->found_posts,
            'per_page' => $per_page,
            'total_pages' => max(1, (int) $query->max_num_pages),
        ]);
    }

    public function get_columns()
    {
        return [
            'cb' => '<input type="checkbox" />',
            'title' => __('Campaign', 'exotic-campaigns'),
            'format' => __('Format', 'exotic-campaigns'),
            'status' => __('Status', 'exotic-campaigns'),
            'schedule' => __('Schedule', 'exotic-campaigns'),
            'impressions' => __('Impressions', 'exotic-campaigns'),
            'clicks' => __('Clicks', 'exotic-campaigns'),
            'ctr' => __('CTR', 'exotic-campaigns'),
            'priority' => __('Priority', 'exotic-campaigns'),
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'title' => ['title', false],
            'status' => ['status', false],
            'impressions' => ['impressions', false],
            'clicks' => ['clicks', false],
            'priority' => ['priority', true],
            'schedule' => ['start_date', false],
        ];
    }

    public function get_views()
    {
        $base = admin_url('admin.php?page=exotic-campaigns');
        $counts = Exotic_Campaign_Admin_Page::get_status_counts();

        $views = [];

        $all_class = $this->status_filter === '' ? ' class="current" aria-current="page"' : '';
        $views['all'] = sprintf(
            '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
            esc_url($base),
            $all_class,
            esc_html__('All', 'exotic-campaigns'),
            (int) $counts['all']
        );

        foreach (['active', 'scheduled', 'paused', 'expired'] as $status) {
            $class = $this->status_filter === $status ? ' class="current" aria-current="page"' : '';
            $views[$status] = sprintf(
                '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
                esc_url(add_query_arg(['page' => 'exotic-campaigns', 'status' => $status], admin_url('admin.php'))),
                $class,
                esc_html(ucfirst($status)),
                (int) $counts[$status]
            );
        }

        return $views;
    }

    protected function get_bulk_actions()
    {
        return [
            'activate' => __('Set Active', 'exotic-campaigns'),
            'pause' => __('Set Paused', 'exotic-campaigns'),
            'schedule' => __('Set Scheduled', 'exotic-campaigns'),
            'expire' => __('Set Expired', 'exotic-campaigns'),
            'delete' => __('Move to Trash', 'exotic-campaigns'),
        ];
    }

    protected function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="campaign_ids[]" value="%d" />', (int) $item->ID);
    }

    protected function column_title($item)
    {
        $edit_url = add_query_arg([
            'page' => 'exotic-campaigns',
            'action' => 'edit',
            'campaign_id' => (int) $item->ID,
        ], admin_url('admin.php'));

        $delete_url = wp_nonce_url(
            add_query_arg([
                'action' => 'exotic_campaign_delete',
                'campaign_id' => (int) $item->ID,
            ], admin_url('admin-post.php')),
            'exotic_campaign_delete_' . (int) $item->ID
        );

        $actions = [
            'edit' => sprintf('<a href="%s">%s</a>', esc_url($edit_url), esc_html__('Edit', 'exotic-campaigns')),
            'delete' => sprintf('<a href="%s">%s</a>', esc_url($delete_url), esc_html__('Trash', 'exotic-campaigns')),
        ];

        return sprintf(
            '<strong><a href="%s">%s</a></strong>%s',
            esc_url($edit_url),
            esc_html(get_the_title($item->ID)),
            $this->row_actions($actions)
        );
    }

    protected function column_format($item)
    {
        $format = get_post_meta($item->ID, '_campaign_format', true) ?: 'card';
        return esc_html(ucfirst($format));
    }

    protected function column_status($item)
    {
        $status = get_post_meta($item->ID, '_campaign_status', true) ?: 'scheduled';
        return esc_html(ucfirst($status));
    }

    protected function column_schedule($item)
    {
        $start = get_post_meta($item->ID, '_campaign_start_date', true);
        $end = get_post_meta($item->ID, '_campaign_end_date', true);

        $start_label = $start ? esc_html(mysql2date('M j, Y H:i', $start)) : '—';
        $end_label = $end ? esc_html(mysql2date('M j, Y H:i', $end)) : '—';

        return sprintf(
            '<div><strong>%s:</strong> %s</div><div><strong>%s:</strong> %s</div>',
            esc_html__('Start', 'exotic-campaigns'),
            $start_label,
            esc_html__('End', 'exotic-campaigns'),
            $end_label
        );
    }

    protected function column_impressions($item)
    {
        return number_format_i18n((int) get_post_meta($item->ID, '_campaign_impressions', true));
    }

    protected function column_clicks($item)
    {
        return number_format_i18n((int) get_post_meta($item->ID, '_campaign_clicks', true));
    }

    protected function column_ctr($item)
    {
        $impressions = max(0, (int) get_post_meta($item->ID, '_campaign_impressions', true));
        $clicks = max(0, (int) get_post_meta($item->ID, '_campaign_clicks', true));

        if ($impressions < 1) {
            return '0.00%';
        }

        $ctr = ($clicks / $impressions) * 100;
        return esc_html(number_format_i18n($ctr, 2) . '%');
    }

    protected function column_priority($item)
    {
        return (int) get_post_meta($item->ID, '_campaign_priority', true);
    }
}

class Exotic_Campaign_Admin_Page
{
    public static function register()
    {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
        add_action('admin_post_exotic_campaign_save', [__CLASS__, 'save_campaign']);
        add_action('admin_post_exotic_campaign_delete', [__CLASS__, 'delete_campaign']);
        add_action('admin_post_exotic_campaign_settings', [__CLASS__, 'save_settings']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    public static function register_menu()
    {
        add_menu_page(
            __('Campaigns', 'exotic-campaigns'),
            __('Campaigns', 'exotic-campaigns'),
            Exotic_Campaign_Post_Type::CAPABILITY,
            'exotic-campaigns',
            [__CLASS__, 'render_page'],
            'dashicons-megaphone',
            56
        );
    }

    public static function enqueue_assets($hook)
    {
        if ($hook !== 'toplevel_page_exotic-campaigns') {
            return;
        }

        $css_file = EXOTIC_CAMPAIGNS_PATH . 'admin/css/campaign-admin.css';
        $js_file = EXOTIC_CAMPAIGNS_PATH . 'admin/js/campaign-admin.js';

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style(
            'exotic-campaign-admin',
            EXOTIC_CAMPAIGNS_URL . 'admin/css/campaign-admin.css',
            ['wp-color-picker'],
            file_exists($css_file) ? filemtime($css_file) : EXOTIC_CAMPAIGNS_VERSION
        );

        wp_enqueue_media();

        wp_enqueue_script(
            'exotic-campaign-admin',
            EXOTIC_CAMPAIGNS_URL . 'admin/js/campaign-admin.js',
            ['jquery', 'wp-color-picker'],
            file_exists($js_file) ? filemtime($js_file) : EXOTIC_CAMPAIGNS_VERSION,
            true
        );

        wp_localize_script('exotic-campaign-admin', 'exoticCampaignAdmin', [
            'chooseImage' => __('Choose Campaign Image', 'exotic-campaigns'),
            'useImage' => __('Use this image', 'exotic-campaigns'),
            'placeholder' => __('Image recommended: 800x440px (minimum 400x220px).', 'exotic-campaigns'),
        ]);
    }

    public static function render_page()
    {
        if (!Exotic_Campaign_Post_Type::user_can_manage_campaigns()) {
            wp_die(esc_html__('You do not have permission to manage campaigns.', 'exotic-campaigns'));
        }

        self::maybe_handle_bulk_actions();

        $action = sanitize_text_field((string) filter_input(INPUT_GET, 'action'));
        $campaign_id = absint((string) filter_input(INPUT_GET, 'campaign_id'));

        if ($action === 'new' || ($action === 'edit' && $campaign_id > 0)) {
            $campaign = self::get_campaign_for_form($campaign_id);
            include EXOTIC_CAMPAIGNS_PATH . 'admin/views/admin-edit.php';
            return;
        }

        $list_table = new Exotic_Campaign_Admin_List_Table();
        $list_table->prepare_items();

        include EXOTIC_CAMPAIGNS_PATH . 'admin/views/admin-list.php';
    }

    public static function save_campaign()
    {
        if (!Exotic_Campaign_Post_Type::user_can_manage_campaigns()) {
            wp_die(esc_html__('You do not have permission to save campaigns.', 'exotic-campaigns'));
        }

        check_admin_referer('exotic_campaign_save');

        $campaign_id = absint((string) filter_input(INPUT_POST, 'campaign_id'));
        $title = sanitize_text_field((string) filter_input(INPUT_POST, 'post_title'));

        if ($title === '') {
            $title = __('Untitled Campaign', 'exotic-campaigns');
        }

        if ($campaign_id > 0) {
            wp_update_post([
                'ID' => $campaign_id,
                'post_title' => $title,
            ]);
        } else {
            $campaign_id = wp_insert_post([
                'post_type' => Exotic_Campaign_Post_Type::POST_TYPE,
                'post_status' => 'publish',
                'post_title' => $title,
            ]);
        }

        if (is_wp_error($campaign_id) || $campaign_id < 1) {
            wp_safe_redirect(add_query_arg([
                'page' => 'exotic-campaigns',
                'error' => 'save_failed',
            ], admin_url('admin.php')));
            exit;
        }

        $payload = self::sanitize_campaign_payload();

        foreach ($payload as $meta_key => $meta_value) {
            update_post_meta($campaign_id, $meta_key, $meta_value);
        }

        Exotic_Campaign_Post_Type::seed_counter_meta($campaign_id);

        wp_safe_redirect(add_query_arg([
            'page' => 'exotic-campaigns',
            'updated' => 1,
            'campaign_id' => $campaign_id,
        ], admin_url('admin.php')));
        exit;
    }

    public static function handle_bulk_actions()
    {
        if (!Exotic_Campaign_Post_Type::user_can_manage_campaigns()) {
            wp_die(esc_html__('You do not have permission to manage campaigns.', 'exotic-campaigns'));
        }

        check_admin_referer('bulk-campaigns');

        $action = sanitize_text_field((string) filter_input(INPUT_POST, 'action'));
        if ($action === '-1') {
            $action = sanitize_text_field((string) filter_input(INPUT_POST, 'action2'));
        }

        $campaign_ids = isset($_POST['campaign_ids']) && is_array($_POST['campaign_ids'])
            ? array_map('absint', wp_unslash($_POST['campaign_ids']))
            : [];

        $campaign_ids = array_values(array_filter($campaign_ids));

        if (empty($campaign_ids)) {
            wp_safe_redirect(add_query_arg(['page' => 'exotic-campaigns'], admin_url('admin.php')));
            exit;
        }

        foreach ($campaign_ids as $campaign_id) {
            if (get_post_type($campaign_id) !== Exotic_Campaign_Post_Type::POST_TYPE) {
                continue;
            }

            switch ($action) {
                case 'activate':
                    update_post_meta($campaign_id, '_campaign_status', 'active');
                    break;
                case 'pause':
                    update_post_meta($campaign_id, '_campaign_status', 'paused');
                    break;
                case 'schedule':
                    update_post_meta($campaign_id, '_campaign_status', 'scheduled');
                    break;
                case 'expire':
                    update_post_meta($campaign_id, '_campaign_status', 'expired');
                    break;
                case 'delete':
                    wp_trash_post($campaign_id);
                    break;
            }
        }

        wp_safe_redirect(add_query_arg([
            'page' => 'exotic-campaigns',
            'updated' => 1,
        ], admin_url('admin.php')));
        exit;
    }

    private static function maybe_handle_bulk_actions()
    {
        if (strtoupper((string) $_SERVER['REQUEST_METHOD']) !== 'POST') {
            return;
        }

        $action = sanitize_text_field((string) filter_input(INPUT_POST, 'action'));
        $action2 = sanitize_text_field((string) filter_input(INPUT_POST, 'action2'));

        $resolved_action = $action !== '-1' && $action !== '' ? $action : $action2;

        if ($resolved_action === '' || $resolved_action === '-1') {
            return;
        }

        self::handle_bulk_actions();
    }

    public static function delete_campaign()
    {
        if (!Exotic_Campaign_Post_Type::user_can_manage_campaigns()) {
            wp_die(esc_html__('You do not have permission to delete campaigns.', 'exotic-campaigns'));
        }

        $campaign_id = absint((string) filter_input(INPUT_GET, 'campaign_id'));
        check_admin_referer('exotic_campaign_delete_' . $campaign_id);

        if ($campaign_id > 0 && get_post_type($campaign_id) === Exotic_Campaign_Post_Type::POST_TYPE) {
            wp_trash_post($campaign_id);
        }

        wp_safe_redirect(add_query_arg([
            'page' => 'exotic-campaigns',
            'updated' => 1,
        ], admin_url('admin.php')));
        exit;
    }

    public static function save_settings()
    {
        if (!Exotic_Campaign_Post_Type::user_can_manage_campaigns()) {
            wp_die(esc_html__('You do not have permission to edit campaign settings.', 'exotic-campaigns'));
        }

        check_admin_referer('exotic_campaign_settings');

        $header = sanitize_text_field((string) filter_input(INPUT_POST, 'exotic_campaigns_trusted_proxy_header'));
        $allowed = array_keys(self::get_proxy_header_options());

        if (!in_array($header, $allowed, true)) {
            $header = '';
        }

        update_option('exotic_campaigns_trusted_proxy_header', $header, false);

        wp_safe_redirect(add_query_arg([
            'page' => 'exotic-campaigns',
            'settings_updated' => 1,
        ], admin_url('admin.php')));
        exit;
    }

    public static function get_status_counts()
    {
        $counts = [
            'all' => 0,
            'active' => 0,
            'scheduled' => 0,
            'paused' => 0,
            'expired' => 0,
        ];

        $all_ids = get_posts([
            'post_type' => Exotic_Campaign_Post_Type::POST_TYPE,
            'post_status' => 'publish',
            'fields' => 'ids',
            'posts_per_page' => -1,
            'no_found_rows' => true,
        ]);

        $counts['all'] = count($all_ids);

        foreach ($all_ids as $campaign_id) {
            $status = get_post_meta($campaign_id, '_campaign_status', true);
            if (isset($counts[$status])) {
                $counts[$status]++;
            }
        }

        return $counts;
    }

    public static function get_proxy_header_options()
    {
        return [
            '' => __('Use REMOTE_ADDR (default, safest)', 'exotic-campaigns'),
            'CF-Connecting-IP' => __('CF-Connecting-IP (Cloudflare)', 'exotic-campaigns'),
            'X-Forwarded-For' => __('X-Forwarded-For (trusted reverse proxy)', 'exotic-campaigns'),
            'X-Real-IP' => __('X-Real-IP', 'exotic-campaigns'),
        ];
    }

    private static function get_campaign_for_form($campaign_id)
    {
        $defaults = [
            'ID' => 0,
            'post_title' => '',
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
            '_campaign_impressions' => 0,
            '_campaign_clicks' => 0,
        ];

        if ($campaign_id < 1) {
            return $defaults;
        }

        $post = get_post($campaign_id);
        if (!$post || $post->post_type !== Exotic_Campaign_Post_Type::POST_TYPE) {
            return $defaults;
        }

        $campaign = $defaults;
        $campaign['ID'] = (int) $post->ID;
        $campaign['post_title'] = $post->post_title;

        foreach ($defaults as $key => $default_value) {
            if (strpos($key, '_campaign_') !== 0) {
                continue;
            }

            $value = get_post_meta($campaign_id, $key, true);
            $campaign[$key] = $value !== '' ? $value : $default_value;
        }

        return $campaign;
    }

    private static function sanitize_campaign_payload()
    {
        $format = Exotic_Campaign_Post_Type::sanitize_format((string) filter_input(INPUT_POST, '_campaign_format'));

        $payload = [
            '_campaign_format' => $format,
            '_campaign_badge_text' => Exotic_Campaign_Post_Type::sanitize_badge((string) filter_input(INPUT_POST, '_campaign_badge_text')),
            '_campaign_description' => Exotic_Campaign_Post_Type::sanitize_description((string) filter_input(INPUT_POST, '_campaign_description')),
            '_campaign_icon_class' => Exotic_Campaign_Post_Type::sanitize_icon_class((string) filter_input(INPUT_POST, '_campaign_icon_class')),
            '_campaign_color_primary' => Exotic_Campaign_Post_Type::sanitize_hex_color((string) filter_input(INPUT_POST, '_campaign_color_primary')),
            '_campaign_color_secondary' => Exotic_Campaign_Post_Type::sanitize_hex_color_optional((string) filter_input(INPUT_POST, '_campaign_color_secondary')),
            '_campaign_image_id' => absint((string) filter_input(INPUT_POST, '_campaign_image_id')),
            '_campaign_image_alt' => Exotic_Campaign_Post_Type::sanitize_alt((string) filter_input(INPUT_POST, '_campaign_image_alt')),
            '_campaign_cta_text' => Exotic_Campaign_Post_Type::sanitize_cta_text((string) filter_input(INPUT_POST, '_campaign_cta_text')),
            '_campaign_cta_url' => Exotic_Campaign_Post_Type::sanitize_http_url((string) filter_input(INPUT_POST, '_campaign_cta_url')),
            '_campaign_cta_visible' => isset($_POST['_campaign_cta_visible']) ? '1' : '0',
            '_campaign_start_date' => Exotic_Campaign_Post_Type::sanitize_datetime_optional(self::normalize_datetime_input((string) filter_input(INPUT_POST, '_campaign_start_date'))),
            '_campaign_end_date' => Exotic_Campaign_Post_Type::sanitize_datetime_optional(self::normalize_datetime_input((string) filter_input(INPUT_POST, '_campaign_end_date'))),
            '_campaign_status' => Exotic_Campaign_Post_Type::sanitize_status((string) filter_input(INPUT_POST, '_campaign_status')),
            '_campaign_priority' => absint((string) filter_input(INPUT_POST, '_campaign_priority')),
        ];

        if ($payload['_campaign_priority'] < 1) {
            $payload['_campaign_priority'] = 10;
        }

        if ($format === 'card') {
            $payload['_campaign_image_id'] = 0;
            $payload['_campaign_image_alt'] = '';
            $payload['_campaign_cta_visible'] = '1';
        }

        return $payload;
    }

    private static function normalize_datetime_input($value)
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
}
