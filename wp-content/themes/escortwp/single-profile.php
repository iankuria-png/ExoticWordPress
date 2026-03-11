<?php
global $post, $taxonomy_location_url, $gender_a, $ethnicity_a, $haircolor_a, $hairlength_a, $bustsize_a, $build_a, $looks_a, $smoker_a, $availability_a, $languagelevel_a, $services_a, $currency_a, $taxonomy_profile_name, $taxonomy_agency_name, $taxonomy_profile_name_plural, $taxonomy_profile_url, $taxonomy_agency_url, $payment_duration_a;
$current_user = wp_get_current_user();
if(is_user_logged_in()) {
	$userid = $current_user->ID;
	$userstatus = get_option("escortid".$userid);
} else { $userid = "none"; $userstatus = "none"; }

$profile_author_id = $post->post_author;
$this_post_id = get_the_ID();

if (current_user_can('level_10')) {
	if (isset($_POST['action']) && $_POST['action'] == 'escortupgrade') {
		if (isset($_POST['delpremium'])) {
			update_post_meta(get_the_ID(), 'premium', "0");
			delete_post_meta(get_the_ID(), 'premium_expire');
			delete_post_meta(get_the_ID(), 'premium_renew');
			delete_post_meta(get_the_ID(), 'premium_since');

			$body_email = __('Hello','escortwp').'<br /><br />
'.__('The PREMIUM status has been remove from your profile.', 'escortwp').'<br /><br />
'.__('You can view your profile here','escortwp').':<br />
<a href="'.get_permalink(get_the_ID()).'">'.get_permalink(get_the_ID()).'</a>';
		}
		if (isset($_POST['premium'])) {
			update_post_meta(get_the_ID(), "premium", "1");
			update_post_meta(get_the_ID(), "premium_since", time());
			if ($_POST['premiumduration']) {
				$expiration = strtotime("+".$payment_duration_a[$_POST['premiumduration']][2]);
				if(get_post_meta(get_the_ID(), "premium_expire", true)) {
					$available_time = get_post_meta(get_the_ID(), 'premium_expire', true);
					if($available_time && $available_time > time()) { $expiration = $expiration + ($available_time - time()); }
				}
				update_post_meta(get_the_ID(), 'premium_expire', $expiration);
			} else {
				delete_post_meta(get_the_ID(), 'premium_expire');
				delete_post_meta(get_the_ID(), 'premium_renew');
			}

			$body_email = __('Hello','escortwp').'<br /><br />
'.__('Your profile has been upgraded to PREMIUM.', 'escortwp').'<br /><br />
'.__('You can view your profile here','escortwp').':<br />
<a href="'.get_permalink(get_the_ID()).'">'.get_permalink(get_the_ID()).'</a>';
		}

		if (isset($_POST['delfeatured'])) {
			update_post_meta(get_the_ID(), "featured", "0");
			delete_post_meta(get_the_ID(), 'featured_expire');
			delete_post_meta(get_the_ID(), 'featured_renew');

			$body_email = __('Hello','escortwp').'<br /><br />
'.__('The VIP status has been remove from your profile.', 'escortwp').'<br /><br />
'.__('You can view your profile here','escortwp').':<br />
<a href="'.get_permalink(get_the_ID()).'">'.get_permalink(get_the_ID()).'</a>';
		}
		if (isset($_POST['featured'])) {
			$featured = get_post_meta(get_the_ID(), "featured", true);
			if (!$featured || $featured == "0") { update_post_meta(get_the_ID(), "featured", "1"); }
			if ($_POST['featuredduration']) {
				$expiration = strtotime("+".$payment_duration_a[$_POST['featuredduration']][2]);
				if(get_post_meta(get_the_ID(), "featured_expire", true)) {
					$available_time = get_post_meta(get_the_ID(), 'featured_expire', true);
					if($available_time && $available_time > time()) { $expiration = $expiration + ($available_time - time()); }
				}
				update_post_meta(get_the_ID(), 'featured_expire', $expiration);
			} else {
				delete_post_meta(get_the_ID(), 'featured_expire');
				delete_post_meta(get_the_ID(), 'featured_renew');
			}

			$body_email = __('Hello','escortwp').'<br /><br />
'.__('Your profile has been upgraded to a VIP profile.', 'escortwp').'<br /><br />
'.__('You can view your profile here','escortwp').':<br />
<a href="'.get_permalink(get_the_ID()).'">'.get_permalink(get_the_ID()).'</a>';
		}

		if (isset($_POST['delexpiration'])) {
			delete_post_meta(get_the_ID(), 'escort_expire');
			delete_post_meta(get_the_ID(), 'escort_renew');

			$plan_name = get_option("escortid".$profile_author_id) == $taxonomy_agency_url ? "agescortreg" : 'indescreg';
			if(payment_plans($plan_name,'price')) {
				update_post_meta(get_the_ID(), 'needs_payment', "1");
				wp_update_post(array( 'ID' => get_the_ID(), 'post_status' => 'private' ));
			}
		}
		if (isset($_POST['expirationperiod'])) {
			if ($_POST['profileduration']) {
				$expiration = strtotime("+".$payment_duration_a[$_POST['profileduration']][2]);
				if(get_post_meta(get_the_ID(), "escort_expire", true)) {
					$available_time = get_post_meta(get_the_ID(), 'escort_expire', true);
					if($available_time && $available_time > time()) { $expiration = $expiration + ($available_time - time()); }
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

			$body_email = __('Hello','escortwp').'<br /><br />
'.sprintf(__('The status of you profile has changed from %s to %s on %s','escortwp'),"<b>".$first_text."</b>","<b>".$second_text."</b>",get_option("email_sitename")).'<br /><br />
'.__('You can view your profile here','escortwp').':<br />
<a href="'.get_permalink(get_the_ID()).'">'.get_permalink(get_the_ID()).'</a>';
		}

		if($body_email && get_option('ifemail9') == "1") {
			dolce_email("", "", get_the_author_meta('email', $profile_author_id), __('Profile status changed on','escortwp')." ".get_option("email_sitename"), $body_email);
		}
		wp_redirect(get_permalink(get_the_ID())); exit();
	} // escort upgrade

	if (isset($_POST['action']) && $_POST['action'] == 'adminnote') {
		$adminnote = wp_strip_all_tags($_POST['adminnote']);
		update_post_meta(get_the_ID(), "adminnote", $adminnote);
		wp_redirect(get_permalink(get_the_ID())); exit();
	} // adminnote

	if (isset($_POST['action']) && $_POST['action'] == 'activateprivateprofile') {
		$privprof = array( 'ID' => get_the_ID(), 'post_status' => 'publish' );
		delete_post_meta(get_the_ID(), "notactive");
		wp_update_post($privprof);
		wp_redirect(get_permalink(get_the_ID())); exit;
	} // activate private escort

	if (isset($_POST['action']) && $_POST['action'] == 'activateunpaidprofile') {
		if ($_POST['profileduration']) {
			$expiration = strtotime("+".$payment_duration_a[$_POST['profileduration']][2]);
			if(get_post_meta(get_the_ID(), "escort_expire", true)) {
				$available_time = get_post_meta(get_the_ID(), 'escort_expire', true);
				if($available_time && $available_time > time()) { $expiration = $expiration + ($available_time - time()); }
			}
			update_post_meta(get_the_ID(), 'escort_expire', $expiration);
		}

		$unpaidprof = array( 'ID' => get_the_ID(), 'post_status' => 'publish' );
		delete_post_meta(get_the_ID(), "needs_payment");
		wp_update_post($unpaidprof);
		wp_redirect(get_permalink(get_the_ID())); exit;
	} // activate unpaid profile
} // if admin

if ($userstatus == "member" || current_user_can('level_10')) {
	if (isset($_POST['action']) && $_POST['action'] == 'addreview') {
		$rateescort = (int)$_POST['rateescort'];
		if ($rateescort < 1 || $rateescort > 6) {
			$err .= sprintf(esc_html__('The %s rating is wrong. Please select again.','escortwp'),$taxonomy_profile_name)."<br />"; unset($rateescort);
		}

		$reviewtext = substr(stripslashes(wp_kses($_POST['reviewtext'], array())), 0, 5000);
		if (!$reviewtext) {
			$err .= __('You didn\'t write a review','escortwp')."<br />";
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
				$reviews_cat_id = term_exists( 'Reviews', "category" );
			}
			$reviews_cat_id = $reviews_cat_id['term_id'];
			$add_review = array(
				'post_title' => __('Review for','escortwp')." ".get_the_title(),
				'post_content' => $reviewtext,
				'post_status' => $reviewstatus,
				'post_author' => $userid,
				'post_category' => array($reviews_cat_id),
				'post_type' => 'review',
				'ping_status' => 'closed'
			);
			$add_review_id = wp_insert_post( $add_review );
			update_post_meta($add_review_id, "rateescort", $rateescort);
			update_post_meta($add_review_id, "escortid", get_the_ID());
			update_post_meta($add_review_id, "reviewfor", "profile");

			$reviewadminurl = admin_url('post.php').'?post='.$add_review_id.'&action=edit';
			if (get_option("manactivesc") == "1") {
				$new_review_email_title = __('A new review is waiting for approval on','escortwp')." ".get_option("email_sitename");
			} else {
				$new_review_email_title = sprintf(esc_html__('Someone wrote a %s review on','escortwp'),$taxonomy_profile_name).' '.get_option("email_sitename");
			}
			$body = __('Hello','escortwp').',<br />
'.sprintf(esc_html__('Someone wrote a %s review on.','escortwp'),$taxonomy_profile_name).' '.get_option("email_sitename").':<br /><br />
'.__('Read/Edit the review here','escortwp').':<br />
<a href="'.$reviewadminurl.'">'.$reviewadminurl.'</a><br />'.__('(to activate the review simply click te button "Publish")','escortwp');
			if(get_option("ifemail6") == "1" || get_option("manactivag") == "1") {
				dolce_email(null, null, get_bloginfo("admin_email"), $new_review_email_title, $body);
			}

			if (get_option("permalink_structure")) {
				wp_redirect(get_permalink(get_the_id())."?postreview=ok"); exit();
			} else {
				wp_redirect(get_permalink(get_the_id())."&postreview=ok"); exit();
			}
			
		}
	} // add review
} // if user status member

// delete an escort account
if (isset($_POST['action']) && $_POST['action'] == 'deleteescort' && ($profile_author_id == $userid || current_user_can('level_10'))) {
	if (!get_post_meta(get_the_ID(), "independent", true)) {
		$agency_id = get_option("agencypostid".$profile_author_id);
	}

	delete_profile(get_the_ID()); // delete escort and everything related to the profile

	if ($agency_id) {
		wp_redirect(get_permalink($agency_id)); exit();
	} else {
		wp_redirect(get_bloginfo("url")); exit();
	}
} // if admin


// set profile to private
if (isset($_POST['action']) && $_POST['action'] == 'settoprivate') {
	$new_post_status = get_post_status(get_the_ID()) == "publish" ? "private" : "publish";

	if(current_user_can('level_10')) {
		if(get_post_status(get_the_ID()) == "publish") {
			update_post_meta(get_the_ID(), 'notactive', '1');
		} else {
			delete_post_meta(get_the_ID(), "notactive");
		}
		wp_update_post(array('ID' => get_the_ID(), 'post_status' => $new_post_status));
	}

	if($profile_author_id == $userid && !get_post_meta(get_the_ID(), 'notactive', true) && !get_post_meta(get_the_ID(), 'needs_payment', true)) {
		wp_update_post(array('ID' => get_the_ID(), 'post_status' => $new_post_status));
	}

	wp_redirect(get_permalink(get_the_ID())); die();
}


//if the agency wants to edit the profile information process the data below
if (isset($_POST['action']) && $_POST['action'] == 'register') {
	if ($profile_author_id == $userid && $userstatus == $taxonomy_agency_url || current_user_can('level_10')) {
		$agencyid = $userid;
		$single_page = "yes";
		$escort_post_id = get_the_ID();
		include (get_template_directory() . '/register-independent-personal-info-process.php');
	} // if the escort was added by this user and if the user is an agency
} else {
	$agencyid = $userid;
	$escort_post_id = get_the_ID();
	$single_page = "yes";
	$escort = get_post($escort_post_id);

	$aboutyou = nl2br(do_shortcode($escort->post_content));
	$yourname = $escort->post_title;

	$phone = get_post_meta($escort_post_id, "phone", true);
	$phone_available_on = get_post_meta($escort_post_id, "phone_available_on", true);
	$escortemail = get_post_meta($escort_post_id, "escortemail", true);
	$website = get_post_meta($escort_post_id, "website", true);
	$instagram = get_post_meta($escort_post_id, "instagram", true);
	$snapchat = get_post_meta($escort_post_id, "snapchat", true);
	$twitter = get_post_meta($escort_post_id, "twitter", true);
	$facebook = get_post_meta($escort_post_id, "facebook", true);


	$city_data = wp_get_post_terms(get_the_ID(), $taxonomy_location_url);
	if($city_data && !is_wp_error($city_data)) {
		if(get_option('locationdropdown') == "1") {
			$city = $city_data[0]->term_id;
		} else {
			$city = get_term($city_data[0]->term_id, $taxonomy_location_url);
			$city = $city_data[0]->name;
		}

		$state_data = get_term($city_data[0]->parent, $taxonomy_location_url);
		if($state_data && !is_wp_error($state_data)) {
			if(get_option('locationdropdown') == "1") {
				$state = $state_data->term_id;
			} else {
				$state = get_term($state_data->term_id, $taxonomy_location_url);
				$state = $state_data->name;
			}
			$country_data = get_term($state_data->parent, $taxonomy_location_url);
			if(!is_wp_error($country_data)) {
				$country = $country_data->term_id;
			} else {
				$country = $state_data->term_id; unset($state);
			}
		}
	}

	$gender = get_post_meta($escort_post_id, "gender", true);
	$birthday = get_post_meta($escort_post_id, "birthday", true);
	$age = floor((time() - strtotime($birthday))/31556926);
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
	$education = get_post_meta(get_the_ID(),'education', true);
	$sports = get_post_meta(get_the_ID(),'sports', true);
	$hobbies = get_post_meta(get_the_ID(),'hobbies', true);
	$zodiacsign = get_post_meta(get_the_ID(),'zodiacsign', true);
	$sexualorientation = get_post_meta(get_the_ID(),'sexualorientation', true);
	$occupation = get_post_meta(get_the_ID(),'occupation', true);
}

if ($profile_author_id == $userid && $userstatus == $taxonomy_agency_url || current_user_can('level_10')) {
	// if the agency wants to add a tour to the escort then process the data below
	if (isset($_POST['action']) && ($_POST['action'] == 'addtour' || $_POST['action'] == 'edittour')) {
		$is_escort_page = "yes";
		$escort_post_id_for_tours = get_the_ID();
		include (get_template_directory() . '/register-independent-manage-my-tours-process-data.php');
		if($ok) { wp_redirect(get_permalink($escort_profile_id)."?add_tour=ok#tours"); exit; }
	}
} // if the escort was added by this user and if the user is an agency


if (isset($_POST['action']) && $_POST['action'] == "contactform") {
	if ($_POST['emails']) { $err .= "."; }

	if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && get_option("recaptcha5") && !is_user_logged_in()) { $err .= verify_recaptcha(); }

	if (is_user_logged_in()) {
		$contactformname = $current_user->display_name;
		$contactformemail = $current_user->user_email;
	} else {
		$contactformname = get_option("email_sitename");
		$contactformemail = $_POST['contactformemail'];
		if ($contactformemail) {
			if(!is_email($contactformemail)) { $err .= __('Your email address seems to be wrong','escortwp')."<br />"; }
		} else {
			$err .= __('Your email is missing','escortwp')."<br />";
		}
	}
	$contactformmess = substr(sanitize_textarea_field($_POST['contactformmess']), 0, 5000);
	if (!$contactformmess) { $err .= __('You need to write a message','escortwp')."<br />"; }

	if (!$err) {
		$body = __('Hello','escortwp').' '.get_the_author_meta('display_name', $profile_author_id).'<br /><br />
'.__('Someone sent you a message from','escortwp').' '.get_option("email_sitename").':<br />
<a href="'.get_permalink(get_the_ID()).'">'.get_permalink(get_the_ID()).'</a><br /><br />
'.__('Sender information','escortwp').':<br />
'.__('name','escortwp').': <b>'.$contactformname.'</b><br />
'.__('email','escortwp').': <b>'.$contactformemail.'</b><br />
'.__('message','escortwp').':<br />'.$contactformmess.'<br /><br />'.__('You can send a message back to this person by replying to this email.','escortwp');
		dolce_email($contactformname, $contactformemail, get_the_author_meta('user_email', $profile_author_id), __('Message from','escortwp')." ".get_option("email_sitename"), $body);
		unset($contactformname, $contactformemail, $contactformmess, $body);
		$ok = __('Message sent','escortwp');
	}
}

