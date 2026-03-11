<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('exotic_font_manager_settings');
delete_option('exotic_font_manager_history');

$upload_dir = wp_upload_dir();
if (empty($upload_dir['error'])) {
    $base_dir = trailingslashit((string) $upload_dir['basedir']) . 'exotic-font-manager';
    if (is_dir($base_dir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base_dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            /** @var SplFileInfo $item */
            $path = $item->getRealPath();
            if (!$path) {
                continue;
            }

            if ($item->isDir()) {
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($base_dir);
    }
}
