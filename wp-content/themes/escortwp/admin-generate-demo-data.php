<?php
/*
Template Name: Generate demo data
*/

$current_user = wp_get_current_user();
if (!current_user_can('level_10')) { wp_redirect(get_bloginfo("url")); exit; }
if(!get_option('generate_demo_data_alert')) { update_option('generate_demo_data_alert', 'hide'); }
set_time_limit(0);

// generate demo data
$err = ""; $ok = "";
if (isset($_POST['action']) && $_POST['action'] == 'demodata') {
    $profiles = (int)$_POST['profiles']; // nr of independent profiles
    if($profiles > 0) {
    	$profiles_gender = $_POST['profiles_gender'];
    	if(!$profiles_gender) {
    		$profiles_gender = $settings_theme_genders;
    	} else {
	    	foreach ($profiles_gender as $key=>$gender) {
	    		if(!in_array($gender, $settings_theme_genders)) {
	    			unset($profiles_gender[$key]);
	    		}
	    	}
    	}

		for ($i=1; $i <= $profiles; $i++) {
			// leave the id of the profile in a variable. it will be useful later on when we'll add the reviews and tours to go with the id
			$profile_id = generate_random_profile(generate_random_user('1'), $settings_theme_genders[array_rand($profiles_gender)]);
			$rev_count = rand(1,5);
			for ($i2=1; $i2 <= $rev_count; $i2++) {
				generate_random_review($profile_id);
			}
			// if($profiles_tours > 0) {
			// 	generate_random_tour($profile_id);
			// }
		}
		$ok .= "<span class='icon-ok'></span> <b>".$profiles."</b> ".sprintf(esc_html__('independent %s profiles have been created.','escortwp'),$taxonomy_profile_name)."<br />";
    }


    $agencies = (int)$_POST['agencies']; //nr of agencies
    $agency_profiles = (int)$_POST['agency_profiles']; //nr of profiles for each agency
    if($agency_profiles > 0) {
    	$agency_profiles_gender = $_POST['agency_profiles_gender'];
    	if(!$agency_profiles_gender) {
    		$agency_profiles_gender = $settings_theme_genders;
    	} else {
	    	foreach ($agency_profiles_gender as $key=>$agency_gender) {
	    		if(!in_array($agency_gender, $settings_theme_genders)) {
	    			unset($agency_profiles_gender[$key]);
	    		}
	    	}
    	}
    } // if $agency_profiles > 0

    if($agencies > 0) {
		for ($i=1; $i <= $agencies; $i++) {
			$agency_user_id = generate_random_user('2');
			generate_random_agency($agency_user_id);
			if($agency_profiles > 0) {
				for ($i2=1; $i2 <= $agency_profiles; $i2++) {
					generate_random_profile($agency_user_id, $settings_theme_genders[array_rand($agency_profiles_gender)]);
				}
			}
		}
		$ok .= "<span class='icon-ok'></span> <b>".$agencies."</b> ".sprintf(esc_html__('%s profiles have been created.','escortwp'),$taxonomy_agency_name)."<br />";
		if($agency_profiles > 0) {
			$ok .= "\t"."<span class='icon-ok'></span> <b>".$agency_profiles."</b> ".sprintf(esc_html__('%1$s profiles have been created for each %2$s profile','escortwp'),$taxonomy_profile_name,$taxonomy_agency_name)."<br />";
		}
    }

    if(($profiles + $agencies) == 0) {
    	$err = __('Nothing to generate','escortwp');
    }
    flush_rewrite_rules();
}
// delete previously generated demo data
if (isset($_POST['action']) && $_POST['action'] == 'deletedemodata') {
	global $taxonomy_location_url;
	// delete independent profiles
	$independent_profiles = array(
		'post_type' => $taxonomy_profile_url, 'posts_per_page' => '-1',
		'meta_query' => array( array( 'key' => 'randomly_generated_data', 'value' => 'randomly_generated_data', 'compare' => '=' ) )
	);
	$independent_profiles = new WP_Query($independent_profiles);
	if ( $independent_profiles->have_posts() ) :
		while ( $independent_profiles->have_posts() ) : $independent_profiles->the_post();
			delete_profile(get_the_ID());
		endwhile;
	endif;
	wp_reset_query();
	$ok .= "<span class='icon-ok'></span> <b>$independent_profiles->found_posts</b> ".sprintf(esc_html__('%s profiles have been deleted.','escortwp'),$taxonomy_profile_name)."<br />";

	// delete agency profiles
	$agency_profiles = array(
		'post_type' => $taxonomy_agency_url, 'posts_per_page' => '-1',
		'meta_query' => array( array( 'key' => 'randomly_generated_data', 'value' => 'randomly_generated_data', 'compare' => '=' ) )
	);
	$agency_profiles = new WP_Query($agency_profiles);
	if ( $agency_profiles->have_posts() ) :
		while ( $agency_profiles->have_posts() ) : $agency_profiles->the_post();
			delete_agency(get_the_ID());
		endwhile;
	endif;
	wp_reset_query();
	$ok .= "<span class='icon-ok'></span> <b>$agency_profiles->found_posts</b> ".sprintf(esc_html__('%s profiles have been deleted.','escortwp'),$taxonomy_agency_name)."<br />";

	// delete all generated users
	$user_args = array(
		'role'         => 'subscriber',
		'meta_key'     => 'randomly_generated_data',
		'meta_value'   => 'randomly_generated_data',
		'meta_compare' => '=',
		'fields'       => 'ID'
	);
	$user_list = get_users($user_args);
	foreach ($user_list as $key => $user) {
		include_once(ABSPATH."wp-admin/includes/user.php");
		wp_delete_user($user);
	}


	// delete all generated locations
	global $taxonomy_location_url;
	$get_locations_args = array (
	            'taxonomy' => $taxonomy_location_url,
	            'orderby' => 'ID',
	            'order' => 'DESC',
	            'hide_empty' => false,
	            'fields' => 'ids',
	            'hierarchical' => false,
	            'description__like' => 'randomly_generated_data',
	    );
	$locations = get_terms($get_locations_args);
	foreach ($locations as $key => $term_id) {
		wp_delete_term($term_id, $taxonomy_location_url);
	}

} // if ($_POST['action'] == 'deletedemodata') {


