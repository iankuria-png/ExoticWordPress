<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );
if (!is_user_logged_in()) { die(); }

$attachmentid = (int)$_GET['id'];
if ($attachmentid == 0) { die(); }

global $taxonomy_agency_url;
$current_user = wp_get_current_user();
$userid = $current_user->ID;

$userstatus = get_option("escortid".$userid);
$agpostid = get_option("agencypostid".$userid);
$post = get_post($attachmentid);
if ($agpostid == $post->post_parent && $userstatus == $taxonomy_agency_url || current_user_can('level_10')) {
	wp_delete_attachment( $attachmentid, true );
	_e('Your image has been deleted','escortwp');
}

?>