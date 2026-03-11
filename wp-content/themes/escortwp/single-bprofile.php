<?php
	if (have_posts()) :
		while (have_posts()) : the_post();

			if (!is_user_logged_in()) { wp_redirect(get_bloginfo("url")); exit; }

			global $taxonomy_profile_name, $taxonomy_location_url, $taxonomy_agency_name, $taxonomy_agency_url;
			$current_user = wp_get_current_user();
			$userid = $current_user->ID;
			$userstatus = get_option("escortid".$userid);

			if (get_the_author_meta('ID') == $userid && $userstatus == $taxonomy_agency_url || current_user_can('level_10')) {
				//delete an escort account
				if (isset($_POST['action']) && $_POST['action'] == 'deleteescort') {
					$escortidtodelete = (int)$_POST['escortidtodelete'];
					delete_profile($escortidtodelete);
					wp_redirect(get_permalink(get_option('blacklisted_escorts_page_id'))); exit();
				}

				//if the agency wants to edit the profile information process the data below
				if (isset($_POST['action']) && $_POST['action'] == 'addescort') {
					$agencyid = $userid;
					$single_page = "yes";
					$escort_post_id = get_the_ID();
					include (get_template_directory() . '/blacklisted-escorts-personal-info-process.php');
				} else {
					$agencyid = $userid;
					$escort_post_id = get_the_ID();
					$single_page = "yes";
					$escort = get_post($escort_post_id);

					$aboutyou = $escort->post_content;
					$yourname = $escort->post_title;

					$phone = get_post_meta($escort_post_id, "phone", true);
					$escortemail = get_post_meta($escort_post_id, "email", true);

					$country = get_post_meta($escort_post_id, "country", true);
					if(get_option('locationdropdown') == "1") {
						$city = get_post_meta($escort_post_id, "city", true);
						if(showfield('state')) {
							$state = get_post_meta($escort_post_id, "state", true);
						}
					} else {
						$city = get_term(get_post_meta(get_the_ID(), "city", true), $taxonomy_location_url);
						$city = $city->name;

						if(showfield('state')) {
							$state = get_term(get_post_meta(get_the_ID(), "state", true), $taxonomy_location_url);
							$state = $state->name;
						}
					}


					$gender = get_post_meta($escort_post_id, "gender", true);
					$haircolor = get_post_meta($escort_post_id, "haircolor", true);
				}

			} // if the escort was added by this user and if the user is an agency


			get_header();
