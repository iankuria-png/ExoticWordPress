<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );

//only registered users can save information bellow this point
// if (!is_user_logged_in()) { die(); }

//only admins can save information bellow this point
if (!current_user_can('level_10')) die();

if($_GET['hide_demo_data_alert'] == "yes") {
	update_option('generate_demo_data_alert', 'hide'); //hide the alert for demo data generation
	echo "ok";
}
// if($_GET['taxonomy_location_url']) {
// 	$taxonomy_location_url = sanitize_title(char_to_utf8($_GET['taxonomy_location_url']));
// 	update_option("taxonomy_location_url", strtolower($taxonomy_location_url)); // set the taxonomy for the location
// 	echo "ok";
// }
// if($_GET['taxonomy_profile_url']) {
// 	$taxonomy_profile_url = sanitize_title(char_to_utf8($_GET['taxonomy_profile_url']));
// 	update_option("taxonomy_profile_url", strtolower($taxonomy_profile_url)); // set the taxonomy for the location
// 	echo "ok";
// }
?>