<?php
/*
Template Name: Edit email options
*/

$current_user = wp_get_current_user();
if (!current_user_can('level_10')) { wp_redirect(get_bloginfo("url")); exit; }

$err = ""; $ok = "";
if (isset($_POST['action']) && $_POST['action'] == 'emailsettings') {
	if ($_POST['email_sitename']) {
		$email_sitename = $_POST['email_sitename'];
	} else { $err .= __('Choose a site name','escortwp')."<br />"; }

	if ($_POST['admin_email']) {
		$admin_email = $_POST['admin_email'];
		if ( !is_email($admin_email) ) { $err .= __('Your admin email seems to be wrong','escortwp')."<br />"; }
	} else { $err .= __('Choose an email for notifications','escortwp')."<br />"; }

	if ($_POST['email_siteemail']) {
		$email_siteemail = $_POST['email_siteemail'];
		if ( !is_email($email_siteemail) ) { $err .= __('Your site email address seems to be wrong','escortwp')."<br />"; }
	} else { $err .= __('Choose a site email address for the site','escortwp')."<br />"; }

	$email_signature = stripslashes($_POST["email_signature"]);

	$ifemail1 = (int)$_POST['ifemail1'];
	$ifemail2 = (int)$_POST['ifemail2'];
	$ifemail3 = (int)$_POST['ifemail3'];
	$ifemail4 = (int)$_POST['ifemail4'];
	$ifemail5 = (int)$_POST['ifemail5'];
	$ifemail6 = (int)$_POST['ifemail6'];
	$ifemail7 = (int)$_POST['ifemail7'];
	$ifemail8 = (int)$_POST['ifemail8'];
	$ifemail9 = (int)$_POST['ifemail9'];

	if(!$err) {
		update_option("admin_email", $admin_email);
		update_option("email_sitename", $email_sitename);
		update_option("email_siteemail", $email_siteemail);
		update_option("email_signature", $email_signature);
		
		update_option("ifemail1", $ifemail1);
		update_option("ifemail2", $ifemail2);
		update_option("ifemail3", $ifemail3);
		update_option("ifemail4", $ifemail4);
		update_option("ifemail5", $ifemail5);
		update_option("ifemail6", $ifemail6);
		update_option("ifemail7", $ifemail7);
		update_option("ifemail8", $ifemail8);
		update_option("ifemail9", $ifemail9);
		$ok = "ok";
	}
} else {
	$admin_email = get_option("admin_email");
	$email_sitename = get_option("email_sitename");
	$email_siteemail = get_option("email_siteemail");
	$email_signature = get_option("email_signature");
	$ifemail1 = get_option("ifemail1");
	$ifemail2 = get_option("ifemail2");
	$ifemail3 = get_option("ifemail3");
	$ifemail4 = get_option("ifemail4");
	$ifemail5 = get_option("ifemail5");
	$ifemail6 = get_option("ifemail6");
	$ifemail7 = get_option("ifemail7");
	$ifemail8 = get_option("ifemail8");
	$ifemail9 = get_option("ifemail9");
}

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox">
			<?php if ($err) { echo "<div class=\"err rad25\">$err</div>"; } ?>
			<?php if ($ok) { echo "<div class=\"ok rad25\">Your settings have been saved</div>"; } ?>
			<form action="<?php echo get_permalink(get_the_ID()); ?>" class="form-styling" method="post">
			<input type="hidden" name="action" value="emailsettings" />
				<h3 class="settingspagetitle"><?php _e('Site users will receive emails with the following sender','escortwp'); ?>:</h3>
				<div class="clear20"></div>
				<div class="form-label">
					<label for="email_sitename"><?php _e('Name','escortwp'); ?><i>*</i></label>
				</div>
				<div class="form-input">
					<input type="text" name="email_sitename" id="email_sitename" class="input longinput" value="<?php echo $email_sitename; ?>" />
				</div> <!-- site name --> <div class="formseparator"></div>

				<div class="form-label">
					<label for="email_siteemail"><?php _e('Email','escortwp'); ?><i>*</i></label>
				</div>
				<div class="form-input">
					<input type="email" name="email_siteemail" id="email_siteemail" class="input longinput" value="<?php echo $email_siteemail; ?>" />
				</div> <!-- site email --> <div class="formseparator"></div>

				<div class="form-label">
			    	<label for="email_signature"><?php _e('Email signature','escortwp'); ?></label>
			    	<small><i>!</i> <?php _e('This will be at the end of all emails','escortwp'); ?></small>
			    </div>
				<div class="form-input">
				    <textarea class="textarea longtextarea" name="email_signature" id="email_signature" rows="7"><?php echo $email_signature; ?></textarea>
					<small><?php _e('html allowed','escortwp'); ?></small>
			    </div> <!-- signature --> <div class="formseparator"></div>


				<div class="clear30"></div>
				<h3 class="settingspagetitle"><?php _e('When do you want to receive an email?','escortwp'); ?></h3>
				<div class="clear10"></div>

				<div class="form-label">
					<label for="admin_email"><?php _e('Your email','escortwp'); ?><i>*</i></label>
					<small><i>!</i> <?php _e('Where to receive email notifications','escortwp'); ?></small>
				</div>
				<div class="form-input">
					<input type="email" name="admin_email" id="admin_email" class="input longinput" value="<?php echo $admin_email; ?>" />
				</div> <!-- admin email --> <div class="formseparator"></div>

				<div class="form-label">
			    	<label><?php printf(esc_html__('Someone ads an %s to the blacklist','escortwp'),$taxonomy_profile_name); ?></label>
			    </div>
				<div class="form-input">
				    <label for="ifemail1yes"><input type="radio" name="ifemail1" value="1" id="ifemail1yes"<?php if($ifemail1 == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    	<label for="ifemail1no"><input type="radio" name="ifemail1" value="2" id="ifemail1no"<?php if($ifemail1 == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
			    </div> <!-- --> <div class="formseparator"></div>

				<div class="form-label">
			    	<label><?php printf(esc_html__('%s registration','escortwp'),ucfirst($taxonomy_agency_name)); ?></label>
			    </div>
				<div class="form-input">
				    <label for="ifemail2yes"><input type="radio" name="ifemail2" value="1" id="ifemail2yes"<?php if($ifemail2 == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    	<label for="ifemail2no"><input type="radio" name="ifemail2" value="2" id="ifemail2no"<?php if($ifemail2 == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
			    </div> <!-- --> <div class="formseparator"></div>

				<div class="form-label">
			    	<label><?php printf(esc_html__('%s registration','escortwp'),ucfirst($taxonomy_profile_name)); ?></label>
			    </div>
				<div class="form-input">
				    <label for="ifemail3yes"><input type="radio" name="ifemail3" value="1" id="ifemail3yes"<?php if($ifemail3 == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    	<label for="ifemail3no"><input type="radio" name="ifemail3" value="2" id="ifemail3no"<?php if($ifemail3 == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
			    </div> <!-- --> <div class="formseparator"></div>

			    <div class="form-label">
			    	<label><?php _e('Member registration','escortwp'); ?></label>
			    </div>
				<div class="form-input">
				    <label for="ifemail4yes"><input type="radio" name="ifemail4" value="1" id="ifemail4yes"<?php if($ifemail4 == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    	<label for="ifemail4no"><input type="radio" name="ifemail4" value="2" id="ifemail4no"<?php if($ifemail4 == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
			    </div> <!-- --> <div class="formseparator"></div>

			    <div class="form-label">
			    	<label><?php printf(esc_html__('%s has new review','escortwp'),ucfirst($taxonomy_agency_name)); ?></label>
			    </div>
				<div class="form-input">
				    <label for="ifemail5yes"><input type="radio" name="ifemail5" value="1" id="ifemail5yes"<?php if($ifemail5 == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    	<label for="ifemail5no"><input type="radio" name="ifemail5" value="2" id="ifemail5no"<?php if($ifemail5 == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
			    </div> <!-- --> <div class="formseparator"></div>

			    <div class="form-label">
			    	<label><?php printf(esc_html__('%s has new review','escortwp'),ucfirst($taxonomy_profile_name)); ?></label>
			    </div>
				<div class="form-input">
				    <label for="ifemail6yes"><input type="radio" name="ifemail6" value="1" id="ifemail6yes"<?php if($ifemail6 == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    	<label for="ifemail6no"><input type="radio" name="ifemail6" value="2" id="ifemail6no"<?php if($ifemail6 == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
			    </div> <!-- --> <div class="formseparator"></div>

			    <div class="form-label">
			    	<label><?php _e('Someone posts a new ad','escortwp'); ?></label>
			    </div>
				<div class="form-input">
				    <label for="ifemail8yes"><input type="radio" name="ifemail8" value="1" id="ifemail8yes"<?php if($ifemail8 == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    	<label for="ifemail8no"><input type="radio" name="ifemail8" value="2" id="ifemail8no"<?php if($ifemail8 == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
			    </div> <!-- --> <div class="formseparator"></div>

			    <div class="form-label">
			    	<label><?php printf(esc_html__('%1$s / %2$s makes a payment','escortwp'),ucfirst($taxonomy_profile_name),$taxonomy_agency_name); ?></label>
			    </div>
				<div class="form-input">
				    <label for="ifemail7yes"><input type="radio" name="ifemail7" value="1" id="ifemail7yes"<?php if($ifemail7 == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    	<label for="ifemail7no"><input type="radio" name="ifemail7" value="2" id="ifemail7no"<?php if($ifemail7 == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
			    </div> <!-- --> <div class="formseparator"></div>

			    <div class="form-label">
			    	<label><?php _e('When admin manually adds Premium, Featured or Verified to a profile','escortwp'); ?></label>
			    </div>
				<div class="form-input">
			    	<label for="ifemail9yes"><input type="radio" name="ifemail9" value="1" id="ifemail9yes"<?php if($ifemail9 == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    	<label for="ifemail9no"><input type="radio" name="ifemail9" value="2" id="ifemail9no"<?php if($ifemail9 == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
			    </div> <!-- --> <div class="formseparator"></div>

			    <div class="form-label">
			    	<label><?php _e('Verified status image upload','escortwp'); ?></label>
			    </div>
				<div class="form-input">
			    	<?php _e('Yes','escortwp'); ?>
			    </div> <!-- --> <div class="formseparator"></div>

			    <div class="form-label">
			    	<label><?php _e('Contact form message','escortwp'); ?></label>
			    </div>
				<div class="form-input">
			    	<?php _e('Yes','escortwp'); ?>
			    </div> <!-- --> <div class="formseparator"></div>

				<div class="text-center"><input type="submit" name="submit" value="<?php _e('Save settings','escortwp'); ?>" class="pinkbutton rad3" /></div> <!--center-->
			</form>
            <div class="clear"></div>
        </div> <!-- BODY BOX -->
    </div> <!-- BODY -->
    </div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>

<?php get_footer(); ?>