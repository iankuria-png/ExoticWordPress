<?php
global $post, $taxonomy_location_url, $gender_a, $ethnicity_a, $haircolor_a, $hairlength_a, $bustsize_a, $build_a, $looks_a, $smoker_a, $availability_a, $languagelevel_a, $services_a, $currency_a, $taxonomy_profile_name, $taxonomy_agency_name, $taxonomy_profile_name_plural, $taxonomy_profile_url, $taxonomy_agency_url, $payment_duration_a;
$current_user = wp_get_current_user();
if (is_user_logged_in()) {
	$userid = $current_user->ID;
	$userstatus = get_option("escortid" . $userid);
} else {
	$userid = "none";
	$userstatus = "none";
}

$profile_author_id = $post->post_author;
$this_post_id = get_the_ID();

/* Recently Viewed (server-side cookie, safe) */
$rv_cookie_name = 'escortwp_recently_viewed';
$rv_ids = array();

if (!empty($_COOKIE[$rv_cookie_name])) {
	$rv_raw = (string) $_COOKIE[$rv_cookie_name];
	$rv_ids = array_filter(array_map('intval', preg_split('/\s*,\s*/', $rv_raw)));
}

$this_post_id = (int) $this_post_id;
$rv_ids = array_values(array_diff($rv_ids, array($this_post_id)));
array_unshift($rv_ids, $this_post_id);
$rv_ids = array_slice(array_unique($rv_ids), 0, 20);

