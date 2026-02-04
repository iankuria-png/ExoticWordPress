<?php
/**
 * register-independent-personal-info-process.php
 * EscortWP Child Theme (2025) — complete handler (ALWAYS redirect to profile URL)
 *
 * Key points:
 * - Always redirect to the profile permalink (or /?p=ID fallback) — never to Edit Post.
 * - Fixes “random homepage” redirects by removing edit-page branches and adding permalink fallback.
 * - Admin creation: if username/email already exist, link the new profile to that user (no false errors).
 * - Self-registration logs in the new user and goes to their profile URL.
 * - DOB age check bug fixed, robust PHP 8+ sanitization, optional nonce verification.
 * - Preserves 2022 customizations, including `personal_phone` and email flows.
 */

if (!defined('ABSPATH')) { exit; }

// --- Error toggles (compatible with 2022) ---
if (!defined('error_reporting')) { define('error_reporting', '0'); }
@ini_set('display_errors', error_reporting);
if (error_reporting === '1') { @error_reporting(E_ALL); }

// --- Theme guard (kept from 2022) ---
if (defined('isdolcetheme') && isdolcetheme !== 1) { exit; }

// --- Globals provided by parent/theme ---
global $taxonomy_profile_name, $taxonomy_profile_url, $taxonomy_location_url, $taxonomy_agency_name,
       $gender_a, $ethnicity_a, $haircolor_a, $hairlength_a, $bustsize_a, $build_a, $looks_a, $smoker_a,
       $availability_a, $languagelevel_a, $services_a, $currency_a, $taxonomy_profile_name_plural,
       $taxonomy_agency_url, $payment_duration_a;

// ---------- Helpers ----------
function escortwp_post($key, $default = '') {
    return isset($_POST[$key]) ? wp_unslash($_POST[$key]) : $default;
}
function escortwp_bool_int($val) {
    return (isset($val) && ((string)$val === '1' || $val === 1 || $val === true || $val === 'on')) ? 1 : 0;
}
function escortwp_view_url($post_id) {
    $u = get_permalink($post_id);
    if (!$u) { $u = add_query_arg('p', (int)$post_id, home_url('/')); } // robust fallback if permalinks not flushed
    return $u;
}

// ---------- Init ----------
$err            = '';
$escort_post_id = 0;
$agencyid       = 0;
$emailhash      = null;

$current_user = wp_get_current_user();
$is_admin     = current_user_can('level_10') || current_user_can('create_users');
$admin_registers_independent_escort = $is_admin ? "yes" : "";

// ---------- Nonce (if present in form) ----------
$nonce_field = escortwp_post('escort_nonce');
if ($nonce_field && function_exists('wp_verify_nonce')) {
    if (!wp_verify_nonce($nonce_field, 'escort_register_independent')) {
        wp_die(esc_html__('Security check failed. Please go back and try again.', 'escortwp'));
    }
}

// ---------- Editing existing post? ----------
if (escortwp_post('escort_post_id')) {
    $escort_post_id = (int) escortwp_post('escort_post_id');
    if ($escort_post_id > 0) {
        $escort_post = get_post($escort_post_id);
        if (!$escort_post) {
            $err .= esc_html__('The profile you are trying to edit does not exist.', 'escortwp')."<br />";
        } else {
            $escort_post_author = (int) $escort_post->post_author;
            if ($escort_post_author !== (int)$current_user->ID && !$is_admin) {
                $err .= esc_html__('You are not allowed to edit this profile', 'escortwp')."<br />";
            }
        }
    }
}

// ---------- Agency creating an escort under its account? ----------
if (escortwp_post('agencyid')) { $agencyid = (int) escortwp_post('agencyid'); }

// ---------- Username & password (only for brand-new independent self-registration) ----------
$user = ''; $pass = '';
if (!$escort_post_id && !$agencyid) {
    $user = sanitize_user((string) escortwp_post('user'));
    if ($user) {
        $ulen = strlen($user);
        if ($ulen < 4 || $ulen > 30) {
            $err .= esc_html__('Your username must be between 4 and 30 characters','escortwp')."<br />";
        }
        // If admin enters an existing username, we'll link to that user later (no error for admin).
    } else {
        $err .= esc_html__('The username field is empty','escortwp')."<br />";
    }

    $pass = (string) escortwp_post('pass');
    if ($pass) {
        $plen = strlen($pass);
        if ($plen < 6 || $plen > 50) {
            $err .= esc_html__('Your password must be between 6 and 50 characters','escortwp')."<br />";
        } elseif (false !== strpos(stripslashes($pass), "\\")) {
            $err .= esc_html__('Passwords may not contain the character "\"','escortwp')."<br />";
        }
    } else {
        $err .= esc_html__('The password field is empty','escortwp')."<br />";
    }
}

