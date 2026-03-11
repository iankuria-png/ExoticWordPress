<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );
if (!is_user_logged_in()) { die(); }

$escortid = $_GET['id'];
$function = substr($escortid, 0, 3);
$escortid = str_replace($function, "", $escortid);
$escortid = $escortid;
if (!$escortid || $escortid == 0) { die("1"); }

$current_user = wp_get_current_user();
$userid = $current_user->ID;

$favorites = get_user_meta( $userid, "favorites", true);
if ($favorites) {
	$favorites = array_unique(explode(",", $favorites));
} else {
	$favorites = array();
}

print_r($favorites);
if ($function == "add") {
	if (!in_array($escortid, $favorites)) {
		$favorites[] = $escortid;
		$favorites = implode(",", $favorites);
		update_user_meta( $userid, "favorites", $favorites);
	}
} elseif ($function == "rem") {
	if (in_array($escortid, $favorites)) {
		$key = array_search($escortid, $favorites);
		if($key) {
			unset($favorites[$key]);
		}
		$favorites = implode(",", $favorites);
		update_user_meta( $userid, "favorites", $favorites);
	}
}
?>