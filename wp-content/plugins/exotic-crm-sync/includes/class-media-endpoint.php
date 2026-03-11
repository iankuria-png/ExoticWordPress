<?php

if (!defined('ABSPATH')) {
    exit;
}

class Exotic_CRM_Media_Endpoint
{
    private $post_type;

    private $allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp'];

    private $max_size_bytes = 5242880; // 5MB

    private $max_images = 20;

    public function __construct()
    {
        $this->post_type = get_option('taxonomy_profile_url', 'escort');
    }

    public function register_routes($namespace)
    {
        register_rest_route($namespace, '/clients/(?P<post_id>\d+)/media', [
            'methods'             => 'GET',
            'callback'            => [$this, 'list_media'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);

        register_rest_route($namespace, '/clients/(?P<post_id>\d+)/media', [
            'methods'             => 'POST',
            'callback'            => [$this, 'upload_media'],
            'permission_callback' => [$this, 'check_permissions'],
            'args'                => [
                'set_main' => [
                    'required' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
            ],
        ]);

        register_rest_route($namespace, '/clients/(?P<post_id>\d+)/media/(?P<attachment_id>\d+)', [
            'methods'             => 'DELETE',
            'callback'            => [$this, 'delete_media'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);

        register_rest_route($namespace, '/clients/(?P<post_id>\d+)/media/(?P<attachment_id>\d+)/set-main', [
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => [$this, 'set_main_media'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);
    }

    public function check_permissions()
    {
        return current_user_can('manage_options');
    }

    public function list_media($request)
    {
        $post = $this->resolve_client_post((int) $request->get_param('post_id'));
        if (is_wp_error($post)) {
            return $post;
        }

        $mainImageId = (int) get_post_meta($post->ID, 'main_image_id', true);
        if ($mainImageId <= 0) {
            $mainImageId = (int) get_post_thumbnail_id($post->ID);
        }

        $attachments = get_posts([
            'post_type' => 'attachment',
            'post_parent' => $post->ID,
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $payload = array_map(function ($attachment) use ($mainImageId) {
            return $this->format_attachment((int) $attachment->ID, $mainImageId);
        }, $attachments);

        return rest_ensure_response([
            'client_post_id' => (int) $post->ID,
            'main_image_id' => $mainImageId > 0 ? $mainImageId : null,
            'total' => count($payload),
            'data' => array_values(array_filter($payload)),
        ]);
    }

    public function upload_media($request)
    {
        $post = $this->resolve_client_post((int) $request->get_param('post_id'));
        if (is_wp_error($post)) {
            return $post;
        }

        $currentCount = count(get_posts([
            'post_type' => 'attachment',
            'post_parent' => $post->ID,
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'suppress_filters' => true,
        ]));
        if ($currentCount >= $this->max_images) {
            return new WP_Error('media_limit_reached', 'Maximum number of images reached for this profile.', ['status' => 422]);
        }

        $files = $request->get_file_params();
        $file = $files['file'] ?? null;
        if (!$file || !is_array($file) || empty($file['tmp_name'])) {
            return new WP_Error('missing_file', 'A file upload is required.', ['status' => 422]);
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $this->max_size_bytes) {
            return new WP_Error('invalid_file_size', 'File must be greater than 0 and not exceed 5MB.', ['status' => 422]);
        }

        $detectedMime = wp_check_filetype((string) ($file['name'] ?? ''));
        $mimeType = (string) ($detectedMime['type'] ?? ($file['type'] ?? ''));
        if (!in_array($mimeType, $this->allowed_mime_types, true)) {
            return new WP_Error('invalid_file_type', 'Only JPEG, PNG, and WEBP are allowed.', ['status' => 422]);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $_FILES['file'] = $file;
        $attachmentId = media_handle_upload('file', $post->ID);

        if (is_wp_error($attachmentId)) {
            return new WP_Error('upload_failed', $attachmentId->get_error_message(), ['status' => 422]);
        }

        $setMain = rest_sanitize_boolean($request->get_param('set_main'));
        if ($setMain) {
            update_post_meta($post->ID, 'main_image_id', (int) $attachmentId);
            update_post_meta($post->ID, '_thumbnail_id', (int) $attachmentId);
        }

        $mainImageId = (int) get_post_meta($post->ID, 'main_image_id', true);
        if ($mainImageId <= 0) {
            $mainImageId = (int) get_post_thumbnail_id($post->ID);
        }

        return rest_ensure_response([
            'success' => true,
            'client_post_id' => (int) $post->ID,
            'attachment' => $this->format_attachment((int) $attachmentId, $mainImageId),
        ]);
    }

    public function delete_media($request)
    {
        $post = $this->resolve_client_post((int) $request->get_param('post_id'));
        if (is_wp_error($post)) {
            return $post;
        }

        $attachment = $this->resolve_attachment((int) $request->get_param('attachment_id'), (int) $post->ID);
        if (is_wp_error($attachment)) {
            return $attachment;
        }

        $mainImageId = (int) get_post_meta($post->ID, 'main_image_id', true);
        if ($mainImageId === (int) $attachment->ID) {
            delete_post_meta($post->ID, 'main_image_id');
        }

        $thumbnailId = (int) get_post_thumbnail_id($post->ID);
        if ($thumbnailId === (int) $attachment->ID) {
            delete_post_meta($post->ID, '_thumbnail_id');
        }

        $deleted = wp_delete_attachment((int) $attachment->ID, true);
        if (!$deleted) {
            return new WP_Error('delete_failed', 'Failed to delete media item.', ['status' => 500]);
        }

        return rest_ensure_response([
            'success' => true,
            'client_post_id' => (int) $post->ID,
            'deleted_attachment_id' => (int) $attachment->ID,
        ]);
    }

    public function set_main_media($request)
    {
        $post = $this->resolve_client_post((int) $request->get_param('post_id'));
        if (is_wp_error($post)) {
            return $post;
        }

        $attachment = $this->resolve_attachment((int) $request->get_param('attachment_id'), (int) $post->ID);
        if (is_wp_error($attachment)) {
            return $attachment;
        }

        update_post_meta($post->ID, 'main_image_id', (int) $attachment->ID);
        update_post_meta($post->ID, '_thumbnail_id', (int) $attachment->ID);

        return rest_ensure_response([
            'success' => true,
            'client_post_id' => (int) $post->ID,
            'main_image_id' => (int) $attachment->ID,
            'attachment' => $this->format_attachment((int) $attachment->ID, (int) $attachment->ID),
        ]);
    }

    private function resolve_client_post($post_id)
    {
        $post = get_post((int) $post_id);
        if (!$post || $post->post_type !== $this->post_type) {
            return new WP_Error('not_found', 'Client not found', ['status' => 404]);
        }

        return $post;
    }

    private function resolve_attachment($attachment_id, $post_id)
    {
        $attachment = get_post((int) $attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            return new WP_Error('attachment_not_found', 'Attachment not found.', ['status' => 404]);
        }

        if ((int) $attachment->post_parent !== (int) $post_id) {
            return new WP_Error('attachment_forbidden', 'Attachment does not belong to this profile.', ['status' => 403]);
        }

        return $attachment;
    }

    private function format_attachment($attachment_id, $mainImageId)
    {
        $attachment = get_post((int) $attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            return null;
        }

        return [
            'id' => (int) $attachment->ID,
            'url' => wp_get_attachment_url((int) $attachment->ID),
            'filename' => wp_basename(get_attached_file((int) $attachment->ID)),
            'mime_type' => get_post_mime_type((int) $attachment->ID),
            'uploaded_at' => (string) $attachment->post_date_gmt,
            'is_main' => (int) $attachment->ID === (int) $mainImageId,
        ];
    }
}
