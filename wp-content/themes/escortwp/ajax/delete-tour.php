<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );
if (!is_user_logged_in()) { die(); }

$tourid = (int)$_GET['id'];
if ($tourid == 0) { die(); }

$current_user = wp_get_current_user();
$userid = $current_user->ID;

$tour = get_post($tourid);
$tour_author = $tour->post_author;

if ($tour_author == $current_user->ID || current_user_can('level_10')) {
	wp_delete_post( $tourid, true );
	_e('Tour deleted','escortwp');
}
?>