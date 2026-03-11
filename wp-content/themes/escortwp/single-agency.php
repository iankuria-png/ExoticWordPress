<?php
if (have_posts()) :
while (have_posts()) : the_post();

$current_page_url = get_permalink();
global $taxonomy_location_url, $taxonomy_profile_name, $taxonomy_agency_name, $taxonomy_profile_name_plural, $taxonomy_profile_url, $taxonomy_agency_url, $payment_duration_a, $gender_a, $ethnicity_a, $haircolor_a, $hairlength_a, $bustsize_a, $build_a, $looks_a, $smoker_a, $availability_a, $languagelevel_a, $services_a, $currency_a;
$current_user = wp_get_current_user();
$userid = $current_user->ID;
$userstatus = get_option("escortid".$userid);
$thispostid = get_the_ID();
$thisposttitle = get_the_title();

// add escort to agency
if (current_user_can('level_10')) {
	$err = ""; $ok = "";
	if (isset($_POST['agencyid'])) {
		$admin_adding_escort = "yes";
		include (get_template_directory() . '/register-independent-personal-info-process.php');
	}

	if (isset($_POST['agency_post_id'])) {
		$admin_adding_agency = "yes";
		include (get_template_directory() . '/register-agency-personal-info-process.php');
	} else {
		$agency_post_id = get_the_ID();
		$agency = get_post($agency_post_id);

		$aboutagency = do_shortcode(substr(stripslashes(wp_kses(str_replace("</p><p>", "\n\n", $agency->post_content), array())), 0, 5000));
		$agencyemail = get_the_author_meta('user_email');
		$agencyname = get_the_author_meta('display_name');

		$phone = get_post_meta($agency_post_id, "phone", true);
		$website = get_the_author_meta('user_url');
	}

	if (isset($_POST['action']) && $_POST['action'] == 'agencyupgrade') {
		if ( isset($_POST['delexpiration']) ) {
			delete_post_meta(get_the_ID(), 'agency_expire');
			delete_post_meta(get_the_ID(), 'agency_renew');

			if(payment_plans('agreg','price')) {
				update_post_meta(get_the_ID(), 'needs_payment', "1");
				wp_update_post(array( 'ID' => get_the_ID(), 'post_status' => 'private' ));
			}
		}
		if ( isset($_POST['expirationperiod']) ) {
			if ( $_POST['profileduration'] ) {
				$expiration = strtotime("+".$payment_duration_a[$_POST['profileduration']][2]);
				if(get_post_meta(get_the_ID(), "agency_expire", true)) {
					$available_time = get_post_meta(get_the_ID(), 'agency_expire', true);
					if($available_time && $available_time > time()) { $expiration = $expiration + ($available_time - time()); }
				}
				update_post_meta(get_the_ID(), 'agency_expire', $expiration);
			} else {
				delete_post_meta(get_the_ID(), 'agency_expire');
				delete_post_meta(get_the_ID(), 'agency_renew');
			}
		}
	}

	if (isset($_POST['action']) && $_POST['action'] == 'needs_payment') {
		$privprof = array( 'ID' => get_the_ID(), 'post_status' => 'private' );
		wp_update_post($privprof);
		update_post_meta(get_the_ID(), 'needs_payment', '1');
		wp_redirect(get_permalink(get_the_ID())); exit;
	}

	if (isset($_POST['action']) && $_POST['action'] == 'activateprivateprofile') {
		$privprof = array( 'ID' => get_the_ID(), 'post_status' => 'publish' );
		wp_update_post($privprof);
		wp_redirect(get_permalink(get_the_ID())); exit;
	} // activate private agency

	if (isset($_POST['action']) && $_POST['action'] == 'activateunpaidprofile') {
		if ($_POST['profileduration']) {
			$expiration = strtotime("+".$payment_duration_a[$_POST['profileduration']][2]);
			if(get_post_meta(get_the_ID(), "agency_expire", true)) {
				$available_time = get_post_meta(get_the_ID(), 'agency_expire', true);
				if($available_time && $available_time > time()) { $expiration = $expiration + ($available_time - time()); }
			}
			update_post_meta(get_the_ID(), 'agency_expire', $expiration);
		}

		$privprof = array( 'ID' => get_the_ID(), 'post_status' => 'publish' );
		delete_post_meta(get_the_ID(), "needs_payment");
		wp_update_post($privprof);

		$ag_profile_id = get_the_ID();

		$args = array(
			'post_type' => $taxonomy_profile_url,
			'posts_per_page' => -1,
			'author' => get_the_author_meta('ID'),
			'meta_query' => array(
				array(
					'key'     => 'needs_ag_payment',
					'value'   => '1',
					'type'    => 'numeric',
					'compare' => '=',
				),
				array(
					'key' => 'needs_payment',
					'value'   => '1',
					'type'    => 'numeric',
					'compare' => '!=',
				)
			)
		);
		query_posts( $args );
		if (have_posts()) :
		while ( have_posts() ) : the_post();
			wp_update_post(array('ID' => get_the_ID(), 'post_status' => 'publish'));
		endwhile;
		endif;
		wp_reset_query();

		wp_redirect(get_permalink($ag_profile_id)); exit;
	} // activate unpaid profile
} // if admin