// ---------- Email (skip uniqueness error when admin; we will link existing) ----------
$youremail = '';
if (!$agencyid) {
    $youremail = trim((string) escortwp_post('youremail'));
    if (!$youremail) {
        $err .= esc_html__('Please write your email address','escortwp')."<br />";
    } else {
        if (!is_email($youremail)) {
            $err .= esc_html__('Your email address seems to be wrong','escortwp')."<br />";
        } elseif (!$is_admin && email_exists($youremail) && !$escort_post_id) {
            $err .= esc_html__('The email address has been used by someone else already','escortwp')."<br />";
        }
    }
}

// ---------- Admin-only toggles ----------
if ($is_admin) {
    $sendverification = (int) escortwp_post('sendverification'); // 0,1,2
    $sendauth         = (int) escortwp_post('sendauth');         // 1 => include password in email
} else {
    $sendverification = 0;
    $sendauth         = 0;
}

// ---------- Basic fields ----------
$yourname = substr(sanitize_text_field((string) escortwp_post('yourname')), 0, 200);
if (!$yourname) { $err .= esc_html__('Please write your name','escortwp')."<br />"; }

$phone = substr(sanitize_text_field((string) escortwp_post('phone')), 0, 50);
if (!$phone && function_exists('ismand') && ismand('phone','no')) {
    $err .= esc_html__('Please write your phone number','escortwp')."<br />";
}

$personal_phone = sanitize_text_field((string) escortwp_post('personal_phone'));

// phone available on (1,2)
$phone_available_on = escortwp_post('phone_available_on', []);
if (!is_array($phone_available_on)) { $phone_available_on = []; }
foreach ($phone_available_on as $i => $one) {
    $one = (int)$one;
    if ($one !== 1 && $one !== 2) { unset($phone_available_on[$i]); }
}

// Socials / links
$website   = '';
$instagram = '';
$snapchat  = '';
$twitter   = '';
$facebook  = '';

if (escortwp_post('website')) {
    $website = esc_url((string) escortwp_post('website'));
    if (!$website) { $err .= esc_html__('Your website url seems to be wrong','escortwp')."<br />"; }
} elseif (function_exists('ismand') && ismand('website','no')) {
    $err .= esc_html__('Please write a website url for your profile','escortwp')."<br />";
}

if (escortwp_post('instagram')) {
    $instagram = substr(sanitize_text_field((string) escortwp_post('instagram')), 0, 300);
} elseif (function_exists('ismand') && ismand('instagram','no')) {
    $err .= esc_html__('Please write your instagram username','escortwp')."<br />";
}

if (escortwp_post('snapchat')) {
    $snapchat = substr(sanitize_text_field((string) escortwp_post('snapchat')), 0, 300);
} elseif (function_exists('ismand') && ismand('snapchat','no')) {
    $err .= esc_html__('Please write your SnapChat username','escortwp')."<br />";
}

if (escortwp_post('twitter')) {
    $twitter = esc_url((string) escortwp_post('twitter'));
    if (!$twitter) { $err .= esc_html__('Your Twitter url seems to be wrong','escortwp')."<br />"; }
} elseif (function_exists('ismand') && ismand('twitter','no')) {
    $err .= esc_html__('Please write your Twitter page url','escortwp')."<br />";
}

if (escortwp_post('facebook')) {
    $facebook = esc_url((string) escortwp_post('facebook'));
    if (!$facebook) { $err .= esc_html__('Your Facebook url seems to be wrong','escortwp')."<br />"; }
} elseif (function_exists('ismand') && ismand('facebook','no')) {
    $err .= esc_html__('Please write your Facebook page url','escortwp')."<br />";
}

// ---------- Location ----------
$country     = 0;
$state_id    = 0;
$city_id     = 0;
$city_parent = 0;

