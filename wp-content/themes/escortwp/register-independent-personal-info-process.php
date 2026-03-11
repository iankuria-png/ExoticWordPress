<?php
if (!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if (error_reporting == '1') { error_reporting( E_ALL ); }
if (isdolcetheme !== 1) { die(); }

global $taxonomy_profile_name, $taxonomy_profile_url, $taxonomy_location_url, $taxonomy_agency_name, $gender_a, $ethnicity_a, $haircolor_a, $hairlength_a, $bustsize_a, $build_a, $looks_a, $smoker_a, $availability_a, $languagelevel_a, $services_a, $currency_a, $taxonomy_profile_name, $taxonomy_agency_name, $taxonomy_profile_name_plural, $taxonomy_profile_url, $taxonomy_agency_url, $payment_duration_a;

$current_user = wp_get_current_user();

if (current_user_can('level_10')) { $admin_registers_independent_escort = "yes"; }

if ($_POST['escort_post_id']) {
	$escort_post_id = (int)$_POST['escort_post_id'];

	$escort_post = get_post($escort_post_id);
	$escort_post_author = $escort_post->post_author;

	if ($escort_post_author != $current_user->ID && !current_user_can('level_10')) {
		$err .= __('You are not allowed to edit this profile','escortwp')."<br />";
	}
}

if ($_POST['agencyid']) {
	$agencyid = (int)$_POST['agencyid'];
}


if (!$escort_post_id && !$agencyid) {
    $user = sanitize_user($_POST['user']);
	if ($user) {
		if (strlen($user) < 4 || strlen($user) > 30) { $err .= __('Your username must be between 4 and 30 characters','escortwp')."<br />"; } else {
			if (username_exists($user)) { $err .= __('This username already exists. Please select another one','escortwp')."<br />"; }
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

if (!$agencyid) {
    $youremail = trim($_POST['youremail']);
	if (!$youremail) {
		$err .= __('Please write your email address','escortwp')."<br />";
	} else {
		if (!is_email($youremail)) {
			$err .= __('Your email address seems to be wrong','escortwp')."<br />";
		}
		if (email_exists($youremail) && !$escort_post_id) {
			$err .= __('The email address has been used by someone else already','escortwp')."<br />";
		} // if email_exists()
		if($escort_post_id && !$err && $youremail != get_the_author_meta('user_email', $escort_post_id)) {
			$new_youremail = trim($_POST['youremail']);
		}
	} // if !$youremail
} // if !$agencyid


if (current_user_can('level_10')) {
	$sendverification = (int)$_POST['sendverification'];
	$sendauth = (int)$_POST['sendauth'];
} else {
	unset($sendverification, $sendauth);
}

$yourname = substr(stripslashes(wp_strip_all_tags($_POST['yourname'], true)), 0, 200);
if (!$yourname) { $err .= __('Please write your name','escortwp')."<br />"; }


$phone = substr(wp_strip_all_tags($_POST['phone'], true), 0, 50);
if (!$phone && ismand('phone', 'no')) { $err .= __('Please write your phone number','escortwp')."<br />"; }

$phone_available_on = $_POST['phone_available_on'];
if ($phone && $phone_available_on && is_array($phone_available_on)) {
	foreach ($phone_available_on as $i => $one) {
		$one = (int)$one;
		if ($one != "1" && $one != "2") { unset($phone_available_on[$i]); }
	}
}

if ($_POST['website']) {
	$website = substr(esc_url($_POST['website']), 0 , 300);
	if (!$website) {
		$err .= __('Your website url seems to be wrong','escortwp')."<br />";
	}
} elseif (ismand('website', 'no')) {
	$err .= __('Please write a website url for your profile','escortwp')."<br />";
}

if ($_POST['instagram']) {
	$instagram = substr(sanitize_text_field($_POST['instagram']), 0 , 300);
} elseif (ismand('instagram', 'no')) {
	$err .= __('Please write your instagram username','escortwp')."<br />";
}

if ($_POST['snapchat']) {
	$snapchat = substr(sanitize_text_field($_POST['snapchat']), 0 , 300);
} elseif (ismand('snapchat', 'no')) {
	$err .= __('Please write your SnapChat username','escortwp')."<br />";
}

if ($_POST['twitter']) {
	$twitter = substr(esc_url($_POST['twitter']), 0 , 500);
	if (!$twitter) {
		$err .= __('Your Twitter url seems to be wrong','escortwp')."<br />";
	}
} elseif (ismand('twitter', 'no')) {
	$err .= __('Please write your Twitter page url','escortwp')."<br />";
}

if ($_POST['facebook']) {
	$facebook = substr(esc_url($_POST['facebook']), 0 , 500);
	if (!$facebook) {
		$err .= __('Your Facebook url seems to be wrong','escortwp')."<br />";
	}
} elseif (ismand('facebook', 'no')) {
	$err .= __('Please write your Facebook page url','escortwp')."<br />";
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

	if (function_exists('icl_object_id')) {
		global $sitepress;
		if($sitepress->get_default_language() != ICL_LANGUAGE_CODE) {
			$country = icl_object_id($city_parent, $taxonomy_location_url, true, $sitepress->get_default_language());
			if(showfield('state')) {
				$state_id = icl_object_id($state_id, $taxonomy_location_url, true, $sitepress->get_default_language());
			}
			$city_id = icl_object_id($city_id, $taxonomy_location_url, true, $sitepress->get_default_language());
		}
	}
} else {
	$err .= __('You need to select a country','escortwp')."<br />"; unset($country);
}

if ($_POST['gender']) {
	$gender = (int)$_POST['gender'];
	if (!$gender_a[$gender]) { $err .= __('Please choose your gender','escortwp')."<br />"; unset($gender); }
} else {
	$err .= __('Please choose your gender','escortwp')."<br />";
}

if ($_POST['dateday'] && $_POST['datemonth'] && $_POST['dateyear']) {
    $dateday = (int)$_POST['dateday'];
    if ($dateday < 1 || $dateday > 31) {
        $err .= __('The day from you date of birth is wrong','escortwp')."<br />"; unset($dateday);
    }
    $datemonth = (int)$_POST['datemonth'];
    if ($datemonth < 1 || $datemonth > 12) {
        $err .= __('The month from you date of birth is wrong','escortwp')."<br />"; unset($datemonth);
    }
    $dateyear = (int)$_POST['dateyear'];
    if (strlen($dateyear) != "4") {
        $err .= __('The year from you date of birth is wrong','escortwp')."<br />"; unset($dateyear);
    }

    if (isset($dateday) && isset($datemonth) && isset($dateyear)) {
        $birthday = sprintf('%04d-%02d-%02d', $dateyear, $datemonth, $dateday);
        $birth_timestamp = strtotime($birthday);
        $current_timestamp = current_time('timestamp');
        $age = floor(($current_timestamp - $birth_timestamp) / YEAR_IN_SECONDS);
        
        if ($age < 18) {
            $err .= __('You must be at least 18 years old to register on this website','escortwp')."<br />"; unset($dateyear);
        }
    }
} else {
    $err .= __('Please write your date of birth','escortwp')."<br />";
}

if ($_POST['ethnicity']) {
    $ethnicity = (int)$_POST['ethnicity'];
	if (!$ethnicity_a[$ethnicity]) { $err .= __('Choose your ethnicity','escortwp')."<br />"; unset($ethnicity); }
} elseif (ismand('ethnicity', 'no')) {
	$err .= __('Choose your ethnicity','escortwp')."<br />";
}

if ($_POST['haircolor']) {
    $haircolor = (int)$_POST['haircolor'];
	if (!$haircolor_a[$haircolor]) { $err .= __('Choose your hair color','escortwp')."<br />"; unset($haircolor); }
} elseif (ismand('haircolor', 'no')) {
	$err .= __('Choose your hair color','escortwp')."<br />";
}

if ($_POST['hairlength']) {
    $hairlength = (int)$_POST['hairlength'];
	if (!$hairlength_a[$hairlength]) { $err .= __('Choose your hair length','escortwp')."<br />"; unset($hairlength); }
} elseif (ismand('hairlength', 'no')) {
	$err .= __('Choose your hair length','escortwp')."<br />";
}

if ($_POST['bustsize']) {
    $bustsize = (int)$_POST['bustsize'];
	if (!$bustsize_a[$bustsize]) { $err .= __('Choose your bust size','escortwp')."<br />"; unset($bustsize); }
} else {
	if ($gender == "1" && ismand('bustsize', 'no')) {
		$err .= __('Choose your bust size','escortwp')."<br />";
	}
}

if ($_POST['height']) {
    $height = (int)$_POST['height'];
	if ($height < 1) { $err .= __('Choose your height','escortwp')."<br />"; unset($height); }
} elseif (ismand('height', 'no')) {
	$err .= __('Choose your height','escortwp')."<br />";
}
if ($_POST['height2']) {
    $height2 = (int)$_POST['height2'];
	if ($height2 < 1) { unset($height2); }
}

if ($_POST['weight']) {
    $weight = (int)$_POST['weight'];
	if ($weight < 1) { $err .= __('Choose your weight','escortwp')."<br />"; unset($weight); }
} elseif (ismand('weight', 'no')) {
	$err .= __('Choose your weight','escortwp')."<br />";
}

if ($_POST['build']) {
    $build = (int)$_POST['build'];
	if (!$build_a[$build]) { $err .= __('Chose your built type','escortwp')."<br />"; unset($build); }
} elseif (ismand('build', 'no')) {
	$err .= __('Chose your built type','escortwp')."<br />";
}

if ($_POST['looks']) {
    $looks = (int)$_POST['looks'];
	if (!$looks_a[$looks]) { $err .= __('Choose your looks','escortwp')."<br />"; unset($looks); }
} elseif (ismand('looks', 'no')) {
	$err .= __('Choose your looks','escortwp')."<br />";
}

if ($_POST['smoker']) {
    $smoker = (int)$_POST['smoker'];
	if ($smoker != $_POST['smoker'] || !$smoker_a[$smoker]) { $err .= __('Are you a smoker or a non smoker?','escortwp')."<br />"; unset($smoker); }
} elseif (ismand('smoker', 'no')) {
	$err .= __('Are you a smoker or a non smoker?','escortwp')."<br />";
}

$availability = $_POST['availability'];
if ($availability && is_array($availability)) {
	foreach ($availability as $i => $one) {
		$one = preg_replace("/([^0-9])/", "", $one);
		if ($one != "1" && $one != "2") { unset($availability[$i]); }
	}
} elseif (ismand('availability', 'no')) {
	$err .= __('Please choose your availability','escortwp')."<br />";
}

$aboutyou = substr(stripslashes(wp_kses($_POST['aboutyou'], array())), 0, 5000);
if (!$aboutyou && ismand('aboutyou', 'no')) { $err .= __('You must write something about you.','escortwp')."<br />"; }

$education = substr(stripslashes(wp_strip_all_tags($_POST['education'])), 0, 300);
if (!$education && ismand('education', 'no')) { $err .= __('Please write an education','escortwp')."<br />"; }

$sports = substr(stripslashes(wp_strip_all_tags($_POST['sports'])), 0, 300);
if (!$sports && ismand('sports', 'no')) { $err .= __('Please write what sports you like','escortwp')."<br />"; }

$hobbies = substr(stripslashes(wp_strip_all_tags($_POST['hobbies'])), 0, 300);
if (!$hobbies && ismand('hobbies', 'no')) { $err .= __('Please write what hobbies you have','escortwp')."<br />"; }

$zodiacsign = substr(stripslashes(wp_strip_all_tags($_POST['zodiacsign'])), 0, 300);
if (!$zodiacsign && ismand('zodiacsign', 'no')) { $err .= __('Please write your zodiac sign','escortwp')."<br />"; }

$sexualorientation = substr(stripslashes(wp_strip_all_tags($_POST['sexualorientation'])), 0, 300);
if (!$sexualorientation && ismand('sexualorientation', 'no')) { $err .= __('Please write your sexual orientation','escortwp')."<br />"; }

$occupation = substr(stripslashes(wp_strip_all_tags($_POST['occupation'])), 0, 300);
if (!$occupation && ismand('occupation', 'no')) { $err .= __('Please write your occupation','escortwp')."<br />"; }

$language1 = substr(stripslashes(wp_strip_all_tags($_POST['language1'])), 0, 300);
if ($language1) {
	if ($_POST['language1level']) {
		$language1level = (int)$_POST['language1level'];
		if (!$languagelevel_a[$language1level]) { $err .= __('Please choose a language level for','escortwp')." $language1<br />"; unset($language1level); }
	} else {
		$err .= __('Please choose a language level for','escortwp')." $language1<br />";
	}
} else {
	unset($language1level);
}

$language2 = substr(stripslashes(wp_strip_all_tags($_POST['language2'])), 0, 300);
if ($language2) {
	if ($_POST['language2level']) {
		$language2level = (int)$_POST['language2level'];
		if (!$languagelevel_a[$language2level]) { $err .= __('Please choose a language level for','escortwp')." $language2<br />"; unset($language2level); }
	} else {
		$err .= __('Please choose a language level for','escortwp')." $language2<br />";
	}
} else {
	unset($language2level);
}

$language3 = substr(stripslashes(wp_strip_all_tags($_POST['language3'])), 0, 300);
if ($language3) {
	if ($_POST['language3level']) {
		$language3level = (int)$_POST['language3level'];
		if (!$languagelevel_a[$language3level]) { $err .= __('Please choose a language level for','escortwp')." $language3<br />"; unset($language3level); }
	} else {
		$err .= __('Please choose a language level for','escortwp')." $language3<br />";
	}
} else {
	unset($language3level);
}
if (!$language1 && !$language2 && !$language3 && ismand('language', 'no')) {
	$err .= __('Please choose at least one language and conversation level','escortwp')."<br />";
}

$rate30min_incall = substr((int)$_POST['rate30min_incall'], 0, 50);
if (!$rate30min_incall || $rate30min_incall == "0") { unset($rate30min_incall); }
$rate1h_incall = substr((int)$_POST['rate1h_incall'], 0, 50);
if (!$rate1h_incall || $rate1h_incall == "0") { unset($rate1h_incall); }
$rate2h_incall = substr((int)$_POST['rate2h_incall'], 0, 50);
if (!$rate2h_incall || $rate2h_incall == "0") { unset($rate2h_incall); }
$rate3h_incall = substr((int)$_POST['rate3h_incall'], 0, 50);
if (!$rate3h_incall || $rate3h_incall == "0") { unset($rate3h_incall); }
$rate6h_incall = substr((int)$_POST['rate6h_incall'], 0, 50);
if (!$rate6h_incall || $rate6h_incall == "0") { unset($rate6h_incall); }
$rate12h_incall = substr((int)$_POST['rate12h_incall'], 0, 50);
if (!$rate12h_incall || $rate12h_incall == "0") { unset($rate12h_incall); }
$rate24h_incall = substr((int)$_POST['rate24h_incall'], 0, 50);
if (!$rate24h_incall || $rate24h_incall == "0") { unset($rate24h_incall); }

$rate30min_outcall = substr((int)$_POST['rate30min_outcall'], 0, 50);
if (!$rate30min_outcall || $rate30min_outcall == "0") { unset($rate30min_outcall); }
$rate1h_outcall = substr((int)$_POST['rate1h_outcall'], 0, 50);
if (!$rate1h_outcall || $rate1h_outcall == "0") { unset($rate1h_outcall); }
$rate2h_outcall = substr((int)$_POST['rate2h_outcall'], 0, 50);
if (!$rate2h_outcall || $rate2h_outcall == "0") { unset($rate2h_outcall); }
$rate3h_outcall = substr((int)$_POST['rate3h_outcall'], 0, 50);
if (!$rate3h_outcall || $rate3h_outcall == "0") { unset($rate3h_outcall); }
$rate6h_outcall = substr((int)$_POST['rate6h_outcall'], 0, 50);
if (!$rate6h_outcall || $rate6h_outcall == "0") { unset($rate6h_outcall); }
$rate12h_outcall = substr((int)$_POST['rate12h_outcall'], 0, 50);
if (!$rate12h_outcall || $rate12h_outcall == "0") { unset($rate12h_outcall); }
$rate24h_outcall = substr((int)$_POST['rate24h_outcall'], 0, 50);
if (!$rate24h_outcall || $rate24h_outcall == "0") { unset($rate24h_outcall); }

$rates_sum = $rate30min_incall + $rate1h_incall + $rate2h_incall + $rate3h_incall + $rate6h_incall + $rate12h_incall + $rate24h_incall + $rate30min_outcall + $rate1h_outcall + $rate2h_outcall + $rate3h_outcall + $rate6h_outcall + $rate12h_outcall + $rate24h_outcall;
if ($rates_sum < 1 && ismand('rates', 'no')) {
	$err .= __('Please choose at least one rate price','escortwp')."<br />";
}

$currency = (int)$_POST['currency'];
if (!$currency_a[$currency] && ismand('rates', 'no')) {
	$err .= __('Please choose a currency','escortwp')."<br />"; unset($currency);
}


$services = $_POST['services'];
if ($services && is_array($services)) {
	foreach ($services as $i => $service) {
		$service = preg_replace("/([^0-9])/", "", $service);
		if (!$service && $service != "0") { unset($services[$i]); }
	}
	sort($services);

	if ( count($services) == 0 ) {
		$err .= __('You have to select at least one service','escortwp')."<br />";
	} else {
		foreach($services as $i => $service) {
			if (!$services_a[$service]) { unset($services[$i]); }
		}
		if ( count($services) == 0 ) {
			$err .= __('You have to select at least one service','escortwp')."<br />";
		}
	} // if count == 0
} elseif (ismand('services', 'no')) {
	$err .= __('You have to select at least one service','escortwp')."<br />";
} // if $services

$extraservices = substr(wp_strip_all_tags($_POST['extraservices']), 0, 300);
if (ismand('extraservices', 'no') && !$extraservices) { $err .= __('Please write what other extra services you offer','escortwp')."<br />"; }

//spam/emails field must be empty to continue
$emails = $_POST['emails'];
if ($emails != "") { $err = ".<br />"; }

if (get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && !is_user_logged_in() && get_option("recaptcha2")) { $err .= verify_recaptcha(); }

$tos_accept = (int)$_POST['tos_accept'];
$tos_page_data = get_post(get_option('tos_page_id'));
$data_protection_page_data = get_post(get_option('data_protection_page_id'));
if(($tos_page_data || $data_protection_page_data) && !is_user_logged_in() && $tos_accept != "1") {
	$err .= __('You need to agree to our terms and conditions in order to register','escortwp')."<br />";
}

if (!$err) {
	if ($escort_post_id || $agencyid) {
		$new_user_id = $current_user->ID;
		if ($admin_adding_escort == "yes") {
			$new_user_id = $agencyid;
		}

		if(get_option("escortid".$new_user_id) == $taxonomy_profile_url && !$agencyid && !current_user_can('level_10')) {
			if($new_youremail) $youremail = $new_youremail;
			wp_update_user(array('ID' => $new_user_id, 'user_email' => $youremail));
		}
	} else {
		$new_user_id = wp_create_user($user, $pass, $youremail);
		if (is_wp_error($new_user_id)) {
			foreach($new_user_id->errors as $key => $error) {
				$err .= $error[0];
			}
			return false;
		}
		if ($admin_registers_independent_escort == "yes") {
			// set an email hash so the user needs to validate his email in order to use the site
			// if the escort is added by an admin don't add a hash
			if ($sendverification == "1") {
				// create unique email hash
				$emailhash = md5($new_user_id.$user.$youremail."1wg807xlhf4x66vxna30");
				update_user_meta( $new_user_id, "emailhash", $emailhash );
			}
		} else {
			$emailhash = md5($new_user_id.$user.$youremail);
			update_user_meta( $new_user_id, "emailhash", $emailhash );
		}
	}

	if (!$agencyid) {
		wp_update_user( array ('ID' => $new_user_id, 'nickname' => $yourname, 'display_name' => $yourname, 'user_url' => $website) );
	}

	if (!$escort_post_id && !$agencyid) {
		// adding the id in the database and the type of user it is, profile or agency, so we can check later what privileges they have
		escortwp_child_update_dynamic_user_option("escortid".$new_user_id, $taxonomy_profile_url);
	}


	if ($escort_post_id) {
		// Update post
		$post_escort = array(
			'ID' => $escort_post_id,
			'post_title' => $yourname,
			'post_content' => $aboutyou,
			'post_name' => $yourname
		);
		//update the post
		wp_update_post( $post_escort );
		$post_escort_id = $escort_post_id;
	} else {
		// since all the independent escorts need to validate their email we start with the private status
		$post_status = "private";
		if ($agencyid) {
			// agencies don't need to validate anything when adding escorts so we change the status to publish
			$post_status = "publish";
			if (get_option("manactivagescprof") == "1") {
				// if the admin wants to manually activate escorts added by agencies we change the status to private
				$post_status = "private";
			}
			if (payment_plans('agescortreg','price')) {
				// if there is a price for the adding of escorts by agencies then we change the status to private
				$post_status = "private";
			}
		} else {
			if ($sendverification == "2") {
				$post_status = "publish";
			}
			if (get_option("manactivindescprof") == "1" && !current_user_can('level_10')) {
				// if the admin wants to manually activate independent escorts
				$post_status = "private";
			}
			if (payment_plans('indescreg','price')) {
				// if there is a price for the adding of independent escorts
				$post_status = "private";
			}
		}

		// Create post object
		$post_escort = array(
			'post_title' => $yourname,
			'post_content' => $aboutyou,
			'post_name' => $yourname,
			'post_status' => $post_status,
			'post_author' => $new_user_id,
			'post_type' => $taxonomy_profile_url,
			'ping_status' => 'closed'
		);
		// Insert the post into the database
		$post_escort_id = wp_insert_post($post_escort);
		update_post_meta($post_escort_id, "ip", getenv('REMOTE_ADDR'));
		update_post_meta($post_escort_id, "hostname", gethostbyaddr($_SERVER['REMOTE_ADDR']));

		if ($agencyid) {
			if (get_option("manactivagescprof") == "1") {
				update_post_meta($post_escort_id, "notactive", "1"); // requires admin activation
			}
			if (payment_plans('agescortreg','price')) {
				update_post_meta($post_escort_id, "needs_payment", "1"); // requires payment
			}
		} else {
			if (get_option("manactivindescprof") == "1") {
				update_post_meta($post_escort_id, "notactive", "1"); // requires admin activation
			}
			if (payment_plans('indescreg','price')) {
				update_post_meta($post_escort_id, "needs_payment", "1"); // requires payment
			}
		}
	}

	if (function_exists('icl_object_id')) {
	    $languages = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' );
	    if (!empty($languages)) {
	        foreach($languages as $l) {
				$city_id_arr[] = icl_object_id($city_id, $taxonomy_location_url, true, $l['language_code']);
	        }
	    }
		wp_set_post_terms($post_escort_id, $city_id_arr, $taxonomy_location_url);
	} else {
		wp_set_post_terms($post_escort_id, $city_id, $taxonomy_location_url);
	}

	update_post_meta($post_escort_id, "phone", $phone);
	update_post_meta($post_escort_id, "phone_available_on", $phone_available_on);
	update_post_meta($post_escort_id, "website", $website);
	update_post_meta($post_escort_id, "instagram", $instagram);
	update_post_meta($post_escort_id, "snapchat", $snapchat);
	update_post_meta($post_escort_id, "twitter", $twitter);
	update_post_meta($post_escort_id, "facebook", $facebook);
	update_post_meta($post_escort_id, "country", $country);
	if(showfield('state')) {
		update_post_meta($post_escort_id, "state", $state_id);
	}
	update_post_meta($post_escort_id, "city", $city_id);
	update_post_meta($post_escort_id, "gender", $gender);
	update_post_meta($post_escort_id, "birthday", "$dateyear-$datemonth-$dateday");
	update_post_meta($post_escort_id, "ethnicity", $ethnicity);
	update_post_meta($post_escort_id, "haircolor", $haircolor);
	update_post_meta($post_escort_id, "hairlength", $hairlength);
	update_post_meta($post_escort_id, "bustsize", $bustsize);
	update_post_meta($post_escort_id, "height", $height);
	update_post_meta($post_escort_id, "height2", $height2);
	update_post_meta($post_escort_id, "weight", $weight);
	update_post_meta($post_escort_id, "build", $build);
	update_post_meta($post_escort_id, "looks", $looks);
	update_post_meta($post_escort_id, "smoker", $smoker);
	update_post_meta($post_escort_id, "availability", $availability);
	update_post_meta($post_escort_id, "education", $education);
	update_post_meta($post_escort_id, "sports", $sports);
	update_post_meta($post_escort_id, "hobbies", $hobbies);
	update_post_meta($post_escort_id, "zodiacsign", $zodiacsign);
	update_post_meta($post_escort_id, "sexualorientation", $sexualorientation);
	update_post_meta($post_escort_id, "occupation", $occupation);
	update_post_meta($post_escort_id, "language1", $language1);
	update_post_meta($post_escort_id, "language1level", $language1level);
	update_post_meta($post_escort_id, "language2", $language2);
	update_post_meta($post_escort_id, "language2level", $language2level);
	update_post_meta($post_escort_id, "language3", $language3);
	update_post_meta($post_escort_id, "language3level", $language3level);
	update_post_meta($post_escort_id, "currency", $currency);

	update_post_meta($post_escort_id, "rate30min_incall", $rate30min_incall);
	update_post_meta($post_escort_id, "rate1h_incall", $rate1h_incall);
	update_post_meta($post_escort_id, "rate2h_incall", $rate2h_incall);
	update_post_meta($post_escort_id, "rate3h_incall", $rate3h_incall);
	update_post_meta($post_escort_id, "rate6h_incall", $rate6h_incall);
	update_post_meta($post_escort_id, "rate12h_incall", $rate12h_incall);
	update_post_meta($post_escort_id, "rate24h_incall", $rate24h_incall);

	update_post_meta($post_escort_id, "rate30min_outcall", $rate30min_outcall);
	update_post_meta($post_escort_id, "rate1h_outcall", $rate1h_outcall);
	update_post_meta($post_escort_id, "rate2h_outcall", $rate2h_outcall);
	update_post_meta($post_escort_id, "rate3h_outcall", $rate3h_outcall);
	update_post_meta($post_escort_id, "rate6h_outcall", $rate6h_outcall);
	update_post_meta($post_escort_id, "rate12h_outcall", $rate12h_outcall);
	update_post_meta($post_escort_id, "rate24h_outcall", $rate24h_outcall);

	update_post_meta($post_escort_id, "services", $services);
	update_post_meta($post_escort_id, "extraservices", $extraservices);
	if (!$escort_post_id) {
		update_post_meta($post_escort_id, "premium", "0");
	}

	if (!$escort_post_id) {
		$secret = md5($yourname.$aboutyou.$phone.$website.time().rand(1,9999));
		update_post_meta($post_escort_id, "secret", $secret);
		update_post_meta($post_escort_id, "upload_folder", time().rand(1,999));


		if (!$agencyid) {
			//add the post id that the user created. The user will only be able to edit this single post
			escortwp_child_update_dynamic_user_option("escortpostid".$new_user_id, $post_escort_id);
			update_post_meta($post_escort_id, "independent", "yes");
			update_option($secret, $new_user_id);

			$emailtitle = __('Email validation link','escortwp');
			$emailtext = __('Before you can use the site you will need to validate your email address.','escortwp').'
'.__('If you don\'t validate your email in the next 3 days your account will be deleted.','escortwp').'<br /><br />
'.__('Please validate your email address by clicking the link bellow','escortwp').':
<a href="'.get_bloginfo('url').'/?ekey='.$emailhash.'">'.get_bloginfo('url').'/?ekey='.$emailhash.'</a><br /><br />';
			$emailtext_end = '<br /><br />
'.__('You can view your account here','escortwp').':<br />
<a href="'.get_permalink($post_escort_id).'">'.get_permalink($post_escort_id).'</a>';

			if ($sendverification == "2") {
				$emailtitle = __('Welcome to','escortwp');
				$emailtext = __('Your account is now active on','escortwp').' '.get_option("email_sitename").'<br /><br />';
			}

			if ($sendauth != "1") $pass = '('.__('hidden','escortwp').')';


			// send email to escort
			$body = __('Hello','escortwp').' '.$yourname.',<br /><br />
'.$emailtext.'
'.__('Account information','escortwp').':<br />
'.__('type','escortwp').': <b>'.sprintf(esc_html__('independent %s','escortwp'),$taxonomy_profile_name).'</b><br />
'.__('username','escortwp').': <b>'.$user.'</b><br />
'.__('password','escortwp').': <b>'.$pass.'</b>'.$emailtext_end;
			dolce_email("", "", $youremail, $emailtitle." ".get_option("email_sitename"), $body);


			if (!$admin_registers_independent_escort) {
				wp_clear_auth_cookie(); //delete the cookies of the user if he is already logged in. for example if he is the admin
				wp_set_auth_cookie($new_user_id, true, ''); //add login cookies to the user so we can identify him
				wp_redirect(get_permalink(get_option('escort_reg_page_id'))); exit;
			} else {
				wp_redirect(get_permalink($post_escort_id)); exit;
			}
		} else { // if agency
			$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('A new %s has been added on','escortwp'),$taxonomy_profile_name).' '.get_option("email_sitename").':<br /><br />
'.__('Account information','escortwp').':<br />
'.__('type','escortwp').': <b>'.sprintf(esc_html__('%1$s added by %2$s','escortwp'),$taxonomy_profile_name, $taxonomy_agency_name).'</b><br /><br />
'.__('You can view the account here','escortwp').':<br />
<a href="'.get_permalink($post_escort_id).'">'.get_permalink($post_escort_id).'</a>';
			dolce_email(null, null, get_bloginfo("admin_email"), sprintf(esc_html__('New %s on','escortwp'),$taxonomy_profile_name)." ".get_option("email_sitename"), $body);

			//adding the secret to the database along with the post id so we can let agencies add images
			update_option("agency".$secret, $post_escort_id);
			wp_redirect(get_permalink($post_escort_id)); exit;
		}  // if agency
	} else { // if !$escort_post_id
		if ($single_page == "yes" || $admin_adding_escort == "yes") {
			wp_redirect(get_permalink($escort_post_id)); exit;
		}
		$ok = "ok";
	} // if !$escort_post_id
}
?>
