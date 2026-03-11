<?php

if (!defined('ABSPATH')) {
    exit;
}

$campaign_id = get_the_ID();
$title = get_the_title($campaign_id);
$image_id = absint(get_post_meta($campaign_id, '_campaign_image_id', true));
$image_alt = get_post_meta($campaign_id, '_campaign_image_alt', true);
$cta_text = get_post_meta($campaign_id, '_campaign_cta_text', true) ?: __('Learn More', 'exotic-campaigns');
$cta_url = get_post_meta($campaign_id, '_campaign_cta_url', true) ?: home_url('/');
$cta_visible = get_post_meta($campaign_id, '_campaign_cta_visible', true) !== '0';
$primary = get_post_meta($campaign_id, '_campaign_color_primary', true) ?: '#AB1C2F';
$secondary = get_post_meta($campaign_id, '_campaign_color_secondary', true) ?: $primary;

$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'exotic_campaign_card') : '';
$image_srcset = $image_id ? wp_get_attachment_image_srcset($image_id, 'exotic_campaign_card') : '';

if (!$image_url && $image_id) {
    $image_url = wp_get_attachment_image_url($image_id, 'large');
}

if (!$image_srcset && $image_id) {
    $image_srcset = wp_get_attachment_image_srcset($image_id, 'large');
}

if (!$image_alt && $image_id) {
    $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
}

if (!$image_alt) {
    $image_alt = $title;
}

$style = sprintf(
    '--campaign-cta-start:%s;--campaign-cta-end:%s;--campaign-card-border:rgba(255,255,255,0.12);',
    esc_attr($primary),
    esc_attr($secondary)
);
?>
<article class="ad-card ad-card--image ad-card--campaign ad-card--campaign-<?php echo (int) $campaign_id; ?>"
         data-campaign-id="<?php echo (int) $campaign_id; ?>"
         style="<?php echo esc_attr($style); ?>">
    <a class="ad-card__image-link js-campaign-click" href="<?php echo esc_url($cta_url); ?>" data-campaign-id="<?php echo (int) $campaign_id; ?>">
        <?php if ($image_url) : ?>
            <img
                class="ad-card__image"
                src="<?php echo esc_url($image_url); ?>"
                <?php if ($image_srcset) : ?>
                    srcset="<?php echo esc_attr($image_srcset); ?>"
                <?php endif; ?>
                sizes="(max-width: 768px) 85vw, (max-width: 1200px) 33vw, 360px"
                alt="<?php echo esc_attr($image_alt); ?>"
                loading="lazy"
                decoding="async"
            />
        <?php endif; ?>

        <?php if ($cta_visible) : ?>
            <span class="ad-card__cta ad-card__cta--primary ad-card__cta--campaign ad-card__cta--overlay">
                <span><?php echo esc_html($cta_text); ?></span>
                <i class="fa fa-arrow-right" aria-hidden="true"></i>
            </span>
        <?php endif; ?>
    </a>
</article>
