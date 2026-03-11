<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

global $taxonomy_location_url, $taxonomy_agency_name, $taxonomy_agency_url;
$current_user = wp_get_current_user();

if ($_POST['agency_post_id']) {
	$agency_post_id = (int)$_POST['agency_post_id'];

	$agency_post = get_post($agency_post_id);
	$agency_post_author = $agency_post->post_author;

	if ($agency_post_author != $current_user->ID && !current_user_can('level_10')) {
		$err .= __('You are not allowed to edit this profile','escortwp')."<br />";
	}
}

if(!$agency_post_id) {
    $user = preg_replace("/([^a-zA-Z0-9])/", "", $_POST['user']);
	if ($user) {
		if (strlen($user) < 4 || strlen($user) > 30) { $err .= __('Your username must be between 4 and 30 characters','escortwp')."<br />"; } else {
			if (username_exists($user)) { $err .= __('This username already exists. Please write another one','escortwp')."<br />"; }
		}
	} else {
		$err .= __('The username field is empty','escortwp')."<br />";
	}


	$pass = $_POST['pass'];
	if ($pass) {
		if (strlen($pass) < 6 || strlen($pass) > 50) {
			$err .= __('Your password must be between 6 and 50 characters','escortwp')."<br />";
		} else {
			if ( false !== strpos( stripslashes($pass), "\\" ) ) {
				$err .= __('Passwords may not contain the character "\"','escortwp')."<br />";
			}
		}
	} else {
		$err .= __('The password field is empty','escortwp')."<br />";
	}
}

$agencyemail = $_POST['agencyemail'];
if (!$agencyemail) { $err .= __('Please write your email address','escortwp')."<br />"; } else {
	if ( !is_email($agencyemail) ) { $err .= __('Your email address seems to be wrong','escortwp')."<br />"; }
	if ( email_exists($agencyemail) && $agencyemail != get_the_author_meta('user_email', $agency_post_author) ) {
		$err .= __('This email address has already been used by someone else','escortwp')."<br />";
		if ($agency_post_id) {
			$agencyemail = get_the_author_meta('user_email', $agency_post_author);
		}
	}
	if($agency_post_id && !$err && $agencyemail != get_the_author_meta('user_email', $agency_post_author)) {
		$new_agencyemail = $_POST['agencyemail'];
	}
}

if(current_user_can('level_10')) {
	$sendverification = (int)$_POST['sendverification'];
	$sendauth = (int)$_POST['sendauth'];
} else {
	unset($sendverification, $sendauth);
}

$agencyname = wp_strip_all_tags($_POST['agencyname'], true);
if (!$agencyname) { $err .= sprintf(esc_html__('Please write your %s name.','escortwp'),$taxonomy_agency_name)."<br />"; }


$phone = substr(wp_strip_all_tags($_POST['phone'], true), 0, 50);
if (!$phone) { $err .= __('Please write your phone number','escortwp')."<br />"; }


if ($_POST['website']) {
	$website = substr(esc_url($_POST['website'], array('http', 'https')), 0 , 300);
	if (!$website) {
		$err .= __('Your website url seems to be wrong','escortwp')."<br />";
	}
} elseif (ismand('website', 'no')) {
	$err .= __('Please write a website url for your profile','escortwp')."<br />";
}