get_header(); ?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#profiles').change(function() { profiles(); });
			profiles();
			function profiles(){
				var profiles_value = $('#profiles').val();
				if (profiles_value > 0) {
					if (!$('.extra_data_profiles').is(":visible")) { $('.extra_data_profiles').slideDown(); }
				} else {
					if ($('.extra_data_profiles').is(":visible")) { $('.extra_data_profiles').slideUp(); }
				}
			}

			$('#agencies').change(function() { agencies(); });
			agencies();
			function agencies(){
				var agencies_value = $('#agencies').val();
				if (agencies_value > 0) {
					if (!$('.extra_data_agencies').is(":visible")) { $('.extra_data_agencies').slideDown(); }
				} else {
					if ($('.extra_data_agencies').is(":visible")) { $('.extra_data_agencies').slideUp(); }
				}
			}

			$('#agency_profiles').change(function() { agency_profiles(); });
			agency_profiles();
			function agency_profiles(){
				var agency_profiles_value = $('#agency_profiles').val();
				if (agency_profiles_value > 0) {
					if (!$('.extra_data_agency_profiles').is(":visible")) { $('.extra_data_agency_profiles').slideDown(); }
				} else {
					if ($('.extra_data_agency_profiles').is(":visible")) { $('.extra_data_agency_profiles').slideUp(); }
				}
			}

			$("#form_generate_data").submit(function() {
				loader('.submit_button_container');
				$('.submit_button_container').append('<br /><?php _e('Please wait','escortwp'); ?>...');
			});

			$("#form_delete_generated_data").submit(function() {
				loader('.delete_demo_data_button_container');
				$('.delete_demo_data_button_container').append('<br /><?php _e('Please wait','escortwp'); ?>...');
			});
		});
	</script>
	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox site-settings-page">
			<h3 class="settingspagetitle"><?php _e('Generate demo data','escortwp'); ?></h3>
            <div class="clear"></div>
			<?php if ($err) { echo "<div class='err rad25'>$err</div>"; } ?>
			<?php if ($ok) { echo "<div class='ok2 rad3'>$ok</div><div class='clear20'></div>"; } ?>
			<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="form-styling" id="form_generate_data">
				<input type="hidden" name="action" value="demodata" />

				<div class="form-label">
					<label for="profiles"><?php printf(esc_html__('How many independent %s profiles do you want to generate?','escortwp'),$taxonomy_profile_name); ?></label>
                </div>
				<div class="form-input">
					<select name="profiles" id="profiles">
						<option value="">0</option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="5">5</option>
						<option value="10">10</option>
						<option value="20">20</option>
						<option value="30">30</option>
					</select>
					<div class="extra_data_profiles hide">
						<div class="clear10"></div>
						<?php _e('What gender should the profiles have?','escortwp'); ?>
						<div class="clear10"></div>
						<?php
						foreach ($gender_a as $key=>$value) {
							if(in_array($key, $settings_theme_genders)) {
								if(!$profiles_gender) {
									$checked = 'checked="checked" ';
								} elseif(in_array($key, $profiles_gender)) {
									$checked = 'checked="checked" ';
								}
							} else {
								 $disabled = 'disabled ';
								 $disabled_message = ' <small>'.__('You choose not to use this gender in the website','escortwp').'</small>';
							}
							
						    echo '<label for="gender_'.strtolower($value).'">
						    		<input type="checkbox" name="profiles_gender[]" id="gender_'.strtolower($value).'" value="'.$key.'" '.$checked.$disabled.'/> 
						    		'.$gender_a[$key].$disabled_message.'
						    	</label>
						    	<div class="clear5"></div>
						    	'."\n";
						    unset($checked, $disabled, $disabled_message);
						}
						?>
						<div class="clear10"></div>
					</div> <!-- extra data profiles -->
				</div> <!-- profiles --> <div class="formseparator"></div>


				<div class="form-label">
					<label for="agencies"><?php printf(esc_html__('How many %s profiles do you want to generate?','escortwp'),$taxonomy_agency_name); ?></label>
                </div>
				<div class="form-input">
					<select name="agencies" id="agencies">
						<option value="">0</option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="5">5</option>
						<option value="10">10</option>
						<option value="15">15</option>
						<option value="20">20</option>
						<option value="30">30</option>
					</select>

					<div class="extra_data_agencies hide">
						<div class="clear10"></div>
						<?php printf(esc_html__('Generate extra data for each %s profile','escortwp'),$taxonomy_agency_name); ?>:
						<div class="clear10"></div>
						<div class="form-label">
							<label for="agency_profiles"><?php echo ucfirst($taxonomy_profile_name)." ".__('profiles','escortwp'); ?>:</label>
	                    </div>
						<div class="form-input">
							<select name="agency_profiles" id="agency_profiles">
								<option value="">0</option>
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="5">5</option>
								<option value="10">10</option>
								<option value="15">15</option>
							</select>
						</div> <!-- agency_profiles --> <div class="formseparator"></div>
						<div class="extra_data_agency_profiles hide">

							<?php _e('What gender should the profiles have?','escortwp'); ?>
							<div class="clear10"></div>
							<?php
							foreach ($gender_a as $key=>$value) {
								if(in_array($key, $settings_theme_genders)) {
									if(!$agency_profiles_gender) {
										$checked = 'checked="checked" ';
									} elseif(in_array($key, $agency_profiles_gender)) {
										$checked = 'checked="checked" ';
									}
								} else {
									 $disabled = 'disabled ';
									 $disabled_message = ' <small>'.__('You choose not to use this gender in the website','escortwp').'</small>';
								}
								
							    echo '<label for="agency_gender_'.strtolower($value).'">
							    		<input type="checkbox" name="agency_profiles_gender[]" id="agency_gender_'.strtolower($value).'" value="'.$key.'" '.$checked.$disabled.'/> 
							    		'.$gender_a[$key].$disabled_message.'
							    	</label>
							    	<div class="clear5"></div>
							    	'."\n";
							    unset($checked, $disabled, $disabled_message);
							}
							?>
							<div class="clear10"></div>
						</div> <!-- extra data profiles -->
					</div> <!-- extra data profiles -->
				</div> <!-- agencies --> <div class="formseparator"></div>

				<div class="submit_button_container text-center"><input type="submit" name="submit" value="<?php _e('Generate demo data','escortwp'); ?>" class="submit_button pinkbutton rad3" /></div>
			</form>

			<div class="clear30"></div>
			<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="form-styling text-center" id="form_delete_generated_data">
				<input type="hidden" name="action" value="deletedemodata" />
				<div class="clear50"></div>
				<div class="delete_demo_data_button_container">
					<input type="submit" name="submit" value="<?php _e('Delete all generated data','escortwp'); ?>" class="submit_button redbutton rad3" /><br />
					<small><?php _e('Use this button when you are ready to use the site.<br />This will delete all profiles that were generated with the help of this page.<br />This will not delete the profiles that you added yourself or profiles created by users when signing up.','escortwp'); ?></small>
				</div>
			</form>
			<div class="clear"></div>
		</div> <!-- BODY BOX -->
	</div> <!-- BODY -->
    </div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>

<?php get_footer(); ?>