if (escortwp_post('country') && (int)escortwp_post('country') > 0) {
    $country     = (int) escortwp_post('country');
    $city_parent = $country;
    if (!term_exists($country, $taxonomy_location_url)) {
        $err .= esc_html__('The country you selected doesn\'t exist in our database','escortwp')."<br />";
        $country = 0;
    } else {
        $show_state = function_exists('showfield') ? showfield('state') : false;
        if ($show_state) {
            if (escortwp_post('state')) {
                if (get_option('locationdropdown') === "1") {
                    $state = (int) escortwp_post('state');
                    $term  = get_term_by('id', $state, $taxonomy_location_url);
                    if (!$term) { $err .= esc_html__('The state you selected doesn\'t exist in our database','escortwp')."<br />"; }
                    else { $state_id = (int)$term->term_id; }
                } else {
                    $state    = substr(sanitize_text_field((string) escortwp_post('state')), 0, 70);
                    $state_ex = term_exists($state, $taxonomy_location_url, $country);
                    if (!$state_ex) {
                        $arg = array('description' => $state, 'parent' => $country);
                        wp_insert_term($state, $taxonomy_location_url, $arg);
                        $state_ex = term_exists($state, $taxonomy_location_url, $country);
                    }
                    if (is_array($state_ex) && !empty($state_ex['term_id'])) { $state_id = (int)$state_ex['term_id']; }
                }
                if ($state_id) { $city_parent = $state_id; }
            } else {
                $err .= esc_html__('You need to select your state','escortwp')."<br />";
            }
        }

        if (escortwp_post('city')) {
            if (get_option('locationdropdown') === "1") {
                $city = (int) escortwp_post('city');
                $term = get_term_by('id', $city, $taxonomy_location_url);
                if (!$term) { $err .= esc_html__('The city you selected doesn\'t exist in our database','escortwp')."<br />"; }
                else { $city_id = (int)$term->term_id; }
            } else {
                $city    = substr(sanitize_text_field((string) escortwp_post('city')), 0, 70);
                $city_ex = term_exists($city, $taxonomy_location_url, $city_parent);
                if (!$city_ex) {
                    $arg = array('description' => $city, 'parent' => $city_parent);
                    wp_insert_term($city, $taxonomy_location_url, $arg);
                    $city_ex = term_exists($city, $taxonomy_location_url, $city_parent);
                }
                if (is_array($city_ex) && !empty($city_ex['term_id'])) { $city_id = (int)$city_ex['term_id']; }
            }
        } else {
            $err .= esc_html__('You need to select your city','escortwp')."<br />";
        }
    }

    // WPML mapping
    if (function_exists('icl_object_id') && $city_id) {
        global $sitepress;
        if (isset($sitepress) && method_exists($sitepress, 'get_default_language')) {
            if ($sitepress->get_default_language() !== ICL_LANGUAGE_CODE) {
                $country  = icl_object_id($city_parent, $taxonomy_location_url, true, $sitepress->get_default_language());
                if (function_exists('showfield') && showfield('state') && $state_id) {
                    $state_id = icl_object_id($state_id, $taxonomy_location_url, true, $sitepress->get_default_language());
                }
                $city_id = icl_object_id($city_id, $taxonomy_location_url, true, $sitepress->get_default_language());
            }
        }
    }
} else {
    $err .= esc_html__('You need to select a country','escortwp')."<br />";
}

// ---------- Gender ----------
$gender = (int) escortwp_post('gender', 0);
if (!$gender || !isset($gender_a[$gender])) {
    $err .= esc_html__('Please choose your gender','escortwp')."<br />";
}

// ---------- DOB & Age (fixed) ----------
$dateday   = (int) escortwp_post('dateday', 0);
$datemonth = (int) escortwp_post('datemonth', 0);
$dateyear  = (int) escortwp_post('dateyear', 0);
if ($dateday && $datemonth && $dateyear) {
    if ($dateday < 1 || $dateday > 31)    { $err .= esc_html__('The day from your date of birth is wrong','escortwp')."<br />"; }
    if ($datemonth < 1 || $datemonth > 12){ $err .= esc_html__('The month from your date of birth is wrong','escortwp')."<br />"; }
    if (strlen((string)$dateyear) !== 4)   { $err .= esc_html__('The year from your date of birth is wrong','escortwp')."<br />"; }
    $dob_str = sprintf('%04d-%02d-%02d', $dateyear, $datemonth, $dateday);
    $dob_ts  = strtotime($dob_str.' 00:00:00');
    if ($dob_ts !== false) {
        $now = current_time('timestamp');
        $age = (int) floor(($now - $dob_ts) / 31556926);
        if ($age < 18) { $err .= esc_html__('You must be at least 18 years old to register on this site','escortwp')."<br />"; }
    } else {
        $err .= esc_html__('Your date of birth is invalid','escortwp')."<br />";
    }
} else {
    $err .= esc_html__('Please write your date of birth','escortwp')."<br />";
}

// ---------- Looks / attributes ----------
$ethnicity  = (int) escortwp_post('ethnicity', 0);
$haircolor  = (int) escortwp_post('haircolor', 0);
$hairlength = (int) escortwp_post('hairlength', 0);
$bustsize   = (int) escortwp_post('bustsize', 0);
$height     = (int) escortwp_post('height', 0);
$height2    = (int) escortwp_post('height2', 0);
$weight     = (int) escortwp_post('weight', 0);
$build      = (int) escortwp_post('build', 0);
$looks      = (int) escortwp_post('looks', 0);
$smoker     = (int) escortwp_post('smoker', 0);

