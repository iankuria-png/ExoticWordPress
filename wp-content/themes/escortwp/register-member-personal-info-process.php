<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

$current_user = wp_get_current_user();

if (!$member_edit_page) {
    $user = preg_replace("/([^a-zA-Z0-9])/", "", $_POST['user']);
	if ($user) {
		if (strlen($user) < 4 || strlen($user) > 30) { $err .= __('Your username must be between 4 and 30 characters','escortwp')."<br />"; } else {
			if (username_exists($user)) { $err .= __('This username already exists. Please write another one','escortwp')."<br />"; }
		}
	} else {
		$err .= __('The username field is empty','escortwp')."<br />";
	}

	$pass = $_POST['pass'];
	if ($pass) {
		if (strlen($pass) < 6 || strlen($pass) > 50) {
			$err .= __('Your password must be between 6 and 50 characters','escortwp')."<br />";
		} else {
			if ( false !== strpos( stripslashes($pass), "\\" ) ) {
				$err .= __('Passwords may not contain the character "\"','escortwp')."<br />";
			}
		}
	} else {
		$err .= __('The password field is empty','escortwp')."<br />";
	}
}


$membername = wp_strip_all_tags($_POST['membername'], true);
if (!$membername) { $err .= __('Please write your name','escortwp')."<br />"; }

$memberemail = $_POST['memberemail'];
if (!$memberemail) { $err .= __('Please write your email address','escortwp')."<br />"; } else {
	if ( !is_email($memberemail) ) { $err .= __('Your email address seems to be wrong','escortwp')."<br />"; }
	if ( email_exists($memberemail) && $memberemail != $current_user->user_email ) {
		$err .= __('The email address has been used by someone else already','escortwp')."<br />";
		if ($member_edit_page == "yes") {
			$agencyemail = $current_user->user_email;
		}
	}
	if(!$err && $memberemail != $current_user->user_email) {
		$new_memberemail = $_POST['memberemail'];
	}
}

//spam/emails field must be empty to continue
$emails = $_POST['emails'];
if ($emails != "") { $err = ".<br />"; }

if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && !is_user_logged_in() && get_option("recaptcha4")) { $err .= verify_recaptcha(); }

$tos_accept = (int)$_POST['tos_accept'];
$tos_page_data = get_post(get_option('tos_page_id'));
$data_protection_page_data = get_post(get_option('data_protection_page_id'));
if(($tos_page_data || $data_protection_page_data) && !is_user_logged_in() && $tos_accept != "1") {
	$err .= __('You need to agree to our terms and conditions in order to register','escortwp')."<br />";
}

if (!$err) {
	if ($member_edit_page == "yes") {
		$new_user_id = $current_user->ID;
	} else {
		$new_user_id = wp_create_user( $user, $pass, $memberemail );
		//set an email hash so the user needs to validate his email in order to use the site
		$emailhash = md5($new_user_id.$user.$memberemail);
		update_user_meta( $new_user_id, "emailhash", $emailhash );
		//set user type
		escortwp_child_update_dynamic_user_option("escortid".$new_user_id, "member");

		//send email to member
		$body = __('Hello','escortwp').' '.$user.',<br /><br />
'.__('Before you can use the site you will need to validate your email address.','escortwp').'
'.__('If you don\'t validate your email in the next 3 days your account will be deleted.','escortwp').'<br /><br />
'.__('Please validate your email address by clicking the link bellow','escortwp').':
<a href="'.get_bloginfo('url').'/?ekey='.$emailhash.'">'.get_bloginfo('url').'/?ekey='.$emailhash.'</a>';
		dolce_email("", "", $memberemail, __('Email validation link','escortwp')." ".get_option("email_sitename"), $body);

		wp_clear_auth_cookie(); //delete the cookies of the user if he is already logged in. for example if he is the admin
		wp_set_auth_cookie($new_user_id, true, ''); //add login cookies to the user so we can identify him
	}

	wp_update_user( array ('ID' => $new_user_id, 'nickname' => $membername, 'display_name' => $membername, 'user_url' => $website) );
	if($new_memberemail) {
		wp_update_user( array ('ID' => $new_user_id, 'user_email' => $new_memberemail) );
	}

	if ($member_edit_page == "yes") {
		//if the user is just changing the profile info then redirect him back to the profile page
		$ok = "ok";
	} else {
		//if the use is just registering then redirect him to the register page
		wp_redirect(get_permalink(get_option('member_register_page_id'))); exit;
	}
}
?>
