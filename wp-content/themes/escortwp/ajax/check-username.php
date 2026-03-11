<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );

if (strlen($_GET['user']) > 3 && strlen($_GET['user']) <= 30) {
	if (username_exists(sanitize_user($_GET['user']))) { //sanitizing is done by WordPress
		echo '<span class="checkusererr">'.__('This username already exists.','escortwp').'<br />'.__('Please choose another one','escortwp').'</span>';
	} else {
		echo '<span class="checkuserok">'.__('This username is available','escortwp').'</span>';
	}
}
?>