?>

			<div class="contentwrapper">
			<div class="body">
	        	<div class="bodybox">
					<?php
					if (get_the_author_meta('ID') == $userid && $userstatus == $taxonomy_agency_url || current_user_can('level_10')) {
						include (get_template_directory() . '/blacklisted-escorts-option-buttons.php');
					}
					?>
	                <div class="girlsingle single-blacklisted-escorts">
		            	<h3 class="l"><?php the_title(); ?></h3>
						<?php
						if (get_the_author_meta('ID') == $userid && $userstatus == $taxonomy_agency_url || current_user_can('level_10')) {
							 echo '<div class="r image-buttons-legend"><span class="button-delete icon-cancel" ></span>'.__('Delete image','escortwp').'</div>';
						}
						?>
						<div class="clear"></div>
	                	<div class="bigimage l">
							<?php
							$photos = get_children( array('post_parent' => get_the_ID(), 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID') );

							$main_image_id = get_post_meta(get_the_ID(), "main_image_id", true);
							if($main_image_id < 1 || !get_post($main_image_id)) {
								$firstphoto = reset($photos);
								if ($firstphoto) {
									$main_image_id = $firstphoto->ID;
									update_post_meta(get_the_ID(), "main_image_id", $main_image_id);
								}
							}

							$main_image_url = wp_get_attachment_image_src((int)$main_image_id, 'main-image-thumb');
							if($main_image_url[3] != "1") {
								require_once( ABSPATH . 'wp-admin/includes/image.php' );
								$attach_data = wp_generate_attachment_metadata($main_image_id, get_attached_file($main_image_id));
								wp_update_attachment_metadata($main_image_id, $attach_data);
								$main_image_url = wp_get_attachment_image_src($main_image_id, 'main-image-thumb');
							}
							if(!$main_image_url[0]) {
								$main_image_url[0] = get_template_directory_uri().'/i/no-image.png';
							}
							echo '<img src="'.$main_image_url[0].'" class="rad3 l" alt="'.get_the_title().'" />'."\n";
							?>
	                    </div> <!-- BIG IMAGE -->
	                    <div class="thumbs r">
							<?php
								//get the images uploaded
								$photos = (array)$photos;
								sort($photos);
								foreach ($photos as $photo) {
									$photo_th_url = wp_get_attachment_image_src($photo->ID, 'profile-thumb');
									if($photo_th_url[3] != "1") {
										require_once( ABSPATH . 'wp-admin/includes/image.php' );
										$attach_data = wp_generate_attachment_metadata($photo->ID, get_attached_file($photo->ID));
										wp_update_attachment_metadata($photo->ID, $attach_data);
										$photo_th_url = wp_get_attachment_image_src($photo->ID, 'profile-thumb');
									}

									$photo_th_mobile_url = wp_get_attachment_image_src($photo->ID, 'profile-thumb-mobile');
									if($photo_th_mobile_url[3] != "1") {
										require_once( ABSPATH . 'wp-admin/includes/image.php' );
										$attach_data = wp_generate_attachment_metadata($photo->ID, get_attached_file($photo->ID));
										wp_update_attachment_metadata($photo->ID, $attach_data);
										$photo_th_mobile_url = wp_get_attachment_image_src($photo->ID, 'profile-thumb-mobile');
									}

									if (get_the_author_meta('ID') == $userid || current_user_can('level_10')) {
										$imagebuttons = '<span class="edit-buttons"><span class="icon button-delete icon-cancel rad3"></span></span>';
									}
									echo '<div class="profile-img-thumb-wrapper"><div class="profile-img-thumb" id="'.$photo->ID.'">';
									echo 	$imagebuttons;
									echo 	'<a href="'.$photo->guid.'" data-fancybox="profile-photo">';
									echo 		'<img data-original-url="'.$photo_th_url[0].'" class="mobile-ready-img rad3" alt="'.get_the_title().'" data-responsive-img-url="'.$photo_th_mobile_url[0].'" />';
									echo 	'</a>';
									echo '</div></div>'."\n";
								}
							?>
						</div> <!-- THUMBS -->

		                <div class="clear20"></div>
	    	            <div class="girlinfo l">
	                        <h4 class="rad3"><?php _e('Information','escortwp'); ?>:</h4>
	                        <div class="clear"></div>
							<?php
							if (get_option("escortid".get_the_author_meta('ID')) == $taxonomy_profile_url) {
								$author_profile = get_post(get_option("escortpostid".get_the_author_meta('ID')));
							} else if(get_option("escortid".get_the_author_meta('ID')) == $taxonomy_agency_url) {
								$author_profile = get_post(get_option("agencypostid".get_the_author_meta('ID')));
							}
							if($author_profile) {
								echo '<b>'.__('Added by','escortwp').':</b><span class="valuecolumn"><a href="'.$author_profile->guid.'">'.$author_profile->post_title.'</a></span>';
							}

							$location = array();
							$city = get_term(get_post_meta(get_the_ID(), 'city', true));
							if($city) {
								$location[] = '<b>'.__('City','escortwp').':</b><span class="valuecolumn"><a href="'.get_term_link($city).'" title="'.$city->name.'">'.$city->name.'</a></span>';

								$state = get_term($city->parent, $taxonomy_location_url);
								if($state) {
									$state_label = showfield('state') ? __('State','escortwp') : __('Country','escortwp');
									$location[] = '<b>'.$state_label.':</b><span class="valuecolumn"><a href="'.get_term_link($state).'" title="'.$state->name.'">'.$state->name.'</a></span>';

									$country = get_term($state->parent, $taxonomy_location_url);
									if(!is_wp_error($country)) {
										$location[] = '<b>'.__('Country','escortwp').':</b><span class="valuecolumn"><a href="'.get_term_link($country).'" title="'.$country->name.'">'.$country->name.'</a></span>';
									}
								}
							}
							echo implode("", $location);

							if (get_post_meta(get_the_ID(),'gender', true)) {
								echo "<b>".__('Gender','escortwp').':</b><span class="valuecolumn">'.$gender_a[get_post_meta(get_the_ID(), 'gender', true)]."</span>";
							}
							if (get_post_meta(get_the_ID(),'haircolor', true)) {
								echo "<b>".__('Hair color','escortwp').':</b><span class="valuecolumn">'.$haircolor_a[get_post_meta(get_the_ID(), 'haircolor', true)]."</span>";
							}
							if (get_post_meta(get_the_ID(),'email', true)) {
								echo "<b>".__('Email','escortwp').':</b><span class="valuecolumn">'.__('hidden','escortwp')."</span>";
							}
							if (get_post_meta(get_the_ID(),'phone', true)) {
								echo "<b>".__('Phone','escortwp').':</b><span class="valuecolumn">'.__('hidden','escortwp')."</span>";
							}
							?>
	                	</div> <!-- GIRL INFO LEFT -->
	                    <div class="girlinfo r">
		                    <h4 class="rad3"><?php printf(esc_html__('%s note','escortwp'),ucfirst($taxonomy_profile_name)); ?>:</h4>
	                        <div class="clear"></div>
	                        <?php the_content(); ?>
	                    </div> <!-- GIRL INFO RIGHT -->
						<div class="clear10"></div>
						<?php
						if (current_user_can('level_10')) {
							edit_post_link(__('Edit in WordPress','escortwp'));
						}
						?>
	                </div> <!-- GIRL SINGLE -->
		<?php endwhile; ?>
	<?php endif; ?>

    </div> <!-- BODY BOX -->
    <div class="clear"></div>
</div> <!-- BODY -->
</div> <!-- contentwrapper -->

<?php get_sidebar("left"); ?>
<?php get_sidebar("right"); ?>

<div class="clear"></div>

<?php get_footer(); ?>