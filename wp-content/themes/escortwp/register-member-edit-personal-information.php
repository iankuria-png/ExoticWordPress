<?php
/*
Template Name: Member Edit Personal Information
*/

$current_user = wp_get_current_user();
if (!is_user_logged_in() || get_option("escortid".$current_user->ID) != "member") { wp_redirect(get_bloginfo("url")); exit; }

$err = ""; $ok = "";
if (isset($_POST['action']) && $_POST['action'] == 'registermember') {
	$member_edit_page = "yes";
	include (get_template_directory() . '/register-member-personal-info-process.php');
} else {
	$membername = $current_user->display_name;
	$memberemail = $current_user->user_email;
}

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox registerform">
        	<h3><?php _e('Edit Your Profile','escortwp'); ?></h3>
			<?php
			if ($ok) echo "<div class=\"ok rad25\">".__('Profile updated','escortwp')."</div>";

			$member_edit_page = "yes";
			include (get_template_directory() . '/register-member-personal-information-form.php');
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