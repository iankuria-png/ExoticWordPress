<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );
$current_user = wp_get_current_user();

if (is_user_logged_in()) {
	$emailhash = get_user_meta( $current_user->ID, "emailhash", true );
	if($emailhash) {
		$last_email_validation_sent = get_user_meta( $current_user->ID, "last_email_validation_sent", true );
		if (time() >= $last_email_validation_sent) {
			$body = __('Hello','escortwp').' '.$current_user->display_name.'<br /><br />
'.__('Before you can use the site you will need to validate your email address.','escortwp').'
'.__('If you don\'t validate your email in the next 3 days your account will be deleted.','escortwp').'<br /><br />
'.__('Please validate your email address by clicking the link bellow','escortwp').':
<a href="'.get_bloginfo('url').'/?ekey='.$emailhash.'">'.get_bloginfo('url').'/?ekey='.$emailhash.'</a>';
			dolce_email("", "", $current_user->user_email, __('Email validation link','escortwp')." ".get_option("email_sitename"), $body);
			$one_min_from_now = time() + 30;
			update_user_meta( $current_user->ID, "last_email_validation_sent", $one_min_from_now );
			echo '<div class="ok rad25">'.__('We sent another validation link to your email address','escortwp')." ".$current_user->user_email."</div>";
		} else {
			echo '<div class="err rad25">'.__('You can only send one email every 30 seconds','escortwp')."</div>";
		} // if time of last email sent
	} else {
		echo '<div class="err rad25">'.__('Your email has already been validated','escortwp')."</div>";
	} // if email hash
} else {
	echo '<div class="err rad25">'.__('You need to be logged in to resend an activation link','escortwp')."</div>";
} // if user logged in
?>