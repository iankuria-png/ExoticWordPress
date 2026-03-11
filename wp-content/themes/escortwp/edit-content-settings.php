<?php
/*
Template Name: Content Settings
*/

global $taxonomy_profile_name, $taxonomy_profile_name_plural;
$current_user = wp_get_current_user();
if (!current_user_can('level_10')) { wp_redirect(get_bloginfo("url")); exit; }

$err = ""; $ok = "";
if (isset($_POST['action']) && $_POST['action'] == 'hidesections') {
	$showheaderslider = (int)$_POST['showheaderslider'];
	$autoscrollheaderslider = (int)$_POST['autoscrollheaderslider'];
	$headerslideritems = (int)$_POST['headerslideritems'];
	$showheadersliderall = (int)$_POST['showheadersliderall'];
	$showheadersliderfront = (int)$_POST['showheadersliderfront'];
	$showheaderslideresccat = (int)$_POST['showheaderslideresccat'];
	$showheadersliderescprof = (int)$_POST['showheadersliderescprof'];
	$showheaderslideragprof = (int)$_POST['showheaderslideragprof'];
	$showheaderslidersearch = (int)$_POST['showheaderslidersearch'];
	$showheadersliderct = (int)$_POST['showheadersliderct'];
	$showheadersliderrev = (int)$_POST['showheadersliderrev'];
	$showheadersliderads = (int)$_POST['showheadersliderads'];
	$locationsliderpage = (int)$_POST['locationsliderpage'];

	$frontpageshowpremium = (int)$_POST['frontpageshowpremium'];
	$frontpageshowpremiumcols = (int)$_POST['frontpageshowpremiumcols'];
	if ($frontpageshowpremiumcols < 1) { $frontpageshowpremiumcols = "1"; }
	$frontpageshowonline = (int)$_POST['frontpageshowonline'];
	$frontpageshowonlinecols = (int)$_POST['frontpageshowonlinecols'];
	if ($frontpageshowonlinecols < 1) { $frontpageshowonlinecols = "1"; }
	$frontpageshownormal = (int)$_POST['frontpageshownormal'];
	$frontpageshownormalcols = (int)$_POST['frontpageshownormalcols'];
	if ($frontpageshownormalcols < 1) { $frontpageshownormalcols = "1"; }
	$frontpageshowrev = (int)$_POST['frontpageshowrev'];
	$frontpageshowrevitems = (int)$_POST['frontpageshowrevitems'];
	if ($frontpageshowrevitems < 1) { $frontpageshowrevitems = "1"; }
	$frontpageshowrevchars = (int)$_POST['frontpageshowrevchars'];
	if ($frontpageshowrevchars < 1) { $frontpageshowrevchars = "100"; }

	$tos18 = (int)$_POST['tos18'];

	$quickescortsearch = (int)$_POST['quickescortsearch'];
	$hideunchedkedservices = (int)$_POST['hideunchedkedservices'];
	$hide1 = (int)$_POST['hide1'];
	$hide2 = (int)$_POST['hide2'];
	$hide3 = (int)$_POST['hide3'];
	$hide31 = (int)$_POST['hide31'];
	$hide4 = (int)$_POST['hide4'];
	$hide5 = (int)$_POST['hide5'];
	$hide10 = (int)$_POST['hide10'];
	$hide6 = (int)$_POST['hide6'];
	$hide7 = (int)$_POST['hide7'];
	$hide8 = (int)$_POST['hide8'];
	$hide9 = (int)$_POST['hide9'];

	$unregsendcontactform = (int)$_POST['unregsendcontactform'];

	$recaptcha1 = (int)$_POST['recaptcha1'];
	$recaptcha2 = (int)$_POST['recaptcha2'];
	$recaptcha3 = (int)$_POST['recaptcha3'];
	$recaptcha4 = (int)$_POST['recaptcha4'];
	$recaptcha5 = (int)$_POST['recaptcha5'];
	$recaptcha6 = (int)$_POST['recaptcha6'];
	$recaptcha_sitekey = preg_replace("/([^a-zA-Z0-9_-])/", "", $_POST['recaptcha_sitekey']);
	$recaptcha_secretkey = preg_replace("/([^a-zA-Z0-9_-])/", "", $_POST['recaptcha_secretkey']);

	if($recaptcha1 || $recaptcha2 || $recaptcha3 || $recaptcha4 || $recaptcha5 || $recaptcha6) {
		if(!$recaptcha_sitekey) {
			$err .= __('Please enter a site key for reCAPTCHA.','escortwp')."<br />";
		}
		if(!$recaptcha_secretkey) {
			$err .= __('Please enter your secret key for reCAPTCHA.','escortwp')."<br />";
		}
	}

	$locationdropdown = (int)$_POST['locationdropdown'];

	$hitcounter1 = (int)$_POST['hitcounter1'];
	$hitcounter2 = (int)$_POST['hitcounter2'];
	$hitcounter3 = (int)$_POST['hitcounter3'];

	$watermark_position = substr(sanitize_text_field($_POST['watermark_position']), 0, 2);

	if($hide9 == "1") { $hide1 = "1"; } // if member registration is disabled also disable the reviews
	if($hide2 == "1" && $hide3 == "1" && $hide9 == "1") { $hide31 = "1"; } // if member registration is disabled also disable the reviews

	if(!$err) {
		update_option("showheaderslider", $showheaderslider);
		update_option("autoscrollheaderslider", $autoscrollheaderslider);
		update_option("headerslideritems", $headerslideritems);
		update_option("showheadersliderall", $showheadersliderall);
		update_option("showheadersliderfront", $showheadersliderfront);
		update_option("showheaderslideresccat", $showheaderslideresccat);
		update_option("showheadersliderescprof", $showheadersliderescprof);
		update_option("showheaderslideragprof", $showheaderslideragprof);
		update_option("showheaderslidersearch", $showheaderslidersearch);
		update_option("showheadersliderct", $showheadersliderct);
		update_option("showheadersliderrev", $showheadersliderrev);
		update_option("showheadersliderads", $showheadersliderads);
		update_option("locationsliderpage", $locationsliderpage);

		update_option("frontpageshowpremium", $frontpageshowpremium);
		update_option("frontpageshowpremiumcols", $frontpageshowpremiumcols);
		update_option("frontpageshowonline", $frontpageshowonline);
		update_option("frontpageshowonlinecols", $frontpageshowonlinecols);
		update_option("frontpageshownormal", $frontpageshownormal);
		update_option("frontpageshownormalcols", $frontpageshownormalcols);
		update_option("frontpageshowrev", $frontpageshowrev);
		update_option("frontpageshowrevitems", $frontpageshowrevitems);
		update_option("frontpageshowrevchars", $frontpageshowrevchars);

		update_option("tos18", $tos18);
		update_option("quickescortsearch", $quickescortsearch);
		update_option("hideunchedkedservices", $hideunchedkedservices);
		update_option("hide1", $hide1);
		update_option("hide2", $hide2);
		update_option("hide3", $hide3);
		update_option("hide31", $hide31);
		update_option("hide4", $hide4);
		update_option("hide5", $hide5);
		update_option("hide10", $hide10);
		update_option("hide6", $hide6);
		update_option("hide7", $hide7);
		update_option("hide8", $hide8);
		update_option("hide9", $hide9);

		update_option("unregsendcontactform", $unregsendcontactform);

		update_option("recaptcha1", $recaptcha1);
		update_option("recaptcha2", $recaptcha2);
		update_option("recaptcha3", $recaptcha3);
		update_option("recaptcha4", $recaptcha4);
		update_option("recaptcha5", $recaptcha5);
		update_option("recaptcha6", $recaptcha6);
		update_option("recaptcha_sitekey", $recaptcha_sitekey);
		update_option("recaptcha_secretkey", $recaptcha_secretkey);

		update_option("locationdropdown", $locationdropdown);

		update_option("hitcounter1", $hitcounter1);
		update_option("hitcounter2", $hitcounter2);
		update_option("hitcounter3", $hitcounter3);

		update_option("watermark_position", $watermark_position);

		$ok = "ok";
	}
} else {
	$showheaderslider = get_option("showheaderslider");
	$autoscrollheaderslider = get_option("autoscrollheaderslider");
	$headerslideritems = get_option("headerslideritems");
	$showheadersliderall = get_option("showheadersliderall");
	$showheadersliderfront = get_option("showheadersliderfront");
	$showheaderslideresccat = get_option("showheaderslideresccat");
	$showheadersliderescprof = get_option("showheadersliderescprof");
	$showheaderslideragprof = get_option("showheaderslideragprof");
	$showheaderslidersearch = get_option("showheaderslidersearch");
	$showheadersliderct = get_option("showheadersliderct");
	$showheadersliderrev = get_option("showheadersliderrev");
	$showheadersliderads = get_option("showheadersliderads");
	$locationsliderpage = get_option("locationsliderpage");

	$frontpageshowpremium = get_option("frontpageshowpremium");
	$frontpageshowpremiumcols = get_option("frontpageshowpremiumcols");
	$frontpageshowonline = get_option("frontpageshowonline");
	$frontpageshowonlinecols = get_option("frontpageshowonlinecols");
	$frontpageshownormal = get_option("frontpageshownormal");
	$frontpageshownormalcols = get_option("frontpageshownormalcols");
	$frontpageshowrev = get_option("frontpageshowrev");
	$frontpageshowrevitems = get_option("frontpageshowrevitems");
	$frontpageshowrevchars = get_option("frontpageshowrevchars");

	$manactivesc = get_option("manactivesc");
	$manactivag = get_option("manactivag");

	$tos18 = get_option("tos18");
	$quickescortsearch = get_option("quickescortsearch");
	$hideunchedkedservices = get_option("hideunchedkedservices");
	$hide1 = get_option("hide1");
	$hide2 = get_option("hide2");
	$hide3 = get_option("hide3");
	$hide31 = get_option("hide31");
	$hide4 = get_option("hide4");
	$hide5 = get_option("hide5");
	$hide10 = get_option("hide10");
	$hide6 = get_option("hide6");
	$hide7 = get_option("hide7");
	$hide8 = get_option("hide8");
	$hide9 = get_option("hide9");

	$unregsendcontactform = get_option("unregsendcontactform");

	$recaptcha1 = get_option("recaptcha1");
	$recaptcha2 = get_option("recaptcha2");
	$recaptcha3 = get_option("recaptcha3");
	$recaptcha4 = get_option("recaptcha4");
	$recaptcha5 = get_option("recaptcha5");
	$recaptcha6 = get_option("recaptcha6");
	$recaptcha_sitekey = get_option("recaptcha_sitekey");
	$recaptcha_secretkey = get_option("recaptcha_secretkey");

	$locationdropdown = get_option("locationdropdown");

	$hitcounter1 = get_option("hitcounter1");
	$hitcounter2 = get_option("hitcounter2");
	$hitcounter3 = get_option("hitcounter3");

	$watermark_position = get_option("watermark_position");
}

