<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );
if (current_user_can('level_10')) {
	$sitelogo = get_option("sitelogo");
	if ($sitelogo) {
		$sitelogo = str_replace(get_bloginfo("url")."/", "", $sitelogo);
		$sitelogo = ABSPATH . $sitelogo;
		unlink($sitelogo);
		delete_option("sitelogo");
	}
	_e('Your image has been deleted','escortwp');
}
?>