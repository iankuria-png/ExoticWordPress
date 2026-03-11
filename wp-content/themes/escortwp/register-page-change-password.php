<?php
/*
Template Name: Change  Account Password
*/

global $taxonomy_profile_url, $taxonomy_agency_url;
$current_user = wp_get_current_user();
if (!in_array(get_option("escortid".$current_user->ID), array($taxonomy_agency_url, $taxonomy_profile_url, 'member'))) { wp_redirect(get_bloginfo("url")); exit; }

$err = ""; $ok = "";
if (isset($_POST['action']) && $_POST['action'] == 'change' && is_user_logged_in()) {
    $pass = $_POST['pass'];
	if ($pass) {
		if (strlen($pass) < 6 || strlen($pass) > 50) {
			$err .= __('Your password must be between 6 and 50 characters','escortwp')."<br />";
			unset($pass);
		} else {
			if ( false !== strpos( stripslashes($pass), "\\" ) ) {
				$err .= __('Passwords may not contain the character "\"','escortwp')."<br />";
			} else {
				wp_update_user( array ('ID' => $current_user->ID, 'user_pass' => $pass) ) ;
				$ok = __('Your password has been updated','escortwp');
				unset($pass);
			}
		}
	} else {
		$err .= __('The password field is empty','escortwp')."<br />";
	}
}

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox registerform">
        	<h3><?php _e('Change Account Password','escortwp'); ?></h3>
			<?php if ($ok) { echo "<div class=\"ok rad25\">$ok</div>"; } ?>
			<?php if ($err) { echo "<div class=\"err rad25\">$err</div>"; } ?>
			<form action="<?php echo get_permalink(get_option('change_password_page_id')); ?>" method="post" class="form-styling">
				<input type="hidden" name="action" value="change" />
				<div class="clear20"></div>
				<div class="form-label">
			    	<label for="pass"><?php _e('New Password','escortwp'); ?></label>
			    	<small><?php _e('Must be between 6 and 50 characters','escortwp'); ?></small>
			    </div>
			    <div class="form-input">
			    	<input type="password" name="pass" id="pass" class="input longinput" value="" autocomplete="off" />
			    </div> <!-- password --> <div class="form-separator"></div>

			    <div class="clear20"></div>
			    <div class="text-center"><input type="submit" name="submit" value="<?php _e('Update Password','escortwp'); ?>" class="pinkbutton rad3" /></div> <!--center-->
			</form>
            <div class="clear"></div>
        </div> <!-- BODY BOX -->
        <div class="clear"></div>
    </div> <!-- BODY -->
	</div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>

<?php get_footer(); ?>