<?php
ini_set( 'display_errors', 0 );
require( '../../../../wp-load.php' );

global $taxonomy_location_url;
$id = (int)$_GET['id'];
if (!term_exists( $id, $taxonomy_location_url )) {
	die(__('The country you selected doesn\'t exist in our database','escortwp'));
}

//selected city
$city = (int)$_GET['selected'];
if (!term_exists( $city, $taxonomy_location_url )) {
	unset($city);
}

$class = ($_GET['class'] ? " ".substr(preg_replace("/([^a-zA-Z0-9])/", "", $_GET['class']), 0, 20) : "");
$hide_empty = (int)substr($_GET['hide_empty'], 0, 1);
if($_GET['select2'] == "yes") $class .= " select2";
$dropdown_text = $_GET['state'] ? __('Select state','escortwp') : __('Select city','escortwp');
$dropdown_id = $_GET['state'] ? 'state' : 'city';
$args = array(
	'show_option_all'    => '',
	'show_option_none'   => $dropdown_text,
	'orderby'            => 'name', 
	'order'              => 'ASC',
	'show_last_update'   => 0,
	'show_count'         => 0,
	'hide_empty'         => $hide_empty,
	'child_of'           => $id,
	'exclude'            => '',
	'echo'               => 1,
	'selected'           => $city,
	'hierarchical'       => 1, 
	'name'               => $dropdown_id,
	'id'                 => '',
	'class'              => $dropdown_id.$class,
	'depth'              => 1,
	'tab_index'          => 0,
	'taxonomy'           => $taxonomy_location_url,
	'hide_if_empty'      => false );


if ($_GET['is_escort_page'] == "yes" || $_GET['is_tour'] == "yes") {
	$args['name'] = "tour".$args['name'];
	$args['id'] = "tour".$args['id'];
	$args['class'] = "tour".$args['class'].$class;
}
if ($_GET['is_agency_page'] == "yes") {
	$args['name'] = "citymeeting";
	$args['id'] = "citymeeting";
	$args['class'] = "citymeeting".$class;
}
wp_dropdown_categories( $args );
?>