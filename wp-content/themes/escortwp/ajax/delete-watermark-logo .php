<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );
if (current_user_can('level_10')) {
	$watermarklogourl = get_option("watermarklogourl");
	if ($watermarklogourl) {
		$watermarklogourl = str_replace(get_bloginfo("url")."/", "", $watermarklogourl);
		$watermarklogourl = ABSPATH . $watermarklogourl;
		unlink($watermarklogourl);
		delete_option("watermarklogourl");
	}
	_e('Your image has been deleted','escortwp');
}
?>