<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="exotic-campaign-dashboard" id="exotic-campaign-dashboard">
    <section class="exotic-campaign-dashboard__hero" aria-labelledby="campaign-dashboard-overview-title">
        <header class="exotic-campaign-dashboard__header">
            <p class="exotic-campaign-dashboard__eyebrow"><?php esc_html_e('Campaign Operations', 'exotic-campaigns'); ?></p>
            <h2 id="campaign-dashboard-overview-title"><?php esc_html_e('Campaign Overview', 'exotic-campaigns'); ?></h2>
            <p><?php esc_html_e('Manage homepage ad campaigns, schedules, and performance in one place.', 'exotic-campaigns'); ?></p>
        </header>
    </section>

    <section class="exotic-campaign-stats" id="campaign-dashboard-stats" aria-label="<?php esc_attr_e('Campaign stats', 'exotic-campaigns'); ?>">
        <article class="exotic-campaign-stat-card">
            <p class="exotic-campaign-stat-card__label"><?php esc_html_e('Active', 'exotic-campaigns'); ?></p>
            <p class="exotic-campaign-stat-card__value" data-stat="active">0</p>
        </article>
        <article class="exotic-campaign-stat-card">
            <p class="exotic-campaign-stat-card__label"><?php esc_html_e('Impressions', 'exotic-campaigns'); ?></p>
            <p class="exotic-campaign-stat-card__value" data-stat="impressions">0</p>
        </article>
        <article class="exotic-campaign-stat-card">
            <p class="exotic-campaign-stat-card__label"><?php esc_html_e('Clicks', 'exotic-campaigns'); ?></p>
            <p class="exotic-campaign-stat-card__value" data-stat="clicks">0</p>
        </article>
        <article class="exotic-campaign-stat-card">
            <p class="exotic-campaign-stat-card__label"><?php esc_html_e('Avg CTR', 'exotic-campaigns'); ?></p>
            <p class="exotic-campaign-stat-card__value" data-stat="ctr">0%</p>
        </article>
    </section>

    <section class="exotic-campaign-dashboard__toolbar" aria-label="<?php esc_attr_e('Campaign controls', 'exotic-campaigns'); ?>">
        <label class="exotic-campaign-filter" for="campaign-status-filter">
            <span><?php esc_html_e('Status', 'exotic-campaigns'); ?></span>
            <select id="campaign-status-filter">
                <option value=""><?php esc_html_e('All', 'exotic-campaigns'); ?></option>
                <option value="active"><?php esc_html_e('Active', 'exotic-campaigns'); ?></option>
                <option value="scheduled"><?php esc_html_e('Scheduled', 'exotic-campaigns'); ?></option>
                <option value="paused"><?php esc_html_e('Paused', 'exotic-campaigns'); ?></option>
                <option value="expired"><?php esc_html_e('Expired', 'exotic-campaigns'); ?></option>
            </select>
        </label>
        <div class="exotic-campaign-dashboard__toolbar-actions">
            <button type="button" class="exotic-btn exotic-btn--secondary" id="campaign-refresh-btn"><?php esc_html_e('Refresh', 'exotic-campaigns'); ?></button>
            <button type="button" class="exotic-btn exotic-btn--primary" id="campaign-create-btn"><?php esc_html_e('Add Campaign', 'exotic-campaigns'); ?></button>
        </div>
    </section>

    <section class="exotic-campaign-table-wrap">
        <table class="exotic-campaign-table" id="campaign-dashboard-table">
            <caption class="exotic-campaign-table__caption"><?php esc_html_e('Campaign list and performance metrics', 'exotic-campaigns'); ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e('Campaign', 'exotic-campaigns'); ?></th>
                    <th scope="col"><?php esc_html_e('Format', 'exotic-campaigns'); ?></th>
                    <th scope="col"><?php esc_html_e('Status', 'exotic-campaigns'); ?></th>
                    <th scope="col"><?php esc_html_e('Priority', 'exotic-campaigns'); ?></th>
                    <th scope="col"><?php esc_html_e('Impressions', 'exotic-campaigns'); ?></th>
                    <th scope="col"><?php esc_html_e('Clicks', 'exotic-campaigns'); ?></th>
                    <th scope="col"><?php esc_html_e('CTR', 'exotic-campaigns'); ?></th>
                    <th scope="col"><?php esc_html_e('Actions', 'exotic-campaigns'); ?></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>

    <p class="exotic-campaign-feedback" id="campaign-dashboard-feedback" aria-live="polite"></p>

    <?php include EXOTIC_CAMPAIGNS_PATH . 'frontend/views/dashboard-form.php'; ?>
</div>
