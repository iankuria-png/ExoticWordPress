<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

global $taxonomy_location_url, $taxonomy_profile_name, $taxonomy_profile_name_plural, $taxonomy_profile_url;

$yourname = wp_strip_all_tags($_POST['yourname'], true);
if (!$yourname) { $err .= sprintf(esc_html__('Please write the %s name.','escortwp'),$taxonomy_profile_name)."<br />"; }

$phone = wp_strip_all_tags($_POST['phone'], true);
if (!$phone) { $err2 .= __('Please write your phone number','escortwp')."<br />"; }

$escortemail = $_POST['escortemail'];
if (!$escortemail) { $err2 .= sprintf(esc_html__('Please write your %s email.','escortwp'),$taxonomy_profile_name)."<br />"; } else {
	if ( !is_email($escortemail) ) { $err .= sprintf(esc_html__('The %s email seems to be wrong.','escortwp'),$taxonomy_profile_name)."<br />"; }
}

if ($_POST['country'] && $_POST['country'] > 0) {
	$country = (int)$_POST['country'];
	$city_parent = $country;
	if (!term_exists( $country, $taxonomy_location_url )) {
		$err .= __('The country you selected doesn\'t exist in our database','escortwp')."<br />"; unset($country, $city);
	} else {
		if(showfield('state')) {
			if ($_POST['state']) {
				if(get_option('locationdropdown') == "1") { // if location is a dropdown
					$state = (int)$_POST['state'];
					$state_id = get_term_by('id', $state, $taxonomy_location_url);
					if (!$state_id) {
						$err .= __('The state you selected doesn\'t exist in our database','escortwp')."<br />"; unset($state);
					}
					$state_id = $state_id->term_id;
				} else {
					$state = substr(sanitize_text_field($_POST['state']), 0, 70);
					$state_id = term_exists( $state, $taxonomy_location_url, $country );
					if (!$state_id) {
						$arg = array('description' => $state, 'parent' => $country);
						wp_insert_term($state, $taxonomy_location_url, $arg);
						$state_id = term_exists( $state, $taxonomy_location_url, $country );
					}
					$state_id = $state_id['term_id'];
				}
					$city_parent = $state_id;
			} else {
				$err .= __('You need to select your state','escortwp')."<br />"; unset($state);
			} // if post[state]			
		} // if showfield('state')

		if ($_POST['city']) {
			if(get_option('locationdropdown') == "1") { // if location is a dropdown
				$city = (int)$_POST['city'];
				$city_id = get_term_by('id', $city, $taxonomy_location_url);
				if (!$city_id) {
					$err .= __('The city you selected doesn\'t exist in our database','escortwp')."<br />"; unset($city);
				}
				$city_id = $city_id->term_id;
			} else {
				$city = substr(sanitize_text_field($_POST['city']), 0, 70);
				$city_id = term_exists( $city, $taxonomy_location_url, $city_parent);
				if (!$city_id) {
					$arg = array('description' => $city, 'parent' => $city_parent);
					wp_insert_term($city, $taxonomy_location_url, $arg);
					$city_id = term_exists( $city, $taxonomy_location_url, $city_parent);
				}
				$city_id = $city_id['term_id'];
			}
		} else {
			$err .= __('You need to select your city','escortwp')."<br />"; unset($city);
		} // if post[city]
	} // if term exists country
} else {
	$err .= __('You need to select a country','escortwp')."<br />"; unset($country);
}

if ($_POST['gender']) {
	$gender = (int)$_POST['gender'];
	global $gender_a;
	if (!$gender_a[$gender]) { $err .= __('Please choose your gender','escortwp')."<br />"; unset($gender); }
} else {
	$err .= sprintf(esc_html__('Please choose a gender for the %s.','escortwp'),$taxonomy_profile_name)."<br />";
}

$aboutyou = substr(stripslashes(wp_kses($_POST['aboutyou'], array())), 0, 5000);
if (!$aboutyou) { $err .= sprintf(esc_html__('You must write a note about the %s.','escortwp'),$taxonomy_profile_name)."<br />"; }

if (!$err) {
	if ($escort_post_id) {
		// Create post object
		$post_escort = array(
			'ID' => $escort_post_id,
			'post_title' => $yourname,
			'post_content' => $aboutyou,
		);
		wp_update_post( $post_escort );
		$post_escort_id = $escort_post_id;
	} else {
		$blacklistescort_cat_id = term_exists( 'Blacklisted '.ucwords($taxonomy_profile_name_plural), "category" );
		if (!$blacklistescort_cat_id) {
			$arg = array('description' => 'Blacklisted '.$taxonomy_profile_name_plural);
			wp_insert_term('Blacklisted '.ucwords($taxonomy_profile_name_plural), "category", $arg);
			$blacklistescort_cat_id = term_exists( 'Blacklisted '.ucwords($taxonomy_profile_name_plural), "category" );
		}
		$blacklistescort_cat_id = $blacklistescort_cat_id['term_id'];
		// Create post object
		$post_escort = array(
			'post_title' => $yourname,
			'post_content' => $aboutyou,
			'post_status' => 'publish',
			'post_author' => $current_user->ID,
			'post_category' => array($blacklistescort_cat_id),
			'post_type' => 'b'.$taxonomy_profile_url,
			'ping_status' => 'closed'
		);
		// Insert the post into the database
		$post_escort_id = wp_insert_post( $post_escort );
	}

	update_post_meta($post_escort_id, "name", $yourname);
	update_post_meta($post_escort_id, "phone", $phone);
	update_post_meta($post_escort_id, "email", $escortemail);
	update_post_meta($post_escort_id, "country", $country);
	if(showfield('state')) {
		update_post_meta($post_escort_id, "state", $state_id);
	}
	update_post_meta($post_escort_id, "city", $city_id);
	update_post_meta($post_escort_id, "gender", $gender);

	if (!$escort_post_id) {
		$secret = md5($youremail.$yourname.time().rand(1,999));
		update_post_meta($post_escort_id, "secret", $secret);
		update_post_meta($post_escort_id, "upload_folder", time().rand(1,999));
		update_option("agency".$secret, $post_escort_id);
	}

	if (!$escort_post_id && get_option("ifemail1") == "1") {
		$body = __('Hello','escortwp').',<br /><br />'.sprintf(esc_html__('A new blacklisted %s has been added on.','escortwp'),$taxonomy_profile_name).' '.get_option("email_sitename").':<br /><br />
'.sprintf(esc_html__('You can view the %s here.','escortwp'),$taxonomy_profile_name).':<br />
<a href="'.get_permalink($post_escort_id).'">'.get_permalink($post_escort_id).'</a>';
		dolce_email(null, null, get_bloginfo("admin_email"), sprintf(esc_html__('New blacklisted %s on.','escortwp'),$taxonomy_profile_name)." ".get_option("email_sitename"), $body);
	}

	wp_redirect(get_permalink($post_escort_id)); exit;
}
?>