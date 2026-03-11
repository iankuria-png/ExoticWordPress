<?php
global $taxonomy_agency_url;
$current_user = wp_get_current_user();
$userid = $current_user->ID;
$userstatus = get_option("escortid".$userid);


if (have_posts()) :
while (have_posts()) : the_post();

if (get_the_author_meta('ID') == $userid || current_user_can('level_10')) {
	if (isset($_POST['action']) && $_POST['action'] == 'activateprivatead') {
		$privad = array( 'ID' => get_the_ID(), 'post_status' => 'publish' );
		wp_update_post($privad);
		wp_redirect(get_permalink(get_the_ID())); exit;
	} // activate private escort

	if (isset($_POST['action']) && $_POST['action'] == 'deleteclassifiedad') {
		$classifiedadidtodelete = (int)$_POST['classifiedadidtodelete'];
		$post = get_post($classifiedadidtodelete);

		$upload_folder = get_post_meta($classifiedadidtodelete, "upload_folder", true);
		$secret = get_post_meta($classifiedadidtodelete, "secret", true);
		$dirtodelete = ABSPATH."wp-content/uploads/".$upload_folder."/";

		if (is_dir($dirtodelete)) {
			$objects = scandir($dirtodelete);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dirtodelete."/".$object) == "dir") {
						rrmdir($dirtodelete."/".$object);
					} else {
						unlink($dirtodelete."/".$object);
					}
				}
			}
			reset($objects);
			rmdir($dirtodelete);
		} // delete directory and files

		delete_option("agency".$secret);
		wp_delete_post( $classifiedadidtodelete, true ); //delete post
		wp_redirect(get_permalink(get_option('manage_ads_page_id'))); exit();
	}

	//if the agency wants to edit the classified ad
	if (isset($_POST['action']) && $_POST['action'] == 'addclassifiedad') {
		$single_page = "yes";
		include (get_template_directory() . '/manage-classified-ads-info-process.php');
	} else {
		$classifiedad_post_id = get_the_ID();
		$ad = get_post($classifiedad_post_id);

		$description = $ad->post_content;
		$title = $ad->post_title;

		$classifiedadphone = get_post_meta(get_the_ID(), "phone", true);
		$classifiedademail = get_post_meta(get_the_ID(), "email", true);
		$classifiedadtype = get_post_meta(get_the_ID(), "type", true);
	}
} // if the escort was added by this user and if the user is an agency

if (isset($_POST['action']) && $_POST['action'] == "contactform") {
	if ($_POST['emails']) { $err .= "."; }

	if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && get_option("recaptcha5")) { $err .= verify_recaptcha(); }

	if (is_user_logged_in()) {
		$contactformname = $current_user->display_name;
		$contactformemail = $current_user->user_email;
	} else {
		$contactformname = get_option("email_sitename");
		$contactformemail = $_POST['contactformemail'];
		if ($contactformemail) {
			if ( !is_email($contactformemail) ) { $err .= __('Your email address seems to be wrong','escortwp')."<br />"; }
		} else {
			$err .= __('Your email is missing','escortwp')."<br />";
		}
	}
	$contactformmess = substr(sanitize_textarea_field($_POST['contactformmess']), 0, 5000);
	if (!$contactformmess) { $err .= __('You need to write a message','escortwp')."<br />"; }

	if (!$err) {
		$body = __('Hello','escortwp').' '.get_the_author_meta('display_name').'<br />
'.__('Someone sent you a message about your classified ad on','escortwp').' '.get_option("email_sitename").':<br />
<a href="'.get_permalink(get_the_ID()).'">'.get_permalink(get_the_ID()).'</a><br /><br />
'.__('Sender information','escortwp').':<br />
'.__('name','escortwp').': <b>'.$contactformname.'</b><br />
'.__('email','escortwp').': <b>'.$contactformemail.'</b><br />
'.__('message','escortwp').':<br />'.$contactformmess.'<br /><br />'.__('You can send a message back to this person by replying to this email','escortwp');
		dolce_email($contactformname, $contactformemail, get_the_author_meta('user_email'), __('Contact message from','escortwp')." ".get_option("email_sitename"), $body);
		unset($contactformname, $contactformemail, $contactformmess, $body);
		$ok = __('Message sent','escortwp');
	}
}