//delete an agency account
if (is_user_logged_in() && isset($_POST['action']) && $_POST['action'] == "deleteagency" && (get_the_author_meta('ID') == $userid && $userstatus == $taxonomy_agency_url || current_user_can('level_10'))) {
	delete_agency(get_the_ID());
	wp_redirect(get_bloginfo("url")); exit();
} // if agency or admin

if (isset($_POST['action']) && $_POST['action'] == "contactform") {
	if ($_POST['emails']) { $err .= "."; }

	if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && get_option("recaptcha5") && !is_user_logged_in()) { $err .= verify_recaptcha(); }

	if (is_user_logged_in()) {
		$contactformname = $current_user->display_name;
		$contactformemail = $current_user->user_email;
	} else {
		$contactformname = get_option("email_sitename");
		$contactformemail = $_POST['contactformemail'];
		if ($contactformemail) {
			if(!is_email($contactformemail)) { $err .= __('Your email address seems to be wrong','escortwp')."<br />"; }
		} else {
			$err .= __('Your email is missing','escortwp')."<br />";
		}
	}
	$contactformmess = substr(sanitize_textarea_field($_POST['contactformmess']), 0, 5000);
	if (!$contactformmess) { $err .= __('You need to write a message','escortwp')."<br />"; }

	if (!$err) {
		$body = __('Hello','escortwp').' '.get_the_author_meta('display_name').'<br />
'.__('Someone sent you a message from','escortwp').' '.get_option("email_sitename").':<br />
<a href="'.get_permalink(get_the_ID()).'">'.get_permalink(get_the_ID()).'</a><br /><br />
'.__('Sender information','escortwp').':<br />
'.__('name','escortwp').': <b>'.$contactformname.'</b><br />
'.__('email','escortwp').': <b>'.$contactformemail.'</b><br />
'.__('message','escortwp').':<br />'.$contactformmess.'<br /><br />
'.__('You can send a message back to this person by replying to this email.','escortwp');
		dolce_email($contactformname, $contactformemail, get_the_author_meta('user_email'), __('Message from','escortwp')." ".get_option("email_sitename"), $body);
		unset($contactformname, $contactformemail, $contactformmess, $body);
		$ok = __('Message sent','escortwp');
	}
}


