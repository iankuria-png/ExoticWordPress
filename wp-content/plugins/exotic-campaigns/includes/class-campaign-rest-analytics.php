<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_Campaign_REST_Analytics
{
    public function register_routes($namespace)
    {
        register_rest_route($namespace, '/analytics/summary', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_summary'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        register_rest_route($namespace, '/analytics/campaign/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_campaign_analytics'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);
    }

    public function permissions_check()
    {
        return Exotic_Campaign_Post_Type::user_can_manage_campaigns();
    }

    public function get_summary()
    {
        $campaign_ids = get_posts([
            'post_type' => Exotic_Campaign_Post_Type::POST_TYPE,
            'post_status' => 'publish',
            'fields' => 'ids',
            'posts_per_page' => -1,
            'no_found_rows' => true,
        ]);

        $summary = [
            'campaigns_total' => count($campaign_ids),
            'campaigns_active' => 0,
            'campaigns_scheduled' => 0,
            'campaigns_paused' => 0,
            'campaigns_expired' => 0,
            'impressions_total' => 0,
            'clicks_total' => 0,
            'avg_ctr' => 0,
        ];

        $top = [];

        foreach ($campaign_ids as $campaign_id) {
            $status = (string) get_post_meta($campaign_id, '_campaign_status', true);
            if ($status === 'active') {
                $summary['campaigns_active']++;
            } elseif ($status === 'scheduled') {
                $summary['campaigns_scheduled']++;
            } elseif ($status === 'paused') {
                $summary['campaigns_paused']++;
            } elseif ($status === 'expired') {
                $summary['campaigns_expired']++;
            }

            $impressions = (int) get_post_meta($campaign_id, '_campaign_impressions', true);
            $clicks = (int) get_post_meta($campaign_id, '_campaign_clicks', true);

            $summary['impressions_total'] += $impressions;
            $summary['clicks_total'] += $clicks;

            $top[] = [
                'id' => (int) $campaign_id,
                'title' => get_the_title($campaign_id),
                'status' => $status,
                'impressions' => $impressions,
                'clicks' => $clicks,
                'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0,
            ];
        }

        if ($summary['impressions_total'] > 0) {
            $summary['avg_ctr'] = round(($summary['clicks_total'] / $summary['impressions_total']) * 100, 2);
        }

        usort($top, static function ($a, $b) {
            return $b['impressions'] <=> $a['impressions'];
        });

        return rest_ensure_response([
            'summary' => $summary,
            'top_campaigns' => array_slice($top, 0, 10),
        ]);
    }

    public function get_campaign_analytics(WP_REST_Request $request)
    {
        global $wpdb;

        $campaign_id = absint($request['id']);
        $post = get_post($campaign_id);

        if (!$post || $post->post_type !== Exotic_Campaign_Post_Type::POST_TYPE) {
            return new WP_Error('campaign_not_found', 'Campaign not found.', ['status' => 404]);
        }

        $table = $wpdb->prefix . 'campaign_daily_stats';

        $daily_stats = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT stat_date, impressions, clicks
                FROM {$table}
                WHERE campaign_id = %d
                ORDER BY stat_date DESC
                LIMIT 365",
                $campaign_id
            ),
            ARRAY_A
        );

        $impressions_total = (int) get_post_meta($campaign_id, '_campaign_impressions', true);
        $clicks_total = (int) get_post_meta($campaign_id, '_campaign_clicks', true);

        return rest_ensure_response([
            'campaign' => [
                'id' => $campaign_id,
                'title' => $post->post_title,
                'status' => (string) get_post_meta($campaign_id, '_campaign_status', true),
                'impressions_total' => $impressions_total,
                'clicks_total' => $clicks_total,
                'ctr' => $impressions_total > 0 ? round(($clicks_total / $impressions_total) * 100, 2) : 0,
            ],
            'daily' => array_map(static function ($row) {
                return [
                    'date' => (string) $row['stat_date'],
                    'impressions' => (int) $row['impressions'],
                    'clicks' => (int) $row['clicks'],
                    'ctr' => (int) $row['impressions'] > 0
                        ? round(((int) $row['clicks'] / (int) $row['impressions']) * 100, 2)
                        : 0,
                ];
            }, $daily_stats),
        ]);
    }
}
