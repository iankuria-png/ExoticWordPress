<?php
/*
Template Name: Search page
*/
global $taxonomy_location_url;
if (isset($_POST['action']) && $_POST['action'] == 'search') {
	$meta_query = array();
	
	if ($_POST['paged']) {
		$paged = (int)$_POST['paged'];
	} else {
		$paged = 1;
	}

	if (isset($_POST['previous'])) {
		$paged = $paged - 1;
	}
	if (isset($_POST['next'])) {
		$paged = $paged + 1;
	}

	if ($_POST['yourname']) {
    	$yourname = substr(wp_strip_all_tags($_POST['yourname'], true), 0, 200);
		$meta_query[] = array(
			'key' => 'yourname',
			'value' => $yourname
		);
	}

	if ($_POST['country'] && $_POST['country'] > 0) {
		$country = (int)$_POST['country'];
		$city_parent = $country;
		if (!term_exists( $country, $taxonomy_location_url )) {
			unset($country, $city);
			if(showfield('state')) {
				unset($state);
			}
		} else {
			$meta_query[] = array(
				'key' => 'country',
				'value' => $country,
				'compare' => '='
			);

			if(showfield('state')) {
				if ($_POST['state'] && $_POST['state'] > 0) {
					$state = (int)$_POST['state'];
					if (!term_exists( $state, $taxonomy_location_url, $country )) {
						$err .= __('The state you selected doesn\'t exist in our database','escortwp')."<br />"; unset($state);
					} else {
						$meta_query[] = array(
							'key' => 'state',
							'value' => $state,
							'compare' => '='
						);
						$city_parent = $state;
					}
				} else {
					unset($state);
				} // if post[state]
			} // if showfield('state')


			if ($_POST['city'] && $_POST['city'] > 0) {
				$city = (int)$_POST['city'];
				if (!term_exists( $city, $taxonomy_location_url, $city_parent )) {
					$err .= __('The city you selected doesn\'t exist in our database','escortwp')."<br />"; unset($city);
				} else {
					$meta_query[] = array(
						'key' => 'city',
						'value' => $city,
						'compare' => '='
					);
				}
			} else {
				unset($city);
			} // if post[city]
		} // if term exists country
	} else {
		unset($country);
	}

	$independent = $_POST['independent'];
	if ($independent == "1") {
		$meta_query[] = array(
			'key' => 'independent',
			'value' => "yes",
			'compare' => '='
		);
	}

	$premium = (int)$_POST['premium'];
	if ($premium == "1") {
		$meta_query[] = $array = array(
			'key' => 'premium',
			'value' => "1",
			'compare' => '='
		);
	}

	$verified = (int)$_POST['verified'];
	if ($verified == "1") {
		$meta_query[] = $array = array(
			'key' => 'verified',
			'value' => "1",
			'compare' => '='
		);
	}

	if ($_POST['gender']) {
		$gender = (int)$_POST['gender'];
		$meta_query[] = array(
			'key' => 'gender',
			'value' => $gender,
			'compare' => '='
		);
	}

	if ($_POST['age']) {
	    $age = (int)$_POST['age'];
		if ($age && $age > 17) {
			$meta_query[] = array(
				'key' => 'age',
				'value' => $age,
				'compare' => '='
			);
		} else {
			unset($age);
		}
	}

	if ($_POST['ethnicity']) {
	    $ethnicity = (int)$_POST['ethnicity'];
		$ethnicity = (string)$_POST['ethnicity'];
		$meta_query[] = array(
			'key' => 'ethnicity',
			'value' => $ethnicity,
			'compare' => '='
		);
	}

	if ($_POST['haircolor']) {
	    $haircolor = (int)$_POST['haircolor'];
		$meta_query[] = array(
			'key' => 'haircolor',
			'value' => $haircolor,
			'compare' => '='
		);
	}

	if ($_POST['hairlength']) {
	    $hairlength = (int)$_POST['hairlength'];
		$meta_query[] = array(
			'key' => 'hairlength',
			'value' => $hairlength,
			'compare' => '='
		);
	}

	if ($_POST['bustsize']) {
	    $bustsize = (int)$_POST['bustsize'];
		$meta_query[] = array(
			'key' => 'bustsize',
			'value' => $bustsize,
			'compare' => '='
		);
	}

	if ($_POST['height']) {
	    $height = (int)$_POST['height'];
		$meta_query[] = array(
			'key' => 'height',
			'value' => $height,
			'compare' => '='
		);
	}

	if ($_POST['weight']) {
	    $weight = (int)$_POST['weight'];
		$meta_query[] = array(
			'key' => 'weight',
			'value' => $weight,
			'compare' => '='
		);
	}

	if ($_POST['build']) {
	    $build = (int)$_POST['build'];
		$meta_query[] = array(
			'key' => 'build',
			'value' => $build,
			'compare' => '='
		);
	}

	if ($_POST['looks']) {
	    $looks = (int)$_POST['looks'];
		$meta_query[] = array(
			'key' => 'looks',
			'value' => $looks,
			'compare' => '='
		);
	}

	if ($_POST['availability'] && is_array($_POST['availability'])) {
	    $availability = $_POST['availability'];
		foreach($availability as $a) {
			$a = (int)$a;
			$meta_query[] = array(
				'key' => 'availability',
				'value' => '%"'.$a.'"%',
				'compare' => 'LIKE'
			);
		}
	}

	if ($_POST['smoker']) {
	    $smoker = (int)$_POST['smoker'];
		$meta_query[] = array(
			'key' => 'smoker',
			'value' => $smoker,
			'compare' => '='
		);
	}

	if ($_POST['low'] && $_POST['high']) {
	    $low = (int)$_POST['low'];
	    $high = (int)$_POST['high'];
		$meta_query[] = array(
			'key' => 'rate1h_incall',
			'value' => "$low AND `meta_value` <= $high",
			'compare' => '>='
		);
	} else {
		if ($_POST['low']) {
	    	$low = (int)$_POST['low'];
			$meta_query[] = array(
				'key' => 'rate1h_incall',
				'value' => $low,
				'compare' => '>='
			);
		}

		if ($_POST['high']) {
	    	$high = (int)$_POST['high'];
			$meta_query[] = array(
				'key' => 'rate1h_incall',
				'value' => $high,
				'compare' => '<='
			);
		}
	}

    $services = $_POST['services'];
	if ($services && is_array($services)) {
		foreach ($services as $i => $service) {
			$service = (int)$service;
			$meta_query[] = array(
				'key' => 'services',
				'value' => '%"'.$service.'"%',
				'compare' => 'LIKE'
			);
		}
	}
} // if isset

