<?php
/*
Template Name: Agency Edit Personal Information
*/

global $taxonomy_agency_name, $taxonomy_agency_url;
$current_user = wp_get_current_user();
if (get_option("escortid".$current_user->ID) != $taxonomy_agency_url) { wp_redirect(get_bloginfo("url")); exit; }

$err = ""; $ok = "";
global $taxonomy_location_url;
if (isset($_POST['action']) && $_POST['action'] == 'register') {
	include (get_template_directory() . '/register-agency-personal-info-process.php');
} else {
	$agency_post_id = get_option("agencypostid".$current_user->ID);
	$agency = get_post($agency_post_id);

	$aboutagency = $agency->post_content;
	$agencyemail = $current_user->user_email;
	$agencyname = $current_user->display_name;

	$phone = get_post_meta($agency_post_id, "phone", true);
	$website = $current_user->user_url;

	$country = get_post_meta($agency_post_id, "country", true);

	if(showfield('state')) {
		$state_id = get_post_meta($agency_post_id, "state", true);
		$state = get_term($state_id, $taxonomy_location_url);
		$state = $state->name;
	}

	$city_id = get_post_meta($agency_post_id, "city", true);
	$city = get_term($city_id, $taxonomy_location_url);
	$city = $city->name;
}

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox registerform">
        	<h3><?php printf(esc_html__('Edit %s Profile','escortwp'),$taxonomy_agency_name); ?></h3>
			<?php
				if ($ok) { echo "<div class=\"ok rad25\">".__('Profile updated','escortwp')."</div>"; }
				include (get_template_directory() . '/register-agency-personal-information-form.php');
			?>
            <div class="clear"></div>
        </div> <!-- BODY BOX -->
        <div class="clear"></div>
    </div> <!-- BODY -->
    </div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>
<?php get_footer(); ?>