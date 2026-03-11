<?php

if (!defined('ABSPATH')) {
    exit;
}

$campaign_id = get_the_ID();
$title = get_the_title($campaign_id);
$badge_text = get_post_meta($campaign_id, '_campaign_badge_text', true);
$description = get_post_meta($campaign_id, '_campaign_description', true);
$icon_class = get_post_meta($campaign_id, '_campaign_icon_class', true) ?: 'fa fa-bullhorn';
$cta_text = get_post_meta($campaign_id, '_campaign_cta_text', true) ?: __('Learn More', 'exotic-campaigns');
$cta_url = get_post_meta($campaign_id, '_campaign_cta_url', true) ?: home_url('/');
$primary = get_post_meta($campaign_id, '_campaign_color_primary', true) ?: '#AB1C2F';
$secondary = get_post_meta($campaign_id, '_campaign_color_secondary', true) ?: $primary;

$article_class = 'ad-card ad-card--campaign ad-card--campaign-' . (int) $campaign_id;
$style = sprintf(
    '--campaign-cta-start:%s;--campaign-cta-end:%s;--campaign-badge-bg:%s;--campaign-badge-color:#ffffff;--campaign-icon-color:%s;',
    esc_attr($primary),
    esc_attr($secondary),
    esc_attr($primary),
    esc_attr($primary)
);
?>
<article class="<?php echo esc_attr($article_class); ?>" data-campaign-id="<?php echo (int) $campaign_id; ?>" style="<?php echo esc_attr($style); ?>">
    <div class="ad-card__bg-icon"><i class="<?php echo esc_attr($icon_class); ?>" aria-hidden="true"></i></div>
    <div class="ad-card__content">
        <div class="ad-card__header">
            <div class="ad-card__icon"><i class="<?php echo esc_attr($icon_class); ?>" aria-hidden="true"></i></div>
            <?php if (!empty($badge_text)) : ?>
                <span class="ad-card__badge"><?php echo esc_html($badge_text); ?></span>
            <?php endif; ?>
        </div>
        <h3 class="ad-card__title"><?php echo esc_html($title); ?></h3>
        <p class="ad-card__copy"><?php echo esc_html($description); ?></p>
        <a class="ad-card__cta ad-card__cta--primary ad-card__cta--campaign js-campaign-click"
           href="<?php echo esc_url($cta_url); ?>"
           data-campaign-id="<?php echo (int) $campaign_id; ?>">
            <span><?php echo esc_html($cta_text); ?></span>
            <i class="fa fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>
</article>
