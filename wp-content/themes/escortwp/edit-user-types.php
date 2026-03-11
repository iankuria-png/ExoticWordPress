<?php
/*
Template Name: Edit user types
*/

$current_user = wp_get_current_user();
if (!current_user_can('level_10')) { wp_redirect(get_bloginfo("url")); exit; }

$err = ""; $ok = "";
if (isset($_POST['action']) && $_POST['action'] == 'usertypes') {
    $install_profile_name = str_replace("-", " ", sanitize_title(char_to_utf8($_POST['install_profile_name'])));
    $install_profile_name_plural = str_replace("-", " ", sanitize_title(char_to_utf8($_POST['install_profile_name_plural'])));
    $install_profile_url = sanitize_title(char_to_utf8($_POST['install_profile_url']));
    foreach ($_POST['install_gender'] as $value) {
    	if(array_key_exists((int)$value, $gender_a)) {
    		$install_gender[] = $value;
    	}
    }
    $install_agency_name = str_replace("-", " ", sanitize_title(char_to_utf8($_POST['install_agency_name'])));
    $install_agency_name_plural = str_replace("-", " ", sanitize_title(char_to_utf8($_POST['install_agency_name_plural'])));
    $install_agency_url = sanitize_title(char_to_utf8($_POST['install_agency_url']));
    $install_location_url = sanitize_title(char_to_utf8($_POST['install_location_url']));
    if(!$install_profile_name || !$install_profile_name_plural || !$install_profile_url || !$install_agency_name || !$install_agency_name_plural || !$install_agency_url || !$install_agency_url || !$install_location_url || count($install_gender) == 0) {
    	$err .= __('All fields are mandatory. Please make sure you have filled in all the fields.','escortwp');
    }

	if(in_array($install_location_url, get_post_types())) {
    	$err .= __('The "Country & city url" can\'t be the same as any of the profile urls or current registered post types','escortwp');
	}

	if(!$err) {
		global $wpdb;
		//change old post user post types to new ones
		$args = array(
			'post_type' => array(get_option("taxonomy_profile_url"), get_option("taxonomy_agency_url")),
			'posts_per_page' => '-1'
		);
		$a = query_posts($args);
		if ( have_posts() ) :
			while ( have_posts() ) : the_post();
				if(get_post_type(get_the_ID()) == get_option("taxonomy_profile_url")) {
					set_post_type( get_the_ID(), $install_profile_url );
					escortwp_child_update_dynamic_user_option("escortid".get_the_author_meta('ID'), $install_profile_url);
				} elseif(get_post_type(get_the_ID()) == get_option("taxonomy_agency_url")) {
					set_post_type( get_the_ID(), $install_agency_url );
					escortwp_child_update_dynamic_user_option("escortid".get_the_author_meta('ID'), $install_agency_url);
				}
			endwhile;
		endif;
		wp_reset_query();

		//change old location names to new ones
		$q1 = $wpdb->prepare("UPDATE $wpdb->term_taxonomy SET `taxonomy` = '%s' WHERE `taxonomy` = '%s'", $install_location_url, get_option("taxonomy_location_url"));
		$q2 = $wpdb->prepare("UPDATE $wpdb->options SET `option_name` = '%s' WHERE `option_name` = '%s'", $install_location_url."_children", get_option("taxonomy_location_url")."_children");
		$wpdb->query($q1);
		$wpdb->query($q2);

		update_option("taxonomy_profile_name", $install_profile_name);
		update_option("taxonomy_profile_name_plural", $install_profile_name_plural);
		update_option("taxonomy_profile_url", $install_profile_url);
		update_option("settings_theme_genders", $install_gender);
		update_option("taxonomy_agency_name", $install_agency_name);
		update_option("taxonomy_agency_name_plural", $install_agency_name_plural);
		update_option("taxonomy_agency_url", $install_agency_url);
		update_option("taxonomy_location_url", $install_location_url);

	    if($_POST['update_pages'] == '1') {
	    	create_theme_pages();
	    }


		flush_rewrite_rules('false');

		$url = get_option('permalink_structure') ? '?message=ok' : '&message=ok';
		wp_redirect(get_permalink(get_the_ID()).$url); die();
	}
} else {
	$install_profile_name = get_option("taxonomy_profile_name");
	$install_profile_name_plural = get_option("taxonomy_profile_name_plural");
	$install_profile_url = get_option("taxonomy_profile_url");
	$install_gender = get_option("settings_theme_genders");
	$install_agency_name = get_option("taxonomy_agency_name");
	$install_agency_name_plural = get_option("taxonomy_agency_name_plural");
	$install_agency_url = get_option("taxonomy_agency_url");
	$install_location_url = get_option("taxonomy_location_url");
}

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox edit-user-types">
			<h3 class="settingspagetitle"><?php _e('Edit user types','escortwp'); ?></h3>
			<?php if ($err) { echo "<div class=\"err rad25\">$err</div>"; } ?>
			<?php if (isset($_GET['message']) && $_GET['message'] == "ok") { echo "<div class=\"ok rad25\">".__('Your settings have been saved','escortwp')."</div>"; } ?>
            <div class="clear30"></div>
			<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="form-styling">
				<input type="hidden" name="action" value="usertypes" />

			    <div class="form-label">
				    <label for="install_profile_name"><?php _e('The name for individual profiles','escortwp'); ?> (<?php _e('singular','escortwp'); ?>):</label>
					<small>ex: escort, model, companion, cam-girl, massage etc</small>
				</div>
				<div class="form-input">
					<input type="text" class="input rad3 center" name="install_profile_name" id="install_profile_name" value="<?php if($install_profile_name || isset($_POST['action'])) { echo $install_profile_name; } else { echo 'escort'; } ?>" style="width: 250px" />
				</div> <div class="formseparator"></div>

			    <div class="clear20"></div>
			    <div class="form-label">
				    <label for="install_profile_name_plural"><?php _e('The plural for the name above','escortwp'); ?>.<br /><?php _e('Used when there is more than one profile','escortwp'); ?>:</label>
					<small>ex: escorts, models, companions, cam-girls, massages etc</small>
				</div> <!-- form-label -->
				<div class="form-input">
					<input type="text" class="input rad3 center" name="install_profile_name_plural" id="install_profile_name_plural" value="<?php if($install_profile_name_plural || isset($_POST['action'])) { echo $install_profile_name_plural; } else { echo 'escorts'; } ?>" style="width: 250px" />
				</div> <!-- form-input --> <div class="clear10"></div>
			    <div>
					<div class="help center"><i class="reddegrade rad5">&nbsp;!&nbsp;</i> <?php _e('these names will be used in menus and titles so we need both the singular and the plural form of the profile name','escortwp'); ?></div>
				</div> <div class="clear40"></div>


			    <div class="form-label col100">
				    <label for="install_profile_url"><?php _e('Profile url','escortwp'); ?>:</label>
				    <small><?php _e('for individual profiles','escortwp'); ?></small>
				</div> <!-- form-label -->
				<div class="form-input col100">
				    <?php bloginfo('url') ?>/ <input type="text" maxlength="32" class="input rad3 center" name="install_profile_url" id="install_profile_url" value="<?php if($install_profile_url || isset($_POST['action'])) { echo $install_profile_url; } else { echo 'escort'; } ?>" style="width: 150px" /> /jennifer/
				</div> <!-- form-input -->


			    <div class="clear50"></div>
			    <div class="form-label">
				    <label><?php _e('What genders do you want to allow for the individual profiles','escortwp'); ?>:</label>
				</div> <!-- form-label -->
				<div class="form-input">
					<?php
					foreach ($gender_a as $key => $gender) {
						if(in_array($key, $install_gender) || !$install_gender && !isset($_POST['action'])) { $checked =  'checked="checked" '; }
						echo '<label for="gender_'.strtolower($gender).'" class="label-checkbox"><input type="checkbox" name="install_gender[]" id="gender_'.strtolower($gender).'" value="'.$key.'" '.$checked.'/> '.$gender.'</label><div class="clear5"></div>';
						unset($checked);
					}
					?>
				</div> <!-- form-input --> <div class="clear50"></div>


				<div class="form-label">
				    <label for="install_agency_name"><?php _e('The name for agency profiles','escortwp'); ?>:</label>
					<small>ex: agency, studio, massage-parlor etc</small>
				</div> <!-- form-label -->
				<div class="form-input">
				    <input type="text" class="input rad3 center" name="install_agency_name" id="install_agency_name" value="<?php if($install_agency_name || isset($_POST['action'])) { echo $install_agency_name; } else { echo 'agency'; } ?>" style="width: 250px" />
				</div> <!-- form-input --> <div class="clear50"></div>


				<div class="form-label">
				    <label for="install_agency_name_plural"><?php _e('The plural for the name above','escortwp'); ?>.<br /><?php _e('Used when there are more than one agency profile','escortwp'); ?>:</label>
					<small>ex: agencies, studios, massage-parlors etc</small>
				</div> <!-- form-label -->
				<div class="form-input">
				    <input type="text" class="input rad3 center" name="install_agency_name_plural" id="install_agency_name_plural" value="<?php if($install_agency_name_plural || isset($_POST['action'])) { echo $install_agency_name_plural; } else { echo 'agencies'; } ?>" style="width: 250px" />
				</div> <!-- form-input --> <div class="clear10"></div>
				<div class="help center"><i class="reddegrade rad5">&nbsp;!&nbsp;</i> <?php _e('these names will be used menus and titles so we need both the singular and the plural form of the agency profile name','escortwp'); ?></div> <div class="clear40"></div>


			    <div class="form-label col100">
				    <label for="install_agency_url"><?php _e('Agency profile url','escortwp'); ?>:</label>
				    <small><?php _e('for agency profiles','escortwp'); ?></small>
				</div> <!-- form-label -->
				<div class="form-input col100">
				    <?php bloginfo('url') ?>/ <input type="text" maxlength="32" class="input rad3 center" name="install_agency_url" id="install_agency_url" value="<?php if($install_agency_url || isset($_POST['action'])) { echo $install_agency_url; } else { echo 'agency'; } ?>" style="width: 150px" /> /some-name/
				</div> <!-- form-input --> <div class="clear30"></div>


			    <div class="form-label col100">
				    <label for="install_location_url"><?php _e('Country &amp; city url','escortwp'); ?>:</label>
				    <small>ex: escorts-from, models-from</small>
				</div> <!-- form-label -->
				<div class="form-input col100">
				    <?php bloginfo('url') ?>/ <input type="text" maxlength="32" class="input rad3 center" name="install_location_url" id="install_location_url" value="<?php if($install_location_url || isset($_POST['action'])) { echo $install_location_url; } else { echo 'escorts-from'; } ?>" style="width: 150px" /> /france/
				</div> <!-- form-input --> <div class="clear50"></div>


			    <div class="form-label">
				    <label><?php _e('Update pages?','escortwp'); ?></label>
				</div> <!-- form-label -->
				<div class="form-input">
				    <label for="update_pages"><input type="checkbox" name="update_pages" id="update_pages" value="1" /> Yes</label>
				</div> <!-- form-input --> <div class="clear"></div>
				<div class="form-input col100">
				    <small><i>!</i> <?php _e('This will update the page name and page url for pages that contain the old profile names with the new profile name','escortwp'); ?></small>
				    <small><i>!</i> <?php _e('The name and url will change only for pages created by the theme. Pages that you added yourself will not be changed','escortwp'); ?></small>
				    <small><i>!</i> <?php _e('If you have changed some page names or urls by yourself then this option will overwrite those changes','escortwp'); ?></small>
			    </div> <!-- form-input --> <div class="clear50"></div>


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
