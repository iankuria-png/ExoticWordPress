<?php
/*
Template Name: Register - Agency
*/

global $taxonomy_agency_url, $taxonomy_agency_name;
$current_user = wp_get_current_user();
if (is_user_logged_in()) {
	if (get_option("escortid".$current_user->ID) != $taxonomy_agency_url && !current_user_can('level_10')) { wp_redirect(get_bloginfo("url")); exit; }
}

	$err = "";
	$ok = "";
if (isset($_POST['action']) && $_POST['action'] == 'register') {
	if (current_user_can('level_10')) {
		$admin_adding_agency = "yes";
	}
	include (get_template_directory() . '/register-agency-personal-info-process.php');
}

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox registerform">
        	<h3><?php printf(esc_html__('Register as %s','escortwp'),ucwords($taxonomy_agency_name)); ?></h3>
			<?php
			if ( is_user_logged_in() && !current_user_can('level_10')) {
				echo "<div class=\"ok rad25\">".__('Your registration is complete','escortwp').".</div>";
			} else {
				include (get_template_directory() . '/register-agency-personal-information-form.php');
			}
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