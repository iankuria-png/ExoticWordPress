<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }
?>

<?php if ($err) { echo "<div class=\"err rad25\">$err</div>"; } ?>
<?php
global $taxonomy_location_url, $taxonomy_agency_name;
if ($agency_post_id) {
	$form_url = get_permalink(get_option('agency_edit_personal_info_page_id'));
} else  {
	$form_url = get_permalink(get_option('agency_reg_page_id'));
}

if ($admin_editing_agency == "yes") {
	$form_url = get_permalink($agency_post_id);
}
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	//check if the current user is already taken
	$('.register-form-ag #user').keyup(function(){
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

	$('.register-form-ag').on('submit', function(event) {
		if($('.register-form-ag input[name="tos_accept"]').length && !$('.register-form-ag input[name="tos_accept"]').is(':checked')) {
			$('.register-form-ag .form-input-accept-tos').addClass('form-input-accept-tos-err');
			return false;
		}

		var button = $('.register-form-ag .registersubmit');
		if(button.prop("disabled") === false) {
			button.prop("disabled",true);
			setTimeout(function() {
				button.prop("disabled",false);
			}, 2000);
		}
	});
});
</script>
<form action="<?php echo $form_url; ?>" method="post" class="form-styling register-form-ag">
	<small class="mandatory l"><?php _e('Fields marked with <i>*</i> are mandatory','escortwp'); ?></small>
	<div class="clear30"></div>
	<input type="hidden" name="action" value="emails" />
	<input type="hidden" name="action" value="register" />
    <input type="text" name="emails" value="" class="hide" />
    <?php if ($agency_post_id) { ?>
	    <input type="hidden" name="agency_post_id" value="<?php echo $agency_post_id; ?>" />
    <?php } ?>
    
    <?php if(!$agency_post_id) { ?>
	    <div class="form-label">
			<label for="user"><?php _e('Username','escortwp'); ?><i>*</i></label>
			<small class="checkuser"><?php _e('Between 4 and 30 characters','escortwp'); ?></small>
		</div>
		<div class="form-input">
	    	<input type="text" name="user" id="user" class="input longinput" minlength="4" maxlength="30" value="<?php echo $user; ?>" required />
		</div> <!-- username --> <div class="formseparator"></div>

		<div class="form-label">
	    	<label for="pass"><?php _e('Password','escortwp'); ?><i>*</i></label>
	    	<small><?php _e('Must be between 6 and 30 characters','escortwp'); ?></small>
	    </div>
		<div class="form-input">
	    	<input type="password" name="pass" id="pass" class="input longinput" minlength="6" maxlength="30" value="<?php echo $pass; ?>" required autocomplete="off" />
	    </div> <!-- password --> <div class="formseparator"></div>
    <?php } // if !$agency_post_id ?>

    <div class="form-label">
    	<label for="agencyemail"><?php _e('Email','escortwp'); ?><i>*</i></label>
    </div>
	<div class="form-input">
    	<input type="email" name="agencyemail" id="agencyemail" class="input longinput" value="<?php echo $agencyemail; ?>" required />
    </div> <!-- email --> <div class="formseparator"></div>

	<?php if(current_user_can('level_10') && !$agency_post_id) { ?>
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
		<div class="form-input">
			<label for="sendauthyes"><input type="radio" id="sendauthyes" name="sendauth" value="1" <?php if($sendauth == "1") { echo ' checked'; } ?> /><?php _e('Yes','escortwp'); ?></label>
			<label for="sendauthno"><input type="radio" id="sendauthno" name="sendauth" value="2" <?php if($sendauth == "2") { echo ' checked'; } ?> /><?php _e('No','escortwp'); ?></label>
	    </div> <!-- send user and pass in email --> <div class="formseparator"></div>
	    <div class="clear30"></div>
	<?php } // if admin ?>

	<div class="form-label">
    	<label for="agencyname"><?php printf(esc_html__('%s Name','escortwp'),ucfirst($taxonomy_agency_name)); ?><i>*</i></label>
    </div>
	<div class="form-input">
    	<input type="text" name="agencyname" id="agencyname" class="input longinput" value="<?php echo $agencyname; ?>" required />
    </div> <!-- agency name --> <div class="formseparator"></div>

    <div class="form-label">
    	<label for="phone"><?php _e('Phone','escortwp'); ?><i>*</i></label>
    </div>
	<div class="form-input">
    	<input type="tel" name="phone" id="phone" class="input longinput" value="<?php echo $phone; ?>" />
    </div> <!-- phone --> <div class="formseparator"></div>

    <div class="form-label">
    	<label for="website"><?php _e('Website','escortwp'); ?></label>
    </div>
	<div class="form-input">
		<input type="url" name="website" id="website" class="input longinput" value="<?php echo $website; ?>" />
	</div> <!-- website --> <div class="formseparator"></div>

   	<?php if(get_option('locationdropdown') == "1") { ?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			//get cities from the selected country in the countries dropdown
			var c = ".country";
			var parent_div = ".register-form-ag";
			<?php if(showfield('state')) { ?>
				var city_div = '.inputstates';

				var state_c = '.state';
				var state_div = '.inputcities';
			<?php } else { ?>
				var city_div = '.inputcities';
			<?php } ?>

			$(parent_div+' '+c).on('change', function(event) {
				show_search_cities(c);
			});
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
			'hide_empty'         => 0,
			'echo'               => 1,
			'selected'           => $country,
			'hierarchical'       => 1,
			'name'               => 'country',
			'id'                 => 'country',
			'class'              => 'country select2',
			'depth'              => 1,
		    'orderby'            => 'name',
		    'order'              => 'ASC',
			'taxonomy'           => $taxonomy_location_url );

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
			echo '<div class="clear10"></div>'.$country_list[0]->name;
			echo '<input type="hidden" name="country" class="country" value="'.$country_list[0]->term_id.'" />';
			?>
			<script type="text/javascript"> jQuery(document).ready(function($) { $('.register-form-ag .country').trigger('change'); }); </script>
			<?php
		} else {
			wp_dropdown_categories( $args );
		}
		$city_parent = $country;
		?>
    </div> <!-- country --> <div class="formseparator"></div>

	<?php if(showfield('state')) { ?>
	<div class="form-label">
		<label for="state"><?php _e('State','escortwp'); ?><i>*</i></label>
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
		<?php
		 if(get_option('locationdropdown') == "1") {
			if(($country > 0 && !showfield('state')) || ($state > 0 && showfield('state'))) {
				if($city[0]->term_id) { $city_id = $city[0]->term_id; }
				$args = array(
					'show_option_all'    => '',
					'show_option_none'   => __('Select City','escortwp'),
					'show_last_update'   => 0,
					'show_count'         => 0,
					'parent'			 => $city_parent,
					'hide_empty'         => 0,
					'exclude'            => '',
					'echo'               => 1,
					'selected'           => $city_id,
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
		} else {
			if($city[0]->name) { $city = $city[0]->name; }
			?>
			<input type="text" name="city" id="city" class="input longinput" value="<?php echo $city; ?>" />
		<?php } ?>
	</div> <!-- city --> <div class="formseparator"></div>

	<div class="form-label">
	    <label for="aboutagency"><?php printf(esc_html__('About the %s','escortwp'),ucfirst($taxonomy_agency_name)); ?><i>*</i></label>
	</div>
	<div class="form-input">
	    <textarea name="aboutagency" id="aboutagency" class="textarea longtextarea" rows="7" required><?php echo $aboutagency; ?></textarea>
	</div> <!-- about agency --> <div class="formseparator"></div>

    <?php if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && !is_user_logged_in() && get_option("recaptcha3")) { ?>
	<div class="form-input">
		<div class="g-recaptcha" data-sitekey="<?php echo get_option('recaptcha_sitekey'); ?>"></div>
	</div> <!-- captcha --> <div class="formseparator"></div>
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

    <div class="text-center"><input type="submit" name="submit" value="<?php if($agency_post_id) { _e('Update Profile','escortwp'); } else { _e('Complete Registration','escortwp'); } ?>" class="pinkbutton rad3 registersubmit" /></div> <!--center-->
</form>