/* Only try to set the cookie if headers are not sent */
if (!headers_sent()) {
	// Fallbacks so we don't error if constants are unusual
	$cookie_path = (defined('COOKIEPATH') && COOKIEPATH) ? COOKIEPATH : '/';
	$cookie_domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
	$secure = function_exists('is_ssl') ? is_ssl() : (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

	// PHP 7-compatible call signature (works on PHP 8 too)
	setcookie(
		$rv_cookie_name,
		implode(',', $rv_ids),
		time() + (60 * 60 * 24 * 30), // 30 days
		$cookie_path,
		$cookie_domain,
		$secure,
		true // httponly
	);
}


/* ---------------- Track Recently Viewed (cookie-based) ---------------- */
if (is_singular($taxonomy_profile_url)) {
	$recent = isset($_COOKIE['escortwp_recently_viewed']) ? explode(',', $_COOKIE['escortwp_recently_viewed']) : [];

	// remove if already in list
	if (($key = array_search($this_post_id, $recent)) !== false) {
		unset($recent[$key]);
	}
	// add to front
	array_unshift($recent, $this_post_id);
	// keep max 6
	$recent = array_slice($recent, 0, 6);
	// save for 30 days
	setcookie('escortwp_recently_viewed', implode(',', $recent), time() + 86400 * 30, '/');
}

if (current_user_can('level_10')) {
	if (isset($_POST['action']) && $_POST['action'] == 'escortupgrade') {
		if (isset($_POST['delpremium'])) {
			update_post_meta(get_the_ID(), 'premium', "0");
			delete_post_meta(get_the_ID(), 'premium_expire');
			delete_post_meta(get_the_ID(), 'premium_renew');
			delete_post_meta(get_the_ID(), 'premium_since');

			$body_email = __('Hello', 'escortwp') . '<br /><br />
' . __('The PREMIUM status has been remove from your profile.', 'escortwp') . '<br /><br />
' . __('You can view your profile here', 'escortwp') . ':<br />
<a href="' . get_permalink(get_the_ID()) . '">' . get_permalink(get_the_ID()) . '</a>';
		}
		if (isset($_POST['premium'])) {
			update_post_meta(get_the_ID(), "premium", "1");
			update_post_meta(get_the_ID(), "premium_since", time());
			if ($_POST['premiumduration']) {
				$expiration = strtotime("+" . $payment_duration_a[$_POST['premiumduration']][2]);
				if (get_post_meta(get_the_ID(), "premium_expire", true)) {
					$available_time = get_post_meta(get_the_ID(), 'premium_expire', true);
					if ($available_time && $available_time > time()) {
						$expiration = $expiration + ($available_time - time());
					}
				}
				update_post_meta(get_the_ID(), 'premium_expire', $expiration);
			} else {
				delete_post_meta(get_the_ID(), 'premium_expire');
				delete_post_meta(get_the_ID(), 'premium_renew');
			}

			$body_email = __('Hello', 'escortwp') . '<br /><br />
' . __('Your profile has been upgraded to PREMIUM.', 'escortwp') . '<br /><br />
' . __('You can view your profile here', 'escortwp') . ':<br />
<a href="' . get_permalink(get_the_ID()) . '">' . get_permalink(get_the_ID()) . '</a>';
		}

		if (isset($_POST['delfeatured'])) {
			update_post_meta(get_the_ID(), "featured", "0");
			delete_post_meta(get_the_ID(), 'featured_expire');
			delete_post_meta(get_the_ID(), 'featured_renew');

			$body_email = __('Hello', 'escortwp') . '<br /><br />
' . __('The FEATURED status has been removed from your profile.', 'escortwp') . '<br /><br />
' . __('You can view your profile here', 'escortwp') . ':<br />
<a href="' . get_permalink(get_the_ID()) . '">' . get_permalink(get_the_ID()) . '</a>';
		}
		if (isset($_POST['featured'])) {
			$featured = get_post_meta(get_the_ID(), "featured", true);
			if (!$featured || $featured == "0") {
				update_post_meta(get_the_ID(), "featured", "1");
			}
			if ($_POST['featuredduration']) {
				$expiration = strtotime("+" . $payment_duration_a[$_POST['featuredduration']][2]);
				if (get_post_meta(get_the_ID(), "featured_expire", true)) {
					$available_time = get_post_meta(get_the_ID(), 'featured_expire', true);
					if ($available_time && $available_time > time()) {
						$expiration = $expiration + ($available_time - time());
					}
				}
				update_post_meta(get_the_ID(), 'featured_expire', $expiration);
			} else {
				delete_post_meta(get_the_ID(), 'featured_expire');
				delete_post_meta(get_the_ID(), 'featured_renew');
			}

			$body_email = __('Hello', 'escortwp') . '<br /><br />
' . __('Your profile has been upgraded to a FEATURED profile.', 'escortwp') . '<br /><br />
' . __('You can view your profile here', 'escortwp') . ':<br />
<a href="' . get_permalink(get_the_ID()) . '">' . get_permalink(get_the_ID()) . '</a>';
		}

		if (isset($_POST['delexpiration'])) {
			delete_post_meta(get_the_ID(), 'escort_expire');
			delete_post_meta(get_the_ID(), 'escort_renew');

			$plan_name = get_option("escortid" . $profile_author_id) == $taxonomy_agency_url ? "agescortreg" : 'indescreg';
			if (payment_plans($plan_name, 'price')) {
				update_post_meta(get_the_ID(), 'needs_payment', "1");
				wp_update_post(array('ID' => get_the_ID(), 'post_status' => 'private'));
			}
		}
		if (isset($_POST['expirationperiod'])) {
			if ($_POST['profileduration']) {
				$expiration = strtotime("+" . $payment_duration_a[$_POST['profileduration']][2]);
				if (get_post_meta(get_the_ID(), "escort_expire", true)) {
					$available_time = get_post_meta(get_the_ID(), 'escort_expire', true);
					if ($available_time && $available_time > time()) {
						$expiration = $expiration + ($available_time - time());
					}
				}
				update_post_meta(get_the_ID(), 'escort_expire', $expiration);
			} else {
				delete_post_meta(get_the_ID(), 'escort_expire');
				delete_post_meta(get_the_ID(), 'escort_renew');
			}
		}

		if (isset($_POST['verified'])) {
			$verified = get_post_meta(get_the_ID(), "verified", true);
			if ($verified == "1") {
				$verified = "0";
				$first_text = __('verified', 'escortwp');
				$second_text = __('NOT verified', 'escortwp');
			} else {
				$verified = "1";
				$first_text = __('NOT verified', 'escortwp');
				$second_text = __('VERIFIED', 'escortwp');
			}
			update_post_meta(get_the_ID(), "verified", $verified);

			$body_email = __('Hello', 'escortwp') . '<br /><br />
' . sprintf(__('The status of you profile has changed from %s to %s on %s', 'escortwp'), "<b>" . $first_text . "</b>", "<b>" . $second_text . "</b>", get_option("email_sitename")) . '<br /><br />
' . __('You can view your profile here', 'escortwp') . ':<br />
<a href="' . get_permalink(get_the_ID()) . '">' . get_permalink(get_the_ID()) . '</a>';
		}

		if ($body_email && get_option('ifemail9') == "1") {
			dolce_email("", "", get_the_author_meta('email', $profile_author_id), __('Profile status changed on', 'escortwp') . " " . get_option("email_sitename"), $body_email);
		}
		wp_redirect(get_permalink(get_the_ID()));
		exit();
	} // escort upgrade

	if (isset($_POST['action']) && $_POST['action'] == 'adminnote') {
		$adminnote = wp_strip_all_tags($_POST['adminnote']);
		update_post_meta(get_the_ID(), "adminnote", $adminnote);
		wp_redirect(get_permalink(get_the_ID()));
		exit();
	} // adminnote

	if (isset($_POST['action']) && $_POST['action'] == 'activateprivateprofile') {
		$privprof = array('ID' => get_the_ID(), 'post_status' => 'publish');
		delete_post_meta(get_the_ID(), "notactive");
		wp_update_post($privprof);
		wp_redirect(get_permalink(get_the_ID()));
		exit;
	} // activate private escort

	if (isset($_POST['action']) && $_POST['action'] == 'activateunpaidprofile') {
		if ($_POST['profileduration']) {
			$expiration = strtotime("+" . $payment_duration_a[$_POST['profileduration']][2]);
			if (get_post_meta(get_the_ID(), "escort_expire", true)) {
				$available_time = get_post_meta(get_the_ID(), 'escort_expire', true);
				if ($available_time && $available_time > time()) {
					$expiration = $expiration + ($available_time - time());
				}
			}
			update_post_meta(get_the_ID(), 'escort_expire', $expiration);
		}

		$unpaidprof = array('ID' => get_the_ID(), 'post_status' => 'publish');
		delete_post_meta(get_the_ID(), "needs_payment");
		wp_update_post($unpaidprof);
		wp_redirect(get_permalink(get_the_ID()));
		exit;
	} // activate unpaid profile
} // if admin

if ($userstatus == "member" || current_user_can('level_10')) {
	if (isset($_POST['action']) && $_POST['action'] == 'addreview') {
		$rateescort = (int) $_POST['rateescort'];
		if ($rateescort < 1 || $rateescort > 6) {
			$err .= sprintf(esc_html__('The %s rating is wrong. Please select again.', 'escortwp'), $taxonomy_profile_name) . "<br />";
			unset($rateescort);
		}

		$reviewtext = substr(stripslashes(wp_kses($_POST['reviewtext'], array())), 0, 5000);
		if (!$reviewtext) {
			$err .= __('You didn\'t write a review', 'escortwp') . "<br />";
		}

		if (!$err) {
			//add review to database
			if (get_option("manactivesc") == "1") {
				$reviewstatus = "draft";
			} else {
				$reviewstatus = "publish";
			}
			$reviews_cat_id = term_exists('Reviews', "category");
			if (!$reviews_cat_id) {
				$arg = array('description' => 'Reviews');
				wp_insert_term('Reviews', "category", $arg);
				$reviews_cat_id = term_exists('Reviews', "category");
			}
			$reviews_cat_id = $reviews_cat_id['term_id'];
			$add_review = array(
				'post_title' => __('Review for', 'escortwp') . " " . get_the_title(),
				'post_content' => $reviewtext,
				'post_status' => $reviewstatus,
				'post_author' => $userid,
				'post_category' => array($reviews_cat_id),
				'post_type' => 'review',
				'ping_status' => 'closed'
			);
			$add_review_id = wp_insert_post($add_review);
			update_post_meta($add_review_id, "rateescort", $rateescort);
			update_post_meta($add_review_id, "escortid", get_the_ID());
			update_post_meta($add_review_id, "reviewfor", "profile");

			$reviewadminurl = admin_url('post.php') . '?post=' . $add_review_id . '&action=edit';
			if (get_option("manactivesc") == "1") {
				$new_review_email_title = __('A new review is waiting for approval on', 'escortwp') . " " . get_option("email_sitename");
			} else {
				$new_review_email_title = sprintf(esc_html__('Someone wrote a %s review on', 'escortwp'), $taxonomy_profile_name) . ' ' . get_option("email_sitename");
			}
			$body = __('Hello', 'escortwp') . ',<br />
' . sprintf(esc_html__('Someone wrote a %s review on.', 'escortwp'), $taxonomy_profile_name) . ' ' . get_option("email_sitename") . ':<br /><br />
' . __('Read/Edit the review here', 'escortwp') . ':<br />
<a href="' . $reviewadminurl . '">' . $reviewadminurl . '</a><br />' . __('(to activate the review simply click te button "Publish")', 'escortwp');
			if (get_option("ifemail6") == "1" || get_option("manactivag") == "1") {
				dolce_email(null, null, get_bloginfo("admin_email"), $new_review_email_title, $body);
			}

			if (get_option("permalink_structure")) {
				wp_redirect(get_permalink(get_the_id()) . "?postreview=ok");
				exit();
			} else {
				wp_redirect(get_permalink(get_the_id()) . "&postreview=ok");
				exit();
			}

		}
	} // add review
} // if user status member

// delete an escort account
if (isset($_POST['action']) && $_POST['action'] == 'deleteescort' && ($profile_author_id == $userid || current_user_can('level_10'))) {
	if (!get_post_meta(get_the_ID(), "independent", true)) {
		$agency_id = get_option("agencypostid" . $profile_author_id);
	}

	delete_profile(get_the_ID()); // delete escort and everything related to the profile

	if ($agency_id) {
		wp_redirect(get_permalink($agency_id));
		exit();
	} else {
		wp_redirect(get_bloginfo("url"));
		exit();
	}
} // if admin


// set profile to private
if (isset($_POST['action']) && $_POST['action'] == 'settoprivate') {
	$new_post_status = get_post_status(get_the_ID()) == "publish" ? "private" : "publish";

	if (current_user_can('level_10')) {
		if (get_post_status(get_the_ID()) == "publish") {
			update_post_meta(get_the_ID(), 'notactive', '1');
		} else {
			delete_post_meta(get_the_ID(), "notactive");
		}
		wp_update_post(array('ID' => get_the_ID(), 'post_status' => $new_post_status));
	}

	if ($profile_author_id == $userid && !get_post_meta(get_the_ID(), 'notactive', true) && !get_post_meta(get_the_ID(), 'needs_payment', true)) {
		wp_update_post(array('ID' => get_the_ID(), 'post_status' => $new_post_status));
	}

	wp_redirect(get_permalink(get_the_ID()));
	die();
}


//if the agency wants to edit the profile information process the data below
if (isset($_POST['action']) && $_POST['action'] == 'register') {
	if ($profile_author_id == $userid && $userstatus == $taxonomy_agency_url || current_user_can('level_10')) {
		$agencyid = $userid;
		$single_page = "yes";
		$escort_post_id = get_the_ID();
		include(get_template_directory() . '/register-independent-personal-info-process.php');
	} // if the escort was added by this user and if the user is an agency
} else {
	$agencyid = $userid;
	$escort_post_id = get_the_ID();
	$single_page = "yes";
	$escort = get_post($escort_post_id);

	$aboutyou = wpautop(do_shortcode($escort->post_content));
	$yourname = $escort->post_title;

	$phone = get_post_meta($escort_post_id, "phone", true);
	$phone_num = get_post_meta($escort_post_id, "phone", true);
	$phone_available_on = get_post_meta($escort_post_id, "phone_available_on", true);
	$escortemail = get_post_meta($escort_post_id, "escortemail", true);
	$website = get_post_meta($escort_post_id, "website", true);
	$instagram = get_post_meta($escort_post_id, "instagram", true);
	$snapchat = get_post_meta($escort_post_id, "snapchat", true);
	$twitter = get_post_meta($escort_post_id, "twitter", true);
	$facebook = get_post_meta($escort_post_id, "facebook", true);


	$city_data = wp_get_post_terms(get_the_ID(), $taxonomy_location_url);
	if ($city_data && !is_wp_error($city_data)) {
		if (get_option('locationdropdown') == "1") {
			$city = $city_data[0]->term_id;
		} else {
			$city = get_term($city_data[0]->term_id, $taxonomy_location_url);
			$city = $city_data[0]->name;
		}

		$state_data = get_term($city_data[0]->parent, $taxonomy_location_url);
		if ($state_data && !is_wp_error($state_data)) {
			if (get_option('locationdropdown') == "1") {
				$state = $state_data->term_id;
			} else {
				$state = get_term($state_data->term_id, $taxonomy_location_url);
				$state = $state_data->name;
			}
			$country_data = get_term($state_data->parent, $taxonomy_location_url);
			if (!is_wp_error($country_data)) {
				$country = $country_data->term_id;
			} else {
				$country = $state_data->term_id;
				unset($state);
			}
		}
	}

	// City name for sticky bar display (independent of $city overwrites)
	$city_name_display = '';
	if ($city_data && !is_wp_error($city_data)) {
		$city_name_display = $city_data[0]->name;
	}

	$gender = get_post_meta($escort_post_id, "gender", true);
	$birthday = get_post_meta($escort_post_id, "birthday", true);
	$age = floor((time() - strtotime($birthday)) / 31556926);
	$birthday_expaned = explode("-", $birthday);
	$dateyear = $birthday_expaned[0];
	$datemonth = $birthday_expaned[1];
	$dateday = $birthday_expaned[2];

	$ethnicity = get_post_meta($escort_post_id, "ethnicity", true);
	$haircolor = get_post_meta($escort_post_id, "haircolor", true);
	$hairlength = get_post_meta($escort_post_id, "hairlength", true);
	$bustsize = get_post_meta($escort_post_id, "bustsize", true);
	$height = get_post_meta($escort_post_id, "height", true);
	$height2 = get_post_meta($escort_post_id, "height2", true);
	$weight = get_post_meta($escort_post_id, "weight", true);
	$build = get_post_meta($escort_post_id, "build", true);
	$looks = get_post_meta($escort_post_id, "looks", true);
	$smoker = get_post_meta($escort_post_id, "smoker", true);
	$availability = get_post_meta($escort_post_id, "availability", true);
	$language1 = get_post_meta($escort_post_id, "language1", true);
	$language1level = get_post_meta($escort_post_id, "language1level", true);
	$language2 = get_post_meta($escort_post_id, "language2", true);
	$language2level = get_post_meta($escort_post_id, "language2level", true);
	$language3 = get_post_meta($escort_post_id, "language3", true);
	$language3level = get_post_meta($escort_post_id, "language3level", true);
	$currency = get_post_meta($escort_post_id, "currency", true);

	$rate30min_incall = get_post_meta($escort_post_id, "rate30min_incall", true);
	$rate1h_incall = get_post_meta($escort_post_id, "rate1h_incall", true);
	$rate2h_incall = get_post_meta($escort_post_id, "rate2h_incall", true);
	$rate3h_incall = get_post_meta($escort_post_id, "rate3h_incall", true);
	$rate6h_incall = get_post_meta($escort_post_id, "rate6h_incall", true);
	$rate12h_incall = get_post_meta($escort_post_id, "rate12h_incall", true);
	$rate24h_incall = get_post_meta($escort_post_id, "rate24h_incall", true);

	$rate30min_outcall = get_post_meta($escort_post_id, "rate30min_outcall", true);
	$rate1h_outcall = get_post_meta($escort_post_id, "rate1h_outcall", true);
	$rate2h_outcall = get_post_meta($escort_post_id, "rate2h_outcall", true);
	$rate3h_outcall = get_post_meta($escort_post_id, "rate3h_outcall", true);
	$rate6h_outcall = get_post_meta($escort_post_id, "rate6h_outcall", true);
	$rate12h_outcall = get_post_meta($escort_post_id, "rate12h_outcall", true);
	$rate24h_outcall = get_post_meta($escort_post_id, "rate24h_outcall", true);

	$services = get_post_meta($escort_post_id, "services", true);
	$extraservices = get_post_meta($escort_post_id, "extraservices", true);
	$adminnote = get_post_meta($escort_post_id, "adminnote", true);
	$education = get_post_meta(get_the_ID(), 'education', true);
	$sports = get_post_meta(get_the_ID(), 'sports', true);
	$hobbies = get_post_meta(get_the_ID(), 'hobbies', true);
	$zodiacsign = get_post_meta(get_the_ID(), 'zodiacsign', true);
	$sexualorientation = get_post_meta(get_the_ID(), 'sexualorientation', true);
	$occupation = get_post_meta(get_the_ID(), 'occupation', true);
}

if ($profile_author_id == $userid && $userstatus == $taxonomy_agency_url || current_user_can('level_10')) {
	// if the agency wants to add a tour to the escort then process the data below
	if (isset($_POST['action']) && ($_POST['action'] == 'addtour' || $_POST['action'] == 'edittour')) {
		$is_escort_page = "yes";
		$escort_post_id_for_tours = get_the_ID();
		include(get_template_directory() . '/register-independent-manage-my-tours-process-data.php');
		if ($ok) {
			wp_redirect(get_permalink($escort_profile_id) . "?add_tour=ok#tours");
			exit;
		}
	}
} // if the escort was added by this user and if the user is an agency


if (isset($_POST['action']) && $_POST['action'] == "contactform") {
	if ($_POST['emails']) {
		$err .= ".";
	}

	if (get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && get_option("recaptcha5") && !is_user_logged_in()) {
		$err .= verify_recaptcha();
	}

	if (is_user_logged_in()) {
		$contactformname = $current_user->display_name;
		$contactformemail = $current_user->user_email;
	} else {
		$contactformname = get_option("email_sitename");
		$contactformemail = $_POST['contactformemail'];
		if ($contactformemail) {
			if (!is_email($contactformemail)) {
				$err .= __('Your email address seems to be wrong', 'escortwp') . "<br />";
			}
		} else {
			$err .= __('Your email is missing', 'escortwp') . "<br />";
		}
	}
	$contactformmess = substr(sanitize_textarea_field($_POST['contactformmess']), 0, 5000);
	if (!$contactformmess) {
		$err .= __('You need to write a message', 'escortwp') . "<br />";
	}

	if (!$err) {
		$body = __('Hello', 'escortwp') . ' ' . get_the_author_meta('display_name', $profile_author_id) . '<br /><br />
' . __('Someone sent you a message from', 'escortwp') . ' ' . get_option("email_sitename") . ':<br />
<a href="' . get_permalink(get_the_ID()) . '">' . get_permalink(get_the_ID()) . '</a><br /><br />
' . __('Sender information', 'escortwp') . ':<br />
' . __('name', 'escortwp') . ': <b>' . $contactformname . '</b><br />
' . __('email', 'escortwp') . ': <b>' . $contactformemail . '</b><br />
' . __('message', 'escortwp') . ':<br />' . $contactformmess . '<br /><br />' . __('You can send a message back to this person by replying to this email.', 'escortwp');
		dolce_email($contactformname, $contactformemail, get_the_author_meta('user_email', $profile_author_id), __('Message from', 'escortwp') . " " . get_option("email_sitename"), $body);
		unset($contactformname, $contactformemail, $contactformmess, $body);
		$ok = __('Message sent', 'escortwp');
	}
}

$current_tour = get_user_current_tour(get_the_ID());

get_header(); ?>

<div class="contentwrapper">
	<div class="body">
		<div class="bodybox profile-page">

			<?php
			if (function_exists('yoast_breadcrumb')) {
				echo '<nav class="profile-breadcrumbs" aria-label="' . esc_attr__('Breadcrumb', 'escortwp') . '">';
				yoast_breadcrumb('<p id="breadcrumbs">', '</p>');
				echo '</nav>';
			}
			?>

			<?php
			if (isset($_GET['unpaid_tour']) && $_GET['unpaid_tour'] && $profile_author_id == $userid) {
				$unpaid_tour = get_post((int) $_GET['unpaid_tour']);
				if ($unpaid_tour && get_post_meta($unpaid_tour->ID, 'needs_payment', true)) {
					echo '<div class="err rad25">';
					echo '<div class="clear10"></div>';
					printf(__('%s has been added, but it\'s not visible in our website yet.', 'escortwp'), $unpaid_tour->post_title);
					if (payment_plans('tours', 'price')) {
						echo "<br />" . __('In order for the tour to be activated you must pay', 'escortwp') . " " . format_price('tours', "small") . "<br />" . "\n";
						echo '<div class="clear20"></div>';
						echo generate_payment_buttons('tours', (int) $_GET['unpaid_tour'], __('Activate tour', 'escortwp'));
						echo '<div class="clear5"></div>';
						echo "<small>" . format_price('tours') . "</small>";
					}
					echo '<div class="clear10"></div>';
					echo '</div>';
				}
			}
			?>
			<?php if (isset($ok) && $ok && $_POST['action'] == 'edittour') {
				echo "<div class=\"ok rad25\">$ok</div>";
			} ?>

			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					//add or remove from favorites
					$('.favbutton').on('click', function () {
						var escortid = $(this).attr('id');
						$('.favbutton').toggle();
						$.ajax({
							type: "GET",
							url: "<?php bloginfo('template_url'); ?>/ajax/add-remove-favorites.php",
							data: "id=" + escortid
						});
					});

					function openReviewForm(score) {
						var $form = $('.addreviewform');
						if (typeof score !== 'undefined' && score !== null && score !== '') {
							$form.find('input[name="rateescort"][value="' + score + '"]').prop('checked', true).trigger('change');
							$('.profile-review-entry').attr('data-selected-score', score);
							$('.profile-review-entry__star').each(function () {
								var value = parseInt($(this).attr('data-review-score'), 10);
								var isSelected = value <= score;
								$(this)
									.toggleClass('is-selected', isSelected)
									.attr('aria-pressed', isSelected ? 'true' : 'false');
							});
							var helperLabel = $('.profile-review-entry').attr('data-selected-template') || '';
							if (helperLabel) {
								$('.profile-review-entry__helper').text(helperLabel.replace('%s', score));
							}
						}
						$form.stop(true, true).slideDown("slow");
						$('.addreview').slideUp("slow");
						$('html,body').animate({ scrollTop: $form.offset().top }, { duration: 'slow', easing: 'swing' });
					}

					$('.addreview-button').on('click', function () {
						openReviewForm();
					});

					$('.profile-review-entry__star').on('click', function (event) {
						event.preventDefault();
						var score = parseInt($(this).attr('data-review-score'), 10);
						if (!isNaN(score)) {
							openReviewForm(score);
						}
					});

					$('.profile-review-entry__star').on('mouseenter focus', function () {
						var score = parseInt($(this).attr('data-review-score'), 10);
						if (isNaN(score)) {
							return;
						}
						$('.profile-review-entry__star').each(function () {
							var value = parseInt($(this).attr('data-review-score'), 10);
							$(this).toggleClass('is-previewed', value <= score);
						});
					});

					$('.profile-review-entry').on('mouseleave', function () {
						var selectedScore = parseInt($(this).attr('data-selected-score'), 10) || 0;
						$('.profile-review-entry__star').each(function () {
							var value = parseInt($(this).attr('data-review-score'), 10);
							var isSelected = value <= selectedScore;
							$(this)
								.removeClass('is-previewed')
								.toggleClass('is-selected', isSelected)
								.attr('aria-pressed', isSelected ? 'true' : 'false');
						});
					});
					$('.profile-review-entry__stars').on('focusout', function () {
						var $entry = $(this).closest('.profile-review-entry');
						window.setTimeout(function () {
							if ($entry.find(':focus').length) {
								return;
							}
							var selectedScore = parseInt($entry.attr('data-selected-score'), 10) || 0;
							$entry.find('.profile-review-entry__star').each(function () {
								var value = parseInt($(this).attr('data-review-score'), 10);
								var isSelected = value <= selectedScore;
								$(this)
									.removeClass('is-previewed')
									.toggleClass('is-selected', isSelected)
									.attr('aria-pressed', isSelected ? 'true' : 'false');
							});
						}, 0);
					});

					$('.profile-rates-tab').on('click', function () {
						var $tab = $(this);
						var mode = $tab.attr('data-rate-mode');
						var $rates = $tab.closest('.profile-rates');
						if (!mode || !$rates.length) {
							return;
						}
						$rates.find('.profile-rates-tab')
							.removeClass('is-active')
							.attr('aria-selected', 'false')
							.attr('tabindex', '-1');
						$tab
							.addClass('is-active')
							.attr('aria-selected', 'true')
							.attr('tabindex', '0');
						$rates.find('.profile-rates-panel')
							.removeClass('is-active')
							.attr('aria-hidden', 'true')
							.prop('hidden', true);
						$rates.find('.profile-rates-panel[data-rate-mode="' + mode + '"]')
							.addClass('is-active')
							.attr('aria-hidden', 'false')
							.prop('hidden', false);
					});

					$('.profile-rates-tab').on('keydown', function (event) {
						if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') {
							return;
						}
						event.preventDefault();
						var $tabs = $(this).closest('.profile-rates-tabs').find('.profile-rates-tab');
						var currentIndex = $tabs.index(this);
						var nextIndex = event.key === 'ArrowRight' ? currentIndex + 1 : currentIndex - 1;
						if (nextIndex < 0) {
							nextIndex = $tabs.length - 1;
						}
						if (nextIndex >= $tabs.length) {
							nextIndex = 0;
						}
						$tabs.eq(nextIndex).trigger('focus').trigger('click');
					});
					if (window.location.hash == "#addreview") {
						openReviewForm();
					}
					$('.addreviewform .closebtn').on('click', function () {
						$('.addreviewform, .addreview').slideToggle("slow");
					});

					$('.addreviewform input[name="rateescort"]').on('change', function () {
						var selectedScore = parseInt($(this).val(), 10) || 0;
						var $entry = $('.profile-review-entry');
						var selectedTemplate = $entry.attr('data-selected-template') || '';
						var ratingLabel = $(this).attr('data-rating-label') || '';
						var ratingHint = $(this).attr('data-rating-hint') || '';
						var $selection = $('.profile-review-form__selection');
						$entry.attr('data-selected-score', selectedScore);
						$entry.find('.profile-review-entry__star').each(function () {
							var value = parseInt($(this).attr('data-review-score'), 10);
							var isSelected = value <= selectedScore;
							$(this)
								.removeClass('is-previewed')
								.toggleClass('is-selected', isSelected)
								.attr('aria-pressed', isSelected ? 'true' : 'false');
						});
						if (selectedTemplate) {
							$entry.find('.profile-review-entry__helper').text(selectedTemplate.replace('%s', selectedScore));
						}
						if ($selection.length) {
							var defaultLabel = $selection.attr('data-default-label') || '';
							var defaultHint = $selection.attr('data-default-hint') || '';
							$selection.attr('data-selected-score', selectedScore);
							$selection.find('.profile-review-form__selection-value').text(
								selectedScore > 0 && ratingLabel ? selectedScore + ' · ' + ratingLabel : defaultLabel
							);
							$selection.find('.profile-review-form__selection-hint').text(
								selectedScore > 0 && ratingHint ? ratingHint : defaultHint
							);
						}
					});

					var $selectedReviewInput = $('.addreviewform input[name="rateescort"]:checked').first();
					if ($selectedReviewInput.length) {
						$selectedReviewInput.trigger('change');
					}

					count_review_text('#reviewtext');
					$("#reviewtext").keyup(function () {
						count_review_text($(this));
					});
					function count_review_text(t) {
						if (!$(t).length) {
							return false;
						}
						var charlimit = 1000;
						var box = $(t).val();
						var main = box.length * 100;
						var value = (main / charlimit);
						var count = charlimit - box.length;
						var boxremove = box.substring(0, charlimit);
						var ourtextarea = $(t);

						$('.charcount').show('slow');
						if (box.length <= charlimit) {
							$('#count').html(count);
							$("#reviewtext")
							$('#bar').animate({
								"width": value + '%',
							}, 1);
						} else {
							$('#reviewtext').val(boxremove);
							ourtextarea.scrollTop(
								ourtextarea[0].scrollHeight - ourtextarea.height()
							);
						}
						return false;
					}

				});
			</script>
			<?php
			// check if the user has any photos uploaded
			// create an array with all the photos to use later
			$photos = get_children(array('post_parent' => get_the_ID(), 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID'));
			$photos_left = get_option('maximgupload') - count($photos);
			$photos_left = (int) $photos_left;

			$videos = get_children(array('post_parent' => get_the_ID(), 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'video', 'order' => 'ASC', 'orderby' => 'menu_order ID'));
			$videos_left = get_option('maxvideoupload') - count($videos);

			// Hero image URL (simplified — no expensive metadata regeneration)
			$hero_main_image_id = get_post_meta(get_the_ID(), "main_image_id", true);
			if ($hero_main_image_id < 1 || !get_post($hero_main_image_id)) {
				$firstphoto = !empty($photos) ? reset($photos) : null;
				$hero_main_image_id = $firstphoto ? $firstphoto->ID : 0;
			}
			$hero_image_url = $hero_main_image_id
				? wp_get_attachment_image_url((int) $hero_main_image_id, 'main-image-thumb')
				: '';
			if (!$hero_image_url) {
				$hero_image_url = get_stylesheet_directory_uri() . '/i/no-image.png';
			}

			$show_phone_actions = true;
			if (payment_plans('vip', 'extra', 'hide_contact_info') && !is_user_logged_in()) {
				$show_phone_actions = false;
			} elseif (payment_plans('vip', 'extra', 'hide_contact_info') && !get_user_meta($userid, "vip", true) && !current_user_can('level_10') && $profile_author_id != $userid) {
				$show_phone_actions = false;
			}
			$phone_digits = $phone ? preg_replace('/[^0-9]/', '', $phone) : '';
			$has_call_action = $show_phone_actions && !empty($phone);
			$has_whatsapp_action = $show_phone_actions && !empty($phone_digits) && is_array($phone_available_on) && in_array('1', $phone_available_on);
			$has_viber_action = $show_phone_actions && !empty($phone_digits) && is_array($phone_available_on) && in_array('2', $phone_available_on);
			$chat_link = wp_chat_sso_link((int) $profile_author_id);
			$has_chat_action = !empty($chat_link);
			$has_contact_actions = $has_call_action || $has_whatsapp_action || $has_viber_action || $has_chat_action;
			$mobile_contact_panel_id = 'profile-mobile-contact-panel-' . (int) get_the_ID();

			if ($profile_author_id == $userid || current_user_can('level_10')) {
				include(get_template_directory() . '/register-agency-manage-escorts-option-buttons.php');
			}
			if (current_user_can('level_10')) {
				$admin_edit_post_url = get_admin_url('', 'post.php?post=' . (int) get_the_ID() . '&action=edit');
				?>
				<section class="profile-admin-quick-actions profile-admin-quick-actions--sticky"
					aria-label="<?php esc_attr_e('Admin quick actions', 'escortwp'); ?>">
					<div class="profile-admin-quick-actions__header">
						<h4 class="profile-admin-quick-actions__title"><?php _e('Admin Quick Actions', 'escortwp'); ?></h4>
					</div>
					<div class="profile-admin-quick-actions__list" role="toolbar"
						aria-label="<?php esc_attr_e('Profile editing actions', 'escortwp'); ?>">
						<button type="button" class="profile-admin-quick-actions__btn" data-admin-profile-action="editprofile">
							<span class="icon icon-pencil"></span>
							<span><?php _e('Edit Profile', 'escortwp'); ?></span>
						</button>
						<button type="button" class="profile-admin-quick-actions__btn" data-admin-profile-action="addtours">
							<span class="icon icon-airplane"></span>
							<span><?php _e('Add Tours', 'escortwp'); ?></span>
						</button>
						<button type="button" class="profile-admin-quick-actions__btn"
							data-admin-profile-action="verified_status">
							<span class="icon icon-check"></span>
							<span><?php _e('Verified status', 'escortwp'); ?></span>
						</button>
						<button type="button" class="profile-admin-quick-actions__btn" data-admin-profile-action="addanote">
							<span class="icon icon-doc-text"></span>
							<span><?php _e('Add a note', 'escortwp'); ?></span>
						</button>
						<button type="button" class="profile-admin-quick-actions__btn profile-admin-quick-actions__btn--danger"
							data-admin-profile-action="delete">
							<span class="icon icon-cancel"></span>
							<span><?php _e('Delete Profile', 'escortwp'); ?></span>
						</button>
						<a class="profile-admin-quick-actions__btn profile-admin-quick-actions__btn--secondary"
							href="<?php echo esc_url($admin_edit_post_url); ?>">
							<span class="icon icon-w"></span>
							<span><?php _e('Edit in WordPress', 'escortwp'); ?></span>
						</a>
					</div>
				</section>
				<?php
			}
			?>
			<!-- Profile Hero Section — Cover + Avatar Layout -->
			<section class="profile-hero profile-hero--cover" aria-label="Profile hero">
				<!-- Decorative cover band (no user photo) -->
				<div class="profile-hero__cover">
					<div class="profile-hero__cover-pattern"></div>
					<div class="profile-hero__cover-gradient"></div>
				</div>

				<!-- Profile info cluster (overlaps cover bottom) -->
				<div class="profile-hero__info">
					<!-- Avatar -->
					<div class="profile-hero__avatar">
						<img src="<?php echo esc_url($hero_image_url); ?>" alt="<?php the_title_attribute(); ?>"
							class="profile-hero__avatar-img" loading="eager" />
					</div>

					<!-- Name + badges + location + stats -->
					<div class="profile-hero__details">
						<div class="profile-hero__name-row">
							<h3 class="profile-title" title="<?php the_title_attribute(); ?>" itemprop="name">
								<?php the_title(); ?>
							</h3>
							<div class="girlsinglelabels">
								<?php
								$premium = get_post_meta(get_the_ID(), "premium", true);
								if ($premium == "1") {
									echo '<span class="profile-badge orangebutton">' . __('PREMIUM', 'escortwp') . '</span>';
								}

								$featured = get_post_meta(get_the_ID(), "featured", true);
								if ($featured == "1") {
									echo '<span class="profile-badge pinkdegrade">' . strtoupper(__('VIP', 'escortwp')) . '</span>';
								}

								$verified = get_post_meta(get_the_ID(), "verified", true);
								if ($verified == "1") {
									echo '<span class="profile-badge greendegrade">' . __('VERIFIED', 'escortwp') . '</span>';
								}

								$daysago = date("Y-m-d H:i:s", strtotime("-" . get_option('newlabelperiod') . " days"));
								if (get_the_time('Y-m-d H:i:s') > $daysago) {
									echo '<span class="profile-badge pinkbutton">' . __('NEW', 'escortwp') . '</span>';
								}

								if (get_post_status(get_the_ID()) == "private") {
									echo '<span class="profile-badge profile-badge--private redbutton">' . strtoupper(__('Private', 'escortwp')) . '</span>';
								}
								?>
							</div> <!-- girlsinglelabels -->
							<?= show_online_label_html($profile_author_id) ?>
						</div><!-- /name-row -->

						<div class="profile-hero__meta">
							<?php if ($city_name_display): ?>
								<span class="profile-hero__location">
									<span class="icon icon-location"></span>
									<?php echo esc_html($city_name_display); ?>
								</span>
							<?php endif; ?>
							<div class="profile-header-name-info">
								<?php
								if ($height) {
									if (get_option("heightscale") == "imperial" && $height2 > 0) {
										echo '<div class="section-box"><span class="valuecolumn">' . $height2 . '</span><b>' . (get_option("heightscale") == "imperial" ? "in" : "") . '</b></div>';
									}
									echo '<div class="section-box"><span class="valuecolumn">' . $height . '</span><b>' . (get_option("heightscale") == "imperial" ? "ft" : "cm") . '</b></div>';
								}
								if ($weight) {
									echo '<div class="section-box"><span class="valuecolumn">' . $weight . '</span><b>' . (get_option("heightscale") == "imperial" ? "lb" : "kg") . '</b></div>';
								}
								?>
								<div class="section-box"><span
										class="valuecolumn"><?= $age ?></span><b><?= __('years', 'escortwp') ?></b>
								</div>
							</div>
						</div><!-- /meta -->
						</div><!-- /details -->

						<!-- CTA buttons (right-aligned) -->
						<?php if ($has_contact_actions): ?>
							<div class="profile-hero__cta">
								<?php if ($has_call_action): ?>
									<a href="tel:<?php echo esc_attr($phone); ?>" itemprop="telephone"
										class="profile-hero__action profile-hero__action--call">
										<span class="icon icon-phone"></span>
										<span><?php echo esc_html($phone); ?></span>
									</a>
								<?php endif; ?>

								<?php if ($has_whatsapp_action): ?>
									<a href="https://wa.me/<?php echo esc_attr($phone_digits); ?>?text=<?php echo rawurlencode(sprintf(__('Hi, I saw your profile on %s', 'escortwp'), get_site_url())); ?>"
										class="profile-hero__action profile-hero__action--wa" rel="noopener">
										<span class="icon icon-whatsapp"></span>
										<span><?php esc_html_e('WhatsApp', 'escortwp'); ?></span>
									</a>
								<?php endif; ?>

								<?php if ($has_viber_action): ?>
									<a href="viber://chat?number=<?php echo esc_attr($phone_digits); ?>"
										class="profile-hero__action profile-hero__action--viber" rel="noopener">
										<span class="icon icon-viber"></span>
										<span><?php esc_html_e('Viber', 'escortwp'); ?></span>
									</a>
								<?php endif; ?>

								<?php if ($has_chat_action): ?>
									<a href="<?php echo esc_url($chat_link); ?>" target="_blank" rel="noopener"
										class="profile-hero__action profile-hero__action--chat">
										<svg class="chat-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
											stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16"
											height="16">
											<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
										</svg>
										<span><?php esc_html_e('Chat', 'escortwp'); ?></span>
									</a>
								<?php endif; ?>
							</div><!-- /cta -->
						<?php endif; ?>
					</div><!-- /info -->
				</section> <!-- /profile-hero -->

			<?php
			$is_owner_view = is_user_logged_in()
				&& $userstatus == $taxonomy_profile_url
				&& (int) $profile_author_id === (int) $userid
				&& !get_user_meta($current_user->ID, "emailhash", true);

			if ($is_owner_view) {
				$account_profile_id = get_the_ID();
				$account_needs_payment = get_post_meta($account_profile_id, "needs_payment", true);
				$account_is_private = (get_post_status($account_profile_id) === "private");
				$account_notactive = get_post_meta($account_profile_id, "notactive", true);
				$account_has_not_payed = $account_needs_payment ? "yes" : "no";
				$account_now = time();
				$account_warn_window = 7 * DAY_IN_SECONDS;

				$account_profile_expire = (int) get_post_meta($account_profile_id, "escort_expire", true);
				$account_premium_active = (get_post_meta($account_profile_id, "premium", true) == "1");
				$account_premium_expire = (int) get_post_meta($account_profile_id, "premium_expire", true);
				$account_featured_active = (get_post_meta($account_profile_id, "featured", true) == "1");
				$account_featured_expire = (int) get_post_meta($account_profile_id, "featured_expire", true);

				$profile_expiring = $account_profile_expire && (($account_profile_expire - $account_now) <= $account_warn_window);
				$profile_expired_by_date = $account_profile_expire && $account_profile_expire < $account_now;
				$account_is_expired = $account_needs_payment || $profile_expired_by_date || ($account_is_private && $account_notactive);
				$premium_expiring = $account_premium_active && $account_premium_expire && (($account_premium_expire - $account_now) <= $account_warn_window);
				$featured_expiring = $account_featured_active && $account_featured_expire && (($account_featured_expire - $account_now) <= $account_warn_window);

				$account_has_notices = !$account_needs_payment && (
					(bool) get_post_meta($account_profile_id, "escort_expire", true)
					|| get_post_meta($account_profile_id, "premium", true) == "1"
					|| get_post_meta($account_profile_id, "featured", true) == "1"
				);

				$details_open = $account_needs_payment || $account_is_private || $profile_expiring || $premium_expiring || $featured_expiring;
				?>
				<section class="profile-account" id="account-overview"
					aria-label="<?php esc_attr_e('My account overview', 'escortwp'); ?>">
					<div class="profile-account__inner">
						<div class="profile-account__header">
							<div class="profile-account__title-wrap">
								<span class="profile-account__eyebrow"><?php _e('My Account', 'escortwp'); ?></span>
								<h4 class="profile-account__title"><?php _e('Account Overview', 'escortwp'); ?></h4>
							</div>
							<div class="profile-account__header-actions">
								<button type="button" class="profile-account__manage-link" data-account-toggle="account-manage">
									<?php _e('Manage', 'escortwp'); ?>
								</button>
							</div>
						</div>

						<?php if ($account_is_expired) { ?>
							<div class="profile-account__alert" role="status" aria-live="polite">
								<div class="profile-account__alert-text">
									<strong><?php _e('Profile expired', 'escortwp'); ?></strong>
									<span><?php _e('Your profile is hidden from visitors until you reactivate it.', 'escortwp'); ?></span>
								</div>
								<button type="button" class="activate-account-btn" data-user-id="<?php echo esc_attr($userid); ?>">
									<?php _e('Activate Account', 'escortwp'); ?>
								</button>
							</div>
						<?php } ?>

						<div class="profile-account__status">
							<div class="profile-account__card profile-account__card--profile <?php echo $profile_expiring ? 'is-expiring' : ''; ?>">
								<div class="profile-account__card-label"><?php _e('Profile', 'escortwp'); ?></div>
								<div class="profile-account__card-value">
									<?php
									$profile_card_meta = array();
									if ($account_is_expired) {
										echo esc_html__('Expired', 'escortwp');
										if ($profile_expired_by_date) {
											$profile_card_meta[] = sprintf(__('Expired on %s', 'escortwp'), date_i18n('d M Y', $account_profile_expire));
										}
										$profile_card_meta[] = __('Payment required to reactivate', 'escortwp');
									} elseif ($account_profile_expire) {
										echo esc_html(date_i18n('d M Y', $account_profile_expire));
										$profile_card_meta[] = human_time_diff($account_now, $account_profile_expire) . ' ' . __('remaining', 'escortwp');
									} else {
										echo esc_html__('Active', 'escortwp');
										$profile_card_meta[] = __('No expiry date set', 'escortwp');
									}
									?>
								</div>
								<div class="profile-account__card-meta">
									<?php
									if (!empty($profile_card_meta)) {
										foreach ($profile_card_meta as $meta_line) {
											echo '<span>' . esc_html($meta_line) . '</span>';
										}
									}
									?>
								</div>
							</div>

							<div class="profile-account__card profile-account__card--premium <?php echo $premium_expiring ? 'is-expiring' : ''; ?>">
								<div class="profile-account__card-label"><?php _e('Premium', 'escortwp'); ?></div>
								<div class="profile-account__card-value">
									<?php
									if ($account_premium_active) {
										if ($account_premium_expire) {
											echo esc_html(date_i18n('d M Y', $account_premium_expire));
										} else {
											echo esc_html__('forever', 'escortwp');
										}
									} else {
										echo esc_html__('Not active', 'escortwp');
									}
									?>
								</div>
								<div class="profile-account__card-meta">
									<?php
									if ($account_premium_active && $account_premium_expire) {
										echo esc_html(human_time_diff($account_now, $account_premium_expire) . ' ' . __('remaining', 'escortwp'));
									} elseif ($account_premium_active) {
										echo esc_html__('Active', 'escortwp');
									} else {
										echo esc_html__('Upgrade available', 'escortwp');
									}
									?>
								</div>
							</div>

							<div class="profile-account__card profile-account__card--featured <?php echo $featured_expiring ? 'is-expiring' : ''; ?>">
								<div class="profile-account__card-label"><?php _e('Featured', 'escortwp'); ?></div>
								<div class="profile-account__card-value">
									<?php
									if ($account_featured_active) {
										if ($account_featured_expire) {
											echo esc_html(date_i18n('d M Y', $account_featured_expire));
										} else {
											echo esc_html__('forever', 'escortwp');
										}
									} else {
										echo esc_html__('Not active', 'escortwp');
									}
									?>
								</div>
								<div class="profile-account__card-meta">
									<?php
									if ($account_featured_active && $account_featured_expire) {
										echo esc_html(human_time_diff($account_now, $account_featured_expire) . ' ' . __('remaining', 'escortwp'));
									} elseif ($account_featured_active) {
										echo esc_html__('Active', 'escortwp');
									} else {
										echo esc_html__('Upgrade available', 'escortwp');
									}
									?>
								</div>
							</div>
						</div>

						<div class="profile-account__actions" aria-label="<?php esc_attr_e('Quick actions', 'escortwp'); ?>">
							<a class="profile-account__action"
								href="<?php echo get_permalink(get_option('escort_edit_personal_info_page_id')); ?>">
								<span class="icon icon-pencil"></span><?php _e('Edit my Profile', 'escortwp'); ?>
							</a>
							<?php if (is_woocommerce_active) { ?>
								<a class="profile-account__action"
									href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>">
									<span class="icon icon-dollar"></span><?php _e('My Payments', 'escortwp'); ?>
								</a>
							<?php } ?>
							<a class="profile-account__action"
								href="<?php echo get_permalink(get_option('change_password_page_id')); ?>">
								<span class="icon icon-key-outline"></span><?php _e('Change Password', 'escortwp'); ?>
							</a>
						</div>

						<?php
						$wallet_feature_context = array(
							'template' => 'single-profile',
							'profile_id' => (int) $account_profile_id,
							'user_id' => (int) $userid,
						);
						$wallet_enabled = function_exists('escortwp_child_wallet_feature_enabled') ? escortwp_child_wallet_feature_enabled($wallet_feature_context) : false;
						$wallet_state = function_exists('escortwp_child_wallet_feature_state') ? escortwp_child_wallet_feature_state($wallet_feature_context) : ($wallet_enabled ? 'enabled' : 'coming_soon');
						$wallet_unavailable_message = function_exists('escortwp_child_wallet_unavailable_message')
							? escortwp_child_wallet_unavailable_message($wallet_feature_context)
							: __('Wallet payments are currently unavailable.', 'escortwp');
						$wallet_section_classes = 'profile-account__wallet';
						if (!$wallet_enabled) {
							$wallet_section_classes .= ' profile-account__wallet--coming-soon';
						}
						?>

						<!-- ── Wallet Section ── -->
						<div class="<?php echo esc_attr($wallet_section_classes); ?>" id="wallet-section" data-wallet-state="<?php echo esc_attr($wallet_state); ?>">
							<div class="profile-account__wallet-content" <?php echo !$wallet_enabled ? 'inert aria-hidden="true"' : ''; ?>>
								<div class="profile-account__wallet-eyebrow"><?php _e('Wallet', 'escortwp'); ?></div>

								<div class="profile-account__wallet-cards">
									<div class="profile-account__wallet-card profile-account__wallet-card--balance">
										<div class="profile-account__card-label"><?php _e('Balance', 'escortwp'); ?></div>
										<div class="profile-account__wallet-amount" id="wallet-balance-display">
											<span class="wallet-currency">KES</span>
											<span class="wallet-amount wallet-amount--loading" data-wallet-amount="0">&nbsp;</span>
										</div>
										<div class="profile-account__card-meta">
											<span class="wallet-mode-badge" id="wallet-mode-badge" hidden><?php esc_html_e('Sandbox', 'escortwp'); ?></span>
											<span class="wallet-balance-sub"><?php _e('Available to spend', 'escortwp'); ?></span>
											<span class="wallet-last-updated" id="wallet-last-updated"><?php esc_html_e('Waiting for wallet sync', 'escortwp'); ?></span>
										</div>
									</div>

									<div class="profile-account__wallet-card profile-account__wallet-card--last-topup" id="wallet-last-topup" style="display:none;">
										<div class="profile-account__card-label"><?php _e('Last Top-Up', 'escortwp'); ?></div>
										<div class="profile-account__wallet-topup-amount" id="wallet-last-topup-amount"></div>
										<div class="profile-account__card-meta">
											<span id="wallet-last-topup-meta"></span>
										</div>
									</div>
								</div>

								<div class="profile-account__wallet-actions">
									<button type="button" class="profile-account__wallet-topup-btn" id="wallet-topup-btn">
										<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
										<?php _e('Top Up Wallet', 'escortwp'); ?>
									</button>
									<button type="button" class="profile-account__wallet-refresh-btn" id="wallet-refresh-btn" style="display:none;">
										<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.13-3.36L23 10"></path><path d="M20.49 15a9 9 0 0 1-14.13 3.36L1 14"></path></svg>
										<span class="wallet-refresh-label"><?php _e('Refresh', 'escortwp'); ?></span>
									</button>
									<button type="button" class="profile-account__wallet-history-btn" id="wallet-history-toggle" style="display:none;">
										<?php _e('View Transactions', 'escortwp'); ?>
										<span class="wallet-chevron">&rsaquo;</span>
									</button>
								</div>

								<div class="profile-account__wallet-history" id="wallet-history" hidden>
									<div class="profile-account__wallet-history-header">
										<span><?php _e('Recent Activity', 'escortwp'); ?></span>
									</div>
									<div class="profile-account__wallet-txn-list" id="wallet-txn-list"></div>
								</div>
							</div>

							<?php if (!$wallet_enabled) : ?>
								<div class="profile-account__wallet-overlay" role="status" aria-live="polite">
									<span class="profile-account__wallet-overlay-badge"><?php esc_html_e('Unavailable', 'escortwp'); ?></span>
									<p class="profile-account__wallet-overlay-message"><?php echo esc_html($wallet_unavailable_message); ?></p>
								</div>
							<?php endif; ?>
						</div>

						<details class="profile-account__details" id="account-manage" <?php echo $details_open ? 'open' : ''; ?>>
							<summary>
								<?php _e('Manage account', 'escortwp'); ?>
							</summary>
							<div class="profile-account__details-body <?php echo $account_has_notices ? 'profile-account__details-body--split' : 'profile-account__details-body--single'; ?>">
								<?php if (!$account_needs_payment) { ?>
									<div class="profile-account__notices">
										<?php
										if (get_post_meta($account_profile_id, "escort_expire", true)) {
											$escort_expire_date = date("d M Y", get_post_meta($account_profile_id, "escort_expire", true));
											echo '<div class="sidebar-expire-notice-mobile pinkdegrade text-center" data-payment-plan="reg">';
												echo '<div class="expiration-date">' . __('Profile expiration:', 'escortwp') . ' <b>' . $escort_expire_date . '</b></div>';
												if (get_post_meta($account_profile_id, "escort_expire", true) && payment_plans('indescreg', 'price')) {
													echo '<div class="sidebar-expire-mobile-extent-button greenbutton rad25">' . __('Extend', 'escortwp') . '</div>';
												}
											echo '</div>';
											echo '<div class="sidebar-expire-notice sidebar-expire-notice-has-mobile pinkdegrade center" data-payment-plan="reg">';
												echo '<small>' . sprintf(esc_html__('Your %s profile is active until', 'escortwp'), $taxonomy_profile_name) . ':</small>';
												echo '<b>' . $escort_expire_date . '</b>';
												echo '<div class="clear"></div>';
												echo '<small>' . human_time_diff(time(), get_post_meta($account_profile_id, "escort_expire", true)) . ' ' . __('remaining', 'escortwp') . '</small>';
												if (get_post_meta($account_profile_id, "escort_renew", true)) {
													// cancel subscription button
												} elseif (get_post_meta($account_profile_id, "escort_expire", true) && payment_plans('indescreg', 'price')) {
													echo '<div class="clear20"></div>';
													echo '<div class="text-center">' . generate_payment_buttons('indescreg', $account_profile_id, __('Extend registration', 'escortwp')) . '</div>';
													echo '<div class="clear5"></div>';
													echo '<div class="text-center"><small>' . format_price('indescreg') . '</small></div>';
												}
											echo '</div>';
										}

										if (get_post_meta($account_profile_id, "premium", true) == "1") {
											$premium_expire = get_post_meta($account_profile_id, "premium_expire", true);
											if ($premium_expire) {
												$premium_expire_date = date("d M Y", $premium_expire);
												$premium_mobile_expire_text = __('Premium expiration:', 'escortwp') . ' <b>' . $premium_expire_date . '</b>';
											} else {
												$premium_expire_date = strtolower(__('forever', 'escortwp'));
												$premium_mobile_expire_text = __('Premium status is active <b>forever</b>', 'escortwp');
											}

											echo '<div class="sidebar-expire-notice-mobile orangedegrade text-center" data-payment-plan="premium">';
												echo '<div class="expiration-date">' . $premium_mobile_expire_text . '</div>';
												if ($premium_expire && payment_plans('premium', 'price')) {
													echo '<div class="sidebar-expire-mobile-extent-button greenbutton rad25">' . __('Extend', 'escortwp') . '</div>';
												}
											echo '</div>';
											echo '<div class="sidebar-expire-notice sidebar-expire-notice-has-mobile orangedegrade center" data-payment-plan="premium">';
												echo '<small>' . __('Your premium status is active until', 'escortwp') . ':</small><b>' . $premium_expire_date . '</b>';
												if ($premium_expire) {
													echo '<small>' . human_time_diff(time(), $premium_expire) . ' ' . __('remaining', 'escortwp') . '</small>';
												}
												if (get_post_meta($account_profile_id, "premium_renew", true)) {
													// cancel subscription button
												} elseif ($premium_expire && payment_plans('premium', 'price')) {
													echo '<div class="clear20"></div>';
													echo '<div class="text-center">' . generate_payment_buttons('premium', $account_profile_id, __('Extend premium', 'escortwp')) . '</div>';
													echo '<div class="clear5"></div>';
													echo '<small>' . format_price('premium') . '</small>';
												}
											echo '</div>';
										}

										if (get_post_meta($account_profile_id, "featured", true) == "1") {
											$featured_expire = get_post_meta($account_profile_id, "featured_expire", true);
											if ($featured_expire) {
												$featured_expire_date = date("d M Y", $featured_expire);
												$featured_mobile_expire_text = __('Featured expiration:', 'escortwp') . ' <b>' . $featured_expire_date . '</b>';
											} else {
												$featured_expire_date = strtolower(__('forever', 'escortwp'));
												$featured_mobile_expire_text = __('Featured status is active <b>forever</b>', 'escortwp');
											}

											echo '<div class="sidebar-expire-notice-mobile bluedegrade text-center" data-payment-plan="featured">';
												echo '<div class="expiration-date">' . $featured_mobile_expire_text . '</div>';
												if ($featured_expire && payment_plans('featured', 'price')) {
													echo '<div class="sidebar-expire-mobile-extent-button greenbutton rad25">' . __('Extend', 'escortwp') . '</div>';
												}
											echo '</div>';
											echo '<div class="sidebar-expire-notice sidebar-expire-notice-has-mobile bluedegrade center" data-payment-plan="featured">';
												echo '<small>' . __('You featured status is active until', 'escortwp') . ':</small><b>' . $featured_expire_date . '</b>';
												if ($featured_expire) {
													echo '<small>' . human_time_diff(time(), $featured_expire) . ' ' . __('remaining', 'escortwp') . '</small>';
												}
												if (get_post_meta($account_profile_id, "featured_renew", true)) {
													// cancel subscription button
												} elseif ($featured_expire && payment_plans('featured', 'price')) {
													echo '<div class="clear20"></div>';
													echo '<div class="text-center">' . generate_payment_buttons('featured', $account_profile_id, __('Extend featured', 'escortwp')) . '</div>';
													echo '<div class="clear5"></div>';
													echo '<small>' . format_price('featured') . '</small>';
												}
											echo '</div>';
										}
										?>
									</div>
								<?php } ?>

								<div class="dropdownlinks dropdownlinks-dropdown dropdownlinks-profile profile-account__menu">
									<h4><?php _e('My Account', 'escortwp'); ?></h4>
									<ul>
										<li class="menu-card menu-card--profile">
											<a href="<?php echo get_permalink(get_option('escortpostid' . $userid)); ?>">
												<span class="icon icon-star-empty"></span>
												<span class="menu-label"><?php _e('View my Profile', 'escortwp'); ?></span>
											</a>
										</li>
										<li class="menu-card menu-card--edit">
											<a href="<?php echo get_permalink(get_option('escort_edit_personal_info_page_id')); ?>">
												<span class="icon icon-pencil"></span>
												<span class="menu-label"><?php _e('Edit my Profile', 'escortwp'); ?></span>
											</a>
										</li>
										<?php if (is_woocommerce_active) { ?>
											<li class="menu-card menu-card--payments <?php echo wc_get_account_menu_item_classes('orders'); ?>">
												<a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>">
													<span class="icon icon-dollar"></span>
													<span class="menu-label"><?= __('My Payments', 'escortwp') ?></span>
												</a>
											</li>
										<?php }
										if ($account_has_not_payed == "yes") { ?>
											<li class="menu-card menu-card--notice">
												<div class="menu-note"><?php _e('Other edit links will be shown after payment', 'escortwp'); ?></div>
											</li>
										<?php } else {
											if (get_option("hide8") != "1") { ?>
												<li class="menu-card menu-card--tours">
													<a href="<?php echo get_permalink(get_option('escort_tours_page_id')); ?>">
														<span class="icon icon-airplane"></span>
														<span class="menu-label"><?php _e('Tours', 'escortwp'); ?></span>
													</a>
												</li>
											<?php }
											if (get_option("hide6") != "1" && get_option("allowadpostingprofiles") == "1") { ?>
												<li class="menu-card menu-card--ads">
													<a href="<?php echo get_permalink(get_option('manage_ads_page_id')); ?>">
														<span class="icon icon-doc-text"></span>
														<span class="menu-label"><?php _e('Classified Ads', 'escortwp'); ?></span>
													</a>
												</li>
											<?php } ?>
											<li class="menu-card menu-card--password">
												<a href="<?php echo get_permalink(get_option('change_password_page_id')); ?>">
													<span class="icon icon-key-outline"></span>
													<span class="menu-label"><?php _e('Change Password', 'escortwp'); ?></span>
												</a>
											</li>
											<?php if (get_option("hide7") != "1") { ?>
												<li class="menu-card menu-card--verified">
													<a href="<?php echo get_permalink(get_option('escort_verified_status_page_id')); ?>">
														<span class="icon icon-check"></span>
														<span class="menu-label"><?php _e('Verified status', 'escortwp'); ?></span>
													</a>
												</li>
											<?php }
											if (get_option("hide5") != "1") { ?>
												<li class="menu-card menu-card--blacklist">
													<a href="<?php echo get_permalink(get_option('escort_blacklist_clients_page_id')); ?>">
														<span class="icon icon-block"></span>
														<span class="menu-label"><?php _e('Blacklisted Clients', 'escortwp'); ?></span>
													</a>
												</li>
											<?php }
										} ?>
										<li class="menu-card menu-card--logout">
											<a href="<?php echo wp_logout_url(home_url() . "/"); ?>">
												<span class="icon icon-logout"></span>
												<span class="menu-label"><?php _e('Log Out', 'escortwp'); ?></span>
											</a>
										</li>
										<?php if (!get_post_meta(get_option('escortpostid' . $userid), 'notactive', true) && !get_post_meta(get_option('escortpostid' . $userid), 'needs_payment', true)) { ?>
											<li class="menu-card menu-card--visibility">
												<?php
												$button_text = (get_post_status(get_option('escortpostid' . $userid)) == "publish") ? __('Set to private', 'escortwp') : __('Set as visible', 'escortwp');
												$button_class = (get_post_status(get_option('escortpostid' . $userid)) == "publish") ? "pinkbutton redbutton" : "greenbutton";
												?>
												<form action="<?php echo get_permalink(get_option('escortpostid' . $userid)); ?>" method="post">
													<input type="hidden" name="action" value="settoprivate" />
													<input type="submit" name="submit" value="<?= $button_text ?>" class="<?= $button_class ?> center rad25" />
												</form>
											</li>
											<li class="menu-card menu-card--danger">
												<a href="<?php echo get_permalink(get_option('escortpostid' . $userid)); ?>#delete-account" class="delete delete-account-button redbutton center rad25">
													<span class="icon icon-block"></span>
													<span class="menu-label"><?= __('Delete my account', 'escortwp') ?></span>
												</a>
											</li>
										<?php } ?>
									</ul>
									<div class="clear"></div>
								</div>
							</div>
						</details>
						<script type="text/javascript">
							(function() {
								var manageBtn = document.querySelector('.profile-account__manage-link[data-account-toggle="account-manage"]');
								var details = document.getElementById('account-manage');
								if (!manageBtn || !details) return;
								manageBtn.addEventListener('click', function() {
									details.open = !details.open;
									details.scrollIntoView({ behavior: 'smooth', block: 'start' });
								});
							})();
						</script>
					</div>
				</section>
				<?php
			}
			?>

			<!-- Sticky Contact Bar (JS-controlled, appears after scrolling past hero) -->
			<div class="profile-stickybar" id="profile-stickybar" aria-label="Sticky contact bar">
				<div class="profile-stickybar__inner">
					<div class="profile-stickybar__left">
						<div class="profile-stickybar__name">
							<b><?php the_title(); ?></b>
							<span><?php echo esc_html($age); ?><?php if ($city_name_display) {
								   echo ' &bull; ' . esc_html($city_name_display);
							   } ?></span>
						</div>
					</div>
					<nav class="profile-stickybar__nav" aria-label="Profile sections">
						<a href="#about" class="is-active">About</a>
						<a href="#gallery">Gallery</a>
						<a href="#details">Details</a>
						<a href="#services">Services</a>
						<a href="#rates">Rates</a>
						<a href="#tours">Tours</a>
						<a href="#reviews">Reviews</a>
					</nav>
					<div class="profile-stickybar__actions">
						<?php if ($phone): ?>
							<a class="profile-stickybar__btn profile-stickybar__btn--call"
								href="tel:<?php echo esc_attr($phone); ?>">
								<span class="icon icon-phone"></span> Call
							</a>
						<?php endif; ?>
						<?php if (is_array($phone_available_on) && in_array('1', $phone_available_on)): ?>
							<a class="profile-stickybar__btn profile-stickybar__btn--wa"
								href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $phone); ?>">
								<span class="icon icon-whatsapp"></span> WhatsApp
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="girlsingle<?php if (isset($err) && $err && in_array($_POST['action'], array('adminnote', 'addtour', 'edittour', 'register'))) {
				echo " hide";
			} ?>" itemscope itemtype="http://schema.org/Person">

				<?php
				if ($adminnote) {
					echo '<div class="clear"></div>';
					echo '<div class="err rad25">' . $adminnote . '</div>';
				}
				?>
					<?php if ($profile_author_id == $userid || current_user_can('level_10')) { ?>
						<div class="clear10"></div>
						<div
							class="profile-page-no-media-wrapper profile-page-no-media-wrapper-photos <?= (get_option('allowvideoupload') == "1") ? " col50 l" : " col100" ?>">
						<div class="profile-page-no-media profile-page-no-photos profile-page-no-photos-click rad3 col100 text-center"
							id="profile-page-no-photos">
							<div class="profile-upload-card__body">
								<div class="profile-upload-card__icon">
									<span class="icon icon-picture"></span>
								</div>
								<div class="profile-upload-card__content">
									<h5 class="profile-upload-card__title"><?php esc_html_e('Upload images', 'escortwp'); ?></h5>
									<div class="for-browsers"
										data-mobile-text="<?php _e('Tap here to upload your images', 'escortwp'); ?>">
										<p><?php esc_html_e('Drag and drop your images here, or tap to choose a folder.', 'escortwp'); ?></p>
									</div>
								</div>
							</div>
							<div class="profile-upload-card__meta">
								<p class="max-photos">
									<?php printf(esc_html__('You can upload a maximum of %s images', 'escortwp'), '<b>' . $photos_left . '</b>'); ?>
								</p>
								<span class="profile-upload-card__hint"><?php esc_html_e('JPG, PNG, WEBP', 'escortwp'); ?></span>
							</div>
							<div class="clear"></div>
						</div>
							<div class="profile_photos_button_container hide"><input id="profile_photos_upload"
									name="file_upload" type="file" /></div>
						</div> <!-- profile-page-no-media-wrapper -->

						<?php if (get_option('allowvideoupload') == "1") { ?>
							<div class="profile-page-no-media-wrapper profile-page-no-media-wrapper-videos col50 r">
								<div class="profile-page-no-media profile-page-no-videos profile-page-no-videos-click rad3 col100 text-center"
									id="profile-page-no-videos">
								<div class="profile-upload-card__body">
									<div class="profile-upload-card__icon">
										<span class="icon icon-film"></span>
									</div>
									<div class="profile-upload-card__content">
										<h5 class="profile-upload-card__title"><?php esc_html_e('Upload videos', 'escortwp'); ?></h5>
										<div class="for-browsers"
											data-mobile-text="<?php _e('Tap here to upload your videos', 'escortwp'); ?>">
											<p><?php esc_html_e('Drag and drop your videos here, or tap to choose a folder.', 'escortwp'); ?></p>
										</div>
									</div>
								</div>
								<div class="profile-upload-card__meta">
									<p class="max-videos">
										<?php printf(esc_html__('You can upload a maximum of %s videos', 'escortwp'), '<b>' . $videos_left . '</b>'); ?>
									</p>
									<span class="profile-upload-card__hint"><?php esc_html_e('MP4, MOV, WEBM', 'escortwp'); ?></span>
								</div>
								<div class="clear"></div>
							</div>
								<div class="profile_videos_button_container hide"><input id="profile_videos_upload"
										name="file_upload" type="file" /></div>
							</div> <!-- profile-page-no-media-wrapper -->
					<?php } ?>
					<div class="clear20"></div>
				<?php } ?>
				<?php
				if ($photos || $videos) { //we only show the code for the main image and the thumbs if the user has at least one image
					if ($profile_author_id == $userid || current_user_can('level_10')) {
						echo '<div class="image-buttons-legend">
						<div><span class="button-main-image icon-ok"></span> ' . __('Mark as main image', 'escortwp') . '</div>
						<div><span class="button-delete icon-cancel"></span> ' . __('Delete image', 'escortwp') . '</div>
					</div>';
					} // if user is author
					?>
					<div class="clear10"></div>
					<div class="thumbs" id="gallery" itemscope itemtype="http://schema.org/ImageGallery">
						<?php
						$nrofphotos = count($photos) - 1; //nr of photos left if we exclude the main big image
						$nrofvideos = count($videos);
						if (count($photos) > 0 || $nrofvideos > 0) {
							if ($nrofvideos > 0) {
								$and_videos = ' ' . sprintf(esc_html__('and %s more videos', 'escortwp'), '<span class="nr rad5 greendegrade">' . $nrofvideos . '</span>');
							}
							$main_image_id = get_post_meta(get_the_ID(), "main_image_id", true);
							if ($main_image_id < 1 || !get_post($main_image_id)) {
								$firstphoto = reset($photos);
								if ($firstphoto) {
									$main_image_id = $firstphoto->ID;
									update_post_meta(get_the_ID(), "main_image_id", $main_image_id);
								}
							}

							$main_image_url = wp_get_attachment_image_src((int) $main_image_id, 'main-image-thumb');
							if ($main_image_url[3] != "1") {
								require_once(ABSPATH . 'wp-admin/includes/image.php');
								$attach_data = wp_generate_attachment_metadata($main_image_id, get_attached_file($main_image_id));
								wp_update_attachment_metadata($main_image_id, $attach_data);
								$main_image_url = wp_get_attachment_image_src($main_image_id, 'main-image-thumb');
							}
							if (!$main_image_url[0]) {
								$main_image_url[0] = get_stylesheet_directory_uri() . '/i/no-image.png';
							}
							$bigimage = '<div class="bigimage">';
							$bigimage .= '<img src="' . $main_image_url[0] . '" class="rad3" alt="' . get_the_title() . '" />' . "\n";
							$bigimage .= '</div> <!-- bigimage -->';

							if (payment_plans('vip', 'extra', 'hide_photos') && !is_user_logged_in()) {
								echo $bigimage;
								echo '<div class="clear"></div>';
								echo '<div class="lockedsection rad5">';
								echo '<div class="icon icon-lock vcenter l"></div>';
								echo sprintf(esc_html__('This %s has %s more photos', 'escortwp'), $taxonomy_profile_name, '<span class="nr rad5 greendegrade">' . $nrofphotos . '</span>') . $and_videos . '.<br />';
								echo __('You need to', 'escortwp') . ' <a href="' . get_permalink(get_option('main_reg_page_id')) . '">' . __('register', 'escortwp') . '</a> ' . __('or', 'escortwp') . ' <a href="' . wp_login_url(get_permalink()) . '">' . __('login', 'escortwp') . '</a> ' . __('to be able to view the other photos', 'escortwp') . '.';
								echo '<div class="clear"></div>';
								echo '</div> <!-- lockedsection -->';
							} else {
								if (payment_plans('vip', 'extra', 'hide_photos') && !get_user_meta($userid, "vip", true) && !current_user_can('level_10') && $profile_author_id != $userid) {
									echo $bigimage;
									echo '<div class="clear5"></div>';
									echo '<div class="lockedsection rad5">';
									echo '<div class="icon icon-lock vcenter l"></div>';
									printf(esc_html__('This %1$s has %2$s more photos', 'escortwp'), $taxonomy_profile_name, '<span class="nr rad5 greendegrade">' . $nrofphotos . '</span>');
									echo $and_videos . '.<br />';
									echo __('You need to be a VIP member to see the rest of the photos', 'escortwp') . ".<br />";
									echo __('VIP status costs', 'escortwp') . ' <strong>' . format_price('vip', 'small') . "</strong><br />";
									if (payment_plans('vip', 'duration')) {
										echo __('Your VIP status will be active for', 'escortwp') . ' <strong>' . $payment_duration_a[payment_plans('vip', 'duration')][0] . '</strong> ';
									}
									echo '<div class="clear20"></div>';
									echo '<div class="text-center">' . generate_payment_buttons("vip", $userid, __('Upgrade to VIP', 'escortwp')) . "</div> <!--center-->";
									echo '<div class="clear5"></div>';
									echo '<small>' . format_price('vip') . '</small>';
									echo '</div>';
								} else {
									echo '<div class="girlsinglethumbs">';
									/* VIDEOS */
									foreach ($videos as $video) {
										$video_url = wp_get_attachment_url($video->ID);
										if (!$video_url) {
											continue;
										}

										// Try to use generated JPG thumbnail if it exists; otherwise fall back to placeholder
										$abs_path = get_attached_file($video->ID);
										$poster_fs = $abs_path . '.jpg';
										if ($abs_path && file_exists($poster_fs)) {
											$poster_url = $video->guid . '.jpg';
										} else {
											$poster_url = get_stylesheet_directory_uri() . '/i/video-placeholder.svg';
										}

										// Edit buttons for owner/admin
										$imagebuttons = '';
										if ($profile_author_id == $userid || current_user_can('level_10')) {
											$imagebuttons = '<span class="edit-buttons"><span class="icon button-delete icon-cancel rad50"></span></span>';
										}

										echo '<div class="profile-video-thumb-wrapper">';
										echo '<div class="profile-img-thumb profile-video-thumb rad3" id="' . esc_attr($video->ID) . '">';
										echo $imagebuttons;

										// Pure HTML5 video – no wp_video_shortcode, no Fancybox dependency
										echo '<video class="escortwp-profile-video" controls playsinline preload="metadata"'
											. ' poster="' . esc_url($poster_url) . '"'
											. ' style="width:100%;height:auto;">';

										echo '<source src="' . esc_url($video_url) . '" type="' . esc_attr($video->post_mime_type ?: 'video/mp4') . '">';

										echo esc_html__('Your browser does not support the video tag.', 'escortwp');
										echo '</video>';

										echo '<div class="clear"></div></div></div>' . "\n";
									}

									if (count($videos) > 0) {
										echo '<div class="clear10"></div>';
									}


									/* ---------------- PHOTOS (unchanged) ---------------- */
									foreach ($photos as $photo) {
										$photo_th_url = wp_get_attachment_image_src($photo->ID, 'profile-thumb');
										if ($photo_th_url[3] != "1") {
											require_once(ABSPATH . 'wp-admin/includes/image.php');
											$attach_data = wp_generate_attachment_metadata($photo->ID, get_attached_file($photo->ID));
											wp_update_attachment_metadata($photo->ID, $attach_data);
											$photo_th_url = wp_get_attachment_image_src($photo->ID, 'profile-thumb');
										}

										$photo_th_mobile_url = wp_get_attachment_image_src($photo->ID, 'profile-thumb-mobile');
										if ($photo_th_mobile_url[3] != "1") {
											require_once(ABSPATH . 'wp-admin/includes/image.php');
											$attach_data = wp_generate_attachment_metadata($photo->ID, get_attached_file($photo->ID));
											wp_update_attachment_metadata($photo->ID, $attach_data);
											$photo_th_mobile_url = wp_get_attachment_image_src($photo->ID, 'profile-thumb-mobile');
										}

										$imagebuttons = '';
										if ($profile_author_id == $userid || current_user_can('level_10')) {
											$imagebuttons = '<span class="edit-buttons"><span class="icon button-delete icon-cancel rad50"></span><span class="icon button-main-image icon-ok rad50"></span></span>';
										}
										echo '<div class="profile-img-thumb-wrapper"><div class="profile-img-thumb" id="' . $photo->ID . '" itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
										echo $imagebuttons;
										echo '<a href="' . $photo->guid . '" data-fancybox="profile-photo" itemprop="contentURL">';
										echo '<img data-original-url="' . $photo_th_url[0] . '" class="mobile-ready-img rad3" alt="' . get_the_title() . '" data-responsive-img-url="' . $photo_th_mobile_url[0] . '" itemprop="thumbnailUrl" />';
										echo '</a>';
										echo '</div></div>' . "\n";
									}
									// Step 5: Gallery placeholders — pad to 3 cells minimum
									$total_media = count($photos) + count($videos);
									if ($total_media < 3) {
										for ($p = $total_media; $p < 3; $p++) {
											echo '<div class="profile-gallery-placeholder"><span class="icon icon-picture"></span></div>';
										}
									}
									echo '</div>'; // Close girlsinglethumbs
								} // if photo section is locked and user is not VIP
							} // is photo section locked and user is not logged in
						} // if escort has at least one photo
						?>
					</div> <!-- THUMBS -->

					<div class="clear20"></div>
				<?php } // if at least one photo uploaded ?>

				<?php
				$location = array();
				$city = wp_get_post_terms(get_the_ID(), $taxonomy_location_url);
				if ($city && !is_wp_error($city)) {
					$location[] = '<a href="' . get_term_link($city[0]) . '" title="' . $city[0]->name . '">' . $city[0]->name . '</a>';

					$state = get_term($city[0]->parent, $taxonomy_location_url);
					if ($state && !is_wp_error($state)) {
						$location[] = '<a href="' . get_term_link($state) . '" title="' . $state->name . '">' . $state->name . '</a>';

						$country = get_term($state->parent, $taxonomy_location_url);
						if (!is_wp_error($country)) {
							$location[] = '<a href="' . get_term_link($country) . '" title="' . $country->name . '">' . $country->name . '</a>';
						}
					}
				}
				?>
				<div class="clear"></div>
				<div class="aboutme" id="about">
					<div class="aboutme__heading">
						<span class="aboutme__eyebrow"><?php _e('Profile introduction', 'escortwp'); ?></span>
						<h4><?php _e('About me', 'escortwp'); ?>:</h4>
					</div>
					<div class="aboutme__meta">
						<b><?= $age ?> <?= __('year old', 'escortwp') ?> <span
								itemprop="gender"><?= __($gender_a[$gender], 'escortwp') ?></span>
							<?= __('from', 'escortwp') ?>
							<?= implode(", ", $location) ?></b>
					</div>
					<?php
					if ($current_tour) {
						$currently_on_tour_box = '<div class="clear"></div>';
						$currently_on_tour_box .= '<div class="currently-on-tour-in rad5">';
						$currently_on_tour_box .= __('Currently on tour in:', 'escortwp');
						if ($current_tour['city']) {
							$city_obj = $current_tour['city'];
							$tour_location[] = '<a href="' . get_term_link($city_obj) . '" title="' . $city_obj->name . '">' . $city_obj->name . '</a>';
						}
						if ($current_tour['state']) {
							$state_obj = $current_tour['state'];
							$tour_location[] = '<a href="' . get_term_link($state_obj) . '" title="' . $state_obj->name . '">' . $state_obj->name . '</a>';
						}
						if ($current_tour['country']) {
							$country_obj = $current_tour['country'];
							$tour_location[] = '<a href="' . get_term_link($country_obj) . '" title="' . $country_obj->name . '">' . $country_obj->name . '</a>';
						}
						$currently_on_tour_box .= " " . implode(", ", $tour_location);
						$currently_on_tour_box .= '</div>';
						$currently_on_tour_box .= '<div class="clear"></div>';
						echo $currently_on_tour_box;
					}
					?>
					<div class="clear5"></div>
					<div class="aboutme__copy">
						<?php echo $aboutyou; ?>
					</div>
					<div class="clear"></div>
				</div> <!-- ABOUT ME -->
				<div class="clear10"></div>

				<div class="girlinfo l" id="details">
					<div class="girlinfo-section">
						<?php
						$favorites = get_user_meta($userid, "favorites", true);
						if ($favorites) {
							$favorites = array_unique(explode(",", $favorites));
						} else {
							$favorites = array();
						}

						if ($userstatus == "member" || current_user_can('level_10')) {
							if (in_array(get_the_ID(), $favorites)) {
								$addclass = '';
								$remclass = ' style="display: none;"';
							} else {
								$addclass = ' style="display: none;"';
								$remclass = '';
							}
							?>
							<div class="text-center">
								<div class="removefromfavorites rad25 pinkbutton favbutton" id="rem<?php the_ID(); ?>" <?php echo $addclass; ?>><span
										class="icon-heart"></span><?php _e('Remove Favorite', 'escortwp'); ?></div>
								<div class="addtofavorites rad25 pinkbutton favbutton" id="add<?php the_ID(); ?>" <?php echo $remclass; ?>><span
										class="icon-heart"></span><?php _e('Add to Favorites', 'escortwp'); ?></div>
							</div>
						<?php } ?>
						<div class="clear"></div>
						<?php
						if ($availability) {
							foreach ($availability as $a_id) {
								$availability_show[] = __($availability_a[$a_id], 'escortwp');
							}
							echo '<div class="section-box"><b>' . __('Availability', 'escortwp') . '</b><span class="valuecolumn">' . implode(", ", $availability_show) . '</span></div>';
						}
						if ($ethnicity) {
							echo '<div class="section-box"><b>' . __('Ethnicity', 'escortwp') . '</b><span class="valuecolumn">' . __($ethnicity_a[$ethnicity], 'escortwp') . '</span></div>';
						}
						if ($haircolor) {
							echo '<div class="section-box"><b>' . __('Hair color', 'escortwp') . '</b><span class="valuecolumn">' . __($haircolor_a[$haircolor], 'escortwp') . '</span></div>';
						}
						if ($hairlength) {
							echo '<div class="section-box"><b>' . __('Hair length', 'escortwp') . '</b><span class="valuecolumn">' . __($hairlength_a[$hairlength], 'escortwp') . '</span></div>';
						}
						if ($bustsize) {
							echo '<div class="section-box"><b>' . __('Bust size', 'escortwp') . '</b><span class="valuecolumn">' . __($bustsize_a[$bustsize], 'escortwp') . '</span></div>';
						}
						if ($height) {
							echo '<div class="section-box"><b itemprop="height">' . __('Height', 'escortwp') . '</b><span class="valuecolumn">' . $height . (get_option("heightscale") == "imperial" ? "ft" . ($height2 > 0 ? " " . $height2 . "in" : "") : "cm") . '</span></div>';
						}
						if ($weight) {
							echo '<div class="section-box"><b itemprop="weight">' . __('Weight', 'escortwp') . '</b><span class="valuecolumn">' . $weight . (get_option("heightscale") == "imperial" ? "lb" : "kg") . '</span></div>';
						}
						if ($build) {
							echo '<div class="section-box"><b>' . __('Build', 'escortwp') . '</b><span class="valuecolumn">' . __($build_a[$build], 'escortwp') . '</span></div>';
						}
						if ($looks) {
							echo '<div class="section-box"><b>' . __('Looks', 'escortwp') . '</b><span class="valuecolumn">' . __($looks_a[$looks], 'escortwp') . '</span></div>';
						}
						if ($smoker) {
							echo '<div class="section-box"><b>' . __('Smoker', 'escortwp') . '</b><span class="valuecolumn">' . __($smoker_a[$smoker], 'escortwp') . '</span></div>';
						}
						if ($education) {
							echo '<div class="section-box"><b>' . __('Education', 'escortwp') . '</b><span class="valuecolumn">' . __($education, 'escortwp') . '</span></div>';
						}
						if ($sports) {
							echo '<div class="section-box"><b>' . __('Sports', 'escortwp') . '</b><span class="valuecolumn">' . __($sports, 'escortwp') . '</span></div>';
						}
						if ($hobbies) {
							echo '<div class="section-box"><b>' . __('Hobbies', 'escortwp') . '</b><span class="valuecolumn">' . __($hobbies, 'escortwp') . '</span></div>';
						}
						if ($zodiacsign) {
							echo '<div class="section-box"><b>' . __('Zodiac sign', 'escortwp') . '</b><span class="valuecolumn">' . __($zodiacsign, 'escortwp') . '</span></div>';
						}
						if ($sexualorientation) {
							echo '<div class="section-box"><b>' . __('Sexual orientation', 'escortwp') . '</b><span class="valuecolumn">' . __($sexualorientation, 'escortwp') . '</span></div>';
						}
						if ($occupation) {
							echo '<div class="section-box"><b>' . __('Occupation', 'escortwp') . '</b><span class="valuecolumn">' . __($occupation, 'escortwp') . '</span></div>';
						}
						?>
					</div> <!-- girlinfo-section -->

					<?php
					if ($language1 || $language2 || $language3) {
						echo '<div class="girlinfo-section">';
						echo '<h4>' . __('Languages spoken', 'escortwp') . ':</h4><div class="clear"></div>';
						if ($language1) {
							echo "<div class='section-box'><b>" . ucfirst(__($language1, 'escortwp')) . ":</b><span class=\"valuecolumn\">" . __($languagelevel_a[$language1level], 'escortwp') . "</span></div>";
						}
						if ($language2) {
							echo "<div class='section-box'><b>" . ucfirst(__($language2, 'escortwp')) . ":</b><span class=\"valuecolumn\">" . __($languagelevel_a[$language2level], 'escortwp') . "</span></div>";
						}
						if ($language3) {
							echo "<div class='section-box'><b>" . ucfirst(__($language3, 'escortwp')) . ":</b><span class=\"valuecolumn\">" . __($languagelevel_a[$language3level], 'escortwp') . "</span></div>";
						}
						echo '</div> <!-- girlinfo-section -->';
					} // if at least one language
					?>

						<div class="girlinfo-section profile-contact-panel">
							<div class="profile-section-heading">
								<span class="profile-section-heading__eyebrow"><?php _e('Reach out', 'escortwp'); ?></span>
								<h4><?php _e('Contact info', 'escortwp'); ?>:</h4>
							</div>
							<div class="clear"></div>
							<div class="contact profile-contact-card">
								<?php
								if (isset($currently_on_tour_box)) {
									echo $currently_on_tour_box;
								}

								$phone_digits = $phone ? preg_replace("/([^0-9])/", "", $phone) : '';
								$contact_channels = array();
								$contact_actions = array();

								if ($phone) {
									$contact_actions[] = '<a class="profile-contact-card__action profile-contact-card__action--call" href="tel:' . esc_attr($phone) . '" itemprop="telephone"><span class="icon icon-phone"></span><span class="profile-contact-card__action-copy"><strong>' . esc_html__('Call', 'escortwp') . '</strong><small>' . esc_html($phone) . '</small></span></a>';
								}

								if (is_array($phone_available_on) && count($phone_available_on) > 0 && $phone_digits !== '') {
									foreach ($phone_available_on as $value) {
										switch ($value) {
											case '1':
												$contact_actions[] = '<a class="profile-contact-card__action profile-contact-card__action--whatsapp" href="https://wa.me/' . esc_attr($phone_digits) . '?text=' . urlencode(sprintf(__('Hi, I saw your profile on %s', 'escortwp'), get_site_url())) . '" target="_blank" rel="noopener"><span class="icon icon-whatsapp"></span><span class="profile-contact-card__action-copy"><strong>' . esc_html__('WhatsApp', 'escortwp') . '</strong><small>' . esc_html__('Fast reply', 'escortwp') . '</small></span></a>';
												break;
											case '2':
												$contact_actions[] = '<a class="profile-contact-card__action profile-contact-card__action--viber" href="viber://chat?number=' . esc_attr($phone_digits) . '"><span class="icon icon-viber"></span><span class="profile-contact-card__action-copy"><strong>' . esc_html__('Viber', 'escortwp') . '</strong><small>' . esc_html__('Message directly', 'escortwp') . '</small></span></a>';
												break;
										}
									}
								}

								if ($website) {
									$wraped_website_url = str_replace(array("http://www.", "http://", "https://www.", "https://"), "", $website);
									$contact_channels[] = '<a class="profile-contact-card__channel" href="' . esc_url($website) . '" target="_blank" rel="nofollow noopener" itemprop="url"><span class="icon icon-link"></span><span>' . esc_html($wraped_website_url) . '</span></a>';
								}

								if ($snapchat) {
									$contact_channels[] = '<span class="profile-contact-card__channel profile-contact-card__channel--static"><img src="' . esc_url(get_stylesheet_directory_uri() . '/i/snapchat.svg') . '" class="social-icons-contact-info" height="18" alt="SnapChat" /><span>@' . esc_html($snapchat) . '</span></span>';
								}

								if ($instagram) {
									$contact_channels[] = '<a class="profile-contact-card__channel" href="https://www.instagram.com/' . rawurlencode($instagram) . '/" target="_blank" rel="nofollow noopener" itemprop="url"><img src="' . esc_url(get_stylesheet_directory_uri() . '/i/instagram.svg') . '" class="social-icons-contact-info" height="18" alt="Instagram" /><span>@' . esc_html($instagram) . '</span></a>';
								}

								if ($twitter) {
									$twitter_username = "@" . str_replace(array("http://www.", "http://", "https://www.", "https://", "twitter.com/"), "", $twitter);
									$contact_channels[] = '<a class="profile-contact-card__channel" href="' . esc_url($twitter) . '" target="_blank" rel="nofollow noopener" itemprop="url"><img src="' . esc_url(get_stylesheet_directory_uri() . '/i/twitter.svg') . '" class="social-icons-contact-info" height="18" alt="Twitter" /><span>' . esc_html($twitter_username) . '</span></a>';
								}

								if ($facebook) {
									$facebook_username = "@" . str_replace(array("http://www.", "http://", "https://www.", "https://", "facebook.com/"), "", $facebook);
									$contact_channels[] = '<a class="profile-contact-card__channel" href="' . esc_url($facebook) . '" target="_blank" rel="nofollow noopener" itemprop="url"><img src="' . esc_url(get_stylesheet_directory_uri() . '/i/facebook.svg') . '" class="social-icons-contact-info" height="18" alt="Facebook" /><span>' . esc_html($facebook_username) . '</span></a>';
								}

								if (payment_plans('vip', 'extra', 'hide_contact_info') && !is_user_logged_in()) {
									echo '<div class="clear5"></div>';
									echo '<div class="lockedsection rad5">';
									echo __('You need to', 'escortwp') . ' <a href="' . get_permalink(get_option('main_reg_page_id')) . '">' . __('register', 'escortwp') . '</a> ' . __('or', 'escortwp') . ' <a href="' . wp_login_url(get_permalink()) . '">' . __('login', 'escortwp') . '</a> ' . sprintf(esc_html__('to be able to see the contact information for this %s', 'escortwp'), $taxonomy_profile_name) . '.';
									echo '</div>';
								} else {
									if (payment_plans('vip', 'extra', 'hide_contact_info') && !get_user_meta($userid, "vip", true) && !current_user_can('level_10') && $profile_author_id != $userid) {
										echo '<div class="clear5"></div><div class="lockedsection rad5">';
										echo sprintf(esc_html__('You need to be a VIP member to see the contact information of an %s', 'escortwp'), $taxonomy_profile_name) . ".<br />";
										echo __('VIP status costs', 'escortwp') . ' <strong>' . format_price('vip', 'small') . "</strong><br />";
										if (payment_plans('vip', 'duration')) {
											echo __('Your VIP status will be active for', 'escortwp') . ' <strong>' . $payment_duration_a[payment_plans('vip', 'duration')][0] . '</strong> ';
										}
										echo '<div class="clear20"></div>';
										echo '<div class="text-center">' . generate_payment_buttons("vip", $userid, __('Upgrade to VIP', 'escortwp')) . "</div> <!--center-->";
										echo '<div class="clear5"></div>';
										echo '<small>' . format_price('vip') . '</small>';
										echo '</div>';
									} else {
										if (!empty($contact_actions)) {
											echo '<div class="profile-contact-card__actions">' . implode('', array_unique($contact_actions)) . '</div>';
										}

										if (!empty($contact_channels)) {
											echo '<div class="profile-contact-card__channels">' . implode('', $contact_channels) . '</div>';
										}
									}
								}
								?>
							</div> <!-- CONTACT -->
						</div> <!-- girlinfo-section -->
				</div> <!-- girlinfo -->

				<div class="girlinfo r">
						<?php if ($services || $extraservices) { ?>
							<?php
							$service_keys = is_array($services) ? array_map('strval', $services) : array();
							$available_services = array();
							foreach ($services_a as $key => $service) {
								if (in_array((string) $key, $service_keys, true)) {
									$available_services[] = __($service, 'escortwp');
								}
							}
							?>
							<div class="girlinfo-section profile-services-panel" id="services">
								<div class="profile-section-heading">
									<span class="profile-section-heading__eyebrow"><?php _e('Experience', 'escortwp'); ?></span>
									<h4><?php _e('Services', 'escortwp'); ?>:</h4>
								</div>
								<?php if (!empty($available_services)) { ?>
									<div class="services profile-services-grid">
										<?php foreach ($available_services as $service) { ?>
											<div class="profile-service-chip"><span class="icon-ok"></span><span><?php echo esc_html($service); ?></span></div>
										<?php } ?>
									</div> <!-- SERVICES -->
								<?php } ?>

								<?php if ($extraservices) { ?>
									<div class="profile-services-note">
										<span class="profile-services-note__label"><?php _e('Custom requests', 'escortwp'); ?></span>
										<div class="profile-services-note__body"><?php echo wpautop(wp_kses_post($extraservices)); ?></div>
									</div>
								<?php } ?>
							</div> <!-- girlinfo-section -->
						<?php } // if $services ?>

					<?php
					if (!$currency) {
						$currency = $currency_a['1'][0];
					} else {
						$currency = $currency_a[$currency][0];
					}
					$rates_sum_incall = (int) $rate30min_incall + (int) $rate1h_incall + (int) $rate2h_incall + (int) $rate3h_incall + (int) $rate6h_incall + (int) $rate12h_incall + (int) $rate24h_incall;
					$rates_sum_outcall = (int) $rate30min_outcall + (int) $rate1h_outcall + (int) $rate2h_outcall + (int) $rate3h_outcall + (int) $rate6h_outcall + (int) $rate12h_outcall + (int) $rate24h_outcall;
					if ($rates_sum_incall + $rates_sum_outcall > 0) {
						$rate_rows = array();
						$rate_definitions = array(
							array(
								'label' => __('30 minutes', 'escortwp'),
								'incall' => $rate30min_incall,
								'outcall' => $rate30min_outcall,
							),
							array(
								'label' => __('1 hour', 'escortwp'),
								'incall' => $rate1h_incall,
								'outcall' => $rate1h_outcall,
							),
							array(
								'label' => __('2 hours', 'escortwp'),
								'incall' => $rate2h_incall,
								'outcall' => $rate2h_outcall,
							),
							array(
								'label' => __('3 hours', 'escortwp'),
								'incall' => $rate3h_incall,
								'outcall' => $rate3h_outcall,
							),
							array(
								'label' => __('6 hours', 'escortwp'),
								'incall' => $rate6h_incall,
								'outcall' => $rate6h_outcall,
							),
							array(
								'label' => __('12 hours', 'escortwp'),
								'incall' => $rate12h_incall,
								'outcall' => $rate12h_outcall,
							),
							array(
								'label' => __('24 hours', 'escortwp'),
								'incall' => $rate24h_incall,
								'outcall' => $rate24h_outcall,
							),
						);
						foreach ($rate_definitions as $rate_definition) {
							if ($rate_definition['incall'] || $rate_definition['outcall']) {
								$rate_rows[] = $rate_definition;
							}
						}
						$rate_modes = array();
						if ($rates_sum_incall) {
							$rate_modes['incall'] = array(
								'label' => __('Incall', 'escortwp'),
								'note' => __('Hosted session', 'escortwp'),
							);
						}
						if ($rates_sum_outcall) {
							$rate_modes['outcall'] = array(
								'label' => __('Outcall', 'escortwp'),
								'note' => __('Travel session', 'escortwp'),
							);
						}
						$active_rate_mode = array_key_first($rate_modes);

						echo '<div class="girlinfo-section profile-rates" id="rates">';
						echo '<div class="profile-section-heading">';
						echo '<span class="profile-section-heading__eyebrow">' . esc_html__('Session menu', 'escortwp') . '</span>';
						echo '<h4>' . esc_html__('Rates', 'escortwp') . ':</h4>';
						echo '</div>';

						if (count($rate_modes) > 1) {
							echo '<div class="profile-rates-tabs" role="tablist" aria-label="' . esc_attr__('Choose rate type', 'escortwp') . '">';
							foreach ($rate_modes as $rate_mode_key => $rate_mode) {
								$is_active_rate_mode = $rate_mode_key === $active_rate_mode;
								echo '<button type="button" class="profile-rates-tab' . ($is_active_rate_mode ? ' is-active' : '') . '" role="tab" aria-selected="' . ($is_active_rate_mode ? 'true' : 'false') . '" tabindex="' . ($is_active_rate_mode ? '0' : '-1') . '" data-rate-mode="' . esc_attr($rate_mode_key) . '">' . esc_html($rate_mode['label']) . '</button>';
							}
							echo '</div>';
						}

						echo '<div class="profile-rates-panels">';
						foreach ($rate_modes as $rate_mode_key => $rate_mode) {
							$is_active_rate_mode = $rate_mode_key === $active_rate_mode;
							echo '<div class="profile-rates-panel' . ($is_active_rate_mode ? ' is-active' : '') . '" data-rate-mode="' . esc_attr($rate_mode_key) . '" aria-hidden="' . ($is_active_rate_mode ? 'false' : 'true') . '"' . ($is_active_rate_mode ? '' : ' hidden') . '>';
							if (count($rate_modes) === 1) {
								echo '<span class="profile-rates-mode">' . esc_html($rate_mode['label']) . '</span>';
							}
							echo '<p class="profile-rates-note">' . esc_html($rate_mode['note']) . '</p>';
							echo '<div class="profile-rates-list">';
							foreach ($rate_rows as $rate_row) {
								$rate_value = $rate_row[$rate_mode_key];
								if (!$rate_value) {
									continue;
								}
								echo '<div class="profile-rates-row">';
								echo '<span class="profile-rates-duration">' . esc_html($rate_row['label']) . '</span>';
								echo '<span class="profile-rates-price">' . esc_html($rate_value . ' ' . $currency) . '</span>';
								echo '</div>';
							}
							echo '</div>';
							echo '</div>';
						}
						echo '</div>';
						echo '</div> <!-- girlinfo-section -->';
					} // if at least one rate
					?>
					<div class="clear"></div>
				</div> <!-- GIRL INFO RIGHT -->
				<div class="clear20"></div>
				<?php
				if (isset($_GET['add_tour']) && $_GET['add_tour'] == 'ok') {
					echo "<div class=\"ok rad5\">" . __('The tour has been added', 'escortwp') . "</div>";
				}
				?>
				<div id="tours"><?php
				$tours_args = array(
					'post_type' => 'tour',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'orderby' => 'meta_value_num',
					'meta_key' => 'start',
					'meta_query' => array(
						array(
							'key' => 'belongstoescortid',
							'value' => get_the_ID(),
							'compare' => '=',
							'type' => 'NUMERIC'
						),
						array(
							'key' => 'end',
							'value' => mktime(23, 59, 59, date("m"), date("d"), date("Y")),
							'compare' => '>=',
							'type' => 'NUMERIC'
						)
					)
				);
				$tours = new WP_Query($tours_args);
				if ($tours->have_posts()): ?>
						<div class="clear30"></div>
						<a name="tours"></a>
						<h4 class="l single-profile-tours-title"><?php _e('Tours', 'escortwp'); ?>:</h4>
						<div class="clear"></div>
						<?php if ($profile_author_id == $userid && $userstatus == $taxonomy_agency_url || current_user_can('level_10')) { ?>
							<div class="deletemsg r"></div>
						<?php } ?>
						<div class="clear10"></div>
						<div class="addedtours">
							<div class="tour tourhead">
								<div class="addedstart"><?php _e('Start', 'escortwp'); ?></div>
								<div class="addedend"><?php _e('End', 'escortwp'); ?></div>
								<div class="addedplace"><?php _e('Place', 'escortwp'); ?></div>
								<div class="addedphone"><?php _e('Phone', 'escortwp'); ?></div>
							</div>
							<?php
							while ($tours->have_posts()):
								$tours->the_post();
								unset($city, $state, $country, $location);

								$city = get_term(get_post_meta(get_the_ID(), 'city', true), $taxonomy_location_url);
								if ($city)
									$location[] = $city->name;

								if (showfield('state')) {
									$state = get_term(get_post_meta(get_the_ID(), 'state', true), $taxonomy_location_url);
									if ($state) {
										$location[] = $state->name;
									}
								}

								$country = get_term(get_post_meta(get_the_ID(), 'country', true), $taxonomy_location_url);
								if ($country)
									$location[] = $country->name;
								?>
								<div class="tour" id="tour<?php the_ID(); ?>">
									<span class="tour-info-mobile"><?php _e('Start', 'escortwp'); ?>:</span>
									<div class="addedstart">
										<?php echo date("d M Y", get_post_meta(get_the_ID(), 'start', true)); ?>
									</div>
									<span class="tour-info-mobile-clear"></span>

									<span class="tour-info-mobile"><?php _e('End', 'escortwp'); ?>:</span>
									<div class="addedend"><?php echo date("d M Y", get_post_meta(get_the_ID(), 'end', true)); ?>
									</div>
									<span class="tour-info-mobile-clear"></span>

									<span class="tour-info-mobile"><?php _e('Place', 'escortwp'); ?>:</span>
									<div class="addedplace"><?php echo implode(", ", $location); ?></div>
									<span class="tour-info-mobile-clear"></span>

									<span class="tour-info-mobile"><?php _e('Phone', 'escortwp'); ?>:</span>
									<div class="addedphone"><a
											href="tel:<?php echo get_post_meta(get_the_ID(), 'phone', true); ?>"><?php echo get_post_meta(get_the_ID(), 'phone', true); ?></a>
									</div>

									<?php
									if ($profile_author_id == $userid && $userstatus == $taxonomy_agency_url || current_user_can('level_10')) { ?>
										<span class="tour-info-mobile-clear"></span>
										<div class="addedbuttons"><i><?php the_ID(); ?></i><em><?php the_ID(); ?></em></div>
									<?php } ?>
								</div>
								<?php
							endwhile;
							?>
							<div class="clear30"></div>
						</div> <!-- ADDED TOURS -->
					<?php endif;
				wp_reset_postdata();
				// Step 9: Tours empty state
				if (!$tours->have_posts()): ?>
						<div class="profile-empty-state profile-empty-state--tours" role="status">
							<div class="profile-empty-state__illustration">
								<svg width="80" height="80" viewBox="0 0 80 80" fill="none"
									xmlns="http://www.w3.org/2000/svg">
									<!-- Suitcase body -->
									<rect x="15" y="30" width="50" height="35" rx="6" fill="rgba(255,255,255,0.03)"
										stroke="rgba(230,210,164,0.16)" stroke-width="1.5" />
									<!-- Handle -->
									<path d="M30 30V22C30 18.6863 32.6863 16 36 16H44C47.3137 16 50 18.6863 50 22V30"
										stroke="rgba(230,210,164,0.18)" stroke-width="1.5" fill="none" />
									<!-- Strap -->
									<line x1="15" y1="42" x2="65" y2="42" stroke="rgba(255,255,255,0.10)" stroke-width="1" />
									<!-- Airplane -->
									<path d="M58 18L66 14L64 20L68 28L60 24L54 30L56 22Z" fill="rgba(230,198,106,0.30)"
										stroke="rgba(230,198,106,0.46)" stroke-width="1" />
								</svg>
							</div>
								<h5 class="profile-empty-state__title"><?php _e('No tour dates yet', 'escortwp'); ?></h5>
								<p class="profile-empty-state__text"><?php _e('Message to check upcoming stops.', 'escortwp'); ?></p>
								<?php if ($phone): ?>
									<a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $phone); ?>?text=<?php echo urlencode(sprintf(__('Hi %s, I\'m interested in booking a tour. Are you available?', 'escortwp'), get_the_title())); ?>"
										class="profile-empty-state__cta" target="_blank" rel="noopener">
										<span class="icon icon-whatsapp"></span><?php _e('Book tour', 'escortwp'); ?>
									</a>
								<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php
					if ($profile_author_id == $userid) {
						$tours_args = array(
							'post_type' => 'tour',
							'post_status' => 'private',
							'posts_per_page' => -1,
							'order' => 'ASC',
							'orderby' => 'meta_value_num',
							'meta_key' => 'start',
							'meta_query' => array(
								array(
									'key' => 'belongstoescortid',
									'value' => get_the_ID(),
									'compare' => '=',
									'type' => 'NUMERIC'
								),
								array(
									'key' => 'end',
									'value' => mktime(23, 59, 59, date("m"), date("d"), date("Y")),
									'compare' => '>=',
									'type' => 'NUMERIC'
								),
								array(
									'key' => 'needs_payment',
									'value' => "1",
									'compare' => '=',
									'type' => 'NUMERIC'
								),
							)
						);
						$unpaid_tours = new WP_Query($tours_args);
						if ($unpaid_tours->have_posts()): ?>
							<div class="clear30"></div>
							<a name="tours"></a>
							<h4 class="l single-profile-tours-title"><?php _e('Unpaid Tours', 'escortwp'); ?>:</h4>
							<div class="clear"></div>
							<?php if ($profile_author_id == $userid && $userstatus == $taxonomy_agency_url || current_user_can('level_10')) { ?>
								<div class="deletemsg r"></div>
							<?php } ?>
							<div class="clear10"></div>
							<div class="addedtours">
								<div class="tour tourhead">
									<div class="addedstart"><?php _e('Start', 'escortwp'); ?></div>
									<div class="addedend"><?php _e('End', 'escortwp'); ?></div>
									<div class="addedplace"><?php _e('Place', 'escortwp'); ?></div>
									<div class="addedphone"><?php _e('Phone', 'escortwp'); ?></div>
								</div>
								<?php
								while ($unpaid_tours->have_posts()):
									$unpaid_tours->the_post();
									unset($city, $state, $country, $location);

									$city = get_term(get_post_meta(get_the_ID(), 'city', true), $taxonomy_location_url);
									if ($city)
										$location[] = $city->name;

									if (showfield('state')) {
										$state = get_term(get_post_meta(get_the_ID(), 'state', true), $taxonomy_location_url);
										if ($state) {
											$location[] = $state->name;
										}
									}

									$country = get_term(get_post_meta(get_the_ID(), 'country', true), $taxonomy_location_url);
									if ($country)
										$location[] = $country->name;
									?>
									<div class="tour" id="tour<?php the_ID(); ?>">
										<span class="tour-info-mobile"><?php _e('Start', 'escortwp'); ?>:</span>
										<div class="addedstart">
											<?php echo date("d M Y", get_post_meta(get_the_ID(), 'start', true)); ?>
										</div>
										<span class="tour-info-mobile-clear"></span>

										<span class="tour-info-mobile"><?php _e('End', 'escortwp'); ?>:</span>
										<div class="addedend"><?php echo date("d M Y", get_post_meta(get_the_ID(), 'end', true)); ?>
										</div>
										<span class="tour-info-mobile-clear"></span>

										<span class="tour-info-mobile"><?php _e('Place', 'escortwp'); ?>:</span>
										<div class="addedplace"><?php echo implode(", ", $location); ?></div>
										<span class="tour-info-mobile-clear"></span>

										<span class="tour-info-mobile"><?php _e('Phone', 'escortwp'); ?>:</span>
										<div class="addedphone"><a
												href="tel:<?php echo get_post_meta(get_the_ID(), 'phone', true); ?>"><?php echo get_post_meta(get_the_ID(), 'phone', true); ?></a>
										</div>

										<span class="tour-info-mobile-clear"></span>
										<div class="addedbuttons">
											<?php
											echo '<div class="pb"><a class="greenbutton payment-button rad25" href="' . get_permalink($this_post_id) . '?unpaid_tour=' . get_the_ID() . '">' . __('Pay for tour', 'escortwp') . '</a></div>';
											?>
										</div>
									</div>
									<?php
								endwhile;
								?>
								<div class="clear30"></div>
							</div> <!-- ADDED TOURS -->
						<?php endif;
						wp_reset_postdata();
					} // if($profile_author_id == $userid)
					
					if (get_option('hitcounter1')) {
						echo esc_page_hit_counter(get_the_ID());
					}
					?>

				</div> <!-- /tours -->
				<?php if (!get_option("hide1")) {
					$review_args = array(
						'post_type' => 'review',
						'posts_per_page' => -1,
						'meta_query' => array(
							array(
								'key' => 'escortid',
								'value' => get_the_ID(),
								'compare' => '=',
							),
						),
						);
						$reviews_query = new WP_Query($review_args);
						$review_count = (int) $reviews_query->post_count;
						$review_count_copy = $review_count > 0
							? sprintf(_n('%s review', '%s reviews', $review_count, 'escortwp'), number_format_i18n($review_count))
							: __('No reviews yet', 'escortwp');
					$review_form_action = isset($_POST['action']) ? sanitize_key(wp_unslash($_POST['action'])) : '';
					$postreview_status = isset($_GET['postreview']) ? sanitize_key(wp_unslash($_GET['postreview'])) : '';
					?>
					<div class="clear20" id="reviews"></div>

					<section class="profile-reviews-panel<?php echo $review_count > 0 ? ' has-reviews' : ' is-empty'; ?>"
						aria-labelledby="addreviewsection">
						<div class="profile-reviews-panel__header">
							<div class="profile-reviews-panel__title-wrap">
								<div class="profile-reviews-panel__title-row">
									<h4 class="l" id="addreviewsection"><?php _e('Reviews', 'escortwp'); ?></h4>
									<?php if ($review_count > 0) { ?>
										<span class="profile-reviews-panel__count"><?php echo esc_html($review_count_copy); ?></span>
									<?php } ?>
								</div>
								<div class="profile-review-entry"
									data-selected-template="<?php echo esc_attr__('You selected %s stars', 'escortwp'); ?>"
									data-selected-score="0">
									<div class="profile-review-entry__stars" role="group"
										aria-label="<?php esc_attr_e('Rate this experience', 'escortwp'); ?>">
										<?php for ($review_score = 1; $review_score <= 5; $review_score++) { ?>
											<button type="button" class="profile-review-entry__star"
												data-review-score="<?php echo esc_attr($review_score); ?>"
												aria-label="<?php echo esc_attr(sprintf(__('Rate %s out of 5', 'escortwp'), $review_score)); ?>"
												aria-pressed="false">
												<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
													<path d="M12 2.75l2.85 5.78 6.38.93-4.61 4.49 1.09 6.35L12 17.29 6.29 20.3l1.09-6.35L2.77 9.46l6.38-.93L12 2.75z" />
												</svg>
											</button>
										<?php } ?>
									</div>
									<div class="profile-review-entry__helper"><?php _e('Tap a star to rate this experience', 'escortwp'); ?></div>
								</div>
								<?php
								if (get_option("escortid" . $profile_author_id) == $taxonomy_agency_url && !get_option("hide3")) {
									echo '<a href="' . get_permalink(get_option("agencypostid" . $profile_author_id)) . '" class="reviewthegency profile-review-entry__secondary"><span class="icon-plus-circled"></span>' . sprintf(esc_html__('Review the %s', 'escortwp'), $taxonomy_agency_name) . '</a>';
								}
								?>
							</div>
						</div>

						<?php if ($review_count > 0): ?>
							<div class="profile-reviews-list">
								<?php
								while ($reviews_query->have_posts()):
									$reviews_query->the_post();
									$rating_number = (float) get_post_meta(get_the_ID(), 'rateescort', true);
									$rating_number = $rating_number > 0 ? $rating_number : (float) get_post_meta(get_the_ID(), 'rateagency', true);
									$reviewer_name = trim((string) get_the_author_meta('display_name'));
									$reviewer_avatar = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $reviewer_name), 0, 2));
									$reviewer_avatar = $reviewer_avatar !== '' ? $reviewer_avatar : 'GU';
									$reviewer_mask = $reviewer_name !== '' ? strtoupper(substr($reviewer_name, 0, 1)) . '***' : __('Guest', 'escortwp');
									?>
									<article class="review-wrapper profile-review-card rad5">
										<div class="profile-review-card__header">
											<div class="profile-review-card__identity">
												<span class="profile-review-card__avatar"><?php echo esc_html($reviewer_avatar); ?></span>
												<div class="profile-review-card__identity-copy">
													<span class="profile-review-card__name"><?php echo esc_html($reviewer_mask); ?></span>
													<span class="profile-review-card__meta">
														<?php
														printf(
															esc_html__('Posted on %s', 'escortwp'),
															esc_html(get_the_time('d M Y'))
														);
														?>
													</span>
												</div>
											</div>

											<div class="profile-review-card__rating"
												aria-label="<?php echo esc_attr(sprintf(__('Rated %s out of 5', 'escortwp'), number_format_i18n($rating_number, 0))); ?>">
												<span class="profile-review-card__rating-icon" aria-hidden="true">
													<svg viewBox="0 0 24 24" focusable="false">
														<path d="M12 2.75l2.85 5.78 6.38.93-4.61 4.49 1.09 6.35L12 17.29 6.29 20.3l1.09-6.35L2.77 9.46l6.38-.93L12 2.75z" />
													</svg>
												</span>
												<span class="profile-review-card__rating-value"><?php echo esc_html(number_format_i18n($rating_number, 0)); ?></span>
												<span class="profile-review-card__rating-scale">/5</span>
											</div>
										</div>

										<div class="profile-review-card__meter" aria-hidden="true">
											<?php for ($segment = 1; $segment <= 5; $segment++) { ?>
												<span class="<?php echo $rating_number >= $segment ? 'is-active' : ''; ?>"></span>
											<?php } ?>
										</div>

										<div class="profile-review-card__body">
											<?php the_content(); ?>
										</div>

										<?php edit_post_link(__('Edit review', 'escortwp'), '<div class="profile-review-card__edit">', '</div>'); ?>
									</article>
								<?php endwhile; ?>
							</div>
						<?php else: ?>
							<div class="profile-empty-state profile-empty-state--reviews" role="status">
								<div class="profile-empty-state__illustration">
									<svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg"
										aria-hidden="true">
										<circle cx="32" cy="32" r="22" stroke="rgba(230,210,164,0.18)" stroke-width="1.5" />
										<path d="M32 19.5L35.53 26.65L43.42 27.8L37.71 33.37L39.06 41.25L32 37.54L24.94 41.25L26.29 33.37L20.58 27.8L28.47 26.65L32 19.5Z"
											stroke="rgba(230,198,106,0.52)" stroke-width="1.5" stroke-linejoin="round" />
										<path d="M24 46H40" stroke="rgba(255,255,255,0.16)" stroke-width="1.5" stroke-linecap="round" />
									</svg>
								</div>
								<h5 class="profile-empty-state__title"><?php _e('No reviews yet', 'escortwp'); ?></h5>
								<p class="profile-empty-state__text"><?php _e('Be the first to leave a rating.', 'escortwp'); ?></p>
							</div>
						<?php endif; ?>
					</section>

					<?php wp_reset_postdata(); ?>

					<?php
					if ($postreview_status === 'ok') {
						echo '<div class="clear"></div>';
						echo '<div class="ok rad25">';
						if (get_option("manactivesc") == "1") {
							echo __('Your review will be read by our staff and published soon.', 'escortwp') . '<br />';
						}
						echo __('Thank you for posting.', 'escortwp');
						echo '</div>';
					}
					?>

					<div class="addreviewform registerform<?php if (isset($err) && $review_form_action === 'addreview' && $postreview_status !== 'ok') {
					} else {
						echo ' hide';
					} ?>">
						<?php
						if (!is_user_logged_in()) {
							echo '<div class="err rad25">' . __('You need to', 'escortwp') . ' <a href="' . get_permalink(get_option('main_reg_page_id')) . '">' . __('register', 'escortwp') . '</a> ' . __('or', 'escortwp') . ' <a href="' . wp_login_url(get_permalink()) . '">' . __('login', 'escortwp') . '</a> ' . __('to be able to post a review', 'escortwp') . '</div>';
						} else {
							if ($userstatus == "member" || current_user_can('level_10')) {
								if (did_user_post_review($userid, get_the_ID()) == true) {
									echo '<div class="err rad25">' . sprintf(esc_html__('You can\'t post more than one review for the same %s.', 'escortwp'), $taxonomy_profile_name) . '</div>';
								} else {
									if (payment_plans('vip', 'extra', 'hide_review_form') && !get_user_meta($userid, "vip", true) && !current_user_can('level_10') && $profile_author_id != $userid) {
										echo '<div class="clear5"></div>';
										echo '<div class="lockedsection rad5">';
										echo __('You need to be a VIP member to be able to post a review', 'escortwp') . ".<br />";
										echo __('VIP status costs', 'escortwp') . ' <strong>' . format_price('vip', 'small') . "</strong><br />";
										if (payment_plans('vip', 'duration')) {
											echo __('Your VIP status will be active for', 'escortwp') . ' <strong>' . $payment_duration_a[payment_plans('vip', 'duration')][0] . '</strong> ';
										}
										echo '<div class="clear20"></div>';
										echo '<div class="text-center">' . generate_payment_buttons("vip", $userid, __('Upgrade to VIP', 'escortwp')) . "</div> <!--center-->";
										echo '<div class="clear5"></div>';
										echo '<small>' . format_price('vip') . '</small>';
										echo '</div>';
									} else {
										?>
										<?php if (isset($ok) && $review_form_action === 'addreview') {
											echo "<div class=\"ok rad25\">$ok</div>";
										} ?>
										<?php if (isset($err) && $review_form_action === 'addreview') {
											echo "<div class=\"err rad25\">$err</div>";
										} ?>
										<form action="<?php echo get_permalink(get_the_ID()); ?>#addreview" method="post"
											class="form-styling profile-review-form">
											<?php closebtn(); ?>
											<div class="clear10"></div>
											<input type="hidden" name="action" value="addreview" />
											<div class="form-label">
												<label
													for="rateescort"><?php printf(esc_html__('Rate the %s', 'escortwp'), $taxonomy_profile_name); ?>:
													<i>*</i></label>
												<p class="profile-review-form__intro"><?php _e('Choose the score that best matches the full experience.', 'escortwp'); ?></p>
											</div>
											<?php
											if (!isset($rateescort)) {
												$rateescort = "";
											}
											?>
											<div class="form-input form-input-rating">
												<label class="rating-choice" for="rateescort5">
													<input type="radio" id="rateescort5" name="rateescort" value="5"
														data-rating-label="<?php esc_attr_e('Exceptional', 'escortwp'); ?>"
														data-rating-hint="<?php esc_attr_e('Seamless and worth recommending', 'escortwp'); ?>" <?= $rateescort == "5" ? ' checked' : "" ?> />
													<span class="rating-choice__card">
														<span class="rating-choice__value">5</span>
														<span class="rating-choice__copy">
															<span class="rating-choice__title"><?php _e('Exceptional', 'escortwp'); ?></span>
															<span class="rating-choice__hint"><?php _e('Seamless and worth recommending', 'escortwp'); ?></span>
														</span>
													</span>
												</label>
												<label class="rating-choice" for="rateescort4">
													<input type="radio" id="rateescort4" name="rateescort" value="4"
														data-rating-label="<?php esc_attr_e('Great', 'escortwp'); ?>"
														data-rating-hint="<?php esc_attr_e('Strong impression overall', 'escortwp'); ?>" <?= $rateescort == "4" ? ' checked' : "" ?> />
													<span class="rating-choice__card">
														<span class="rating-choice__value">4</span>
														<span class="rating-choice__copy">
															<span class="rating-choice__title"><?php _e('Great', 'escortwp'); ?></span>
															<span class="rating-choice__hint"><?php _e('Strong impression overall', 'escortwp'); ?></span>
														</span>
													</span>
												</label>
												<label class="rating-choice" for="rateescort3">
													<input type="radio" id="rateescort3" name="rateescort" value="3"
														data-rating-label="<?php esc_attr_e('Good', 'escortwp'); ?>"
														data-rating-hint="<?php esc_attr_e('Solid, with room to improve', 'escortwp'); ?>" <?= $rateescort == "3" ? ' checked' : "" ?> />
													<span class="rating-choice__card">
														<span class="rating-choice__value">3</span>
														<span class="rating-choice__copy">
															<span class="rating-choice__title"><?php _e('Good', 'escortwp'); ?></span>
															<span class="rating-choice__hint"><?php _e('Solid, with room to improve', 'escortwp'); ?></span>
														</span>
													</span>
												</label>
												<label class="rating-choice" for="rateescort2">
													<input type="radio" id="rateescort2" name="rateescort" value="2"
														data-rating-label="<?php esc_attr_e('Below average', 'escortwp'); ?>"
														data-rating-hint="<?php esc_attr_e('Several things missed the mark', 'escortwp'); ?>" <?= $rateescort == "2" ? ' checked' : "" ?> />
													<span class="rating-choice__card">
														<span class="rating-choice__value">2</span>
														<span class="rating-choice__copy">
															<span class="rating-choice__title"><?php _e('Below average', 'escortwp'); ?></span>
															<span class="rating-choice__hint"><?php _e('Several things missed the mark', 'escortwp'); ?></span>
														</span>
													</span>
												</label>
												<label class="rating-choice" for="rateescort1">
													<input type="radio" id="rateescort1" name="rateescort" value="1"
														data-rating-label="<?php esc_attr_e('Poor', 'escortwp'); ?>"
														data-rating-hint="<?php esc_attr_e('Would not recommend this experience', 'escortwp'); ?>" <?= $rateescort == "1" ? ' checked' : "" ?> />
													<span class="rating-choice__card">
														<span class="rating-choice__value">1</span>
														<span class="rating-choice__copy">
															<span class="rating-choice__title"><?php _e('Poor', 'escortwp'); ?></span>
															<span class="rating-choice__hint"><?php _e('Would not recommend this experience', 'escortwp'); ?></span>
														</span>
													</span>
												</label>
											</div> <!-- rating -->
											<div class="profile-review-form__selection"
												data-default-label="<?php esc_attr_e('Choose a score', 'escortwp'); ?>"
												data-default-hint="<?php esc_attr_e('Choose the score that best matches the full experience.', 'escortwp'); ?>"
												data-selected-score="<?php echo esc_attr($rateescort ? (int) $rateescort : 0); ?>"
												aria-live="polite">
												<span class="profile-review-form__selection-label"><?php _e('Selected rating', 'escortwp'); ?></span>
												<strong class="profile-review-form__selection-value"><?php _e('Choose a score', 'escortwp'); ?></strong>
												<span class="profile-review-form__selection-hint"><?php _e('Choose the score that best matches the full experience.', 'escortwp'); ?></span>
											</div>
											<div class="formseparator"></div>

											<div class="form-label">
												<label for="reviewtext"><?php _e('Comment', 'escortwp'); ?>: <i>*</i></label>
											</div>
											<div class="form-input">
												<?php if (!isset($reviewtext)) {
													$reviewtext = "";
												} ?>
												<textarea name="reviewtext" class="textarea longtextarea" rows="7"
													id="reviewtext"
													placeholder="<?php esc_attr_e('Share what stood out, how communication felt, and anything future clients should know.', 'escortwp'); ?>"><?php echo $reviewtext; ?></textarea>
												<div clas="clear"></div>
												<small class="l"><?php _e('html code will be removed', 'escortwp'); ?></small>
												<div class="charcount hides r">
													<div id="barbox" class="rad25">
														<div id="bar"></div>
													</div>
													<div id="count"></div>
												</div>
											</div> <!-- review text -->
											<div class="formseparator"></div>

											<div class="text-center">
												<div class="clear10"></div>
												<input type="submit" name="submit" value="<?php _e('Submit review', 'escortwp'); ?>"
													class="profile-review-form__submit" />
											</div> <!--center-->
										</form>
										<?php
									}
								}
							} else {
								echo '<div class="err rad25">' . __('Your user type is not allowed to post a review here', 'escortwp') . '</div>';
							}
						}
						?>
					</div> <!-- ADD REVIEW FORM-->
				<?php } // if !get_option("hide1") ?>

				<?php
				if (current_user_can('level_10')) {
					echo '<div class="admin-edit-link" style="order:9;grid-column:1/-1">';
					edit_post_link(__('Edit in WordPress', 'escortwp'));
					echo '</div>';
				}

				show_report_profile_button($this_post_id);
				?>
			</div> <!-- GIRL SINGLE -->

			<!-- Ad Zone On Listing Page -->
			<br>

			<div class="ad-container">
				<div class="ad-item"><?php the_ad_group(1015); ?></div>
				<div class="ad-item"><?php the_ad_group(1016); ?></div>
				<div class="ad-item"><?php the_ad_group(1017); ?></div>
				<div class="ad-item"><?php the_ad_group(1018); ?></div>
			</div>

			<!-- End Ad Zone -->

			<br>
			<div class="related_profiles">

					<h3 class="profile-title" title="Related Profiles" style="text-align: left !important;"><?php _e('Recommended Escorts', 'escortwp'); ?></h3>
				<?php
				$taxonomy_location = wp_get_post_terms(get_the_ID(), $taxonomy_location_url);

				//echo $taxonomy_location_url; 
				$country_id = $taxonomy_location[0]->term_taxonomy_id;
				$city_id = $taxonomy_location[0]->parent;
				$post_id = (int) get_the_ID();
				$post_id_arr = array($post_id);

				$args = array(
					'post_type' => $taxonomy_profile_url,
					'post__not_in' => $post_id_arr,
					'orderby' => 'rand',
					'posts_per_page' => 3,
					'tax_query' => array(
						array(
							'taxonomy' => $taxonomy_location_url,   // taxonomy name
							'field' => 'term_id',           		// term_id, slug or name
							'terms' => $city_id,                 // term id, term slug or term name
						)
					),
					'meta_query' => array(array('key' => 'featured'))
				);
				$premium_profiles = new WP_Query($args);


				$i = "1";
				//echo "<pre>";
				//print_r($premium_profiles); die;
				$firstPosts = array();
				global $post;
				if ($premium_profiles->have_posts()):
					echo '<div class="profile-related-scroll">';
					while ($premium_profiles->have_posts()):
						$premium_profiles->the_post();
						$firstPosts[] = $post_id;
						include(get_template_directory() . '-child/loop-show-profile.php');
					endwhile;
					echo '</div>';
				else:
					echo '<div class="alert alert_warning">No additional escorts in the same region.</div>';
				endif;
				wp_reset_postdata();
				?>
				<div class="clear"></div>
			</div>

			<!-- Recently Viewed -->
			<div class="recently_viewed_profiles">
				<br>
				<h3 class="profile-title" title="Recently Viewed" style="text-align: left !important;"><?php _e('Recently Viewed', 'escortwp'); ?></h3>
				<div class="rv-scroll-wrapper">
					<div id="rv-cards"></div>
				</div>
				<div class="clear"></div>
				<div class="clear"></div>
			</div>

			<script>
				(function () {
					var CURRENT_ID = <?php echo (int) get_the_ID(); ?>;
					var KEY = 'escortwp_rv_ids';
					try {
						// Read existing list
						var arr = [];
						var raw = localStorage.getItem(KEY);
						if (raw) {
							try { arr = JSON.parse(raw) || []; } catch (e) { arr = []; }
						}
						// Remove CURRENT if present, put it at the front
						arr = arr.filter(function (x) { return Number.isInteger(x) ? true : /^\d+$/.test(x); })
							.map(function (x) { return parseInt(x, 10); })
							.filter(function (id) { return id && id !== CURRENT_ID; });
						arr.unshift(CURRENT_ID);
						// Deduplicate & cap
						var seen = {};
						arr = arr.filter(function (id) { if (seen[id]) return false; seen[id] = 1; return true; });
						if (arr.length > 20) arr = arr.slice(0, 20);
						// Save back
						localStorage.setItem(KEY, JSON.stringify(arr));

						// Prepare list to DISPLAY (exclude current, up to 6)
						var toShow = arr.filter(function (id) { return id !== CURRENT_ID; }).slice(0, 6);
						if (!toShow.length) return; // nothing to render yet

						// Fetch HTML for those IDs
						var data = new FormData();
						data.append('action', 'escortwp_recently_viewed');
						data.append('ids', toShow.join(','));

						// admin-ajax URL
						var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

						fetch(ajaxurl, { method: 'POST', body: data, credentials: 'same-origin' })
							.then(function (r) { return r.text(); })
							.then(function (html) {
								var box = document.getElementById('rv-cards');
								if (box && html) box.innerHTML = html;
							})
							.catch(function () { /* silent */ });
					} catch (e) { }
				})();
			</script>

			<!-- Recently Viewed: cookie writer -->
			<script>
				(function () {
					try {
						var pid = <?php echo (int) get_the_ID(); ?>;
						var cname = 'escortwp_recently_viewed';
						var raw = (document.cookie.match('(^|;)\\s*' + cname + '\\s*=\\s*([^;]+)') || 0)[2] || '';
						var ids = raw ? raw.split(',').map(function (x) { return parseInt(x, 10) || 0; }) : [];
						// put current first, remove duplicates and itself
						ids = ids.filter(function (x) { return x && x !== pid; });
						ids.unshift(pid);
						// cap to 20
						if (ids.length > 20) ids = ids.slice(0, 20);
						var expires = new Date();
						expires.setTime(expires.getTime() + (30 * 24 * 60 * 60 * 1000)); // 30 days
						document.cookie = cname + '=' + ids.join(',') + ';expires=' + expires.toUTCString() + ';path=/;SameSite=Lax';
					} catch (e) { }
				})();
			</script>

			<!-- Mobile sticky CTA bar (shown/hidden via CSS media query, not PHP) -->
			<?php if ($has_contact_actions): ?>
				<div class="profile-mobile-cta" data-mobile-contact>
					<div class="profile-mobile-cta__header">
						<div class="profile-mobile-cta__name"><?php the_title(); ?></div>
						<button type="button" class="profile-mobile-cta__toggle" data-mobile-contact-toggle
							aria-expanded="false" aria-controls="<?php echo esc_attr($mobile_contact_panel_id); ?>">
							<span><?php esc_html_e('Book me now', 'escortwp'); ?></span>
							<svg class="profile-mobile-cta__toggle-icon" viewBox="0 0 16 16" aria-hidden="true">
								<path d="M4 6l4 4 4-4"></path>
							</svg>
						</button>
					</div>

					<div class="profile-mobile-cta__panel" id="<?php echo esc_attr($mobile_contact_panel_id); ?>"
						data-mobile-contact-panel hidden>
						<?php if ($has_call_action): ?>
							<a href="tel:<?php echo esc_attr($phone); ?>"
								class="profile-mobile-cta__btn profile-mobile-cta__btn--call">
								<span class="icon icon-phone"></span>
								<span><?php esc_html_e('Call', 'escortwp'); ?></span>
							</a>
						<?php endif; ?>

						<?php if ($has_whatsapp_action): ?>
							<a href="https://wa.me/<?php echo esc_attr($phone_digits); ?>?text=<?php echo rawurlencode(sprintf(__('Hi, I saw your profile on %s', 'escortwp'), get_site_url())); ?>"
								class="profile-mobile-cta__btn profile-mobile-cta__btn--wa" rel="noopener">
								<span class="icon icon-whatsapp"></span>
								<span><?php esc_html_e('WhatsApp', 'escortwp'); ?></span>
							</a>
						<?php endif; ?>

						<?php if ($has_viber_action): ?>
							<a href="viber://chat?number=<?php echo esc_attr($phone_digits); ?>"
								class="profile-mobile-cta__btn profile-mobile-cta__btn--viber" rel="noopener">
								<span class="icon icon-viber"></span>
								<span><?php esc_html_e('Viber', 'escortwp'); ?></span>
							</a>
						<?php endif; ?>

						<?php if ($has_chat_action): ?>
							<a href="<?php echo esc_url($chat_link); ?>" target="_blank" rel="noopener"
								class="profile-mobile-cta__btn profile-mobile-cta__btn--chat">
								<svg class="chat-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
									stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
									<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
								</svg>
								<span><?php esc_html_e('Chat', 'escortwp'); ?></span>
							</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

		</div> <!-- BODY BOX -->

		<div class="clear"></div>
	</div> <!-- BODY -->

</div> <!-- contentwrapper -->

<?php get_sidebar("left"); ?>
<?php get_sidebar("right"); ?>
<div class="clear"></div>
<?php get_footer(); ?>