if ($userstatus == "member" || current_user_can('level_10')) {
	if (isset($_POST['action']) && $_POST['action'] == 'addreview') {
		$rateagency = (int)$_POST['rateagency'];
		if ($rateagency < 1 || $rateagency > 6) {
			$err .= sprintf(esc_html__('The %s rating is wrong. Please select again.','escortwp'),$taxonomy_agency_name)."<br />"; unset($rateagency);
		}

		$reviewtext = substr(stripslashes(wp_kses($_POST['reviewtext'], array())), 0, 1000);
		if (!$reviewtext) {
			$err .= __('You didn\'t write a review','escortwp')."<br />";
		}

		if (!$err) {
			//add review to database
			if (get_option("manactivag") == "1") {
				$reviewstatus = "draft";
			} else {
				$reviewstatus = "publish";
			}
			$reviews_cat_id = term_exists( 'Reviews', "category" );
			if (!$reviews_cat_id) {
				$arg = array('description' => 'Reviews');
				wp_insert_term('Reviews', "category", $arg);
				$reviews_cat_id = term_exists( 'Reviews', "category" );
			}
			$reviews_cat_id = $reviews_cat_id['term_id'];
			$add_review = array(
				'post_title' => __('review for','escortwp')." ".get_the_title(),
				'post_content' => $reviewtext,
				'post_status' => $reviewstatus,
				'post_author' => $userid,
				'post_category' => array($reviews_cat_id),
				'post_type' => 'review',
				'ping_status' => 'closed'
			);
			$add_review_id = wp_insert_post( $add_review );
			update_post_meta($add_review_id, "rateagency", $rateagency);
			update_post_meta($add_review_id, "agencyid", get_the_ID());
			update_post_meta($add_review_id, "reviewfor", 'agency');

			if (get_option("manactivag") == "1") {
				$new_review_email_title = __('A new review is waiting for approval on','escortwp')." ".get_option("email_sitename");
			} else {
				$new_review_email_title = sprintf(esc_html__('Someone wrote an %s review on.','escortwp'),$taxonomy_agency_name).' '.get_option("email_sitename");
			}
			$reviewadminurl = get_admin_url('', 'post.php?post='.$add_review_id.'&action=edit');
			$body = __('Hello','escortwp').',<br />
'.sprintf(esc_html__('Someone wrote an %s review on.','escortwp'),$taxonomy_agency_name).' '.get_option("email_sitename").':<br /><br />
'.__('Read/Edit the review here','escortwp').':<br />
<a href="'.$reviewadminurl.'">'.$reviewadminurl.'</a>';
			if(get_option("ifemail5") == "1" || get_option("manactivag") == "1") {
				dolce_email(null, null, get_bloginfo("admin_email"), $new_review_email_title, $body);
			}

			wp_redirect(get_permalink(get_the_id())."?postreview=ok"); exit();
		}
	} // if action add review
} // if member

