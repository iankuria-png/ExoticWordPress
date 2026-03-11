<?php
/*
Template Name: Register - Member
*/
	$err = "";
	$ok = "";
if (isset($_POST['action']) && $_POST['action'] == 'registermember') {
	include (get_template_directory() . '/register-member-personal-info-process.php');
}

get_header(); ?>

		<div class="contentwrapper">
		<div class="body">
        	<div class="bodybox registerform">
            	<h3><?php _e('Member Registration','escortwp'); ?></h3>
				<?php
				if (is_user_logged_in()) {
					echo "<div class=\"ok rad25\">".__('Your registration is complete.','escortwp')."</div>";
				} else {
					include (get_template_directory() . '/register-member-personal-information-form.php');
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