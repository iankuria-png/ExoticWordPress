<?php
ini_set( 'display_errors', 0 );
require( '../../../wp-load.php' );

if (!empty($_FILES)) {
	if($_FILES['Filedata']['error'] > 0) {
		die("Error: ".$_FILES['Filedata']['error']);
	}

	$secret = explode("/", $_REQUEST['folder']);
	$secret = $secret[(count($secret)-1)];
	$secret = preg_replace("/([^a-zA-Z0-9])/", "", $secret);
	// check if an agency added this post
	$escort_post_id = get_option("agency".$secret);

	if ($escort_post_id) {
		// this means the article was added by an agency
	} elseif (get_option($secret)) {
		// this means the article was added by an escort
		$escort_user_id = get_option($secret);
		$escort_post_id = get_option("escortpostid".$escort_user_id);
	} else {
		// if we don't find a secret added by an agency and neither by an independent escort then we die()
		die('We couldn\'t find a profile');
	}

	$current_user = wp_get_current_user();
	$escort_post_data = get_post($escort_post_id);
	if($escort_post_data->post_author != $current_user->ID && !current_user_can('level_10')) die('Not the author');

	// checking the number of uploaded videos
	$videos = get_children( array('post_parent' => $escort_post_id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'video', 'order' => 'ASC', 'orderby' => 'menu_order ID') );
	if (count($videos) >= get_option('maxvideoupload')) { die(); }

	// Get the Size of the File
	$size_bytes = get_option('maxvideouploadsize') * 1024 * 1024;
	$file_size = $_FILES['Filedata']['size'];

	// Make sure that file size is correct
	if ($file_size > $size_bytes){ die("The file is too large"); }
	if ($file_size == "0"){ die("The file can't have 0Kb"); }

    $allowed_extensions = array('mpg', 'wma', 'mov', 'flv', 'mp4', 'avi', 'mkv', 'm4v', 'qt', 'wmv', 'rm');

    $video_extension = explode(".", strtolower($_FILES['Filedata']['name']));
	$video_extension = end($video_extension);
	if ((!in_array(strtolower($video_extension),$allowed_extensions))) { die("Wrong file extension"); }

	set_time_limit(0);

	// creating the upload directory path and the filename
	$upload_folder = get_post_meta($escort_post_id, "upload_folder", true);
	if(!$upload_folder) { die("No upload folder specified in custom meta field"); }
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$upload_dir = wp_upload_dir();
	$targetPath = $upload_dir['basedir']."/".$upload_folder;
	$targetFile_name =  time().rand(1000, 9999);

	if (!is_dir($targetPath)) {
		if (!wp_mkdir_p($targetPath)) { die('Failed to create folder for the images!'); }
	}

	if (move_uploaded_file($tempFile,$targetPath."/".$targetFile_name.".".$video_extension)) {
		$attachment = array(
			'post_mime_type' => 'video/mp4',
			'guid' => get_bloginfo("url")."/wp-content/uploads/".$upload_folder."/".$targetFile_name.".mp4",
			'post_status' => 'inherit',
			'post_parent' => $escort_post_id,
			'post_title' => $targetFile_name.".mp4",
			'post_type' => "attachment"
		);

		// Save the attachment metadata
		$attachment_id = wp_insert_attachment($attachment, $targetPath."/".$targetFile_name.".mp4", $escort_post_id);
		if($video_extension != "mp4") {
			$file = $targetPath."/".$targetFile_name.".".$video_extension;
			$new_file = $targetPath."/".$targetFile_name.".mp4";
			$videoresizeheight = get_option("videoresizeheight") ? get_option("videoresizeheight") : '400';
			$command = "nohup nice -n 10 ffmpeg -i $file -strict experimental -ar 22050 -f mp4 -vf scale=$videoresizeheight:-2 $new_file >/dev/null 2>&1 & echo $!";
			$pid = shell_exec($command);
			if($pid) {
				update_post_meta($attachment_id, "processing", $pid);
				update_post_meta($attachment_id, "original_file", $file);
				update_post_meta($attachment_id, "ffmpeg_command", $command);
			}
		}
		echo 'ok';
	}
}
?>