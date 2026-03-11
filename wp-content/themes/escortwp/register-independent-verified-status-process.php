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

	//Get the Size of the File
	$size_bytes = get_option('maximguploadsize') * 1024 * 1024;
	$file_size = $_FILES['Filedata']['size'];

	//Make sure that file size is correct
	if ($file_size > $size_bytes){ die("The file is too large"); }
	if ($file_size == "0"){ die("The file can't have 0Kb"); }

	//check file extension
	$allowed_extensions = array("gif","png","jpg","jpeg");
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
		$watermark_png_file = str_replace(home_url()."/", "", $watermark_png_file);
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

				$watermark = imagecreatefrompng($watermark_png_file); //watermark image
				add_opacity_to_watermark($watermark, 90);

				imagecopy($new_canvas, $watermark, $watermark_x, $watermark_y, 0, 0, $stamp_img_width, $stamp_img_height); //merge image
				imagejpeg($new_canvas, $_FILES['Filedata']['tmp_name'] , 90);
				$extension = "jpg";
			}
		}
	} // if(get_option("watermarklogourl"))

	$tempFile = $_FILES['Filedata']['tmp_name'];
    $image_base64 = base64_encode(file_get_contents($tempFile));
    update_post_meta($escort_post_id, "verified_status", $image_base64);

	// send email to admin and notify him of the uploaded file
	$body = __('Hello','escortwp').',<br /><br />
	'.__('Someone has uploaded images to the "Verified status" section and is awaiting for your verification','escortwp').':<br /><br />
	'.__('Go to this page and check the image','escortwp').':<br />
	<a href="'.get_permalink($escort_post_id).'">'.get_permalink($escort_post_id).'</a><br /><br />';
	dolce_email("", "", get_bloginfo("admin_email"), __('Verified status image uploaded on','escortwp')." ".get_option("email_sitename"), $body);
	update_post_meta($escort_post_id, "verified_status_emailsent_when", time());

    echo 'ok';
}
?>