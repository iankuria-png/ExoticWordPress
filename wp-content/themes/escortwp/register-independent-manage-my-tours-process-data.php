<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }


global $taxonomy_location_url, $taxonomy_agency_url, $taxonomy_profile_url;
if ($_POST['tourcountry'] > 0) {
	$tourcountry = (int)$_POST['tourcountry'];
	$tourcity_parent = $tourcountry;
	if (!term_exists( $tourcountry, $taxonomy_location_url )) {
		$err .= __('The country you selected doesn\'t exist in our database','escortwp')."<br />"; unset($tourcountry);
	} else {
		if(showfield('state')) {
			if ($_POST['tourstate']) {
				if(get_option('locationdropdown') == "1") { // if location is a dropdown
					$tourstate = (int)$_POST['tourstate'];
					$tourstate_id = get_term_by('id', $tourstate, $taxonomy_location_url);
					if (!$tourstate_id) {
						$err .= __('The state you selected doesn\'t exist in our database','escortwp')."<br />"; unset($tourstate);
					}
					$tourstate_id = $tourstate_id->term_id;
				} else {
					$tourstate = substr(sanitize_text_field($_POST['tourstate']), 0, 70);
					$tourstate_id = term_exists( $tourstate, $taxonomy_location_url, $tourcountry );
					if (!$tourstate_id) {
						$arg = array('description' => $tourstate, 'parent' => $tourcountry);
						wp_insert_term($tourstate, $taxonomy_location_url, $arg);
						$tourstate_id = term_exists( $tourstate, $taxonomy_location_url, $tourcountry );
					}
					$tourstate_id = $tourstate_id['term_id'];
				}
					$tourcity_parent = $tourstate_id;
			} else {
				$err .= __('You need to select your state','escortwp')."<br />"; unset($tourstate);
			} // if post[tourstate]			
		} // if showfield('state')

		if ($_POST['tourcity']) {
			if(get_option('locationdropdown') == "1") { // if location is a dropdown
				$tourcity = (int)$_POST['tourcity'];
				$tourcity_id = get_term_by('id', $tourcity, $taxonomy_location_url);
				if (!$tourcity_id) {
					$err .= __('The city you selected doesn\'t exist in our database','escortwp')."<br />"; unset($tourcity);
				}
				$tourcity_id = $tourcity_id->term_id;
			} else {
				$tourcity = substr(sanitize_text_field($_POST['tourcity']), 0, 70);
				$tourcity_id = term_exists( $tourcity, $taxonomy_location_url, $tourcity_parent);
				if (!$tourcity_id) {
					$arg = array('description' => $tourcity, 'parent' => $tourcity_parent);
					wp_insert_term($tourcity, $taxonomy_location_url, $arg);
					$tourcity_id = term_exists( $tourcity, $taxonomy_location_url, $tourcity_parent);
				}
				$tourcity_id = $tourcity_id['term_id'];
			}
		} else {
			$err .= __('You need to select your city','escortwp')."<br />"; unset($tourcity);
		} // if post[city]
	} // if term exists country
} else {
	$err .= __('You need to select a country','escortwp')."<br />"; unset($tourcountry);
}

$start = preg_replace("/([^0-9\/])/", "", $_POST['start']);
if ($start) {
	$start = explode("/", $start);
	if (count($start) != 3) {
		$err .= __('The start date seems to be wrong','escortwp')."<br />"; unset($start);
	}
	$start = mktime(0, 0, 0, $start[1], $start[0], $start[2]);
} else {
	$err .= __('You have to select a start date for the tour','escortwp')."<br />";
}

$end = preg_replace("/([^0-9\/])/", "", $_POST['end']);
if ($end) {
	$end = explode("/", $end);
	if (count($end) != 3) {
		$err .= __('The end date seems to be wrong','escortwp')."<br />"; unset($start);
	}
	$end = mktime(23, 59, 59, $end[1], $end[0], $end[2]);
} else {
	$err .= __('You have to select the end date for the tour','escortwp')."<br />";
}

if($start > $end) {
	$err .= __('The end date must be after your start date','escortwp')."<br />"; unset($end);
}

$tourphone = wp_strip_all_tags($_POST['tourphone'], true);
if (!$tourphone) { $err .= __('Please write your phone number','escortwp')."<br />"; }

if ($_POST['tourid']) {
	$current_user = wp_get_current_user();
	$tourid = (int)$_POST['tourid'];
	$userid = $current_user->ID;

	$escort = get_post(get_post_meta( $tourid, 'belongstoescortid', true));
	$escort_author = $escort->post_author;

	if ($escort_author != $userid && !current_user_can('level_10')) {
		$err .= __('You are not allowed to edit this tour','escortwp');
	}
}

if ($_POST['belongstoescortid']) {
	$belongstoescortid = (int)$_POST['belongstoescortid'];
	$escort = get_post($belongstoescortid);
	$escort_author = $escort->post_author;

	if ($escort_author != $userid && !current_user_can('level_10')) {
		$err .= __('You are not allowed to add tours here','escortwp');
	}
}


if (!$err) {
	$current_user = wp_get_current_user();

	if (payment_plans('tours','price') && !$tourid && !current_user_can('level_10')) {
		$post_status = "private";
	} else {
		$post_status = "publish";
	}

	$tour_escort = array(
		'post_title' => __('Tour to','escortwp').' '.$tourcity,
		'post_content' => "",
		'post_status' => $post_status,
		'post_author' => $current_user->ID,
		'post_type' => 'tour',
		'ping_status' => 'closed'
	);

	if ($tourid) {
		$tour_escort_id = $tourid;
	} else {
		$tour_escort_id = wp_insert_post( $tour_escort );
	}
	wp_set_post_terms($tour_escort_id, $tourcity_id, $taxonomy_location_url);
	update_post_meta($tour_escort_id, "country", $tourcountry);
	if(showfield('state')) {
		update_post_meta($tour_escort_id, "state", $tourstate_id);
	}
	update_post_meta($tour_escort_id, "city", $tourcity_id);
	update_post_meta($tour_escort_id, "start", $start);
	update_post_meta($tour_escort_id, "end", $end);
	update_post_meta($tour_escort_id, "phone", $tourphone);

	if($post_status == "private") {
		update_post_meta($tour_escort_id, "needs_payment", '1');
	}

	$userid = $current_user->ID;
	$userstatus = get_option("escortid".$userid);
	if(!$tourid) {
		if ($userstatus == $taxonomy_agency_url || current_user_can('level_10')) {
			$escort_profile_id = $belongstoescortid;
			update_post_meta($tour_escort_id, "belongstoescortid", $escort_profile_id);
		} elseif ($userstatus == $taxonomy_profile_url) {
			$escort_profile_id = get_option("escortpostid".$userid);
			update_post_meta($tour_escort_id, "belongstoescortid", $escort_profile_id);
		} else {
			$escort_profile_id = $escort->ID;
			update_post_meta($tour_escort_id, "belongstoescortid", $escort_profile_id);
		}
	}

	unset($tourcountry, $tourstate, $tourcity, $tourcity_id, $start, $end, $tourphone);
	if ($tourid) {
		$ok = __('The tour has been updated','escortwp');
	} else {
		if (payment_plans('tours','price') && !current_user_can('level_10')) {
			wp_redirect(get_permalink($escort_profile_id)."?unpaid_tour=".$tour_escort_id); exit;
		} else {
			$ok = __('The tour has been added','escortwp');
		}
	}
}
?>