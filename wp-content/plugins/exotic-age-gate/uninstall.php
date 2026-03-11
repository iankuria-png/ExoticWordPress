<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

delete_option('exotic_age_gate_settings');