if ($ethnicity) { if (!isset($ethnicity_a[$ethnicity])) { $err .= esc_html__('Choose your skin color','escortwp')."<br />"; } }
elseif (function_exists('ismand') && ismand('ethnicity','no')) { $err .= esc_html__('Choose your skin color','escortwp')."<br />"; }

if ($haircolor) { if (!isset($haircolor_a[$haircolor])) { $err .= esc_html__('Choose your hair color','escortwp')."<br />"; } }
elseif (function_exists('ismand') && ismand('haircolor','no')) { $err .= esc_html__('Choose your hair color','escortwp')."<br />"; }

if ($hairlength) { if (!isset($hairlength_a[$hairlength])) { $err .= esc_html__('Choose your hair length','escortwp')."<br />"; } }
elseif (function_exists('ismand') && ismand('hairlength','no')) { $err .= esc_html__('Choose your hair length','escortwp')."<br />"; }

if ($bustsize) { if (!isset($bustsize_a[$bustsize])) { $err .= esc_html__('Choose your bust size','escortwp')."<br />"; } }
elseif ((string)$gender === "1" && function_exists('ismand') && ismand('bustsize','no')) { $err .= esc_html__('Choose your bust size','escortwp')."<br />"; }

if ($height)  { if ($height  < 1) { $err .= esc_html__('Choose your height','escortwp')."<br />"; } }
elseif (function_exists('ismand') && ismand('height','no')) { $err .= esc_html__('Choose your height','escortwp')."<br />"; }

if ($height2 < 1) { $height2 = 0; }

if ($weight)  { if ($weight  < 1) { $err .= esc_html__('Choose your weight','escortwp')."<br />"; } }
elseif (function_exists('ismand') && ismand('weight','no')) { $err .= esc_html__('Choose your weight','escortwp')."<br />"; }

if ($build) { if (!isset($build_a[$build])) { $err .= esc_html__('Chose your built type','escortwp')."<br />"; } }
elseif (function_exists('ismand') && ismand('build','no')) { $err .= esc_html__('Chose your built type','escortwp')."<br />"; }

if ($looks) { if (!isset($looks_a[$looks])) { $err .= esc_html__('Choose your looks','escortwp')."<br />"; } }
elseif (function_exists('ismand') && ismand('looks','no')) { $err .= esc_html__('Choose your looks','escortwp')."<br />"; }

if ($smoker) { if (!isset($smoker_a[$smoker])) { $err .= esc_html__('Are you a smoker or a non smoker?','escortwp')."<br />"; } }
elseif (function_exists('ismand') && ismand('smoker','no')) { $err .= esc_html__('Are you a smoker or a non smoker?','escortwp')."<br />"; }

// Availability (1/2)
$availability = escortwp_post('availability', []);
if (!is_array($availability)) { $availability = []; }
foreach ($availability as $i => $one) {
    $one = preg_replace("/([^0-9])/", "", (string)$one);
    if ($one !== "1" && $one !== "2") { unset($availability[$i]); }
}
if (empty($availability) && function_exists('ismand') && ismand('availability','no')) {
    $err .= esc_html__('Please choose your availability','escortwp')."<br />";
}

// About you
$aboutyou = substr(wp_kses((string) escortwp_post('aboutyou'), array()), 0, 5000);
if (!$aboutyou && function_exists('ismand') && ismand('aboutyou','no')) {
    $err .= esc_html__('You must write something about you.','escortwp')."<br />";
}

// Misc text
$education         = substr(sanitize_text_field((string) escortwp_post('education')), 0, 300);
$sports            = substr(sanitize_text_field((string) escortwp_post('sports')), 0, 300);
$hobbies           = substr(sanitize_text_field((string) escortwp_post('hobbies')), 0, 300);
$zodiacsign        = substr(sanitize_text_field((string) escortwp_post('zodiacsign')), 0, 300);
$sexualorientation = substr(sanitize_text_field((string) escortwp_post('sexualorientation')), 0, 300);
$occupation        = substr(sanitize_text_field((string) escortwp_post('occupation')), 0, 300);

if (!$education && function_exists('ismand') && ismand('education','no')) { $err .= esc_html__('Please write an education','escortwp')."<br />"; }
if (!$sports && function_exists('ismand') && ismand('sports','no'))       { $err .= esc_html__('Please write what sports you like','escortwp')."<br />"; }
if (!$hobbies && function_exists('ismand') && ismand('hobbies','no'))     { $err .= esc_html__('Please write what hobbies you have','escortwp')."<br />"; }
if (!$zodiacsign && function_exists('ismand') && ismand('zodiacsign','no')) { $err .= esc_html__('Please write your zodiac sign','escortwp')."<br />"; }
if (!$sexualorientation && function_exists('ismand') && ismand('sexualorientation','no')) { $err .= esc_html__('Please write your sexual orientation','escortwp')."<br />"; }
if (!$occupation && function_exists('ismand') && ismand('occupation','no')) { $err .= esc_html__('Please write your occupation','escortwp')."<br />"; }

