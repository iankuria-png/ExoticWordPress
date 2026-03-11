<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

$current_user = wp_get_current_user();
$install_gender = array();
if (isset($_POST['action']) && current_user_can('level_10')) {
	$install_profile_name = str_replace("-", " ", sanitize_title(char_to_utf8($_POST['install_profile_name'])));
	if(!$install_profile_name) {
		$err .= "There is no name for the individual profiles<br />";
	}

	$install_profile_name_plural = str_replace("-", " ", sanitize_title(char_to_utf8($_POST['install_profile_name_plural'])));
	if(!$install_profile_name_plural) {
		$err .= "There is no name for the individual profiles plural<br />";
	}

	$install_profile_url = sanitize_title(char_to_utf8($_POST['install_profile_url']));
	if(!$install_profile_url) {
		$err .= "There is no individual profile url<br />";
	}

	$install_gender = $_POST['install_gender'];
	if(!$install_gender || !is_array($install_gender)) {
		$err .= "Choose your gender list<br />";
	} else {
		global $gender_a;
		foreach ($install_gender as $key=>$gender) {
			if(!array_key_exists($gender, $gender_a)) {
				unset($install_gender[$key]);
			}
		}
	}

	$install_agency_name = str_replace("-", " ", sanitize_title(char_to_utf8($_POST['install_agency_name'])));
	if(!$install_agency_name) {
		$err .= "There is no name for the agency profiles<br />";
	}

	$install_agency_name_plural = str_replace("-", " ", sanitize_title(char_to_utf8($_POST['install_agency_name_plural'])));
	if(!$install_agency_name_plural) {
		$err .= "There is no name for agency profiles plural<br />";
	}

	$install_agency_url = sanitize_title(char_to_utf8($_POST['install_agency_url']));
	if(!$install_agency_url) {
		$err .= "There is no agency url<br />";
	}

	$install_location = (int)$_POST['install_location'];
	if(!$install_location) {
		$err .= "Choose what countries you want to use in the theme<br />";
	}

	if($install_location == "2") {
		$install_countries = trim($_POST['install_countries']);
		if(!$install_countries) {
			$err .= "You choose to write your own countries manually but the countries field is empty<br />";
		}
	}

	$install_location_url = sanitize_title(char_to_utf8($_POST['install_location_url']));
	if(!$install_location_url) {
		$err .= "There is no url for countries and cities<br />";
	}

	if(!$err) {
		update_option("taxonomy_profile_name", strtolower($install_profile_name));
		update_option("taxonomy_profile_name_plural", strtolower($install_profile_name_plural));
		update_option("taxonomy_profile_url", strtolower($install_profile_url));

		update_option("taxonomy_agency_name", strtolower($install_agency_name));
		update_option("taxonomy_agency_name_plural", strtolower($install_agency_name_plural));
		update_option("taxonomy_agency_url", strtolower($install_agency_url));

		update_option("settings_theme_genders", $install_gender);
		update_option("taxonomy_location_url", strtolower($install_location_url));

		//create pages
		if (get_option('are_all_pages_created') != 'yes') {
	    	$pages_created_count = create_theme_pages();
			$ok .= '<p><span class="icon icon-ok"></span> '.$pages_created_count.' pages have been created</p>';
		} else {
			$ok .= '<p><span class="icon icon-ok"></span> 0 pages have been created. Pages already exist.</p>';
		}

		//create initial country list
		if($install_location == "1") { //add the full country list
			import_country_list($install_location_url);
			$ok .= '<p><span class="icon icon-ok"></span> 232 countries have been created</p>';
		} elseif ($install_location == "2") { //add the custom country list that the admin wrote
			create_country_list($install_countries, $install_location_url);
			$ok .= '<p><span class="icon icon-ok"></span> '.count(explode("\n", trim($install_countries))).' countries have been created</p>';
		}
		update_option('are_all_countries_imported', 'yes');

	    if (get_option('defaults_have_been_set') != 'yes') {
			set_default_settings();
			$ok .= '<p><span class="icon icon-ok"></span> Default settings have been set</p>';
		} else {
			$ok .= '<p><span class="icon icon-ok"></span> Default settings are already set</p>';
		}

		update_option('is_theme_installed', 'yes');

		flush_rewrite_rules();
	} // if !$err
} // if isset($_POST['action'])

