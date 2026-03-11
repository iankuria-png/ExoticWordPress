<?php
/*
Template Name: Register Independent Verified Status
*/

global $taxonomy_profile_url;
$current_user = wp_get_current_user();
if (get_option("escortid".$current_user->ID) != $taxonomy_profile_url) { wp_redirect(get_bloginfo("url")); exit; }
$current_user_post_id = get_option("escortpostid".$current_user->ID);
$verified_photo = get_post_meta($current_user_post_id, "verified_status", true);
get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox girlsingle upload_verified_photo">
        	<h3><?php _e('Verify Account','escortwp'); ?></h3>
			<script type="text/javascript">
			jQuery(document).ready(function($) {
			    $('#file_upload').uploadifive({
					'auto'           : true,
					'buttonClass'    : 'pinkbutton rad25',
					'buttonText'     : '<?php _e('Upload image','escortwp'); ?>',
					'fileSizeLimit'  : '<?=get_option("maximguploadsize")?>MB',
			        'fileType'       : 'image/*',
			        'formData'       : { 'folder' : '<?php echo get_post_meta($current_user_post_id, "secret", true); ?>' },
					'multi'          : false,
					'queueID'        : 'upload-queue',
					'queueSizeLimit' : 1,
					'removeCompleted': true,
					'simUploadLimit' : 1,
					'uploadLimit'    : 100,
					'uploadScript'   : '<?php bloginfo('template_url'); ?>/register-independent-verified-status-process.php',
					'onQueueComplete': function(data) {
						$('#status-message').html('<?php _e('Your image has been uploaded','escortwp'); ?>.<br /><'+'a href="<?php echo get_permalink(get_the_ID()); ?>"><?php _e('Refresh the page to see it','escortwp'); ?><'+'/a><br /><br /><?php _e('Refreshing the page automatically in 2 seconds','escortwp'); ?>');
						setTimeout(location.reload(), 0);
					}
				});

				//delete an image from the account
				$('.thumbs .button-delete').on('click', function(){
					$('.profile-img-thumb img').fadeTo('slow', 0.3).css('z-index', '0');
					$('.profile-img-thumb .edit-buttons').html('<div class="preloader r"><'+'/div>');
					loader('.profile-img-thumb .edit-buttons .preloader');
					$.ajax({
						type: "GET",
						url: "<?php bloginfo('template_url'); ?>/ajax/delete-verified-status-image.php",
						data: "id=<?=$current_user_post_id?>",
						success: function(data){
							$('.profile-img-thumb .edit-buttons').html('<div class="image_msg_girl_single">'+data+'<'+'/div>');
							setTimeout(function () {
								$('.profile-img-thumb .edit-buttons').slideUp();
							    $('.profile-img-thumb a').animate({
							    	width: 0},
							    	500, function() {
							    	$(this).hide();
							    });
							}, 2000);
						}
					});
				});
			});
			</script>
			<div class="upload_photos_form">
				<div id="status-message">
			    	<div class="upload_photos_button r">
						<input id="file_upload" name="file_upload" type="file" />
			    	</div>
				<?php _e('Increase the credibility of your account by submitting a verification photo.','escortwp'); ?><br />
				<?php _e('Please upload a photo of yourself holding a paper in your hands with the name of our website.','escortwp'); ?><br />
				<?php _e('All photos are private and will not be published!','escortwp'); ?><br />
				<?php _e('You can only upload 1 photo.','escortwp'); ?>
		        </div>
		        <div class="clear"></div>
				<div id="upload-queue"></div><div class="clear20"></div>
		        <div class="image_msg l"></div><div class="clear"></div>
				<?php
				$verified_photo_url = get_post_meta($current_user_post_id, "verified_status", true);
				if($verified_photo_url) {
					$verified_photo_url = "data:image/jpeg;base64,".$verified_photo_url;
				?>
					<h4><?php _e('Images you uploaded for verification:','escortwp'); ?></h4>
					<div class="clear10"></div>
					<div class="thumbs col30 m50">
						<span class="profile-img-thumb l">
							<span class="edit-buttons"><span class="icon button-delete icon-cancel"></span></span>
							<a href="<?=$verified_photo_url?>" data-fancybox="profile-photo">
								<img src="<?=$verified_photo_url?>" class="rad3" />
							</a>
						</span>
					</div>
				<?php
				} // if($verified_photo_url)
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