// Languages (+ levels)
$language1 = substr(sanitize_text_field((string) escortwp_post('language1')), 0, 300);
$language2 = substr(sanitize_text_field((string) escortwp_post('language2')), 0, 300);
$language3 = substr(sanitize_text_field((string) escortwp_post('language3')), 0, 300);

$language1level = null; $language2level = null; $language3level = null;

if ($language1) {
    $language1level = (int) escortwp_post('language1level');
    if (!isset($languagelevel_a[$language1level])) {
        $err .= esc_html__('Please choose a language level for','escortwp')." ".esc_html($language1)."<br />";
        $language1level = null;
    }
}
if ($language2) {
    $language2level = (int) escortwp_post('language2level');
    if (!isset($languagelevel_a[$language2level])) {
        $err .= esc_html__('Please choose a language level for','escortwp')." ".esc_html($language2)."<br />";
        $language2level = null;
    }
}
if ($language3) {
    $language3level = (int) escortwp_post('language3level');
    if (!isset($languagelevel_a[$language3level])) {
        $err .= esc_html__('Please choose a language level for','escortwp')." ".esc_html($language3)."<br />";
        $language3level = null;
    }
}
if (!$language1 && !$language2 && !$language3 && function_exists('ismand') && ismand('language','no')) {
    $err .= esc_html__('Please choose at least one language and conversation level','escortwp')."<br />";
}

// ---------- Rates & currency ----------
$rate_fields = [
    'rate30min_incall','rate1h_incall','rate2h_incall','rate3h_incall','rate6h_incall','rate12h_incall','rate24h_incall',
    'rate30min_outcall','rate1h_outcall','rate2h_outcall','rate3h_outcall','rate6h_outcall','rate12h_outcall','rate24h_outcall'
];
$rates = [];
foreach ($rate_fields as $rf) {
    $val = (int) escortwp_post($rf, 0);
    $rates[$rf] = $val > 0 ? $val : 0;
}
$rates_sum = array_sum($rates);

if ($rates_sum < 1 && function_exists('ismand') && ismand('rates','no')) {
    $err .= esc_html__('Please choose at least one rate price','escortwp')."<br />";
}

$currency = (int) escortwp_post('currency', 0);
if ((!isset($currency_a[$currency]) || !$currency) && function_exists('ismand') && ismand('rates','no')) {
    $err .= esc_html__('Please choose a currency','escortwp')."<br />";
    $currency = 0;
}

// ---------- Services ----------
$services = escortwp_post('services', []);
if (!is_array($services)) { $services = []; }
foreach ($services as $i => $service) {
    $service = preg_replace("/([^0-9])/", "", (string)$service);
    if ($service === '' && $service !== "0") { unset($services[$i]); }
    else { $services[$i] = (int)$service; }
}
sort($services);
if (empty($services) && function_exists('ismand') && ismand('services','no')) {
    $err .= esc_html__('You have to select at least one service','escortwp')."<br />";
} else {
    foreach ($services as $i => $service) {
        if (!isset($services_a[$service])) { unset($services[$i]); }
    }
    if (empty($services) && function_exists('ismand') && ismand('services','no')) {
        $err .= esc_html__('You have to select at least one service','escortwp')."<br />";
    }
}

$extraservices = substr(sanitize_text_field((string) escortwp_post('extraservices')), 0, 300);
if (function_exists('ismand') && ismand('extraservices','no') && !$extraservices) {
    $err .= esc_html__('Please write what other extra services you offer','escortwp')."<br />";
}

// ---------- Spam honeypot ----------
$emails_honeypot = (string) escortwp_post('emails', '');
if ($emails_honeypot !== '') { $err = ".<br />"; }

// ---------- reCAPTCHA ----------
if (get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && !is_user_logged_in() && get_option("recaptcha2")) {
    if (function_exists('verify_recaptcha')) { $err .= verify_recaptcha(); }
}

// ---------- TOS / Data protection ----------
$tos_accept = (int) escortwp_post('tos_accept', 0);
$tos_page_data = get_post((int) get_option('tos_page_id'));
$data_protection_page_data = get_post((int) get_option('data_protection_page_id'));
if (($tos_page_data || $data_protection_page_data) && !is_user_logged_in() && $tos_accept !== 1) {
    $err .= esc_html__('You need to agree to our terms and conditions in order to register','escortwp')."<br />";
}

// ---------- Stop if errors (let the form consume $err) ----------
if ($err) { return; }

