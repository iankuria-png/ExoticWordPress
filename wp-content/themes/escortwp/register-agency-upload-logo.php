<?php
/*
Template Name: Upload/Edit Agency Logo
*/

global $taxonomy_agency_url, $taxonomy_agency_name;
$current_user = wp_get_current_user();
if (get_option("escortid".$current_user->ID) != $taxonomy_agency_url) { wp_redirect(get_bloginfo("url")); exit; }

get_header(); ?>

		<div class="contentwrapper">
		<div class="body">
        	<div class="bodybox upload_photos_page">
            	<h3><?php printf(esc_html__('Upload/Edit %s Logo','escortwp'),$taxonomy_agency_name); ?></h3>

				<script type="text/javascript">
				jQuery(document).ready(function($) {
				    $('#file_upload').uploadifive({
						'auto'           : true,
						'buttonClass'    : 'pinkbutton rad25',
						'buttonText'     : '<?php _e('Upload logo','escortwp'); ?>',
						'fileSizeLimit'  : '<?=get_option("maximguploadsize")?>MB',
				        'fileType'       : 'image/*',
				        'formData'       : { 'folder' : '<?php echo get_post_meta(get_option("agencypostid".$current_user->ID), "secret", true); ?>' },
						'multi'          : false,
						'queueID'        : 'upload-queue',
						'queueSizeLimit' : 1,
						'removeCompleted': true,
						'simUploadLimit' : 1,
						'uploadLimit'    : 100,
						'uploadScript'   : '<?php bloginfo('template_url'); ?>/register-agency-upload-logo-process.php',
						'onQueueComplete': function(data) {
							$('#status-message').html('<?php _e('Your image has been uploaded','escortwp'); ?>.<br /><'+'a href="<?php echo get_permalink(get_the_ID()); ?>"><?php _e('Refresh the page to see it','escortwp'); ?><'+'/a>');
							setTimeout(location.reload(), 0);
						}
					});

					//delete an image from the account
					$('.upload_photos_page .girl span i').on('click', function(){
						$(this).hide();
						var imgid = $(this).text();
						$("#img"+imgid).fadeOut("slow");
						$.ajax({
							type: "GET",
							url: "<?php bloginfo('template_url'); ?>/ajax/delete-agency-logo.php",
							data: "id=" + imgid,
							success: function(data){
								$('.image_msg').html(data).fadeIn("slow").delay(1000).fadeOut("slow");
							}
						});
					});
				});
				</script>
		    	<div class="clear20"></div>
				<div class="upload_photos_form text-center">
			    	<div class="upload_photos_button center"><input id="file_upload" name="file_upload" type="file" /></div>
			    	<div class="clear10"></div>
					<div id="status-message" class="text-center">
						<?php printf(esc_html__('Click the button and select your %s logo.','escortwp'),$taxonomy_agency_name); ?>
						<br />
						<?php _e('You can only upload a single image.','escortwp'); ?>
					</div>
			        <div class="clear"></div>
					<div id="upload-queue"></div><div class="clear20"></div>
			        <div class="image_msg text-center"></div><div class="clear10"></div>
					<?php
					//check and display the photos uploaded by the user
					global $post;
					$current_user_post = get_option("agencypostid".$current_user->ID);
					$photos = get_children( array('post_parent' => $current_user_post, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID') );
					sort($photos);
					//get the images uploaded
					if(count($photos) > "0") {
						?>
					        <div class="text-center">
								<h4><?php printf(esc_html__('Current %s logo','escortwp'),$taxonomy_agency_name); ?>:</h4>
							</div>
							<div class="clear10"></div>
						<?php
					}
					foreach ($photos as $key => $ag_photo) {
						$ag_photo_th_url = wp_get_attachment_image_src($ag_photo->ID, 'listings-thumb');
						if($ag_photo_th_url[3] != "1") {
							require_once( ABSPATH . 'wp-admin/includes/image.php' );
							$attach_data = wp_generate_attachment_metadata($ag_photo->ID, get_attached_file($ag_photo->ID));
							wp_update_attachment_metadata($ag_photo->ID, $attach_data);
							$ag_photo_th_url = wp_get_attachment_image_src($ag_photo->ID, 'listings-thumb');
						}
						echo '<div class="girl" id="img'.$ag_photo->ID.'"><span class="rad3"><img src="'.$ag_photo_th_url[0].'" class="rad3" alt="" /><i class="rad5">'.$ag_photo->ID.'</i></span><div class="clear"></div></div> <!-- GIRL -->'."\n";
					}
					?>
				</div> <!-- UPLOAD PHOTOS FORM-->
                <div class="clear"></div>
            </div> <!-- BODY BOX -->
            <div class="clear"></div>
        </div> <!-- BODY -->
        </div> <!-- contentwrapper -->

		<?php get_sidebar("left"); ?>
		<?php get_sidebar("right"); ?>
    	<div class="clear"></div>
<?php get_footer(); ?>