if (current_user_can('level_10')) { ?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#location').change(function() {
			location();
		});
		location();
		function location(){
			var location = $('#location').val();
			if (location == "1") {
				$('.location-option').hide();
				$('.location1').show();
			} else if (location == "2") {
				$('.location-option').hide();
				$('.location2').show();
			} else if (location == "") {
				$('.location-option').hide();
			}
		}

		<?php if(isset($_POST['action'])) { ?>
			var install_profile_url_has_been_edited = 'yes';
			var install_agency_name_plural_has_been_edited = 'yes';
		<?php } else { ?>
			var install_profile_url_has_been_edited = null;
			var install_agency_name_plural_has_been_edited = null;
		<?php } ?>

		$('#install_profile_name').keyup(function(){
			var install_profile_name = $.trim($(this).val());
			$('#install_profile_name_plural').val(install_profile_name+"s");
			if(install_profile_url_has_been_edited != 'yes') {
				install_profile_name = install_profile_name.replace(/ /g,"-");
				$('#install_profile_url').val(install_profile_name);
				$('#install_location_url').val(install_profile_name+'s-from')
			}
		});

		$('#install_agency_name').keyup(function(){
			var install_agency_name = $(this).val();
			install_agency_name = $.trim(install_agency_name).replace(/ /g,"-");
			$('#install_agency_url').val(install_agency_name);
			if(install_agency_name_plural_has_been_edited != 'yes') {
				$('#install_agency_name_plural').val('');
			}
		});

		$('#install_profile_url').keyup(function(){ install_profile_url_has_been_edited = 'yes'; });
		$('#install_agency_name_plural').keyup(function(){ install_agency_name_plural_has_been_edited = 'yes'; });

		$("#install_form").submit(function() {
			$(".submit-button").hide();
			loader('.install_submit_button');
			$('.install_submit_button').append('Please wait...');	
		});
	});
	</script>
