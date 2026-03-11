<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

global $taxonomy_profile_name;
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	// Photo upload START
		// if images are dragged in the upload images div
		var obj = $(".profile-page-no-photos");
		// if images are dragged outside the upload images div
		$(document).on('dragenter', function (e) {
			e.stopPropagation();
			e.preventDefault();
			obj.css('border-color', '#41c7f9'); //blue
		});
		$(document).on('dragover', function (e) {
			e.stopPropagation();
			e.preventDefault();
			obj.css('border-color', '#41c7f9'); //blue
		});
		$(document).on('drop', function (e) {
			e.stopPropagation();
			e.preventDefault();
			obj.css('border-color', '#ff0000');
		});

		obj.on('dragenter', function (e) {
			e.stopPropagation();
			e.preventDefault();
			$(this).css('border-color', '#3FC380'); // blue
		});
		obj.on('dragover', function (e)  {
			e.stopPropagation();
			e.preventDefault();
			$(this).css('border-color', '#3FC380'); // blue
		});
		obj.on('drop', function (e)  {
			e.preventDefault();
			$(this).css('border-color', '#3FC380'); // green
		});

		$('.profile-page-no-photos-click').on('click', function(event) {
			if(<?php echo $photos_left; ?> > 0) {
				$('.profile_photos_button_container input[type="file"]').last().trigger('click');
			} else {
				$('.profile-page-no-photos').html("<p><?php _e('You can\'t upload anymore photos','escortwp'); ?></p>");
			}
		});

	    $('#profile_photos_upload').uploadifive({
			'auto'           : true,
			'debug'          : true,
			'buttonClass'    : 'pinkbutton rad25',
			'buttonText'     : "<?php _e('Upload images','escortwp'); ?>",
			'fileSizeLimit'  : '<?=get_option("maximguploadsize")?>MB',
	        'fileType'       : 'image/*',
	        'formData'       : { 'folder' : '<?php echo get_post_meta(get_the_ID(), "secret", true); ?>' },
			'multi'          : true,
			'queueID'        : 'profile-page-no-photos',
			'queueSizeLimit' : <?php echo $photos_left; ?>,
			'removeCompleted': true,
			'simUploadLimit' : <?php if($photos_left > 10) { echo "10"; } else { echo $photos_left; } ?>,
			'uploadLimit'    : <?php echo $photos_left; ?>,
			'uploadScript'   : '<?php bloginfo('template_url'); ?>/register-independent-upload-photos-process.php',
			'onQueueComplete': function(data) {
				$('.profile-page-no-photos').html('<p>' + data.successful + ' <?=addslashes(__('images have been uploaded','escortwp'))?>.<br /><'+'a href="<?php echo get_permalink(get_the_ID()); ?>"><?=addslashes(__('Refresh the page to see them','escortwp'))?><'+'/a><br /><small><?=addslashes(__('Refreshing the page automatically in 2 seconds','escortwp'))?>.</small><'+'/p>').css('cursor', 'default').removeClass('profile-page-no-photos-click');
				setTimeout(location.reload(), 2000);
			}
		});
	// Photo upload END


	// Video upload START
		var video = $(".profile-page-no-videos");
		//if videos are dragged outside the upload videos div
		$(document).on('dragenter', function (e) {
			e.stopPropagation();
			e.preventDefault();
			video.css('border-color', '#41c7f9'); //blue
		});
		$(document).on('dragover', function (e) {
			e.stopPropagation();
			e.preventDefault();
			video.css('border-color', '#41c7f9'); //blue
		});
		$(document).on('drop', function (e) {
			e.stopPropagation();
			e.preventDefault();
			video.css('border-color', '#ff0000');
		});

		video.on('dragenter', function (e) {
			e.stopPropagation();
			e.preventDefault();
			$(this).css('border-color', '#3FC380'); // blue
		});
		video.on('dragover', function (e)  {
			e.stopPropagation();
			e.preventDefault();
			$(this).css('border-color', '#3FC380'); // blue
		});
		video.on('drop', function (e)  {
			e.preventDefault();
			$(this).css('border-color', '#3FC380'); // green
		});

		$('.profile-page-no-videos-click').on('click', function(event) {
			if(<?php echo $videos_left; ?> > 0) {
				$('.profile_videos_button_container input[type="file"]').last().trigger('click');
			} else {
				$('.profile-page-no-videos').html("<p><?php _e('You can\'t upload anymore videos','escortwp'); ?></p>");
			}
		});

	    $('#profile_videos_upload').uploadifive({
			'auto'           : true,
			'debug'          : true,
			'buttonClass'    : 'pinkbutton rad25',
			'buttonText'     : "<?php _e('Upload videos','escortwp'); ?>",
			'fileSizeLimit'  : '<?=get_option('maxvideouploadsize')?>MB',
	        'fileType'       : 'video/*',
	        'formData'       : { 'folder' : '<?php echo get_post_meta(get_the_ID(), "secret", true); ?>' },
			'multi'          : true,
			'queueID'        : 'profile-page-no-videos',
			'queueSizeLimit' : <?php echo $videos_left; ?>,
			'removeCompleted': true,
			'simUploadLimit' : <?php if($videos_left >= get_option('maxvideoupload')) { echo get_option('maxvideoupload'); } else { echo $videos_left; } ?>,
			'uploadLimit'    : <?php echo $videos_left; ?>,
			'uploadScript'   : '<?php bloginfo('template_url'); ?>/register-independent-upload-videos-process.php',
			'onQueueComplete': function(data) {
				$('.profile-page-no-videos').html('<p>' + data.successful + ' <?=addslashes(__('videos have been uploaded','escortwp'))?>.<br /><'+'a href="<?php echo get_permalink(get_the_ID()); ?>"><?=addslashes(__('Refresh the page to see them','escortwp'))?><'+'/a><br /><small><?=addslashes(__('Refreshing the page automatically in 2 seconds','escortwp'))?>.</small><'+'/p>').css('cursor', 'default').removeClass('profile-page-no-videos-click');
				setTimeout(location.reload(), 2000);
			},
			'onProgress'     : function (file, e) {
	            if (e.lengthComputable) {
	                var percent = Math.round((e.loaded / e.total) * 100);
	                if(percent > 97) {
	                	percent = 97;
	                }
	            }
	            file.queueItem.find('.fileinfo').html(' - ' + percent + '%');
	            file.queueItem.find('.progress-bar').css('width', percent + '%');
			}
		});
	// Video upload END


	// delete verified status image
	$('.verified_status_images .girl span i').on('click', function(){
		var img = $(this).text();
		var arr = img.split('.');
		var imgid = arr[0] + arr[1];
		$(this).parents('.girl').fadeOut("slow");
		$.ajax({
			type: "GET",
			url: "<?php bloginfo('template_url'); ?>/ajax/delete-verified-status-image.php",
			data: "id=" + arr[0],
			success: function(data){
				$('.image_msg').html(data).fadeIn("slow").delay(1000).fadeOut("slow");
			}
		});
	});

	$("a[rel=verified_status_img]").fancybox();

    $('#file_upload_verify').uploadifive({
		'auto'           : true,
		'buttonClass'    : 'pinkbutton rad25',
		'buttonText'     : "<?php _e('Upload image','escortwp'); ?>",
		'fileSizeLimit'  : '<?=get_option("maximguploadsize")?>MB',
        'fileType'       : 'image/*',
        'formData'       : { 'folder' : '<?php echo get_post_meta(get_the_ID(), "secret", true); ?>' },
		'multi'          : false,
		'queueID'        : 'upload-queue-verified-status',
		'queueSizeLimit' : 1,
		'removeCompleted': true,
		'simUploadLimit' : 1,
		'uploadLimit'    : 100,
		'uploadScript'   : '<?php bloginfo('template_url'); ?>/register-independent-verified-status-process.php',
		'onQueueComplete': function(data) {
			$('#status-message-verified-status').html(data.successful + ' <?=addslashes(__('images have been uploaded','escortwp'))?>.<br /><'+'a href="<?php echo get_permalink(get_the_ID()); ?>"><?=addslashes(__('Refresh the page to see them','escortwp'))?><'+'/a>');
			setTimeout(location.reload(), 2000);
		}
	});

	//delete an image from the account
	$('.girlsingle .thumbs .button-delete').on('click', function(){
		var imgid = $(this).parents('.profile-img-thumb').attr('id');
		if(!$('#'+imgid+' img').hasClass('video-image-th')) {
			$('#'+imgid+' img').fadeTo('slow', 0.3).css('z-index', '0');
		}
		$('#'+imgid+' .edit-buttons').html('<div class="preloader r"><'+'/div>');
		loader('#'+imgid+' .edit-buttons .preloader');
		$.ajax({
			type: "GET",
			url: "<?php bloginfo('template_url'); ?>/ajax/delete-uploaded-image.php",
			data: "id=" + imgid,
			success: function(data){
				if(isMobile) {
					$('#'+imgid).css('height', $('#'+imgid).height()).html('<div class="image_msg_girl_single rad3">'+data+'<'+'/div>');
				    $('#'+imgid).animate({ height: $('#'+imgid+' .image_msg_girl_single').height()}, 500);
					setTimeout(function () {
					    $('#'+imgid).parent().slideUp('fast', function() {
					    	$(this).remove();
					    });
						window.scrollTo(0, ($(window).scrollTop() + 1)); //scroll the page just 1px so the other images showup
					}, 2000);
				} else {
					$('#'+imgid+' .edit-buttons').html('<div class="image_msg_girl_single rad3">'+data+'<'+'/div>');
					setTimeout(function () {
						$('#'+imgid+' .edit-buttons').slideUp();
					    $('#'+imgid).parent().fadeOut('150', function() {
					    	$(this).remove();
					    });
					}, 2000);
				}
				$('.profile-page-no-photos p.max-photos b').text(parseInt($('.profile-page-no-photos p.max-photos b').text()) + 1);
			}
		});
	});

	//mark an image as the main image
	$('.girlsingle .thumbs .button-main-image').on('click', function(){
		var imgid = $(this).parents('.profile-img-thumb').attr('id');
		$('.girlsingle .thumbs .edit-buttons .button-main-image').show();
		$('#'+imgid+' .edit-buttons .icon').hide();
		if(!$('#'+imgid+' .edit-buttons .preloader').length) {
			$('#'+imgid+' .edit-buttons').prepend('<div class="preloader r"><'+'/div>');
		} else {
			$('#'+imgid+' .edit-buttons .preloader').show();
		}
		loader('#'+imgid+' .edit-buttons .preloader');
		$.ajax({
			type: "GET",
			url: "<?php bloginfo('template_url'); ?>/ajax/mark-as-main-image.php",
			data: "id=" + imgid,
			success: function(data){
				var json = JSON.parse(data);
				$('#'+imgid+' .edit-buttons .preloader').hide();
				if(!$('#'+imgid+' .edit-buttons .image_msg_girl_single').length) {
					$('#'+imgid+' .edit-buttons').prepend('<div class="image_msg_girl_single rad3"><'+'/div>');
				} else {
					$('#'+imgid+' .edit-buttons .image_msg_girl_single').show();
				}
				$('#'+imgid+' .edit-buttons .image_msg_girl_single').text(json.message);
				setTimeout(function() {
					$('#'+imgid+' .edit-buttons .image_msg_girl_single').fadeOut('slow', function() {
						$('#'+imgid+' .edit-buttons .button-delete').fadeIn('slow');
					});
				}, 2000);
			}
		});
	});
	<?php if(isset($_POST['action']) && $_POST['action'] == 'register' && $err) { echo "$('.girlsingle').slideUp();"."$('.agency_options_editprofile').slideDown();"; } ?>
});
</script>

