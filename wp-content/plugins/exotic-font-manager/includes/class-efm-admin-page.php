<?php

if (!defined('ABSPATH')) {
    exit;
}

class EFM_Admin_Page
{
    const MENU_SLUG = 'exotic-font-manager';

    public static function register()
    {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);

        add_action('admin_post_efm_save_rule', [__CLASS__, 'save_rule']);
        add_action('admin_post_efm_delete_rule', [__CLASS__, 'delete_rule']);
        add_action('admin_post_efm_upload_local_font', [__CLASS__, 'upload_local_font']);
        add_action('admin_post_efm_add_custom_cdn_font', [__CLASS__, 'add_custom_cdn_font']);
        add_action('admin_post_efm_add_google_cdn_font', [__CLASS__, 'add_google_cdn_font']);
        add_action('admin_post_efm_install_google_local_font', [__CLASS__, 'install_google_local_font']);
        add_action('admin_post_efm_delete_font', [__CLASS__, 'delete_font']);
        add_action('admin_post_efm_rollback', [__CLASS__, 'rollback_settings']);
        add_action('admin_post_efm_reset_overrides', [__CLASS__, 'reset_overrides']);
        add_action('admin_post_efm_export_profile', [__CLASS__, 'export_profile']);
        add_action('admin_post_efm_import_profile', [__CLASS__, 'import_profile']);
    }

    public static function settings_capability()
    {
        $capability = apply_filters('exotic_font_manager_settings_capability', 'manage_options');
        return is_string($capability) && $capability !== '' ? $capability : 'manage_options';
    }

    public static function settings_page_url()
    {
        return admin_url('admin.php?page=' . self::MENU_SLUG);
    }

    public static function register_menu()
    {
        add_menu_page(
            __('Font Manager', 'exotic-font-manager'),
            __('Font Manager', 'exotic-font-manager'),
            self::settings_capability(),
            self::MENU_SLUG,
            [__CLASS__, 'render_page'],
            'dashicons-editor-textcolor',
            57
        );
    }

    public static function enqueue_assets($hook)
    {
        if ($hook !== 'toplevel_page_' . self::MENU_SLUG) {
            return;
        }

        $css_file = EXOTIC_FONT_MANAGER_PATH . 'admin/css/font-admin.css';
        $js_file = EXOTIC_FONT_MANAGER_PATH . 'admin/js/font-admin.js';

        wp_enqueue_style(
            'exotic-font-manager-admin',
            EXOTIC_FONT_MANAGER_URL . 'admin/css/font-admin.css',
            [],
            file_exists($css_file) ? filemtime($css_file) : EXOTIC_FONT_MANAGER_VERSION
        );

        wp_enqueue_script(
            'exotic-font-manager-admin',
            EXOTIC_FONT_MANAGER_URL . 'admin/js/font-admin.js',
            ['jquery'],
            file_exists($js_file) ? filemtime($js_file) : EXOTIC_FONT_MANAGER_VERSION,
            true
        );
    }

    public static function render_page()
    {
        if (!current_user_can(self::settings_capability())) {
            wp_die(esc_html__('You do not have permission to manage fonts.', 'exotic-font-manager'));
        }

        $settings = EFM_Settings_Repository::get_settings();
        $rules = EFM_Settings_Repository::get_rules();
        $font_library = EFM_Settings_Repository::get_font_library();
        $presets = EFM_Target_Presets::get_presets();

        $edit_rule_id = isset($_GET['edit_rule']) ? sanitize_key((string) wp_unslash($_GET['edit_rule'])) : '';
        $edit_rule = self::find_rule($rules, $edit_rule_id);

        include EXOTIC_FONT_MANAGER_PATH . 'admin/views/admin-page.php';
    }

    public static function save_rule()
    {
        if (!current_user_can(self::settings_capability())) {
            wp_die(esc_html__('You do not have permission to manage font rules.', 'exotic-font-manager'));
        }

        check_admin_referer('efm_save_rule');

        $rule = [
            'id' => isset($_POST['rule_id']) ? sanitize_key((string) wp_unslash($_POST['rule_id'])) : '',
            'label' => isset($_POST['rule_label']) ? sanitize_text_field((string) wp_unslash($_POST['rule_label'])) : '',
            'enabled' => isset($_POST['enabled']) ? 1 : 0,
            'target_key' => isset($_POST['target_key']) ? sanitize_key((string) wp_unslash($_POST['target_key'])) : 'global_body_text',
            'scope_type' => isset($_POST['scope_type']) ? sanitize_key((string) wp_unslash($_POST['scope_type'])) : 'site',
            'scope_value' => isset($_POST['scope_value']) ? sanitize_text_field((string) wp_unslash($_POST['scope_value'])) : '',
            'font_id' => isset($_POST['font_id']) ? sanitize_key((string) wp_unslash($_POST['font_id'])) : 'system_inter',
            'font_weight' => isset($_POST['font_weight']) ? sanitize_text_field((string) wp_unslash($_POST['font_weight'])) : '',
            'font_style' => isset($_POST['font_style']) ? sanitize_key((string) wp_unslash($_POST['font_style'])) : 'normal',
            'delivery_mode' => isset($_POST['delivery_mode']) ? sanitize_key((string) wp_unslash($_POST['delivery_mode'])) : 'auto',
        ];

        EFM_Settings_Repository::save_rule($rule);

        wp_safe_redirect(add_query_arg([
            'page' => self::MENU_SLUG,
            'notice' => 'rule_saved',
        ], admin_url('admin.php')));
        exit;
    }

    public static function delete_rule()
    {
        if (!current_user_can(self::settings_capability())) {
            wp_die(esc_html__('You do not have permission to delete font rules.', 'exotic-font-manager'));
        }

        $rule_id = isset($_GET['rule_id']) ? sanitize_key((string) wp_unslash($_GET['rule_id'])) : '';
        check_admin_referer('efm_delete_rule_' . $rule_id);

        EFM_Settings_Repository::delete_rule($rule_id);

        wp_safe_redirect(add_query_arg([
            'page' => self::MENU_SLUG,
            'notice' => 'rule_deleted',
        ], admin_url('admin.php')));
        exit;
    }

    public static function upload_local_font()
    {
        if (!current_user_can(self::settings_capability())) {
            wp_die(esc_html__('You do not have permission to upload fonts.', 'exotic-font-manager'));
        }

        check_admin_referer('efm_upload_local_font');

        $label = isset($_POST['font_label']) ? sanitize_text_field((string) wp_unslash($_POST['font_label'])) : '';
        $family = isset($_POST['font_family']) ? sanitize_text_field((string) wp_unslash($_POST['font_family'])) : '';
        $stack = isset($_POST['font_stack']) ? sanitize_text_field((string) wp_unslash($_POST['font_stack'])) : '';
        $weight = isset($_POST['font_weight']) ? absint((string) wp_unslash($_POST['font_weight'])) : 400;
        $style = isset($_POST['font_style']) ? sanitize_key((string) wp_unslash($_POST['font_style'])) : 'normal';

        if ($family === '' || $label === '') {
            self::redirect_with_notice('font_failed', __('Font label and family are required.', 'exotic-font-manager'));
        }

        if ($stack === '') {
            $stack = "'" . $family . "', sans-serif";
        }

        if (!in_array($style, ['normal', 'italic'], true)) {
            $style = 'normal';
        }

        if ($weight < 100 || $weight > 900) {
            $weight = 400;
        }

        $uploaded = self::handle_font_upload_file('font_file');
        if (is_wp_error($uploaded)) {
            self::redirect_with_notice('font_failed', $uploaded->get_error_message());
        }

        $font_id = 'upload_' . sanitize_title($family);
        $existing_font = self::find_font_by_id(EFM_Settings_Repository::get_font_library(), $font_id);
        $variants = [];
        if (is_array($existing_font) && !empty($existing_font['variants']) && is_array($existing_font['variants'])) {
            $variants = $existing_font['variants'];
        }

        $variants[] = [
            'weight' => $weight,
            'style' => $style,
            'format' => $uploaded['format'],
            'url' => $uploaded['url'],
            'attachment_id' => $uploaded['attachment_id'],
        ];

        $font = [
            'id' => $font_id,
            'label' => $label,
            'source' => 'upload',
            'stack' => $stack,
            'family' => $family,
            'variants' => $variants,
            'cdn_css_url' => '',
            'google_family' => '',
            'google_variants' => [],
            'status' => 'ready',
        ];

        EFM_Settings_Repository::save_font($font);
        self::redirect_with_notice('font_saved');
    }

    public static function add_custom_cdn_font()
    {
        if (!current_user_can(self::settings_capability())) {
            wp_die(esc_html__('You do not have permission to add CDN fonts.', 'exotic-font-manager'));
        }

        check_admin_referer('efm_add_custom_cdn_font');

        $label = isset($_POST['font_label']) ? sanitize_text_field((string) wp_unslash($_POST['font_label'])) : '';
        $family = isset($_POST['font_family']) ? sanitize_text_field((string) wp_unslash($_POST['font_family'])) : '';
        $stack = isset($_POST['font_stack']) ? sanitize_text_field((string) wp_unslash($_POST['font_stack'])) : '';
        $css_url = isset($_POST['cdn_css_url']) ? esc_url_raw((string) wp_unslash($_POST['cdn_css_url'])) : '';

        if ($label === '' || $css_url === '') {
            self::redirect_with_notice('font_failed', __('CDN font label and stylesheet URL are required.', 'exotic-font-manager'));
        }

        if ($stack === '' && $family !== '') {
            $stack = "'" . $family . "', sans-serif";
        }

        if ($stack === '') {
            $stack = "'Inter', sans-serif";
        }

        $font = [
            'id' => 'custom_cdn_' . sanitize_title($label),
            'label' => $label,
            'source' => 'custom_cdn',
            'stack' => $stack,
            'family' => $family,
            'variants' => [],
            'cdn_css_url' => $css_url,
            'google_family' => '',
            'google_variants' => [],
            'status' => 'ready',
        ];

        EFM_Settings_Repository::save_font($font);
        self::redirect_with_notice('font_saved');
    }

    public static function add_google_cdn_font()
    {
        if (!current_user_can(self::settings_capability())) {
            wp_die(esc_html__('You do not have permission to add Google CDN fonts.', 'exotic-font-manager'));
        }

        check_admin_referer('efm_add_google_cdn_font');

        $family = isset($_POST['google_family']) ? sanitize_text_field((string) wp_unslash($_POST['google_family'])) : '';
        $variants = isset($_POST['google_variants']) ? (string) wp_unslash($_POST['google_variants']) : '';

        $font = EFM_Google_Installer::create_google_cdn_font($family, $variants);
        if ($font === null) {
            self::redirect_with_notice('font_failed', __('Failed to build Google CDN font.', 'exotic-font-manager'));
        }

        EFM_Settings_Repository::save_font($font);
        self::redirect_with_notice('font_saved');
    }

    public static function install_google_local_font()
    {
        if (!current_user_can(self::settings_capability())) {
            wp_die(esc_html__('You do not have permission to install Google fonts.', 'exotic-font-manager'));
        }

        check_admin_referer('efm_install_google_local_font');

        $family = isset($_POST['google_family']) ? sanitize_text_field((string) wp_unslash($_POST['google_family'])) : '';
        $variants = isset($_POST['google_variants']) ? (string) wp_unslash($_POST['google_variants']) : '';
        $error = '';

        $font = EFM_Google_Installer::install_local_font($family, $variants, $error);
        if ($font === null) {
            self::redirect_with_notice('font_failed', $error !== '' ? $error : __('Failed to install Google font locally.', 'exotic-font-manager'));
        }

        EFM_Settings_Repository::save_font($font);
        self::redirect_with_notice('font_saved');
    }

    public static function delete_font()
    {
        if (!current_user_can(self::settings_capability())) {
            wp_die(esc_html__('You do not have permission to delete fonts.', 'exotic-font-manager'));
        }

        $font_id = isset($_GET['font_id']) ? sanitize_key((string) wp_unslash($_GET['font_id'])) : '';
        check_admin_referer('efm_delete_font_' . $font_id);

        $font = self::find_font_by_id(EFM_Settings_Repository::get_font_library(), $font_id);
        if (!is_array($font)) {
            self::redirect_with_notice('font_failed', __('Font not found.', 'exotic-font-manager'));
        }

        if (isset($font['source']) && $font['source'] === 'system') {
            self::redirect_with_notice('font_failed', __('System fonts cannot be deleted.', 'exotic-font-manager'));
        }

        $variants = isset($font['variants']) && is_array($font['variants']) ? $font['variants'] : [];
        foreach ($variants as $variant) {
            if (!is_array($variant)) {
                continue;
            }
            if (!empty($variant['attachment_id'])) {
                wp_delete_attachment(absint($variant['attachment_id']), true);
            } elseif (!empty($variant['url'])) {
                self::delete_local_file_from_upload_url((string) $variant['url']);
            }
        }

        EFM_Settings_Repository::delete_font($font_id);
        self::redirect_with_notice('font_deleted');
    }

    public static function rollback_settings()
    {
        if (!current_user_can(self::settings_capability())) {
            wp_die(esc_html__('You do not have permission to rollback typography settings.', 'exotic-font-manager'));
        }

        check_admin_referer('efm_rollback');

        $rolled_back = EFM_Settings_Repository::rollback_latest();
        if (!$rolled_back) {
            self::redirect_with_notice('font_failed', __('No rollback snapshot available.', 'exotic-font-manager'));
        }

        self::redirect_with_notice('settings_rolled_back');
    }

    public static function reset_overrides()
    {
        if (!current_user_can(self::settings_capability())) {
            wp_die(esc_html__('You do not have permission to reset typography settings.', 'exotic-font-manager'));
        }

        check_admin_referer('efm_reset_overrides');
        EFM_Settings_Repository::reset_overrides();
        self::redirect_with_notice('settings_reset');
    }

    public static function export_profile()
    {
        if (!current_user_can(self::settings_capability())) {
            wp_die(esc_html__('You do not have permission to export typography settings.', 'exotic-font-manager'));
        }

        check_admin_referer('efm_export_profile');

        $payload = EFM_Settings_Repository::export_profile();
        $json = wp_json_encode($payload, JSON_PRETTY_PRINT);
        if (!is_string($json) || $json === '') {
            wp_die(esc_html__('Could not generate export payload.', 'exotic-font-manager'));
        }

        $filename = sprintf('exotic-font-profile-%s.json', gmdate('Y-m-d-His'));
        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Length: ' . strlen($json));
        echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }

    public static function import_profile()
    {
        if (!current_user_can(self::settings_capability())) {
            wp_die(esc_html__('You do not have permission to import typography settings.', 'exotic-font-manager'));
        }

        check_admin_referer('efm_import_profile');

        if (!isset($_FILES['import_profile']) || !is_array($_FILES['import_profile'])) {
            self::redirect_with_notice('font_failed', __('Please choose a JSON profile file.', 'exotic-font-manager'));
        }

        $file = $_FILES['import_profile'];
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            self::redirect_with_notice('font_failed', __('Uploaded file is invalid.', 'exotic-font-manager'));
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'json') {
            self::redirect_with_notice('font_failed', __('Import file must be a JSON document.', 'exotic-font-manager'));
        }

        $raw_json = file_get_contents($file['tmp_name']);
        if (!is_string($raw_json) || $raw_json === '') {
            self::redirect_with_notice('font_failed', __('Import file is empty.', 'exotic-font-manager'));
        }

        $error = '';
        $imported = EFM_Settings_Repository::import_profile($raw_json, $error);
        if (!$imported) {
            self::redirect_with_notice('font_failed', $error !== '' ? $error : __('Could not import profile.', 'exotic-font-manager'));
        }

        self::redirect_with_notice('profile_imported');
    }

    private static function find_rule($rules, $rule_id)
    {
        if ($rule_id === '' || !is_array($rules)) {
            return null;
        }

        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }

            if (!isset($rule['id'])) {
                continue;
            }

            if ($rule['id'] === $rule_id) {
                return $rule;
            }
        }

        return null;
    }

    private static function find_font_by_id($font_library, $font_id)
    {
        if (!is_array($font_library)) {
            return null;
        }

        foreach ($font_library as $font) {
            if (!is_array($font) || !isset($font['id'])) {
                continue;
            }

            if ($font['id'] === $font_id) {
                return $font;
            }
        }

        return null;
    }

    private static function redirect_with_notice($notice, $message = '')
    {
        $args = [
            'page' => self::MENU_SLUG,
            'notice' => sanitize_key((string) $notice),
        ];

        if ($message !== '') {
            $args['message'] = $message;
        }

        wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
        exit;
    }

    private static function handle_font_upload_file($file_key)
    {
        if (!isset($_FILES[$file_key]) || !is_array($_FILES[$file_key])) {
            return new WP_Error('efm_missing_file', __('No font file uploaded.', 'exotic-font-manager'));
        }

        $file = $_FILES[$file_key];
        if (!isset($file['name']) || $file['name'] === '') {
            return new WP_Error('efm_empty_file', __('Please choose a font file.', 'exotic-font-manager'));
        }

        $filename = sanitize_file_name((string) $file['name']);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed_extensions = ['woff2', 'woff', 'ttf', 'otf'];
        if (!in_array($extension, $allowed_extensions, true)) {
            return new WP_Error('efm_invalid_extension', __('Only WOFF2, WOFF, TTF, and OTF files are allowed.', 'exotic-font-manager'));
        }

        $mimes = [
            'woff2' => 'font/woff2',
            'woff' => 'font/woff',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
        ];

        $upload = wp_handle_upload($file, [
            'test_form' => false,
            'mimes' => $mimes,
        ]);

        if (!is_array($upload) || !empty($upload['error']) || empty($upload['file']) || empty($upload['url'])) {
            $error_message = is_array($upload) && !empty($upload['error']) ? (string) $upload['error'] : __('Font upload failed.', 'exotic-font-manager');
            return new WP_Error('efm_upload_failed', $error_message);
        }

        $attachment_id = wp_insert_attachment([
            'post_mime_type' => self::mime_for_extension($extension),
            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
            'post_content' => '',
            'post_status' => 'inherit',
        ], $upload['file']);

        if (!is_numeric($attachment_id) || (int) $attachment_id < 1) {
            @unlink($upload['file']);
            return new WP_Error('efm_attachment_failed', __('Could not create media record for uploaded font.', 'exotic-font-manager'));
        }

        return [
            'attachment_id' => (int) $attachment_id,
            'url' => esc_url_raw((string) $upload['url']),
            'file' => (string) $upload['file'],
            'format' => $extension,
        ];
    }

    private static function mime_for_extension($extension)
    {
        switch ($extension) {
            case 'woff2':
                return 'font/woff2';
            case 'woff':
                return 'font/woff';
            case 'ttf':
                return 'font/ttf';
            case 'otf':
                return 'font/otf';
            default:
                return 'application/octet-stream';
        }
    }

    private static function delete_local_file_from_upload_url($url)
    {
        $upload_dir = wp_upload_dir();
        if (!empty($upload_dir['error'])) {
            return;
        }

        $base_url = trailingslashit((string) $upload_dir['baseurl']);
        $base_dir = trailingslashit((string) $upload_dir['basedir']);

        $url = (string) $url;
        if (strpos($url, $base_url) !== 0) {
            return;
        }

        $relative_path = ltrim(substr($url, strlen($base_url)), '/');
        if ($relative_path === '') {
            return;
        }

        $local_path = wp_normalize_path($base_dir . $relative_path);
        if (is_file($local_path)) {
            @unlink($local_path);
        }
    }
}
