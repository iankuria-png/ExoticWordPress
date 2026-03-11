<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );
if (!is_user_logged_in()) { die(); }

$escortid = (int)$_GET['id'];
if (!$escortid || $escortid < 1) { die(); }

$current_user = wp_get_current_user();
$userid = $current_user->ID;

$escort = get_post($escortid);
$escort_author = $escort->post_author;
$status = $escort->post_status;

if ($escort_author == $current_user->ID || current_user_can('level_10')) {
	if ($status == "publish") {
		$status = "private";
		$ok = __('Set to','escortwp')." <strong>".__('INACTIVE','escortwp')."</strong>";
	} else {
		$status = "publish";
		$ok = __('Set to','escortwp')." <strong>".__('ACTIVE','escortwp')."</strong>";
	}

	$post_escort = array( 'ID' => $escortid, 'post_status' => $status );
	wp_update_post( $post_escort );
	echo $ok;
}
?>