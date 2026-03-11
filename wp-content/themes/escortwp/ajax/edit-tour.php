<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );
if (!is_user_logged_in()) { die(); }

$tourid = (int)$_GET['id'];
$escortid = (int)$_GET['escort_id'];
if ($tourid == 0) { die(); }

global $taxonomy_location_url;
$current_user = wp_get_current_user();
$userid = $current_user->ID;

$tour = get_post($tourid);
$escort = get_post(get_post_meta( $tourid, 'belongstoescortid', true));
$escort_author = $escort->post_author;

if ($escort_author == $current_user->ID || current_user_can('level_10')) {
	$tourcountry = get_post_meta($tourid, 'country', true);
	if(showfield('state')) {
		$tourstate = get_post_meta($tourid,'state', true);
	}
	$tourcity = get_post_meta($tourid,'city', true);

	if(get_option('locationdropdown') != "1") {
		if(showfield('state')) {
			$tourstate = get_term($tourstate, $taxonomy_location_url);
			$tourstate = $tourstate->name;
		}

		$tourcity = get_term($tourcity, $taxonomy_location_url);
		$tourcity = $tourcity->name;
	}




	$start = get_post_meta($tourid,'start', true);
	$end = get_post_meta($tourid,'end', true);
	$tourphone = get_post_meta($tourid,'phone', true);
	$edittour = "yes";
	if ($_GET['edit_tour_in_escort_page'] == "yes") {
		$is_escort_page = "yes";
		$escort_post_id_for_tours = (int)$_GET['escort_id_to_redirect'];
	}
	include (get_template_directory() . '/register-independent-add-tour-form.php');
}
?>