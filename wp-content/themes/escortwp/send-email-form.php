<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }
?>
<div class="escortcontact rad5 hide"<?php if (isset($err) && $_POST['action'] == "contactform") { echo ' style="display: block;"'; } ?>>
	<?php closebtn(); ?>
	<?php if (is_user_logged_in() || get_option("unregsendcontactform") == "1") { ?>
	    <form action="<?php echo get_permalink(get_the_ID()); ?>#contactform" method="post" class="form-styling">
		    <input type="hidden" name="action" value="contactform" />
			<input type="text" name="emails" value="" class="hide" />
			<?php if(!is_user_logged_in()) { ?>
			<div class="clear"></div>
			<div class="form-label col100">
				<label for="contactformemail"><?php _e('Email','escortwp'); ?> <i>*</i></label>
			</div>
			<div class="form-input col100">
				<input type="text" name="contactformemail" id="contactformemail" class="input col100" value="" />
			</div>
			<div class="formseparator"></div>
			<?php } ?>

			<div class="form-label col100">
				<label for="contactformmess"><?php _e('Message','escortwp'); ?><?php if(!is_user_logged_in()) { echo " <i>*</i>"; } ?></label>
				<small><?php _e('Be sure to include your name and phone number','escortwp'); ?></small>
			</div>
			<div class="form-input col100">
				<?php
				if(!isset($contactformmess)) $contactformmess = "";
				?>
				<textarea name="contactformmess" id="contactformmess" class="textarea col100" cols="8" rows="7"><?php echo $contactformmess; ?></textarea>
			</div> <div class="formseparator"></div>

		    <?php if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && get_option("recaptcha5") && !is_user_logged_in()) { ?>
			<div class="form-input col100">
				<div class="g-recaptcha" data-sitekey="<?php echo get_option('recaptcha_sitekey'); ?>"></div>
			</div> <!-- message --> <div class="formseparator"></div>
		    <?php } ?>

		    <div class="text-center"><input type="submit" name="submit" value="<?php _e('Send message','escortwp'); ?>" class="pinkbutton rad3 center" /></div>
	    </form>
	<?php } else { ?>
		<div class="err rad25"><?php _e('You need to','escortwp'); ?> <a href="<?php echo get_permalink(get_option('main_reg_page_id')); ?>"><?php _e('register','escortwp'); ?></a> <?php _e('or','escortwp'); ?> <a href="<?php echo wp_login_url(get_permalink()); ?>"><?php _e('login','escortwp'); ?></a> <?php _e('to be able to send messages','escortwp'); ?></div>
	<?php } ?>
</div> <!-- contact form -->