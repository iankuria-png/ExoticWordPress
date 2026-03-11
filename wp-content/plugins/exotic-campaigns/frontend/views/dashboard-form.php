<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="exotic-campaign-modal" id="campaign-form-modal" hidden aria-hidden="true">
    <div class="exotic-campaign-modal__backdrop" data-modal-close></div>
    <div class="exotic-campaign-modal__panel" role="dialog" aria-modal="true" aria-labelledby="campaign-form-title">
        <header class="exotic-campaign-modal__header">
            <h3 id="campaign-form-title"><?php esc_html_e('Campaign', 'exotic-campaigns'); ?></h3>
            <button type="button" class="exotic-campaign-modal__close" data-modal-close aria-label="<?php esc_attr_e('Close', 'exotic-campaigns'); ?>">&times;</button>
        </header>

        <form id="campaign-dashboard-form" class="exotic-campaign-form-grid">
            <input type="hidden" name="id" value="" />
            <p class="exotic-campaign-form-feedback" id="campaign-form-feedback" aria-live="polite"></p>

            <div class="exotic-campaign-form-grid__body">
                <label>
                    <span><?php esc_html_e('Campaign Name', 'exotic-campaigns'); ?></span>
                    <input type="text" name="post_title" maxlength="100" required />
                </label>

                <label>
                    <span><?php esc_html_e('Format', 'exotic-campaigns'); ?></span>
                    <select name="_campaign_format">
                        <option value="card"><?php esc_html_e('Card', 'exotic-campaigns'); ?></option>
                        <option value="image"><?php esc_html_e('Image', 'exotic-campaigns'); ?></option>
                    </select>
                </label>

                <div class="campaign-form-block" data-format="card">
                    <label>
                        <span><?php esc_html_e('Badge Text', 'exotic-campaigns'); ?></span>
                        <input type="text" name="_campaign_badge_text" maxlength="20" />
                    </label>
                    <label>
                        <span><?php esc_html_e('Description', 'exotic-campaigns'); ?></span>
                        <textarea name="_campaign_description" rows="3" maxlength="200"></textarea>
                    </label>
                    <label>
                        <span><?php esc_html_e('Icon Class', 'exotic-campaigns'); ?></span>
                        <input type="text" name="_campaign_icon_class" placeholder="fa fa-diamond" />
                    </label>
                    <div class="campaign-form-row">
                        <label>
                            <span><?php esc_html_e('Primary Color', 'exotic-campaigns'); ?></span>
                            <input type="text" name="_campaign_color_primary" placeholder="#AB1C2F" />
                        </label>
                        <label>
                            <span><?php esc_html_e('Secondary Color', 'exotic-campaigns'); ?></span>
                            <input type="text" name="_campaign_color_secondary" placeholder="#BD263A" />
                        </label>
                    </div>
                </div>

                <div class="campaign-form-block" data-format="image" hidden>
                    <input type="hidden" name="_campaign_image_id" value="" />
                    <label>
                        <span><?php esc_html_e('Campaign Image', 'exotic-campaigns'); ?></span>
                        <div class="campaign-image-picker">
                            <div class="campaign-image-preview" data-image-preview></div>
                            <div class="campaign-image-picker__actions">
                                <button type="button" class="exotic-row-btn" data-image-select><?php esc_html_e('Choose Image', 'exotic-campaigns'); ?></button>
                                <button type="button" class="button-link-delete" data-image-remove><?php esc_html_e('Remove', 'exotic-campaigns'); ?></button>
                            </div>
                        </div>
                    </label>
                    <label>
                        <span><?php esc_html_e('Image Alt Text', 'exotic-campaigns'); ?></span>
                        <input type="text" name="_campaign_image_alt" maxlength="125" />
                    </label>
                    <label class="campaign-form-checkbox">
                        <input type="checkbox" name="_campaign_cta_visible" value="1" checked />
                        <span><?php esc_html_e('Show CTA overlay button', 'exotic-campaigns'); ?></span>
                    </label>
                </div>

                <div class="campaign-form-block">
                    <div class="campaign-form-row">
                        <label>
                            <span><?php esc_html_e('CTA Text', 'exotic-campaigns'); ?></span>
                            <input type="text" name="_campaign_cta_text" maxlength="30" />
                        </label>
                        <label>
                            <span><?php esc_html_e('CTA URL', 'exotic-campaigns'); ?></span>
                            <input type="url" name="_campaign_cta_url" placeholder="https://" />
                        </label>
                    </div>
                    <div class="campaign-form-row">
                        <label>
                            <span><?php esc_html_e('Status', 'exotic-campaigns'); ?></span>
                            <select name="_campaign_status">
                                <option value="active"><?php esc_html_e('Active', 'exotic-campaigns'); ?></option>
                                <option value="scheduled"><?php esc_html_e('Scheduled', 'exotic-campaigns'); ?></option>
                                <option value="paused"><?php esc_html_e('Paused', 'exotic-campaigns'); ?></option>
                                <option value="expired"><?php esc_html_e('Expired', 'exotic-campaigns'); ?></option>
                            </select>
                        </label>
                        <label>
                            <span><?php esc_html_e('Priority', 'exotic-campaigns'); ?></span>
                            <input type="number" name="_campaign_priority" min="1" value="10" />
                        </label>
                    </div>
                    <div class="campaign-form-row">
                        <label>
                            <span><?php esc_html_e('Start Date', 'exotic-campaigns'); ?></span>
                            <input type="datetime-local" name="_campaign_start_date" />
                        </label>
                        <label>
                            <span><?php esc_html_e('End Date', 'exotic-campaigns'); ?></span>
                            <input type="datetime-local" name="_campaign_end_date" />
                        </label>
                    </div>
                </div>
            </div>

            <footer class="exotic-campaign-modal__footer">
                <button type="button" class="exotic-btn exotic-btn--secondary" data-modal-close><?php esc_html_e('Cancel', 'exotic-campaigns'); ?></button>
                <button type="submit" class="exotic-btn exotic-btn--primary" id="campaign-save-btn"><?php esc_html_e('Save Campaign', 'exotic-campaigns'); ?></button>
            </footer>
        </form>
    </div>
</div>
