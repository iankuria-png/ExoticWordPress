<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );
if (current_user_can('level_10')) { echo get_option('sitelogo'); }
?>