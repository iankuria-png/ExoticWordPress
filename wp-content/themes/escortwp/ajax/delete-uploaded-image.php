<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );
if (!is_user_logged_in()) { die(); }

$attachmentid = (int)$_GET['id'];
if ($attachmentid == 0) { die('nope'); }

$current_user = wp_get_current_user();
$userid = $current_user->ID;
if (!$userid) { die('nope'); }

$post = get_post($attachmentid);
$post_parent = get_post($post->post_parent);
$author_id = $post_parent->post_author;

//if the current user is the one who added the post
if ($author_id == $userid || current_user_can('level_10'))  {
	$ffmpeg_command = get_post_meta($attachmentid, 'ffmpeg_command', true);
	wp_delete_attachment($attachmentid, true);
	
	if($ffmpeg_command) {
		unlink(get_attached_file($attachmentid).".jpg");
		_e('Your video has been deleted','escortwp');
	} else {
		_e('Your image has been deleted','escortwp');
	}
}
?>