<?php
ini_set( 'display_errors', 0 );
require( '../../../wp-load.php' );
if (!empty($_FILES)) {
	$secret = explode("/", $_REQUEST['folder']);
	$secret = $secret[(count($secret)-1)];
	$secret = preg_replace("/([^a-zA-Z0-9])/", "", $secret);
	//check if an agency added this post
	$escort_post_id = get_option("agency".$secret);

	if ($escort_post_id) {
		//this means the article was added by an agency
	} elseif (get_option($secret)) {
		//this means the article was added by an escort
		$escort_user_id = get_option($secret);
		$escort_post_id = get_option("escortpostid".$escort_user_id);
	} else {
		//if we don't find a secret added by an agency and neither by an independent escort then we die()
		die('We couldn\'t find a profile');
	}

	$current_user = wp_get_current_user();
	$escort_post_data = get_post($escort_post_id);
	if($escort_post_data->post_author != $current_user->ID && !current_user_can('level_10')) die('Not the author');

	//checking the number of uploaded photos
	$photos = get_children( array('post_parent' => $escort_post_id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID') );
	if (count($photos) >= get_option('maximgupload')) { die(); }

	//Get the Size of the File
	$size_bytes = get_option('maximguploadsize') * 1024 * 1024; //5MB
	$file_size = $_FILES['Filedata']['size'];

	//Make sure that file size is correct
	if ($file_size > $size_bytes){ die("The file is too large"); }
	if ($file_size == "0"){ die("The file can't have 0Kb"); }

	//check file extension
	$allowed_extensions = array("gif","png","jpg","jpeg","webp");
	$image_mime_type = getimagesize($_FILES['Filedata']['tmp_name']);
	$extension = str_replace("image/", "", $image_mime_type['mime']);

	if(get_option("maximgpxsize")) {
		$max_size = get_option("maximgpxsize") ? get_option("maximgpxsize") : "";
		$image_temp = $_FILES['Filedata']['tmp_name']; //file temp
		$image_type = $_FILES['Filedata']['type']; //file type
		switch(strtolower($image_type)){ //determine uploaded image type 
				//Create new image from file
				case 'image/png': 
					$image_resource =  imagecreatefrompng($image_temp);
					break;
				case 'image/gif':
					$image_resource =  imagecreatefromgif($image_temp);
					break;          
				case 'image/jpeg': case 'image/jpeg':
					$image_resource = imagecreatefromjpeg($image_temp);
					break;
				case 'image/webp': 
					$image_resource =  imagecreatefromwebp($image_temp);
					break;
				default:
					$image_resource = false;
			}
		
		if($image_resource){
			//Copy and resize part of an image with resampling
			list($img_width, $img_height) = getimagesize($image_temp);
			
		    //Construct a proportional size of new image
			$image_scale        = min($max_size / $img_width, $max_size / $img_height); 
			$new_image_width    = ceil($image_scale * $img_width);
			$new_image_height   = ceil($image_scale * $img_height);
			$new_canvas         = imagecreatetruecolor($new_image_width , $new_image_height);

		    //Resize image with new height and width
			if(imagecopyresampled($new_canvas, $image_resource , 0, 0, 0, 0, $new_image_width, $new_image_height, $img_width, $img_height)) {
				imagejpeg($new_canvas, $_FILES['Filedata']['tmp_name'] , 90);
			}
		}
	} // if(get_option("maximgpxsize"))

	if(get_option("watermarklogourl")) {
		$watermark_png_file = get_option("watermarklogourl");
		$watermark_png_file = str_replace(get_bloginfo("url")."/", "", $watermark_png_file);
		$watermark_png_file = ABSPATH . $watermark_png_file;

		$image_temp = $_FILES['Filedata']['tmp_name']; //file temp
		$image_type = $_FILES['Filedata']['type']; //file type
		switch(strtolower($image_type)){ //determine uploaded image type 
				//Create new image from file
				case 'image/png': 
					$image_resource =  imagecreatefrompng($image_temp);
					break;
				case 'image/gif':
					$image_resource =  imagecreatefromgif($image_temp);
					break;          
				case 'image/jpeg': case 'image/pjpeg':
					$image_resource = imagecreatefromjpeg($image_temp);
					break;
				default:
					$image_resource = false;
			}
		
		if($image_resource){
			//Copy and resize part of an image with resampling
			list($img_width, $img_height) = getimagesize($image_temp);
			
		    //Construct a proportional size of new image
			$new_image_width    = $img_width;
			$new_image_height   = $img_height;
			$new_canvas         = imagecreatetruecolor($new_image_width , $new_image_height);

		    //Resize image with new height and width
			if(imagecopyresampled($new_canvas, $image_resource , 0, 0, 0, 0, $new_image_width, $new_image_height, $img_width, $img_height)) {
				list($stamp_img_width, $stamp_img_height) = getimagesize($watermark_png_file);
				$watermark_position = get_option("watermark_position") ? get_option("watermark_position") : "cc";
				switch ($watermark_position) {
					case 'tl':
							// top left
							$watermark_x = "10";
							$watermark_y = "10";
						break;
					case 'tc':
							// top center
							$watermark_x = ($new_image_width/2)-($stamp_img_width/2);
							$watermark_y = "10";
						break;
					case 'tr':
							// top right
							$watermark_x = $new_image_width-$stamp_img_width-10;
							$watermark_y = "10";
						break;

					case 'cl':
							// center left
							$watermark_x = "10";
							$watermark_y = ($new_image_height/2)-($stamp_img_height/2);
						break;
					case 'cc':
							// center
							$watermark_x = ($new_image_width/2)-($stamp_img_width/2);
							$watermark_y = ($new_image_height/2)-($stamp_img_height/2);
						break;
					case 'cr':
							// center right
							$watermark_x = $new_image_width-$stamp_img_width+10;
							$watermark_y = ($new_image_height/2)-($stamp_img_height/2);
						break;

					case 'bl':
							// bottom right
							$watermark_x = "10";
							$watermark_y = $new_image_height-$stamp_img_height-10;
						break;
					case 'bc':
							// bottom center
							$watermark_x = ($new_image_width/2)-($stamp_img_width/2);
							$watermark_y = $new_image_height-$stamp_img_height-10;
						break;
					case 'br':
							// bottom right
							$watermark_x = $new_image_width-$stamp_img_width-10;
							$watermark_y = $new_image_height-$stamp_img_height-10;
						break;
				}

				$imageTypeWatermark = mime_content_type($watermark_png_file);
				switch(strtolower($imageTypeWatermark)){ //determine uploaded image type 
					//Create new image from file
					case 'image/png': 
						$watermark = imagecreatefrompng($watermark_png_file); //watermark image
						break;

					case 'image/gif':
						$watermark = imagecreatefromgif($watermark_png_file); //watermark image
						break;          

					case 'image/jpeg': case 'image/pjpeg':
						$watermark = imagecreatefromjpeg($watermark_png_file); //watermark image
						break;

					default:
						$watermark = false;
				}
				add_opacity_to_watermark($watermark, 90);

				imagecopy($new_canvas, $watermark, $watermark_x, $watermark_y, 0, 0, $stamp_img_width, $stamp_img_height); //merge image
				imagejpeg($new_canvas, $_FILES['Filedata']['tmp_name'] , 90);
				$extension = "jpg";
			}
		}
	} // if(get_option("watermarklogourl"))

	// if the image is too small we'll enlarge it
	if($image_mime_type[0] < "400" || $image_mime_type[1] < "600") {
		//part of the code is from: http://www.geekality.net/2011/05/01/php-how-to-proportionally-resize-an-uploaded-image/?PHPSESSID=xekem26zag4i03rqh1eg8l43dluwc9tl371l44s3
		if($extension == "jpg" || $extension == "jpeg") {
    	    $image = imagecreatefromjpeg($_FILES['Filedata']['tmp_name']);
    	} elseif ($extension == "png") {
    	    $image = imagecreatefrompng($_FILES['Filedata']['tmp_name']);
    	} elseif ($extension == "webp") {
    	    $image = imagecreatefromwebp($_FILES['Filedata']['tmp_name']);
		} elseif ($extension == "gif") {
    	    $image = imagecreatefromgif($_FILES['Filedata']['tmp_name']);
		} else {
			die("Unsupported type");
		}

		// Target dimensions
		$max_width = "400";
		$max_height = "600";

		// Get current dimensions
		$old_width  = imagesx($image);
		$old_height = imagesy($image);

		// Calculate the scaling we need to do to fit the image inside our frame
		$scale = max($max_width/$old_width, $max_height/$old_height);

		// Get the new dimensions
		$new_width  = ceil($scale*$old_width);
		$new_height = ceil($scale*$old_height);

		// Create new empty image
		$new = imagecreatetruecolor($new_width, $new_height);

		// Resize old image into new
		imagecopyresampled($new, $image, 0, 0, 0, 0, $new_width, $new_height, $old_width, $old_height);
		//replace temp image with the resized one

   	    imagejpeg($new, $_FILES['Filedata']['tmp_name'], 99);
   	    $extension = "jpg";
	} // if the image is too small

	//creating the upload directory path and the filename
	$extension = str_replace("jpeg", "jpg", $extension);
	$upload_folder = get_post_meta($escort_post_id, "upload_folder", true);
	if(!$upload_folder) { die("No upload folder specified in custom meta field"); }
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$upload_dir = wp_upload_dir();
	$targetPath = $upload_dir['basedir']."/".$upload_folder;
	$targetFile =  time().rand(1000, 9999).".".$extension;

	if ((!in_array($extension,$allowed_extensions))) { die("Wrong file extension"); }
	if (!is_dir($targetPath)) {
		if (!wp_mkdir_p($targetPath)) { die('Failed to create folder for the images!'); }
	}

	if (move_uploaded_file($tempFile,$targetPath."/".$targetFile)) {
		$attachment = array(
			'post_mime_type' => $image_mime_type['mime'],
			'guid' => content_url()."/uploads/".$upload_folder."/".$targetFile,
			'post_status' => 'inherit',
			'post_parent' => $escort_post_id,
			'post_title' => $targetFile,
			'post_type ' => "attachment"
		);

		// Save the attachment metadata
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attachment_id = wp_insert_attachment($attachment, $targetPath."/".$targetFile, $escort_post_id);
		$attach_data = wp_generate_attachment_metadata($attachment_id, $targetPath."/".$targetFile);
		wp_update_attachment_metadata($attachment_id, $attach_data);
		echo 'ok';
	}
}
?>