// ---------- Save begins ----------
$single_page         = isset($single_page) ? $single_page : "";
$admin_adding_escort = isset($admin_adding_escort) ? $admin_adding_escort : "";

// New / Edit user setup
$new_user_id = 0;
if ($escort_post_id || $agencyid) {
    $new_user_id = (int) $current_user->ID;
    if ($admin_adding_escort === "yes" && $agencyid) { $new_user_id = (int) $agencyid; }
    if (get_option("escortid".$new_user_id) === $taxonomy_profile_url && !$agencyid && !$is_admin && isset($youremail)) {
        wp_update_user(array('ID' => $new_user_id, 'user_email' => $youremail));
    }
} else {
    // Create/link WP user for independent escort
    $existing_by_email = $youremail ? get_user_by('email', $youremail) : false;
    $existing_by_login = $user ? get_user_by('login', $user) : false;

    if ($is_admin && ($existing_by_email || $existing_by_login)) {
        // Link profile to an existing user (admin convenience)
        $link_user = $existing_by_email ?: $existing_by_login;
        $new_user_id = (int) $link_user->ID;
    } else {
        $new_user_id = wp_create_user($user, $pass, $youremail);
        if (is_wp_error($new_user_id)) {
            foreach ($new_user_id->errors as $error) {
                if (!empty($error[0])) { $err .= esc_html($error[0])."<br />"; }
            }
            return;
        }
        // Email validation hash (preserved logic)
        if ($admin_registers_independent_escort === "yes") {
            if ($sendverification === 1) {
                $emailhash = md5($new_user_id.$user.$youremail."1wg807xlhf4x66vxna30");
                update_user_meta($new_user_id, "emailhash", $emailhash);
            }
        } else {
            $emailhash = md5($new_user_id.$user.$youremail);
            update_user_meta($new_user_id, "emailhash", $emailhash);
        }
    }
}

// Update user display fields (not when agency id is used)
if (!$agencyid && $new_user_id) {
    wp_update_user(array(
        'ID'           => (int)$new_user_id,
        'nickname'     => $yourname,
        'display_name' => $yourname,
        'user_url'     => $website
    ));
}

// Mark brand-new accounts as escort type
if (!$escort_post_id && !$agencyid && $new_user_id) {
    update_option("escortid".$new_user_id, $taxonomy_profile_url);
}

// Create / Update post
if ($escort_post_id) {
    $post_escort = array(
        'ID'           => (int)$escort_post_id,
        'post_title'   => $yourname,
        'post_content' => $aboutyou,
        'post_name'    => sanitize_title($yourname)
    );
    $updated_post_id = wp_update_post($post_escort, true);
    if (is_wp_error($updated_post_id)) {
        wp_die(esc_html__('Could not update the profile. Please try again.','escortwp'));
    }
    $post_escort_id = (int)$escort_post_id;
} else {
    // Determine initial status (unchanged logic)
    $post_status = "private";
    if ($agencyid) {
        $post_status = "publish";
        if (get_option("manactivagescprof") === "1") { $post_status = "private"; }
        if (function_exists('payment_plans') && payment_plans('agescortreg','price')) { $post_status = "private"; }
    } else {
        if ($sendverification === 2) { $post_status = "publish"; }
        if (get_option("manactivindescprof") === "1" && !$is_admin) { $post_status = "private"; }
        if (function_exists('payment_plans') && payment_plans('indescreg','price')) { $post_status = "private"; }
    }

    $post_escort = array(
        'post_title'   => $yourname,
        'post_content' => $aboutyou,
        'post_name'    => sanitize_title($yourname),
        'post_status'  => $post_status,
        'post_author'  => (int)$new_user_id,
        'post_type'    => $taxonomy_profile_url,
        'ping_status'  => 'closed'
    );
    $post_escort_id = wp_insert_post($post_escort, true);
    if (is_wp_error($post_escort_id) || !$post_escort_id) {
        $msg = esc_html__('Could not create the profile. Please check required fields and try again.','escortwp');
        if (is_wp_error($post_escort_id)) { $msg .= ' '.esc_html($post_escort_id->get_error_message()); }
        wp_die($msg);
    }

    update_post_meta($post_escort_id, "ip", (string) $_SERVER['REMOTE_ADDR']);
    $host = @gethostbyaddr((string) $_SERVER['REMOTE_ADDR']);
    if ($host) { update_post_meta($post_escort_id, "hostname", $host); }

    if ($agencyid) {
        if (get_option("manactivagescprof") === "1") { update_post_meta($post_escort_id, "notactive", "1"); }
        if (function_exists('payment_plans') && payment_plans('agescortreg','price')) { update_post_meta($post_escort_id, "needs_payment", "1"); }
    } else {
        if (get_option("manactivindescprof") === "1") { update_post_meta($post_escort_id, "notactive", "1"); }
        if (function_exists('payment_plans') && payment_plans('indescreg','price')) { update_post_meta($post_escort_id, "needs_payment", "1"); }
    }
}

