<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

global $taxonomy_profile_name_plural, $taxonomy_profile_name, $gender_a, $settings_theme_genders;
?>
<div class="clear"></div>
<?php if ( $err && $_POST['action'] == 'addescort') { echo "<div class=\"err rad25\">$err</div>"; } ?>
<?php $form_url = get_permalink(get_the_ID()); ?>
<form action="<?php echo $form_url; ?>" method="post" class="form-styling add-bprofile-form<?=$search_page ? '2' : ''?>" novalidate>
	<small class="mandatory l"><?php _e('Fields marked with <i>*</i> are mandatory','escortwp'); ?></small>
	<div class="clear10"></div>
	<?php if (!$search_page) { ?>
	<input type="hidden" name="action" value="addescort" />
    <?php } else { ?>
	<input type="hidden" name="action" value="search" />
    <?php } ?>
    <?php if ($escort_post_id) { ?>
    <input type="hidden" name="escort_post_id" value="<?php echo $escort_post_id; ?>" />
    <?php } ?>

    <div class="form-label">
    	<label for="yourname"><?php printf(esc_html__('%s Name','escortwp'),ucwords($taxonomy_profile_name)); ?> <i>*</i></label>
    </div>
	<div class="form-input">
		<input type="text" name="yourname" id="yourname" class="input longinput" value="<?php echo $yourname; ?>" />
	</div> <!-- name --> <div class="formseparator"></div>

    <div class="form-label">
    	<label for="phone"><?php _e('Phone','escortwp'); ?></label>
    </div>
	<div class="form-input">
		<input type="tel" name="phone" id="phone" class="input longinput" value="<?php echo $phone; ?>" />
    </div> <!-- phone --> <div class="formseparator"></div>

    <div class="form-label">
    	<label for="escortemail"><?php printf(esc_html__('%s Email','escortwp'),ucwords($taxonomy_profile_name)); ?></label>
    </div>
	<div class="form-input">
		<input type="email" name="escortemail" id="escortemail" class="input longinput" value="<?php echo $escortemail; ?>" />
    </div> <!-- email --> <div class="formseparator"></div>

   	<?php if(get_option('locationdropdown') == "1") { ?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			//get cities from the selected country in the countries dropdown
			var c<?=$search_page ? '2' : ''?> = ".country";
			var parent_div<?=$search_page ? '2' : ''?> = ".add-bprofile-form<?=$search_page ? '2' : ''?>";
			<?php if(showfield('state')) { ?>
				var city_div<?=$search_page ? '2' : ''?> = '.inputstates';

				var state_c<?=$search_page ? '2' : ''?> = '.state';
				var state_div<?=$search_page ? '2' : ''?> = '.inputcities';
			<?php } else { ?>
				var city_div<?=$search_page ? '2' : ''?> = '.inputcities';
			<?php } ?>

			$(parent_div<?=$search_page ? '2' : ''?>+' '+c<?=$search_page ? '2' : ''?>).change(function(){ show_search_cities<?=$search_page ? '2' : ''?>(c<?=$search_page ? '2' : ''?>); });
			function show_search_cities<?=$search_page ? '2' : ''?>(e) {
				var country = $(parent_div<?=$search_page ? '2' : ''?>+' '+e).val();
				$(parent_div<?=$search_page ? '2' : ''?>+' '+city_div<?=$search_page ? '2' : ''?>).text($(city_div<?=$search_page ? '2' : ''?>).data('text'));
				<?php if(showfield('state')) { ?>
					$(parent_div<?=$search_page ? '2' : ''?>+' '+state_div<?=$search_page ? '2' : ''?>).text($(state_div<?=$search_page ? '2' : ''?>).data('text'));
				<?php } ?>

				if(country < 1) return true;

				loader($(e).parents(parent_div<?=$search_page ? '2' : ''?>).find(city_div<?=$search_page ? '2' : ''?>));
				$.ajax({
					type: "GET",
					url: "<?php bloginfo('template_url'); ?>/ajax/get-cities.php",
					<?php if(showfield('state')) { ?>
						data: "id=" + country +"&selected=<?php echo $state ?>&hide_empty=0&state=yes",
					<?php } else { ?>
						data: "id=" + country +"&selected=<?php echo $city ?>&hide_empty=0",
					<?php } ?>
					success: function(data){
						$(e).parents(parent_div<?=$search_page ? '2' : ''?>).find(city_div<?=$search_page ? '2' : ''?>).html(data);
					}
				});
			}

			<?php if(showfield('state')) { ?>
				$(parent_div<?=$search_page ? '2' : ''?>).on("change", state_c<?=$search_page ? '2' : ''?>, function(){
					show_search_cities_when_states<?=$search_page ? '2' : ''?>(state_c<?=$search_page ? '2' : ''?>);
				});
				function show_search_cities_when_states<?=$search_page ? '2' : ''?>(e) {
					var state = $(parent_div<?=$search_page ? '2' : ''?>+' '+e).val();
					$(parent_div<?=$search_page ? '2' : ''?>+' '+state_div<?=$search_page ? '2' : ''?>).text($(state_div<?=$search_page ? '2' : ''?>).data('text'));
					if(state < 1) {
						return true;
					}

					loader($(e).parents(parent_div<?=$search_page ? '2' : ''?>).find(state_div<?=$search_page ? '2' : ''?>));
					$.ajax({
						type: "GET",
						url: "<?php bloginfo('template_url'); ?>/ajax/get-cities.php",
						data: "id=" + state +"&selected=<?php echo $city ?>&hide_empty=0",
						success: function(data){
							$(e).parents(parent_div<?=$search_page ? '2' : ''?>).find(state_div<?=$search_page ? '2' : ''?>).html(data);
						}
					});
				}
			<?php } // if showfield('state') ?>
		});
	</script>
	<?php } // if the city and state need to be dropdowns ?>
    <div class="form-label">
    	<label for="country"><?php _e('Country','escortwp'); ?> <i>*</i></label>
    </div>
	<div class="form-input">
		<?php
		global $taxonomy_location_url;
		$args = array(
			'show_option_all'    => '',
			'show_option_none'   => __('Select country','escortwp'),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_last_update'   => 0,
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'exclude'            => '',
			'echo'               => 1,
			'selected'           => $country,
			'hierarchical'       => 1, 
			'name'               => 'country',
			'id'                 => '',
			'class'              => 'country',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => $taxonomy_location_url );
		wp_dropdown_categories( $args );
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
						'class'              => 'state',
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
					'class'              => 'city',
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
    	<label><?php _e('Gender','escortwp'); ?> <i>*</i></label>
    </div>
	<div class="form-input">
		<?php
		foreach($gender_a as $key => $g) {
			if(in_array($key, $settings_theme_genders)) {
		?>
		<label for="gender<?php echo $key ?>">
			<input type="radio" name="gender" value="<?php echo $key; ?>" id="gender<?php echo $key ?>"<?php if($gender == $key) { echo ' checked'; } ?> />
			<?php echo $g; ?>
		</label>
		<?php
			} // if in_array
		} // foreach
		?>
    </div> <!-- gender --> <div class="formseparator"></div>
    <?php if (!$search_page) { ?>

    <div class="form-label">
    	<label for="aboutyou"><?php printf(esc_html__('About the %s','escortwp'),$taxonomy_profile_name); ?> <i>*</i></label>
    </div>
	<div class="form-input">
	    <textarea name="aboutyou" id="aboutyou" class="textarea longtextarea" rows="7"><?php echo $aboutyou; ?></textarea>
	    <small><?php _e('html code will be removed','escortwp'); ?></small>
	</div> <!-- about --> <div class="formseparator"></div>
    <?php } ?>

	<div class="clear"></div>
    <div class="text-center"><input type="submit" name="submit" value="<?php if ($escort_post_id) { printf(esc_html__('Update %s','escortwp'),$taxonomy_profile_name); } else { if ($search_page) { _e('Search','escortwp'); } else { printf(esc_html__('Add %s','escortwp'),$taxonomy_profile_name); } } ?>" class="pinkbutton rad3" /></div> <!--center-->
</form>