<?php if (current_user_can('level_10')) { ?>
<div class="agency_options_addanote agency_options_dropdowns registerform">
	<div class="settingspagetitle rad3 l"><?php printf(esc_html__('Add a note to this %s','escortwp'),$taxonomy_profile_name); ?></div>
	<?php closebtn(); ?>
	<div class="clear20"></div>
	<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="form-styling">
    	<input type="hidden" name="action" value="adminnote" />
    	<?php _e('Add a public note about this profile. The text will be shown for all visitors, under the profile name.','escortwp') ?>
    	<div class="form-input col100">
    		<textarea name="adminnote" class="textarea longtextarea"  rows="7"><?php echo $adminnote; ?></textarea>
    		<small><?php _e('html allowed','escortwp'); ?></small>
    	</div> <!-- --> <div class="form-separator"></div>

        <div class="clear10"></div>
        <div class="text-center"><input type="submit" name="submit" value="Add/Update Note" class="pinkbutton rad3" /></div> <!--center-->
	</form>
</div> <!-- ADMIN ADD NOTE -->
<?php } // if admin ?>

<div class="agency_options_delete agency_options_dropdowns text-center">
	<?php closebtn(); ?>
	<div class="clear10"></div>
	<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="text-center">
		<?php
		if(get_the_author_meta('ID') == $userid) {
			printf(esc_html__('Are you sure you want to delete your %s account?','escortwp'),$taxonomy_profile_name);
		} else {
			printf(esc_html__('Are you sure you want to delete this %s account?','escortwp'),$taxonomy_profile_name);
		}
		?><div class="clear5"></div>
		<?php _e('Deleted accounts can\'t be recovered.','escortwp'); ?><div class="clear5"></div>
		<?php _e('To completely delete the account click the button below:','escortwp'); ?><div class="clear20"></div>
		<input type="submit" name="submit" value="<?php printf(esc_html__('Delete %s','escortwp'),$taxonomy_profile_name); ?> <?php the_title(); ?>" class="redbutton submitbutton rad25" />
		<input type="hidden" name="escortidtodelete" value="<?php the_ID(); ?>" />
		<input type="hidden" name="action" value="deleteescort" />
	</form>
