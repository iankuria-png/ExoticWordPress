<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_Campaign_Renderer
{
    public static function register_image_sizes()
    {
        // Dedicated hard-crop size for image campaign cards to prevent layout stretch.
        add_image_size('exotic_campaign_card', 640, 840, true);
    }

    public static function enqueue_frontend_assets()
    {
        if (!is_front_page()) {
            return;
        }

        $tracking_file = EXOTIC_CAMPAIGNS_PATH . 'frontend/js/campaign-tracking.js';
        if (!file_exists($tracking_file)) {
            return;
        }

        wp_enqueue_script(
            'exotic-campaign-tracking',
            EXOTIC_CAMPAIGNS_URL . 'frontend/js/campaign-tracking.js',
            [],
            filemtime($tracking_file),
            true
        );

        wp_localize_script('exotic-campaign-tracking', 'exoticCampaignTracking', [
            'impressionUrl' => rest_url('exotic-campaigns/v1/track/impression'),
            'clickUrl' => rest_url('exotic-campaigns/v1/track/click'),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    public static function refresh_scheduled_statuses()
    {
        $now = current_time('mysql');

        $to_activate = get_posts([
            'post_type' => Exotic_Campaign_Post_Type::POST_TYPE,
            'post_status' => 'publish',
            'fields' => 'ids',
            'posts_per_page' => -1,
            'no_found_rows' => true,
            'meta_query' => [
                [
                    'key' => '_campaign_status',
                    'value' => 'scheduled',
                    'compare' => '=',
                ],
                [
                    'key' => '_campaign_start_date',
                    'value' => $now,
                    'compare' => '<=',
                    'type' => 'DATETIME',
                ],
            ],
        ]);

        foreach ($to_activate as $campaign_id) {
            update_post_meta((int) $campaign_id, '_campaign_status', 'active');
        }

        $to_expire = get_posts([
            'post_type' => Exotic_Campaign_Post_Type::POST_TYPE,
            'post_status' => 'publish',
            'fields' => 'ids',
            'posts_per_page' => -1,
            'no_found_rows' => true,
            'meta_query' => [
                [
                    'key' => '_campaign_status',
                    'value' => 'active',
                    'compare' => '=',
                ],
                [
                    'key' => '_campaign_end_date',
                    'compare' => 'EXISTS',
                ],
                [
                    'key' => '_campaign_end_date',
                    'value' => '',
                    'compare' => '!=',
                ],
                [
                    'key' => '_campaign_end_date',
                    'value' => $now,
                    'compare' => '<=',
                    'type' => 'DATETIME',
                ],
            ],
        ]);

        foreach ($to_expire as $campaign_id) {
            update_post_meta((int) $campaign_id, '_campaign_status', 'expired');
        }
    }

    public static function get_active_campaigns($limit = 6)
    {
        self::refresh_scheduled_statuses();

        $limit = max(1, min(12, absint($limit)));

        $query = new WP_Query([
            'post_type' => Exotic_Campaign_Post_Type::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => [
                'meta_value_num' => 'ASC',
                'date' => 'DESC',
            ],
            'meta_key' => '_campaign_priority',
            'meta_query' => [
                [
                    'key' => '_campaign_status',
                    'value' => 'active',
                    'compare' => '=',
                ],
            ],
            'no_found_rows' => true,
        ]);

        return $query;
    }

    public static function render_carousel($echo = true)
    {
        $query = self::get_active_campaigns(6);

        if (!$query->have_posts()) {
            return false;
        }

        ob_start();
        ?>
        <div class="static-ad-carousel" aria-label="<?php esc_attr_e('Sponsored', 'exotic-campaigns'); ?>">
            <?php
            while ($query->have_posts()) {
                $query->the_post();

                $campaign_id = get_the_ID();
                $format = get_post_meta($campaign_id, '_campaign_format', true) ?: 'card';
                $template = $format === 'image' ? 'campaign-image.php' : 'campaign-card.php';
                $template_path = EXOTIC_CAMPAIGNS_PATH . 'templates/' . $template;

                if (file_exists($template_path)) {
                    include $template_path;
                }
            }

            wp_reset_postdata();
            ?>
        </div>
        <?php

        $markup = ob_get_clean();

        if ($echo) {
            echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        return $markup;
    }
}
