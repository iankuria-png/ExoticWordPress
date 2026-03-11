<?php

if (!defined('ABSPATH')) {
    exit;
}

$notice = isset($_GET['notice']) ? sanitize_key((string) wp_unslash($_GET['notice'])) : '';
$message = isset($_GET['message']) ? sanitize_text_field((string) wp_unslash($_GET['message'])) : '';

$rule_defaults = [
    'id' => '',
    'label' => '',
    'enabled' => 1,
    'target_key' => 'global_body_text',
    'scope_type' => 'site',
    'scope_value' => '',
    'font_id' => 'system_inter',
    'font_weight' => '',
    'font_style' => 'normal',
    'delivery_mode' => 'auto',
];

$rule_form = is_array($edit_rule) ? array_merge($rule_defaults, $edit_rule) : $rule_defaults;
$scope_options = [
    'site' => __('Whole Site', 'exotic-font-manager'),
    'front_page' => __('Front Page', 'exotic-font-manager'),
    'post_type_single' => __('Single Post Type', 'exotic-font-manager'),
    'post_type_archive' => __('Post Type Archive', 'exotic-font-manager'),
    'page' => __('Specific Page ID', 'exotic-font-manager'),
    'post' => __('Specific Post ID', 'exotic-font-manager'),
    'taxonomy' => __('Taxonomy Archive', 'exotic-font-manager'),
];

