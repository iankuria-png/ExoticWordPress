<?php
/*
Template Name: Contact page
*/

$err = ""; $ok = "";
if (isset($_POST['action']) && $_POST['action'] == 'contactus') {
	if ($_POST['emails']) { $err .= "."; }

	$contactformname = wp_strip_all_tags($_POST['contactformname']);
	if (!$contactformname) { $err .= __('Your name is missing','escortwp')."<br />"; }

	$contactformemail = $_POST['contactformemail'];
	if ($contactformemail) {
		if ( !is_email($contactformemail) ) { $err .= __('The email address seems to be wrong','escortwp')."<br />"; }
	} else {
		$err .= __('Your email is missing','escortwp')."<br />";
	}

	$contactformwebsite = substr(wp_strip_all_tags($_POST['contactformwebsite']), 0, 200);

	$contactformmess = substr(stripslashes(wp_kses($_POST['contactformmess'], array())), 0, 5000);
	if (!$contactformmess) { $err .= __('You need to write a message','escortwp')."<br />"; }

	if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && get_option("recaptcha1")) { $err .= verify_recaptcha(); }

	if (!$err) {
		$body = __('Hello','escortwp').',<br /><br />'.__('Someone sent you a message from','escortwp')." ".get_option("email_sitename").':<br /><br />
'.__('Sender information','escortwp').':<br />
'.__('Name','escortwp').': <b>'.$contactformname.'</b><br />
'.__('Email','escortwp').': <b>'.$contactformemail.'</b><br />
'.__('Website','escortwp').': <b>'.$contactformwebsite.'</b><br />
'.__('Message','escortwp').':<br />'.$contactformmess.'<br /><br />
'.__('You can send a message back to this person by replying to this email','escortwp').'.';

		dolce_email($contactformname, $contactformemail, get_bloginfo("admin_email"), __('Contact message from','escortwp')." ".get_option("email_sitename"), $body, $contactformmess);
		$ok = __('Message sent','escortwp');
	}
}

if (is_user_logged_in() && !isset($_POST['action'])) {
	$contactformname = $current_user->display_name;
	$contactformemail = $current_user->user_email;
}

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox">
        	<h3><?php _e('Contact us','escortwp'); ?></h3>
			<?php if (have_posts()) : ?>
				<div class="clear"></div>
				<?php while (have_posts()) : the_post(); ?>
		                <?php the_content(); ?><?php edit_post_link(__('Click to add some text here','escortwp'), '<br />', ''); ?>
				<?php endwhile; ?>
			<?php endif; ?>
			<div class="clear"></div>
			<?php if ($err) { echo '<div class="err rad25">'.$err.'</div>'; } ?>
			<?php if ($ok) { echo '<div class="ok rad25">'.$ok.'</div>'; } ?>
			<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="form-styling">
				<input type="hidden" name="action" value="contactus" />
				<input type="text" name="emails" value="" style="display:none;" />
				<div class="form-label">
					<label for="contactformname"><?php _e('Name','escortwp'); ?>: <i>*</i></label>
				</div>
				<div class="form-input">
					<input type="text" name="contactformname" id="contactformname" class="input" value="<?php echo $contactformname; ?>" />
				</div> <!-- name --> <div class="formseparator"></div>

				<div class="form-label">
					<label for="contactformemail"><?php _e('Email','escortwp'); ?>: <i>*</i></label>
				</div>
				<div class="form-input">
					<input type="email" name="contactformemail" id="contactformemail" class="input" value="<?php echo $contactformemail; ?>" />
				</div> <!-- email --> <div class="formseparator"></div>

				<div class="form-label">
					<label for="contactformwebsite"><?php _e('Website','escortwp'); ?>: </label>
				</div>
				<div class="form-input">
					<input type="text" name="contactformwebsite" id="contactformwebsite" class="input" value="<?php echo $contactformwebsite; ?>" />
				</div> <!-- website --> <div class="formseparator"></div>

				<div class="form-label">
					<label for="contactformmess"><?php _e('Message','escortwp'); ?>: <i>*</i></label>
				</div>
				<div class="form-input">
					<textarea name="contactformmess" id="contactformmess" class="textarea" rows="7" cols="42"><?php echo $contactformmess; ?></textarea><br /><small><?php _e('html code will be removed','escortwp'); ?></small>
				</div> <!-- message --> <div class="formseparator"></div>

			    <?php if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && get_option("recaptcha1")) { ?>
				<div class="form-input">
					<div class="g-recaptcha" data-sitekey="<?php echo get_option('recaptcha_sitekey'); ?>"></div>
				</div> <!-- message --> <div class="formseparator"></div>
			    <?php } ?>

				<div class="clear"></div>
                <div class="text-center"><input type="submit" name="submit" value="<?php _e('Send message','escortwp'); ?>" class="pinkbutton rad3" /></div> <!--center-->
            </form>
            <div class="clear"></div>
        </div> <!-- BODY BOX -->
    </div> <!-- BODY -->
    </div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>

<?php get_footer(); ?>