</div> <!-- DELETE -->

<div class="agency_options_editprofile agency_options_dropdowns registerform">
	<?php closebtn(); ?>
	<div class="clear"></div>
	<?php include (get_template_directory() . '/register-independent-personal-information-form.php'); ?>
</div> <!-- EDIT PROFILE -->

<div class="agency_options_edittours agency_options_dropdowns managetours"<?php if (isset($err) && $err && $_POST['action'] == 'edittour') { echo ' style="display: block;"'; } ?>>
<script type="text/javascript">
jQuery(document).ready(function($) {
	//delete a city tour
	$('.tour .addedbuttons i').on('click', function(){
		var id = $(this).text();
		$('#tour'+id+' .addedbuttons').html('<b></b>');
		$.ajax({
			type: "GET",
			url: "<?php bloginfo('template_url'); ?>/ajax/delete-tour.php",
			data: "id=" + id,
			success: function(data){
				$('.deletemsg').html(data).fadeIn("slow").delay(1500).fadeOut("slow");
				$('#tour'+id).slideUp("slow");
			}
		});
	});

	//edit a city tour
	$('.tour .addedbuttons em').on("click", function(){
		var id = $(this).text();
		$('.editthetoursform').html('');
		$('#tour'+id+' .addedbuttons em').hide('fast');
		$('#tour'+id+' .addedbuttons').append('<b></b>');
		$.ajax({
			type: "GET",
			url: "<?php bloginfo('template_url'); ?>/ajax/edit-tour.php",
			data: "id=" + id + "&escort_id=<?php the_ID(); ?>&edit_tour_in_escort_page=yes",
			success: function(data){
				$('html,body').animate({ scrollTop: $('.body').offset().top }, { duration: 'slow', easing: 'swing'});
				$('.girlsingle').hide('fast');
				$('.editthetoursform').html(data);
				$('.agency_options_edittours').slideDown();
				$('#tour'+id+' .addedbuttons b').hide('fast');
				$('#tour'+id+' .addedbuttons').append('<em>'+id+'</em>');
				if($(window).width() > "960") { $('.select2').select2(); }
			}
		});
	});

	<?php if(isset($_POST['action']) && $_POST['action'] == 'edittour' && $err != "") { echo "$('.girlsingle').slideUp();"."$('.agency_options_edittours').slideDown();"; } ?>
});
</script>
	<?php closebtn(); ?>
	<div class="clear"></div>
	<?php if (isset($err) && $err && $_POST['action'] == 'edittour') { echo "<div class=\"err rad25\">$err</div>"; } ?>
    <div class="editthetoursform"></div>
