<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );
if (!is_user_logged_in()) { die(); }

$clientid = (int)$_GET['id'];
if ($clientid == 0) { die(); }

$current_user = wp_get_current_user();
$userid = $current_user->ID;

$client = get_post($clientid);
$client_author = $client->post_author;

if ($client_author == $userid || current_user_can('level_10')) {
	wp_delete_post( $clientid, true );
	_e('Client deleted','escortwp');;
}
?>