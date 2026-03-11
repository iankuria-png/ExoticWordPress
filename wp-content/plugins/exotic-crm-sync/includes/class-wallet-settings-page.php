<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_CRM_Wallet_Settings_Page
{
    const PAGE_SLUG = 'exotic-crm-wallet-settings';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_page()
    {
        add_options_page(
            'Exotic CRM Wallet',
            'Exotic CRM Wallet',
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render_page']
        );
    }

    public function register_settings()
    {
        register_setting('exotic_crm_wallet_settings', 'exotic_crm_api_base_url', [
            'sanitize_callback' => 'esc_url_raw',
            'default' => '',
        ]);
        register_setting('exotic_crm_wallet_settings', 'exotic_crm_wallet_api_base_url', [
            'sanitize_callback' => 'esc_url_raw',
            'default' => '',
        ]);
        register_setting('exotic_crm_wallet_settings', 'exotic_crm_wallet_platform_id', [
            'sanitize_callback' => 'absint',
            'default' => 0,
        ]);
        register_setting('exotic_crm_wallet_settings', 'exotic_crm_wallet_bearer_key', [
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
        register_setting('exotic_crm_wallet_settings', 'exotic_crm_wallet_hmac_secret', [
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
        register_setting('exotic_crm_wallet_settings', 'exotic_stk_retry_enabled', [
            'sanitize_callback' => [$this, 'sanitize_checkbox'],
            'default' => '0',
        ]);
    }

    public function sanitize_checkbox($value)
    {
        return $value ? '1' : '0';
    }

    public function render_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $config = get_option(Exotic_CRM_Wallet_Sync_Endpoint::CONFIG_OPTION, []);
        $mode = is_array($config) ? (string) ($config['mode'] ?? 'disabled') : 'disabled';
        $synced_at = is_array($config) ? (string) ($config['synced_at'] ?? '') : '';
        $market_currency = is_array($config) ? (string) ($config['config']['market']['currency'] ?? '') : '';
        ?>
        <div class="wrap">
            <h1>Exotic CRM Wallet</h1>
            <p>Configure the WordPress to CRM wallet credentials used by the wallet AJAX proxy and view the latest public wallet config synced from CRM.</p>

            <form method="post" action="options.php">
                <?php settings_fields('exotic_crm_wallet_settings'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="exotic_crm_api_base_url">CRM API Base URL</label></th>
                        <td>
                            <input type="url" class="regular-text" id="exotic_crm_api_base_url" name="exotic_crm_api_base_url" value="<?php echo esc_attr((string) get_option('exotic_crm_api_base_url', '')); ?>" />
                            <p class="description">Used by browser-facing activation and legacy payment flows. Example: <code>https://testing.exotic-ads.com</code></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="exotic_crm_wallet_api_base_url">Wallet API Base URL</label></th>
                        <td>
                            <input type="url" class="regular-text" id="exotic_crm_wallet_api_base_url" name="exotic_crm_wallet_api_base_url" value="<?php echo esc_attr((string) get_option('exotic_crm_wallet_api_base_url', '')); ?>" />
                            <p class="description">Used only by the server-side wallet proxy. For local wallet testing, set this to <code>http://localhost:8000</code>. Leave blank to use the environment default/fallback.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="exotic_crm_wallet_platform_id">Wallet Platform ID</label></th>
                        <td>
                            <input type="number" class="small-text" min="1" id="exotic_crm_wallet_platform_id" name="exotic_crm_wallet_platform_id" value="<?php echo esc_attr((string) get_option('exotic_crm_wallet_platform_id', 0)); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="exotic_crm_wallet_bearer_key">Wallet Bearer Key</label></th>
                        <td>
                            <input type="text" class="regular-text" id="exotic_crm_wallet_bearer_key" name="exotic_crm_wallet_bearer_key" value="<?php echo esc_attr((string) get_option('exotic_crm_wallet_bearer_key', '')); ?>" autocomplete="off" spellcheck="false" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="exotic_crm_wallet_hmac_secret">Wallet HMAC Secret</label></th>
                        <td>
                            <input type="text" class="regular-text" id="exotic_crm_wallet_hmac_secret" name="exotic_crm_wallet_hmac_secret" value="<?php echo esc_attr((string) get_option('exotic_crm_wallet_hmac_secret', '')); ?>" autocomplete="off" spellcheck="false" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Allow STK Retry</th>
                        <td>
                            <label for="exotic_stk_retry_enabled">
                                <input type="checkbox" id="exotic_stk_retry_enabled" name="exotic_stk_retry_enabled" value="1" <?php checked(get_option('exotic_stk_retry_enabled', '0'), '1'); ?> />
                                Enable the optional wallet STK retry button.
                            </label>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Wallet Settings'); ?>
            </form>

            <hr />
            <h2>Last Synced Wallet Config</h2>
            <table class="widefat striped" style="max-width: 760px;">
                <tbody>
                    <tr>
                        <th style="width: 220px;">Mode</th>
                        <td><?php echo esc_html($mode !== '' ? $mode : 'disabled'); ?></td>
                    </tr>
                    <tr>
                        <th>Synced At</th>
                        <td><?php echo esc_html($synced_at !== '' ? $synced_at : 'Not synced yet'); ?></td>
                    </tr>
                    <tr>
                        <th>Market Currency</th>
                        <td><?php echo esc_html($market_currency !== '' ? $market_currency : 'Unknown'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }
}
