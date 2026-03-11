<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

global $taxonomy_profile_name;
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('.agencyeditbuttons .pinkbutton').on('click', function(){
		var id = $(this).attr("id");
		$('.agency_options_dropdowns').slideUp();
		$('.agency_options_'+id).slideDown();
		$('.girlsingle, .agencyeditbuttons').slideUp();
	});
	$('.agency_options_dropdowns .closebtn').on('click', function(){
		$(this).parent().slideUp();
		$('.girlsingle, .agencyeditbuttons').slideDown();
	});

    $('#file_upload').uploadifive({
		'auto'           : true,
		'buttonClass'    : 'pinkbutton rad5',
		'buttonText'     : '<?php _e('Upload images','escortwp'); ?>',
		'fileSizeLimit'  : '<?=get_option("maximguploadsize")?>MB',
        'fileType'       : 'image/*',
        'formData'       : { 'folder' : '<?php echo get_post_meta(get_the_ID(), "secret", true); ?>' },
		'multi'          : true,
		'queueID'        : 'upload-queue',
		'queueSizeLimit' : 20,
		'removeCompleted': true,
		'simUploadLimit' : 10,
		'uploadLimit'    : 20,
		'uploadScript'   : '<?php bloginfo('template_url'); ?>/register-independent-upload-photos-process.php',
		'onQueueComplete': function(data) {
			$('#status-message').html(data.successful + ' <?php _e('images have been uploaded','escortwp'); ?>.<br /><'+'a href="<?php echo get_permalink(get_the_ID()); ?>"><?php _e('Refresh the page to see them','escortwp'); ?><'+'/a>');
			setTimeout(location.reload(), 0);
		}
	});

	//delete an image from the account
	$('.girlsingle .thumbs .button-delete').on('click', function(){
		var imgid = $(this).parents('.profile-img-thumb').hide().attr('id');
		$.ajax({
			type: "GET",
			url: "<?php bloginfo('template_url'); ?>/ajax/delete-uploaded-image.php",
			data: "id=" + imgid,
			success: function(data){
				$('.image-buttons-legend').slideUp().hide().addClass('image_msg_girl_single').html(data).slideDown();
				setTimeout(function () {
				    $('.image-buttons-legend').hide().removeClass('image_msg_girl_single');
				}, 2000);
			}
		});
	});

	<?php if($_POST['action'] == 'addescort' && $err) { echo "$('.girlsingle').slideUp();"."$('.agency_options_editprofile').slideDown();"; } ?>
});
</script>
<div class="agencyeditbuttons">
	<div class="pinkbutton rad25 l" id="uploadimages"><?php _e('Upload Images','escortwp'); ?></div>
    <div class="pinkbutton rad25 l" id="editprofile"><?php _e('Edit Profile','escortwp'); ?></div>
    <div class="pinkbutton redbutton rad25 l" id="delete"><?php _e('Delete','escortwp'); ?></div>
    <div class="clear10"></div>
</div> <!-- AGENCY EDIT BUTTONS -->

<div class="agency_options_uploadimages agency_options_dropdowns">
	<?php closebtn(); ?>
	<div class="clear20"></div>
	<div class="upload_photos_form">
    	<div class="upload_photos_button r">
			<input id="file_upload" name="file_upload" type="file" />
    	</div>
		<div id="status-message"><?php _e('Click the upload button and select all the images you want to upload.','escortwp'); echo "<br />"; printf(esc_html__('You can upload a maximum of 20 images for each blacklisted %s.<br />Refresh the page to see newly uploaded images.','escortwp'),$taxonomy_profile_name); ?></div>
        <div class="clear"></div>
		<div id="upload-queue"></div>
		<div class="clear"></div>
	</div>
</div> <!-- UPLOAD PHOTOS -->

<div class="agency_options_editprofile agency_options_dropdowns">
	<?php closebtn(); ?>
	<?php include (get_template_directory() . '/blacklisted-escorts-form.php'); ?>
</div> <!-- EDIT PROFILE -->

<div class="agency_options_delete agency_options_dropdowns">
	<?php closebtn(); ?>
	<?php printf(esc_html__('Are you sure you want to delete this blacklisted %s?','escortwp'),$taxonomy_profile_name); ?>
	<div class="clear10"></div>
	<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="text-center">
		<input type="submit" name="submit" value="<?php _e('Delete','escortwp'); ?>: <?php the_title(); ?>" class="pinkbutton redbutton submitbutton rad3" />
		<input type="hidden" name="escortidtodelete" value="<?php the_ID(); ?>" />
		<input type="hidden" name="action" value="deleteescort" />
	</form>
</div> <!-- DELETE -->
<div class="clear10"></div>