// Assign taxonomy
if (function_exists('icl_object_id') && $city_id) {
    $languages = apply_filters('wpml_active_languages', null, 'orderby=id&order=desc');
    if (!empty($languages) && is_array($languages)) {
        $city_id_arr = [];
        foreach ($languages as $l) {
            $mapped = icl_object_id($city_id, $taxonomy_location_url, true, $l['language_code']);
            if ($mapped) { $city_id_arr[] = (int)$mapped; }
        }
        if (!empty($city_id_arr)) { wp_set_post_terms($post_escort_id, $city_id_arr, $taxonomy_location_url); }
    } else {
        wp_set_post_terms($post_escort_id, [$city_id], $taxonomy_location_url);
    }
} else {
    if ($city_id) { wp_set_post_terms($post_escort_id, [$city_id], $taxonomy_location_url); }
}

// Save meta
update_post_meta($post_escort_id, "phone", $phone);
update_post_meta($post_escort_id, "phone_available_on", array_values($phone_available_on));
update_post_meta($post_escort_id, "website", $website);
update_post_meta($post_escort_id, "instagram", $instagram);
update_post_meta($post_escort_id, "snapchat", $snapchat);
update_post_meta($post_escort_id, "twitter", $twitter);
update_post_meta($post_escort_id, "facebook", $facebook);
if ($country) { update_post_meta($post_escort_id, "country", $country); }
if (function_exists('showfield') && showfield('state') && $state_id) { update_post_meta($post_escort_id, "state", $state_id); }
if ($city_id) { update_post_meta($post_escort_id, "city", $city_id); }

update_post_meta($post_escort_id, "gender", $gender);
update_post_meta($post_escort_id, "birthday", sprintf('%04d-%02d-%02d', $dateyear, $datemonth, $dateday));

if ($ethnicity)  { update_post_meta($post_escort_id, "ethnicity", $ethnicity); }
if ($haircolor)  { update_post_meta($post_escort_id, "haircolor", $haircolor); }
if ($hairlength) { update_post_meta($post_escort_id, "hairlength", $hairlength); }
if ($bustsize)   { update_post_meta($post_escort_id, "bustsize", $bustsize); }

if ($height)  { update_post_meta($post_escort_id, "height", $height); }
if ($height2) { update_post_meta($post_escort_id, "height2", $height2); }
if ($weight)  { update_post_meta($post_escort_id, "weight", $weight); }

if ($build) { update_post_meta($post_escort_id, "build", $build); }
if ($looks) { update_post_meta($post_escort_id, "looks", $looks); }
if ($smoker){ update_post_meta($post_escort_id, "smoker", $smoker); }

update_post_meta($post_escort_id, "availability", array_values($availability));

if ($education)         { update_post_meta($post_escort_id, "education", $education); }
if ($sports)            { update_post_meta($post_escort_id, "sports", $sports); }
if ($hobbies)           { update_post_meta($post_escort_id, "hobbies", $hobbies); }
if ($zodiacsign)        { update_post_meta($post_escort_id, "zodiacsign", $zodiacsign); }
if ($sexualorientation) { update_post_meta($post_escort_id, "sexualorientation", $sexualorientation); }
if ($occupation)        { update_post_meta($post_escort_id, "occupation", $occupation); }

if ($language1) { update_post_meta($post_escort_id, "language1", $language1); }
if ($language1level !== null) { update_post_meta($post_escort_id, "language1level", $language1level); }
if ($language2) { update_post_meta($post_escort_id, "language2", $language2); }
if ($language2level !== null) { update_post_meta($post_escort_id, "language2level", $language2level); }
if ($language3) { update_post_meta($post_escort_id, "language3", $language3); }
if ($language3level !== null) { update_post_meta($post_escort_id, "language3level", $language3level); }

if ($currency) { update_post_meta($post_escort_id, "currency", $currency); }

foreach ($rate_fields as $rf) {
    if (!empty($rates[$rf])) { update_post_meta($post_escort_id, $rf, (int)$rates[$rf]); }
    else { delete_post_meta($post_escort_id, $rf); }
}

update_post_meta($post_escort_id, "services", array_values($services));
if ($extraservices) { update_post_meta($post_escort_id, "extraservices", $extraservices); }

if (!$escort_post_id) { update_post_meta($post_escort_id, "premium", "0"); }