if ($_POST['country'] && $_POST['country'] > 0) {
	$country = (int)$_POST['country'];
	$city_parent = $country;
	if (!term_exists( $country, $taxonomy_location_url )) {
		$err .= __('The country you selected doesn\'t exist in our database','escortwp')."<br />"; unset($country);
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
						if (function_exists('icl_object_id')) { global $sitepress; $current_lang = ICL_LANGUAGE_CODE; $sitepress->switch_lang($sitepress->get_default_language()); }
						wp_insert_term($state, $taxonomy_location_url, $arg);
						if (function_exists('icl_object_id')) { $sitepress->switch_lang($current_lang); }
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
					if (function_exists('icl_object_id')) { global $sitepress; $current_lang = ICL_LANGUAGE_CODE; $sitepress->switch_lang($sitepress->get_default_language()); }
					wp_insert_term($city, $taxonomy_location_url, $arg);
					if (function_exists('icl_object_id')) { $sitepress->switch_lang($current_lang); }
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

$aboutagency = substr(stripslashes(wp_kses($_POST['aboutagency'], array())), 0, 5000);
if (!$aboutagency) { $err .= sprintf(esc_html__('You must write something about the %s.','escortwp'),$taxonomy_agency_name)."<br />"; }

//spam/emails field must be empty to continue
$emails = $_POST['emails'];
if ($emails != "") { $err = ".<br />"; }

if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && !is_user_logged_in() && get_option("recaptcha3")) { $err .= verify_recaptcha(); }

$tos_accept = (int)$_POST['tos_accept'];
$tos_page_data = get_post(get_option('tos_page_id'));
$data_protection_page_data = get_post(get_option('data_protection_page_id'));
if(($tos_page_data || $data_protection_page_data) && !is_user_logged_in() && $tos_accept != "1") {
	$err .= __('You need to agree to our terms and conditions in order to register','escortwp')."<br />";
}

if (!$err) {
	if ($agency_post_id) {
		$new_user_id = $agency_post_author;
	} else {
		$new_user_id = wp_create_user( $user, $pass, $agencyemail );
		//set an email hash so the user needs to validate his email in order to use the site
		if(!current_user_can('level_10') || $sendverification == "1") {
			$emailhash = md5($new_user_id.$user.$agencyemail);
			update_user_meta( $new_user_id, "emailhash",  $emailhash);
		}
	}
	wp_update_user( array ('ID' => $new_user_id, 'nickname' => $agencyname, 'display_name' => $agencyname, 'user_url' => $website) );
	if($new_agencyemail) {
		wp_update_user( array ('ID' => $new_user_id, 'user_email' => $new_agencyemail) );
	}

	if (!$agency_post_id) {
		//adding the id in the database and the type of user it is, escort or agency, so we can check later what privileges they have
		escortwp_child_update_dynamic_user_option("escortid".$new_user_id, $taxonomy_agency_url);
	}


	if ($agency_post_id) {
		// Create post object
		$post_agency = array(
			'ID' => $agency_post_id,
			'post_title' => $agencyname,
			'post_name' => $agencyname,
			'post_content' => $aboutagency
		);
		//update the post
		wp_update_post( $post_agency );
		$post_agency_id = $agency_post_id;
	} else {
		$post_status = "private";
		if (get_option("manactivagprof") == 1) {
			$post_status = "private"; //setting the profile to private until the admin activates the account
		}

		if ($sendverification == "1") {
			$post_status = "private"; //setting the profile to draft until the user validates his email
		} elseif(payment_plans('agreg','price')) {
			$post_status = "private";
		}

		// Create post object
		$post_agency = array(
			'post_title' => $agencyname,
			'post_content' => $aboutagency,
			'post_status' => $post_status,
			'post_author' => $new_user_id,
			'post_type' => $taxonomy_agency_url,
			'ping_status' => 'closed'
		);
		// Insert the post into the database
		if (function_exists('icl_object_id')) { global $sitepress; $current_lang = ICL_LANGUAGE_CODE; $sitepress->switch_lang($sitepress->get_default_language()); }
		$post_agency_id = wp_insert_post($post_agency);
		if (function_exists('icl_object_id')) { $sitepress->switch_lang($current_lang); }

		if(!current_user_can('level_10') && get_option("manactivagprof") == "1") {
			update_post_meta($post_agency_id, "notactive", "1"); //requires admin activation
		} // if not admin

		update_post_meta($post_agency_id, "ip", getenv('REMOTE_ADDR'));
		update_post_meta($post_agency_id, "hostname", gethostbyaddr($_SERVER['REMOTE_ADDR']));
		update_post_meta($post_agency_id, "premium", "0");
	}
	wp_set_post_terms($post_agency_id, $city_id, $taxonomy_location_url);
	update_post_meta($post_agency_id, "phone", $phone);
	update_post_meta($post_agency_id, "country", $country);
	if(showfield('state')) {
		update_post_meta($post_agency_id, "state", $state_id);
	}
	update_post_meta($post_agency_id, "city", $city_id);
	if (!$agency_post_id) {
		$secret = md5($agencyemail.$user.time().rand(1,999));
		update_post_meta($post_agency_id, "secret", $secret);
		update_post_meta($post_agency_id, "upload_folder", time().rand(1,999));
		if(payment_plans('agreg','price') && !current_user_can('level_10')) {
			update_post_meta($post_agency_id, "needs_payment", "1");
		}

		//add the post id where this agency keeps all the info and images
		escortwp_child_update_dynamic_user_option("agencypostid".$new_user_id, $post_agency_id);
		update_option($secret, $new_user_id);


		if(!current_user_can('level_10') || $sendverification == "1") {
			$emailtext = __('Before you can use the site you will need to validate your email address.','escortwp').'
'.__('If you don\'t validate your email in the next 3 days your account will be deleted.','escortwp').'<br /><br />
'.__('Please validate your email address by clicking the link bellow','escortwp').':
<a href="'.get_bloginfo('url').'/?ekey='.$emailhash.'">'.get_bloginfo('url').'/?ekey='.$emailhash.'</a><br /><br />';
		} else {
			$emailtext = __('Your account is now active on','escortwp').' '.get_option("email_sitename").'<br /><br />';
		}

		if(!current_user_can('level_10') || $sendverification == "1") {
			$emailtitle = __('Email validation link','escortwp');
		} else {
			$emailtitle = __('Welcome to','escortwp');
		}

		if($sendauth == "1") {
		} else {
			$pass = '('.__('hidden','escortwp').')';
		}

		//send email to agency
		$body = __('Hello','escortwp').' '.$agencyname.',<br /><br />
'.$emailtext.'
'.__('Account information','escortwp').':<br />
'.__('type','escortwp').': <b>'.$taxonomy_agency_name.'</b><br />
'.__('username','escortwp').': <b>'.$user.'</b><br />
'.__('password','escortwp').': <b>'.$pass.'</b><br /><br />
'.__('You can view your account here','escortwp').':<br />
<a href="'.get_permalink($post_agency_id).'">'.get_permalink($post_agency_id).'</a>';
		dolce_email("", "", $agencyemail, $emailtitle." ".get_option("email_sitename"), $body);

		if ($admin_adding_agency == "yes") {
			wp_redirect(get_permalink($post_agency_id)); exit;
		} else {
			wp_clear_auth_cookie(); //delete the cookies of the user if he is already logged in. for example if he is the admin
			wp_set_auth_cookie($new_user_id, true, ''); //add login cookies to the user so we can identify him
			wp_redirect(get_permalink(get_option('agency_reg_page_id'))); exit;
		}
	} else {
		if ($admin_adding_agency == "yes") {
			wp_redirect(get_permalink($post_agency_id)); exit;
		}

		$ok = "ok";
	}
	
}
?>
