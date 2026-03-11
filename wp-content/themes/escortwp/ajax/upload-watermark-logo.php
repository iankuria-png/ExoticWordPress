<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );
if (!empty($_FILES) && current_user_can('level_10')) {
	$secret = explode("/", $_REQUEST['folder']);
	$secret = $secret[(count($secret)-1)];
	$secret = preg_replace("/([^a-zA-Z0-9])/", "", $secret);
	if($secret != get_option("secret_to_upload_site_logo")) { die(); }

	$watermarklogourl = get_option("watermarklogourl");
	if ($watermarklogourl) {
		$watermarklogourl = str_replace(get_bloginfo("url")."/", "", $watermarklogourl);
		$watermarklogourl = ABSPATH . $watermarklogourl;
		unlink($watermarklogourl);
	}

	//Get the Size of the File
	$size_bytes = "9242880"; //5MB
	$file_size = $_FILES['Filedata']['size'];

	//Make sure that file size is correct
	if ($file_size > $size_bytes){ die("The file is too large"); }
	if ($file_size == "0"){ die("The file can't have 0Kb"); }

	//check file extension
	$allowed_extensions = array("gif","png","jpg","jpeg");
	$image_mime_type = getimagesize($_FILES['Filedata']['tmp_name']);
	$extension = str_replace("image/", "", $image_mime_type['mime']);
	$extension = str_replace("jpeg", "jpg", $extension);

	//creating the upload directory path and the filename
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$upload_dir = wp_upload_dir();
	$targetPath = $upload_dir['basedir'];
	$targetFile =  "watermark-logo.".$extension;

	if ((!in_array($extension,$allowed_extensions))) { die("Wrong file extension"); }
	if (!is_dir($targetPath)) {
		if (!wp_mkdir_p($targetPath)) { die('Failed to create folder for the images!'); }
	}

	if (move_uploaded_file($tempFile,$targetPath."/".$targetFile)) {
		update_option("watermarklogourl", content_url()."/uploads/".$targetFile);
		echo $targetFile;
	}
}
?>