<?php } // if admin ?>
	<div class="logo"><img src="<?=get_bloginfo('template_url')?>/i/logo.png" /></div>
	<div class="clear10"></div>
	<div class="install rad5">
		<?php if (!current_user_can('level_10')) { ?>
			<div class="text-center"><h3>Theme Configuration Wizard</h3></div>
		    <p>Only admins are allowed to install the theme.</p>
		    <p>If you are the admin of this website please <a href="<?php echo wp_login_url(get_bloginfo('url')); ?>">click here to login</a> first.</p>
		<?php } else { ?>
		    <form action="" method="post" name="install_form" id="install_form">
			    <?php if($ok) { ?>
			    	<div class="ok rad25">Your theme has been configured successfully</div>
			    	<?php echo $ok; ?>
					<div class="clear20"></div>
					<a href="<?php bloginfo('url'); ?>/" class="pinkbutton rad25"><b>Go to your website</b></a>
					<div class="clear"></div>
			    <?php } else { ?>
		    	<div class="text-center"><h3>Theme Configuration Wizard</h3></div>
		    	<div class="clear20"></div>
			    <p>This is the first time you are viewing the theme.</p>
			    <p>We recommend that you install this theme on a vanilla installation of WordPress (meaning you haven't used this blog before for anything else).</p>
			    <p>In order for the theme to work properly we first need you to choose some important settings for your future website.</p>
			    <p>Go through all the options bellow and configure your theme.</p>
			    <p>If you are not sure about a certain option then just use the pre-filled information.</p>

				<?php if($err) { echo '<div class="err rad25">'.$err.'</div>'; } ?>

			    <input type="hidden" name="action" value="install_theme" />

			    <div class="clear30"></div>
			    <div class="col50 l">
				    <div class="label">The name for individual profiles (singular):</div>
					<div class="help">ex: escort, model, companion, cam-girl, massage etc</div>
				</div>
				<div class="col50 l">
					<input type="text" class="input rad3 center" name="install_profile_name" id="install_profile_name" value="<?php if($install_profile_name || isset($_POST['action'])) { echo $install_profile_name; } else { echo 'escort'; } ?>" style="width: 250px" />
				</div>

			    <div class="clear30"></div>
			    <div class="col50 l">
				    <div class="label">The plural for the name above.<br />Used when there is more than one profile:</div>
					<div class="help">ex: escorts, models, companions, cam-girls, massages etc</div>
				</div>
				<div class="col50 l">
					<input type="text" class="input rad3 center" name="install_profile_name_plural" id="install_profile_name_plural" value="<?php if($install_profile_name_plural || isset($_POST['action'])) { echo $install_profile_name_plural; } else { echo 'escorts'; } ?>" style="width: 250px" />
				</div>
				<div class="clear10"></div>
			    <div>
					<div class="help center"><i class="reddegrade rad5">&nbsp;!&nbsp;</i> these names will be used in menus and titles so we need both the singular and the plural form of the profile name</div>
				</div>

			    <div class="clear40"></div>
			    <div class="col30 l">
				    <div class="label">Profile url:</div>
				    <div class="help">for individual profiles</div>
				</div>
				<div class="col70 l">
				    <?php bloginfo('url') ?>/ <input type="text" maxlength="32" class="input rad3 center" name="install_profile_url" id="install_profile_url" value="<?php if($install_profile_url || isset($_POST['action'])) { echo $install_profile_url; } else { echo 'escort'; } ?>" style="width: 150px" /> /jennifer/
				</div>


			    <div class="clear50"></div>
			    <div class="col50 l">
				    <div class="label">What genders do you want to allow for the individual profiles:</div>
				    <div class="help"></div>
				</div>
				<div class="col50 l">
				    <label for="gender_female" class="label-checkbox"><input type="checkbox" name="install_gender[]" id="gender_female" value="1" <?php if(in_array('1', $install_gender) || !$install_gender && !isset($_POST['action'])) { echo 'checked="checked" '; } ?> /> Female</label><div class="clear5"></div>
				    <label for="gender_male" class="label-checkbox"><input type="checkbox" name="install_gender[]" id="gender_male" value="2" <?php if(in_array('2', $install_gender) || !$install_gender && !isset($_POST['action'])) { echo 'checked="checked" '; } ?>/> Male</label><div class="clear5"></div>
				    <label for="gender_couple" class="label-checkbox"><input type="checkbox" name="install_gender[]" id="gender_couple" value="3" <?php if(in_array('3', $install_gender) || !$install_gender && !isset($_POST['action'])) { echo 'checked="checked" '; } ?>/> Couple</label><div class="clear5"></div>
				    <label for="gender_gay" class="label-checkbox"><input type="checkbox" name="install_gender[]" id="gender_gay" value="4" <?php if(in_array('4', $install_gender) || !$install_gender && !isset($_POST['action'])) { echo 'checked="checked" '; } ?>/> Gay</label><div class="clear5"></div>
				    <label for="gender_trans" class="label-checkbox"><input type="checkbox" name="install_gender[]" id="gender_trans" value="5" <?php if(in_array('5', $install_gender) || !$install_gender && !isset($_POST['action'])) { echo 'checked="checked" '; } ?>/> Transsexual</label>
				</div>

				<div class="clear50"></div>
				<div class="col50 l">
				    <div class="label">The name for agency profiles:</div>
					<div class="help">ex: agency, studio, massage-parlor etc</div>
				</div>
				<div class="col50 l">
				    <input type="text" class="input rad3 center" name="install_agency_name" id="install_agency_name" value="<?php if($install_agency_name || isset($_POST['action'])) { echo $install_agency_name; } else { echo 'agency'; } ?>" style="width: 250px" />
				</div>

				<div class="clear50"></div>
				<div class="col50 l">
				    <div class="label">The plural for the name above.<br />Used when there are more than one agency profile:</div>
					<div class="help">ex: agencies, studios, massage-parlors etc</div>
				</div>
				<div class="col50 l">
				    <input type="text" class="input rad3 center" name="install_agency_name_plural" id="install_agency_name_plural" value="<?php if($install_agency_name_plural || isset($_POST['action'])) { echo $install_agency_name_plural; } else { echo 'agencies'; } ?>" style="width: 250px" />
				</div>
				<div class="clear10"></div>
			    <div>
					<div class="help center"><i class="reddegrade rad5">&nbsp;!&nbsp;</i> these names will be used menus and titles so we need both the singular and the plural form of the agency profile name</div>
				</div>

			    <div class="clear40"></div>
			    <div class="col30 l">
				    <div class="label">Agency profile url:</div>
				    <div class="help">for agency profiles</div>
				</div>
				<div class="col70 l">
				    <?php bloginfo('url') ?>/ <input type="text" maxlength="32" class="input rad3 center" name="install_agency_url" id="install_agency_url" value="<?php if($install_agency_url || isset($_POST['action'])) { echo $install_agency_url; } else { echo 'agency'; } ?>" style="width: 150px" /> /some-name/
				</div>


				<div class="clear50"></div>
				<div class="col50 l">
					<div class="label">What countries do you want to use:</div>
					<div class="help">
						We can import a full list of all the countries in the world for you.<br />
						If you would prefer to add your own countries (only from Europe for example) then please write them manually.
					</div>
				</div>
				<div class="col50 l">
					<select class="select rad3 col90" name="install_location" id="location">
						<option value="">Choose option</option>
						<option value="1"<?php if($install_location == "1") { echo ' selected="selected"'; } ?>>All countries</option>
						<option value="2"<?php if($install_location == "2") { echo ' selected="selected"'; } ?>>Write countries manually</option>
					</select>
				</div>
				<div class="help col50 r location1 location-option hide">
					<div class="clear5"></div>
					We'll import a list of 232 countries for you.<br />
					You can edit the list from your WordPress dashboard after you finish the configuration.
				</div>
				<div class="location2 location-option hide">
					<div class="clear20"></div>
					<div class="col40 l">
						<div class="label">Your country list:</div>
						<div class="help">Write one country per line</div>
					</div>
					<div class="col60 l">
						<textarea name="install_countries" class="textarea rad3 col90"><?php if($install_countries || isset($_POST['action'])) { echo $install_countries; } else { echo "Germany\nFrance\nSpain"; } ?></textarea>
					</div>
				</div>

			    <div class="clear30"></div>
			    <div class="col30 l">
				    <div class="label">Country &amp; city url:</div>
				    <div class="help">ex: escorts-from, models-from</div>
				</div>
				<div class="col70 l">
				    <?php bloginfo('url') ?>/ <input type="text" maxlength="32" class="input rad3 center" name="install_location_url" id="install_location_url" value="<?php if($install_location_url || isset($_POST['action'])) { echo $install_location_url; } else { echo 'escorts-from'; } ?>" style="width: 150px" /> /france/
				</div>

				<div class="clear50"></div>
				<div class="install_submit_button"><input type="submit" name="submit" value="<?=__('Save configuration', 'escortwp')?>" class="greenbutton rad25 submit-button" /></div>
				<div class="clear"></div>
			</form>
		<?php
			} // if !$ok
		} // if admin
		?>
		<div class="clear20"></div>
	</div> <!-- install -->
	<div class="clear40"></div>
</body>
</html>