get_header(); ?>

		<div class="contentwrapper">
		<div class="body agency-page">
			<?php if (current_user_can('level_10')) { ?>
				<div class="bodybox girlsingle agency_options_add_profile<?php if(isset($_POST['escort_post_id']) && $err) { } else { echo ' hide'; } ?>">
					<div class="registerform">
						<?php closebtn(); ?>
						<div class="clear10"></div>
						<?php
							$agencyid = get_the_author_meta('ID');
							$agency_profile_id = get_the_ID();
							$admin_adding_escort = "yes";
							include (get_template_directory() . '/register-independent-personal-information-form.php');
						?>
						<div class="clear"></div>
					</div> <!-- ADD PROFILE -->
				</div> <!-- BODY BOX -->

				<div class="bodybox girlsingle agency_options_edit_agency<?php if(isset($_POST['agency_post_id']) && $err) { } else { echo ' hide'; } ?>">
					<div class="registerform">
						<?php closebtn(); ?>
						<div class="clear10"></div>
						<?php
							$agency_post_id = get_the_ID();
							$admin_editing_agency = "yes";
							$city = wp_get_post_terms($agency_profile_id, $taxonomy_location_url);
							if($city[0]) {
								$state = get_term($city[0]->parent, $taxonomy_location_url);
								if($state->parent > 0 && !is_wp_error($state)) {
									$country = get_term($state->parent, $taxonomy_location_url);
									$country = $country->term_id;
								} else {
									$country = $state->term_id;
								}
								$state = $state->term_id;
							}
							include (get_template_directory() . '/register-agency-personal-information-form.php');
						?>
						<div class="clear"></div>
					</div> <!-- EDIT AGENCY -->
				</div> <!-- BODY BOX -->

				<div class="bodybox girlsingle agency_options_add_logo hide">
					<?php closebtn(); ?>
					<div class="clear"></div>
	            	<h3><?php printf(esc_html__('Upload/Edit %s Logo','escortwp'),$taxonomy_agency_name); ?></h3>

					<script type="text/javascript">
					jQuery(document).ready(function($) {
					    $('#file_upload').uploadifive({
							'auto'           : true,
							'buttonClass'    : 'pinkbutton rad25',
							'buttonText'     : '<?php _e('Upload logo','escortwp'); ?>',
							'fileSizeLimit'  : '<?=get_option("maximguploadsize")?>MB',
					        'fileType'       : 'image/*',
					        'formData'       : { 'folder' : '<?php echo get_post_meta(get_the_ID(), "secret", true); ?>' },
							'multi'          : false,
							'queueID'        : 'upload-queue',
							'queueSizeLimit' : 1,
							'removeCompleted': true,
							'simUploadLimit' : 1,
							'uploadLimit'    : 100,
							'uploadScript'   : '<?php bloginfo('template_url'); ?>/register-agency-upload-logo-process.php',
							'onQueueComplete': function(data) {
								$('#status-message').html('<?=addslashes(__('Your image has been uploaded','escortwp'))?>.<br /><'+'a href="<?php echo get_permalink(get_the_ID()); ?>"><?=addslashes(__('Refresh the page to see it','escortwp'))?><'+'/a>');
								setTimeout(location.reload(), 0);
							}
						});

						// delete an image from the account
						$('.upload_photos_form .profile-img-thumb .button-delete').on('click', function(){
							$(this).hide();
							var imgid = $(this).parents('.profile-img-thumb').data('id');
							$("#img"+imgid).fadeOut("slow");
							$.ajax({
								type: "GET",
								url: "<?php bloginfo('template_url'); ?>/ajax/delete-agency-logo.php",
								data: "id=" + imgid,
								success: function(data){
									$('.image_msg').html(data).addClass('ok').fadeIn("slow").delay(3000).fadeOut("slow");
									setTimeout(location.reload(), 0);
								}
							});
						});
					});
					</script>

					<div class="upload_photos_form">
				    	<div class="upload_photos_button"><input id="file_upload" name="file_upload" type="file" /></div><div class="clear"></div>
						<div id="status-message" class="text-center">
							<?php printf(esc_html__('Click the button and select your %s logo.','escortwp'),$taxonomy_agency_name); ?><br />
							<?php _e('You can only upload a single image.', 'escortwp'); ?><br />
							<?php _e('To change an uploaded logo simply upload a new one.','escortwp'); ?>
						</div><div class="clear"></div>
						<div id="upload-queue"></div><div class="clear20"></div>
						<h4 class="logo-used"><?php printf(esc_html__('The %s logo you will be using','escortwp'),$taxonomy_agency_name); ?>:</h4><div class="clear"></div>
				        <div class="image_msg center rad25"></div><div class="clear10"></div>
						<?php
						// check and display the photos uploaded by the user
						$photos = get_children( array('post_parent' => get_the_ID(), 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID', 'numberposts' => '5') );
						sort($photos);
						// get the images uploaded
						foreach ($photos as $key => $ag_photo) {
							$ag_photo_th_url = wp_get_attachment_image_src($ag_photo->ID, 'listings-thumb');
							if($ag_photo_th_url[3] != "1") {
								require_once( ABSPATH . 'wp-admin/includes/image.php' );
								$attach_data = wp_generate_attachment_metadata($ag_photo->ID, get_attached_file($ag_photo->ID));
								wp_update_attachment_metadata($ag_photo->ID, $attach_data);
								$ag_photo_th_url = wp_get_attachment_image_src($ag_photo->ID, 'listings-thumb');
							}
							echo '<div class="text-center">
									<div class="profile-img-thumb center" data-id="'.$ag_photo->ID.'" id="img'.$ag_photo->ID.'">
										<span class="rad3">
											<img src="'.$ag_photo_th_url[0].'" class="rad3" alt="" />
											<span class="edit-buttons"><span class="icon button-delete icon-cancel rad3"></span></span>
										</span>
										<div class="clear"></div>
									</div>
								</div>'."\n";
						}
						?>
					</div> <!-- UPLOAD PHOTOS FORM-->

	                <div class="clear"></div>
				</div> <!-- BODY BOX -->

			<?php } // if admin ?>
			<?php if (current_user_can('level_10') || get_the_author_meta('ID') == $userid) { ?>
				<div class="bodybox agency_options_delete hide">
					<div class="registerform text-center">
						<?php closebtn(); ?>
						<div class="clear10"></div>
						<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="text-center">
							<?php
							if(get_the_author_meta('ID') == $userid) {
								printf(esc_html__('Are you sure you want to delete your %s account?','escortwp'),$taxonomy_agency_name);
							} else {
								printf(esc_html__('Are you sure you want to delete this %s account?','escortwp'),$taxonomy_agency_name);
							}
							?>
							<div class="clear5"></div>
							<?php _e('Deleted accounts can\'t be recovered.','escortwp'); ?><div class="clear5"></div>
							<?php
							if(get_the_author_meta('ID') == $userid) {
								printf(esc_html__('All %s profiles added under this account will also be deleted.','escortwp'),$taxonomy_profile_name);
							} else {
								printf(esc_html__('All %1$s profiles added by this %2$s will also be deleted','escortwp'),$taxonomy_profile_name,$taxonomy_agency_name);
							}
							?>
							<div class="clear5"></div>
							<?php
							if(get_the_author_meta('ID') == $userid) {
								_e('To completely delete your account click the button below:','escortwp');
							} else {
								_e('To completely delete the account click the button below:','escortwp');
							}
							?>
							<div class="clear20"></div>
							<input type="submit" name="submit" value="<?php printf(esc_html__('Delete %s','escortwp'),$taxonomy_agency_name); ?> <?php the_title(); ?>" class="redbutton submitbutton rad25" />
							<input type="hidden" name="action" value="deleteagency" />
						</form>
					</div>
				</div> <!-- DELETE -->
			<?php } // if admin or profile owner ?>

        	<div class="bodybox girlsingle agency-profile" itemscope itemtype ="http://schema.org/Brand">
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						$('.sendemail').on('click', function(){
							$('.escortcontact').slideToggle("slow");
							$(this).slideToggle("slow");
						});
						$('.escortcontact .closebtn').on('click', function(){
							$('.escortcontact').slideToggle("slow");
							$('.sendemail').slideToggle("slow");
						});

						$('.addreview').on('click', function(){
							$('.addreviewform, .addreview').slideDown("slow");
							$('html,body').animate({ scrollTop: $('.addreviewform').offset().top }, { duration: 'slow', easing: 'swing'});
						});
					    if(window.location.hash == "#addreview") {
							$('.addreviewform, .addreview').slideToggle("slow");
							$('html,body').animate({ scrollTop: $('#addreviewsection').offset().top }, { duration: 'slow', easing: 'swing'});
						}
						$('.addreviewform .closebtn').on('click', function(){
							$('.addreviewform, .addreview').slideToggle("slow");
						});

						<?php if ($userstatus == "member" || current_user_can('level_10')) { ?>
						function count_review_text(t) {
							var charlimit = 1000;
							var box = $(t).val();
							var main = box.length * 100;
							var value = (main / charlimit);
							var count = charlimit - box.length;
							var boxremove = box.substring(0, charlimit);
							var ourtextarea = $(t);

							$('.charcount').show('slow');
							if(box.length <= charlimit) {
								$('#count').html(count);
								$("#reviewtext")
								$('#bar').animate( {
									"width": value+'%',
								}, 1);
							} else {
								$('#reviewtext').val(boxremove);
					            ourtextarea.scrollTop(
					                ourtextarea[0].scrollHeight - ourtextarea.height()
					            );
							}
							return false;
						}
						if($('#reviewtext').length) {
							count_review_text('#reviewtext');
						}
						$("#reviewtext").keyup(function() {
							count_review_text("#reviewtext");
						});
						<?php } ?>
					});
				</script>
			    <div class="profile-header">
			    	<div class="profile-header-name">
		            	<?php
		            	// no need to restrict the message since only admins and post authors can see the profile anyway
						if(get_post_status(get_the_ID()) == "private") {
							echo '<div class="girlsinglelabels text-center">';
							echo '<span class="redbutton rad25 center">'.__('This profile is set to private and will not be shown in the site', 'escortwp').'</span>';
							echo '</div><div class="clear10"></div>';
						}
		            	?>
				    	<h3 class="profile-title" title="<?php the_title_attribute(); ?>" itemprop="name"><?php the_title(); ?></h3>
				    	<div class="clear"></div>
				    	<?=show_online_label_html(get_the_author_meta('ID'))?>
					</div> <!-- profile-header-name -->
				</div> <!-- profile-header -->

				<?php
					$photos = get_children(array( 'post_parent' => get_the_ID(), 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'ID', 'numberposts' => '1' ));
					if(count($photos) > 0) {
						$photos = reset($photos);
						$ag_photo_th_url = wp_get_attachment_image_src($photos->ID, 'main-image-thumb');
						if($ag_photo_th_url[3] != "1") {
							require_once( ABSPATH . 'wp-admin/includes/image.php' );
							$attach_data = wp_generate_attachment_metadata($photos->ID, get_attached_file($photos->ID));
							wp_update_attachment_metadata($photos->ID, $attach_data);
							$ag_photo_th_url = wp_get_attachment_image_src($photos->ID, 'main-image-thumb');
						}
					}
					if($ag_photo_th_url[0]) {
		            	echo '<div class="bigimage l">';
							echo '<img src="'.$ag_photo_th_url[0].'" class="rad3 l" alt="'.get_the_title().'" itemscope="logo" />'."\n";
		                echo '</div> <!-- BIG IMAGE -->';
					}
				?>
				<div class="agencydetails <?php if(!$ag_photo_th_url[0]) { echo " agencydetails-noimg"; } ?>">
                    <?php if (get_the_author_meta('user_url')) { ?>
					<b><?php _e('Website','escortwp'); ?>:</b><span><a href="<?php echo get_the_author_meta('user_url'); ?>" target="_blank" rel="nofollow" itemscope="url"><?php echo str_replace(array("http://www.", "http://"), "", get_the_author_meta('user_url')); ?></a></span><br />
					<?php } ?>
                    <b><?php _e('Phone','escortwp'); ?>:</b><span><?php echo get_post_meta(get_the_ID(), "phone", true); ?></span><br />
					<?php
					$location = array();
					$city = wp_get_post_terms($thispostid, $taxonomy_location_url);
					if($city) {
						$location[] = '<b>'.__('City','escortwp').':</b><span><a href="'.get_term_link($city[0]->term_id).'" title="'.$city[0]->name.'">'.$city[0]->name.'</a></span><br />';
						$state = get_term($city[0]->parent, $taxonomy_location_url);
						if($state) {
							$country = get_term($state->parent, $taxonomy_location_url);
							if(is_wp_error($country)) {
								$country = $state;
							} else {
								$location[] = '<b>'.__('State','escortwp').':</b><span><a href="'.get_term_link($state).'" title="'.$state->name.'">'.$state->name.'</a></span><br />';
							}
								$location[] = '<b>'.__('Country','escortwp').':</b><span><a href="'.get_term_link($country).'" title="'.$country->name.'">'.$country->name.'</a></span><br />';
						}
					}
					echo implode("", $location);
					?>

                    <b><?php echo ucfirst($taxonomy_profile_name_plural); ?>:</b><span><?php echo show_post_count(get_the_author_meta('ID')); ?></span><br />
					<?php if(get_option("hide1") != "1") { ?>
						<b><?php _e('Rating','escortwp'); ?>:</b>
						<div class="starrating l"><div class="starrating_stars l star<?php echo get_agency_rating(get_the_ID()); ?>"></div></div><br />
					<?php } ?>
                    <div class="clear10"></div><a name="contactform"></a>
                    <?php if(get_option("hide1") != "1") { ?>
	                    <div class="addreview rad25 pinkbutton l"><span class="icon-plus-circled"></span><?php _e('Add Review','escortwp'); ?></div>
                    <?php } ?>
                    <div class="sendemail rad25 pinkbutton l"<?php if ($err && $_POST['action'] == "contactform") { echo ' style="display: none;"'; } ?>><span class="icon-mail"></span><?php printf(esc_html__('Contact this %s','escortwp'),$taxonomy_agency_name); ?></div>
					<?php if ($err && $_POST['action'] == "contactform") { echo '<div class="err rad25">'.$err.'</div>'; } ?>
					<?php if ($ok && $_POST['action'] == "contactform") { echo '<div class="ok rad25">'.$ok.'</div>'; } ?>
                    <div class="clear10"></div>
					<?php include (get_template_directory() . '/send-email-form.php'); ?>
                    <div class="clear"></div>
                </div> <!-- AGENCY DETAILS -->
                <?php
                if($ag_photo_th_url[0]) {
                	echo '<div class="clear20"></div>';
                }
                ?>
                <div class="agency-desc<?=$desc_class?>">
					<h4><?php printf(esc_html__('About the %s','escortwp'),$taxonomy_agency_name); ?>:</h4>
					<div itemscope="description"><?php the_content(); ?></div>
					<?php
					if (current_user_can('level_10')) {
						edit_post_link(__('Edit in WordPress','escortwp'));
					}
					endwhile;
					endif;
					wp_reset_query();
					?>
				</div> <!-- agency-desc -->
                <div class="clear"></div>
                <?php
	            if(get_option('hitcounter2')) {
	                echo esc_page_hit_counter(get_the_ID());
	            }
                ?>
                <div class="clear"></div>
            </div> <!-- BODY BOX -->

        	<div class="bodybox">
            	<h3 class="l"><?php printf(esc_html__('%1$s added by this %2$s','escortwp'),ucfirst($taxonomy_profile_name_plural),$taxonomy_agency_name); ?></h3>
            	<div class="clear10"></div>
				<?php
				$path = explode("/", $_SERVER['REQUEST_URI']);
				foreach ($path as $key=>$element) {
					if ($element == "") { unset($path[$key]); }
				}
				if(is_numeric(end($path))) {
					$paged = (int)end($path);
				}
				if($_GET['page']) {
					$paged = (int)$_GET['page'];
				}
				$posts_per_page = "20";

				$args = array(
					'author' => get_the_author_meta('ID'),
					'post_type' => $taxonomy_profile_url,
					'paged' => $paged,
					'posts_per_page' => $posts_per_page,
					'order' => 'DESC',
					'orderby' => 'ID'
				);

				$i = 1;
				$profiles = new WP_Query( $args );
				if ( $profiles->have_posts() ) : while ( $profiles->have_posts() ) : $profiles->the_post();
					include (get_template_directory() . '/loop-show-profile.php');
				endwhile;
					$total = $profiles->max_num_pages;
					$format = get_option('permalink_structure') ? 'paged/%#%/' : '&page=%#%';
					dolce_pagination($total, $paged, $format, $current_page_url);
				else:
					printf(esc_html__('No %s here yet','escortwp'),$taxonomy_profile_name_plural);
				endif;
				wp_reset_query();
				?>
	            <div class="clear"></div>
            </div> <!-- BODY BOX -->

			<?php if(get_option("hide1") != "1") { ?>
	        	<div class="bodybox agency-reviews-bodybox">
	            	<h4 class="l"><?php printf(esc_html__('%s reviews','escortwp'),ucwords($taxonomy_agency_name)); ?></h4>
	                <div class="addreview rad25 pinkbutton r"><span class="icon-plus-circled"></span><?php _e('Add Review','escortwp'); ?></div>
					<div class="clear10" id="addreviewsection"></div>
					<div class="addreviewform hide registerform">
						<?php
						if ($_GET['postreview'] == "ok") {
							echo '<div class="clear"></div>';
							echo '<div class="ok rad25">';
								if (get_option("manactivag") == "1") {
									echo __('Your review needs to be approved by an admin before being published.','escortwp').'<br />';
								}
								echo __('Thank you for posting.','escortwp');
							echo '</div>';
						}

						if (did_user_post_review($userid, get_the_ID())) {
							if ($_GET['postreview'] != "ok") {
								echo '<div class="err rad25">'.sprintf(esc_html__('You can\'t post more than one review for the same %s.','escortwp'),$taxonomy_agency_name).'</div>';
							}
						} else if (($userstatus == "member" || current_user_can('level_10')) && did_user_post_review($userid, get_the_ID()) == 0) { ?>
							<?php if ( $ok && $_POST['action'] == 'addreview') { echo "<div class=\"ok rad25\">$ok</div>"; } ?>
							<?php if ( $err && $_POST['action'] == 'addreview') { echo "<div class=\"err rad25\">$err</div>"; } ?>
							<form action="<?php echo get_permalink(get_the_ID()); ?>#addreview" method="post" class="form-styling">
							    <?php closebtn(); ?>
							    <div class="clear10"></div>
							    <input type="hidden" name="action" value="addreview" />

							   	<div class="form-label">
							    	<label for="rateagency"><?php printf(esc_html__('Rate the %s','escortwp'),$taxonomy_agency_name); ?>: <i>*</i></label>
							    </div>
								<div class="form-input form-input-rating">
									<label for="rateagency5"><input type="radio" id="rateagency5" name="rateagency" value="5" <?=$rateagency == "5" ? ' checked' : ""?> />5 - <?php _e('Perfect','escortwp'); ?></label><div class="clear"></div>
									<label for="rateagency4"><input type="radio" id="rateagency4" name="rateagency" value="4" <?=$rateagency == "4" ? ' checked' : ""?> />4 - <?php _e('Good','escortwp'); ?></label><div class="clear"></div>
									<label for="rateagency3"><input type="radio" id="rateagency3" name="rateagency" value="3" <?=$rateagency == "3" ? ' checked' : ""?> />3 - <?php _e('Average','escortwp'); ?></label><div class="clear"></div>
									<label for="rateagency2"><input type="radio" id="rateagency2" name="rateagency" value="2" <?=$rateagency == "2" ? ' checked' : ""?> />2 - <?php _e('Bellow average','escortwp'); ?></label><div class="clear"></div>
									<label for="rateagency1"><input type="radio" id="rateagency1" name="rateagency" value="1" <?=$rateagency == "1" ? ' checked' : ""?> />1 - <?php _e('Bad','escortwp'); ?></label><div class="clear"></div>
							    </div> <!-- rateing --> <div class="formseparator"></div>

								<div class="form-label">
									<label for="reviewtext"><?php _e('Comment','escortwp'); ?>: <i>*</i></label>
								</div>
								<div class="form-input">
									<textarea name="reviewtext" class="textarea longtextarea" rows="7" id="reviewtext"><?php echo $reviewtext; ?></textarea>
									<div clas="clear"></div>
									<small class="l"><?php _e('html code will be removed','escortwp'); ?></small>
									<div class="charcount hides r"><div id="barbox" class="rad25"><div id="bar"></div></div><div id="count"></div></div>
								</div> <!-- review text --> <div class="formseparator"></div>

								<div class="text-center">
									<div class="clear10"></div>
									<input type="submit" name="submit" value="<?php _e('Add Review','escortwp'); ?>" class="pinkbutton rad25" />
								</div> <!--center-->
								<div class="clear30"></div>
							</form>
						<?php
						} else {
							if (is_user_logged_in()) {
								echo '<div class="err rad25">'.__('Your user type is not allowed to post a review here','escortwp').'</div>';
							} else {
								echo '<div class="err rad25">'.__('You need to','escortwp').' <a href="'.get_permalink(get_option('main_reg_page_id')).'">'.__('register','escortwp').'</a> '.__('or','escortwp').' <a href="'.wp_login_url(get_permalink()).'">'.__('login','escortwp').'</a> '.__('to be able to post a review','escortwp').'</div>';
							}
						}
						?>
					</div> <!-- ADD REVIEW FORM-->
					<?php
					$args = array(
						'post_type' => 'review',
						'posts_per_page' => '-1',
						'meta_query' => array( array('key' => 'agencyid', 'value' => $thispostid, 'compare' => '=', 'type' => 'NUMERIC') )
					);

					query_posts($args);
					if ( have_posts() ) :
					while ( have_posts() ) : the_post();
						$rating_number = get_post_meta(get_the_ID(), 'rateagency', true);
					?>
						<div class="review-wrapper rad5">
							<div class="starrating l"><div class="starrating_stars star<?php echo $rating_number; ?>"></div></div>&nbsp;&nbsp;<i><?php echo strtolower(__('Added by','escortwp')); ?></i>&nbsp;&nbsp;<b><?php echo substr(get_the_author_meta('display_name'), 0, 2); ?>...</b> <i><?php _e('for','escortwp'); ?></i> <b><?=$thisposttitle?></b> <i><?php __('on','escortwp'); ?></i> <b class="r"><?php echo the_time("d F Y"); ?></b>
							<?php the_content(); ?>
							<?php edit_post_link(__('Edit review','escortwp')); ?>
						</div>
						<div class="clear30"></div>
					<?php
					endwhile;
					else:
						echo '<div class="text-center">'.__('No reviews yet','escortwp').'</div>';
					endif;
					wp_reset_query();
					?>
		            <div class="clear"></div>
	            </div> <!-- BODY BOX -->
			<?php } // ifhide reviews ?>

			<div class="clear"></div>
        </div> <!-- BODY -->
		</div> <!-- contentwrapper -->

		<?php get_sidebar("left"); ?>
		<?php get_sidebar("right"); ?>

    	<div class="clear"></div>
<?php get_footer(); ?>