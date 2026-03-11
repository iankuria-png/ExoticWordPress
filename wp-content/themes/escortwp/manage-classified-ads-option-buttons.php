<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }
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
				$('#status-message').html(data.successful + ' <?php _e('images have been uploaded','escortwp'); ?>.<br /><'+'a href="<?php echo get_permalink(get_the_ID()); ?>"><?php _e('Refresh the page to see them','escortwp'); ?><'+'/a><br /><small><?php _e('Refreshing the page automatically in 2 seconds','escortwp'); ?>.</small>');
				setTimeout(location.reload(), 2000);
			}
		});

		//delete an image from the account
		$('.girlsingle .thumbs .button-delete').on('click', function(){
			var imgid = $(this).parents('.profile-img-thumb').attr('id');
			$('#'+imgid+' img').fadeTo('slow', 0.3).css('z-index', '0');
			$('#'+imgid+' .edit-buttons').html('<div class="preloader r"><'+'/div>');
			loader('#'+imgid+' .edit-buttons .preloader');
			$.ajax({
				type: "GET",
				url: "<?php bloginfo('template_url'); ?>/ajax/delete-uploaded-image.php",
				data: "id=" + imgid,
				success: function(data){
					$('#'+imgid+' .edit-buttons').html('<div class="image_msg_girl_single">'+data+'<'+'/div>');
					setTimeout(function () {
						$('#'+imgid+' .edit-buttons').slideUp();
					    $('#'+imgid).animate({
					    	width: 0},
					    	500, function() {
					    	$(this).hide();
					    });
					}, 2000);
				} // success
			}); // ajax
		}); // click

		<?php if($_POST['action'] == 'addclassifiedad' && $err) { ?>
			$('.agency_options_dropdowns').slideUp();
			$('.agency_options_editclassifiedad').slideDown();
			$('.girlsingle, .agencyeditbuttons').slideDown();
		<?php } ?>
	});
</script>
<div class="agencyeditbuttons text-center">
	<div class="pinkbutton rad25 center" id="uploadimages"><?php _e('Add Images','escortwp'); ?></div>
    <div class="pinkbutton rad25 center" id="editclassifiedad"><?php _e('Edit This Ad','escortwp'); ?></div>
    <div class="pinkbutton redbutton rad25 center" id="delete"><?php _e('Delete','escortwp'); ?></div>
	<div class="clear10"></div>
</div> <!-- AGENCY EDIT BUTTONS -->

<div class="agency_options_uploadimages agency_options_dropdowns">
	<?php closebtn(); ?>
	<div class="clear10"></div>
	<div class="upload_photos_form">
    	<div class="upload_photos_button r">
			<input id="file_upload" name="file_upload" type="file" />
    	</div>
		<div id="status-message"><?php _e('Click the upload button and select all the images you want to upload.<br />You can upload a maximum of 20 images for this classified ad.<br />Refresh the page to see newly uploaded images.','escortwp'); ?></div>
        <div class="clear"></div>
		<div id="upload-queue"></div>
		<div class="clear"></div>
	</div>
</div> <!-- UPLOAD PHOTOS -->

<div class="agency_options_editclassifiedad agency_options_dropdowns">
	<?php closebtn(); ?>
	<?php
	$single_page = "yes";
    include (get_template_directory() . '/manage-classified-ads-form.php');
	?>
</div> <!-- EDIT PROFILE -->

<div class="agency_options_delete agency_options_dropdowns">
	<?php _e('Are you sure you want to delete this classified ad?','escortwp'); ?>
	<?php closebtn(); ?>
	<div class="clear10"></div>
	<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="text-center">
		<input type="submit" name="submit" value="<?php _e('Delete this ad','escortwp'); ?>" class="redbutton submitbutton rad25" />
		<input type="hidden" name="classifiedadidtodelete" value="<?php the_ID(); ?>" />
		<input type="hidden" name="action" value="deleteclassifiedad" />
	</form>
</div> <!-- DELETE -->
<div class="clear10"></div>