get_header(); ?>

		<div class="contentwrapper">
		<div class="body">
        	<div class="bodybox">
				<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="form-styling">
            	<h3 class="l"><?php printf(esc_html__('Search for %s','escortwp'),$taxonomy_profile_name_plural); ?></h3>
                <div class="pinkbutton rad25 r filtersearch"<?php if (!isset($_POST['action'])) { echo ' style="display: none;"'; }?>><?php _e('Filter search','escortwp'); ?></div>
                <div class="clear30"></div>
				<script type="text/javascript">
				jQuery(document).ready(function($) {
					//get cities from the selected country in the countries dropdown
					var c = ".country";
					var parent_div = ".searchform";
					<?php if(showfield('state')) { ?>
						var city_div = '.inputstates';

						var state_c = '.state';
						var state_div = '.inputcities';
					<?php } else { ?>
						var city_div = '.inputcities';
					<?php } ?>

					// if(country > 0) { show_search_cities(c); }
					$(c).change(function(){ show_search_cities(c); });
					function show_search_cities(e) {
						var country = $(e).val();
						$(city_div).text($(city_div).data('text'));
						<?php if(showfield('state')) { ?>
							$(state_div).text($(state_div).data('text'));
						<?php } ?>

						if(country < 1) return true;

						loader($(e).parents(parent_div).find(city_div));
						$.ajax({
							type: "GET",
							url: "<?php bloginfo('template_url'); ?>/ajax/get-cities.php",
							<?php if(showfield('state')) { ?>
								data: "id=" + country +"&selected=<?php echo $city ?>&hide_empty=1&state=yes&select2=yes",
							<?php } else { ?>
								data: "id=" + country +"&selected=<?php echo $city ?>&hide_empty=1&select2=yes",
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
							var state = $(e).val();
							$(state_div).text($(state_div).data('text'));
							if(state < 1) {
								return true;
							}

							loader($(e).parents(parent_div).find(state_div));
							$.ajax({
								type: "GET",
								url: "<?php bloginfo('template_url'); ?>/ajax/get-cities.php",
								data: "id=" + state +"&selected=<?php echo $city ?>&hide_empty=1&select2=yes",
								success: function(data){
									$(e).parents(parent_div).find(state_div).html(data + '<div class="formseparator"><'+'/div>');
									if($(window).width() > "960") { $('.select2').select2(); }
								}
							});
						}
					<?php } // if showfield('state') ?>

					$('.filtersearch').on('click', function(){
						var text = $('.filtersearch').text();
						$('.searchform, .search-results-wrapper').slideToggle();
						$(this).text(text == '<?=__('Filter search','escortwp')?>' ? '<?=__('Back to results','escortwp')?>' : '<?=__('Filter search','escortwp')?>');
					});
				});
				</script>

				<div class="searchform registerform"<?php if (isset($_POST['action']) && $_POST['action'] == 'search') { echo ' style="display: none;"'; }?>>
					<input type="hidden" name="action" value="search" />
				    <input type="hidden" name="paged" value="<?php echo $paged; ?>" />

					<?php if(insearch('yourname')) { ?>
					<div class="form-label">
				    	<label for="yourname"><?php printf(esc_html__('%s Name','escortwp'),ucwords($taxonomy_profile_name)); ?></label>
				    </div>
					<div class="form-input">
				    	<input type="text" name="yourname" id="yourname" class="input" value="<?php echo htmlspecialchars(stripslashes($yourname)); ?>" />
				    </div> <!-- name --> <div class="formseparator"></div>
					<?php } ?>

					<?php if(insearch('country')) { ?>
					<div class="form-label">
				    	<label for="country"><?php _e('Country','escortwp'); ?></label>
				    </div>
					<div class="form-input">
						<?php
						$args = array(
							'show_option_all'    => '',
							'show_option_none'   => __('Select country','escortwp'),
							'orderby'            => 'name', 
							'order'              => 'ASC',
							'show_last_update'   => 0,
							'show_count'         => 0,
							'hide_empty'         => 1,
							'exclude'            => '',
							'echo'               => 1,
							'selected'           => $country,
							'hierarchical'       => 1, 
							'name'               => 'country',
							'id'                 => '',
							'class'              => 'country select2',
							'depth'              => 1,
							'tab_index'          => 0,
							'taxonomy'           => $taxonomy_location_url );


						$categories_count_array = array(
							'show_option_all' => '',
							'show_count' => '0',
							'hide_empty' => '1',
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
							<script type="text/javascript"> jQuery(document).ready(function($) { $('.searchform .country').trigger('change'); }); </script>
							<?php
						} else {
							wp_dropdown_categories( $args );
						}
						$city_parent = $country;
						?>
				    </div> <!-- country --> <div class="formseparator"></div>
				    <?php } // insearch ?>

					<?php if(insearch('country') && showfield('state')) { ?>
					<div class="form-label">
						<label for="state"><?php _e('State','escortwp'); ?></label>
					</div>
					<div class="form-input inputstates" data-text="<?=__('Please select a country first','escortwp')?>">
						<?php
						if($_POST['country'] > 0) {
							$args = array(
								'show_option_all'    => '',
								'show_option_none'   => __('Select State','escortwp'),
								'show_last_update'   => 0,
								'show_count'         => 0,
								'parent'			 => $country,
								'hide_empty'         => 1,
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
						$city_parent = $state;
						?>
					</div> <!-- state --> <div class="formseparator"></div>
					<?php } // insearch ?>

					<?php
					if(insearch('country') && insearch('city')) {
						if(showfield('state')) {
							$city_text = __('Please select a state first','escortwp');
						} else {
							$city_text = __('Please select a country first','escortwp');
						}
					?>
					<div class="form-label">
						<label for="city"><?php _e('City','escortwp'); ?></label>
					</div>
					<div class="form-input inputcities" data-text="<?=$city_text?>">
						<?php
						if(($_POST['country'] > 0 && !showfield('state')) || ($_POST['state'] > 0 && showfield('state'))) {
							$args = array(
								'show_option_all'    => '',
								'show_option_none'   => __('Select City','escortwp'),
								'show_last_update'   => 0,
								'show_count'         => 0,
								'parent'			 => $city_parent,
								'hide_empty'         => 1,
								'exclude'            => '',
								'echo'               => 1,
								'selected'           => $city,
								'hierarchical'       => 1, 
								'name'               => 'city',
								'id'                 => '',
								'class'              => 'city',
								'depth'              => 1,
								'tab_index'          => 0,
								'taxonomy'           => $taxonomy_location_url );
							wp_dropdown_categories( $args );
						} else {
							echo $city_text;
						}
						?>
					</div> <!-- city --> <div class="formseparator"></div>
					<?php } // insearch ?>

					<div class="form-label">
				    	<label><?php printf(esc_html__('Only show independent %s?','escortwp'),$taxonomy_profile_name_plural); ?></label>
				    </div>
					<div class="form-input">
					    <label for="independent">
					    	<input type="checkbox" name="independent" value="yes" id="independent"<?php if($independent == "yes") { echo ' checked'; } ?> />
					    	<?php _e('Yes','escortwp'); ?>
					    </label>
				    </div> <!-- independent --> <div class="formseparator"></div>

				    <div class="form-label">
				    	<label><?php printf(esc_html__('Only show premium %s?','escortwp'),$taxonomy_profile_name_plural); ?></label>
				    </div>
					<div class="form-input">
						<label for="premium">
					    	<input type="checkbox" name="premium" value="1" id="premium"<?php if($premium == "1") { echo ' checked'; } ?> />
					    	<?php _e('Yes','escortwp'); ?>
					    </label>
				    </div> <!-- premium --> <div class="formseparator"></div>

				    <div class="form-label">
				    	<label><?php printf(esc_html__('Only show verified %s?','escortwp'),$taxonomy_profile_name_plural); ?></label>
				    </div>
					<div class="form-input">
						<label for="verified">
					    	<input type="checkbox" name="verified" value="1" id="verified"<?php if($verified == "1") { echo ' checked'; } ?> />
					    	<?php _e('Yes','escortwp'); ?>
					    </label>
				    </div> <!-- verified --> <div class="formseparator"></div>

					<?php if(insearch('gender')) { ?>
					<div class="form-label">
				    	<label><?php _e('Gender','escortwp'); ?></label>
				    </div>
					<div class="form-input">
						<?php
						foreach($gender_a as $key=>$g) {
							if(in_array($key, $settings_theme_genders)) {
						?>
						<label for="gender<?php echo $key ?>">
							<input type="radio" name="gender" value="<?php echo $key; ?>" id="gender<?php echo $key ?>"<?php if($gender == $key) { echo ' checked'; } ?> />
							<?php _e($g,'escortwp'); ?>
						</label>
						<?php
							} // if in_array
						} // foreach
						?>
				    </div> <!-- gender --> <div class="formseparator"></div>
				    <?php } // insearch ?>

					<?php /* ?>
				    <label for="age">Age</label><input type="text" name="age" id="age" class="input" value="<?php echo $age; ?>" /><br />
					<?php */ ?>

					<?php if(insearch('ethnicity')) { ?>
					<div class="form-label">
				    	<label for="ethnicity"><?php _e('Ethnicity','escortwp'); ?></label>
				    </div>
					<div class="form-input">
					    <select name="ethnicity" id="ethnicity" class="ethnicity">
							<option value=""><?php _e('Select','escortwp'); ?></option>
						    <?php foreach($ethnicity_a as $key=>$s) { ?>
								<option value="<?php echo $key; ?>"<?php if($ethnicity == $key) { echo ' selected="selected"'; } ?>><?php _e($s,'escortwp'); ?></option>
							<?php } ?>
						</select>
					</div> <!-- skin color --> <div class="formseparator"></div>
					<?php } // insearch ?>

					<?php if(insearch('haircolor')) { ?>
					<div class="form-label">
				    	<label for="haircolor"><?php _e('Hair Color','escortwp'); ?></label>
				    </div>
					<div class="form-input">
						<select name="haircolor" id="haircolor" class="haircolor">
							<option value=""><?php _e('Select','escortwp'); ?></option>
						    <?php foreach($haircolor_a as $key=>$h) { ?>
								<option value="<?php echo $key; ?>"<?php if($haircolor == $key) { echo ' selected="selected"'; } ?>><?php _e($h,'escortwp'); ?></option>
							<?php } ?>
						</select>
				    </div> <!-- hair color --> <div class="formseparator"></div>
					<?php } // insearch ?>

					<?php if(insearch('hairlength')) { ?>
					<div class="form-label">
				    	<label for="hairlength"><?php _e('Hair length','escortwp'); ?></label>
				    </div>
					<div class="form-input">
						<select name="hairlength" id="hairlength" class="hairlength">
							<option value=""><?php _e('Select','escortwp'); ?></option>
						    <?php foreach($hairlength_a as $key=>$h) { ?>
								<option value="<?php echo $key; ?>"<?php if($hairlength == $key) { echo ' selected="selected"'; } ?>><?php _e($h,'escortwp'); ?></option>
							<?php } ?>
						</select>
				    </div> <!-- hair length --> <div class="formseparator"></div>
					<?php } // insearch ?>

					<?php if(insearch('bustsize')) { ?>
					<div class="form-label">
				    	<label for="bustsize"><?php _e('Bust size','escortwp'); ?></label>
				    </div>
					<div class="form-input">
						<select name="bustsize" id="bustsize" class="bustsize">
							<option value=""><?php _e('Select','escortwp'); ?></option>
						    <?php foreach($bustsize_a as $key=>$b) { ?>
								<option value="<?php echo $key; ?>"<?php if($bustsize == $key) { echo ' selected="selected"'; } ?>><?php _e($b,'escortwp'); ?></option>
							<?php } ?>
						</select>
				    </div> <!-- bist size --> <div class="formseparator"></div>
					<?php } // insearch ?>

					<?php if(insearch('height')) { ?>
					<div class="form-label">
				    	<label for="height"><?php _e('Height','escortwp'); ?></label>
				    </div>
					<div class="form-input">
						<input type="text" name="height" size="4" id="height" class="input smallinput text-center" value="<?php echo $height; ?>" /> &nbsp; cm
				    </div> <!-- height --> <div class="formseparator"></div>
					<?php } // insearch ?>

					<?php if(insearch('weight')) { ?>
					<div class="form-label">
				    	<label for="weight"><?php _e('Weight','escortwp'); ?></label>
				    </div>
					<div class="form-input">
						<input type="text" name="weight" size="4" id="weight" class="input smallinput text-center" value="<?php echo $weight; ?>" /> &nbsp; kg
				    </div> <!-- weight --> <div class="formseparator"></div>
					<?php } // insearch ?>

					<?php if(insearch('build')) { ?>
					<div class="form-label">
				    	<label for="build"><?php _e('Build','escortwp'); ?></label>
				    </div>
					<div class="form-input">
						<select name="build" id="build" class="build">
							<option value=""><?php _e('Select','escortwp'); ?></option>
						    <?php foreach($build_a as $key=>$b) { ?>
								<option value="<?php echo $key; ?>"<?php if($build == $key) { echo ' selected="selected"'; } ?>><?php _e($b,'escortwp'); ?></option>
							<?php } ?>
						</select>
				    </div> <!-- build --> <div class="formseparator"></div>
					<?php } // insearch ?>

					<?php if(insearch('looks')) { ?>
					<div class="form-label">
				    	<label for="looks"><?php _e('Looks','escortwp'); ?></label>
				    </div>
					<div class="form-input">
						<select name="looks" id="looks" class="looks">
							<option value=""><?php _e('Select','escortwp'); ?></option>
						    <?php foreach($looks_a as $key=>$l) { ?>
								<option value="<?php echo $key; ?>"<?php if($looks == $key) { echo ' selected="selected"'; } ?>><?php _e($l,'escortwp'); ?></option>
							<?php } ?>
						</select>
				    </div> <!-- looks --> <div class="formseparator"></div>
					<?php } // insearch ?>

					<?php if(insearch('availability')) { ?>
					<?php if(!$availability) $availability = array(); ?>
					<div class="form-label">
				    	<label><?php _e('Availability','escortwp'); ?></label>
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
				    </div> <!-- availability --> <div class="formseparator"></div>
					<?php } // insearch ?>

					<?php if(insearch('smoker')) { ?>
					<div class="form-label">
				    	<label><?php _e('Smoker','escortwp'); ?></label>
				    </div>
					<div class="form-input">
						<label for="smokeyes">
						    <input type="radio" name="smoker" value="1" id="smokeyes"<?php if($smoker == "1") { echo ' checked'; } ?> />
						    <?php _e('Yes','escortwp'); ?>
						</label>
						<label for="smokeno">
				    		<input type="radio" name="smoker" value="2" id="smokeno"<?php if($smoker == "2") { echo ' checked'; } ?> />
				    		<?php _e('No','escortwp'); ?>
				    	</label>
				    </div> <!-- smoker --> <div class="formseparator"></div>
					<?php } // insearch ?>

					<?php if(insearch('rates')) { ?>
					<div class="form-label">
				    	<label><?php _e('Rates','escortwp'); ?></label>
				    </div>
					<div class="form-input">
						<div class="col40 l">
				    		<?php _e('Between','escortwp'); ?>
				    	</div>
				    	<div class="col60 l">
				    		<input type="text" name="low" value="<?php echo $low; ?>" class="input" />
				    	</div>
				    	<div class="clear10 "></div>

				    	<div class="col40 l">
				    		<?php _e('and','escortwp'); ?>
				    	</div>
				    	<div class="col60 l">
				    		<input type="text" name="high" value="<?php echo $high; ?>" class="input" />
				    	</div>
				    </div> <!-- rates --> <div class="formseparator"></div>
					<?php } // insearch ?>

					<?php if(insearch('services')) { ?>
					<?php if(!$services) $services = array(); ?>
					<div class="form-label">
				    	<label for="services"><?php _e('Services','escortwp'); ?></label>
				    </div>
					<div class="form-input">
						<?php foreach($services_a as $key=>$service) { ?>
							<div class="col50 l">
								<label for="service<?php echo $key; ?>">
									<input type="checkbox" name="services[]" value="<?php echo $key; ?>" id="service<?php echo $key; ?>"<?php if( in_array($key, $services) ) { echo ' checked'; } ?> />
									<?php _e($service,'escortwp'); ?>
								</label>
								<div class="clear5"></div>
							</div> <!-- one service -->
						<?php } ?>
				    </div> <!-- services --> <div class="formseparator"></div>
					<?php } // insearch ?>

					<div class="clear20"></div>
				    <div class="text-center"><input type="submit" name="submit" value="<?php printf(esc_html__('Search %s','escortwp'),$taxonomy_profile_name_plural); ?>" class="pinkbutton rad3" /></div> <!--center-->
				</div> <!-- SEARCH FORM -->

				<?php
				if (isset($_POST['action']) && $_POST['action'] == 'search') {
					//get post_id for all search criterias
					if (count($meta_query) > 0) {
						global $wpdb;
						foreach($meta_query as $one) {
							if($one['key'] == 'rate1h_incall') {
								$sql = "SELECT `post_id` FROM `".$wpdb->postmeta."` WHERE `meta_key` = '".$one['key']."' AND `meta_value` ".$one['compare']." ".$one['value'];
							} elseif ($one['key'] == 'yourname') {
								$sql = "SELECT `ID` FROM `".$wpdb->posts."` WHERE `post_type` = '".$taxonomy_profile_url."' AND `post_title` LIKE '%".$one['value']."%'";
							} else {
								$sql = "SELECT `post_id` FROM `".$wpdb->postmeta."` WHERE `meta_key` = '".$one['key']."' AND `meta_value` ".$one['compare']." '".$one['value']."'";
							}
							$cols = $wpdb->get_col($sql);
							if(count($cols) > 0) {
								$query[] = $wpdb->get_col($sql);
							}
						}
					}

					//get only post ids that have all search criteria
					$r = $query[0];
					foreach($query as $key=>$one) {
						$r = array_intersect($r, $query[$key]);
					}

				if (count($r) > 0) {
					global $wp_query;
					
					$posts_per_page = "30";

					$premium_args = array(
						'post_type' => $taxonomy_profile_url,
						'posts_per_page' => $posts_per_page,
						'orderby' => 'meta_value_num', 'meta_key' => 'premium_since',
						'paged' => $paged,
						'post__in' => $r,
						'meta_query' => array( array( 'key' => 'premium', 'value' => '1', 'compare' => '=', 'type' => 'NUMERIC' ) )
					);
					$premium = new WP_Query( $premium_args );
					$premium_found_posts = $premium->found_posts;


					//offset when we have premium posts
					if($paged > $premium->max_num_pages) {
						$normal_offset = $posts_per_page*($paged-1) - $premium->found_posts;
					}
					//offset when we don't have premium posts
					if($premium->found_posts < 1) { $normal_offset = $posts_per_page*$paged - $posts_per_page; }
					$normal_args = array(
						'offset' => $normal_offset,
						'post_type' => $taxonomy_profile_url,
						'posts_per_page' => $posts_per_page - count($premium->posts),
						'orderby' => 'date',
						'order' => 'DESC',
						'post__in' => $r,
						'meta_query' => array( array( 'key' => 'premium', 'value' => '0', 'compare' => '=', 'type' => 'NUMERIC' ) )
					);
					$normal = new WP_Query( $normal_args );
					$normal_found_posts = $normal->found_posts;

					$all = $premium;
					if(count($premium->posts) < $posts_per_page) {
						$q = array_merge($premium->posts, $normal->posts);
						$all->post_count = count($q);
						$all->posts = $q;
					}


					$number_of_posts = $premium->found_posts + $normal->found_posts;

					$nr_of_pages = ceil($number_of_posts / $posts_per_page);

					if ($paged != "1") {
						$pagination = '<input type="submit" name="previous" value="'.__('Previous page','escortwp').'" class="pinkbutton rad3 l" />';
					}

					if ($paged < $nr_of_pages) {
						$pagination .= '<input type="submit" name="next" value="'.__('Next page','escortwp').'" class="pinkbutton rad3 r" />';
					}

					$i = 1;
					if ( $all->have_posts() ) : while ( $all->have_posts() ) : $all->the_post();
						include (get_template_directory() . '/loop-show-profile.php');
					endwhile;
						echo '<div class="clear20"></div>';
						echo $pagination;
					else:
						printf(esc_html__('No %s found','escortwp'),$taxonomy_profile_name_plural);
					endif;
					wp_reset_query();
				} else { // if count($r) > 0
					printf(esc_html__('No %s found','escortwp'),$taxonomy_profile_name_plural);
				}
				} ?>
				</form>
                <div class="clear"></div>
            </div> <!-- BODY BOX -->
            <div class="clear"></div>
        </div> <!-- BODY -->
        </div> <!-- contentwrapper -->

		<?php get_sidebar("left"); ?>
		<?php get_sidebar("right"); ?>
    	<div class="clear"></div>
<?php get_footer(); ?>