$current_tour = get_user_current_tour(get_the_ID());

get_header(); ?>

<div class="contentwrapper">
	<div class="body">
		<div class="bodybox profile-page">
			<?php
			if(isset($_GET['unpaid_tour']) && $_GET['unpaid_tour'] && $profile_author_id == $userid) {
				$unpaid_tour = get_post((int)$_GET['unpaid_tour']);
				if($unpaid_tour && get_post_meta($unpaid_tour->ID, 'needs_payment', true)) {
					echo '<div class="err rad25">';
					echo '<div class="clear10"></div>';
					printf(__('%s has been added, but it\'s not visible in our website yet.','escortwp'),$unpaid_tour->post_title);
					if (payment_plans('tours','price')) {
						echo "<br />".__('In order for the tour to be activated you must pay','escortwp')." ".format_price('tours', "small")."<br />"."\n";
						echo '<div class="clear20"></div>';
						echo generate_payment_buttons('tours',(int)$_GET['unpaid_tour'],__('Activate tour','escortwp'));
						echo '<div class="clear5"></div>';
						echo "<small>".format_price('tours')."</small>";
					}
					echo '<div class="clear10"></div>';
					echo '</div>';
				}
			}
			?>
			<?php if (isset($ok) && $ok && $_POST['action'] == 'edittour') { echo "<div class=\"ok rad25\">$ok</div>"; } ?>

			<script type="text/javascript">
			jQuery(document).ready(function($) {
				//add or remove from favorites
				$('.favbutton').on('click', function(){
					var escortid = $(this).attr('id');
					$('.favbutton').toggle();
					$.ajax({
						type: "GET",
						url: "<?php bloginfo('template_url'); ?>/ajax/add-remove-favorites.php",
						data: "id=" + escortid
					});
				});

				$('.addreview-button').on('click', function(){
					$('.addreviewform').slideDown("slow");
					$('.addreview').slideUp("slow");
					$('html,body').animate({ scrollTop: $('.addreviewform').offset().top }, { duration: 'slow', easing: 'swing'});
				});
			    if(window.location.hash == "#addreview") {
					// $('.addreviewform, .addreview').slideToggle("slow");
					$('html,body').animate({ scrollTop: $('#addreviewsection').offset().top }, { duration: 'slow', easing: 'swing'});
				}
				$('.addreviewform .closebtn').on('click', function(){
					$('.addreviewform, .addreview').slideToggle("slow");
				});

				count_review_text('#reviewtext');
				$("#reviewtext").keyup(function() {
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
					if(box.length <= charlimit) {
						$('#count').html(count);
						$("#reviewtext")
						$('#bar').animate( {
							"width": value+'%',
						}, 1);
					} else {
						$('#reviewtext').val(boxremove);
			            ourtextarea.scrollTop(
			                ourtextarea[0].scrollHeight - ourtextarea.height()
			            );
					}
					return false;
				}

				$('.sendemail').on('click', function(){
					$('.escortcontact').slideToggle("slow");
					$(this).slideToggle("slow");
				});
			    if(window.location.hash == "#contactform") {
					$('html,body').animate({ scrollTop: $('.escortcontact').offset().top }, { duration: 'slow', easing: 'swing'});
				}
				$('.escortcontact .closebtn').on('click', function(){
					$('.escortcontact').slideToggle("slow");
					$('.sendemail').slideToggle("slow");
				});


				<?php
				if($availability) {
					if(!in_array("1", $availability)) {
						echo '$(\'.girlinfo .hide-incall\').hide();';
					}
					if(!in_array("2", $availability)) {
						echo '$(\'.girlinfo .hide-outcall\').hide();';
					}
				}
				?>
			});
			</script>
			<?php
			// check if the user has any photos uploaded
			// create an array with all the photos to use later
			$photos = get_children( array('post_parent' => get_the_ID(), 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID') );
			$photos_left = get_option('maximgupload') - count($photos); $photos_left = (int)$photos_left;

			$videos = get_children( array('post_parent' => get_the_ID(), 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'video', 'order' => 'ASC', 'orderby' => 'menu_order ID') );
			$videos_left = get_option('maxvideoupload') - count($videos);

			if ($profile_author_id == $userid || current_user_can('level_10')) {
				include (get_template_directory() . '/register-agency-manage-escorts-option-buttons.php');
			}
			?>
		    <div class="girlsingle<?php if(isset($err) && $err && in_array($_POST['action'], array('adminnote', 'addtour', 'edittour', 'register'))) { echo " hide"; } ?>" itemscope itemtype ="http://schema.org/Person">
		    <div class="profile-header">
		    	<div class="profile-header-name text-center l">
			    	<h3 class="profile-title" title="<?php the_title_attribute(); ?>" itemprop="name"><?php the_title(); ?></h3>
			        <div class="girlsinglelabels">
						<?php
							$premium = get_post_meta(get_the_ID(), "premium", true);
							if ($premium == "1") { echo '<span class="orangebutton rad25">'.__('PREMIUM','escortwp').'</span>'; }

							$featured = get_post_meta(get_the_ID(), "featured", true);
							if ($featured == "1") { echo '<span class="pinkdegrade rad25">'.strtoupper(__('VIP','escortwp')).'</span>'; }

							$verified = get_post_meta(get_the_ID(), "verified", true);
							if ($verified == "1") { echo '<span class="greendegrade rad25">'.__('VERIFIED','escortwp').'</span>'; }

							$daysago = date("Y-m-d H:i:s", strtotime("-".get_option('newlabelperiod')." days"));
							if (get_the_time('Y-m-d H:i:s') > $daysago) {
								echo '<span class="pinkbutton rad25">'.__('NEW','escortwp').'</span>';
							}

							if(get_post_status(get_the_ID()) == "private") {
								echo '<span class="redbutton rad25">'.strtoupper(__('Private','escortwp')).'</span>';
							}
						?>
					</div> <!-- girlsinglelabels -->
			    	<?=show_online_label_html($profile_author_id)?>
				</div> <!-- profile-header-name -->
				<div class="profile-header-name-info rad5 r">
					<?php
		            if($height) {
		            	if(get_option("heightscale") == "imperial" && $height2 > 0) {
			            	echo '<div class="section-box"><span class="valuecolumn">'.$height2.'</span><b>'.(get_option("heightscale") == "imperial" ? "in" : "").'</b></div>';
		            	}
		            	echo '<div class="section-box"><span class="valuecolumn">'.$height.'</span><b>'.(get_option("heightscale") == "imperial" ? "ft" : "cm").'</b></div>';
		            }
		            if($weight) { echo '<div class="section-box"><span class="valuecolumn">'.$weight.'</span><b>'.(get_option("heightscale") == "imperial" ? "lb" : "kg").'</b></div>'; }
		            ?>
					<div class="section-box"><span class="valuecolumn"><?=$age?></span><b><?=__('years','escortwp')?></b></div>
				</div>
				<?php
				if(payment_plans('vip','extra','hide_contact_info') && !is_user_logged_in()) {
				} else {
					if(payment_plans('vip','extra','hide_contact_info') && !get_user_meta($userid, "vip", true) && !current_user_can('level_10') && $profile_author_id != $userid) {
					} else {
						if($phone) { ?>
							<div class="phone-box r">
								<div class="label"><?=__('call me','escortwp')?></div>
								<a class="" href="tel:<?=$phone?>" itemprop="telephone"><span class="icon icon-phone"></span><?=$phone?></a>
							</div>
							<?php if(is_array($phone_available_on) && count($phone_available_on) > 0) { ?>
							<div class="available-on r">
								<div class="label"><?=__('text me','escortwp')?></div>
								<?php
								foreach ($phone_available_on as $key => $value) {
									switch ($value) {
										case '1':
												echo '<a href="https://wa.me/'.preg_replace("/([^0-9])/", "", $phone).'?text='.urlencode(sprintf(__('Hi, I saw your profile on %s', 'escortwp'), get_site_url())).'"><span class="icon icon-whatsapp"></span></a>';
											break;

										case '2':
												echo '<a href="viber://chat?number='.preg_replace("/([^0-9])/", "", $phone).'"><span class="icon icon-viber"></span></a>';
											break;
									}
								}
								?>
							</div> <!-- available-on -->
							<?php } ?>
						<?php }
					} // if VIP or admin
				} // if contact section hidden and user not logged in
				?>
				<div class="clear10"></div>
			</div> <!-- profile-header -->

			<?php
			if ($adminnote) {
				echo '<div class="clear"></div>';
				echo '<div class="err rad25">'.$adminnote.'</div>';
			}
			?>
			<?php if ($profile_author_id == $userid || current_user_can('level_10')) { ?>
				<div class="clear10"></div>
				<div class="profile-page-no-media-wrapper profile-page-no-media-wrapper-photos <?=(get_option('allowvideoupload') == "1") ? " col50 l" : " col100"?>">
					<div class="profile-page-no-media profile-page-no-photos profile-page-no-photos-click rad3 col100 text-center" id="profile-page-no-photos">
						<div class="icon icon-picture"></div>
						<div class="for-browsers" data-mobile-text="<?php _e('Tap here to upload your images','escortwp'); ?>">
							<p><?php _e('Drag your images here to upload them','escortwp'); ?> <?php _e('or <u>Select from a folder</u>','escortwp'); ?></p>
						</div>
						<p class="max-photos"><?php printf(esc_html__('You can upload a maximum of %s images','escortwp'), '<b>'.$photos_left.'</b>'); ?></p>
						<div class="clear"></div>
					</div>
					<div class="profile_photos_button_container hide"><input id="profile_photos_upload" name="file_upload" type="file" /></div>
				</div> <!-- profile-page-no-media-wrapper -->

				<?php if(get_option('allowvideoupload') == "1") { ?>
				<div class="profile-page-no-media-wrapper profile-page-no-media-wrapper-videos col50 r">
					<div class="profile-page-no-media profile-page-no-videos profile-page-no-videos-click rad3 col100 text-center" id="profile-page-no-videos">
						<div class="icon icon-film"></div>
						<div class="for-browsers" data-mobile-text="<?php _e('Tap here to upload your videos','escortwp'); ?>">
							<p><?php _e('Drag your videos here to upload them','escortwp'); ?> <?php _e('or <u>Select from a folder</u>','escortwp'); ?></p>
						</div>
						<p class="max-videos"><?php printf(esc_html__('You can upload a maximum of %s videos','escortwp'), '<b>'.$videos_left.'</b>'); ?></p>
						<div class="clear"></div>
					</div>
					<div class="profile_videos_button_container hide"><input id="profile_videos_upload" name="file_upload" type="file" /></div>
				</div> <!-- profile-page-no-media-wrapper -->
				<?php } ?>
				<div class="clear20"></div>
			<?php } ?>

			<?php
			if($photos || $videos) { //we only show the code for the main image and the thumbs if the user has at least one image
				if ($profile_author_id == $userid || current_user_can('level_10')) {
					echo '<div class="image-buttons-legend">
						<div><span class="button-main-image icon-ok"></span> '.__('Mark as main image','escortwp').'</div>
						<div><span class="button-delete icon-cancel"></span> '.__('Delete image','escortwp').'</div>
					</div>';
				} // if user is author
			?>
			<div class="clear10"></div>
            <div class="thumbs" itemscope itemtype="http://schema.org/ImageGallery">
				<?php
				$nrofphotos = count($photos) - 1; //nr of photos left if we exclude the main big image
				$nrofvideos = count($videos);
				if(count($photos) > 0 || $nrofvideos > 0) {
					if($nrofvideos > 0) {
						$and_videos = ' '.sprintf(esc_html__('and %s more videos','escortwp'),'<span class="nr rad5 greendegrade">'.$nrofvideos.'</span>');
					}
					$main_image_id = get_post_meta(get_the_ID(), "main_image_id", true);
					if($main_image_id < 1 || !get_post($main_image_id)) {
						$firstphoto = reset($photos);
						if ($firstphoto) {
							$main_image_id = $firstphoto->ID;
							update_post_meta(get_the_ID(), "main_image_id", $main_image_id);
						}
					}

					$main_image_url = wp_get_attachment_image_src((int)$main_image_id, 'main-image-thumb');
					if($main_image_url[3] != "1") {
						require_once( ABSPATH . 'wp-admin/includes/image.php' );
						$attach_data = wp_generate_attachment_metadata($main_image_id, get_attached_file($main_image_id));
						wp_update_attachment_metadata($main_image_id, $attach_data);
						$main_image_url = wp_get_attachment_image_src($main_image_id, 'main-image-thumb');
					}
					if(!$main_image_url[0]) {
						$main_image_url[0] = get_template_directory_uri().'/i/no-image.png';
					}
					$bigimage  = '<div class="bigimage">';
					$bigimage .= '<img src="'.$main_image_url[0].'" class="rad3" alt="'.get_the_title().'" />'."\n";
		            $bigimage .= '</div> <!-- bigimage -->';

					if(payment_plans('vip','extra','hide_photos') && !is_user_logged_in()) {
						echo $bigimage;
						echo '<div class="clear"></div>';
						echo '<div class="lockedsection rad5">';
							echo '<div class="icon icon-lock vcenter l"></div>';
							echo sprintf(esc_html__('This %s has %s more photos','escortwp'),$taxonomy_profile_name, '<span class="nr rad5 greendegrade">'.$nrofphotos.'</span>').$and_videos.'.<br />';
							echo __('You need to','escortwp').' <a href="'.get_permalink(get_option('main_reg_page_id')).'">'.__('register','escortwp').'</a> '.__('or','escortwp').' <a href="'.wp_login_url(get_permalink()).'">'.__('login','escortwp').'</a> '.__('to be able to view the other photos','escortwp').'.';
							echo '<div class="clear"></div>';
						echo '</div> <!-- lockedsection -->';
					} else {
						if(payment_plans('vip','extra','hide_photos') && !get_user_meta($userid, "vip", true) && !current_user_can('level_10') && $profile_author_id != $userid) {
							echo $bigimage;
							echo '<div class="clear5"></div>';
							echo '<div class="lockedsection rad5">';
								echo '<div class="icon icon-lock vcenter l"></div>';
								printf(esc_html__('This %1$s has %2$s more photos','escortwp'),$taxonomy_profile_name,'<span class="nr rad5 greendegrade">'.$nrofphotos.'</span>');
								echo $and_videos.'.<br />';
								echo __('You need to be a VIP member to see the rest of the photos','escortwp').".<br />";
								echo __('VIP status costs','escortwp').' <strong>'.format_price('vip','small')."</strong><br />";
								if(payment_plans('vip','duration')) {
									echo __('Your VIP status will be active for','escortwp').' <strong>'.$payment_duration_a[payment_plans('vip','duration')][0].'</strong> ';
								}
								echo '<div class="clear20"></div>';
								echo '<div class="text-center">'.generate_payment_buttons("vip", $userid, __('Upgrade to VIP','escortwp'))."</div> <!--center-->";
								echo '<div class="clear5"></div>';
								echo '<small>'.format_price('vip').'</small>';
							echo '</div>';
						} else {
							// get the videos uploaded
							foreach ($videos as $video) {
								if ($profile_author_id == $userid || current_user_can('level_10')) {
									$imagebuttons = '<span class="edit-buttons"><span class="icon button-delete icon-cancel rad50"></span></span>';
								}

								echo '<div class="profile-video-thumb-wrapper"><div class="profile-img-thumb profile-video-thumb rad3"  id="'.$video->ID.'" style="background: url('.$video->guid.'.jpg) center no-repeat; background-size: cover;">';
								echo 	$imagebuttons;

								if(get_post_meta($video->ID, 'processing', true) && !is_video_processing_running(get_post_meta($video->ID, 'processing', true))) {
									delete_post_meta($video->ID, 'processing');
									unlink(get_post_meta($video->ID, "original_file", true));
									delete_post_meta($video->ID, 'original_file');

								}
								$file_path = get_attached_file($video->ID);
								$file_path_thumb = $file_path.".jpg";
								if(!file_exists($file_path_thumb)) {
									$output = shell_exec("ffmpeg -i $file_path");
									$videoresizeheight = get_option("videoresizeheight") ? get_option("videoresizeheight") : '400';
									shell_exec("ffmpeg -y -i $file_path -f mjpeg -vframes 1 -ss 00:00:03.435 -vf scale=".$videoresizeheight.":-1 $file_path_thumb");
								}

								if(get_post_meta($video->ID, 'processing', true)) {
									if ($profile_author_id == $userid || current_user_can('level_10')) {
										echo '<span class="video-processing rad3">'.__('this video is still processing','escortwp').'</span>';
										echo '<img data-original-url="'.get_template_directory_uri().'/i/video-placeholder.svg" class="mobile-ready-img rad3" alt="'.get_the_title().'" data-responsive-img-url="'.get_template_directory_uri().'/i/video-placeholder-mobile.svg" />';
									}
								} else {
									echo '<div id="'.preg_replace("/([^a-zA-Z0-9])/", "", $video->post_title).'" class="video-player-lightbox text-center hide" itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';
									echo 	'<meta itemprop="thumbnailUrl" content="'.$video->guid.'.jpg" />';
									echo 	'<meta itemprop="contentURL" content="'.$video->guid.'" />';
									echo 	'<meta itemprop="name" content="'.get_the_title().'" />';
									echo 	'<meta itemprop="description" content="'.get_the_title().'" />';
									echo 	'<meta itemprop="uploadDate" content="'.date("c", strtotime($video->post_date)).'" />';
									echo 	'<video height="100%" width="100%" controls>';
									echo 		'<source src="'.$video->guid.'" type="video/mp4">';
									echo 		__('Your browser does not support the video tag.','escortwp');
									echo 	'</video> ';
									echo '</div>';

									echo '<a href="#'.preg_replace("/([^a-zA-Z0-9])/", "", $video->post_title).'" data-fancybox="profile-video">';
									echo 	'<img src="'.$video->guid.'.jpg" class="hide video-image-th" />';
									echo 	'<img src="'.get_template_directory_uri().'/i/video-placeholder.svg" class="video-image-play" />';
									echo '</a>';
								}

								echo '<div class="clear"></div></div></div>'."\n";
							}
							if(count($videos) > 0) {
								echo '<div class="clear10"></div>';
							}
							// get the images uploaded
							foreach ($photos as $photo) {
								$photo_th_url = wp_get_attachment_image_src($photo->ID, 'profile-thumb');
								if($photo_th_url[3] != "1") {
									require_once( ABSPATH . 'wp-admin/includes/image.php' );
									$attach_data = wp_generate_attachment_metadata($photo->ID, get_attached_file($photo->ID));
									wp_update_attachment_metadata($photo->ID, $attach_data);
									$photo_th_url = wp_get_attachment_image_src($photo->ID, 'profile-thumb');
								}

								$photo_th_mobile_url = wp_get_attachment_image_src($photo->ID, 'profile-thumb-mobile');
								if($photo_th_mobile_url[3] != "1") {
									require_once( ABSPATH . 'wp-admin/includes/image.php' );
									$attach_data = wp_generate_attachment_metadata($photo->ID, get_attached_file($photo->ID));
									wp_update_attachment_metadata($photo->ID, $attach_data);
									$photo_th_mobile_url = wp_get_attachment_image_src($photo->ID, 'profile-thumb-mobile');
								}

								if ($profile_author_id == $userid || current_user_can('level_10')) {
									$style_main_img_btn = ($photo->ID == $main_image_id) ? ' style="display: none"' : "";
									$imagebuttons = '<span class="edit-buttons"><span class="icon button-delete icon-cancel rad50"></span><span class="icon button-main-image icon-ok rad50"'.$style_main_img_btn.'></span></span>';
								}
								echo '<div class="profile-img-thumb-wrapper"><div class="profile-img-thumb" id="'.$photo->ID.'" itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
								echo 	$imagebuttons;
								echo 	'<a href="'.$photo->guid.'" data-fancybox="profile-photo" itemprop="contentURL">';
								echo 		'<img data-original-url="'.$photo_th_url[0].'" class="mobile-ready-img rad3" alt="'.get_the_title().'" data-responsive-img-url="'.$photo_th_mobile_url[0].'" itemprop="thumbnailUrl" />';
								echo 	'</a>';
								echo '</div></div>'."\n";
							}
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
				if($city && !is_wp_error($city)) {
					$location[] = '<a href="'.get_term_link($city[0]).'" title="'.$city[0]->name.'">'.$city[0]->name.'</a>';

					$state = get_term($city[0]->parent, $taxonomy_location_url);
					if($state && !is_wp_error($state)) {
						$location[] = '<a href="'.get_term_link($state).'" title="'.$state->name.'">'.$state->name.'</a>';

						$country = get_term($state->parent, $taxonomy_location_url);
						if(!is_wp_error($country)) {
							$location[] = '<a href="'.get_term_link($country).'" title="'.$country->name.'">'.$country->name.'</a>';
						}
					}
				}
			?>
			<div class="clear"></div>
            <div class="aboutme">
				<h4><?php _e('About me','escortwp'); ?>:</h4>
				<b><?=$age?> <?=__('year old','escortwp')?> <span itemprop="gender"><?=__($gender_a[$gender], 'escortwp')?></span> <?=__('from','escortwp')?> <?=implode(", ", $location)?></b>
				<?php
				if($current_tour) {
					$currently_on_tour_box = '<div class="clear"></div>';
					$currently_on_tour_box .= '<div class="currently-on-tour-in rad5">';
					$currently_on_tour_box .=  __('Currently on tour in:','escortwp');
					if($current_tour['city']) {
						$city_obj = $current_tour['city'];
						$tour_location[] = '<a href="'.get_term_link($city_obj).'" title="'.$city_obj->name.'">'.$city_obj->name.'</a>';
					}
					if($current_tour['state']) {
						$state_obj = $current_tour['state'];
						$tour_location[] = '<a href="'.get_term_link($state_obj).'" title="'.$state_obj->name.'">'.$state_obj->name.'</a>';
					}
					if($current_tour['country']) {
						$country_obj = $current_tour['country'];
						$tour_location[] = '<a href="'.get_term_link($country_obj).'" title="'.$country_obj->name.'">'.$country_obj->name.'</a>';
					}
					$currently_on_tour_box .= " ".implode(", ", $tour_location);
					$currently_on_tour_box .= '</div>';
					$currently_on_tour_box .= '<div class="clear"></div>';
					echo $currently_on_tour_box;
				}
				?>
                <div class="clear5"></div>
				<?php echo $aboutyou; ?>
                <div class="clear"></div>
			</div> <!-- ABOUT ME -->
            <div class="clear10"></div>

            <div class="girlinfo l">
	            <div class="girlinfo-section">
	            	<?php if(!get_option("hide1")) { ?>
		                <div class="profilestarrating-wrapper">
		                	<div class="starrating"><div class="starrating_stars star<?php echo get_escort_rating(get_the_ID(), false); ?>"></div></div>
			                <div class="clear5"></div>
			                <div class="label"><?php printf(esc_html__('%s rating','escortwp'),ucfirst($taxonomy_profile_name)); ?></div>
			                <div class="clear"></div>
		                	<i><?=get_escort_rating(get_the_ID(), true)." ".strtolower(__('reviews','escortwp')); ?></i>
			                <div class="clear"></div>
		                </div>
		                <div class="clear10"></div>
		                <div class="clear10"></div>
	                <?php } // hide ratings ?>
	                <?php
					$favorites = get_user_meta( $userid, "favorites", true);
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
						<?php if(!get_option("hide1")) { ?>
			                <div class="addreview-button rad25 pinkbutton"><span class="icon-plus-circled"></span><?php _e('Add Review','escortwp'); ?></div>
		                <?php } // hide ratings ?>
						<div class="removefromfavorites rad25 pinkbutton favbutton" id="rem<?php the_ID(); ?>"<?php echo $addclass; ?>><span class="icon-heart"></span><?php _e('Remove Favorite','escortwp'); ?></div>
						<div class="addtofavorites rad25 pinkbutton favbutton" id="add<?php the_ID(); ?>"<?php echo $remclass; ?>><span class="icon-heart"></span><?php _e('Add to Favorites','escortwp'); ?></div>
					</div>
	                <?php } ?>
	                <div class="clear"></div>
		        	<?php
	                if($availability) {
	                	foreach($availability as $a_id) {
							$availability_show[] = __($availability_a[$a_id],'escortwp');
						}
	                	echo '<div class="section-box"><b>'.__('Availability','escortwp').'</b><span class="valuecolumn">'.implode(", ", $availability_show).'</span></div>';
					}
		        	if($ethnicity) { echo '<div class="section-box"><b>'.__('Ethnicity','escortwp').'</b><span class="valuecolumn">'.__($ethnicity_a[$ethnicity],'escortwp').'</span></div>'; }
	    	        if($haircolor) { echo '<div class="section-box"><b>'.__('Hair color','escortwp').'</b><span class="valuecolumn">'.__($haircolor_a[$haircolor],'escortwp').'</span></div>'; }
	                if($hairlength) { echo '<div class="section-box"><b>'.__('Hair length','escortwp').'</b><span class="valuecolumn">'.__($hairlength_a[$hairlength],'escortwp').'</span></div>'; }
	                if($bustsize) { echo '<div class="section-box"><b>'.__('Bust size','escortwp').'</b><span class="valuecolumn">'.__($bustsize_a[$bustsize],'escortwp').'</span></div>'; }
		            if($height) { echo '<div class="section-box"><b itemprop="height">'.__('Height','escortwp').'</b><span class="valuecolumn">'.$height.(get_option("heightscale") == "imperial" ? "ft".($height2 > 0 ? " ".$height2."in" : "") : "cm").'</span></div>'; }
		            if($weight) { echo '<div class="section-box"><b itemprop="weight">'.__('Weight','escortwp').'</b><span class="valuecolumn">'.$weight.(get_option("heightscale") == "imperial" ? "lb" : "kg").'</span></div>'; }
	    	        if($build) { echo '<div class="section-box"><b>'.__('Build','escortwp').'</b><span class="valuecolumn">'.__($build_a[$build],'escortwp').'</span></div>'; }
	                if($looks) { echo '<div class="section-box"><b>'.__('Looks','escortwp').'</b><span class="valuecolumn">'.__($looks_a[$looks],'escortwp').'</span></div>'; }
	                if($smoker) { echo '<div class="section-box"><b>'.__('Smoker','escortwp').'</b><span class="valuecolumn">'.__($smoker_a[$smoker],'escortwp').'</span></div>'; }
					if ($education) { echo '<div class="section-box"><b>'.__('Education','escortwp').'</b><span class="valuecolumn">'.__($education,'escortwp').'</span></div>'; }
					if ($sports) { echo '<div class="section-box"><b>'.__('Sports','escortwp').'</b><span class="valuecolumn">'.__($sports,'escortwp').'</span></div>'; }
					if ($hobbies) { echo '<div class="section-box"><b>'.__('Hobbies','escortwp').'</b><span class="valuecolumn">'.__($hobbies,'escortwp').'</span></div>'; }
					if ($zodiacsign) { echo '<div class="section-box"><b>'.__('Zodiac sign','escortwp').'</b><span class="valuecolumn">'.__($zodiacsign,'escortwp').'</span></div>'; }
					if ($sexualorientation) { echo '<div class="section-box"><b>'.__('Sexual orientation','escortwp').'</b><span class="valuecolumn">'.__($sexualorientation,'escortwp').'</span></div>'; }
					if ($occupation) { echo '<div class="section-box"><b>'.__('Occupation','escortwp').'</b><span class="valuecolumn">'.__($occupation,'escortwp').'</span></div>'; }
					?>
				</div> <!-- girlinfo-section -->

				<?php
                if($language1 || $language2 || $language3) {
					echo '<div class="girlinfo-section">';
	                	echo '<h4>'.__('Languages spoken','escortwp').':</h4><div class="clear"></div>';
						if ($language1) { echo "<div class='section-box'><b>".ucfirst(__($language1,'escortwp')).":</b><span class=\"valuecolumn\">".__($languagelevel_a[$language1level],'escortwp')."</span></div>"; }
						if ($language2) { echo "<div class='section-box'><b>".ucfirst(__($language2,'escortwp')).":</b><span class=\"valuecolumn\">".__($languagelevel_a[$language2level],'escortwp')."</span></div>"; }
						if ($language3) { echo "<div class='section-box'><b>".ucfirst(__($language3,'escortwp')).":</b><span class=\"valuecolumn\">".__($languagelevel_a[$language3level],'escortwp')."</span></div>"; }
					echo '</div> <!-- girlinfo-section -->';
				} // if at least one language
				?>

				<div class="girlinfo-section">
	                <h4><?php _e('Contact info','escortwp'); ?>:</h4>
	                <div class="clear"></div>
	                <div class="contact">
						<?php
						if(isset($currently_on_tour_box)) {
							echo $currently_on_tour_box;
						}
						$location = array();
						$city = wp_get_post_terms(get_the_ID(), $taxonomy_location_url);
						if($city && !is_wp_error($city)) {
							$location[] = '<span class="b"><span class="b-label">'.__('City','escortwp').':</span></span><span class="valuecolumn"><a href="'.get_term_link($city[0]).'" title="'.$city[0]->name.'" itemprop="addressLocality">'.$city[0]->name.'</a></span>';

							$state = get_term($city[0]->parent, $taxonomy_location_url);
							if($state && !is_wp_error($state)) {
								$state_label = showfield('state') ? __('State','escortwp') : __('Country','escortwp');
								$itemprop = showfield('state') ? "addressRegion" : "country";
								$location[] = '<span class="b"><span class="b-label">'.$state_label.':</span></span><span class="valuecolumn"><a href="'.get_term_link($state).'" title="'.$state->name.'" itemprop="'.$itemprop.'">'.$state->name.'</a></span>';

								$country = get_term($state->parent, $taxonomy_location_url);
								if(!is_wp_error($country)) {
									$location[] = '<span class="b"><span class="b-label">'.__('Country','escortwp').':</span></span><span class="valuecolumn"><a href="'.get_term_link($country).'" title="'.$country->name.'" itemprop="nationality">'.$country->name.'</a></span>';
								}
							}
						}
						echo '<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
						echo implode('<div class="clear"></div>', $location).'<div class="clear"></div>';
						echo '</div>';

						if(payment_plans('vip','extra','hide_contact_info') && !is_user_logged_in()) {
							echo '<div class="clear5"></div>'.
								'<div class="lockedsection rad5">'.
								__('You need to','escortwp').' <a href="'.get_permalink(get_option('main_reg_page_id')).'">'.__('register','escortwp').'</a> '.__('or','escortwp').' <a href="'.wp_login_url(get_permalink()).'">'.__('login','escortwp').'</a> '.sprintf(esc_html__('to be able to see the contact information or send a message to this %s','escortwp'),$taxonomy_profile_name).'.'.
								'</div>';
						} else {
							if(payment_plans('vip','extra','hide_contact_info') && !get_user_meta($userid, "vip", true) && !current_user_can('level_10') && $profile_author_id != $userid) {
								echo '<div class="clear5"></div><div class="lockedsection rad5">';
									echo sprintf(esc_html__('You need to be a VIP member to see the contact information of an %s','escortwp'),$taxonomy_profile_name).".<br />";
									echo __('VIP status costs','escortwp').' <strong>'.format_price('vip','small')."</strong><br />";
									if(payment_plans('vip','duration')) {
										echo __('Your VIP status will be active for','escortwp').' <strong>'.$payment_duration_a[payment_plans('vip','duration')][0].'</strong> ';
									}
									echo '<div class="clear20"></div>';
									echo '<div class="text-center">'.generate_payment_buttons("vip", $userid, __('Upgrade to VIP','escortwp'))."</div> <!--center-->";
									echo '<div class="clear5"></div>';
									echo '<small>'.format_price('vip').'</small>';
								echo '</div>';
							} else {
								if ($website) {
									$wraped_website_url = str_replace(array("http://www.", "http://", "https://www.", "https://"), "", $website);
									echo '<span class="b"><span class="b-label">'.__('Website','escortwp').':</span></span><span class="valuecolumn"><a href="'.$website.'" target="_blank" rel="nofollow" itemprop="url">'.$wraped_website_url.'</a></span><div class="clear"></div>';
								}

								if ($snapchat) {
									echo '<span class="b"><span class="b-label">'.__('SnapChat','escortwp').':</span><img src="'.get_template_directory_uri().'/i/snapchat.svg" class="social-icons-contact-info" height="20" alt="SnapChat" /></span><span class="valuecolumn">@'.$snapchat.'</span><div class="clear"></div>';
								}

								if ($instagram) {
									echo '<span class="b"><span class="b-label">'.__('Instagram','escortwp').':</span><img src="'.get_template_directory_uri().'/i/instagram.svg" class="social-icons-contact-info" height="20" alt="SnapChat" /></span><span class="valuecolumn"><a href="https://www.instagram.com/'.$instagram.'/" target="_blank" rel="nofollow" itemprop="url">@'.$instagram.'</a></span><div class="clear"></div>';
								}

								if ($twitter) {
									$twitter_username = "@".str_replace(array("http://www.", "http://", "https://www.", "https://", "twitter.com/"), "", $twitter);
									echo '<span class="b"><span class="b-label">'.__('Twitter','escortwp').':</span><img src="'.get_template_directory_uri().'/i/twitter.svg" class="social-icons-contact-info" height="20" alt="SnapChat" /></span><span class="valuecolumn"><a href="'.$twitter.'" target="_blank" rel="nofollow" itemprop="url">'.$twitter_username.'</a></span><div class="clear"></div>';
								}

								if ($facebook) {
									$facebook_username = "@".str_replace(array("http://www.", "http://", "https://www.", "https://", "facebook.com/"), "", $facebook);
									echo '<span class="b"><span class="b-label">'.__('Facebook','escortwp').':</span><img src="'.get_template_directory_uri().'/i/facebook.svg" class="social-icons-contact-info" height="20" alt="SnapChat" /></span><span class="valuecolumn"><a href="'.$facebook.'" target="_blank" rel="nofollow" itemprop="url">'.$facebook_username.'</a></span><div class="clear"></div>';
								}
						?>
	            	            <?php if($phone) { ?>
	            	            	<span class="b"><span class="b-label"><?php _e('Phone','escortwp'); ?>:</span></span><span class="valuecolumn"><a href="tel:<?=$phone?>" itemprop="telephone"><?=$phone?></a></span>
							<?php if(is_array($phone_available_on) && count($phone_available_on) > 0) { ?>
									<div class="clear"></div>
	            	            	<span class="b"><span class="b-label"><?php _e('Text me','escortwp'); ?>:</span></span>
	            	            	<span class="valuecolumn">
										<div class="available-on">
											<?php
											foreach ($phone_available_on as $key => $value) {
												switch ($value) {
													case '1':
															echo '<a class="" href="https://wa.me/'.preg_replace("/([^0-9])/", "", $phone).'?text='.urlencode(sprintf(__('Hi, I saw your profile on %s', 'escortwp'), get_site_url())).'"><span class="text-me-icon icon icon-whatsapp"></span></a>';
														break;

													case '2':
															echo '<a href="viber://chat?number='.preg_replace("/([^0-9])/", "", $phone).'"><span class="icon text-me-icon icon-viber"></span></a>';
														break;
												}
											}
											?>
										</div> <!-- available-on -->
	            	            	</span>

							<?php } ?>

	            	        <?php } ?>
	                            <div class="clear10"></div><a name="contactform"></a>
								<div class="text-center"><div class="sendemail center rad25 pinkbutton"<?php if (isset($err) && $_POST['action'] == "contactform") { echo ' style="display: none;"'; } ?>><span class="icon-mail"></span><?php printf(esc_html__('Send me an email','escortwp'),$taxonomy_profile_name); ?></div></div>
								<div class="clear"></div>
						<?php
								if (isset($err) && $_POST['action'] == "contactform") { echo '<div class="err rad25">'.$err.'</div>'; }
								if (isset($ok) && $_POST['action'] == "contactform") { echo '<div class="ok rad25">'.$ok.'</div>'; }
								include (get_template_directory() . '/send-email-form.php');
							} // if VIP or admin
						} // if contact section hidden and user not logged in
						?>
						</div> <!-- CONTACT -->
				</div> <!-- girlinfo-section -->
        	</div> <!-- girlinfo -->

            <div class="girlinfo r">
            	<?php if($services || $extraservices) { ?>
	            	<div class="girlinfo-section">
	            		<?php if($services) { ?>
			            	<h4><?php _e('Services','escortwp'); ?>:</h4>
			                <div class="services">
								<?php
								foreach($services_a as $key=>$service) {
									$service = __($service,'escortwp');
									if (in_array($key, $services)) {
										echo '<div><span class="icon-ok"></span>'.$service.'</div>';
									} else {
										if(get_option("hideunchedkedservices") != "2") {
											echo '<div><span class="icon-cancel"></span>'.$service.'</div>';
										}
									}
								} // foreach
								?>
			                </div> <!-- SERVICES -->
		                <?php } // if $services ?>
						
		            	<?php if($services && $extraservices) { ?>
							<div class="clear20"></div>
		                <?php } // if $services ?>

						<?php if ($extraservices) { ?>
							<h4><?=__('Extra Services','escortwp')?>:</h4>
							<div class="services">
								<div class="yes"><?=$extraservices?></div>
							</div>
		                <?php } // if $services ?>
	                </div> <!-- girlinfo-section -->
                <?php } // if $services ?>

				<?php
				if (!$currency) {
					$currency = $currency_a['1'][0];
				} else {
					$currency = $currency_a[$currency][0];
				}
				$rates_sum_incall = (int)$rate30min_incall + (int)$rate1h_incall + (int)$rate2h_incall + (int)$rate3h_incall + (int)$rate6h_incall + (int)$rate12h_incall + (int)$rate24h_incall;
				$rates_sum_outcall = (int)$rate30min_outcall + (int)$rate1h_outcall + (int)$rate2h_outcall + (int)$rate3h_outcall + (int)$rate6h_outcall + (int)$rate12h_outcall + (int)$rate24h_outcall;
				if($rates_sum_incall + $rates_sum_outcall > 0) {
					echo '<div class="girlinfo-section">';
	                	echo '<div class="clear20"></div><h4>'.__('Rates','escortwp').':</h4><div class="clear"></div>';

						echo '<table class="rates-table col100">';
							echo 	'<tr>';
							echo		'<th></th>';
							if($rates_sum_incall) {
								echo '<th class="hide-incall">'.__('Incall','escortwp').'</th>';
							}
							if($rates_sum_outcall) {
								echo '<th class="hide-outcall">'.__('Outcall','escortwp').'</th>';
							}
							echo 	'</tr>';
							if ($rate30min_incall || $rate30min_outcall) {
								echo '<tr>';
								echo '<td><strong>'.__('30 minutes','escortwp').'</strong></td>';
								echo '<td class="text-center hide-incall">';
								if ($rate30min_incall) {
									echo $rate30min_incall.' '.$currency;
								}
								echo '</td>';
								echo '<td class="text-center hide-outcall">';
								if ($rate30min_outcall) {
									echo $rate30min_outcall.' '.$currency;
								}
								echo '</td>';
								echo '</tr>';
							}
							if ($rate1h_incall || $rate1h_outcall) {
								echo '<tr>';
								echo '<td><strong>'.__('1 hour','escortwp').'</strong></td><td class="text-center hide-incall">';
								if ($rate1h_incall) {
									echo $rate1h_incall.' '.$currency;
								}
								echo '</td>';
								echo '<td class="text-center hide-outcall">';
								if ($rate1h_outcall) {
									echo $rate1h_outcall.' '.$currency;
								}
								echo '</td>';
								echo '</tr>';
							}
							if ($rate2h_incall || $rate2h_outcall) {
								echo '<tr>';
								echo '<td><strong>'.__('2 hours','escortwp').'</strong></td><td class="text-center hide-incall">';
								if ($rate2h_incall) {
									echo $rate2h_incall.' '.$currency;
								}
								echo '</td>';
								echo '<td class="text-center hide-outcall">';
								if ($rate2h_outcall) {
									echo $rate2h_outcall.' '.$currency;
								}
								echo '</td>';
								echo '</tr>';
							}
							if ($rate3h_incall || $rate3h_outcall) {
								echo '<tr>';
								echo '<td><strong>'.__('3 hours','escortwp').'</strong></td><td class="text-center hide-incall">';
								if ($rate3h_incall) {
									echo $rate3h_incall.' '.$currency;
								}
								echo '</td>';
								echo '<td class="text-center hide-outcall">';
								if ($rate3h_outcall) {
									echo $rate3h_outcall.' '.$currency;
								}
								echo '</td>';
								echo '</tr>';
							}
							if ($rate6h_incall || $rate6h_outcall) {
								echo '<tr>';
								echo '<td><strong>'.__('6 hours','escortwp').'</strong></td><td class="text-center hide-incall">';
								if ($rate6h_incall) {
									echo $rate6h_incall.' '.$currency;
								}
								echo '</td>';
								echo '<td class="text-center hide-outcall">';
								if ($rate6h_outcall) {
										echo $rate6h_outcall.' '.$currency;
								}
								echo '</td>';
								echo '</tr>';
							}
							if ($rate12h_incall || $rate12h_outcall) {
								echo '<tr>';
								echo '<td><strong>'.__('12 hours','escortwp').'</strong></td><td class="text-center hide-incall">';
								if ($rate12h_incall) {
									echo $rate12h_incall.' '.$currency;
								}
								echo '</td>';
								echo '<td class="text-center hide-outcall">';
								if ($rate12h_outcall) {
										echo $rate12h_outcall.' '.$currency;
								}
								echo '</td>';
								echo '</tr>';
							}
							if ($rate24h_incall || $rate24h_outcall) {
								echo '<tr>';
								echo '<td><strong>'.__('24 hours','escortwp').'</strong></td><td class="text-center hide-incall">';
								if ($rate24h_incall) {
									echo $rate24h_incall.' '.$currency;
								}
								echo '</td>';
								echo '<td class="text-center hide-outcall">';
								if ($rate24h_outcall) {
										echo $rate24h_outcall.' '.$currency;
								}
								echo '</td>';
								echo '</tr>';
							}
						echo '</table>';
					echo '</div> <!-- girlinfo-section -->';
				} // if at least one rate
				?>
				<div class="clear"></div>
            </div> <!-- GIRL INFO RIGHT -->
			<div class="clear20"></div>

			<?php
			if (isset($_GET['add_tour']) && $_GET['add_tour'] == 'ok') { echo "<div class=\"ok rad5\">".__('The tour has been added','escortwp')."</div>"; }
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
			if ($tours->have_posts()) : ?>
				<div class="clear30"></div>
				<a name="tours"></a>
				<h4 class="l single-profile-tours-title"><?php _e('Tours','escortwp'); ?>:</h4>
				<div class="clear"></div>
		        <?php if ($profile_author_id == $userid && $userstatus == $taxonomy_agency_url || current_user_can('level_10')) { ?>
		        <div class="deletemsg r"></div>
		        <?php } ?>
				<div class="clear10"></div>
				<div class="addedtours">
					<div class="tour tourhead">
						<div class="addedstart"><?php _e('Start','escortwp'); ?></div>
				    	<div class="addedend"><?php _e('End','escortwp'); ?></div>
					    <div class="addedplace"><?php _e('Place','escortwp'); ?></div>
				    	<div class="addedphone"><?php _e('Phone','escortwp'); ?></div>
					</div>
					<?php
					while($tours->have_posts()) : $tours->the_post();
						unset($city, $state, $country, $location);

						$city = get_term(get_post_meta(get_the_ID(), 'city', true), $taxonomy_location_url);
						if($city) $location[] = $city->name;

						if(showfield('state')) {
							$state = get_term(get_post_meta(get_the_ID(), 'state', true), $taxonomy_location_url);
							if($state) {
								$location[] = $state->name;
							}
						}

						$country = get_term(get_post_meta(get_the_ID(), 'country', true), $taxonomy_location_url);
						if($country) $location[] = $country->name;
						?>
						<div class="tour" id="tour<?php the_ID(); ?>">
							<span class="tour-info-mobile"><?php _e('Start','escortwp'); ?>:</span>
							<div class="addedstart"><?php echo date("d M Y", get_post_meta(get_the_ID(),'start', true)); ?></div>
							<span class="tour-info-mobile-clear"></span>

							<span class="tour-info-mobile"><?php _e('End','escortwp'); ?>:</span>
					    	<div class="addedend"><?php echo date("d M Y", get_post_meta(get_the_ID(),'end', true)); ?></div>
					    	<span class="tour-info-mobile-clear"></span>

					    	<span class="tour-info-mobile"><?php _e('Place','escortwp'); ?>:</span>
						    <div class="addedplace"><?php echo implode(", ", $location); ?></div>
						    <span class="tour-info-mobile-clear"></span>

						    <span class="tour-info-mobile"><?php _e('Phone','escortwp'); ?>:</span>
					    	<div class="addedphone"><a href="tel:<?php echo get_post_meta(get_the_ID(),'phone', true); ?>"><?php echo get_post_meta(get_the_ID(),'phone', true); ?></a></div>

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

			if($profile_author_id == $userid) {
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
				if ($unpaid_tours->have_posts()) : ?>
					<div class="clear30"></div>
					<a name="tours"></a>
					<h4 class="l single-profile-tours-title"><?php _e('Unpaid Tours','escortwp'); ?>:</h4>
					<div class="clear"></div>
			        <?php if ($profile_author_id == $userid && $userstatus == $taxonomy_agency_url || current_user_can('level_10')) { ?>
			        <div class="deletemsg r"></div>
			        <?php } ?>
					<div class="clear10"></div>
					<div class="addedtours">
						<div class="tour tourhead">
							<div class="addedstart"><?php _e('Start','escortwp'); ?></div>
					    	<div class="addedend"><?php _e('End','escortwp'); ?></div>
						    <div class="addedplace"><?php _e('Place','escortwp'); ?></div>
					    	<div class="addedphone"><?php _e('Phone','escortwp'); ?></div>
						</div>
						<?php
						while($unpaid_tours->have_posts()) : $unpaid_tours->the_post();
							unset($city, $state, $country, $location);

							$city = get_term(get_post_meta(get_the_ID(), 'city', true), $taxonomy_location_url);
							if($city) $location[] = $city->name;

							if(showfield('state')) {
								$state = get_term(get_post_meta(get_the_ID(), 'state', true), $taxonomy_location_url);
								if($state) {
									$location[] = $state->name;
								}
							}

							$country = get_term(get_post_meta(get_the_ID(), 'country', true), $taxonomy_location_url);
							if($country) $location[] = $country->name;
							?>
							<div class="tour" id="tour<?php the_ID(); ?>">
								<span class="tour-info-mobile"><?php _e('Start','escortwp'); ?>:</span>
								<div class="addedstart"><?php echo date("d M Y", get_post_meta(get_the_ID(),'start', true)); ?></div>
								<span class="tour-info-mobile-clear"></span>

								<span class="tour-info-mobile"><?php _e('End','escortwp'); ?>:</span>
						    	<div class="addedend"><?php echo date("d M Y", get_post_meta(get_the_ID(),'end', true)); ?></div>
						    	<span class="tour-info-mobile-clear"></span>

						    	<span class="tour-info-mobile"><?php _e('Place','escortwp'); ?>:</span>
							    <div class="addedplace"><?php echo implode(", ", $location); ?></div>
							    <span class="tour-info-mobile-clear"></span>

							    <span class="tour-info-mobile"><?php _e('Phone','escortwp'); ?>:</span>
						    	<div class="addedphone"><a href="tel:<?php echo get_post_meta(get_the_ID(),'phone', true); ?>"><?php echo get_post_meta(get_the_ID(),'phone', true); ?></a></div>

						    	<span class="tour-info-mobile-clear"></span>
						        <div class="addedbuttons">
						        	<?php
						        	echo '<div class="pb"><a class="greenbutton payment-button rad25" href="'.get_permalink($this_post_id).'?unpaid_tour='.get_the_ID().'">'.__('Pay for tour','escortwp').'</a></div>';
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

            if(get_option('hitcounter1')) {
                echo esc_page_hit_counter(get_the_ID());
            }
			?>

			<?php if(!get_option("hide1")) { ?>
				<div class="clear20"></div>
				<h4 class="l" id="addreviewsection"><?php _e('Reviews','escortwp'); ?>:</h4>

				<?php
				if ( get_option("escortid".$profile_author_id) == $taxonomy_agency_url && !get_option("hide3")) {
					echo '<a href="'.get_permalink(get_option("agencypostid".$profile_author_id)).'" class="rad25 pinkbutton r reviewthegency"><span class="icon-plus-circled"></span>'.sprintf(esc_html__('Review the %s','escortwp'),$taxonomy_agency_name).'</a>';
				}
				?>
				<div class="addreview-button rad25 pinkbutton r"><span class="icon-plus-circled"></span><?php _e('Add review','escortwp'); ?></div>
				<div class="clear"></div>
				<?php
				if (isset($_GET['postreview']) && $_GET['postreview'] == "ok") {
					echo '<div class="clear"></div>';
					echo '<div class="ok rad25">';
						if (get_option("manactivesc") == "1") {
							echo __('Your review will be read by our staff and published soon.','escortwp').'<br />';
						}
						echo __('Thank you for posting.','escortwp');
					echo '</div>';
				}
				?>
				<div class="addreviewform registerform<?php if(isset($err) && $_POST['action'] == 'addreview' && $_GET['postreview'] != "ok") { } else { echo ' hide'; } ?>">
					<?php
					if (!is_user_logged_in()) {
						echo '<div class="err rad25">'.__('You need to','escortwp').' <a href="'.get_permalink(get_option('main_reg_page_id')).'">'.__('register','escortwp').'</a> '.__('or','escortwp').' <a href="'.wp_login_url(get_permalink()).'">'.__('login','escortwp').'</a> '.__('to be able to post a review','escortwp').'</div>';
					} else {
						if ($userstatus == "member" || current_user_can('level_10')) {
							if(did_user_post_review($userid, get_the_ID()) == true) {
								echo '<div class="err rad25">'.sprintf(esc_html__('You can\'t post more than one review for the same %s.','escortwp'),$taxonomy_profile_name).'</div>';
							} else {
								if(payment_plans('vip','extra','hide_review_form') && !get_user_meta($userid, "vip", true) && !current_user_can('level_10') && $profile_author_id != $userid) {
									echo '<div class="clear5"></div>';
									echo '<div class="lockedsection rad5">';
										echo __('You need to be a VIP member to be able to post a review','escortwp').".<br />";
										echo __('VIP status costs','escortwp').' <strong>'.format_price('vip','small')."</strong><br />";
										if(payment_plans('vip','duration')) {
											echo __('Your VIP status will be active for','escortwp').' <strong>'.$payment_duration_a[payment_plans('vip','duration')][0].'</strong> ';
										}
										echo '<div class="clear20"></div>';
										echo '<div class="text-center">'.generate_payment_buttons("vip", $userid, __('Upgrade to VIP','escortwp'))."</div> <!--center-->";
										echo '<div class="clear5"></div>';
										echo '<small>'.format_price('vip').'</small>';
									echo '</div>';
								} else {
								?>
								<?php if (isset($ok) && $_POST['action'] == 'addreview') { echo "<div class=\"ok rad25\">$ok</div>"; } ?>
								<?php if (isset($err) && $_POST['action'] == 'addreview') { echo "<div class=\"err rad25\">$err</div>"; } ?>
								<form action="<?php echo get_permalink(get_the_ID()); ?>#addreview" method="post" class="form-styling">
							    	<?php closebtn(); ?>
							    	<div class="clear10"></div>
								    <input type="hidden" name="action" value="addreview" />
									<div class="form-label">
								    	<label for="rateescort"><?php printf(esc_html__('Rate the %s','escortwp'),$taxonomy_profile_name); ?>: <i>*</i></label>
								    </div>
								    <?php
								    if(!isset($rateescort)) $rateescort = "";
								    ?>
									<div class="form-input form-input-rating">
										<label for="rateescort5"><input type="radio" id="rateescort5" name="rateescort" value="5" <?=$rateescort == "5" ? ' checked' : ""?> />5 - <?php _e('Perfect','escortwp'); ?></label><div class="clear"></div>
										<label for="rateescort4"><input type="radio" id="rateescort4" name="rateescort" value="4" <?=$rateescort == "4" ? ' checked' : ""?> />4 - <?php _e('Good','escortwp'); ?></label><div class="clear"></div>
										<label for="rateescort3"><input type="radio" id="rateescort3" name="rateescort" value="3" <?=$rateescort == "3" ? ' checked' : ""?> />3 - <?php _e('Average','escortwp'); ?></label><div class="clear"></div>
										<label for="rateescort2"><input type="radio" id="rateescort2" name="rateescort" value="2" <?=$rateescort == "2" ? ' checked' : ""?> />2 - <?php _e('Bellow average','escortwp'); ?></label><div class="clear"></div>
										<label for="rateescort1"><input type="radio" id="rateescort1" name="rateescort" value="1" <?=$rateescort == "1" ? ' checked' : ""?> />1 - <?php _e('Bad','escortwp'); ?></label><div class="clear"></div>
								    </div> <!-- rateing --> <div class="formseparator"></div>

								    <div class="form-label">
										<label for="reviewtext"><?php _e('Comment','escortwp'); ?>: <i>*</i></label>
									</div>
									<div class="form-input">
										<?php if(!isset($reviewtext)) $reviewtext = ""; ?>
										<textarea name="reviewtext" class="textarea longtextarea" rows="7" id="reviewtext"><?php echo $reviewtext; ?></textarea>
										<div clas="clear"></div>
										<small class="l"><?php _e('html code will be removed','escortwp'); ?></small>
										<div class="charcount hides r"><div id="barbox" class="rad25"><div id="bar"></div></div><div id="count"></div></div>
									</div> <!-- review text --> <div class="formseparator"></div>

									<div class="text-center">
										<div class="clear10"></div>
										<input type="submit" name="submit" value="<?php _e('Add Review','escortwp'); ?>" class="pinkbutton rad3" />
									</div> <!--center-->
								</form>
								<?php
								} // if review section is locked and user is not VIP
							}
						} else {
							echo '<div class="err rad25">'.__('Your user type is not allowed to post a review here','escortwp').'</div>';
						}
					}
					?>
				</div> <!-- ADD REVIEW FORM-->

				<?php
				$args = array(
					'post_type' => 'review',
					'posts_per_page' => '-1',
					'meta_query' => array(
						array(
							'key' => 'escortid',
							'value' => get_the_ID(),
							'compare' => '='
						)
					)
				);
				query_posts($args);
				if ( have_posts() ) : ?>
				<div class="clear20"></div>
				<?php
				while ( have_posts() ) : the_post();
					if (get_post_meta(get_the_ID(), 'reviewfor', true) == 'agency') {
						$escort_or_agency = get_post(get_post_meta(get_the_ID(), 'agencyid', true));
						$rating_number = get_post_meta(get_the_ID(), 'rateagency', true);
					} elseif (get_post_meta(get_the_ID(), 'reviewfor', true) == 'profile') {
						$escort_or_agency = get_post(get_post_meta(get_the_ID(), 'escortid', true));
						$rating_number = get_post_meta(get_the_ID(), 'rateescort', true);
					}
					?>
					<div class="review-wrapper rad5">
						<div class="starrating l"><div class="starrating_stars star<?php echo $rating_number; ?>"></div></div>&nbsp;&nbsp;<i><?php echo strtolower(__('Added by','escortwp')); ?></i>&nbsp;&nbsp;<b><?php echo substr(get_the_author_meta('display_name'), 0, 2); ?>...</b> <i><?php _e('for','escortwp'); ?></i> <b><?php echo $escort_or_agency->post_title; ?></b> <i><?php _e('on','escortwp'); ?></i> <b><?php echo the_time("d F Y"); ?></b>
						<?php the_content(); ?>
						<?php edit_post_link(__('Edit review','escortwp')); ?>
					</div>
					<div class="clear30"></div>
					<?php endwhile; ?>

					<?php
				else:
					_e('No reviews yet','escortwp');
				endif;
				wp_reset_query();
			} // if !get_option("hide1")

			if (current_user_can('level_10')) {
				echo '<div class="clear10"></div>';
				edit_post_link(__('Edit in WordPress','escortwp'));
			}

			show_report_profile_button($this_post_id);
			?>
        </div> <!-- GIRL SINGLE -->
	</div> <!-- BODY BOX -->

    <div class="clear"></div>
</div> <!-- BODY -->
</div> <!-- contentwrapper -->

<?php get_sidebar("left"); ?>
<?php get_sidebar("right"); ?>
<div class="clear"></div>
<?php get_footer(); ?>