$font_options = EFM_Font_Library::index_by_id($font_library);
$history = EFM_Settings_Repository::get_history();
?>
<div class="wrap efm-admin-wrap">
    <div class="efm-hero">
        <div>
            <p class="efm-hero__eyebrow"><?php esc_html_e('Exotic Font Manager', 'exotic-font-manager'); ?></p>
            <h1><?php esc_html_e('Typography Control Center', 'exotic-font-manager'); ?></h1>
            <p><?php esc_html_e('Create safe font rules for the full site, specific templates, or individual pages.', 'exotic-font-manager'); ?></p>
        </div>
        <div class="efm-hero__meta">
            <span><?php echo esc_html(count($rules)); ?> <?php esc_html_e('rules', 'exotic-font-manager'); ?></span>
            <span><?php echo esc_html(count($font_options)); ?> <?php esc_html_e('fonts', 'exotic-font-manager'); ?></span>
        </div>
    </div>

    <?php if ($notice === 'rule_saved') : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Font rule saved.', 'exotic-font-manager'); ?></p></div>
    <?php elseif ($notice === 'rule_deleted') : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Font rule deleted.', 'exotic-font-manager'); ?></p></div>
    <?php elseif ($notice === 'font_saved') : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Font saved to library.', 'exotic-font-manager'); ?></p></div>
    <?php elseif ($notice === 'font_deleted') : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Font removed from library.', 'exotic-font-manager'); ?></p></div>
    <?php elseif ($notice === 'font_failed') : ?>
        <div class="notice notice-error is-dismissible"><p><?php echo esc_html($message !== '' ? $message : __('Font operation failed.', 'exotic-font-manager')); ?></p></div>
    <?php elseif ($notice === 'settings_rolled_back') : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Typography settings rolled back to the previous snapshot.', 'exotic-font-manager'); ?></p></div>
    <?php elseif ($notice === 'settings_reset') : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Typography rules reset to default.', 'exotic-font-manager'); ?></p></div>
    <?php elseif ($notice === 'profile_imported') : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Typography profile imported successfully.', 'exotic-font-manager'); ?></p></div>
    <?php endif; ?>

    <div class="efm-grid">
        <section class="efm-card">
            <h2><?php esc_html_e('Rule Builder', 'exotic-font-manager'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="efm-form" id="efm-rule-form">
                <?php wp_nonce_field('efm_save_rule'); ?>
                <input type="hidden" name="action" value="efm_save_rule" />
                <input type="hidden" name="rule_id" value="<?php echo esc_attr($rule_form['id']); ?>" />

                <label>
                    <span><?php esc_html_e('Rule Name', 'exotic-font-manager'); ?></span>
                    <input type="text" name="rule_label" value="<?php echo esc_attr($rule_form['label']); ?>" placeholder="<?php esc_attr_e('Example: Homepage headings', 'exotic-font-manager'); ?>" />
                </label>

                <label>
                    <span><?php esc_html_e('Target Group', 'exotic-font-manager'); ?></span>
                    <select name="target_key" required>
                        <?php foreach ($presets as $preset_key => $preset) : ?>
                            <option value="<?php echo esc_attr($preset_key); ?>" <?php selected($rule_form['target_key'], $preset_key); ?>>
                                <?php echo esc_html(isset($preset['label']) ? $preset['label'] : $preset_key); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <div class="efm-form__row">
                    <label>
                        <span><?php esc_html_e('Scope', 'exotic-font-manager'); ?></span>
                        <select name="scope_type" data-efm-scope>
                            <?php foreach ($scope_options as $scope_key => $scope_label) : ?>
                                <option value="<?php echo esc_attr($scope_key); ?>" <?php selected($rule_form['scope_type'], $scope_key); ?>>
                                    <?php echo esc_html($scope_label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label data-efm-scope-value-wrap>
                        <span><?php esc_html_e('Scope Value', 'exotic-font-manager'); ?></span>
                        <input type="text" name="scope_value" value="<?php echo esc_attr($rule_form['scope_value']); ?>" placeholder="<?php esc_attr_e('Page ID, post type, or taxonomy slug', 'exotic-font-manager'); ?>" data-efm-scope-value />
                    </label>
                </div>

                <label>
                    <span><?php esc_html_e('Font', 'exotic-font-manager'); ?></span>
                    <select name="font_id" required>
                        <?php foreach ($font_options as $font_id => $font) : ?>
                            <option value="<?php echo esc_attr($font_id); ?>" <?php selected($rule_form['font_id'], $font_id); ?>>
                                <?php echo esc_html($font['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <div class="efm-form__row efm-form__row--small">
                    <label>
                        <span><?php esc_html_e('Weight', 'exotic-font-manager'); ?></span>
                        <input type="text" name="font_weight" value="<?php echo esc_attr($rule_form['font_weight']); ?>" placeholder="400" />
                    </label>

                    <label>
                        <span><?php esc_html_e('Style', 'exotic-font-manager'); ?></span>
                        <select name="font_style">
                            <option value="normal" <?php selected($rule_form['font_style'], 'normal'); ?>><?php esc_html_e('Normal', 'exotic-font-manager'); ?></option>
                            <option value="italic" <?php selected($rule_form['font_style'], 'italic'); ?>><?php esc_html_e('Italic', 'exotic-font-manager'); ?></option>
                        </select>
                    </label>

                    <label>
                        <span><?php esc_html_e('Delivery', 'exotic-font-manager'); ?></span>
                        <select name="delivery_mode">
                            <option value="auto" <?php selected($rule_form['delivery_mode'], 'auto'); ?>><?php esc_html_e('Auto', 'exotic-font-manager'); ?></option>
                            <option value="local" <?php selected($rule_form['delivery_mode'], 'local'); ?>><?php esc_html_e('Local', 'exotic-font-manager'); ?></option>
                            <option value="cdn" <?php selected($rule_form['delivery_mode'], 'cdn'); ?>><?php esc_html_e('CDN', 'exotic-font-manager'); ?></option>
                        </select>
                    </label>
                </div>

                <label class="efm-checkbox">
                    <input type="checkbox" name="enabled" value="1" <?php checked(!empty($rule_form['enabled'])); ?> />
                    <span><?php esc_html_e('Enable this rule', 'exotic-font-manager'); ?></span>
                </label>

                <div class="efm-form__actions">
                    <button type="submit" class="button button-primary button-large"><?php echo $rule_form['id'] ? esc_html__('Update Rule', 'exotic-font-manager') : esc_html__('Save Rule', 'exotic-font-manager'); ?></button>
                    <?php if (!empty($rule_form['id'])) : ?>
                        <a class="button" href="<?php echo esc_url(EFM_Admin_Page::settings_page_url()); ?>"><?php esc_html_e('Cancel edit', 'exotic-font-manager'); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="efm-card">
            <h2><?php esc_html_e('Active Rules', 'exotic-font-manager'); ?></h2>
            <?php if (empty($rules)) : ?>
                <p><?php esc_html_e('No font rules yet. Create your first rule from the left panel.', 'exotic-font-manager'); ?></p>
            <?php else : ?>
                <table class="widefat striped efm-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Rule', 'exotic-font-manager'); ?></th>
                            <th><?php esc_html_e('Target', 'exotic-font-manager'); ?></th>
                            <th><?php esc_html_e('Scope', 'exotic-font-manager'); ?></th>
                            <th><?php esc_html_e('Font', 'exotic-font-manager'); ?></th>
                            <th><?php esc_html_e('Actions', 'exotic-font-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rules as $rule) : ?>
                            <?php
                            if (!is_array($rule)) {
                                continue;
                            }
                            $rule_name = !empty($rule['label']) ? $rule['label'] : __('Untitled rule', 'exotic-font-manager');
                            $target_label = isset($presets[$rule['target_key']]['label']) ? $presets[$rule['target_key']]['label'] : $rule['target_key'];
                            $scope_label = isset($scope_options[$rule['scope_type']]) ? $scope_options[$rule['scope_type']] : $rule['scope_type'];
                            $font_label = isset($font_options[$rule['font_id']]['label']) ? $font_options[$rule['font_id']]['label'] : $rule['font_id'];
                            $edit_url = add_query_arg([
                                'page' => EFM_Admin_Page::MENU_SLUG,
                                'edit_rule' => $rule['id'],
                            ], admin_url('admin.php'));
                            $delete_url = wp_nonce_url(add_query_arg([
                                'action' => 'efm_delete_rule',
                                'rule_id' => $rule['id'],
                            ], admin_url('admin-post.php')), 'efm_delete_rule_' . $rule['id']);
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($rule_name); ?></strong>
                                    <?php if (empty($rule['enabled'])) : ?>
                                        <span class="efm-pill efm-pill--muted"><?php esc_html_e('Disabled', 'exotic-font-manager'); ?></span>
                                    <?php else : ?>
                                        <span class="efm-pill"><?php esc_html_e('Enabled', 'exotic-font-manager'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($target_label); ?></td>
                                <td>
                                    <?php echo esc_html($scope_label); ?>
                                    <?php if (!empty($rule['scope_value'])) : ?>
                                        <code><?php echo esc_html($rule['scope_value']); ?></code>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($font_label); ?></td>
                                <td>
                                    <a class="button button-small" href="<?php echo esc_url($edit_url); ?>"><?php esc_html_e('Edit', 'exotic-font-manager'); ?></a>
                                    <a class="button button-small" href="<?php echo esc_url($delete_url); ?>" onclick="return confirm('<?php echo esc_js(__('Delete this rule?', 'exotic-font-manager')); ?>');"><?php esc_html_e('Delete', 'exotic-font-manager'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="efm-card efm-card--full">
            <h2><?php esc_html_e('Font Library', 'exotic-font-manager'); ?></h2>
            <p class="efm-muted"><?php esc_html_e('Use local uploads, Google Fonts (local or CDN), or custom CDN stylesheets.', 'exotic-font-manager'); ?></p>

            <div class="efm-font-tools">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="efm-mini-form">
                    <?php wp_nonce_field('efm_upload_local_font'); ?>
                    <input type="hidden" name="action" value="efm_upload_local_font" />
                    <h3><?php esc_html_e('Upload Local Font', 'exotic-font-manager'); ?></h3>
                    <label>
                        <span><?php esc_html_e('Label', 'exotic-font-manager'); ?></span>
                        <input type="text" name="font_label" placeholder="<?php esc_attr_e('Example: Brand Sans Local', 'exotic-font-manager'); ?>" required />
                    </label>
                    <label>
                        <span><?php esc_html_e('Family Name', 'exotic-font-manager'); ?></span>
                        <input type="text" name="font_family" placeholder="<?php esc_attr_e('Example: Brand Sans', 'exotic-font-manager'); ?>" required />
                    </label>
                    <label>
                        <span><?php esc_html_e('Font Stack', 'exotic-font-manager'); ?></span>
                        <input type="text" name="font_stack" placeholder="'Brand Sans', 'Inter', sans-serif" />
                    </label>
                    <div class="efm-mini-form__row">
                        <label>
                            <span><?php esc_html_e('Weight', 'exotic-font-manager'); ?></span>
                            <input type="number" min="100" max="900" step="100" name="font_weight" value="400" />
                        </label>
                        <label>
                            <span><?php esc_html_e('Style', 'exotic-font-manager'); ?></span>
                            <select name="font_style">
                                <option value="normal"><?php esc_html_e('Normal', 'exotic-font-manager'); ?></option>
                                <option value="italic"><?php esc_html_e('Italic', 'exotic-font-manager'); ?></option>
                            </select>
                        </label>
                    </div>
                    <label>
                        <span><?php esc_html_e('Font File', 'exotic-font-manager'); ?></span>
                        <input type="file" name="font_file" accept=".woff2,.woff,.ttf,.otf" required />
                    </label>
                    <button type="submit" class="button"><?php esc_html_e('Upload Font', 'exotic-font-manager'); ?></button>
                </form>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="efm-mini-form">
                    <?php wp_nonce_field('efm_install_google_local_font'); ?>
                    <input type="hidden" name="action" value="efm_install_google_local_font" />
                    <h3><?php esc_html_e('Install Google Font Locally', 'exotic-font-manager'); ?></h3>
                    <label>
                        <span><?php esc_html_e('Family', 'exotic-font-manager'); ?></span>
                        <select name="google_family" required>
                            <option value=""><?php esc_html_e('Select font family', 'exotic-font-manager'); ?></option>
                            <?php foreach (EFM_Google_Installer::get_catalog() as $family_name) : ?>
                                <option value="<?php echo esc_attr($family_name); ?>"><?php echo esc_html($family_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span><?php esc_html_e('Weights (comma-separated)', 'exotic-font-manager'); ?></span>
                        <input type="text" name="google_variants" value="400,500,700" />
                    </label>
                    <button type="submit" class="button"><?php esc_html_e('Install Local', 'exotic-font-manager'); ?></button>
                </form>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="efm-mini-form">
                    <?php wp_nonce_field('efm_add_google_cdn_font'); ?>
                    <input type="hidden" name="action" value="efm_add_google_cdn_font" />
                    <h3><?php esc_html_e('Add Google Font (CDN)', 'exotic-font-manager'); ?></h3>
                    <label>
                        <span><?php esc_html_e('Family', 'exotic-font-manager'); ?></span>
                        <select name="google_family" required>
                            <option value=""><?php esc_html_e('Select font family', 'exotic-font-manager'); ?></option>
                            <?php foreach (EFM_Google_Installer::get_catalog() as $family_name) : ?>
                                <option value="<?php echo esc_attr($family_name); ?>"><?php echo esc_html($family_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span><?php esc_html_e('Weights (comma-separated)', 'exotic-font-manager'); ?></span>
                        <input type="text" name="google_variants" value="400,500,700" />
                    </label>
                    <button type="submit" class="button"><?php esc_html_e('Add CDN Font', 'exotic-font-manager'); ?></button>
                </form>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="efm-mini-form">
                    <?php wp_nonce_field('efm_add_custom_cdn_font'); ?>
                    <input type="hidden" name="action" value="efm_add_custom_cdn_font" />
                    <h3><?php esc_html_e('Add Custom CDN Font', 'exotic-font-manager'); ?></h3>
                    <label>
                        <span><?php esc_html_e('Label', 'exotic-font-manager'); ?></span>
                        <input type="text" name="font_label" placeholder="<?php esc_attr_e('Example: Bunny Sans', 'exotic-font-manager'); ?>" required />
                    </label>
                    <label>
                        <span><?php esc_html_e('Family Name', 'exotic-font-manager'); ?></span>
                        <input type="text" name="font_family" placeholder="<?php esc_attr_e('Example: Bunny Sans', 'exotic-font-manager'); ?>" />
                    </label>
                    <label>
                        <span><?php esc_html_e('Font Stack', 'exotic-font-manager'); ?></span>
                        <input type="text" name="font_stack" placeholder="'Bunny Sans', 'Inter', sans-serif" />
                    </label>
                    <label>
                        <span><?php esc_html_e('CDN Stylesheet URL', 'exotic-font-manager'); ?></span>
                        <input type="url" name="cdn_css_url" placeholder="https://..." required />
                    </label>
                    <button type="submit" class="button"><?php esc_html_e('Add Custom CDN', 'exotic-font-manager'); ?></button>
                </form>
            </div>

            <div class="efm-font-table-wrap">
                <table class="widefat striped efm-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Font', 'exotic-font-manager'); ?></th>
                            <th><?php esc_html_e('Source', 'exotic-font-manager'); ?></th>
                            <th><?php esc_html_e('Stack', 'exotic-font-manager'); ?></th>
                            <th><?php esc_html_e('Variants', 'exotic-font-manager'); ?></th>
                            <th><?php esc_html_e('Actions', 'exotic-font-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($font_options as $font_id => $font) : ?>
                            <?php
                            $source = isset($font['source']) ? (string) $font['source'] : 'system';
                            $variants = isset($font['variants']) && is_array($font['variants']) ? $font['variants'] : [];
                            $variant_count = count($variants);
                            $delete_url = wp_nonce_url(add_query_arg([
                                'action' => 'efm_delete_font',
                                'font_id' => $font_id,
                            ], admin_url('admin-post.php')), 'efm_delete_font_' . $font_id);
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($font['label']); ?></strong></td>
                                <td><code><?php echo esc_html($source); ?></code></td>
                                <td><code><?php echo esc_html($font['stack']); ?></code></td>
                                <td>
                                    <?php
                                    if ($variant_count > 0) {
                                        echo esc_html(sprintf(_n('%d variant', '%d variants', $variant_count, 'exotic-font-manager'), $variant_count));
                                    } elseif (!empty($font['cdn_css_url'])) {
                                        esc_html_e('CDN stylesheet', 'exotic-font-manager');
                                    } else {
                                        esc_html_e('System stack', 'exotic-font-manager');
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($source === 'system') : ?>
                                        <span class="efm-muted"><?php esc_html_e('Protected', 'exotic-font-manager'); ?></span>
                                    <?php else : ?>
                                        <a class="button button-small" href="<?php echo esc_url($delete_url); ?>" onclick="return confirm('<?php echo esc_js(__('Delete this font from library?', 'exotic-font-manager')); ?>');"><?php esc_html_e('Delete', 'exotic-font-manager'); ?></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="efm-card efm-card--full">
            <h2><?php esc_html_e('Safety and Cross-Site Sync', 'exotic-font-manager'); ?></h2>
            <p class="efm-muted"><?php esc_html_e('Rollback recent changes, reset active rules, and move typography profiles between sites.', 'exotic-font-manager'); ?></p>

            <div class="efm-maintenance-grid">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="efm-maintenance-card">
                    <?php wp_nonce_field('efm_rollback'); ?>
                    <input type="hidden" name="action" value="efm_rollback" />
                    <h3><?php esc_html_e('Rollback', 'exotic-font-manager'); ?></h3>
                    <p><?php echo esc_html(sprintf(_n('%d snapshot available', '%d snapshots available', count($history), 'exotic-font-manager'), count($history))); ?></p>
                    <button type="submit" class="button" <?php disabled(empty($history)); ?>><?php esc_html_e('Restore Previous Snapshot', 'exotic-font-manager'); ?></button>
                </form>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="efm-maintenance-card">
                    <?php wp_nonce_field('efm_reset_overrides'); ?>
                    <input type="hidden" name="action" value="efm_reset_overrides" />
                    <h3><?php esc_html_e('Reset Rules', 'exotic-font-manager'); ?></h3>
                    <p><?php esc_html_e('Clears active typography rules while preserving your font library.', 'exotic-font-manager'); ?></p>
                    <button type="submit" class="button" onclick="return confirm('<?php echo esc_js(__('Reset all typography rules?', 'exotic-font-manager')); ?>');"><?php esc_html_e('Reset Typography Rules', 'exotic-font-manager'); ?></button>
                </form>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="efm-maintenance-card">
                    <?php wp_nonce_field('efm_export_profile'); ?>
                    <input type="hidden" name="action" value="efm_export_profile" />
                    <h3><?php esc_html_e('Export Profile', 'exotic-font-manager'); ?></h3>
                    <p><?php esc_html_e('Download current font library and rules as a portable JSON profile.', 'exotic-font-manager'); ?></p>
                    <button type="submit" class="button"><?php esc_html_e('Download Profile', 'exotic-font-manager'); ?></button>
                </form>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="efm-maintenance-card">
                    <?php wp_nonce_field('efm_import_profile'); ?>
                    <input type="hidden" name="action" value="efm_import_profile" />
                    <h3><?php esc_html_e('Import Profile', 'exotic-font-manager'); ?></h3>
                    <p><?php esc_html_e('Upload a JSON profile exported from another site using this plugin.', 'exotic-font-manager'); ?></p>
                    <input type="file" name="import_profile" accept=".json,application/json" required />
                    <button type="submit" class="button"><?php esc_html_e('Import Profile', 'exotic-font-manager'); ?></button>
                </form>
            </div>
        </section>
    </div>
</div>
