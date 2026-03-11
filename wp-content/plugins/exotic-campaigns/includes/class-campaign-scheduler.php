<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_Campaign_Scheduler
{
    const CRON_HOOK = 'exotic_campaigns_check_schedule';

    public static function register_hooks()
    {
        add_action(self::CRON_HOOK, [__CLASS__, 'run']);
    }

    public static function activate()
    {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + MINUTE_IN_SECONDS, 'hourly', self::CRON_HOOK);
        }
    }

    public static function deactivate()
    {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        while ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
            $timestamp = wp_next_scheduled(self::CRON_HOOK);
        }
    }

    public static function run()
    {
        if (class_exists('Exotic_Campaign_Renderer')) {
            Exotic_Campaign_Renderer::refresh_scheduled_statuses();
        }

        if (class_exists('Exotic_Campaign_Post_Type')) {
            Exotic_Campaign_Post_Type::dedupe_counter_meta_rows();
        }
    }
}