get_header(); ?>

		<div class="contentwrapper">
		<div class="body">
        	<div class="bodybox content-settings-page">
				<h3 class="settingspagetitle"><?php _e('Content Settings','escortwp'); ?></h3>
                <div class="clear30"></div>
				<?php if ($err) { echo "<div class=\"err rad25\">$err</div>"; } ?>
				<?php if ($ok) { echo "<div class=\"ok rad25\">".__('Your settings have been saved','escortwp')."</div>"; } ?>
				<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="form-styling">
					<input type="hidden" name="action" value="hidesections" />

					<div class="form-label">
						<label><?php _e('Show header slider?','escortwp'); ?></label>
					</div>
					<div class="form-input">
						<label for="showheaderslideryes"><input type="radio" name="showheaderslider" value="1" id="showheaderslideryes"<?php if($showheaderslider == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
						<label for="showheadersliderno"><input type="radio" name="showheaderslider" value="2" id="showheadersliderno"<?php if($showheaderslider == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label><br />
					</div> <!-- header slider --> <div class="formseparator"></div>

					<div class="form-label">
						<label><?php _e('Scroll the header slider automatically?','escortwp'); ?></label>
					</div>
					<div class="form-input">
						<label for="autoscrollheaderslideryes"><input type="radio" name="autoscrollheaderslider" value="1" id="autoscrollheaderslideryes"<?php if($autoscrollheaderslider == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
						<label for="autoscrollheadersliderno"><input type="radio" name="autoscrollheaderslider" value="2" id="autoscrollheadersliderno"<?php if($autoscrollheaderslider == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label><br />
					</div> <!-- header slider --> <div class="formseparator"></div>

					<div class="form-label">
						<label><?php printf(esc_html__('Number of %s to show in slider','escortwp'),$taxonomy_profile_name_plural); ?></label>
                    </div>
					<div class="form-input">
						<input type="text" name="headerslideritems" id="headerslideritems" class="input" value="<?php echo $headerslideritems; ?>" />
					</div> <!-- --> <div class="formseparator"></div>

					<div class="form-label">
    					<label><?php _e('Where to show the slider?','escortwp'); ?></label>
				    </div>
					<div class="form-input">
					    <label for="showheadersliderall">
			        		<input type="checkbox" name="showheadersliderall" value="1" id="showheadersliderall"<?php if($showheadersliderall == "1") { echo ' checked'; } ?> /> 
			    	        <?php _e('All site pages','escortwp'); ?>
						</label>
						<small><i>!</i> <?php _e('this overwrites any settings below','escortwp'); ?></small>
						<div class="clear10"></div>
					    <label for="showheadersliderfront">
				        	<input type="checkbox" name="showheadersliderfront" value="1" id="showheadersliderfront"<?php if($showheadersliderfront == "1") { echo ' checked'; } ?> /> 
				            <?php _e('On front page','escortwp'); ?>
							</label><div class="clear5"></div>
					    <label for="showheaderslideresccat">
				        	<input type="checkbox" name="showheaderslideresccat" value="1" id="showheaderslideresccat"<?php if($showheaderslideresccat == "1") { echo ' checked'; } ?> /> 
				            <?php printf(esc_html__('On %s category pages','escortwp'),$taxonomy_profile_name); ?>
						</label><div class="clear5"></div>
					    <label for="showheadersliderescprof">
			    	    	<input type="checkbox" name="showheadersliderescprof" value="1" id="showheadersliderescprof"<?php if($showheadersliderescprof == "1") { echo ' checked'; } ?> /> 
				            <?php printf(esc_html__('On %s profile pages','escortwp'),$taxonomy_profile_name); ?>
						</label><div class="clear5"></div>
					    <label for="showheaderslideragprof">
				        	<input type="checkbox" name="showheaderslideragprof" value="1" id="showheaderslideragprof"<?php if($showheaderslideragprof == "1") { echo ' checked'; } ?> /> 
				            <?php printf(esc_html__('On %s profile pages','escortwp'),$taxonomy_agency_name); ?>
						</label><div class="clear5"></div>
					    <label for="showheaderslidersearch">
				        	<input type="checkbox" name="showheaderslidersearch" value="1" id="showheaderslidersearch"<?php if($showheaderslidersearch == "1") { echo ' checked'; } ?> /> 
				            <?php _e('On search pages','escortwp'); ?>
						</label><div class="clear5"></div>
					    <label for="showheadersliderct">
				        	<input type="checkbox" name="showheadersliderct" value="1" id="showheadersliderct"<?php if($showheadersliderct == "1") { echo ' checked'; } ?> /> 
				            <?php _e('On city tours page','escortwp'); ?>
						</label><div class="clear5"></div>
					    <label for="showheadersliderrev">
				        	<input type="checkbox" name="showheadersliderrev" value="1" id="showheadersliderrev"<?php if($showheadersliderrev == "1") { echo ' checked'; } ?> /> 
				            <?php _e('On the reviews pages','escortwp'); ?>
						</label><div class="clear5"></div>
				    	<label for="showheadersliderads">
			        		<input type="checkbox" name="showheadersliderads" value="1" id="showheadersliderads"<?php if($showheadersliderads == "1") { echo ' checked'; } ?> /> 
				            <?php _e('On the classified ads pages','escortwp'); ?>
						</label>
				    </div> <!-- --> <div class="formseparator"></div>

					<div class="form-label">
						<label><?php _e('Country/City pages only show featured profiles from that location','escortwp'); ?></label>
					</div>
					<div class="form-input">
						<label for="locationsliderpageyes"><input type="radio" name="locationsliderpage" value="1" id="locationsliderpageyes"<?php if($locationsliderpage == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
						<label for="locationsliderpageno"><input type="radio" name="locationsliderpage" value="2" id="locationsliderpageno"<?php if($locationsliderpage == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label><br />
					</div> <!-- header slider --> <div class="formseparator"></div>

					<div class="clear10"></div>
					<fieldset class="fieldset rad5">
						<legend class="rad25"><?php _e('What content to show on the front page?','escortwp'); ?></legend>

						<div class="form-label">
					    	<label><?php printf(esc_html__('Premium %s content box','escortwp'),$taxonomy_profile_name_plural); ?></label>
					    </div>
						<div class="form-input">
							<select name="frontpageshowpremium" id="frontpageshowpremium">
								<option value="1"<?php if($frontpageshowpremium == "1") { echo ' selected'; } ?>>Yes</option>
								<option value=""<?php if(!$frontpageshowpremium) { echo ' selected'; } ?>>No</option>
							</select><div class="clear10"></div>

							<label><?php _e('Number of columns to show','escortwp'); ?></label>
					        <small><i>!</i> <?php printf(esc_html__('each column has 5 %s','escortwp'),$taxonomy_profile_name_plural); ?></small>
					        <input type="text" name="frontpageshowpremiumcols" id="frontpageshowpremiumcols" class="input" value="<?php echo $frontpageshowpremiumcols; ?>" />
						</div> <!-- show premium --> <div class="formseparator"></div>

						<div class="form-label">
					        <label><?php printf(esc_html__('Online %s content box','escortwp'),$taxonomy_profile_name_plural); ?></label>
					 	</div>
						<div class="form-input">
							<select name="frontpageshowonline" id="frontpageshowonline">
								<option value="1"<?php if($frontpageshowonline == "1") { echo ' selected'; } ?>>Yes</option>
								<option value=""<?php if(!$frontpageshowonline) { echo ' selected'; } ?>>No</option>
							</select><div class="clear10"></div>

				    	    <?php _e('Number of columns to show','escortwp'); ?>:
				    	    <small><i>!</i> <?php printf(esc_html__('each column has 5 %s','escortwp'),$taxonomy_profile_name_plural); ?></small>
				    	    <input type="text" name="frontpageshowonlinecols" id="frontpageshowonlinecols" class="input" value="<?php echo $frontpageshowonlinecols; ?>" />
						</div> <!-- show normal --> <div class="formseparator"></div>
							
						<div class="form-label">
					        <label><?php printf(esc_html__('Normal %s content box','escortwp'),$taxonomy_profile_name_plural); ?></label>
					 	</div>
						<div class="form-input">
							<select name="frontpageshownormal" id="frontpageshownormal">
								<option value="1"<?php if($frontpageshownormal == "1") { echo ' selected'; } ?>>Yes</option>
								<option value=""<?php if(!$frontpageshownormal) { echo ' selected'; } ?>>No</option>
							</select><div class="clear10"></div>

				    	    <?php _e('Number of columns to show','escortwp'); ?>:
				    	    <small><i>!</i> <?php printf(esc_html__('each column has 5 %s','escortwp'),$taxonomy_profile_name_plural); ?></small>
				    	    <input type="text" name="frontpageshownormalcols" id="frontpageshownormalcols" class="input" value="<?php echo $frontpageshownormalcols; ?>" />
						</div> <!-- show normal --> <div class="formseparator"></div>
							
						<div class="form-label">
					        <label for="frontpageshowrev"><?php _e('Reviews content box','escortwp'); ?></label>
					    </div>
						<div class="form-input">
							<select name="frontpageshowrev" id="frontpageshowrev">
								<option value="1"<?php if($frontpageshowrev == "1") { echo ' selected'; } ?>>Yes</option>
								<option value=""<?php if(!$frontpageshowrev) { echo ' selected'; } ?>>No</option>
							</select><div class="clear5"></div>

					        <?php _e('Number of reviews to show','escortwp'); ?>:<div class="clear10"></div>
					        <input type="text" name="frontpageshowrevitems" id="frontpageshowrevitems" class="input" value="<?php echo $frontpageshowrevitems; ?>" /><div class="clear10"></div>

					        <?php _e('How many characters to show from each review?','escortwp'); ?><div class="clear"></div>
				    	    <input type="text" name="frontpageshowrevchars" id="frontpageshowrevchars" class="input" value="<?php echo $frontpageshowrevchars; ?>" />
					    </div> <!-- show reviews -->
					</fieldset> <!-- what to show on the front page --> <div class="formseparator"></div>

				    <div class="form-label">
				    	<label><?php _e('Show agreement/disclaimer when someone visits the site for the first time?','escortwp'); ?></label>
				    </div>
					<div class="form-input">
					    <label for="tos18yes"><input type="radio" name="tos18" value="1" id="tos18yes"<?php if($tos18 == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
				    	<label for="tos18no"><input type="radio" name="tos18" value="2" id="tos18no"<?php if($tos18 == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
						<small><i>!</i> <?php _e('If you want to change the text from the disclaimer then edit the file','escortwp'); ?> "<u>footer-tos-18years-agreement-overlay.php</u>" <?php _e('from your theme directory','escortwp'); ?>.</small>
				    </div> <!-- --> <div class="formseparator"></div>

				    <div class="form-label">
				    	<label><?php printf(esc_html__('Show "Quick %s Search" widget in right sidebar?','escortwp'),ucwords($taxonomy_profile_name)); ?></label>
				    </div>
					<div class="form-input">
					    <label for="quickescortsearchyes"><input type="radio" name="quickescortsearch" value="1" id="quickescortsearchyes"<?php if($quickescortsearch == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
				    	<label for="quickescortsearchno"><input type="radio" name="quickescortsearch" value="2" id="quickescortsearchno"<?php if($quickescortsearch == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
				    </div> <!-- --> <div class="formseparator"></div>

				    <div class="form-label">
				    	<label><?php printf(esc_html__('Show unchecked services in %s profile pages?','escortwp'),$taxonomy_profile_name); ?></label>
				    </div>
					<div class="form-input">
					    <label for="hideunchedkedservicesyes"><input type="radio" name="hideunchedkedservices" value="1" id="hideunchedkedservicesyes"<?php if($hideunchedkedservices == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
				    	<label for="hideunchedkedservicesno"><input type="radio" name="hideunchedkedservices" value="2" id="hideunchedkedservicesno"<?php if($hideunchedkedservices == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
				    </div> <!-- --> <div class="formseparator"></div>

				    <div class="form-label">
    					<label><?php _e('What sections do you want to hide from the site?','escortwp'); ?></label>
				    </div>
					<div class="form-input">
					    <label for="hide1">
			        		<input type="checkbox" name="hide1" value="1" id="hide1"<?php if($hide1 == "1") { echo ' checked'; } ?> />
							<?php _e('Reviews','escortwp'); ?>
						</label><div class="clear5"></div>
					    <label for="hide2">
			        		<input type="checkbox" name="hide2" value="1" id="hide2"<?php if($hide2 == "1") { echo ' checked'; } ?> />
							<?php printf(esc_html__('Independent %s registration','escortwp'),$taxonomy_profile_name); ?>
						</label><div class="clear5"></div>
					    <label for="hide3">
			        		<input type="checkbox" name="hide3" value="1" id="hide3"<?php if($hide3 == "1") { echo ' checked'; } ?> />
							<?php printf(esc_html__('%s registration','escortwp'),ucfirst($taxonomy_agency_name)); ?>
						</label><div class="clear5"></div>
					    <label for="hide31">
			        		<input type="checkbox" name="hide31" value="1" id="hide31"<?php if($hide31 == "1") { echo ' checked'; } ?> />
							<?php _e('Register/Login header links','escortwp'); ?>
						</label><div class="clear5"></div>
					    <label for="hide4">
			        		<input type="checkbox" name="hide4" value="1" id="hide4"<?php if($hide4 == "1") { echo ' checked'; } ?> />
							<?php printf(esc_html__('Blacklisted %s','escortwp'),$taxonomy_profile_name_plural); ?>
						</label><div class="clear5"></div>
					    <label for="hide5">
			        		<input type="checkbox" name="hide5" value="1" id="hide5"<?php if($hide5 == "1") { echo ' checked'; } ?> />
							<?php _e('Blacklisted clients','escortwp'); ?>
						</label><div class="clear5"></div>
					    <label for="hide10">
			        		<input type="checkbox" name="hide10" value="1" id="hide10"<?php if($hide10 == "1") { echo ' checked'; } ?> />
							<?php _e('Our Blog','escortwp'); ?>
						</label><div class="clear5"></div>
					    <label for="hide6">
			        		<input type="checkbox" name="hide6" value="1" id="hide6"<?php if($hide6 == "1") { echo ' checked'; } ?> />
							<?php _e('Classified ads','escortwp'); ?>
						</label><div class="clear5"></div>
					    <label for="hide7">
			        		<input type="checkbox" name="hide7" value="1" id="hide7"<?php if($hide7 == "1") { echo ' checked'; } ?> />
							<?php _e('Verified status','escortwp'); ?>
						</label><div class="clear5"></div>
					    <label for="hide8">
			        		<input type="checkbox" name="hide8" value="1" id="hide8"<?php if($hide8 == "1") { echo ' checked'; } ?> />
							<?php printf(esc_html__('%s on tour','escortwp'),ucfirst($taxonomy_profile_name_plural)); ?>
						</label><div class="clear5"></div>
					    <label for="hide9">
			        		<input type="checkbox" name="hide9" value="1" id="hide9"<?php if($hide9 == "1") { echo ' checked'; } ?> />
							<?php _e('Member registration','escortwp'); ?>
						</label>
						<small><i>!</i> <?php _e('Disabling member registration will also disable the reviews','escortwp'); ?></small>
				    </div> <!-- --> <div class="formseparator"></div>

					<div class="form-label">
						<label><?php printf(esc_html__('Allow unregistered visitors to send messages to %1$s and %2$s?','escortwp'), $taxonomy_profile_name_plural, $taxonomy_agency_name_plural); ?></label>
					</div>
					<div class="form-input">
						<label for="unregsendcontactformyes"><input type="radio" name="unregsendcontactform" value="1" id="unregsendcontactformyes"<?php if($unregsendcontactform == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
						<label for="unregsendcontactformno"><input type="radio" name="unregsendcontactform" value="2" id="unregsendcontactformno"<?php if($unregsendcontactform == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label><br />
					</div> <!-- header slider --> <div class="formseparator"></div>

					<script type="text/javascript">
						jQuery(document).ready(function($) {
							check_recaptcha_fields();

							$('.recaptcha input:checkbox').on('change', function() {
								check_recaptcha_fields();
							});

							function check_recaptcha_fields() {
								if ($(".recaptcha input:checkbox:checked").length) {
								    $('.recaptcha-keys').slideDown('fast');
								} else {
								    $('.recaptcha-keys').slideUp('fast');
								}
							}
						});
					</script>
				    <div class="form-label">
    					<label><?php _e('Add a reCAPTCHA to these forms to prevent spam','escortwp'); ?>:</label>
				    </div>
					<div class="form-input recaptcha">
					    <label for="recaptcha1">
			        		<input type="checkbox" name="recaptcha1" value="1" id="recaptcha1"<?php if($recaptcha1 == "1") { echo ' checked'; } ?> />
							<?php _e('Website contact form','escortwp'); ?>
						</label><div class="clear5"></div>
					    <label for="recaptcha2">
			        		<input type="checkbox" name="recaptcha2" value="1" id="recaptcha2"<?php if($recaptcha2 == "1") { echo ' checked'; } ?> />
							<?php printf(esc_html__('Independent %s registration','escortwp'),$taxonomy_profile_name); ?>
						</label><div class="clear5"></div>
					    <label for="recaptcha3">
			        		<input type="checkbox" name="recaptcha3" value="1" id="recaptcha3"<?php if($recaptcha3 == "1") { echo ' checked'; } ?> />
							<?php printf(esc_html__('%s registration','escortwp'),ucfirst($taxonomy_agency_name)); ?>
						</label><div class="clear5"></div>
					    <label for="recaptcha4">
			        		<input type="checkbox" name="recaptcha4" value="1" id="recaptcha4"<?php if($recaptcha4 == "1") { echo ' checked'; } ?> />
							<?php _e('Member registration','escortwp'); ?>
						</label><div class="clear5"></div>
					    <label for="recaptcha5">
			        		<input type="checkbox" name="recaptcha5" value="1" id="recaptcha5"<?php if($recaptcha5 == "1") { echo ' checked'; } ?> />
							<?php _e('','escortwp'); ?>
							<?php printf(esc_html__('%s and %s contact form','escortwp'),ucfirst($taxonomy_profile_name),$taxonomy_agency_name); ?>
						</label><div class="clear5"></div>
					    <label for="recaptcha6">
			        		<input type="checkbox" name="recaptcha6" value="1" id="recaptcha6"<?php if($recaptcha6 == "1") { echo ' checked'; } ?> />
							<?php _e('','escortwp'); ?>
							<?=__('Report form (unregistered users only)', 'escortwp')?>
						</label><div class="clear5"></div>

						<div class="recaptcha-keys hide">
							<div class="clear20"></div>
							<small><i>!</i> <a href="https://www.google.com/recaptcha/" target="_blank"><u><?php _e('Click here to register for reCAPTCHA for free','escortwp'); ?></u></a></small>
	                    	Site key:<br />
							<input type="text" name="recaptcha_sitekey" id="recaptcha_sitekey" class="input longinput" value="<?php echo $recaptcha_sitekey; ?>" /><div class="clear10"></div>
	                    	Secret key:<br />
							<input type="text" name="recaptcha_secretkey" id="recaptcha_secretkey" class="input longinput" value="<?php echo $recaptcha_secretkey; ?>" /><div class="clear10"></div>
						</div>
				    </div> <!-- --> <div class="formseparator"></div>

				    <div class="form-label">
				    	<label><?php _e('Show registration city/state as a dropdown list?','escortwp'); ?></label>
				    </div>
					<div class="form-input">
					    <label for="locationdropdownyes"><input type="radio" name="locationdropdown" value="1" id="locationdropdownyes"<?php if($locationdropdown == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
				    	<label for="locationdropdownno"><input type="radio" name="locationdropdown" value="2" id="locationdropdownno"<?php if($locationdropdown == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
				    	<small><i>!</i> <?=__('If you activate this you will need to add the city list manually','escortwp')?></small>
				    	<small><i>!</i> <?=__('To add cities click the link "Add countries" from the admin menu','escortwp')?></small>
				    </div> <!-- --> <div class="formseparator"></div>

				    <div class="form-label">
				    	<label><?php _e('Show visitor counter in the following pages:','escortwp'); ?></label>
				    </div>
					<div class="form-input">
					    <label for="hitcounter1">
			        		<input type="checkbox" name="hitcounter1" value="1" id="hitcounter1"<?php if($hitcounter1 == "1") { echo ' checked'; } ?> />
							<?php printf(esc_html__('%s profiles','escortwp'),ucfirst($taxonomy_profile_name)); ?>
						</label><div class="clear5"></div>
					    <label for="hitcounter2">
			        		<input type="checkbox" name="hitcounter2" value="1" id="hitcounter2"<?php if($hitcounter2 == "1") { echo ' checked'; } ?> />
							<?php printf(esc_html__('%s profiles','escortwp'),ucfirst($taxonomy_agency_name)); ?>
						</label><div class="clear5"></div>
					    <label for="hitcounter3">
			        		<input type="checkbox" name="hitcounter3" value="1" id="hitcounter3"<?php if($hitcounter3 == "1") { echo ' checked'; } ?> />
							<?php _e('Classified ads page','escortwp'); ?>
						</label><div class="clear5"></div>
						<small><i>!</i> <?=__('This does not save the IP information for the visitor. This means the visitor counter is increased at each page load regardless if the visitor already visited the page.','escortwp')?></small>
						<small><i>!</i> <?=__('The hit counter will not count admin visits or visits from the author of a profile or classified ad.','escortwp')?></small>
				    </div> <!-- --> <div class="formseparator"></div>

					<fieldset class="fieldset rad5">
						<legend class="rad25"><?=__('Watermarking settings', 'escortwp')?></legend>
						<script type="text/javascript">
							jQuery(document).ready(function($) {
							    $('#file_upload').uploadifive({
									'auto'           : true,
									'buttonClass'    : 'pinkbutton rad5 l',
									'buttonText'     : '<?php _e('Upload logo','escortwp'); ?>',
									'fileSizeLimit'  : '<?=get_option("maximguploadsize")?>MB',
							        'fileType'       : 'image/*',
							        'formData'       : { 'folder' : '<?php echo get_option("secret_to_upload_site_logo"); ?>' },
									'multi'          : false,
									'queueID'        : 'upload-queue',
									'queueSizeLimit' : 1,
									'removeCompleted': true,
									'simUploadLimit' : 1,
									'uploadLimit'    : 100,
									'uploadScript'   : '<?php bloginfo('template_url'); ?>/ajax/upload-watermark-logo.php',
									'onAddQueueItem': function(data) {
										$('.showwatermarklogo').slideUp('slow');
									},
									'onQueueComplete': function(data) {
										$.ajax({
											type: "GET",
											url: "<?php bloginfo('template_url'); ?>/ajax/get-watermark-logo-url.php",
											data: "id=" + '1',
											success: function(data){
												$('#status-message').hide().html('<'+'div class="ok rad25"><?=addslashes(__('Your image has been uploaded','escortwp'))?><'+'/div>').delay(500).slideDown("slow").delay(5000).slideUp("slow");
												$('.showwatermarklogo').html('<'+'img src="'+data+'" alt="" id="uploaded_logo_img">').slideDown('slow');
												$('.deletesitelogo').show();
											}
										});
									}
								});

								//delete site logo
								$('.deletesitelogo').on('click', function(){
									$('.showwatermarklogo').slideUp("slow");
									$('.deletesitelogo').fadeOut(500);
									$.ajax({
										type: "GET",
										url: "<?php bloginfo('template_url'); ?>/ajax/delete-watermark-logo.php",
										data: "id=" + '1',
										success: function(data){
											$('#status-message').hide().html('<'+'div class="ok rad25"><?php _e('Your image has been deleted','escortwp'); ?><'+'/div>').slideDown("slow").delay(5000).slideUp("slow");
										}
									});
								});
							});
						</script>
						<div class="form-label col100 text-center">
							<h3 style="color: #fff"><?php _e('Watermark logo','escortwp'); ?></h3>
		                </div>
						<div class="form-input col100 text-center">
							<div class="wrapper center">
								<div class="clear10"></div>
								<div class="upload_photos_button l" style="padding: 0 10px;"><input id="file_upload" name="file_upload" type="hidden" /></div>
								<div class="redbutton rad5 r deletesitelogo<?php if(!get_option("watermarklogourl")) { echo ' hide'; } ?>" style=""><?php _e('Delete Logo','escortwp'); ?></div>
							</div>
						</div> <!-- upload logo --> <div class="clear20"></div>

						<div id="upload-queue"></div><div id="status-message"></div>
						<div class="showwatermarklogo rad5 text-center<?php if(!get_option("watermarklogourl")) { echo ' hide'; } ?>">
							<?php if(get_option("watermarklogourl")) { echo '<img src="'.get_option("watermarklogourl").'" alt="" />'; } ?>
						</div>
						<div class="form-input col100 text-center">
							<small><i>!</i> <?php _e('If a logo is already uploaded and you upload a new one then the old one is automatically deleted','escortwp'); ?></small>
						</div> <div class="formseparator"></div>

					    <div class="form-label">
					    	<label><?php _e('Watermark position on image:','escortwp'); ?></label>
					    </div>
						<div class="form-input">
							<div class="text-center l watermark-position-box">
								<label for="tl" class="text-center l"><input id="tl" type="radio" name="watermark_position" value="tl"<?=$watermark_position == "tl" ? ' checked' : ""?> /><br /><?=__('top<br />left', 'escortwp')?></label>
								<label for="tc" class="text-center l"><input id="tc" type="radio" name="watermark_position" value="tc"<?=$watermark_position == "tc" ? ' checked' : ""?> /><br /><?=__('top<br />center', 'escortwp')?></label>
								<label for="tr" class="text-center l"><input id="tr" type="radio" name="watermark_position" value="tr"<?=$watermark_position == "tr" ? ' checked' : ""?> /><br /><?=__('top<br />right', 'escortwp')?></label><div class="clear"></div>

								<label for="cl" class="text-center l"><input id="cl" type="radio" name="watermark_position" value="cl"<?=$watermark_position == "cl" ? ' checked' : ""?> /><br /><?=__('center<br />left', 'escortwp')?></label>
								<label for="cc" class="text-center l"><input id="cc" type="radio" name="watermark_position" value="cc"<?=$watermark_position == "cc" ? ' checked' : ""?> /><br /><?=__('center', 'escortwp')?></label>
								<label for="cr" class="text-center l"><input id="cr" type="radio" name="watermark_position" value="cr"<?=$watermark_position == "cr" ? ' checked' : ""?> /><br /><?=__('center<br />right', 'escortwp')?></label><div class="clear"></div>

								<label for="bl" class="text-center l"><input id="bl" type="radio" name="watermark_position" value="bl"<?=$watermark_position == "bl" ? ' checked' : ""?> /><br /><?=__('bottom<br />left', 'escortwp')?></label>
								<label for="bc" class="text-center l"><input id="bc" type="radio" name="watermark_position" value="bc"<?=$watermark_position == "bc" ? ' checked' : ""?> /><br /><?=__('bottom<br />center', 'escortwp')?></label>
								<label for="br" class="text-center l"><input id="br" type="radio" name="watermark_position" value="br"<?=$watermark_position == "br" ? ' checked' : ""?> /><br /><?=__('bottom<br />right', 'escortwp')?></label><div class="clear"></div>
							</div>
					    </div> <!-- --> <div class="formseparator"></div>
					</fieldset><div class="formseparator"></div>

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