get_header(); ?>

		<div class="contentwrapper">
		<div class="body">
        	<div class="bodybox profile-page single-ad-page">
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
				});
				</script>
				<?php
				if (get_the_author_meta('ID') == $userid || current_user_can('level_10')) {
					if(get_post_status() == "private") {
						echo '<div class="err rad5">'.__('This ad is currently set to private and will not be shown in the site.','escortwp').'<br />'.__('This website requires all ads to be manually activated by an admin.','escortwp').'</div>';
					}

					include (get_template_directory() . '/manage-classified-ads-option-buttons.php');
				}
				?>
                <div class="girlsingle">
	            	<h3 class="l"><?php the_title(); ?></h3>
					<?php
					if (get_the_author_meta('ID') == $userid && $userstatus == $taxonomy_agency_url || current_user_can('level_10')) {
						echo '<div class="r image-buttons-legend"><span class="button-delete icon-cancel" ></span>'.__('Delete image','escortwp').'</div>';
					}
					?>
					<div class="clear"></div>
                    <div class="thumbs col100">
						<?php
							$photos = get_children( array('post_parent' => get_the_ID(), 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID') );
							//get the images uploaded
							$photos = (array)$photos;
							sort($photos);
							foreach ($photos as $photo) {
								global $thumb_sizes; //get image size from functions file
								$w = $thumb_sizes[3][0];
								$h = $thumb_sizes[3][1];
								$w_mobile = $thumb_sizes[4][0];
								$h_mobile = $thumb_sizes[4][1];

								$photo_url = explode("wp-content/uploads/", $photo->guid);
								$photo_abspath = ABSPATH."wp-content/uploads/".$photo_url[1];

								$photo_th_url = $photo_url[0]."wp-content/uploads/".str_replace(".", "-".$w."x".$h.".", $photo_url[1]);
								$photo_th_abspath = ABSPATH."wp-content/uploads/".str_replace(".", "-".$w."x".$h.".", $photo_url[1]);

								$photo_th_mobile_url = $photo_url[0]."wp-content/uploads/".str_replace(".", "-".$w_mobile."x".$h_mobile.".", $photo_url[1]);
								$photo_th_mobile_abspath = ABSPATH."wp-content/uploads/".str_replace(".", "-".$w_mobile."x".$h_mobile.".", $photo_url[1]);

								//create thumbs for browser
								if (!file_exists($photo_th_abspath)) {
									$image = wp_get_image_editor($photo_abspath);
									if ( !is_wp_error($image) ) {
							    		$image->resize( $w, $h, true );
							    		$image->save($photo_th_abspath);
									}
								}
								if (!file_exists($photo_th_mobile_abspath)) {
									$image = wp_get_image_editor($photo_abspath);
									if ( !is_wp_error($image) ) {
							    		$image->resize( $w_mobile, "", false );
							    		$image->save($photo_th_mobile_abspath);
									}
								}

								if (get_the_author_meta('ID') == $userid || current_user_can('level_10')) {
									$imagebuttons = '<span class="edit-buttons"><span class="icon button-delete icon-cancel rad3"></span></span>';
								}
								echo '<div class="profile-img-thumb-wrapper"><div class="profile-img-thumb" id="'.$photo->ID.'">';
								echo $imagebuttons;
								echo '<a href="'.$photo->guid.'" data-fancybox="profile-photo">';
								echo '<img data-original-url="'.$photo_th_url.'" class="rad3 mobile-ready-img"  alt="'.get_the_title().'" data-responsive-img-url="'.$photo_th_mobile_url.'" />';
								echo '</a>';
								echo '</div></div>'."\n";
							}
						?>
						<div class="clear"></div>
					</div> <!-- THUMBS -->
	                <div class="clear20"></div>
    	            <div class="girlinfo l">
                        <h4 class="rad3"><?php _e('Classified Ad Information','escortwp'); ?>:</h4>
                        <div class="clear"></div>
                        <?php
					    if (get_option("escortid".$current_user->ID) == $taxonomy_agency_name && get_option("agencypostid".get_the_author_meta('ID'))) {
					    	$author_profile_id = get_option("agencypostid".get_the_author_meta('ID'));
					    	$profile = get_post($author_profile_id);
					    	$ad_author = $profile->post_title;
					    	if($profile->post_status == "publish") {
						    	$ad_author = '<a href="'.get_permalink($author_profile_id).'">'.$ad_author.'</a>';
					    	}
					    }
					    if (get_option("escortid".$current_user->ID) == $taxonomy_profile_name && get_option("escortpostid".get_the_author_meta('ID'))) {
					    	$author_profile_id = get_option("escortpostid".get_the_author_meta('ID'));
					    	$profile = get_post($author_profile_id);
					    	$ad_author = $profile->post_title;
					    	if($profile->post_status == "publish") {
						    	$ad_author = '<a href="'.get_permalink($author_profile_id).'">'.$ad_author.'</a>';
					    	}
					    }
					    if(get_option("escortid".$current_user->ID) == "member") {
					    	$ad_author = get_the_author_meta('display_name');
					    }

                        if($ad_author) {
	                        echo '<b>'.__('Added by','escortwp').':</b><span class="valuecolumn">'.$ad_author.'</span>';
                        }
						?>
	                    <b><?php _e('Classified ad type','escortwp'); ?>:</b><span class="valuecolumn"><?=get_post_meta(get_the_ID(),'type', true) == "offering" ? __('offering','escortwp') : __('looking','escortwp')?></span>
						<?php
						if (get_post_meta(get_the_ID(),'phone', true)) {
							echo '<b>Phone:</b><span class="valuecolumn">'.get_post_meta(get_the_ID(), 'phone', true)."</span>";
						}

						if (get_post_meta(get_the_ID(),'email', true)) {
						?>
	                        <div class="clear10"></div><a name="contactform"></a>
							<div class="sendemail rad25 pinkbutton l"<?php if ($err && $_POST['action'] == "contactform") { echo ' style="display: none;"'; } ?>><span class="icon-mail"></span><?php _e('Send a message','escortwp') ?></div>
							<div class="clear"></div>
							<?php if ($err && $_POST['action'] == "contactform") { echo '<div class="err rad25">'.$err.'</div>'; } ?>
							<?php if ($ok && $_POST['action'] == "contactform") { echo '<div class="ok rad25">'.$ok.'</div>'; } ?>
							<?php include (get_template_directory() . '/send-email-form.php'); ?>
						<?php } // if email ?>
						<div class="clear10"></div>
                	</div> <!-- GIRL INFO LEFT -->
                    <div class="girlinfo r">
	                    <h4 class="rad3"><?php _e('Description','escortwp'); ?>:</h4>
                        <div class="clear"></div>
                        <?php echo nl2br(get_the_content()); ?>
                    </div> <!-- GIRL INFO RIGHT -->
					<div class="clear10"></div>
	                <?php
		            if(get_option('hitcounter3')) {
		                echo esc_page_hit_counter(get_the_ID());
		            }

					if (current_user_can('level_10')) {
						echo '<div class="clear10"></div>';
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