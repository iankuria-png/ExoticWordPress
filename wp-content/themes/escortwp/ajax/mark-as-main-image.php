<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );
if (!is_user_logged_in()) { die(); }

$attachmentid = (int)$_GET['id'];
if ($attachmentid == 0) { die(); }

$current_user = wp_get_current_user();
$userid = $current_user->ID;

$post = get_post($attachmentid);
$post_parent = get_post($post->post_parent);
$author_id = $post_parent->post_author;

//if the current user is the one who added the post
if ($author_id == $userid || current_user_can('level_10')) {
	update_post_meta($post->post_parent, "main_image_id", $attachmentid);
	$imgurl = wp_get_attachment_image_src($attachmentid, 'main-image-thumb');
	$return = array(
		'message' => __('Default image has been set','escortwp'),
		'imgurl' => $imgurl[0]
	);
	echo json_encode($return);
}
?>