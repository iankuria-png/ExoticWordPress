<?php
/*
Template Name: Edit site settings
*/

$current_user = wp_get_current_user();
if (!current_user_can('level_10')) { wp_redirect(get_bloginfo("url")); exit; }

	$err = "";
	$ok = "";
if (isset($_POST['action']) && $_POST['action'] == 'sitesettings') {
	$dolce_sitelang = substr(wp_filter_nohtml_kses($_POST['dolce_sitelang']), 0, 100);
	$blogname = wp_strip_all_tags($_POST['blogname']);
	$blogdescription = wp_strip_all_tags($_POST['blogdescription']);

	$manactivesc = (int)$_POST['manactivesc'];
	$manactivag = (int)$_POST['manactivag'];
	$manactivagprof = (int)$_POST['manactivagprof'];
	$manactivagescprof = (int)$_POST['manactivagescprof'];
	$manactivindescprof = (int)$_POST['manactivindescprof'];
	$manactivclassads = (int)$_POST['manactivclassads'];

	$allowadpostingprofiles = (int)$_POST['allowadpostingprofiles'];
	$allowadpostingagencies = (int)$_POST['allowadpostingagencies'];
	$allowadpostingmembers = (int)$_POST['allowadpostingmembers'];

	$newlabelperiod = (int)$_POST['newlabelperiod'];
	$maximgupload = (int)$_POST['maximgupload'];
	$maximguploadsize = (int)$_POST['maximguploadsize'];
	$maximgpxsize = (int)$_POST['maximgpxsize'];

	$allowvideoupload = (int)$_POST['allowvideoupload'];
	$maxvideoupload = (int)$_POST['maxvideoupload'];
	$maxvideouploadsize = (int)$_POST['maxvideouploadsize'];
	$videoresizeheight = (int)$_POST['videoresizeheight'];
	$heightscale = preg_replace("/([^a-z])/", "", $_POST['heightscale']);

	$tos_page_id = (int)$_POST['tos_page_id'];
	$data_protection_page_id = (int)$_POST['data_protection_page_id'];

	if(!$err) {
		update_option("dolce_sitelang", $dolce_sitelang);
		update_option("blogname", $blogname);
		update_option("blogdescription", $blogdescription);

		update_option("manactivesc", $manactivesc);
		update_option("manactivag", $manactivag);
		update_option("manactivagprof", $manactivagprof);
		update_option("manactivagescprof", $manactivagescprof);
		update_option("manactivindescprof", $manactivindescprof);
		update_option("manactivclassads", $manactivclassads);

		update_option("allowadpostingprofiles", $allowadpostingprofiles);
		update_option("allowadpostingagencies", $allowadpostingagencies);
		update_option("allowadpostingmembers", $allowadpostingmembers);

		update_option("newlabelperiod", $newlabelperiod);
		update_option("maximgupload", $maximgupload);
		update_option("maximguploadsize", $maximguploadsize);
		update_option("maximgpxsize", $maximgpxsize);

		update_option("allowvideoupload", $allowvideoupload);
		update_option("maxvideoupload", $maxvideoupload);
		update_option("maxvideouploadsize", $maxvideouploadsize);
		update_option("videoresizeheight", $videoresizeheight);
		update_option("heightscale", $heightscale);

		update_option("tos_page_id", $tos_page_id);
		update_option("data_protection_page_id", $data_protection_page_id);

		$ok = "ok";
	}
} else {
	$dolce_sitelang = get_option("dolce_sitelang");
	$blogname = get_option("blogname");
	$blogdescription = get_option("blogdescription");
	$showheaderslider = get_option("showheaderslider");

	$manactivesc = get_option("manactivesc");
	$manactivag = get_option("manactivag");
	$manactivagprof = get_option("manactivagprof");
	$manactivagescprof = get_option("manactivagescprof");
	$manactivindescprof = get_option("manactivindescprof");
	$manactivclassads = get_option("manactivclassads");

	$allowadpostingprofiles = get_option("allowadpostingprofiles");
	$allowadpostingagencies = get_option("allowadpostingagencies");
	$allowadpostingmembers = get_option("allowadpostingmembers");

	$newlabelperiod = get_option("newlabelperiod");
	$maximgupload = get_option("maximgupload");
	$maximguploadsize = get_option("maximguploadsize");
	$maximgpxsize = get_option("maximgpxsize");

	$allowvideoupload = get_option("allowvideoupload") ? get_option("allowvideoupload") : '2';
	$maxvideoupload = get_option("maxvideoupload") ? get_option("maxvideoupload") : '4';
	$maxvideouploadsize = get_option("maxvideouploadsize") ? get_option("maxvideouploadsize") : '100';
	$videoresizeheight = get_option("videoresizeheight") ? get_option("videoresizeheight") : '400';
	$heightscale = get_option("heightscale") ? get_option("heightscale") : 'metric';

	$tos_page_id = get_option("tos_page_id");
	$data_protection_page_id = get_option("data_protection_page_id");
}

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox site-settings-page">
			<h3 class="settingspagetitle"><?php _e('Site Settings','escortwp'); ?></h3>
			<?php if ($err) { echo "<div class=\"err rad25\">$err</div>"; } ?>
			<?php if ($ok) { echo "<div class=\"ok rad25\">".__('Your settings have been saved','escortwp')."</div>"; } ?>
            <div class="clear30"></div>
			<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="form-styling">
				<input type="hidden" name="action" value="sitesettings" />

				<div class="form-label">
					<label><?php _e('Site name','escortwp'); ?></label>
                </div>
				<div class="form-input">
					<input type="text" name="blogname" id="blogname" class="input longinput" value="<?php echo $blogname; ?>" />
				</div> <!-- blogname --> <div class="formseparator"></div>

				<div class="form-label">
					<label><?php _e('Site description','escortwp'); ?></label>
                </div>
				<div class="form-input">
					<input type="text" name="blogdescription" id="blogdescription" class="input longinput" value="<?php echo $blogdescription; ?>" />
				</div> <!-- --> <div class="formseparator"></div>

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
						'uploadScript'   : '<?php bloginfo('template_url'); ?>/ajax/upload-site-logo-process.php',
						'onAddQueueItem': function(data) {
							$('.showsitelogo').slideUp('slow');
						},
						'onQueueComplete': function(data) {
							$.ajax({
								type: "GET",
								url: "<?php bloginfo('template_url'); ?>/ajax/get-site-logo-url.php",
								data: "id=" + '1',
								success: function(data){
									$('#status-message').hide().html('<'+'div class="ok rad25"><?=addslashes(__('Your image has been uploaded','escortwp'))?><'+'/div>').delay(500).slideDown("slow").delay(5000).slideUp("slow");
									$('.showsitelogo').html('<'+'img src="'+data+'" alt="" id="uploaded_logo_img">').slideDown('slow', function() {
										$('.logo h1 a').animate({
											height: $('#uploaded_logo_img').height(),
											opacity: 0},
											1000, function() {
											$(this).html('<'+'img src="'+data+'" alt="<?php echo get_bloginfo('name'); ?>">').animate({opacity: 1}, 500);
										});
									});

									$('.deletesitelogo').show();
								}
							});
						}
					});

					//delete site logo
					$('.deletesitelogo').on('click', function(){
						$('.showsitelogo').slideUp("slow");
						$('.deletesitelogo').fadeOut(500);
						$.ajax({
							type: "GET",
							url: "<?php bloginfo('template_url'); ?>/ajax/delete-site-logo.php",
							data: "id=" + '1',
							success: function(data){
								$('#status-message').hide().html('<'+'div class="ok rad25"><?php _e('Your image has been deleted','escortwp'); ?><'+'/div>').slideDown("slow").delay(5000).slideUp("slow");
								$('.logo h1 a').slideUp('slow').html('<?php echo get_bloginfo('name'); ?>').slideDown('slow');
							}
						});
					});
				});
				</script>
				<div class="form-label">
					<label><?php _e('Site logo','escortwp'); ?></label>
                </div>
				<div class="form-input">
					<div class="upload_photos_button l"><input id="file_upload" name="file_upload" type="hidden" />&nbsp;&nbsp;&nbsp;</div>
					<div class="redbutton rad5 r deletesitelogo<?php if(!get_option("sitelogo")) { echo ' hide'; } ?>" style=""><?php _e('Delete Logo','escortwp'); ?></div>
				</div> <!-- upload logo --> <div class="formseparator"></div>

				<div id="upload-queue"></div><div id="status-message"></div>
				<div class="showsitelogo rad5 col100 text-center<?php if(!get_option("sitelogo")) { echo ' hide'; } ?>">
					<?php if(get_option("sitelogo")) { echo '<img src="'.get_option("sitelogo").'" alt="" />'; } ?>
				</div>
				<div class="form-input col100">
					<small><i>!</i> <?php _e('If a logo is already uploaded and you upload a new one then the old one is automatically deleted','escortwp'); ?></small>
				</div> <!-- --> <div class="formseparator"></div>
				<div class="clear30"></div>

				<div class="form-label">
			    	<label><?php printf(esc_html__('Manually activate reviews for %s?','escortwp'),$taxonomy_profile_name_plural); ?></label>
			    </div>
				<div class="form-input">
				    <label for="manactivescyes"><input type="radio" name="manactivesc" value="1" id="manactivescyes"<?php if($manactivesc == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
		    		<label for="manactivescno"><input type="radio" name="manactivesc" value="2" id="manactivescno"<?php if($manactivesc == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
					<small><i>!</i> <?php _e('If you choose "yes" you will be notified by email each time someone<br />adds a new review. The email will have a link to the review','escortwp'); ?>.</small>
			    </div> <!-- manually activate escorts --> <div class="formseparator"></div>

			    <div class="form-label">
			    	<label><?php printf(esc_html__('Manually activate reviews for %s?','escortwp'),$taxonomy_agency_name_plural); ?></label>
			    </div>
				<div class="form-input">
				    <label for="manactivagyes"><input type="radio" name="manactivag" value="1" id="manactivagyes"<?php if($manactivag == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    	<label for="manactivagno"><input type="radio" name="manactivag" value="2" id="manactivagno"<?php if($manactivag == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label><br />
					<small><i>!</i> <?php _e('If you choose "yes" you will be notified by email each time someone<br />adds a new review. The email will have a link to the review','escortwp'); ?>.</small>
			    </div> <!-- manually activate agencies --> <div class="formseparator"></div>

			    <div class="form-label">
			    	<label><?php printf(esc_html__('Manually activate new %s profiles?','escortwp'),$taxonomy_agency_name); ?></label>
			    </div>
				<div class="form-input">
				    <label for="manactivagprofyes"><input type="radio" name="manactivagprof" value="1" id="manactivagprofyes"<?php if($manactivagprof == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    	<label for="manactivagprofno"><input type="radio" name="manactivagprof" value="2" id="manactivagprofno"<?php if($manactivagprof == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
					<small><i>!</i> <?php _e('If you choose "yes" you will have to activate each profile manually','escortwp'); ?>.</small>
			    </div> <!-- --> <div class="formseparator"></div>

			    <div class="form-label">
			    	<label><?php printf(esc_html__('Manually activate new %1$s added by %2$s?','escortwp'),$taxonomy_profile_name_plural,$taxonomy_agency_name_plural); ?></label>
			    </div>
				<div class="form-input">
				    <label for="manactivagescprofyes"><input type="radio" name="manactivagescprof" value="1" id="manactivagescprofyes"<?php if($manactivagescprof == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    	<label for="manactivagescprofno"><input type="radio" name="manactivagescprof" value="2" id="manactivagescprofno"<?php if($manactivagescprof == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
					<small><i>!</i> <?php _e('If you choose "yes" you will have to activate each profile manually','escortwp'); ?>.</small>
			    </div> <!-- --> <div class="formseparator"></div>

			    <div class="form-label">
			    	<labe><?php printf(esc_html__('Manually activate new independent %s?','escortwp'),$taxonomy_profile_name_plural); ?></label>
			    </div>
				<div class="form-input">
				    <label for="manactivindescprofyes"><input type="radio" name="manactivindescprof" value="1" id="manactivindescprofyes"<?php if($manactivindescprof == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    	<label for="manactivindescprofno"><input type="radio" name="manactivindescprof" value="2" id="manactivindescprofno"<?php if($manactivindescprof == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
					<small><i>!</i> <?php _e('If you choose "yes" you will have to activate each profile manually','escortwp'); ?>.</small>
			    </div> <!-- manually activate independent escorts --> <div class="formseparator"></div>

			    <div class="form-label">
			    	<labe><?php _e('Manually activate new classified ads?','escortwp'); ?></label>
			    </div>
				<div class="form-input">
				    <label for="manactivclassadsyes"><input type="radio" name="manactivclassads" value="1" id="manactivclassadsyes"<?php if($manactivclassads == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    	<label for="manactivclassadsno"><input type="radio" name="manactivclassads" value="2" id="manactivclassadsno"<?php if($manactivclassads == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
					<small><i>!</i> <?php _e('If you choose "yes" you will have to activate each classified ad manually','escortwp'); ?>.</small>
			    </div> <!-- manually activate independent escorts --> <div class="formseparator"></div>

				<div class="form-label">
					<label><?php _e('What user types can post classified ads?','escortwp'); ?></label>
			    </div>
				<div class="form-input">
				    <label for="allowadpostingprofiles">
			        	<input type="checkbox" name="allowadpostingprofiles" value="1" id="allowadpostingprofiles"<?php if($allowadpostingprofiles == "1") { echo ' checked'; } ?> /> 
			            <?=ucfirst($taxonomy_profile_name_plural)?>
						</label><div class="clear5"></div>
				    <label for="allowadpostingagencies">
			        	<input type="checkbox" name="allowadpostingagencies" value="1" id="allowadpostingagencies"<?php if($allowadpostingagencies == "1") { echo ' checked'; } ?> /> 
			            <?=ucfirst($taxonomy_agency_name_plural)?>
					</label><div class="clear5"></div>
				    <label for="allowadpostingmembers">
		    	    	<input type="checkbox" name="allowadpostingmembers" value="1" id="allowadpostingmembers"<?php if($allowadpostingmembers == "1") { echo ' checked'; } ?> /> 
			            <?php _e('Normal users','escortwp'); ?>
					</label><div class="clear5"></div>
			    </div> <!-- --> <div class="formseparator"></div>

				<div class="form-label">
					<label for="newlabelperiod"><?php printf(esc_html__('New %s label period','escortwp'),$taxonomy_profile_name); ?></label>
                </div>
				<div class="form-input">
					<input type="text" name="newlabelperiod" id="newlabelperiod" class="input text-center" size="3" maxlength="3" value="<?php echo $newlabelperiod; ?>" /> <?=__('days','escortwp')?>
					<small><i>!</i> <?=sprintf(esc_html__('new %s profiles will have the NEW label if they have registered less then X days ago.','escortwp'),$taxonomy_profile_name)?></small>
				</div> <!-- --> <div class="formseparator"></div>

				<div class="clear30"></div>

				<fieldset class="fieldset rad5">
					<legend class="rad25"><?php _e('Images settings','escortwp'); ?></legend>
					<div class="form-label">
						<label for="maximgupload"><?php printf(esc_html__('Maximum number of images a %s can upload','escortwp'),$taxonomy_profile_name); ?></label>
	                </div>
					<div class="form-input">
						<input type="text" name="maximgupload" id="maximgupload" class="input" size="3" maxlength="3" value="<?php echo $maximgupload; ?>" /> <?=__('images','escortwp')?>
					</div><div class="formseparator"></div>

					<div class="form-label">
						<label for="maximguploadsize"><?php _e('Maximum size per image','escortwp'); ?></label>
	                </div>
					<div class="form-input">
						<input type="text" name="maximguploadsize" id="maximguploadsize" class="input" size="3" maxlength="2" value="<?php echo $maximguploadsize; ?>" /> MB
					</div><div class="formseparator"></div>

					<div class="form-label">
						<label for="maximgpxsize"><?php _e('Resize big images to a maximum of','escortwp'); ?></label>
	                </div>
					<div class="form-input">
    					<select name="maximgpxsize">
							<option value=""><?=__('Keep original size', 'escortwp')?></option>
    						<?php
    						for ($i = 1000; $i <= 5000; $i++) {
    							$selected = $maximgpxsize == $i ? ' selected="selected"' : "";
    							echo '<option value="'.$i.'"'.$selected.'>'.$i.'px</option>';
    							$i = $i + 499;
    						}
    						?>
						</select>
					</div>	
				</fieldset>

				<div class="clear30"></div>

				<fieldset class="fieldset rad5">
					<legend class="rad25"><?php _e('Video settings','escortwp'); ?></legend>
				    <div class="form-label">
				    	<labe><?php _e('Allow video upload?','escortwp'); ?></label>
				    </div>
					<div class="form-input">
					    <label for="allowvideouploadyes"><input type="radio" name="allowvideoupload" value="1" id="allowvideouploadyes"<?php if($allowvideoupload == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
				    	<label for="allowvideouploadno"><input type="radio" name="allowvideoupload" value="2" id="allowvideouploadno"<?php if($allowvideoupload == "2" || !$allowvideoupload) { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
				    	<div class="clear10"></div>
					    <?php
					    	echo "<span class='err rad25'>".__('You need to install "ffmpeg" and enable "shell_exec" php function in order for the video upload to work','escortwp')."</span><div class='clear15'></div>";
					    ?>
				    </div> <!-- allow video --> <div class="formseparator"></div>

					<div class="form-label">
						<label for="maxvideoupload"><?php _e('Maximum number of videos allowed','escortwp'); ?></label>
	                </div>
					<div class="form-input">
						<input type="text" name="maxvideoupload" id="maxvideoupload" class="input" size="3" maxlength="2" value="<?php echo $maxvideoupload; ?>" /> <?=__('videos','escortwp')?>
					</div> <!-- --> <div class="formseparator"></div>

					<div class="form-label">
						<label for="maxvideouploadsize"><?php _e('Maximum video size','escortwp'); ?></label>
	                </div>
					<div class="form-input">
						<input type="text" name="maxvideouploadsize" id="maxvideouploadsize" class="input" size="3" maxlength="3" value="<?php echo $maxvideouploadsize; ?>" /> MB
						<div class="clear10"></div>
					    <?php
					    $upload_max_filesize = ini_get('upload_max_filesize');
					    if((int)$upload_max_filesize > $maxvideouploadsize) {
					    	echo "<span class='ok rad25'>upload_max_filesize is ".$upload_max_filesize."</span><div class='clear15'></div>";
					    } else {
					    	echo "<span class='err rad25'>upload_max_filesize is ".$upload_max_filesize."</span><div class='clear15'></div>";
					    }

					    $post_max_size = ini_get('post_max_size');
					    if((int)$post_max_size > $maxvideouploadsize) {
					    	echo "<span class='ok rad25'>post_max_size is ".$post_max_size."</span><div class='clear15'></div>";
					    } else {
					    	echo "<span class='err rad25'>post_max_size is ".$post_max_size."</span><div class='clear15'></div>";
					    }
					    ?>
					</div> <!-- --> <div class="formseparator"></div>

				    <div class="form-label">
				    	<label><?php _e('Resize uploaded videos at','escortwp'); ?></label>
				    </div>
					<div class="form-input">
    					<select name="videoresizeheight">
							<option value="300"<? if($videoresizeheight == "300") { echo ' selected="selected"'; } ?>>300px</option>
							<option value="400"<? if($videoresizeheight == "400") { echo ' selected="selected"'; } ?>>400px</option>
							<option value="500"<? if($videoresizeheight == "500") { echo ' selected="selected"'; } ?>>500px</option>
							<option value="600"<? if($videoresizeheight == "600") { echo ' selected="selected"'; } ?>>600px</option>
						</select>
						<?=__('in height','escortwp')?>
				    </div> <!-- --> <div class="formseparator"></div>
				</fieldset> <div class="formseparator"></div>

			    <div class="form-label">
			    	<labe><?php _e('Height & weight scales','escortwp'); ?></label>
			    </div>
				<div class="form-input">
				    <label for="heightscale_metric"><input type="radio" name="heightscale" value="metric" id="heightscale_metric"<?php if($heightscale == "metric") { echo ' checked'; } ?> /> <?php _e('Metric (cm, kg)','escortwp'); ?></label>
				    <div class="clear10"></div>
			    	<label for="heightscale_imperial"><input type="radio" name="heightscale" value="imperial" id="heightscale_imperial"<?php if($heightscale == "imperial") { echo ' checked'; } ?> /> <?php _e('Imperial (ft, lb)','escortwp'); ?></label>
			    </div> <!-- manually activate independent escorts --> <div class="formseparator"></div>


			    <?php
			    $wp_dropdown_pages_args = array(
			    				'show_option_none' => __('Select page', 'escortwp'),
				    			'exclude' => array(
						    					get_option('main_reg_page_id'), get_option('escort_reg_page_id'), get_option('escort_tours_page_id'), get_option('escort_edit_personal_info_page_id'), get_option('change_password_page_id'), get_option('escort_verified_status_page_id'), get_option('escort_blacklist_clients_page_id'), get_option('agency_reg_page_id'), get_option('all_profiles_page_id'), get_option('all_female_profiles_page_id'), get_option('all_male_profiles_page_id'), get_option('all_couple_profiles_page_id'), get_option('all_gay_profiles_page_id'), get_option('all_trans_profiles_page_id'), get_option('all_independent_profiles_page_id'), get_option('all_premium_profiles_page_id'), get_option('all_verified_profiles_page_id'), get_option('all_new_profiles_page_id'), get_option('all_online_profiles_page_id'), get_option('agency_edit_personal_info_page_id'), get_option('agency_upload_logo_page_id'), get_option('agency_manage_escorts_page_id'), get_option('member_register_page_id'), get_option('member_edit_personal_info_page_id'), get_option('member_favorite_escorts_page_id'), get_option('member_reviews_page_id'), get_option('city_tours_page_id'), get_option('nav_reviews_page_id'), get_option('nav_reviews_agencies_page_id'), get_option('list_agencies_page_id'), get_option('contact_page_id'), get_option('search_page_id'), get_option('blacklisted_escorts_page_id'), get_option('manage_ads_page_id'), get_option('see_all_ads_page_id'), get_option('see_offering_ads_page_id'), get_option('see_looking_ads_page_id'), get_option('edit_payment_settings_page_id'), get_option('edit_user_types'), get_option('edit_registration_form_escort'), get_option('nav_blacklisted_escorts_page_id'), get_option('email_settings_page_id'), get_option('site_settings_page_id'), get_option('content_settings_page_id'), get_option('blog_page_id'), get_option('generate_demo_data_page'), get_option('woocommerce_shop_page_id'), get_option('woocommerce_cart_page_id'), get_option('woocommerce_checkout_page_id'), get_option('woocommerce_pay_page_id'), get_option('woocommerce_thanks_page_id'), get_option('woocommerce_myaccount_page_id'), get_option('woocommerce_edit_address_page_id'), get_option('woocommerce_view_order_page_id')
						    				)
				    			);
			    ?>
			    <div class="form-label">
			    	<label><?php _e('Terms & services page','escortwp'); ?></label>
				    <small><i>!</i> <?=__('This page will be used in the registration page, for all users', 'escortwp')?></small>
			    </div>
				<div class="form-input">
			    	<?php
			    	$wp_dropdown_pages_args['name'] = 'tos_page_id';
			    	$wp_dropdown_pages_args['selected'] = $tos_page_id;
				    wp_dropdown_pages($wp_dropdown_pages_args);
				    ?>
			    </div><div class="formseparator"></div>

			    <div class="form-label">
			    	<label><?php _e('Data protection page','escortwp'); ?></label>
				    <small><i>!</i> <?=__('This page will be used in the registration page, for all users', 'escortwp')?></small>
			    </div>
				<div class="form-input">
			    	<?php
			    	$wp_dropdown_pages_args['selected'] = $data_protection_page_id;
			    	$wp_dropdown_pages_args['name'] = 'data_protection_page_id';
				    wp_dropdown_pages($wp_dropdown_pages_args);
				    ?>
			    </div><div class="formseparator"></div>


				<div class="clear30"></div>
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