// CUSTOM: personal_phone (add/update)
$get_val = get_post_meta($post_escort_id, 'personal_phone', true);
if ($get_val === '' || $get_val === null) {
    add_post_meta($post_escort_id, "personal_phone", $personal_phone, true);
} else {
    update_post_meta($post_escort_id, "personal_phone", $personal_phone);
}

// ---------- Post-create / update redirects (ALWAYS to profile URL) ----------
if (!$escort_post_id) {
    // New profile: create secret, upload folder
    $secret = md5($yourname.$aboutyou.$phone.$website.time().rand(1,9999));
    update_post_meta($post_escort_id, "secret", $secret);
    update_post_meta($post_escort_id, "upload_folder", time().rand(1,999));

    if (!$agencyid) {
        // Independent
        if ($new_user_id) {
            update_option("escortpostid".$new_user_id, $post_escort_id);
            update_post_meta($post_escort_id, "independent", "yes");
            update_option($secret, $new_user_id);
        }

        // Email
        $emailtitle    = esc_html__('Email validation link','escortwp');
        $emailtext     = esc_html__('Before you can use the site you will need to validate your email address.','escortwp')
                         .'<br />'.esc_html__('If you don\'t validate your email in the next 3 days your account will be deleted.','escortwp')
                         .'<br /><br />'.esc_html__('Please validate your email address by clicking the link below','escortwp').':<br />';
        $emailtext_end = '<br /><br />'.esc_html__('You can view your account here','escortwp').':<br />'
                         .'<a href="'.esc_url(get_permalink($post_escort_id)).'">'.esc_html(get_permalink($post_escort_id)).'</a>';

        if ($sendverification === 2) {
            $emailtitle = esc_html__('Welcome to','escortwp');
            $emailtext  = esc_html__('Your account is now active on','escortwp').' '.esc_html(get_option("email_sitename")).'<br /><br />';
        } else {
            if (!empty($emailhash)) {
                $verify_link = add_query_arg('ekey', rawurlencode($emailhash), trailingslashit(get_bloginfo('url')));
                $emailtext  .= '<a href="'.esc_url($verify_link).'">'.esc_html($verify_link).'</a><br /><br />';
            }
        }
        $pass_to_send = $sendauth === 1 ? (string)$pass : '('.esc_html__('hidden','escortwp').')';

        if ($youremail && function_exists('dolce_email')) {
            $body = esc_html__('Hello','escortwp').' '.esc_html($yourname).',<br /><br />'
                .$emailtext
                .esc_html__('Account information','escortwp').':<br />'
                .esc_html__('type','escortwp').': <b>'.sprintf(esc_html__('independent %s','escortwp'), esc_html($taxonomy_profile_name)).'</b><br />'
                .esc_html__('username','escortwp').': <b>'.esc_html($user).'</b><br />'
                .esc_html__('password','escortwp').': <b>'.esc_html($pass_to_send).'</b>'
                .$emailtext_end;
            dolce_email("", "", $youremail, $emailtitle." ".get_option("email_sitename"), $body);
        }

        // Redirects
        if (!$is_admin && $new_user_id) {
            // Self-registration: log in and go to profile URL
            wp_clear_auth_cookie();
            wp_set_auth_cookie((int)$new_user_id, true);
        }
        wp_safe_redirect(escortwp_view_url($post_escort_id));
        exit;

    } else {
        // Agency created new escort
        if (function_exists('dolce_email')) {
            $body = esc_html__('Hello','escortwp').',<br /><br />'
                .sprintf(esc_html__('A new %s has been added on','escortwp'), esc_html($taxonomy_profile_name)).' '.esc_html(get_option("email_sitename")).':<br /><br />'
                .esc_html__('Account information','escortwp').':<br />'
                .esc_html__('type','escortwp').': <b>'.sprintf(esc_html__('%1$s added by %2$s','escortwp'), esc_html($taxonomy_profile_name), esc_html($taxonomy_agency_name)).'</b><br /><br />'
                .esc_html__('You can view the account here','escortwp').':<br />'
                .'<a href="'.esc_url(get_permalink($post_escort_id)).'">'.esc_html(get_permalink($post_escort_id)).'</a>';
            dolce_email(null, null, get_bloginfo("admin_email"), sprintf(esc_html__('New %s on','escortwp'), esc_html($taxonomy_profile_name))." ".get_option("email_sitename"), $body);
        }
        update_option("agency".$secret, (int)$post_escort_id);

        // Always to profile URL
        wp_safe_redirect(escortwp_view_url($post_escort_id));
        exit;
    }
} else {
    // Update flow complete — ALWAYS go to profile URL (even if private)
    wp_safe_redirect(escortwp_view_url($escort_post_id));
    exit;
}

// If execution ever reaches here (it shouldn't), just in case:
wp_safe_redirect(home_url('/'));
exit;
