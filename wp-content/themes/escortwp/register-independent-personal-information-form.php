<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

global $gender_a, $settings_theme_genders, $taxonomy_profile_name, $taxonomy_location_url;

if (isset($err) && $err && $_POST['action'] == 'register') { echo "<div class=\"err rad25\">$err</div>"; }

if (isset($escort_post_id)) {
	$form_url = get_permalink(get_option('escort_edit_personal_info_page_id'));
} else {
	$form_url = get_permalink(get_option('escort_reg_page_id'));
}
if (isset($agencyid)) {
	$form_url = get_permalink(get_option('agency_manage_escorts_page_id'));
	if (isset($single_page) && $single_page == "yes") {
		$form_url = get_permalink($escort_post_id);
	}
	if (isset($admin_adding_escort) && $admin_adding_escort == "yes") {
		$form_url = get_permalink($agency_profile_id);
	}
}
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	//check if the current user is already taken
	$('#register_form #user').keyup(function(){
		var user = $('#user').val();
		var userlength = document.getElementById("user").value.length;
		if(userlength >= 4 && userlength <= 30) {
			$('.checkuser').empty();
			$.ajax({
				type: "GET",
				url: "<?php bloginfo('template_url'); ?>/ajax/check-username.php",
				data: "user=" + user,
				success: function(data){
					$('.checkuser').html(data);
				}
			});
		};
	});

	check_availability();
	$('#incall, #outcall').change(function() {
		check_availability();
	});
	function check_availability() {
		if($('#incall').is(":checked")) {
			$(".hide-incall").show();
		} else {
			$(".hide-incall").hide();
		}

		if($('#outcall').is(":checked")) {
			$(".hide-outcall").show();
		} else {
			$(".hide-outcall").hide();
		}

		if(!$('#incall').is(":checked") && !$('#outcall').is(":checked")) {
			$(".hide-outcall, .hide-incall").show();
		}
	}

	$('#register_form').on('submit', function(event) {
		$('.register-form .form-input-accept-tos').removeClass('form-input-accept-tos-err');
		if($('.register-form input[name="tos_accept"]').length && !$('.register-form input[name="tos_accept"]').is(':checked')) {
			$('.register-form .form-input-accept-tos').addClass('form-input-accept-tos-err');
			return false;
		}

		var button = $('#register_form #register_submit');
		if(button.prop("disabled") === false) {
			button.prop("disabled",true);
			setTimeout(function() {
				button.prop("disabled",false);
			}, 4000);
		}
	});
});
</script>
<form action="<?php echo $form_url; ?>" method="post" class="form-styling register-form" id="register_form">
	<small class="mandatory l"><?php _e('Fields marked with <i>*</i> are mandatory','escortwp'); ?></small>
	<div class="clear20"></div>
	<input type="hidden" name="action" value="emails" />
	<input type="hidden" name="action" value="register" />
	<?php if (isset($escort_post_id)) { ?>
		<input type="hidden" name="escort_post_id" value="<?php echo $escort_post_id; ?>" />
	<?php } ?>
	<?php if (isset($agencyid)) { ?>
		<input type="hidden" name="agencyid" value="<?php echo $agencyid; ?>" />
	<?php } ?>

	<?php if(!$escort_post_id && !$agencyid) { ?>
	<div class="form-label">
		<label class="with-help" for="user"><?php _e('Username','escortwp'); ?><i>*</i></label>
		<small class="checkuser"><?php _e('Between 4 and 30 characters','escortwp'); ?></small>
	</div>
	<div class="form-input">
		<input type="text" name="user" id="user" class="input longinput" minlength="4" maxlength="30" value="<?php echo $user; ?>" required />
	</div> <!-- username --> <div class="formseparator"></div>

	<div class="form-label">
		<label class="with-help" for="pass"><?php _e('Password','escortwp'); ?><i>*</i></label>
		<small><?php _e('Must be between 6 and 30 characters','escortwp'); ?></small>
	</div>
	<div class="form-input">
		<input type="password" name="pass" id="pass" class="input longinput" minlength="6" maxlength="30" value="<?php echo $pass; ?>" required autocomplete="off" />
	</div> <!-- password --> <div class="formseparator"></div>
	<?php } // if is new registration ?>

	<?php if(!$agencyid) {?>
	<div class="form-label">
		<label for="youremail"><?php _e('Your Email','escortwp'); ?><i>*</i></label>
	</div>
	<div class="form-input">
		<input type="email" name="youremail" id="youremail" class="input longinput" value="<?php echo $youremail; ?>" required />
	</div> <!-- email --> <div class="formseparator"></div>
	<?php } ?>

	<?php if(current_user_can('level_10') && !isset($escort_post_id) & !isset($agency_profile_id)) { ?>
	<div class="form-label">
		<label><?php _e('Send verification email','escortwp'); ?></label>
	</div>
	<div class="form-input">
		<label for="sendverificationyes"><input type="radio" id="sendverificationyes" name="sendverification" value="1" <?php if($sendverification == "1") { echo ' checked'; } ?> /><?php _e('Yes','escortwp'); ?></label>
		<label for="sendverificationno"><input type="radio" id="sendverificationno" name="sendverification" value="2" <?php if($sendverification == "2") { echo ' checked'; } ?> /><?php _e('No','escortwp'); ?></label>
		<div class="clear10"></div>
		<small>
			<i>!</i> <?php _e('Send a validation link to the email. The user has to click that link in order to activate the account and verify that the email is valid.','escortwp'); ?><br />
			<i>!</i> <?php _e('If you choose not to send a validation link then the account will be activated by default.','escortwp'); ?>
		</small>
	</div> <!-- send verification email --> <div class="formseparator"></div>

	<div class="form-label">
		<label><?php _e('Send username and password by email','escortwp'); ?></label>
	</div>
	<div class="form-input" id="sendauth">
		<label for="sendauthyes"><input type="radio" id="sendauthyes" name="sendauth" value="1" <?php if($sendauth == "1") { echo ' checked'; } ?> /><?php _e('Yes','escortwp'); ?></label>
		<label for="sendauthno"><input type="radio" id="sendauthno" name="sendauth" value="2" <?php if($sendauth == "2") { echo ' checked'; } ?> /><?php _e('No','escortwp'); ?></label>
	</div> <!-- send user and pass in email --> <div class="formseparator"></div>
	<?php } // if admin ?>

	<div class="form-label">
		<label class="with-help" for="yourname"><?php if($agencyid) { echo ucfirst($taxonomy_profile_name)." "; } ?><?php _e('Name','escortwp'); ?><i>*</i></label>
		<small><?php _e('will be publicly shown','escortwp'); ?></small>
	</div>
	<div class="form-input">
		<input type="text" name="yourname" id="yourname" class="input longinput" value="<?php echo $yourname; ?>" required />
	</div> <!-- name --> <div class="formseparator"></div>


	<?php if(showfield('phone')) { ?>
	<div class="form-label">
		<label for="phone"><?php _e('Phone','escortwp'); ismand('phone'); ?></label>
	</div>
	<div class="form-input">
		<input type="tel" name="phone" id="phone" class="input longinput" value="<?php echo $phone; ?>" />
		<div class="clear"></div>
	</div> <!-- phone --> <div class="formseparator"></div>

	<div class="form-label">
		<label for="phone"><?php _e('Available on','escortwp'); ?></label>
	</div>
	<div class="form-input available-on">
		<label for="whatsapp">
			<input type="checkbox" name="phone_available_on[]" value="1" id="whatsapp"<?php if(in_array("1", (array)$phone_available_on)) { echo ' checked'; } ?> />
			<span class="icon icon-whatsapp"></span> WhatsApp
		</label>
		<label for="viber">
			<input type="checkbox" name="phone_available_on[]" value="2" id="viber"<?php if(in_array("2", (array)$phone_available_on)) { echo ' checked'; } ?> />
			<span class="icon icon-viber"></span> Viber
		</label>
	</div> <!-- phone --> <div class="formseparator"></div>
	<?php } ?>

	<?php if(showfield('website')) { ?>
	<div class="form-label">
		<label for="website"><?php _e('Website','escortwp'); ismand('website'); ?></label>
	</div>
	<div class="form-input">
		<input type="url" name="website" id="website" class="input longinput" value="<?php echo $website; ?>" />
	</div> <!-- website --> <div class="formseparator"></div>
   	<?php } ?>

	<?php if(showfield('instagram')) { ?>
	<div class="form-label">
		<label for="instagram"><?php _e('Instagram','escortwp'); ismand('instagram'); ?></label>
	</div>
	<div class="form-input">
		@ <input type="text" name="instagram" id="instagram" class="input" size="30" value="<?php echo $instagram; ?>" />
		<small><?=__('Instagram username','escortwp')?></small>
	</div> <!-- instagram --> <div class="formseparator"></div>
   	<?php } ?>

	<?php if(showfield('snapchat')) { ?>
	<div class="form-label">
		<label for="snapchat"><?php _e('SnapChat','escortwp'); ismand('snapchat'); ?></label>
	</div>
	<div class="form-input">
		@ <input type="text" name="snapchat" id="snapchat" class="input" size="30" value="<?php echo $snapchat; ?>" />
		<small><?=__('Snapchat username','escortwp')?></small>
	</div> <!-- snapchat --> <div class="formseparator"></div>
   	<?php } ?>

	<?php if(showfield('twitter')) { ?>
	<div class="form-label">
		<label for="twitter"><?php _e('Twitter','escortwp'); ismand('twitter'); ?></label>
	</div>
	<div class="form-input">
		<input type="url" name="twitter" id="twitter" class="input longinput" placeholder="https://" value="<?php echo $twitter; ?>" />
		<small><?=__('Twitter profile url','escortwp')?></small>
	</div> <!-- twitter --> <div class="formseparator"></div>
   	<?php } ?>

	<?php if(showfield('facebook')) { ?>
	<div class="form-label">
		<label for="facebook"><?php _e('Facebook','escortwp'); ismand('facebook'); ?></label>
	</div>
	<div class="form-input">
		<input type="url" name="facebook" id="facebook" class="input longinput" placeholder="https://" value="<?php echo $facebook; ?>" />
		<small><?=__('Facebook profile/page url','escortwp')?></small>
	</div> <!-- facebook --> <div class="formseparator"></div>
   	<?php } ?>

   	<?php if(get_option('locationdropdown') == "1") { ?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			show_search_cities(c)
			//get cities from the selected country in the countries dropdown
			var c = ".country";
			var parent_div = ".register-form";
			<?php if(showfield('state')) { ?>
				var city_div = '.inputstates';

				var state_c = '.state';
				var state_div = '.inputcities';
			<?php } else { ?>
				var city_div = '.inputcities';
			<?php } ?>

			$(parent_div+' '+c).change(function(){ show_search_cities(c); });
			function show_search_cities(e) {
				var country = $(parent_div+' '+e).val();
				$(parent_div+' '+city_div).text($(city_div).data('text'));
				<?php if(showfield('state')) { ?>
					$(parent_div+' '+state_div).text($(state_div).data('text'));
				<?php } ?>

				if(country < 1) return true;

				loader($(e).parents(parent_div).find(city_div));
				$.ajax({
					type: "GET",
					url: "<?php bloginfo('template_url'); ?>/ajax/get-cities.php",
					<?php if(showfield('state')) { ?>
						data: "id=" + country +"&hide_empty=0&state=yes&select2=yes",
					<?php } else { ?>
						data: "id=" + country +"&hide_empty=0&select2=yes",
					<?php } ?>
					success: function(data){
						$(e).parents(parent_div).find(city_div).html(data);
						if($(window).width() > "960") { $('.select2').select2(); }
					}
				});
			}

			<?php if(showfield('state')) { ?>
				$(parent_div).on("change", state_c, function(){
					show_search_cities_when_states(state_c);
				});
				function show_search_cities_when_states(e) {
					var state = $(parent_div+' '+e).val();
					$(parent_div+' '+state_div).text($(state_div).data('text'));
					if(state < 1) {
						return true;
					}

					loader($(e).parents(parent_div).find(state_div));
					$.ajax({
						type: "GET",
						url: "<?php bloginfo('template_url'); ?>/ajax/get-cities.php",
						data: "id=" + state +"&hide_empty=0&select2=yes",
						success: function(data){
							$(e).parents(parent_div).find(state_div).html(data);
							if($(window).width() > "960") { $('.select2').select2(); }
						}
					});
				}
			<?php } // if showfield('state') ?>
		});
	</script>
	<?php } // if the city and state need to be dropdowns ?>
	<div class="form-label">
		<label for="country"><?php _e('Country','escortwp'); ?><i>*</i></label>
	</div>
	<div class="form-input">
		<?php
		$args = array(
			'show_option_none'   => __('Select country','escortwp'),
		    'orderby'            => 'name',
		    'order'              => 'ASC',
			'hide_empty'         => 0,
			'echo'               => 1,
			'selected'           => $country,
			'hierarchical'       => 1,
			'name'               => 'country',
			'id'                 => 'country',
			'class'              => 'country select2',
			'depth'              => 1,
			'taxonomy'           => $taxonomy_location_url
		);

		$categories_count_array = array(
			'show_option_all' => '',
			'show_count' => '0',
			'hide_empty' => '0',
			'show_option_none' => '',
			'pad_counts' => '0',
			'taxonomy' => $taxonomy_location_url,
			'parent' => 0,
			'number' => '2',
			'fields' => 'ids'
		);
		$categories_count_data = get_categories($categories_count_array);
		if(count($categories_count_data) == "1") {
			unset($categories_count_array['fields']);
			$country_list = get_categories($categories_count_array);
			$country_list = array_values($country_list);
			echo '<div class="clear10"></div>'.$country_list[0]->name;
			echo '<input type="hidden" name="country" class="country" value="'.$country_list[0]->term_id.'" />';
			?>
			<script type="text/javascript"> jQuery(document).ready(function($) { $('.register-form .country').trigger('change'); }); </script>
			<?php
		} else {
			wp_dropdown_categories( $args );
		}
		$city_parent = $country;
		?>
    </div> <!-- country --> <div class="formseparator"></div>

	<?php if(showfield('state')) { ?>
	<div class="form-label">
		<label for="state"><?php _e('State','escortwp'); ismand('state'); ?></label>
	</div>
	<div class="form-input inputstates" data-text="<?=__('Please select a country first','escortwp')?>">
		<?php if(get_option('locationdropdown') == "1") {
				if($country > 0) {
					$city_parent = $state;
					$args = array(
						'show_option_all'    => '',
						'show_option_none'   => __('Select State','escortwp'),
						'show_last_update'   => 0,
						'show_count'         => 0,
						'parent'			 => $country,
						'hide_empty'         => 0,
						'exclude'            => '',
						'echo'               => 1,
						'selected'           => $state,
						'hierarchical'       => 1, 
						'name'               => 'state',
						'id'                 => '',
						'class'              => 'state select2',
						'depth'              => 1,
						'tab_index'          => 0,
					    'orderby'            => 'name',
					    'order'              => 'ASC',
						'taxonomy'           => $taxonomy_location_url );
					wp_dropdown_categories( $args );
				} else {
					_e('Please select a country first','escortwp');
				}
		} else { ?>
			<input type="text" name="state" id="state" class="input longinput" value="<?php echo $state; ?>" />
		<?php } ?>
	</div> <!-- state --> <div class="formseparator"></div>
   	<?php } ?>

    <div class="form-label">
		<label for="city"><?php _e('City','escortwp'); ?> <i>*</i></label>
	</div>
	<?php
	if(showfield('state')) {
		$city_text = __('Please select a state first','escortwp');
	} else {
		$city_text = __('Please select a country first','escortwp');
	}
	?>
	<div class="form-input inputcities" data-text="<?=$city_text?>">
		<?php if(get_option('locationdropdown') == "1") {
			if(($country > 0 && !showfield('state')) || ($state > 0 && showfield('state'))) {
				$args = array(
					'show_option_all'    => '',
					'show_option_none'   => __('Select City','escortwp'),
					'show_last_update'   => 0,
					'show_count'         => 0,
					'parent'			 => $city_parent,
					'hide_empty'         => 0,
					'exclude'            => '',
					'echo'               => 1,
					'selected'           => $city,
					'hierarchical'       => 1, 
					'name'               => 'city',
					'id'                 => '',
					'class'              => 'city select2',
					'depth'              => 1,
					'tab_index'          => 0,
				    'orderby'            => 'name',
				    'order'              => 'ASC',
					'taxonomy'           => $taxonomy_location_url );
				wp_dropdown_categories( $args );
			} else {
				echo $city_text;
			}
		} else { ?>
			<input type="text" name="city" id="city" class="input longinput" value="<?php echo $city; ?>" />
		<?php } ?>
	</div> <!-- city --> <div class="formseparator"></div>

	<div class="form-label">
		<label><?php _e('Gender','escortwp'); ?><i>*</i></label>
	</div>
	<div class="form-input" id="gender">
		<?php
		foreach($gender_a as $key=>$g) {
			if(in_array($key, $settings_theme_genders)) {
		?>
		<label for="gender<?php echo $key ?>">
			<input type="radio" name="gender" value="<?php echo $key; ?>" id="gender<?php echo $key ?>"<?php if($gender == $key) { echo ' checked'; } ?> />
			<?php _e($g,'escortwp'); ?><br />
		</label>
		<?php
			} // if in_array
		} // foreach
		?>
	</div> <!-- GENDER --> <div class="formseparator"></div>

	<div class="form-label">
		<label class="with-help"><?php _e('Date of birth','escortwp'); ?><i>*</i></label>
		<small><?php _e('we\'ll calculate your age from this','escortwp'); ?></small>
	</div>
	<div class="form-input">
		<select name="dateday" id="dateday" class="birthday select col33 l">
			<option value=""><?php _e('Day','escortwp'); ?></option>
			<?php
			for($i=1;$i<=31;$i+=1) {
				$selected = $dateday == $i ? ' selected="selected"' : "";
				echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
			}
			?>
		</select>
		<select name="datemonth" id="datemonth" class="birthday select col33 l">
			<option value=""><?php _e('Month','escortwp'); ?></option>
			<option value="1"<?php if($datemonth == "1") { echo ' selected="selected"'; } ?>><?php _e('January','escortwp'); ?></option>
			<option value="2"<?php if($datemonth == "2") { echo ' selected="selected"'; } ?>><?php _e('February','escortwp'); ?></option>
			<option value="3"<?php if($datemonth == "3") { echo ' selected="selected"'; } ?>><?php _e('March','escortwp'); ?></option>
			<option value="4"<?php if($datemonth == "4") { echo ' selected="selected"'; } ?>><?php _e('April','escortwp'); ?></option>
			<option value="5"<?php if($datemonth == "5") { echo ' selected="selected"'; } ?>><?php _e('May','escortwp'); ?></option>
			<option value="6"<?php if($datemonth == "6") { echo ' selected="selected"'; } ?>><?php _e('June','escortwp'); ?></option>
			<option value="7"<?php if($datemonth == "7") { echo ' selected="selected"'; } ?>><?php _e('July','escortwp'); ?></option>
			<option value="8"<?php if($datemonth == "8") { echo ' selected="selected"'; } ?>><?php _e('August','escortwp'); ?></option>
			<option value="9"<?php if($datemonth == "9") { echo ' selected="selected"'; } ?>><?php _e('September','escortwp'); ?></option>
			<option value="10"<?php if($datemonth == "10") { echo ' selected="selected"'; } ?>><?php _e('October','escortwp'); ?></option>
			<option value="11"<?php if($datemonth == "11") { echo ' selected="selected"'; } ?>><?php _e('November','escortwp'); ?></option>
			<option value="12"<?php if($datemonth == "12") { echo ' selected="selected"'; } ?>><?php _e('December','escortwp'); ?></option>
		</select>
		<select name="dateyear" id="dateyear" class="birthday select col33 l">
			<option value=""><?php _e('Year','escortwp'); ?></option>
			<?php
			$startyear = date('Y') - 18;
			$endyear = date('Y') - 100;
			for($i=$startyear;$i>=$endyear;$i--) {
				$selected = $dateyear == $i ? ' selected="selected"' : "";
				echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
			}
			?>
		</select>
	</div> <!-- DATE OF BIRTH --> <div class="formseparator"></div>

	<?php if(showfield('ethnicity')) { ?>
	<div class="form-label">
		<label for="ethnicity"><?php _e('Ethnicity','escortwp'); ismand('ethnicity'); ?></label>
	</div>
	<div class="form-input">
		<select name="ethnicity" id="ethnicity">
			<option value=""><?php _e('Select','escortwp'); ?></option>
			<?php foreach($ethnicity_a as $key=>$s) { ?>
				<option value="<?php echo $key; ?>"<?php if($ethnicity == $key) { echo ' selected="selected"'; } ?>><?php _e($s,'escortwp'); ?></option>
			<?php } ?>
		</select>
	</div> <!-- SKIN COLOR --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('haircolor')) { ?>
	<div class="form-label">
		<label for="haircolor"><?php _e('Hair Color','escortwp'); ismand('haircolor'); ?></label>
	</div>
	<div class="form-input">
		<select name="haircolor" id="haircolor" class="haircolor">
			<option value=""><?php _e('Select','escortwp'); ?></option>
			<?php foreach($haircolor_a as $key=>$h) { ?>
				<option value="<?php echo $key; ?>"<?php if($haircolor == $key) { echo ' selected="selected"'; } ?>><?php _e($h,'escortwp'); ?></option>
			<?php } ?>
		</select>
	</div> <!-- HAIR COLOR --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('hairlength')) { ?>
	<div class="form-label">
		<label for="hairlength"><?php _e('Hair length','escortwp'); ismand('hairlength'); ?></label>
	</div>
	<div class="form-input">
		<select name="hairlength" id="hairlength" class="hairlength">
			<option value=""><?php _e('Select','escortwp'); ?></option>
			<?php foreach($hairlength_a as $key=>$h) { ?>
				<option value="<?php echo $key; ?>"<?php if($hairlength == $key) { echo ' selected="selected"'; } ?>><?php _e($h,'escortwp'); ?></option>
			<?php } ?>
		</select>
	</div> <!-- HAIR LENGTH --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('bustsize')) { ?>
	<div class="form-label">
		<label for="bustsize"><?php _e('Bust size','escortwp'); ismand('bustsize'); ?></label>
		<small><?php _e('mandatory only for females','escortwp'); ?></small>
	</div>
	<div class="form-input">
		<select name="bustsize" id="bustsize" class="bustsize">
			<option value=""><?php _e('Select','escortwp'); ?></option>
			<?php foreach($bustsize_a as $key=>$b) { ?>
				<option value="<?php echo $key; ?>"<?php if($bustsize == $key) { echo ' selected="selected"'; } ?>><?php _e($b,'escortwp'); ?></option>
			<?php } ?>
		</select>
	</div> <!-- BUST SIZE --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('height')) { ?>
	<div class="form-label">
		<label for="height"><?php _e('Height','escortwp'); ismand('height'); ?></label>
	</div>
	<div class="form-input">
		<input type="number" name="height" size="4" maxlength="4" id="height" class="input smallinput text-center" value="<?php echo $height; ?>" /> &nbsp; <?=get_option('heightscale') == "imperial" ? "ft" : "cm"?>
		<?php if(get_option('heightscale') == "imperial") { ?>
		&nbsp;&nbsp;<input type="number" name="height2" size="2" maxlength="2" id="height2" class="input smallinput text-center" value="<?php echo $height2; ?>" /> &nbsp; inches
		<?php } ?>
	</div> <!-- HEIGHT --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('weight')) { ?>
	<div class="form-label">
		<label for="weight"><?php _e('Weight','escortwp'); ismand('weight'); ?></label>
	</div>
	<div class="form-input">
		<input type="number" step="0.01" name="weight" size="4" id="weight" class="input smallinput text-center" value="<?php echo $weight; ?>" /> &nbsp; <?=get_option('heightscale') == "imperial" ? "lb" : "kg"?>
	</div> <!-- WEIGHT --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('build')) { ?>
	<div class="form-label">
		<label for="build"><?php _e('Build','escortwp'); ismand('build'); ?></label>
	</div>
	<div class="form-input">
		<select name="build" id="build" class="build">
			<option value=""><?php _e('Select','escortwp'); ?></option>
			<?php foreach($build_a as $key=>$b) { ?>
				<option value="<?php echo $key; ?>"<?php if($build == $key) { echo ' selected="selected"'; } ?>><?php _e($b,'escortwp'); ?></option>
			<?php } ?>
		</select>
	</div> <!-- BUILT --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('looks')) { ?>
	<div class="form-label">
		<label for="looks"><?php _e('Looks','escortwp'); ismand('looks'); ?></label>
	</div>
	<div class="form-input">
		<select name="looks" id="looks" class="looks">
			<option value=""><?php _e('Select','escortwp'); ?></option>
			<?php foreach($looks_a as $key=>$l) { ?>
				<option value="<?php echo $key; ?>"<?php if($looks == $key) { echo ' selected="selected"'; } ?>><?php _e($l,'escortwp'); ?></option>
			<?php } ?>
		</select>
	</div> <!-- LOOKS --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php
	if(showfield('availability')) {
		if(!$availability) { $availability = array(); }
	?>
	<div class="form-label">
		<label><?php _e('Availability','escortwp'); ismand('availability'); ?></label>
	</div>
	<div class="form-input">
		<label for="incall">
			<input type="checkbox" name="availability[]" value="1" id="incall"<?php if( in_array("1", $availability) ) { echo ' checked'; } ?> />
			<?php _e('Incall','escortwp'); ?>
		</label>
		<label for="outcall">
			<input type="checkbox" name="availability[]" value="2" id="outcall"<?php if( in_array("2", $availability) ) { echo ' checked'; } ?> />
			<?php _e('Outcall','escortwp'); ?>
		</label>
	</div> <!-- AVAILABILITY --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('smoker')) { ?>
	<div class="form-label">
		<label><?php _e('Smoker','escortwp'); ismand('smoker'); ?></label>
	</div>
	<div class="form-input">
		<label for="smokeyes"><input type="radio" name="smoker" value="1" id="smokeyes"<?php if($smoker == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
		<label for="smokeno"><input type="radio" name="smoker" value="2" id="smokeno"<?php if($smoker == "2") { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
	</div> <!-- SMOKER --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('aboutyou')) { ?>
	<div class="form-label">
		<?php
			$labeltext = __('About you','escortwp');
			if ($agencyid || current_user_can('level_10')) { $labeltext = sprintf(esc_html__('About the %s','escortwp'),$taxonomy_profile_name); }
		?>
		<label for="aboutyou"><?php echo $labeltext; ismand('aboutyou'); ?></label>
	</div>
	<div class="form-input">
		<textarea name="aboutyou" id="aboutyou" class="textarea longtextarea" rows="7" required><?php echo strip_tags($aboutyou); ?></textarea>
		<small><?php _e('html code will be removed','escortwp'); ?></small>
	</div> <!-- about you --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('education')) { ?>
	<div class="form-label">
		<label for="education"><?php _e('Education','escortwp'); ismand('education'); ?></label>
	</div>
	<div class="form-input">
		<input type="text" name="education" id="education" class="input longinput" value="<?php echo $education; ?>" />
	</div> <!-- education --> <div class="formseparator"></div>
   	<?php } // showfield ?>

	<?php if(showfield('sports')) { ?>
	<div class="form-label">
		<label for="sports"><?php _e('Sports','escortwp'); ismand('sports'); ?></label>
	</div>
	<div class="form-input">
		<input type="text" name="sports" id="sports" class="input longinput" value="<?php echo $sports; ?>" />
	</div> <!-- sports --> <div class="formseparator"></div>
   	<?php } // showfield ?>

	<?php if(showfield('hobbies')) { ?>
	<div class="form-label">
		<label for="hobbies"><?php _e('Hobbies','escortwp'); ismand('hobbies'); ?></label>
	</div>
	<div class="form-input">
		<input type="text" name="hobbies" id="hobbies" class="input longinput" value="<?php echo $hobbies; ?>" />
	</div> <!-- hobbies --> <div class="formseparator"></div>
   	<?php } // showfield ?>

	<?php if(showfield('zodiacsign')) { ?>
	<div class="form-label">
		<label for="zodiacsign"><?php _e('Zodiac sign','escortwp'); ismand('zodiacsign'); ?></label>
	</div>
	<div class="form-input">
		<input type="text" name="zodiacsign" id="zodiacsign" class="input longinput" value="<?php echo $zodiacsign; ?>" />
	</div> <!-- zodiac sign --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('sexualorientation')) { ?>
	<div class="form-label">
		<label for="sexualorientation"><?php _e('Sexual orientation','escortwp'); ismand('sexualorientation'); ?></label>
	</div>
	<div class="form-input">
		<input type="text" name="sexualorientation" id="sexualorientation" class="input longinput" value="<?php echo $sexualorientation; ?>" />
	</div> <!-- sexual orientation --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('occupation')) { ?>
	<div class="form-label">
		<label for="occupation"><?php _e('Occupation','escortwp'); ismand('occupation'); ?></label>
	</div>
	<div class="form-input">
		<input type="text" name="occupation" id="occupation" class="input longinput" value="<?php echo $occupation; ?>" />
	</div> <!-- occupation --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('language')) { ?>
	<div class="form-label">
		<label><?php _e('Languages spoken','escortwp'); ismand('language'); ?></label>
	</div>
	<div class="form-input" id="language">
		<input type="text" name="language1" class="input" value="<?php echo $language1; ?>" />
		<select name="language1level" id="language1level" class="language1level">
			<option value=""><?php _e('Select level','escortwp'); ?></option>
			<?php foreach($languagelevel_a as $key=>$l) { ?>
			<option value="<?php echo $key; ?>"<?php if($language1level == $key) { echo ' selected="selected"'; } ?>><?php _e($l,'escortwp'); ?></option>
			<?php } ?>
		</select><div class="clear10"></div>

		<input type="text" name="language2" class="input" value="<?php echo $language2; ?>" />
		<select name="language2level" id="language2level" class="language2level">
			<option value=""><?php _e('Select level','escortwp'); ?></option>
			<?php foreach($languagelevel_a as $key=>$l) { ?>
			<option value="<?php echo $key; ?>"<?php if($language2level == $key) { echo ' selected="selected"'; } ?>><?php _e($l,'escortwp'); ?></option>
			<?php } ?>
		</select><div class="clear10"></div>

		<input type="text" name="language3" class="input" value="<?php echo $language3; ?>" />
		<select name="language3level" id="language3level" class="language3level">
			<option value=""><?php _e('Select level','escortwp'); ?></option>
			<?php foreach($languagelevel_a as $key=>$l) { ?>
			<option value="<?php echo $key; ?>"<?php if($language3level == $key) { echo ' selected="selected"'; } ?>><?php _e($l,'escortwp'); ?></option>
			<?php } ?>
		</select>
	</div> <!-- language --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('rates')) { ?>
	<div class="form-label">
		<label><?php _e('Rates','escortwp'); ismand('rates'); ?></label>
		<?php if(ismand('rates', 'no')) { echo '<small>'.__('at least one rate','escortwp').'</small>'; } ?>
	</div>
	<div class="form-input">
		<div class="col30 l currency-label-text"><?php _e('Currency','escortwp'); ?>:</div>
		<div class="col60 l currency-label-dropdown">
			<select name="currency" id="currency" class="col100">
				<?php
				foreach($currency_a as $key=>$c) {
					$selected = $currency == $key ? ' selected="selected"' : "";
					echo '<option value="'.$key.'"'.$selected.'>'.$c[0].' - '.__($c[1],'escortwp').'</option>'."\n";
				}
				?>
			</select>
		</div>
		<div class="clear20"></div>

		<div class="rates l">
			<div class="col30 l">&nbsp;</div>
			<div class="col30 text-center l hide-incall"><b><?php _e('Incall','escortwp'); ?></b></div>
			<div class="col30 text-center l hide-outcall"><b><?php _e('Outcall','escortwp'); ?></b></div>
		</div>
		<div class="clear10"></div>
		<div class="rates l">
			<div class="col30 l rates-label"><?php echo "30 ".__('minutes','escortwp'); ?></div>
			<div class="col30 l hide-incall"><input type="number" name="rate30min_incall" maxlength="15" value="<?php echo $rate30min_incall; ?>" class="input" /></div>
			<div class="col30 l hide-outcall"><input type="number" name="rate30min_outcall" maxlength="15" value="<?php echo $rate30min_outcall; ?>" class="input" /></div>
		</div>
		<div class="rates l">
			<div class="col30 l rates-label"><?php echo "1 ".__('hour','escortwp'); ?></div>
			<div class="col30 l hide-incall"><input type="number" name="rate1h_incall" maxlength="15" value="<?php echo $rate1h_incall; ?>" class="input" /></div>
			<div class="col30 l hide-outcall"><input type="number" name="rate1h_outcall" maxlength="15" value="<?php echo $rate1h_outcall; ?>" class="input" /></div>
		</div>
		<div class="rates l">
			<div class="col30 l rates-label"><?php echo "2 ".__('hours','escortwp'); ?></div>
			<div class="col30 l hide-incall"><input type="number" name="rate2h_incall" maxlength="15" value="<?php echo $rate2h_incall; ?>" class="input" /></div>
			<div class="col30 l hide-outcall"><input type="number" name="rate2h_outcall" maxlength="15" value="<?php echo $rate2h_outcall; ?>" class="input" /></div>
		</div>
		<div class="rates l">
			<div class="col30 l rates-label"><?php echo "3 ".__('hours','escortwp'); ?></div>
			<div class="col30 l hide-incall"><input type="number" name="rate3h_incall" maxlength="15" value="<?php echo $rate3h_incall; ?>" class="input" /></div>
			<div class="col30 l hide-outcall"><input type="number" name="rate3h_outcall" maxlength="15" value="<?php echo $rate3h_outcall; ?>" class="input" /></div>
		</div>
		<div class="rates l">
			<div class="col30 l rates-label"><?php echo "6 ".__('hours','escortwp'); ?></div>
			<div class="col30 l hide-incall"><input type="number" name="rate6h_incall" maxlength="15" value="<?php echo $rate6h_incall; ?>" class="input" /></div>
			<div class="col30 l hide-outcall"><input type="number" name="rate6h_outcall" maxlength="15" value="<?php echo $rate6h_outcall; ?>" class="input" /></div>
		</div>
		<div class="rates l">
			<div class="col30 l rates-label"><?php echo "12 ".__('hours','escortwp'); ?></div>
			<div class="col30 l hide-incall"><input type="number" name="rate12h_incall" maxlength="15" value="<?php echo $rate12h_incall; ?>" class="input" /></div>
			<div class="col30 l hide-outcall"><input type="number" name="rate12h_outcall" maxlength="15" value="<?php echo $rate12h_outcall; ?>" class="input" /></div>
		</div>
		<div class="rates l">
			<div class="col30 l rates-label"><?php echo "24 ".__('hours','escortwp'); ?></div>
			<div class="col30 l hide-incall"><input type="number" name="rate24h_incall" maxlength="15" value="<?php echo $rate24h_incall; ?>" class="input" /></div>
			<div class="col30 l hide-outcall"><input type="number" name="rate24h_outcall" maxlength="15" value="<?php echo $rate24h_outcall; ?>" class="input" /></div>
		</div>
		<div class="clear"></div>
	</div> <!-- RATES --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('services')) { ?>
	<div class="form-label">
		<label><?php _e('Services','escortwp'); ismand('services'); ?></label>
	</div>
	<div class="form-input">
		<?php
		if(!$services) { $services = array(); }
		foreach($services_a as $key=>$service) { ?>
			<div class="col50 one-service l">
				<label for="service<?php echo $key; ?>">
					<input type="checkbox" name="services[]" value="<?php echo $key; ?>" id="service<?php echo $key; ?>"<?php if( in_array($key, $services) ) { echo ' checked'; } ?> />
					<?php _e($service,'escortwp'); ?>
				</label>
				<div class="clear5"></div>
			</div> <!-- one service -->
		<?php } // foreach ?>
	</div> <!-- SERVICES --> <div class="formseparator"></div>
	<?php } // showfield ?>

	<?php if(showfield('extraservices')) { ?>
	<div class="form-label">
		<label for="extraservices"><?php _e('Extra services','escortwp'); ismand('extraservices'); ?></label>
	</div>
	<div class="form-input">
		<input type="text" name="extraservices" id="extraservices" class="input longinput" value="<?php echo $extraservices; ?>" />
	</div> <!-- extra services --> <div class="formseparator"></div>
	<?php } // showfield ?>

    <?php if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && !is_user_logged_in() && get_option("recaptcha2")) { ?>
	<div class="form-input">
		<div class="g-recaptcha" data-sitekey="<?php echo get_option('recaptcha_sitekey'); ?>"></div>
	</div> <!-- message --> <div class="formseparator"></div>
    <?php } ?>

    <?php
    $tos_page_data = get_post(get_option('tos_page_id'));
    $data_protection_page_data = get_post(get_option('data_protection_page_id'));
    $tos_pages_links = array();
    if(($tos_page_data || $data_protection_page_data) && !is_user_logged_in()) {
    	if($tos_page_data) {
    		$message = sprintf(__('I agree with the %s of this website', 'escortwp'), '<a href="'.get_permalink(get_option('tos_page_id')).'" target="_blank">'.$tos_page_data->post_title.'</a>');
    	}
    	if($data_protection_page_data) {
    		$message = sprintf(__('I agree with the %s of this website', 'escortwp'), '<a href="'.get_permalink(get_option('data_protection_page_id')).'" target="_blank">'.$data_protection_page_data->post_title.'</a>');
    	}
    	if($data_protection_page_data && $tos_page_data) {
    		$message = sprintf(__('I agree with the %s and the %s of this website', 'escortwp'), '<a href="'.get_permalink(get_option('tos_page_id')).'" target="_blank">'.$tos_page_data->post_title.'</a>', '<a href="'.get_permalink(get_option('data_protection_page_id')).'" target="_blank">'.$data_protection_page_data->post_title.'</a>');
    	}
    ?>
    <div class="formseparator"></div>
	<div class="form-input col100 center form-input-accept-tos">
		<label for="tos_checkbox" class="rad25">
			<input type="checkbox" name="tos_accept" value="1" id="tos_checkbox"<?php if($_POST['tos_accept'] == "1") { echo ' checked'; } ?> />
			<?=$message?>
		</label>
	</div> <!-- message --> <div class="clear15"></div>
    <?php } ?>

	<div class="text-center">
		<input id="register_submit" type="submit" name="submit" value="<?php if($escort_post_id) { _e('Update Profile','escortwp'); } elseif ($agencyid) { printf(esc_html__('Add %s','escortwp'),$taxonomy_profile_name); } else { _e('Complete Registration','escortwp'); } ?>" class="pinkbutton rad25" />
	</div> <!--center-->
</form>