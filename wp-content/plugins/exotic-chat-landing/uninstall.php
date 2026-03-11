<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('exotic_chat_landing_settings');
