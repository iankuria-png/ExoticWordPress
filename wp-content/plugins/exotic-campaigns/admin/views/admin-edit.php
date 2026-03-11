<?php

if (!defined('ABSPATH')) {
    exit;
}

$is_new = empty($campaign['ID']);
$campaign_id = (int) $campaign['ID'];
$format = (string) $campaign['_campaign_format'];
$start_local = !empty($campaign['_campaign_start_date']) ? gmdate('Y-m-d\TH:i', strtotime((string) $campaign['_campaign_start_date'])) : '';
$end_local = !empty($campaign['_campaign_end_date']) ? gmdate('Y-m-d\TH:i', strtotime((string) $campaign['_campaign_end_date'])) : '';
$image_id = absint((string) $campaign['_campaign_image_id']);
$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium_large') : '';
?>
<div class="wrap exotic-campaign-admin exotic-campaign-admin--edit">
    <h1><?php echo esc_html($is_new ? __('Add Campaign', 'exotic-campaigns') : __('Edit Campaign', 'exotic-campaigns')); ?></h1>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="exotic-campaign-form">
        <?php wp_nonce_field('exotic_campaign_save'); ?>
        <input type="hidden" name="action" value="exotic_campaign_save" />
        <input type="hidden" name="campaign_id" value="<?php echo (int) $campaign_id; ?>" />

        <div class="exotic-campaign-grid">
            <section class="exotic-campaign-panel">
                <h2><?php esc_html_e('Campaign Content', 'exotic-campaigns'); ?></h2>

                <label class="exotic-field">
                    <span><?php esc_html_e('Campaign Name', 'exotic-campaigns'); ?></span>
                    <input type="text" name="post_title" value="<?php echo esc_attr((string) $campaign['post_title']); ?>" maxlength="100" required />
                </label>

                <fieldset class="exotic-field exotic-fieldset">
                    <legend><?php esc_html_e('Format', 'exotic-campaigns'); ?></legend>
                    <label><input type="radio" name="_campaign_format" value="card" <?php checked($format, 'card'); ?> /> <?php esc_html_e('Card', 'exotic-campaigns'); ?></label>
                    <label><input type="radio" name="_campaign_format" value="image" <?php checked($format, 'image'); ?> /> <?php esc_html_e('Image', 'exotic-campaigns'); ?></label>
                </fieldset>

                <div class="exotic-campaign-format exotic-campaign-format--card" data-format="card">
                    <label class="exotic-field">
                        <span><?php esc_html_e('Badge Text', 'exotic-campaigns'); ?></span>
                        <input type="text" name="_campaign_badge_text" value="<?php echo esc_attr((string) $campaign['_campaign_badge_text']); ?>" maxlength="20" />
                    </label>

                    <label class="exotic-field">
                        <span><?php esc_html_e('Description', 'exotic-campaigns'); ?></span>
                        <textarea name="_campaign_description" rows="4" maxlength="200"><?php echo esc_textarea((string) $campaign['_campaign_description']); ?></textarea>
                    </label>

                    <label class="exotic-field">
                        <span><?php esc_html_e('Icon Class (Font Awesome 4.7)', 'exotic-campaigns'); ?></span>
                        <input type="text" name="_campaign_icon_class" value="<?php echo esc_attr((string) $campaign['_campaign_icon_class']); ?>" placeholder="fa fa-diamond" />
                    </label>

                    <div class="exotic-field-row exotic-field-row--colors">
                        <label class="exotic-field">
                            <span><?php esc_html_e('Primary Color', 'exotic-campaigns'); ?></span>
                            <input type="text" class="campaign-color-picker" name="_campaign_color_primary" value="<?php echo esc_attr((string) $campaign['_campaign_color_primary']); ?>" />
                        </label>
                        <label class="exotic-field">
                            <span><?php esc_html_e('Secondary Color', 'exotic-campaigns'); ?></span>
                            <input type="text" class="campaign-color-picker" name="_campaign_color_secondary" value="<?php echo esc_attr((string) $campaign['_campaign_color_secondary']); ?>" />
                        </label>
                    </div>
                </div>

                <div class="exotic-campaign-format exotic-campaign-format--image" data-format="image">
                    <input type="hidden" name="_campaign_image_id" id="campaign-image-id" value="<?php echo (int) $image_id; ?>" />

                    <div class="exotic-field exotic-media-field">
                        <span><?php esc_html_e('Campaign Image', 'exotic-campaigns'); ?></span>
                        <p class="description"><?php esc_html_e('Recommended: 800x440px (minimum 400x220px).', 'exotic-campaigns'); ?></p>
                        <div class="exotic-media-preview" id="campaign-image-preview">
                            <?php if ($image_url) : ?>
                                <img src="<?php echo esc_url($image_url); ?>" alt="" />
                            <?php endif; ?>
                        </div>
                        <button type="button" class="button" id="campaign-image-select"><?php esc_html_e('Choose Image', 'exotic-campaigns'); ?></button>
                        <button type="button" class="button-link-delete" id="campaign-image-remove"><?php esc_html_e('Remove image', 'exotic-campaigns'); ?></button>
                    </div>

                    <label class="exotic-field">
                        <span><?php esc_html_e('Image Alt Text', 'exotic-campaigns'); ?></span>
                        <input type="text" name="_campaign_image_alt" value="<?php echo esc_attr((string) $campaign['_campaign_image_alt']); ?>" maxlength="125" />
                    </label>

                    <label class="exotic-field exotic-field--checkbox">
                        <input type="checkbox" name="_campaign_cta_visible" value="1" <?php checked((string) $campaign['_campaign_cta_visible'], '1'); ?> />
                        <span><?php esc_html_e('Show CTA button overlay on image', 'exotic-campaigns'); ?></span>
                    </label>
                </div>

                <h3><?php esc_html_e('Shared CTA Fields', 'exotic-campaigns'); ?></h3>

                <label class="exotic-field">
                    <span><?php esc_html_e('CTA Text', 'exotic-campaigns'); ?></span>
                    <input type="text" name="_campaign_cta_text" value="<?php echo esc_attr((string) $campaign['_campaign_cta_text']); ?>" maxlength="30" />
                </label>

                <label class="exotic-field">
                    <span><?php esc_html_e('CTA URL', 'exotic-campaigns'); ?></span>
                    <input type="url" name="_campaign_cta_url" value="<?php echo esc_attr((string) $campaign['_campaign_cta_url']); ?>" placeholder="https://" />
                </label>
            </section>

            <aside class="exotic-campaign-panel">
                <h2><?php esc_html_e('Publishing & Schedule', 'exotic-campaigns'); ?></h2>

                <label class="exotic-field">
                    <span><?php esc_html_e('Status', 'exotic-campaigns'); ?></span>
                    <select name="_campaign_status">
                        <option value="active" <?php selected((string) $campaign['_campaign_status'], 'active'); ?>><?php esc_html_e('Active', 'exotic-campaigns'); ?></option>
                        <option value="scheduled" <?php selected((string) $campaign['_campaign_status'], 'scheduled'); ?>><?php esc_html_e('Scheduled', 'exotic-campaigns'); ?></option>
                        <option value="paused" <?php selected((string) $campaign['_campaign_status'], 'paused'); ?>><?php esc_html_e('Paused', 'exotic-campaigns'); ?></option>
                        <option value="expired" <?php selected((string) $campaign['_campaign_status'], 'expired'); ?>><?php esc_html_e('Expired', 'exotic-campaigns'); ?></option>
                    </select>
                </label>

                <label class="exotic-field">
                    <span><?php esc_html_e('Priority (lower appears first)', 'exotic-campaigns'); ?></span>
                    <input type="number" min="1" name="_campaign_priority" value="<?php echo esc_attr((string) $campaign['_campaign_priority']); ?>" />
                </label>

                <label class="exotic-field">
                    <span><?php esc_html_e('Start Date', 'exotic-campaigns'); ?></span>
                    <input type="datetime-local" name="_campaign_start_date" value="<?php echo esc_attr($start_local); ?>" />
                </label>

                <label class="exotic-field">
                    <span><?php esc_html_e('End Date', 'exotic-campaigns'); ?></span>
                    <input type="datetime-local" name="_campaign_end_date" value="<?php echo esc_attr($end_local); ?>" />
                </label>

                <h3><?php esc_html_e('Live Preview', 'exotic-campaigns'); ?></h3>

                <div class="campaign-preview" id="campaign-live-preview">
                    <article class="ad-card ad-card--campaign-preview">
                        <div class="ad-card__content">
                            <div class="ad-card__header">
                                <div class="ad-card__icon"><i class="fa fa-bullhorn"></i></div>
                                <span class="ad-card__badge" data-preview-badge><?php echo esc_html((string) $campaign['_campaign_badge_text']); ?></span>
                            </div>
                            <h3 class="ad-card__title" data-preview-title><?php echo esc_html((string) $campaign['post_title']); ?></h3>
                            <p class="ad-card__copy" data-preview-copy><?php echo esc_html((string) $campaign['_campaign_description']); ?></p>
                            <span class="ad-card__cta ad-card__cta--primary" data-preview-cta>
                                <span><?php echo esc_html((string) $campaign['_campaign_cta_text']); ?></span>
                                <i class="fa fa-arrow-right"></i>
                            </span>
                        </div>
                    </article>
                </div>

                <p class="submit">
                    <button type="submit" class="button button-primary button-large"><?php esc_html_e('Save Campaign', 'exotic-campaigns'); ?></button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=exotic-campaigns')); ?>" class="button button-secondary"><?php esc_html_e('Back to list', 'exotic-campaigns'); ?></a>
                </p>
            </aside>
        </div>
    </form>
</div>
