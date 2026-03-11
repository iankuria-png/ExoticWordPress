<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );

$profileid = (int)$_POST['profileid'];
$reason = substr(sanitize_text_field($_POST['reason']), 0, 300);
$recaptcha = sanitize_text_field($_POST["g-recaptcha-response"]);

if(!$profileid) { $err .= __('No ID provided', 'escortwp')."<br />"; }
if(!$reason) { $err .= __('Please write a reason for your report', 'escortwp')."<br />"; }

if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && !is_user_logged_in() && get_option("recaptcha6")) {
	if(!$recaptcha) {
		$err .= __('Please click the reCaptcha field', 'escortwp')."<br />";
	} else {
		$err .= verify_recaptcha();
	}
}

if($err) {
	echo json_encode(array('status'=>'err', 'msg'=>$err));
} else {
	// send email to admin
	$body = __('Hello admin','escortwp').',<br /><br />
'.__('Someone has reported a profile','escortwp').':<br />
'.__('Profile url','escortwp').': <b>'.get_permalink($profileid).'</b><br />
'.__('Reason for reporting','escortwp').':<br /><b>'.$reason.'</b>';
	if(is_user_logged_in()) {
		$current_user_email = wp_get_current_user();
		$current_user_email = $current_user_email->data->user_email;
	} else {
		$current_user_email = "visitor";
	}
	$body .= '<br />'.__('User who reported the profile','escortwp').': <b>'.$current_user_email.'</b>';
	dolce_email("", "", get_option("email_siteemail"), __('Someone has reported a profile on','escortwp')." ".get_option("email_sitename"), $body);

	echo json_encode(array('status'=>'ok', 'msg'=>__('Thank you for your report. The admin of the website has been notified.', 'escortwp')));
}
?>