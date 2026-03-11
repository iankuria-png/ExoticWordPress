<?php

if (!defined('ABSPATH')) {
    exit;
}

$notice_error = sanitize_text_field((string) filter_input(INPUT_GET, 'error'));
$notice_updated = absint((string) filter_input(INPUT_GET, 'updated'));
$notice_settings_updated = absint((string) filter_input(INPUT_GET, 'settings_updated'));
$selected_proxy_header = (string) get_option('exotic_campaigns_trusted_proxy_header', '');
$proxy_header_options = Exotic_Campaign_Admin_Page::get_proxy_header_options();
?>
<div class="wrap exotic-campaign-admin">
    <h1 class="wp-heading-inline"><?php esc_html_e('Campaigns', 'exotic-campaigns'); ?></h1>
    <a href="<?php echo esc_url(add_query_arg(['page' => 'exotic-campaigns', 'action' => 'new'], admin_url('admin.php'))); ?>" class="page-title-action">
        <?php esc_html_e('Add New', 'exotic-campaigns'); ?>
    </a>
    <hr class="wp-header-end" />

    <?php if ($notice_error === 'save_failed') : ?>
        <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Campaign save failed. Please try again.', 'exotic-campaigns'); ?></p></div>
    <?php endif; ?>

    <?php if ($notice_updated === 1) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Campaigns updated.', 'exotic-campaigns'); ?></p></div>
    <?php endif; ?>

    <?php if ($notice_settings_updated === 1) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Tracking settings updated.', 'exotic-campaigns'); ?></p></div>
    <?php endif; ?>

    <div class="exotic-campaign-settings-box">
        <h2><?php esc_html_e('Tracking IP Source', 'exotic-campaigns'); ?></h2>
        <p><?php esc_html_e('Keep default unless this site is behind a trusted proxy/CDN and you need header-based client IP resolution for rate limiting.', 'exotic-campaigns'); ?></p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="exotic-campaign-settings-form">
            <?php wp_nonce_field('exotic_campaign_settings'); ?>
            <input type="hidden" name="action" value="exotic_campaign_settings" />
            <label>
                <span><?php esc_html_e('Trusted Proxy Header', 'exotic-campaigns'); ?></span>
                <select name="exotic_campaigns_trusted_proxy_header">
                    <?php foreach ($proxy_header_options as $header_value => $header_label) : ?>
                        <option value="<?php echo esc_attr($header_value); ?>" <?php selected($selected_proxy_header, $header_value); ?>>
                            <?php echo esc_html($header_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="button button-secondary"><?php esc_html_e('Save Tracking Settings', 'exotic-campaigns'); ?></button>
        </form>
    </div>

    <?php $list_table->views(); ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=exotic-campaigns')); ?>">
        <?php
        $list_table->search_box(__('Search Campaigns', 'exotic-campaigns'), 'campaign-search');
        ?>
        <input type="hidden" name="page" value="exotic-campaigns" />
        <?php
        $list_table->display();
        ?>
    </form>
</div>
