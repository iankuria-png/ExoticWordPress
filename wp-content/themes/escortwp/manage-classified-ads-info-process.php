<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

if(get_option("hide6") == "1" && !current_user_can('level_10')) { die(); }

$classifiedadtype = wp_strip_all_tags($_POST['classifiedadtype']);
if (!in_array($classifiedadtype, array('offering', 'looking'))) {
	$err .= __('Please choose an ad type','escortwp')."<br />";
}

$title = wp_strip_all_tags($_POST['adtitle'], true);
if (!$title) { $err .= __('Please write a title','escortwp')."<br />"; }

$description = substr(stripslashes(wp_kses($_POST['description'], array())), 0, 5000);
if (!$description) { $err .= __('Please write a description','escortwp')."<br />"; }

$classifiedademail = $_POST['classifiedademail'];
if ($classifiedademail) {
	if ( !is_email($classifiedademail) ) { $err .= __('The email address seems to be wrong','escortwp')."<br />"; unset($classifiedademail); }
}

$classifiedadphone = wp_strip_all_tags($_POST['classifiedadphone'], true);

if ($classifiedademail == "" && $classifiedadphone == "") {
	$err .= __('Please write an email or a phone number for the ad','escortwp')."<br />";
}

if (!$err) {
	if ($single_page) {
		// Create post object
		$post_classifiedad = array(
			'ID' => get_the_ID(),
			'post_title' => $title,
			'post_content' => $description,
		);
		$post_classifiedad_id = wp_update_post($post_classifiedad);
	} else {
		$classifiedads_cat_id = term_exists( 'Ads', "category" );
		if (!$classifiedads_cat_id) {
			$arg = array('description' => 'Ads');
			wp_insert_term('Ads', "category", $arg);
			$classifiedads_cat_id = term_exists( 'Ads', "category" );
		}
		$classifiedads_cat_id = $classifiedads_cat_id['term_id'];

		$post_status = get_option('manactivclassads') == "1" ? "private" : "publish";

		// Create post object
		$post_classifiedad = array(
			'post_title' => $title,
			'post_content' => $description,
			'post_status' => $post_status,
			'post_category' => array($classifiedads_cat_id),
			'post_author' => $current_user->ID,
			'post_type' => 'ad',
			'ping_status' => 'closed'
		);
		// Insert the post into the database
		$post_classifiedad_id = wp_insert_post( $post_classifiedad );

		$secret = md5($classifiedademail.$classifiedadphone.$title.time().rand(1,999));
		update_post_meta($post_classifiedad_id, "secret", $secret);
		update_post_meta($post_classifiedad_id, "upload_folder", time().rand(1,999));
		update_option("agency".$secret, $post_classifiedad_id);

		if(get_option('ifemail8') == "1") {
			$body = __('Hello','escortwp').',<br /><br />
'.__('A new classified ad has been added on','escortwp').' '.get_option("email_sitename").':<br /><br />
'.__('You can view the ad here','escortwp').':<br />
<a href="'.get_permalink($post_classifiedad_id).'">'.get_permalink($post_classifiedad_id).'</a>';
			dolce_email(null, null, get_bloginfo("admin_email"), __('New classified ad on','escortwp')." ".get_option("email_sitename"), $body);
		}
	} // if $singlepage
	
	update_post_meta($post_classifiedad_id, "email", $classifiedademail);
	update_post_meta($post_classifiedad_id, "phone", $classifiedadphone);
	update_post_meta($post_classifiedad_id, "type", $classifiedadtype);

	wp_redirect(get_permalink($post_classifiedad_id)); exit;
}
?>