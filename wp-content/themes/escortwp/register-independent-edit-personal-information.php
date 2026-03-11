<?php
/*
Template Name: Register Independent - Edit Personal Information
*/

global $taxonomy_profile_url, $taxonomy_location_url;
$current_user = wp_get_current_user();
if (!is_user_logged_in() || get_option("escortid".$current_user->ID) != $taxonomy_profile_url) { wp_redirect(get_bloginfo("url")); exit; }


	$err = "";
	$ok = "";
if (isset($_POST['action']) && $_POST['action'] == 'register') {
	include (get_template_directory() . '/register-independent-personal-info-process.php');
} else {
	$escort_post_id = get_option("escortpostid".$current_user->ID);
	$escort = get_post($escort_post_id);

	$aboutyou = $escort->post_content;
	$youremail = $current_user->user_email;
	$yourname = $current_user->display_name;

	$phone = get_post_meta($escort_post_id, "phone", true);
	$phone_available_on = get_post_meta($escort_post_id, "phone_available_on", true);
	$escortemail = get_post_meta($escort_post_id, "escortemail", true);
	$website = get_post_meta($escort_post_id, "website", true);
	$instagram = get_post_meta($escort_post_id, "instagram", true);
	$snapchat = get_post_meta($escort_post_id, "snapchat", true);
	$twitter = get_post_meta($escort_post_id, "twitter", true);
	$facebook = get_post_meta($escort_post_id, "facebook", true);

	$city_data = wp_get_post_terms($escort_post_id, $taxonomy_location_url);
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
	$birthday = explode("-", $birthday);
	$dateyear = $birthday[0];
	$datemonth = $birthday[1];
	$dateday = $birthday[2];
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
	$services = get_post_meta($escort_post_id, "services");
	$services = $services[0];
	$extraservices = get_post_meta($escort_post_id, "extraservices", true);
	$secret = get_post_meta($escort_post_id, "secret", true);
	$upload_folder = get_post_meta($escort_post_id, "upload_folder", true);
	$education = get_post_meta($escort_post_id,'education', true);
	$sports = get_post_meta($escort_post_id,'sports', true);
	$hobbies = get_post_meta($escort_post_id,'hobbies', true);
	$zodiacsign = get_post_meta($escort_post_id,'zodiacsign', true);
	$sexualorientation = get_post_meta($escort_post_id,'sexualorientation', true);
	$occupation = get_post_meta($escort_post_id,'occupation', true);
} // if is user logged in
?>
<?php get_header(); ?>

		<div class="contentwrapper">
		<div class="body">
        	<div class="bodybox registerform">
            	<h3><?php _e('Edit my Profile','escortwp'); ?></h3>
				<?php
					if ($ok) echo "<div class=\"ok rad25\">".__('Profile updated','escortwp')."</div>";
					include (get_template_directory() . '/register-independent-personal-information-form.php');
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