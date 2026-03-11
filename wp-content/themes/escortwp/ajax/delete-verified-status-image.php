<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );
if (!is_user_logged_in() || !$_GET['id']) { die(); }

$current_user = wp_get_current_user();
$postid = (int)$_GET['id'];
$userid = $current_user->ID;
$post = get_post($postid);

if($post->post_author == $userid || current_user_can('level_10')) {
	delete_post_meta($postid, 'verified_status');
	_e('Your image has been deleted','escortwp');
}
?>