</div> <!-- EDIT TOUR -->


<div class="agency_options_addtours agency_options_dropdowns managetours"<?php if (isset($err) && $err && ($_POST['action'] == 'edittour' || $_POST['action'] == 'addtour')) { echo ' style="display: block;"'; } ?>>
	<?php closebtn(); ?>
	<div class="clear"></div>
	<?php if (isset($err) && $err && $_POST['action'] == 'addtour') { echo "<div class=\"err rad25\">$err</div>"; } ?>
    <div class="addthetoursform">
		<?php
		$is_escort_page = "yes";
		$escort_post_id_for_tours = get_the_ID(); //we need this so we can construct a url for the form action. the submitted data will go to that url for processing
	    include (get_template_directory() . '/register-independent-add-tour-form.php');
		?>
    </div>
</div> <!-- EDIT TOUR -->


<div class="agency_options_verified_status agency_options_dropdowns">
	<h3 class="l settingspagetitle"><?php _e('Images you uploaded for verification:','escortwp'); ?></h3>
	<?php closebtn(); ?>
	<div class="clear10"></div>

	<?php
	$verified_photo_url = get_post_meta(get_the_ID(), "verified_status", true);
	if($verified_photo_url) {
		$verified_photo_url = "data:image/jpeg;base64,".$verified_photo_url;
		echo '<div class="verified_status_images upload_photos_page">';
		echo '<div class="image_msg l"></div>';

		echo '<div class="girl col20"><div class="thumb rad3 l"><span class="rad3 col100 nopadding"><a href="'.$verified_photo_url.'" rel="verified_status_img" class="col100 nopadding"><img src="'.$verified_photo_url.'" alt="" class="col100 nopadding" /></a><i class="rad3">'.get_the_ID().'</i></span></div><div class="clear"></div></div> <!-- GIRL -->'."\n";
		echo '</div> <!-- VERIFIED STATUS IMAGES --> <div class="clear10"></div>';
	}
	?>

	<div id="status-message-verified-status">
		<div class="upload_photos_button r">
			<input class="hide" id="file_upload_verify" name="file_upload" type="file" />
		</div>
		<?php _e('Increase the credibility of your account by submitting a verification photo.','escortwp'); ?><br />
		<?php _e('Please upload a photo of yourself holding a paper in your hands with the name of our website.','escortwp'); ?><br />
		<?php _e('All photos are private and will not be published!','escortwp'); ?><br />
		<?php _e('refresh the page to see newly uploaded images','escortwp'); ?><br />
		<?php _e('You can only upload 1 photo.','escortwp'); ?>
    </div> <div class="clear"></div>

	<div id="upload-queue-verified-status"></div>
</div> <!-- VERIFIED STATUS -->