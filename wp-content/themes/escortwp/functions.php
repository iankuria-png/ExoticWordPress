<?php
$error_reporting = 0;
// $error_reporting = 1; // uncomment this line to turn on error reporting
if($error_reporting == '1') {
	error_reporting( E_ALL );
}
define('error_reporting', $error_reporting);
ini_set( 'display_errors', $error_reporting);

add_action('after_setup_theme', 'escortwp_theme_translate');
function escortwp_theme_translate(){
    load_theme_textdomain('escortwp', get_template_directory() . '/languages');
}

add_theme_support('woocommerce');
add_theme_support('wc-product-gallery-slider');
add_theme_support('wc-product-gallery-zoom');
add_theme_support('wc-product-gallery-lightbox');


if(isset($_GET['install']) && $_GET['install'] == "yes") { // only hide the header bar if we are on the installation page
	function dolce_temp_remove_admin_login_header() { remove_action('wp_head', '_admin_bar_bump_cb'); }
	add_action('get_header', 'dolce_temp_remove_admin_login_header');
}

// Add new constant that returns true if WooCommerce is active
define('is_woocommerce_active', class_exists('WooCommerce'));

if(!current_user_can('level_10')) add_filter('show_admin_bar', '__return_false'); // disable admin bar
remove_action('wp_head', 'wp_generator'); // remove the generator tag
remove_action('wp_head', 'print_emoji_detection_script', 7); // disable emojies
remove_action('wp_print_styles', 'print_emoji_styles'); // disable emojies
remove_action('wp_head', 'rsd_link'); // removes EditURI/RSD (Really Simple Discovery) link.
remove_action('wp_head', 'wlwmanifest_link'); // removes wlwmanifest (Windows Live Writer) link.
remove_action('wp_head', 'rest_output_link_wp_head', 10);

// setting a value to istheme to make sure all the included files can't be loaded by themselves in the browser
define('isdolcetheme', 1);
$theme_version = "360";

// escortwp.zip for Julian Chavulimo
$license_key = 'rvrua4v4c6o3e277y9b7xlr3aqvew6x9y42t5fk6rsyv89e2h567797yj7hj15uib2m5ue7wzfas50cw';

include(get_template_directory().'-child/functions-settings.php');
include(get_template_directory().'/functions-payments.php');
include(get_template_directory().'/functions-mobile-detect.php');

if (!function_exists('escortwp_age_gate_should_render')) {
	/**
	 * Legacy theme age gate render decision.
	 * The plugin can short-circuit this via filter when it takes over rendering.
	 */
	function escortwp_age_gate_should_render() {
		$should_render = !isset($_COOKIE['tos18']) && get_option('tos18') == '1';

		return (bool) apply_filters(
			'escortwp_age_gate_should_render',
			$should_render,
			array(
				'option_value' => (string) get_option('tos18'),
				'has_cookie' => isset($_COOKIE['tos18']) && $_COOKIE['tos18'] === 'yes',
				'source' => 'theme-legacy',
			)
		);
	}
}

$detect = new Mobile_Detect;
if($detect->isMobile() && !$detect->isTablet()) {
	$isphone = true;
}

// add the necesarry javascript and css files to the header of the theme
function add_js_css() {
	global $taxonomy_profile_url, $taxonomy_agency_url, $isphone, $taxonomy_location_url;
	wp_dequeue_style( 'wp-block-library' );
	wp_enqueue_style('open-sans-font', '//fonts.googleapis.com/css?family=Open+Sans:400,600,700&display=swap');
	wp_enqueue_style('main-css-file', get_bloginfo('stylesheet_url'), array());

	if(isset($_GET['install']) && $_GET['install'] == "yes") {
		wp_enqueue_style('install-theme', get_bloginfo('template_url').'/css/style-install-theme.css', array());
	}
	wp_enqueue_style('icon-font', get_bloginfo('template_url').'/css/icon-font/style.css', array());
	wp_enqueue_style('responsive', get_bloginfo('template_url').'/css/responsive.css', array());
	if($isphone) {
		wp_enqueue_style('responsive', get_bloginfo('template_url').'/css/responsive-isphone.css');
	}
	wp_enqueue_style('select2', get_bloginfo('template_url').'/css/select2.min.css');
	wp_enqueue_script('select2', get_bloginfo('template_url').'/js/select2.min.js', array('jquery'));
	wp_enqueue_script('dolcejs', get_bloginfo('template_url').'/js/dolceescort.js', array('jquery'));
	wp_enqueue_script('js-cookie', get_bloginfo('template_url').'/js/js.cookie.js');
	wp_enqueue_script('jquery-uploadifive', get_bloginfo('template_url').'/js/jquery.uploadifive.min.js', array('jquery'));
	wp_enqueue_script('jquery-mobile-custom', get_bloginfo('template_url').'/js/jquery.mobile.custom.min.js', array('jquery'));

	wp_enqueue_script('checkator', get_bloginfo('template_url').'/js/checkator.jquery.js', array('jquery'));

	if (get_post_type() == $taxonomy_profile_url || get_post_type() == "b".$taxonomy_profile_url || get_post_type() == "ad" || get_the_ID() == get_option('escort_verified_status_page_id')) {
		wp_enqueue_script('jquery-fancybox', get_bloginfo('template_url').'/js/jquery.fancybox.min.js', array('jquery'));
		wp_enqueue_style('jquery-fancybox', get_bloginfo('template_url').'/css/jquery.fancybox.min.css');
	}
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) { 
        wp_enqueue_script( 'comment-reply');
    }
	if (get_option("showheaderslider") == 1) {
		if (get_option("showheadersliderall") == 1) {
			$showslider = 1;
		} else {
			if(is_front_page() && get_option("showheadersliderfront") == 1) { $showslider = 1; }
			if(is_tax($taxonomy_location_url) && get_option("showheaderslideresccat") == 1) { $showslider = 1; }
			if(get_post_type() == $taxonomy_profile_url && get_option("showheadersliderescprof") == 1) { $showslider = 1; }
			if(get_post_type() == $taxonomy_agency_url && get_option("showheaderslideragprof") == 1) { $showslider = 1; }
			if(is_page(get_option('search_page_id')) && get_option("showheaderslidersearch") == 1) { $showslider = 1; }

			if(get_option("showheadersliderct") == 1) {
				if (is_page(get_option('city_tours_page_id')) || get_post_type() == 'tour') { $showslider = 1; }
			}

			if(get_option("showheadersliderrev") == 1) {
				if (is_page(get_option('nav_reviews_page_id')) || is_page(get_option('nav_reviews_agencies_page_id')) || get_post_type() == 'review') { $showslider = 1; }
			}

			if(get_option("showheadersliderads") == 1) {
				if (is_page(get_option('see_all_ads_page_id')) || is_page(get_option('see_offering_ads_page_id')) || is_page(get_option('see_looking_ads_page_id')) || get_post_type() == 'ad') { $showslider = 1; }
			}
		}
	} // if slider activated
	if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && (in_array(get_the_ID(), array(get_option('contact_page_id'), get_option('escort_reg_page_id'), get_option('agency_reg_page_id'), get_option('member_register_page_id'))) || (is_single() && (get_option('recaptcha5') || get_option('recaptcha6'))))) {
		wp_enqueue_script('reCAPTCHA', '//www.google.com/recaptcha/api.js');
	}
	if(isset($showslider) && $showslider == "1") {
		wp_enqueue_style('owl-carousel-css', get_bloginfo('template_url').'/css/owl.carousel.min.css');
		wp_enqueue_script('owl-carousel-js', get_bloginfo('template_url').'/js/owl.carousel.min.js');
		define('showslider', 1);
	} else {
		define('showslider', 0);
	}
	//if edit registration fields page - enable iOS style checkboxes
	if(get_option('edit_registration_form_escort') == get_the_ID()) {
		wp_enqueue_style('ios-checkboxes', get_bloginfo('template_url').'/css/ios-checkboxes-switchery.min.css');
		wp_enqueue_script('ios-checkboxes', get_bloginfo('template_url').'/js/ios-checkboxes-switchery.min.js', array('jquery'));
	}
}

add_filter('body_class', 'dolce_extra_body_class');
// Add specific CSS class by filter
function dolce_extra_body_class($classes) {
	global $taxonomy_profile_name, $taxonomy_agency_name;
	if(in_array(get_post_type(), array($taxonomy_profile_name, $taxonomy_agency_name))) {
		$classes[] = 'single-profile-page';
	}
	return $classes;
}

// Add theme settings pages links to the admin bar on frontpage
add_action('admin_bar_menu', 'add_toolbar_items', 100);
function add_toolbar_items($admin_bar){
	global $taxonomy_profile_name, $taxonomy_agency_name, $taxonomy_location_url;
	$admin_bar->add_menu(array(
		'id'    => 'wp-escortwp-menu',
		'title' => '<span class="icon icon-menu"></span> '.__('EscortWP Menu','escortwp'),
		'href'  => '',
		'meta'  => array(
			'title' => __('EscortWP Menu','escortwp'),
		),
	));
	$admin_bar->add_menu(array(
		'id'    => 'wp-escortwp-menu-site-settings',
		'parent'=> 'wp-escortwp-menu',
		'title' => '<span class="icon icon-cog-alt"></span> '.__('Site Settings','escortwp'),
		'href'  => get_permalink(get_option('site_settings_page_id')),
	));

	$admin_bar->add_menu(array(
		'id'    => 'wp-escortwp-menu-content-settings',
		'parent'=> 'wp-escortwp-menu',
		'title' => '<span class="icon icon-cog-alt"></span> '.__('Content settings','escortwp'),
		'href'  => get_permalink(get_option('content_settings_page_id')),
	));

	$admin_bar->add_menu(array(
		'id'    => 'wp-escortwp-menu-reg-form',
		'parent'=> 'wp-escortwp-menu',
		'title' => '<span class="icon icon-cog-alt"></span> '.__('Registration Form','escortwp'),
		'href'  => get_permalink(get_option('edit_registration_form_escort')),
	));

	$admin_bar->add_menu(array(
		'id'    => 'wp-escortwp-menu-payment-settings',
		'parent'=> 'wp-escortwp-menu',
		'title' => '<span class="icon icon-dollar"></span> '.__('Payment Settings','escortwp'),
		'href'  => get_permalink(get_option('edit_payment_settings_page_id')),
	));

	$admin_bar->add_menu(array(
		'id'    => 'wp-escortwp-menu-email-settings',
		'parent'=> 'wp-escortwp-menu',
		'title' => '<span class="icon icon-mail"></span> '.__('Email Settings','escortwp'),
		'href'  => get_permalink(get_option('email_settings_page_id')),
	));

	$admin_bar->add_menu(array(
		'id'    => 'wp-escortwp-menu-edit-user-types',
		'parent'=> 'wp-escortwp-menu',
		'title' => '<span class="icon icon-user"></span> '.__('Edit User Types','escortwp'),
		'href'  => get_permalink(get_option('edit_user_types')),
	));

	$admin_bar->add_menu(array(
		'id'    => 'wp-escortwp-menu-add-countries',
		'parent'=> 'wp-escortwp-menu',
		'title' => '<span class="icon icon-plus-circled"></span> '.__('Add countries','escortwp'),
		'href'  => admin_url( 'edit-tags.php?taxonomy='.$taxonomy_location_url),
	));

	$admin_bar->add_menu(array(
		'id'    => 'wp-escortwp-menu-generate-demo-data',
		'parent'=> 'wp-escortwp-menu',
		'title' => '<span class="icon icon-plus-circled"></span> '.__('Generate demo data','escortwp'),
		'href'  => get_permalink(get_option('generate_demo_data_page')),
	));
}

function check_if_we_are_showing_the_slider() {
	add_action('wp_enqueue_scripts', 'add_js_css');
}
add_action('wp', 'check_if_we_are_showing_the_slider');
function login_stylesheet() {
	wp_enqueue_style('main-css-file', get_template_directory_uri().'/style.css', array());
	wp_enqueue_style('responsive-css', get_template_directory_uri().'/css/responsive.css', array());
	wp_enqueue_script('dolcejs', get_template_directory_uri().'/js/dolceescort.js', array('jquery'));
}
add_action('login_enqueue_scripts', 'login_stylesheet');


function login_logo_url() { return get_bloginfo( 'url'); }
add_filter('login_headerurl', 'login_logo_url');

function login_logo_url_title() { return get_bloginfo('name'); }
add_filter('login_headertitle', 'login_logo_url_title');

//change the look of the wordpress login form
function change_login_form() {
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.login .button-primary').removeAttr('class').addClass('pinkbutton rad3 l');
		$('.login form, .login .message, .login #login_error, .login #backtoblog a, .login #nav a').addClass('rad3');
		$('.login form').addClass('form-styling');
		$('.login form .input').parent().addClass('form-input col100');

		<?php
		if(get_option("sitelogo")) {
			$site_logo = '<img src="'.get_option("sitelogo").'" alt="'.get_bloginfo('name').'" id="logo_img" />';
		?>
			$('.login h1 a').html('<?php echo $site_logo; ?>');
			$("#logo_img").load(function() {
				$('.login h1 a').css('height', $('#logo_img').height());
			});
		<?php } ?>
	});
	</script>
	<?php
}
add_action('login_head', 'change_login_form');

function dolce_email($fromname, $fromemail, $to, $subj, $body) {
	if (!$fromname) {
		$fromname = get_option("email_sitename");
	}
	if (!$fromemail) {
		$fromemail = get_option("email_siteemail");
	}
	$body = str_replace("<br /><br />", "<br />", nl2br($body));
	$body = $body."<br />".nl2br(get_option("email_signature"));

    $headers[] = "From: $fromname <$fromemail>";
	$headers[] = 'Content-Type: text/html; charset=UTF-8';
    wp_mail($to, $subj, $body, $headers);
}

function change_password_reset_email($message){
	$signature = "<br />".nl2br(get_option("email_signature"));
	return nl2br($message).$signature;
}
add_filter('retrieve_password_message', 'change_password_reset_email');

function dolce_register_sidebars() {
	register_sidebar(array(
		'name' => 'Sidebar Left',
		'id' => 'widget-sidebar-left',
		'before_widget' => '<div id="%1$s" class="widgetbox rad3 widget %2$s">',
		'after_widget' => '</div><div class="clear10"></div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>'
	));
	register_sidebar(array(
		'name' => 'Left Ads',
		'id' => 'widget-left-ads',
		'before_widget' => '<div id="%1$s" class="widgetadbox rad3 widget %2$s">',
		'after_widget' => '</div><div class="clear10"></div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>'
	));
	register_sidebar(array(
		'name' => 'Sidebar Right',
		'id' => 'widget-sidebar-right',
		'before_widget' => '<div id="%1$s" class="widgetbox rad3 widget %2$s">',
		'after_widget' => '</div><div class="clear10"></div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>'
	));
	register_sidebar(array(
		'name' => 'Right Ads',
		'id' => 'widget-right-ads',
		'before_widget' => '<div id="%1$s" class="widgetadbox rad3 widget %2$s">',
		'after_widget' => '</div><div class="clear10"></div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>'
	));
	register_sidebar(array(
		'name' => 'Footer',
		'id' => 'widget-footer',
		'before_widget' => '<div id="%1$s" class="widgetbox rad3 widget %2$s l">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>'
	));
	register_sidebar(array(
		'name' => 'Header Language Switcher Only',
		'id' => 'header-language-switcher',
		'before_widget' => '<li class="header-language-switcher">',
		'after_widget' => '</li>',
		'before_title' => '',
		'after_title' => ''
	));
}
add_action('widgets_init', 'dolce_register_sidebars');


function dolce_create_post_taxonomies() {
	global $taxonomy_profile_url, $taxonomy_agency_url, $taxonomy_location_url;
	register_taxonomy(
		$taxonomy_location_url,
		array($taxonomy_profile_url,$taxonomy_agency_url,'tour'),
		array(
			'hierarchical' => true,
			'label' => __('Countries','escortwp'),
			'sort' => true,
			'show_ui' => true,
			'query_var' => true,
			'args' => array( 'orderby' => 'term_order' ),
            'rewrite' => array('slug' => $taxonomy_location_url, 'hierarchical' => true, 'with_front' => false),
		)
	);
}
add_action('init', 'dolce_create_post_taxonomies');

function dolce_create_post_types() {
	global $taxonomy_profile_name, $taxonomy_profile_url, $taxonomy_agency_name, $taxonomy_agency_url, $taxonomy_location_url;
	$post_types = array(
		array(ucfirst($taxonomy_profile_name), $taxonomy_profile_url, 'Add another '.$taxonomy_profile_name.' to the site'),
		array('Blacklisted '.ucfirst($taxonomy_profile_name), 'b'.$taxonomy_profile_url, 'Add another '.$taxonomy_profile_name.' to the blacklist'),
		array(ucfirst($taxonomy_agency_name), $taxonomy_agency_url, 'Add an '.$taxonomy_agency_name.' to the site'),
		array('City Tours', 'tour', 'Add city-tour'),
		array('Blacklisted Clients', 'bclient', 'Add a client to the blacklist'),
		array('Reviews', 'review', 'Add a review to an '.$taxonomy_profile_name),
		array('Classified Ads', 'ad', 'Add classified ad'),
	);

	foreach($post_types as $t) {
		$args = array(
			'label' => $t[0],
            'rewrite' => array('slug' => $t[1], 'hierarchical' => true, 'with_front' => false),
			'description ' => $t[2],
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 80,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_icon'	=> get_template_directory_uri().'/i/admin-menu-icon.png',
			'has_archive' => true,
			'supports' => array('title', 'editor', 'author', 'excerpt', 'custom-fields'),
			'taxonomies' => array($taxonomy_location_url)
		);
		if($t[1] == "escort") {
			$args['rewrite'] = array('slug' => _x('escort', 'Escort profile slug', 'escortwp'), 'hierarchical' => true, 'with_front' => false);
		}
		if($t[1] == "agency") {
			$args['rewrite'] = array('slug' => _x('agency', 'Agency profile slug', 'escortwp'), 'hierarchical' => true, 'with_front' => false);
		}
		register_post_type( $t[1], $args );
	}
}
add_action('init', 'dolce_create_post_types');

function edit_body_css_classes($classes){
	//remove the class "single-ad" from the body because AdBLockPlus hides the whole page when the class is present
	if(is_singular() && get_post_type() == "ad") {
		foreach($classes as $key=>$class) {
			if($class == "single-ad") {
				unset($classes[$key]);	
				break;
			}
		}
	}

	//add the "isphone" css class if the device is a phone
	global $isphone;
	if($isphone) { $classes[] = 'isphone'; }

	return $classes;
}
add_filter('body_class', 'edit_body_css_classes');


add_action( 'generate_rewrite_rules', 'register_product_rewrite_rules' );
function register_product_rewrite_rules( $wp_rewrite ) {
	global $taxonomy_location_url, $taxonomy_profile_url, $taxonomy_agency_url;
    $new_rules = array( 
		// agency/name/paged/2/
		$taxonomy_agency_url.'/([^/]+)/'.__('paged', 'escortwp').'/(\d{1,})/?$' => 'index.php?'.$taxonomy_agency_url.'=' . $wp_rewrite->preg_index(1) . '&paged=' . $wp_rewrite->preg_index(2),

        // escorts-from/location/
        $taxonomy_location_url.'/([^/]+)/?$' => 'index.php?'.$taxonomy_location_url.'=' . $wp_rewrite->preg_index(1),

        // escorts-from/location/page/2/
        $taxonomy_location_url.'/([^/]+)/'.$GLOBALS['wp_rewrite']->pagination_base.'/(\d{1,})/?$' => 'index.php?'.$taxonomy_location_url.'=' . $wp_rewrite->preg_index(1) . '&page=' . $wp_rewrite->preg_index(2), // match paginated results for a sub-category archive

        // escorts-from/country/city/page/2/
        $taxonomy_location_url.'/([^/]+)/([^/]+)/'.$GLOBALS['wp_rewrite']->pagination_base.'/(\d{1,})/?$' => 'index.php?'.$taxonomy_location_url.'=' . $wp_rewrite->preg_index(2) . '&page=' . $wp_rewrite->preg_index(3), // match paginated results for a sub-category archive

        // escorts-from/country/state/city/page/2/
        $taxonomy_location_url.'/([^/]+)/([^/]+)/([^/]+)/'.$GLOBALS['wp_rewrite']->pagination_base.'/(\d{1,})/?$' => 'index.php?'.$taxonomy_location_url.'=' . $wp_rewrite->preg_index(3) . '&page=' . $wp_rewrite->preg_index(4), // match paginated results for a sub-category archive
    );
    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

function filter_post_type_link($link, $post) {
	global $taxonomy_location_url, $taxonomy_profile_url;
    if ($post->post_type != $taxonomy_profile_url)
        return $link;

    if ($cats = get_the_terms($post->ID, $taxonomy_location_url)) {
        $link = str_replace('%product_category%', get_taxonomy_parents(array_pop($cats)->term_id, $taxonomy_location_url, false, '/', true), $link); // see custom function defined below\
        $link = str_replace('//', '/', $link);
        $link = str_replace('http:/', 'http://', $link);
        $link = str_replace('https:/', 'https://', $link);
    }
    return $link;
}
add_filter('post_type_link', 'filter_post_type_link', 10, 2);

function get_taxonomy_parents($id, $taxonomy, $link = false, $separator = '/', $nicename = false, $visited = array()) {    
    $chain = '';   
    $parent = get_term($id, $taxonomy);

    if (is_wp_error($parent)) {
        return $parent;
    }

    if ($nicename) {
        $name = $parent -> slug;        
    } else {
		$name = $parent -> name;
    }

    if ($parent -> parent && ($parent -> parent != $parent -> term_id) && !in_array($parent -> parent, $visited)) {    
        $visited[] = $parent -> parent;    
        $chain .= get_taxonomy_parents($parent -> parent, $taxonomy, $link, $separator, $nicename, $visited);
    }

    if ($link) {
    } else {
        $chain .= $name . $separator;    
    }
    return $chain;    
}

function fix_product_subcategory_query($query) {
	global $taxonomy_profile_url, $taxonomy_location_url;
    if ( isset( $query['post_type'] ) && $taxonomy_profile_url == $query['post_type'] ) {
        if ( isset( $query[$taxonomy_profile_url] ) && $query[$taxonomy_profile_url] && isset( $query[$taxonomy_location_url] ) && $query[$taxonomy_location_url] ) {
            $query_old = $query;
            // Check if this is a paginated result(like search results)
            if ( 'page' == $query[$taxonomy_location_url] ) {
                $query['paged'] = $query['name'];
                unset( $query[$taxonomy_location_url], $query['name'], $query[$taxonomy_profile_url] );
            }
            // Make it easier on the DB
            $query['fields'] = 'ids';
            $query['posts_per_page'] = 1;
            // See if we have results or not
            $_query = new WP_Query( $query );
            if ( ! $_query->posts ) {
                $query = array( $taxonomy_location_url => $query[$taxonomy_profile_url] );
                if ( isset( $query_old[$taxonomy_location_url] ) && 'page' == $query_old[$taxonomy_location_url] ) {
                    $query['paged'] = $query_old['name'];
                }
            }
        }
    }
    return $query;
}
add_filter( 'request', 'fix_product_subcategory_query', 10 );


// restrict access to the admin dashboard for users other than admin
function restricted_pages(){
	$current_user = wp_get_current_user();
	if(!current_user_can('level_10') && !defined('DOING_AJAX')) {
		wp_redirect(get_bloginfo("url")); die();
	}
	if(defined('dolce_demo_theme') && $current_user->ID != "1"){
		wp_redirect(get_bloginfo("url")); die();
	}
}
add_action('admin_init', 'restricted_pages');


function get_first_image($id, $s = "1") {
	global $thumb_sizes;
	$w = $thumb_sizes[$s][0]; //width
	$h = $thumb_sizes[$s][1]; //height

	$main_image_id = get_post_meta($id, "main_image_id", true);
	$upload_folder = get_post_meta($id, "upload_folder", true);
	if (wp_get_attachment_image_src($main_image_id, 'full')) {
		$imgurl = wp_get_attachment_image_src($main_image_id, 'full');
		$imgurl = $imgurl[0];
	} else {
		global $wpdb;
		$photos = get_children(array( 'post_parent' => $id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'ID', 'numberposts' => '1' ));
		$photos = reset($photos);
		if ($photos) {
			$main_image_id = $photos->ID;
			update_post_meta(get_the_ID(), "main_image_id", $main_image_id);
			$imgurl = wp_get_attachment_image_src($main_image_id, 'full');
			$imgurl = $imgurl[0];
		} else {
			$no_image = "no-image.png";
			if($s == "2") { $no_image = "no-image.png"; }
			$imgurlth = get_bloginfo("template_url")."/i/".$no_image;
			return $imgurlth;
		}
	}

	$explode_url = explode("wp-content", $imgurl);
	$photo_url = explode(".", $explode_url[1]);
	$extension = $photo_url[count($photo_url)-1];
	unset($photo_url[count($photo_url)-1]);
	$imgurlth = "wp-content".implode(".", $photo_url)."-".$w."x".$h.".".strtolower($extension);

	if (!file_exists(ABSPATH.$imgurlth)) {
		$image = wp_get_image_editor(ABSPATH."wp-content".$explode_url[1]);
		if ( !is_wp_error($image) ) {
    		$image->resize( $w, $h, true );
    		$image->save(ABSPATH.$imgurlth);
		}
	}

	$imgurlth = site_url()."/".$imgurlth;
	return $imgurlth;
}



function get_escort_rating($id, $reviewcount="") {
	$args = array(
		'post_type' => 'review',
		'posts_per_page' => '-1',
		'meta_query' => array( array('key' => 'escortid', 'value' => $id, 'compare' => '=', 'type' => 'NUMERIC') )
	);
	$q = new WP_Query( $args );
	if ( $q->have_posts() ) {
		$rating = array();
		foreach ($q->posts as $post) {
			$rating[] = get_post_meta($post->ID, "rateescort", true);
		}
	}
	if(!isset($rating)) $rating = array();
	$num = (count($rating) == 0) ? "0" : array_sum($rating) / count($rating);

	if ($reviewcount == true) {
		return $q->post_count;
	} else {
		return str_replace(".", "", round_to_half($num));
	}
}



function get_agency_rating($id, $reviewcount="") {
	$args = array(
		'post_type' => 'review',
		'posts_per_page' => '-1',
		'meta_query' => array( array('key' => 'agencyid', 'value' => $id, 'compare' => '=', 'type' => 'NUMERIC') )
	);
	$rating = array();
	$q = new WP_Query( $args );
	if ( $q->have_posts() ) {
		$rating = array();
		foreach ($q->posts as $post) {
			$rating[] = get_post_meta($post->ID, "rateagency", true);
		}
	}

	$num = count($rating) == 0 ? "0" : array_sum($rating) / count($rating);

	if($reviewcount == true) {
		return $q->post_count;
	} else {
		return str_replace(".", "", round_to_half($num));
	}
}

function round_to_half($num) {
	if ($num >= ($half = ($ceil = ceil($num))- 0.5) + 0.25) {
		return $ceil;
	} else if ($num < $half - 0.25) {
		return floor($num);
	} else {
		return $half;
	}
}



function get_escort_labels($id) {
	$escort_label = array();

	$post = get_post($id);
	$date = $post->post_date;
	$daysago = date("Y-m-d H:i:s", strtotime("-".get_option('newlabelperiod')." days"));
	if ($date > $daysago) { $escort_label[] = '<span class="label label-new rad3 pinkdegrade">'.__('NEW','escortwp').'</span>'; }

	if (get_post_status($id) == "private") { $escort_label[] = '<span class="label label-private rad3 reddegrade">'.__('PRIVATE','escortwp').'</span>'; }

	$verified = get_post_meta($id, "verified", true);
	if ($verified == "1") { $escort_label[] = '<span class="label label-verified rad3 greendegrade">'.__('VERIFIED','escortwp').'</span>'; }

	$featured = get_post_meta($id, "featured", true);
	if ($featured == "1") { $escort_label[] = '<span class="vip-div in-loop">'.__('','escortwp').'</span>'; }
//	if ($featured == "1") { $escort_label[] = '<span class="label label-featured rad3 pinkdegrade">'.__('VIP','escortwp').'</span>'; }

	$escort_label[] = show_online_label_html($post->post_author);

	if($escort_label) {
		return '<span class="labels">'.implode('<div class="clear"></div>', array_filter($escort_label)).'</span>';
	}
}

function get_featured_escort_labels($id) {
	$escort_label = "";

	$verified = get_post_meta($id, "verified", true);
	if ($verified == "1") { $escort_label .= '<span class="verified greendegrade rad3">'.__('VERIFIED','escortwp').'</span>'; }

	$post = get_post($id);
	$date = $post->post_date;
	$daysago = date("Y-m-d H:i:s", strtotime("-".get_option('newlabelperiod')." days"));
	if ($date > $daysago) { $escort_label .= '<span class="new pinkdegrade rad3">'.__('NEW','escortwp').'</span>'; }
	            
	if($escort_label) {
		echo '<span class="labels l">'.$escort_label.'</span>';
	}
	return $escort_label;
}

function show_post_count($id) {
	global $wpdb, $taxonomy_profile_url;
	$sql = $wpdb->prepare("SELECT COUNT(ID) FROM `".$wpdb->posts."` WHERE `post_status` = 'publish' AND `post_type` = '".$taxonomy_profile_url."' AND `post_author`= %d", $id);
	$posts = $wpdb->get_var($sql);
	return $posts;
}

function did_user_post_review($user_id, $escort_id) {
	$args = array(
		'post_type' => 'review',
		'posts_per_page' => '1',
		'author' => $user_id,
		'meta_query' => array(
			'relation' => 'OR',
			array('key' => 'escortid', 'value' => $escort_id, 'compare' => '=', 'type' => 'NUMERIC'),
			array('key' => 'agencyid', 'value' => $escort_id, 'compare' => '=', 'type' => 'NUMERIC')
		)
	);
	$q = query_posts($args);
	if(count($q) > 0) {
		return true;
	} else {
		return false;
	}
}


//send email to escort when a review is published
function send_email_when_review_is_saved($post_id) {
	$email_sent = get_post_meta($post_id, 'email_sent', true);
	if ($email_sent != "yes") {
		$review_data = get_post($post_id);
		$escort_id = get_post_meta($post_id, 'escortid', true);
		$escort_data = get_post($escort_id);
		$escort_author = $escort_data->post_author;
		$escort_info = get_userdata($escort_author);
		$escort_email = $escort_info->user_email;
		$review_url = get_permalink($post_id);
		if ($review_data->post_type == "review" && $review_data->post_status == 'publish' && $escort_id > 1) {
			$body = __('Hello','escortwp').',<br /><br />
'.__('Someone added a new review to your profile','escortwp').'.<br />'.__('To read it please click the link below','escortwp').':<br />
'.$review_url;
			if($randomly_generated_data != "randomly_generated_data") {
				dolce_email(null, null, $escort_email, __('You have a new review on','escortwp')." ".get_option("email_sitename"), $body);
			}
			update_post_meta($post_id, 'email_sent', 'yes');
		}
	}
}
add_action('save_post', 'send_email_when_review_is_saved',10,2);

//add our own custom post types to the rss feed
function dolce_rssfeed($rss) {
	global $taxonomy_profile_url, $taxonomy_agency_url;
	if (isset($rss['feed']) && !isset($rss['post_type'])) {
		$rss['post_type'] = array($taxonomy_profile_url, $taxonomy_agency_url, 'ad');
	}
	return $rss;
}
add_filter('request', 'dolce_rssfeed');


//get available language list
function get_langs_list($lang) {
	$dir = get_template_directory()."/lang/";
   	if ($dh = opendir($dir)) {
       	while (($file = readdir($dh)) !== false) {
			if ($file != "." && $file != ".." && substr($file, -3, 3) == "php" && filetype($dir.$file) == "file") {
				$langs[] = preg_replace("/([^a-zA-Z0-9])/", "", str_replace(".php", "", $file));
			}
        }
   	    closedir($dh);
    }

    if($langs) {
	    sort($langs);
	    foreach ($langs as $key => $file) {
			$selected = ($file == $lang) ? ' selected="selected"' : '';
			echo '<option value="'.$file.'"'.$selected.'>'.ucfirst(strtolower($file)).'</option>'."\n";
			unset($selected);
	    }
    }
}

function check_if_user_has_validated_his_email() {
	global $taxonomy_profile_name, $taxonomy_profile_url, $taxonomy_agency_name, $taxonomy_agency_url;
	// check if the user has clicked the validation link
	if(isset($_GET['ekey'])) {
		global $wpdb;
		$ekey = preg_replace("/([^a-zA-Z0-9])/", "", $_GET['ekey']);
		$user_id = $wpdb->get_var($wpdb->prepare("SELECT `user_id` FROM `".$wpdb->usermeta."` WHERE `meta_key` = 'emailhash' AND `meta_value` = %s LIMIT 1", $ekey));
		$user_info = get_userdata($user_id);
		if($user_id) {
			delete_user_meta( $user_id, "emailhash", $ekey );
			delete_user_meta( $user_id, "last_email_validation_sent" );

			$user_type = get_option("escortid".$user_id);
			if($user_type == $taxonomy_agency_url) {
				$agency_post_id = get_option("agencypostid".$user_id);
				// if the admin has choosen to activate the profiles manually
				$post_status = "publish";
				if(get_option("manactivagprof") == "1" || payment_plans('agreg','price')) {
					$post_status = "private";
				}
				$post_agency = array( 'ID' => $agency_post_id, 'post_status' => $post_status );
				wp_update_post( $post_agency );

				// send email to agency
				$body = __('Hello','escortwp').' '.$user_info->display_name.'<br /><br />
'.__('Your account is now active on','escortwp').' '.get_option("email_sitename").'<br /><br />
'.__('Account information','escortwp').':<br />
'.__('type','escortwp').': <b>'.$taxonomy_agency_name.'</b><br />
'.__('username','escortwp').': <b>'.$user_info->user_login.'</b><br />
'.__('password','escortwp').': <b>('.__('hidden','escortwp').')</b><br /><br />
'.__('You can view your account here','escortwp').':<br />
<a href="'.get_permalink($agency_post_id).'">'.get_permalink($agency_post_id).'</a>';
				dolce_email("", "", $user_info->user_email, __('Welcome to','escortwp')." ".get_option("email_sitename"), $body);


				// send email to admin
				$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('A new %s has registered on','escortwp'),$taxonomy_agency_name).' '.get_option("email_sitename").':<br /><br />
'.__('Account information','escortwp').':<br />
'.__('username','escortwp').': <b>'.$user_info->user_login.'</b><br />
'.__('password','escortwp').': <b>('.__('hidden','escortwp').')</b><br /><br />
'.__('You can view the account here','escortwp').':<br />
<a href="'.get_permalink($agency_post_id).'">'.get_permalink($agency_post_id).'</a>';
				if (get_option("ifemail2") == "1") {
					dolce_email(null, null, get_bloginfo("admin_email"), sprintf(esc_html__('New %s registration on','escortwp'),$taxonomy_agency_name)." ".get_option("email_sitename"), $body);
				}
			} elseif ($user_type == $taxonomy_profile_url) {
				$escort_post_id = get_option("escortpostid".$user_id);
				$post_status = "publish";
				if(get_option("manactivindescprof") == "1" || payment_plans('indescreg','price')) {
					$post_status = "private";
				}
				$post_escort = array( 'ID' => $escort_post_id, 'post_status' => $post_status );
				wp_update_post($post_escort);

				// send email to escort
				$body = __('Hello','escortwp').' '.$user_info->display_name.'<br /><br />
'.__('Your account is now active on','escortwp').' '.get_option("email_sitename").'.<br /><br />
'.__('Account information','escortwp').':<br />
'.__('type','escortwp').': <b>'.sprintf(esc_html__('independent %s','escortwp'),$taxonomy_profile_name).'</b><br />
'.__('username','escortwp').': <b>'.$user_info->user_login.'</b><br />
'.__('password','escortwp').': <b>('.__('hidden','escortwp').')</b><br /><br />
'.__('You can view your account here','escortwp').':<br />
<a href="'.get_permalink($escort_post_id).'">'.get_permalink($escort_post_id).'</a>';
				dolce_email("", "", $user_info->user_email, __('Welcome to','escortwp')." ".get_option("email_sitename"), $body);

				// send email to admin
				$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('A new %s has been added on','escortwp'),$taxonomy_profile_name).' '.get_option("email_sitename").':<br /><br />
'.__('Account information','escortwp').':<br />
'.__('type','escortwp').': <b>'.sprintf(esc_html__('independent %s','escortwp'),$taxonomy_profile_name).'</b><br />
'.__('username','escortwp').': <b>'.$user_info->user_login.'</b><br />
'.__('password','escortwp').': <b>('.__('hidden','escortwp').')</b><br /><br />
'.__('You can view the account here','escortwp').':<br />
<a href="'.get_permalink($escort_post_id).'">'.get_permalink($escort_post_id).'</a>';
				if (get_option("ifemail3") == "1") {
					dolce_email(null, null, get_bloginfo("admin_email"), sprintf(esc_html__('New %s on','escortwp'),$taxonomy_profile_name)." ".get_option("email_sitename"), $body);
				}
			} elseif ($user_type == "member") {
				// send email to member
				$body = __('Hello','escortwp').' '.$user_info->display_name.'<br /><br />
'.__('Your account is now active on','escortwp').' '.get_option("email_sitename").'.<br /><br />
'.__('Account information','escortwp').':<br />
'.__('type','escortwp').': <b>'.__('member','escortwp').'</b><br />
'.__('username','escortwp').': <b>'.$user_info->user_login.'</b><br />
'.__('password','escortwp').': <b>('.__('hidden','escortwp').')</b>';
				dolce_email("", "", $user_info->user_email, __('Welcome to','escortwp')." ".get_option("email_sitename"), $body);

				// send email to admin
				$body = __('Hello','escortwp').',<br /><br />
'.__('A new member has registered on','escortwp').' '.get_option("email_sitename").':<br /><br />
'.__('Account information','escortwp').':<br />
'.__('type','escortwp').': <b>'.__('member','escortwp').'</b><br />
'.__('username','escortwp').': <b>'.$user_info->user_login.'</b><br />
'.__('password','escortwp').': <b>('.__('hidden','escortwp').')</b>';
				if (get_option("ifemail4") == "1") {
					dolce_email(null, null, get_bloginfo("admin_email"), __('New member registration on','escortwp')." ".get_option("email_sitename"), $body);
				}
			}
			echo '<div class="ok rad5">'.__('Thank you for verifying your email address. Your account has been activated.','escortwp').'</div>';
		}
	} elseif(is_user_logged_in()) {
		$current_user = wp_get_current_user();

		if(isset($_POST['action']) && $_POST['action'] == "change_email_address") {
			$new_email = sanitize_email($_POST['email_addr']);
			$new_email_err = "";
			if (!$new_email) { $new_email_err .= __('Please write a valid email address','escortwp')."<br />"; } else {
				if (!is_email($new_email)) { $new_email_err .= __('Your email address seems to be wrong','escortwp')."<br />"; }
				$user_info = get_userdata($current_user->ID);
				if (email_exists($new_email) && $new_email != $user_info->user_email) {
					$new_email_err .= __('You can\'t use this email address. Please try another one.','escortwp')."<br />";
				}
			}

			if(!$new_email_err) {
				wp_update_user(array ('ID' => $current_user->ID, 'user_email' => $new_email));
				$current_user = wp_get_current_user();
				unset($new_email);
				$ok = __('Your email address has been updated', 'escortwp');
				$resend_email = "ok";
			}
		}

		if(get_user_meta( $current_user->ID, "emailhash", true ) && get_the_ID() != get_option('contact_page_id')) {
			echo '<div class="bodybox rad5 registrationcomplete">';
			echo "<div class='registrationcomplete-title'>".__('Your registration is complete','escortwp')."</div><br />";
			if(isset($ok)) {
				echo '<div class="ok rad25" style="display: inline-block; padding: 5px 20px">'.$ok.'</div><div class="clear10"></div>';
			}
			echo __('Before you can use the site you will need to validate your email address.','escortwp')."<br />";
			echo __('We sent a validation link to your email address.','escortwp')."<br />";
			echo __('Please click the link from that email so we can activate your account.','escortwp')."<br />";
			echo __('If you don\'t validate your email in the next 3 days, your account will be deleted.','escortwp')."<br />";
			echo '<div class="clear20"></div>';
			echo '<div class="send-validation-email-button-preloader hide"></div>';
			echo '<span id="resendvalidationlink" class="greenbutton rad25">'.__('Resend validation email','escortwp').'</span><div class="resendvalidationlink-message hide"></div>'."<br />";
			echo '<div class="clear30"></div>';
			?>

			<div class="change-email-address <?=$new_email_err ? " hide" : ""?>"><?=__('Problems getting the email?', 'escortwp')?><br /><div class="change-email-button greenbutton rad25"><?=__('Change your email address', 'escortwp')?></div></div>
			<?php
			if($new_email_err) {
				echo '<div class="err rad25" style="display: inline-block; padding: 5px 20px">'.$new_email_err.'</div><div class="clear"></div>';
			}
			?>
			<form action="" method="post" class="change-email-address-form form-styling"<?=$new_email_err ? " style='display: inline-block'" : ""?>>
				<input type="hidden" name="action" value="change_email_address" />
			    <div class="form-label col100">
					<label for="email_addr"><?php _e('New email address:','escortwp'); ?></label>
				</div>
				<div class="form-input col100">
			    	<input type="text" name="email_addr" id="email_addr" class="input longinput" value="<?=$new_email?>" required />
				</div><div class="clear10"></div>
				<input type="submit" name="submit" value="<?=__('Change', 'escortwp')?>" placeholder="<?=__('email address', 'escortwp')?>" class="greenbutton rad25" />
			</form>
			<?php

			echo '</div>';
    		echo '<div class="clear"></div>';
?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					function click_resend_button(button) {
						button.hide();
						$('.send-validation-email-button-preloader').show();
						$.ajax({
							type: "GET",
							url: "<?php bloginfo('template_url'); ?>/ajax/resend-validation-link.php?time=<?php echo time(); ?>",
							success: function(data){
								$('.send-validation-email-button-preloader').hide();
								$('.resendvalidationlink-message').html(data).fadeIn("slow").delay('5000').fadeOut("slow",function(){
									$('#resendvalidationlink').show();
								});
							}
						});
					}

					$('#resendvalidationlink').on('click', function(){
						click_resend_button($(this));
					});

					<?php if(isset($ok)) { ?>
						click_resend_button($('#resendvalidationlink'));
					<?php } ?>
				});
			</script>
			<?php
			get_footer();
			die();
		}
	}
} // check_if_user_has_validated_his_email()

//check unverified users
time_check_unverified();
function time_check_unverified() {
	$time = get_option('time_check_unverified');
	if(!$time || $time < time()) {
		update_option("time_check_unverified", strtotime("+1 day 3:10:00"));
		check_unverified_users();
	}
}

//search for unverified users and delete them
function check_unverified_users() {
	global $wpdb, $taxonomy_agency_url, $taxonomy_profile_url;
	$users = $wpdb->get_col("SELECT `user_id` FROM `$wpdb->usermeta` WHERE `meta_key` = 'emailhash'");
	if(count($users) > 0) {
		foreach($users as $user_id) {
			$user_info = get_userdata($user_id);
			$user_registered = $user_info->user_registered;
			$user_registered = strtotime($user_registered);
			if($user_registered < strtotime("-2 days")) {
				$user_type = get_option("escortid".$user_id);
				if($user_type == $taxonomy_agency_url) {
					$agency_post_id = get_option("agencypostid".$user_id);
					wp_delete_post( $agency_post_id, true );
					delete_option("agencypostid".$user_id);
				} elseif ($user_type == $taxonomy_profile_url) {
					$escort_post_id = get_option("escortpostid".$user_id);
					wp_delete_post( $escort_post_id, true );
					delete_option("escortpostid".$user_id);
				} elseif ($user_type == "member") {
					//do nothing
				}
				$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE user_id = %d", $user_id) );
				$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->users WHERE ID = %d", $user_id) );
				delete_option("escortid".$user_id);
			}
		}
	}
}





//check expired ads
function time_check_expired() {
	$time = get_option('time_check_expired');
	if(!$time || $time < time()) {
		update_option("time_check_expired", strtotime("+1 day 3:00:00"));
		check_expired();
	}
}

function check_expired() {
	global $wpdb, $taxonomy_profile_name_plural, $taxonomy_profile_url, $taxonomy_agency_name, $taxonomy_profile_name;
	include_once(ABSPATH."wp-admin/includes/user.php");

	// check for expired profiles/tours/upgrades

	// for expired tours
	$end = current_time('timestamp') - 60*60*24*2; // delete tours 2 days after they have expired
	$tours_args = array(
		'post_type' => 'tour',
		'meta_query' => array(
			array(
				'key' => 'end',
				'value' => $end,
				'compare' => '<=',
				'type' => 'NUMERIC'
			)
		),
		'posts_per_page' => '-1'
	);
	$tours = new WP_Query($tours_args);
	foreach($tours->posts as $tour) { wp_delete_post($tour->ID, true ); }

	// for premium status
	$expired_premium = $wpdb->get_col("SELECT `post_id` FROM `".$wpdb->postmeta."` WHERE `meta_key` = 'premium_expire' AND `meta_value` < '".time()."'");
	foreach($expired_premium as $id) {
		update_post_meta($id, "premium", "0");
		delete_post_meta($id, "premium_since");
		delete_post_meta($id, "premium_txn_id");
		delete_post_meta($id, "premium_expire");
		delete_post_meta($id, "premium_expire_notice");
		$temp_post = get_post($id); $post_author = $temp_post->post_author;
		$email = $wpdb->get_var("SELECT `user_email` FROM `".$wpdb->users."` WHERE `ID`='".$post_author."'");
		if(get_option("escortid".$post_author) == $taxonomy_profile_url) {
			$body = __('Hello','escortwp').',<br /><br />
'.__('Your premium status has expired and it has been removed from your profile.','escortwp').'.<br />'.__('You can purchase a premium status again at any time by visiting your profile page','escortwp').':<br />
<a href="'.get_permalink($id).'">'.get_permalink($id).'</a>';
		} else {
			$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('The premium status for one of your %s has expired and has been removed from their profile.','escortwp'),$taxonomy_profile_name_plural).'.<br />'.__('If you would like to purchase a premium status again then you can do so by visiting the profile page here','escortwp').':<br />
<a href="'.get_permalink($id).'">'.get_permalink($id).'</a>';
		}
		dolce_email(null, null, $email, __('Expiration notice from','escortwp')." ".get_option("email_sitename"), $body);
	}


	// for featured status
	$expired_featured = $wpdb->get_col("SELECT `post_id` FROM `".$wpdb->postmeta."` WHERE `meta_key` = 'featured_expire' AND `meta_value` < '".time()."'");
	foreach($expired_featured as $id) {
		update_post_meta($id, "featured", "0");
		delete_post_meta($id, "featured_txn_id");
		delete_post_meta($id, "featured_expire");
		delete_post_meta($id, "featured_expire_notice");
		$temp_post = get_post($id); $post_author = $temp_post->post_author;
		$email = $wpdb->get_var("SELECT `user_email` FROM `".$wpdb->users."` WHERE `ID`='".$post_author."'");
		if(get_option("escortid".$post_author) == $taxonomy_profile_url) {
			$body = __('Hello','escortwp').',<br /><br />
'.__('Your VIP status has expired and it has been removed from your profile.','escortwp').'.<br />'.__('You can purchase a VIP status again at any time by visiting the profile page','escortwp').':<br />
<a href="'.get_permalink($id).'">'.get_permalink($id).'</a>';
		} else {
			$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('The VIP status for one of your %s has expired and has been removed from their profile.','escortwp'),$taxonomy_profile_name_plural).'.<br />'.__('You can purchase a VIP status again at any time by visiting the profile page','escortwp').':<br />
<a href="'.get_permalink($id).'">'.get_permalink($id).'</a>';
		}
		dolce_email(null, null, $email, __('Expiration notice from','escortwp')." ".get_option("email_sitename"), $body);
	}

	// for VIP status
	$expired_vip = $wpdb->get_col("SELECT `user_id` FROM `".$wpdb->usermeta."` WHERE `meta_key` = 'vip_expire' AND `meta_value` < '".time()."'");
	foreach($expired_vip as $id) {
		delete_user_meta($id, "vip");
		delete_user_meta($id, "vip_txn_id");
		delete_user_meta($id, "vip_expire");
		delete_user_meta($id, "vip_expire_notice");
		$email = $wpdb->get_var("SELECT `user_email` FROM `".$wpdb->users."` WHERE `ID`='".$id."'");
		$body = __('Hello','escortwp').',<br /><br />
'.__('Your VIP status has expired and has been removed from your account.','escortwp').'.<br />'.__('You can purchase a VIP status again at any time by visiting our website','escortwp').':<br />
<a href="'.get_bloginfo('url').'/">'.get_bloginfo('url').'/</a>';
		dolce_email(null, null, $email, __('Expiration notice from','escortwp')." ".get_option("email_sitename"), $body);
	}


	// for agency profiles
	$expired_agency = $wpdb->get_col("SELECT `post_id` FROM `".$wpdb->postmeta."` WHERE `meta_key` = 'agency_expire' AND `meta_value` < '".time()."'");
	foreach($expired_agency as $id) {
		$temp_post = get_post($id);
		$post_author = $temp_post->post_author;
		$email = $wpdb->get_var("SELECT `user_email` FROM `".$wpdb->users."` WHERE `ID`='".$post_author."'");
		delete_post_meta($id, 'agency_expire');

		if(payment_plans('agreg','exp') == "1") {
			delete_agency($id);

			$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('Your %s profile has expired and has been deleted from our website.','escortwp'),$taxonomy_agency_name).'.<br />'.sprintf(esc_html__('If you had any %s in your profile they have been removed too.','escortwp'),$taxonomy_profile_name_plural).'.<br />'.__('You can create another account at anytime by visiting our website','escortwp').':<br />
<a href="'.get_bloginfo('url').'/">'.get_bloginfo('url').'/</a>';
			dolce_email(null, null, $email, __('Profile remove from','escortwp')." ".get_option("email_sitename"), $body);
		} else {
			$user_escorts = $wpdb->get_col("SELECT `ID` FROM `".$wpdb->posts."` WHERE `post_author` = '".$post_author."' AND `post_type` = '".$taxonomy_profile_url."'");
			foreach($user_escorts as $escort_id) {
				wp_update_post(array('ID' => $escort_id, 'post_status' => 'private'));
				update_post_meta($escort_id, "needs_ag_payment", "1"); // requires agency payment
			}

			wp_update_post(array('ID' => $id, 'post_status' => 'private'));
			if(payment_plans('agreg','price')) {
				update_post_meta($id, "needs_payment", "1"); // requires payment
			} else {
				update_post_meta($id, "notactive", "1"); // requires admin activation
			}

			$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('Your %s profile has expired and we have set your profile to private.','escortwp'),$taxonomy_agency_name).'.<br />'.sprintf(esc_html__('If you had any %s in your profile then they have been set to private too.','escortwp'),$taxonomy_profile_name_plural).'.<br />'.sprintf(esc_html__('Your profile or any %s profiles that you added will not be visible in our website until you pay your registration fee.','escortwp'),$taxonomy_profile_name).'<br />
<a href="'.get_bloginfo($id).'/">'.get_permalink($id).'/</a>';
			dolce_email(null, null, $email, __('Expiration notice from','escortwp')." ".get_option("email_sitename"), $body);
		}
	}


	// for escort profiles(independent or from agencies)
	$expired_escort = $wpdb->get_col("SELECT `post_id` FROM `".$wpdb->postmeta."` WHERE `meta_key` = 'escort_expire' AND `meta_value` < '".time()."'");
	foreach($expired_escort as $id) {
		$temp_post = get_post($id);
		$post_author = $temp_post->post_author;
		$email = $wpdb->get_var("SELECT `user_email` FROM `".$wpdb->users."` WHERE `ID`='".$post_author."'");
		$user_type = get_option("escortid".$post_author); // checking the user type before deleting the escort
		delete_post_meta($id, 'escort_expire');

		if($user_type == $taxonomy_profile_url) { // independent
			if(payment_plans('indescreg','exp') == "1") { // delete profile on expiration
				delete_profile($id); //delete the escort profile

				$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('Your %s profile has expired and has been deleted from our website.','escortwp'),$taxonomy_profile_name).'.<br />'.__('You can create another account at anytime by visiting our website','escortwp').':<br />
<a href="'.get_bloginfo('url').'/">'.get_bloginfo('url').'/</a>';
				dolce_email(null, null, $email, __('Expiration notice from','escortwp')." ".get_option("email_sitename"), $body);
			} else {
				wp_update_post(array('ID' => $id, 'post_status' => 'private'));

				if(payment_plans('indescreg','price')) {
					update_post_meta($id, "needs_payment", "1"); // requires payment
				} else {
					update_post_meta($id, "notactive", "1"); // requires payment
				}

				$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('Your %s profile has expired and has been set to private.','escortwp'),$taxonomy_profile_name).'.<br />'.__('Your profile will not be visible in our website anymore, until you pay your registration fee.','escortwp').'<br />
<a href="'.get_bloginfo('url').'/">'.get_bloginfo('url').'/</a>';
				dolce_email(null, null, $email, __('Expiration notice from','escortwp')." ".get_option("email_sitename"), $body);
			}
		} else { // escort from agency
			if(payment_plans('agescortreg','exp') == "1") { // delete profile on expiration
				delete_profile($id); //delete the escort profile

				$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('An %s you added has expired and has been deleted from our website.','escortwp'),$taxonomy_profile_name).'.<br />'.sprintf(esc_html__('%s name','escortwp'),ucfirst($taxonomy_profile_name)).':<br />
'.$temp_post->post_title.'<br /><br />
'.__('Website','escortwp').':<br /><a href="'.get_bloginfo('url').'/">'.get_bloginfo('url').'/</a>';
				dolce_email(null, null, $email, __('Expiration notice from','escortwp')." ".get_option("email_sitename"), $body);
			} else {
				wp_update_post(array('ID' => $id, 'post_status' => 'private'));
				if(payment_plans('agescortreg','price')) {
					update_post_meta($id, "needs_payment", "1"); // requires payment
				} else {
					update_post_meta($id, "notactive", "1"); // requires admin activation
				}

				$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('An %1$s you added has expired and has been set to private. This profile will not be shown on our website until you pay the registration fee for the %2$s.','escortwp'),$taxonomy_profile_name,$taxonomy_profile_name).'.<br />'.sprintf(esc_html__('%s name','escortwp'),ucfirst($taxonomy_profile_name)).':<br />
'.$temp_post->post_title.'<br /><br />
'.__('Website','escortwp').':<br /><a href="'.get_bloginfo('url').'/">'.get_bloginfo('url').'/</a>';
				dolce_email(null, null, $email, __('Expiration notice from','escortwp')." ".get_option("email_sitename"), $body);
			}
		}
	}
	//check for expired profiles END



	// send notice emails for profiles with soon to expire statuses
	// for premium status
	$soon_to_expire_premium = $wpdb->get_col("SELECT `post_id` FROM `".$wpdb->postmeta."` WHERE `meta_key` = 'premium_expire' AND `meta_value` < '".strtotime('+2 days')."'");
	foreach($soon_to_expire_premium as $id) {
		if(get_post_meta($id, "premium_expire_notice", true) != "1") {
			$temp_post = get_post($id);
			$post_author = $temp_post->post_author;
			$email = $wpdb->get_var("SELECT `user_email` FROM `".$wpdb->users."` WHERE `ID`='".$post_author."'");
			if(get_option("escortid".$post_author) == $taxonomy_profile_url) {
				$body = __('Hello','escortwp').',<br /><br />
'.__('Your premium status will expire very soon','escortwp').'.<br />'.__('If you want to renew your status please visit your profile page','escortwp').':<br />
<a href="'.get_permalink($id).'">'.get_permalink($id).'</a>';
			} else {
				$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('The premium status for one of your %s will expire very soon.','escortwp'),$taxonomy_profile_name_plural).'.<br />'.sprintf(esc_html__('If you want to renew this status please visit the %s profile page.','escortwp'),$taxonomy_profile_name).':<br />
<a href="'.get_permalink($id).'">'.get_permalink($id).'</a>';
			}
			dolce_email(null, null, $email, __('Expiration notice from','escortwp')." ".get_option("email_sitename"), $body);
			add_post_meta($id, "premium_expire_notice", "1", true);
		}
	}


	// for featured status
	$soon_to_expire_featured = $wpdb->get_col("SELECT `post_id` FROM `".$wpdb->postmeta."` WHERE `meta_key` = 'featured_expire' AND `meta_value` < '".strtotime('+2 days')."'");
	foreach($soon_to_expire_featured as $id) {
		if(get_post_meta($id, "featured_expire_notice", true) != "1") {
			$temp_post = get_post($id);
			$post_author = $temp_post->post_author;
			$email = $wpdb->get_var("SELECT `user_email` FROM `".$wpdb->users."` WHERE `ID`='".$post_author."'");
			if(get_option("escortid".$post_author) == $taxonomy_profile_url) {
				$body = __('Hello','escortwp').',<br /><br />
'.__('Your VIP status will expire very soon.','escortwp').'.<br />'.__('If you want to renew your status please visit your profile page','escortwp').':<br />
<a href="'.get_permalink($id).'">'.get_permalink($id).'</a>';
			} else {
				$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('The VIP status for one of your %s will expire very soon.','escortwp'),$taxonomy_profile_name_plural).'.<br />'.sprintf(esc_html__('If you want to renew this status please visit the %s profile page.','escortwp'),$taxonomy_profile_name).':<br />
<a href="'.get_permalink($id).'">'.get_permalink($id).'</a>';
			}
			dolce_email(null, null, $email, __('Expiration notice from','escortwp')." ".get_option("email_sitename"), $body);
			add_post_meta($id, "featured_expire_notice", "1", true);
		}
	}


	// for VIP status
	$soon_to_expire_vip = $wpdb->get_col("SELECT `user_id` FROM `".$wpdb->usermeta."` WHERE `meta_key` = 'vip_expire' AND `meta_value` < '".strtotime('+2 days')."'");
	foreach($soon_to_expire_vip as $id) {
		if(get_user_meta($id, "vip_expire_notice", true) != "1") {
			$email = $wpdb->get_var("SELECT `user_email` FROM `".$wpdb->users."` WHERE `ID`='".$id."'");
			$body = __('Hello','escortwp').',<br /><br />
'.__('Your VIP status will expire very soon.','escortwp').'.<br />'.__('If you want to renew your status please visit our website','escortwp').':<br />
<a href="'.get_bloginfo('url').'/">'.get_bloginfo('url').'/</a>';
			dolce_email(null, null, $email, __('Expiration notice from','escortwp')." ".get_option("email_sitename"), $body);
			add_user_meta($id, "vip_expire_notice", "1", true);
		}
	}


	// for agency profiles
	$soon_to_expire_agency = $wpdb->get_col("SELECT `post_id` FROM `".$wpdb->postmeta."` WHERE `meta_key` = 'agency_expire' AND `meta_value` < '".strtotime('+2 days')."'");
	foreach($soon_to_expire_agency as $id) {
		if(get_post_meta($id, "agency_expire_notice", true) != "1") {
			$temp_post = get_post($id);
			$post_author = $temp_post->post_author;
			$email = $wpdb->get_var("SELECT `user_email` FROM `".$wpdb->users."` WHERE `ID`='".$post_author."'");
			$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('Your %s profile will expire very soon.','escortwp'),$taxonomy_agency_name).'.<br />'.sprintf(esc_html__('If you do not want your profile to be removed from our website(along with any %s you might have added) please visit your profile page and renew it.','escortwp'),$taxonomy_profile_name_plural).':<br />
<a href="'.get_permalink($id).'">'.get_permalink($id).'</a>';
			dolce_email(null, null, $email, __('Expiration notice from','escortwp')." ".get_option("email_sitename"), $body);
			add_post_meta($id, "agency_expire_notice", "1", true);
		}
	}


	// for escort profiles(independent and from agencies)
	$soon_to_expire_escort = $wpdb->get_col("SELECT `post_id` FROM `".$wpdb->postmeta."` WHERE `meta_key` = 'escort_expire' AND `meta_value` < '".strtotime('+2 days')."'");
	foreach($soon_to_expire_escort as $id) {
		if(get_post_meta($id, "escort_expire_notice", true) != "1") {
			$temp_post = get_post($id);
			$post_author = $temp_post->post_author;
			$email = $wpdb->get_var("SELECT `user_email` FROM `".$wpdb->users."` WHERE `ID`='".$post_author."'");
			if(get_option("escortid".$post_author) == $taxonomy_profile_url) {
				$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('Your %s profile from our website will expire very soon.','escortwp'),$taxonomy_profile_name).'.<br />'.__('If you do not want your profile to be removed from our website please visit your profile page and renew it','escortwp').':<br />
<a href="'.get_permalink($id).'">'.get_permalink($id).'</a>';
			} else {
				$body = __('Hello','escortwp').',<br /><br />
'.sprintf(esc_html__('The profile for one of the %s you added will expire very soon.','escortwp'),$taxonomy_profile_name_plural).'.<br />'.__('If you do not want the profile to be deleted from our website please visit the profile page and renew it','escortwp').':<br />
<a href="'.get_permalink($id).'">'.get_permalink($id).'</a>';
			}
			dolce_email(null, null, $email, __('Expiration notice from','escortwp')." ".get_option("email_sitename"), $body);
			add_post_meta($id, "escort_expire_notice", "1", true);
		}
	}
} // check_expired()


function delete_profile($escort_id) {
	global $taxonomy_profile_url;
	$upload_folder = get_post_meta($escort_id, "upload_folder", true);
	$secret = get_post_meta($escort_id, "secret", true);
	$dirtodelete = ABSPATH."wp-content/uploads/".$upload_folder."/";

	$attachments = get_children(array('post_parent' => $escort_id, 'post_status' => 'inherit', 'post_type' => 'attachment'));
	foreach ($attachments as $attachment) {
		wp_delete_attachment($attachment->ID, 'true');
	}

	if (is_dir($dirtodelete)) {
		$objects = scandir($dirtodelete);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dirtodelete.$object) == "dir") {
					rmdir($dirtodelete.$object);
				} else {
					unlink($dirtodelete.$object);
				}
			}
		}
		reset($objects);
		rmdir($dirtodelete);
	} // delete directory and files

	//delete all tours
	$args = array(
		'post_type' => 'tour',
		'posts_per_page' => -1,
		'meta_query' => array( array('key' => 'belongstoescortid', 'value' => $escort_id, 'compare' => '=', 'type' => 'NUMERIC') )
	);
	query_posts( $args );
	if (have_posts()) :
		while ( have_posts() ) : the_post();
			wp_delete_post( get_the_ID(), true ); //delete post
		endwhile;
	endif;
	wp_reset_query();


	//delete all reviews
	$args = array(
		'post_type' => 'review',
		'posts_per_page' => -1,
		'meta_query' => array( array('key' => 'escortid', 'value' => $escort_id, 'compare' => '=', 'type' => 'NUMERIC') )
	);
	query_posts( $args );
	if (have_posts()) :
		while ( have_posts() ) : the_post();
			wp_delete_post( get_the_ID(), true ); //delete post
		endwhile;
	endif;
	wp_reset_query();

	$post = get_post($escort_id);
	delete_option("escortpostid".$escort_id);
	delete_option($secret);
	delete_option("agency".$secret);
	wp_delete_post( $escort_id, true ); //delete post
	if(get_option("escortid".$post->post_author) == $taxonomy_profile_url) {
		delete_option("escortid".$escort_id);
		include_once(ABSPATH."wp-admin/includes/user.php");
		wp_delete_user($post->post_author);
	}
}

function delete_agency($agency_id) {
	global $taxonomy_profile_url;
	$agency_profile = get_post($agency_id);
	$upload_folder = get_post_meta($agency_id, "upload_folder", true);
	$secret = get_post_meta($agency_id, "secret", true);
	$dirtodelete = ABSPATH."wp-content/uploads/".$upload_folder."/";

	//delete all profiles added by this agency
	$args = array(
		'post_type' => $taxonomy_profile_url,
		'posts_per_page' => -1,
		'author' => $agency_profile->post_author
	);
	query_posts( $args );
	if (have_posts()) :
	while ( have_posts() ) : the_post();
		delete_profile(get_the_ID());
	endwhile;
	endif;
	wp_reset_query();


	if (is_dir($dirtodelete)) {
		$objects = scandir($dirtodelete);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dirtodelete.$object) == "dir") {
					rmdir($dirtodelete.$object);
				} else {
					unlink($dirtodelete.$object);
				}
			}
		}
		reset($objects);
		rmdir($dirtodelete);
	} // delete directory and files

	delete_option($secret);
	delete_option("escortid".$agency_profile->post_author);
	delete_option("agencypostid".$agency_profile->post_author);

	wp_delete_post( $agency_id, true ); //delete post
	include_once(ABSPATH."wp-admin/includes/user.php");
	wp_delete_user($agency_profile->post_author);
}

// delete a member account
function escwp_delete_member() {
	if(!is_user_logged_in()) return false;

	$current_user = wp_get_current_user();
	$userid = $current_user->ID;
	$userstatus = get_option("escortid".$userid);
	if (isset($_POST['action']) && $_POST['action'] == 'member_delete_account' && $userstatus == "member") {
		include_once(ABSPATH."wp-admin/includes/user.php");
		wp_delete_user($userid);
		wp_redirect(get_bloginfo("url")); die();
	} // if admin
}
add_action('init', 'escwp_delete_member');

function dolce_pagination($total, $current="", $format="", $base="") {
	if ( $total > 1 ) {
		if(!$format) {
			$format = get_option('permalink_structure') ? 'page/%#%/' : '&page=%#%';
		}
		if(!$base) {
			$base = get_pagenum_link(1);
		}
		if(!(int)$current) { $current = "1"; }
		echo '<div class="escort-pagination">';
		echo paginate_links(array(
				'base' => $base . '%_%',
				'format' => $format,
				'total' => $total,
				'current' => $current,
				'end_size' => '2',
				'mid_size' => '2',
				'prev_text' => __('Previous','escortwp'),
				'next_text' => __('Next','escortwp'),
				'type' => 'list'
			));
		echo '</div>';
	}
}

function build_checkbox_edit_fields_page($value, $name, $position) {
	if($value == "1") {
		return '<input type="checkbox" '.build_name_for_checkbox_edit_fields_page($name, $position).' value="1" class="ios-checkbox" checked />';
	} elseif ($value == "2") {
		return '<input type="checkbox" '.build_name_for_checkbox_edit_fields_page($name, $position).' value="1" class="ios-checkbox" />';
	} elseif ($value == "3") {
		return __("YES",'escortwp');
	} elseif ($value == "4") {
		return __("NO",'escortwp');
	}
}
function build_name_for_checkbox_edit_fields_page($name, $position) {
	if($position == "1") {
		return 'name="'.$name.'showinreg" id="firstcheckbox'.$name.'"';
	} elseif ($position == "2") {
		return 'name="'.$name.'mandatory"';
	} elseif ($position == "3") {
		return 'name="'.$name.'useinsearch"';
	}
}

//is registration field mandatory
function ismand($name, $show = '') {
	$fields = get_option('regfieldsescort');
	if($fields[$name][2] == "1" || $fields[$name][2] == "3") {
		if($show) {
			return true;
		} else {
			echo '<i>*</i>';
		}
	}
}

//is field showing in reg page
function showfield($name) {
	$fields = get_option('regfieldsescort');
	if($fields[$name][1] == "1" || $fields[$name][1] == "3") {
		return true;
	} else {
		return false;
	}
}

//is field showing in search page
function insearch($name) {
	$fields = get_option('regfieldsescort');
	if($fields[$name][3] == "1" || $fields[$name][3] == "3") {
		return true;
	} else {
		return false;
	}
}


function get_reg_price($type, $free="") {
	if(!$type) { return false; }

	if(payment_plans($type,'price')) {
		$pricecode  =  '<span class="showprice showprice-'.$type.' rad3">';
		$pricecode .=  "<b>".format_price($type,'price')."</b>";
		if(payment_plans($type,'woo_product_id') && class_exists('WC_Subscriptions_Product') && get_post_meta(payment_plans($type,'woo_product_id'), '_subscription_price', true)) {
			$pricecode .=  "<small> / ".WC_Subscriptions_Product::get_period(payment_plans($type,'woo_product_id'))."</small>";
		} else {
			if(payment_plans($type,'duration')) {
				global $payment_duration_a;
				$pricecode .=  "<small>";
				$pricecode .=  " ".__('for','escortwp')." ";
				$pricecode .=  __($payment_duration_a[payment_plans($type,'duration')][0], 'escortwp');
				$pricecode .=  "</small>";
			}
		}

		$pricecode .=  "</span>";
	} else {
		if($free) { $pricecode =  '<span class="showprice showprice-'.$type.' rad3">'.__('Free','escortwp').'</span>'; }
	}

	return $pricecode;
}

function dolce_custom_menu() {
	register_nav_menus(array('header-menu' => 'Header Menu'));
}
add_action('init', 'dolce_custom_menu');

function closebtn($t=1) {
	if($t == "1")
		echo '<div class="rad25 redbutton closebtn r"><span class="label">'.__('Close','escortwp').'</span><span class="icon icon-cancel-circled r"></span></div>';

	if($t == "2")
		echo '<div class="closebtn_box rad25"><span class="icon icon-cancel-circled"></span></div>';
}

function get_current_url() {
	$current_url  = 'http';
	$server_https = $_SERVER["HTTPS"];
	$server_name  = $_SERVER["SERVER_NAME"];
	$server_port  = $_SERVER["SERVER_PORT"];
	$request_uri  = $_SERVER["REQUEST_URI"];
	if ($server_https == "on") $current_url .= "s";
	$current_url .= "://";
	if ($server_port != "80") $current_url .= $server_name . ":" . $server_port . $request_uri;
	else $current_url .= $server_name . $request_uri;
	return esc_url($current_url, array('http', 'https'));
}

function upgrade_theme() {
	if(!get_option('is_theme_installed')) return false;

	global $theme_version;
	$revnr = get_option('revnr');

	if($revnr == $theme_version)
		return true;

	if($revnr < "200") {
		upgrade_theme_code('200');
	}

	if ($revnr < "220") {
		upgrade_theme_code('220');
	}
	if ($revnr < "230") {
		upgrade_theme_code('230');
	}
	if ($revnr < "300") {
		upgrade_theme_code('300');
	}
	if ($revnr < "350") {
		upgrade_theme_code('350');
	}
	if ($revnr < "360") {
		upgrade_theme_code('360');
	}

	wp_redirect(get_bloginfo('url')); die();
}

function upgrade_theme_code($v) {
	switch ($v) {
		case '200':
				// adding new settings
				set_default_settings();
				update_option('generate_demo_data_alert', 'hide');

				// upgrading profile genders
				$args = array(
					'post_type' => 'escort', 'posts_per_page' => '-1',
					'meta_query' => array(
						array('key' => 'gender', 'value' => '3', 'compare' => '=')
					)
				);
				query_posts($args);
				if ( have_posts() ) :
					while ( have_posts() ) : the_post();
						update_post_meta(get_the_ID(), 'gender', '4');
					endwhile;
				endif;
				wp_reset_query();


				// add premium_since meta field to premium profiles otherwise the premium loops won't show any results
				$args = array(
					'post_type' => 'escort', 'posts_per_page' => '-1',
					'meta_query' => array( array('key' => 'premium', 'value' => '1', 'compare' => '=', 'type' => 'NUMERIC') )
				);
				$a = query_posts($args);
				if ( have_posts() ) :
					while ( have_posts() ) : the_post();
						update_post_meta(get_the_ID(), 'premium_since', get_the_time('U'));
					endwhile;
				endif;
				wp_reset_query();


				// upgrading jobs to ads
				$args = array('post_type' => 'job', 'posts_per_page' => '-1');
				query_posts($args);
				if ( have_posts() ) :
					global $wpdb;
					while ( have_posts() ) : the_post();
						set_post_type( get_the_ID(), 'ad');
					endwhile;
				endif;
				wp_reset_query();


				// upgrade reviews
				$args = array(
					'post_type' => 'review',
					'posts_per_page' => '-1',
					'meta_query' => array( array('key' => 'reviewfor', 'value' => 'escort', 'compare' => '=') ),
					'paged' => $paged
				);
				query_posts($args);
				if ( have_posts() ) :
					while ( have_posts() ) : the_post();
						update_post_meta(get_the_ID(), 'reviewfor', 'profile');
					endwhile;
				endif;
				wp_reset_query();


				update_option("taxonomy_profile_name", 'escort');
				update_option("taxonomy_profile_name_plural", 'escorts');
				update_option("taxonomy_profile_url", 'escort');
				update_option("settings_theme_genders", array('1', '2', '3', '4', '5'));
				update_option("taxonomy_agency_name", 'agency');
				update_option("taxonomy_agency_name_plural", 'agencies');
				update_option("taxonomy_agency_url", 'agency');
				update_option("taxonomy_location_url", 'escorts-from');

				// update page titles, urls and template files
				create_theme_pages();

				update_option('revnr', '200');
			break; // upgrade to 200

		case '220':
				global $escortregfields;
				update_option('newlabelperiod', '14');
				update_option('regfieldsescort', $escortregfields);
				update_option('revnr', '220');
			break;  // upgrade to 220

		case '230':
				global $taxonomy_profile_url, $escortregfields;
				update_option('regfieldsescort', $escortregfields);
				update_option('autoscrollheaderslider', '1');
				update_option('manactivclassads', '2');

				query_posts(array('post_type' => $taxonomy_profile_url, 'posts_per_page' => '-1'));
				if ( have_posts() ) :
					while ( have_posts() ) : the_post();
						if(get_post_meta(get_the_ID(), "upgraded_to_v230", true) != "yes") {
							update_post_meta(get_the_ID(), "ethnicity", get_post_meta(get_the_ID(), 'skincolor', true));
							delete_post_meta(get_the_ID(), 'skincolor');

							update_post_meta(get_the_ID(), "rate30min_incall", get_post_meta(get_the_ID(), 'rate30min', true));
							update_post_meta(get_the_ID(), "rate1h_incall", get_post_meta(get_the_ID(), 'rate1h', true));
							update_post_meta(get_the_ID(), "rate2h_incall", get_post_meta(get_the_ID(), 'rate2h', true));
							update_post_meta(get_the_ID(), "rate3h_incall", get_post_meta(get_the_ID(), 'rate3h', true));
							update_post_meta(get_the_ID(), "rate6h_incall", get_post_meta(get_the_ID(), 'rate6h', true));
							update_post_meta(get_the_ID(), "rate12h_incall", get_post_meta(get_the_ID(), 'rate12h', true));
							update_post_meta(get_the_ID(), "rate24h_incall", get_post_meta(get_the_ID(), 'rate24h', true));

							update_post_meta(get_the_ID(), "rate30min_outcall", get_post_meta(get_the_ID(), 'rate30min', true));
							update_post_meta(get_the_ID(), "rate1h_outcall", get_post_meta(get_the_ID(), 'rate1h', true));
							update_post_meta(get_the_ID(), "rate2h_outcall", get_post_meta(get_the_ID(), 'rate2h', true));
							update_post_meta(get_the_ID(), "rate3h_outcall", get_post_meta(get_the_ID(), 'rate3h', true));
							update_post_meta(get_the_ID(), "rate6h_outcall", get_post_meta(get_the_ID(), 'rate6h', true));
							update_post_meta(get_the_ID(), "rate12h_outcall", get_post_meta(get_the_ID(), 'rate12h', true));
							update_post_meta(get_the_ID(), "rate24h_outcall", get_post_meta(get_the_ID(), 'rate24h', true));
							update_post_meta(get_the_ID(), "upgraded_to_v230", 'yes');
						}
					endwhile;
				endif;
				wp_reset_query();

				update_option('revnr', '230');
				update_option('maximgupload', '20');
				update_option('maximguploadsize', '5');
			break;  // upgrade to 230

		case '300':
				global $height_a;
				query_posts(array('post_type' => $taxonomy_profile_url, 'posts_per_page' => '-1'));
				if ( have_posts() ) :
					while ( have_posts() ) : the_post();
						if(get_post_meta(get_the_ID(), "upgraded_to_v3", true) != "yes") {
							if($height_a[get_post_meta(get_the_ID(), "height", true)]) {
								update_post_meta(get_the_ID(), "height", $height_a[get_post_meta(get_the_ID(), "height", true)]);
								update_post_meta(get_the_ID(), "upgraded_to_v3", 'yes');
							}
						}
					endwhile;
				endif;
				wp_reset_query();
				update_option('revnr', '300');
			break;  // upgrade to 300

		case '350':
				global $taxonomy_profile_name, $taxonomy_agency_name;
				update_option('content_settings_page_id', get_option('hide_site_sections_page_id'));
				delete_option('hide_site_sections_page_id');

				$payment_plans = array(
					'indescreg' => array(
									'title' => array('Independent %s registration','taxonomy_profile_name'),
									'label' => array('Price to register as independent %s','taxonomy_profile_name'),
									'label_help' => array('keep empty for free registration',''),
									'price' => get_option("indescregprice"),
									'duration' => get_option("indescregduration"),
									'woo_product_id' => '',
									'woo_product_name' => array('Independent %s registration','taxonomy_profile_name'),
									'exp' => get_option("escexp"),
									'exp_text' => array('What happens on profile expiration?',''),
								),
					'agreg' => array(
									'title' => array('%s registration','taxonomy_agency_name'),
									'label' => array('Price to register as %s','taxonomy_agency_name'),
									'label_help' => array('keep empty for free registration',''),
									'price' => get_option("agregprice"),
									'duration' => get_option("agregduration"),
									'woo_product_id' => '',
									'woo_product_name' => array('%s registration','taxonomy_agency_name'),
									'exp' => get_option("agexp"),
									'exp_text' => array('What happens on profile expiration?',''),
								),
					'agescortreg' => array(
									'title' => array('%1$s adding %2$s','taxonomy_agency_name','taxonomy_profile_name'),
									'label' => array('Price for %1$s to add an %2$s','taxonomy_agency_name','taxonomy_profile_name'),
									'label_help' => array('keep empty for free registration',''),
									'price' => get_option("agescortregprice"),
									'duration' => get_option("agescortregduration"),
									'woo_product_id' => '',
									'woo_product_name' => array('Add new %s profile','taxonomy_profile_name'),
									'exp' => get_option("agescexp"),
									'exp_text' => array('What happens on profile expiration?',''),
								),
					'premium' => array(
									'title' => array('Premium options',''),
									'label' => array('Price to become a premium %s','taxonomy_profile_name'),
									'label_help' => array('keep empty to disable',''),
									'price' => get_option("premiumprice"),
									'duration' => get_option("premiumduration"),
									'woo_product_id' => '',
									'woo_product_name' => array('Upgrade profile to premium','taxonomy_profile_name'),
								),
					'featured' => array(
									'title' => array('Featured options',''),
									'label_help' => array('keep empty to disable',''),
									'label' => array('Price to become a featured %s','taxonomy_profile_name'),
									'price' => get_option("featuredprice"),
									'duration' => get_option("featuredduration"),
									'woo_product_id' => '',
									'woo_product_name' => array('Upgrade profile to featured','taxonomy_profile_name'),
								),
					'tours' => array(
									'title' => array('Tour options',''),
									'label' => array('Price to add a tour',''),
									'label_help' => array('keep empty for free tours',''),
									'price' => get_option("tourprice"),
									'woo_product_id' => '',
									'woo_product_name' => array('Add city tour','taxonomy_profile_name'),
								),
					'vip' => array(
									'title' => array('VIP Options',''),
									'label' => array('Price to become a VIP member',''),
									'label_help' => array('keep empty to disable',''),
									'price' => get_option("vipprice"),
									'duration' => get_option("vipduration"),
									'woo_product_id' => '',
									'woo_product_name' => array('Upgrade to VIP','taxonomy_profile_name'),
									'extra' => array(
													'hide_photos' => get_option("viphide1"),
													'hide_contact_info' => get_option("viphide2"),
													'hide_review_form' => get_option("viphide3"),
												),
								),
				);
				update_option('payment_plans', $payment_plans);

				global $escortregfields;
				update_option('newlabelperiod', '14');
				update_option('unregsendcontactform', '2');
				update_option("frontpageshowonline", '1');
				update_option("frontpageshowonlinecols", '2');
				update_option("hitcounter1", '1');
				update_option("hitcounter2", '1');
				update_option("hitcounter3", '1');

				$escortregfields = get_option('regfieldsescort');
				$new_escortregfields = array(
					'instagram' => array('Instagram','1','2','4'),
					'snapchat' => array('SnapChat','1','2','4'),
					'twitter' => array('Twitter','1','2','4'),
					'facebook' => array('Facebook','1','2','4'),
				);
				$escortregfields_final = insert_array_inside_another_array($escortregfields, 'website', $new_escortregfields);
				update_option('regfieldsescort', $escortregfields_final);
				create_theme_pages();
				update_option('revnr', '350');
			break;  // upgrade to 350

		case '360':
				update_option('watermark_position', 'cc');
				update_option('locationsliderpage', '1');
				update_option('revnr', '360');

				$pages = array(array('all_verified_profiles_page_id', 'verified-'.sanitize_title($taxonomy_profile_name_plural), 'Verified '.ucwords($taxonomy_profile_name_plural), 'all-profiles.php'));

				foreach($pages as $p) {
					$new_page = array(
						'post_author' => "1",
						'comment_status' => "closed",
						'ping_status' => "closed",
						'post_name' => $p[1], // slug
						'post_title' => $p[2], // title
						'post_status' => "publish",
						'post_type' => "page",
						'page_template'  => $p[3]
					);
					global $wpdb;
					if (get_option($p[0]) < 1) {
						// create page
						$current_lang = multilang_switch_language();
						$new_page_id = wp_insert_post( $new_page );
						multilang_switch_language($current_lang);
						if ($new_page_id && $new_page_id != "0") {
							update_option($p[0], $new_page_id);
							$wpdb->insert( $wpdb->postmeta, array( 'post_id' => $new_page_id, 'meta_key' => '_wp_page_template', 'meta_value' => $p[4] ), array( '%d', '%s', '%s' ) );
						}
					}
				} // foreach
			break;  // upgrade to 360

		default:
			break;
	}
} // upgrade_to_v2


function set_default_settings() {
	global $payment_plans;
	if(!get_option('dolce_sitelang')) update_option("dolce_sitelang", 'english');
	if(!get_option('secret_to_upload_site_logo')) update_option("secret_to_upload_site_logo", md5(rand(1, 999)));
	if(!get_option('showheaderslider')) update_option("showheaderslider", '1');
	if(!get_option('autoscrollheaderslider')) update_option("autoscrollheaderslider", '1');
	if(!get_option('headerslideritems')) update_option("headerslideritems", '10');
	if(!get_option('showheadersliderall')) update_option("showheadersliderall", '');
	if(!get_option('showheadersliderfront')) update_option("showheadersliderfront", '1');
	if(!get_option('showheaderslideresccat')) update_option("showheaderslideresccat", '');
	if(!get_option('showheadersliderescprof')) update_option("showheadersliderescprof", '');
	if(!get_option('showheaderslideragprof')) update_option("showheaderslideragprof", '');
	if(!get_option('showheaderslidersearch')) update_option("showheaderslidersearch", '');
	if(!get_option('showheadersliderct')) update_option("showheadersliderct", '');
	if(!get_option('showheadersliderrev')) update_option("showheadersliderrev", '');
	if(!get_option('showheadersliderads')) update_option("showheadersliderads", '');
	if(!get_option('showheadersliderads')) update_option("showheadersliderads", '');
	if(!get_option('hideunchedkedservices')) update_option("hideunchedkedservices", '2');

	if(!get_option('frontpageshowpremium')) update_option("frontpageshowpremium", '1');
	if(!get_option('frontpageshowpremiumcols')) update_option("frontpageshowpremiumcols", '2');
	if(!get_option('frontpageshowonline')) update_option("frontpageshowonline", '1');
	if(!get_option('frontpageshowonlinecols')) update_option("frontpageshowonlinecols", '2');
	if(!get_option('frontpageshownormal')) update_option("frontpageshownormal", '1');
	if(!get_option('frontpageshownormalcols')) update_option("frontpageshownormalcols", '2');
	if(!get_option('frontpageshowrev')) update_option("frontpageshowrev", '1');
	if(!get_option('frontpageshowrevitems')) update_option("frontpageshowrevitems", '3');
	if(!get_option('frontpageshowrevchars')) update_option("frontpageshowrevchars", '400');

	if(!get_option('newlabelperiod')) update_option("newlabelperiod", '14');

	if(!get_option('maximgupload')) update_option("maximgupload", '20');
	if(!get_option('maximguploadsize')) update_option("maximguploadsize", '5');

	if(!get_option('allowvideoupload')) update_option("allowvideoupload", '2');
	if(!get_option('maxvideoupload')) update_option("maxvideoupload", '5');
	if(!get_option('maxvideouploadsize')) update_option("maxvideouploadsize", '50');
	if(!get_option('videoresizeheight')) update_option("videoresizeheight", '400');

	if(!get_option('heightscale')) update_option("heightscale", 'metric');

	if(!get_option('manactivesc')) update_option("manactivesc", '2');
	if(!get_option('manactivag')) update_option("manactivag", '2');
	if(!get_option('manactivagescprof')) update_option("manactivagescprof", '2');
	if(!get_option('manactivindescprof')) update_option("manactivindescprof", '2');
	if(!get_option('manactivclassads')) update_option("manactivclassads", '2');

	if(!get_option('allowadpostingprofiles')) update_option("allowadpostingprofiles", '1');
	if(!get_option('allowadpostingagencies')) update_option("allowadpostingagencies", '1');
	if(!get_option('allowadpostingmembers')) update_option("allowadpostingmembers", '1');

	if(!get_option('unregsendcontactform')) update_option("unregsendcontactform", "2");
	if(!get_option('tos18')) update_option("tos18", "2");
	if(!get_option('quickescortsearch')) update_option("quickescortsearch", "1");
	if(!get_option('hitcounter1')) update_option("hitcounter1", "1");
	if(!get_option('hitcounter2')) update_option("hitcounter2", "1");
	if(!get_option('hitcounter3')) update_option("hitcounter3", "1");


	if(!get_option('payment_plans')) update_option("payment_plans", $payment_plans);

	if(!get_option('show_coupon_checkout')) update_option("show_coupon_checkout", '2');
	if(!get_option('show_address_checkout')) update_option("show_address_checkout", '2');

	if(!get_option('admin_email')) update_option("admin_email", get_bloginfo('admin_email'));
	if(!get_option('email_sitename')) update_option("email_sitename", get_bloginfo('name'));
	if(!get_option('email_siteemail')) update_option("email_siteemail", get_bloginfo('admin_email'));
	if(!get_option('email_signature')) update_option("email_signature", "\n"."--"."\n".get_bloginfo('name'));
		
	if(!get_option('ifemail1')) update_option("ifemail1", '1');
	if(!get_option('ifemail2')) update_option("ifemail2", '1');
	if(!get_option('ifemail3')) update_option("ifemail3", '1');
	if(!get_option('ifemail4')) update_option("ifemail4", '1');
	if(!get_option('ifemail5')) update_option("ifemail5", '1');
	if(!get_option('ifemail6')) update_option("ifemail6", '1');
	if(!get_option('ifemail7')) update_option("ifemail7", '1');
	if(!get_option('ifemail8')) update_option("ifemail8", '1');
	if(!get_option('ifemail9')) update_option("ifemail9", '1');
	if(!get_option('manactivagprof')) update_option("manactivagprof", '2');
	if(!get_option('manactivescprof')) update_option("manactivescprof", '2');
	if(!get_option('manactivindesc')) update_option("manactivindesc", '2');

	global $escortregfields, $theme_version;
	if(!get_option('regfieldsescort')) update_option('regfieldsescort', $escortregfields);
	if(!get_option('defaults_have_been_set')) update_option('defaults_have_been_set', 'yes');
	if(!get_option('revnr')) update_option('revnr', $theme_version);
}


//Import a full list of countries
function import_country_list($taxonomy_url) {
	$countries = array("Afghanistan" => "afghanistan", "Albania" => "albania", "Algeria" => "algeria", "American Samoa" => "american-samoa", "Andorra" => "andorra", "Angola" => "angola", "Anguilla" => "anguilla", "Antigua and Barbuda" => "antigua-barbuda", "Argentina" => "argentina", "Armenia" => "armenia", "Aruba" => "aruba", "Australia" => "australia", "Austria" => "austria", "Azerbaijan" => "azerbaijan", "Bahamas" => "bahamas", "Bahrain" => "bahrain", "Bangladesh" => "bangladesh", "Barbados" => "barbados", "Belarus" => "belarus", "Belgium" => "belgium", "Belize" => "belize", "Benin" => "benin", "Bermuda" => "bermuda", "Bhutan" => "bhutan", "Bolivia" => "bolivia", "Bosnia-Herzegovina" => "bosnia-herzegovina", "Botswana" => "botswana", "Bouvet Island" => "bouvet-island", "Brazil" => "brazil", "Brunei" => "brunei", "Bulgaria" => "bulgaria", "Burkina Faso" => "burkina-faso", "Burundi" => "burundi", "Cambodia" => "cambodia", "Cameroon" => "cameroon", "Canada" => "canada", "Cape Verde" => "cape-verde", "Cayman Islands" => "cayman-islands", "Central African Republic" => "central-african-republic", "Chad" => "chad", "Chile" => "chile", "China" => "china", "Christmas Island" => "christmas-island", "Cocos (Keeling) Islands" => "cocos-keeling-islands", "Colombia" => "colombia", "Comoros" => "comoros", "Congo, Democratic Republic of the (Zaire)" => "congo-zaire", "Congo, Republic of" => "congo", "Cook Islands" => "cook-islands", "Costa Rica" => "costa-rica", "Croatia" => "croatia", "Cuba" => "cuba", "Cyprus" => "cyprus", "Czech Republic" => "czech-republic", "Denmark" => "denmark", "Djibouti" => "djibouti", "Dominica" => "dominica", "Dominican Republic" => "dominican-republic", "Ecuador" => "ecuador", "Egypt" => "egypt", "El Salvador" => "el-salvador", "Equatorial Guinea" => "equatorial-guinea", "Eritrea" => "eritrea", "Estonia" => "estonia", "Ethiopia" => "ethiopia", "Falkland Islands" => "falkland-islands", "Faroe Islands" => "faroe-islands", "Fiji" => "fiji", "Finland" => "finland", "France" => "france", "French Guiana" => "french-guiana", "Gabon" => "gabon", "Gambia" => "gambia", "Georgia" => "georgia", "Germany" => "germany", "Ghana" => "ghana", "Gibraltar" => "gibraltar", "Greece" => "greece", "Greenland" => "greenland", "Grenada" => "grenada", "Guadeloupe (French)" => "guadeloupe", "Guam (USA)" => "guam", "Guatemala" => "guatemala", "Guinea" => "guinea", "Guinea Bissau" => "guinea-bissau", "Guyana" => "guyana", "Haiti" => "haiti", "Holy See" => "holy-see", "Honduras" => "honduras", "Hong Kong" => "hong-kong", "Hungary" => "hungary", "Iceland" => "iceland", "India" => "india", "Indonesia" => "indonesia", "Iran" => "iran", "Iraq" => "iraq", "Ireland" => "ireland", "Israel" => "israel", "Italy" => "italy", "Ivory Coast (Cote D`Ivoire)" => "ivory-coast", "Jamaica" => "jamaica", "Japan" => "japan", "Jordan" => "jordan", "Kazakhstan" => "kazakhstan", "Kenya" => "kenya", "Kiribati" => "kiribati", "Kuwait" => "kuwait", "Kyrgyzstan" => "kyrgyzstan", "Laos" => "laos", "Latvia" => "latvia", "Lebanon" => "lebanon", "Lesotho" => "lesotho", "Liberia" => "liberia", "Libya" => "libya", "Liechtenstein" => "liechtenstein", "Lithuania" => "lithuania", "Luxembourg" => "luxembourg", "Macau" => "macau", "Macedonia" => "macedonia", "Madagascar" => "madagascar", "Malawi" => "malawi", "Malaysia" => "malaysia", "Maldives" => "maldives", "Mali" => "mali", "Malta" => "malta", "Marshall Islands" => "marshall-islands", "Martinique (French)" => "martinique", "Mauritania" => "mauritania", "Mauritius" => "mauritius", "Mayotte" => "mayotte", "Mexico" => "mexico", "Micronesia" => "micronesia", "Moldova" => "moldova", "Monaco" => "monaco", "Mongolia" => "mongolia", "Montenegro" => "montenegro", "Montserrat" => "montserrat", "Morocco" => "morocco", "Mozambique" => "mozambique", "Myanmar" => "myanmar", "Namibia" => "namibia", "Nauru" => "nauru", "Nepal" => "nepal", "Netherlands" => "netherlands", "Netherlands Antilles" => "netherlands-antilles", "New Caledonia (French)" => "new-caledonia", "New Zealand" => "new-zealand", "Nicaragua" => "nicaragua", "Niger" => "niger", "Nigeria" => "nigeria", "Niue" => "niue", "Norfolk Island" => "norfolk-island", "North Korea" => "north-korea", "Northern Mariana Islands" => "northern-mariana-islands", "Norway" => "norway", "Oman" => "oman", "Pakistan" => "pakistan", "Palau" => "palau", "Panama" => "panama", "Papua New Guinea" => "papua-new-guinea", "Paraguay" => "paraguay", "Peru" => "peru", "Philippines" => "philippines", "Pitcairn Island" => "pitcairn-island", "Poland" => "poland", "Polynesia (French)" => "polynesia", "Portugal" => "portugal", "Puerto Rico" => "puerto-rico", "Qatar" => "qatar", "Reunion" => "reunion", "Romania" => "romania", "Russia" => "russia", "Rwanda" => "rwanda", "Saint Helena" => "saint-helena", "Saint Kitts and Nevis" => "saint-kitts-nevis", "Saint Lucia" => "saint-lucia", "Saint Pierre and Miquelon" => "saint-pierre-miquelon", "Saint Vincent and Grenadines" => "saint-vincent-grenadines", "Samoa" => "samoa", "San Marino" => "san-marino", "Sao Tome and Principe" => "sao-tome-principe", "Saudi Arabia" => "saudi-arabia", "Senegal" => "senegal", "Serbia" => "serbia", "Seychelles" => "seychelles", "Sierra Leone" => "sierra-leone", "Singapore" => "singapore", "Slovakia" => "slovakia", "Slovenia" => "slovenia", "Solomon Islands" => "solomon-islands", "Somalia" => "somalia", "South Africa" => "south-africa", "South Georgia and South Sandwich Islands" => "south-georgia-south-sandwich-islands", "South Korea" => "south-korea", "Spain" => "spain", "Sri Lanka" => "sri-lanka", "Sudan" => "sudan", "Suriname" => "suriname", "Svalbard and Jan Mayen Islands" => "svalbard-jan-mayen-islands", "Swaziland" => "swaziland", "Sweden" => "sweden", "Switzerland" => "switzerland", "Syria" => "syria", "Taiwan" => "taiwan", "Tajikistan" => "tajikistan", "Tanzania" => "tanzania", "Thailand" => "thailand", "Timor-Leste (East Timor)" => "timor-leste", "Togo" => "togo", "Tokelau" => "tokelau", "Tonga" => "tonga", "Trinidad and Tobago" => "trinidad-tobago", "Tunisia" => "tunisia", "Turkey" => "turkey", "Turkmenistan" => "turkmenistan", "Turks and Caicos Islands" => "turks-caicos-islands", "Tuvalu" => "tuvalu", "Uganda" => "uganda", "Ukraine" => "ukraine", "United Arab Emirates" => "united-arab-emirates", "United Kingdom" => "united-kingdom", "United States" => "united-states", "Uruguay" => "uruguay", "Uzbekistan" => "uzbekistan", "Vanuatu" => "vanuatu", "Venezuela" => "venezuela", "Vietnam" => "vietnam", "Virgin Islands" => "virgin-islands", "Wallis and Futuna Islands" => "wallis-futuna-islands", "Yemen" => "yemen", "Zambia" => "zambia", "Zimbabwe" => "zimbabwe");
	foreach ($countries as $key=>$c) {
		if (!term_exists( $key, $taxonomy_url)) {
			$current_lang = multilang_switch_language();
			$post_profile_id = wp_insert_post($post_profile);
			wp_insert_term( $key, $taxonomy_url, array('description'=> '', 'slug' => $c));
			multilang_switch_language($current_lang);
		}
	}
}
//Create countries from custom list
function create_country_list($countries, $taxonomy_url) {
	global $taxonomy_location_url;
	$install_countries = explode("\n", $countries);
	foreach ($install_countries as $c) {
		$c_slug = sanitize_title(trim($c));
		if (!term_exists( $c, $taxonomy_location_url)) {
			$current_lang = multilang_switch_language();
			$a = wp_insert_term( $c, $taxonomy_location_url, array('description'=> $c, 'slug' => $c_slug));
			multilang_switch_language($current_lang);
		}
	}
}


// Create all the pages from the site
function create_theme_pages() {
	$taxonomy_profile_name = get_option("taxonomy_profile_name");
	$taxonomy_profile_name_plural = get_option("taxonomy_profile_name_plural");
	$taxonomy_profile_url = get_option("taxonomy_profile_url");
	$settings_theme_genders = get_option("settings_theme_genders");
	$taxonomy_agency_name = get_option("taxonomy_agency_name");
	$taxonomy_agency_name_plural = get_option("taxonomy_agency_name_plural");
	$taxonomy_agency_url = get_option("taxonomy_agency_url");
	$taxonomy_location_url = get_option("taxonomy_location_url");

	//wp option name - page slug - page title - php file
	$pages = array(
		array('main_reg_page_id', 'registration', 'Register on Our Website', 'register-main-page.php'),
		array('escort_reg_page_id', $taxonomy_profile_url.'-registration', 'Independent '.ucwords($taxonomy_profile_name).' Registration', 'register-independent.php'),
		array('escort_tours_page_id', 'manage-my-tours', 'Manage my Tours', 'register-independent-manage-my-tours.php'),
		array('escort_edit_personal_info_page_id', 'edit-profile', 'Edit my Profile', 'register-independent-edit-personal-information.php'),
		array('change_password_page_id', 'change-password', 'Change Password', 'register-page-change-password.php'),
		array('escort_verified_status_page_id', 'verify-account', 'Verify Account', 'register-independent-verified-status.php'),
		array('escort_blacklist_clients_page_id', 'blacklisted-clients', 'Blacklisted Clients', 'blacklist-clients.php'),
		array('agency_reg_page_id', $taxonomy_agency_url.'-registration', ucwords($taxonomy_agency_name).' Register', 'register-agency.php'),

		array('all_profiles_page_id', 'all-'.sanitize_title($taxonomy_profile_name_plural), ucwords($taxonomy_profile_name_plural), 'all-profiles.php'),
		array('all_female_profiles_page_id', 'female-'.sanitize_title($taxonomy_profile_name_plural), 'Female '.ucwords($taxonomy_profile_name_plural), 'all-profiles.php'),
		array('all_male_profiles_page_id', 'male-'.sanitize_title($taxonomy_profile_name_plural), 'Male '.ucwords($taxonomy_profile_name_plural), 'all-profiles.php'),
		array('all_couple_profiles_page_id', 'couple-'.sanitize_title($taxonomy_profile_name_plural), 'Couple '.ucwords($taxonomy_profile_name_plural), 'all-profiles.php'),
		array('all_gay_profiles_page_id', 'gay-'.sanitize_title($taxonomy_profile_name_plural), 'Gay '.ucwords($taxonomy_profile_name_plural), 'all-profiles.php'),
		array('all_trans_profiles_page_id', 'transsexual-'.sanitize_title($taxonomy_profile_name_plural), 'Transsexual '.ucwords($taxonomy_profile_name_plural), 'all-profiles.php'),
		array('all_independent_profiles_page_id', 'independent-'.sanitize_title($taxonomy_profile_name_plural), 'Independent '.ucwords($taxonomy_profile_name_plural), 'all-profiles.php'),
		array('all_premium_profiles_page_id', 'premium-'.sanitize_title($taxonomy_profile_name_plural), 'Premium '.ucwords($taxonomy_profile_name_plural), 'all-profiles.php'),
		array('all_verified_profiles_page_id', 'verified-'.sanitize_title($taxonomy_profile_name_plural), 'Verified '.ucwords($taxonomy_profile_name_plural), 'all-profiles.php'),
		array('all_new_profiles_page_id', 'new-'.sanitize_title($taxonomy_profile_name_plural), 'New '.ucwords($taxonomy_profile_name_plural), 'all-profiles.php'),
		array('all_online_profiles_page_id', 'online-'.sanitize_title($taxonomy_profile_name_plural), 'Online '.ucwords($taxonomy_profile_name_plural), 'all-profiles.php'),

		array('agency_edit_personal_info_page_id', $taxonomy_agency_url.'-edit-profile', 'Edit Your '.ucfirst($taxonomy_agency_name).' Profile', 'register-agency-edit-personal-information.php'),
		array('agency_upload_logo_page_id', 'upload-logo', 'Upload/Edit your Logo', 'register-agency-upload-logo.php'),
		array('agency_manage_escorts_page_id', 'manage-'.sanitize_title($taxonomy_profile_name_plural), 'Manage your '.ucwords($taxonomy_profile_name_plural), 'register-agency-manage-escorts.php'),
		array('member_register_page_id', 'member-registration', 'Member Registration', 'register-member.php'),
		array('member_edit_personal_info_page_id', 'member-edit-profile', 'Member Edit Profile', 'register-member-edit-personal-information.php'),
		array('member_favorite_escorts_page_id', 'favorite-'.sanitize_title($taxonomy_profile_name_plural), 'My Favorite '.ucwords($taxonomy_profile_name_plural), 'register-member-see-favorites.php'),
		array('member_reviews_page_id', 'my-reviews', 'My Reviews', 'register-member-see-reviews.php'),
		array('city_tours_page_id', sanitize_title($taxonomy_profile_name_plural).'-on-tour', ucwords($taxonomy_profile_name_plural).' on Tour', 'nav-city-tours.php'),
		array('nav_reviews_page_id', 'reviews', 'Reviews', 'nav-reviews.php'),
		array('nav_reviews_agencies_page_id', $taxonomy_agency_url.'-reviews', ucwords($taxonomy_agency_name).' Reviews', 'nav-reviews-agencies.php'),
		array('list_agencies_page_id', sanitize_title($taxonomy_agency_name_plural), ucwords($taxonomy_agency_name_plural), 'nav-agencies.php'),
		array('contact_page_id', 'contact-us', 'Contact us', 'nav-contact.php'),
		array('search_page_id', 'search-for-'.sanitize_title($taxonomy_profile_name_plural), 'Search for '.ucwords($taxonomy_profile_name_plural), 'nav-search.php'),
		array('blacklisted_escorts_page_id', sanitize_title($taxonomy_profile_name_plural).'-blacklist', 'Blacklisted '.ucwords($taxonomy_profile_name_plural), 'blacklisted-escorts.php'),
		array('manage_ads_page_id', 'manage-classified-ads', 'Manage Classified Ads', 'manage-classified-ads.php'),
		array('see_all_ads_page_id', 'classified-ads', 'Classified ads', 'nav-classified-ads.php'),
		array('see_offering_ads_page_id', 'classified-ads-offering', 'Classified Ads - Offering', 'nav-classified-ads-offering.php'),
		array('see_looking_ads_page_id', 'classified-ads-looking', 'Classified Ads - Looking', 'nav-classified-ads-looking.php'),
		array('edit_payment_settings_page_id', 'payment-settings', 'Edit Payment Settings', 'edit-payment-settings.php'),
		array('edit_user_types', 'edit-user-types', 'Edit User Types', 'edit-user-types.php'),

		array('edit_registration_form_escort', 'edit-registration-form-for-'.$taxonomy_profile_url, 'Edit Registration Form for '.ucwords($taxonomy_profile_name), 'edit-registration-form-escort.php'),
		array('nav_blacklisted_escorts_page_id', 'blacklisted-'.sanitize_title($taxonomy_profile_name_plural), 'Blacklisted '.ucwords($taxonomy_profile_name_plural), 'nav-blacklisted-escorts.php'),
		array('email_settings_page_id', 'edit-email-options', 'Email Options', 'edit-email-options.php'),
		array('site_settings_page_id', 'edit-site-settings', 'Site Settings', 'edit-site-settings.php'),
		array('content_settings_page_id', 'content-settings', 'Content Settings', 'edit-content-settings.php'),
		array('blog_page_id', 'blog', 'Our Blog', 'nav-blog.php'),
		array('generate_demo_data_page', 'generate-demo-data', 'Generate Demo Data', 'admin-generate-demo-data.php')
	);

	foreach($pages as $p) {
		$new_page = array(
			'post_author' => "1",
			'comment_status' => "closed",
			'ping_status' => "closed",
			'post_name' => $p[1], // slug
			'post_title' => $p[2], // title
			'post_status' => "publish",
			'post_type' => "page",
			'page_template'  => $p[3]
		);
		global $wpdb;
		if (get_option($p[0]) > 0) {
			//update page details
			$page_id = get_option($p[0]);
			$new_page['ID'] = $page_id;
			wp_update_post($new_page);
			$wpdb->update( $wpdb->postmeta, array( 'post_id' => $page_id, 'meta_key' => '_wp_page_template', 'meta_value' => $p[4] ), '', array( '%d', '%s', '%s' ));
		} else {
			//create page
			$current_lang = multilang_switch_language();
			$new_page_id = wp_insert_post( $new_page );
			multilang_switch_language($current_lang);
			if ($new_page_id && $new_page_id != "0") {
				update_option($p[0], $new_page_id);
				$wpdb->insert( $wpdb->postmeta, array( 'post_id' => $new_page_id, 'meta_key' => '_wp_page_template', 'meta_value' => $p[4] ), array( '%d', '%s', '%s' ) );
			}
		}
	} // foreach

	//all pages are now created so we don't need to run this again
	update_option('are_all_pages_created', 'yes');

	flush_rewrite_rules();

	return count($pages);
} // create the site pages


if (is_user_logged_in() && isset($_GET['activated']) && !get_option('is_theme_installed')) {
	wp_redirect(site_url().'/?install=yes'); die();
}
function install_theme_wizard() {
	if(!get_option('is_theme_installed')) {
		if($_GET['install'] == "yes") {
			include(get_template_directory().'/functions-install-theme-wizard.php');
		} else { ?>
			<div class="all">
				<div class="err rad5">
					<p>
					<?=__("This theme has not been configured yet.",'escortwp')?><br />
					<?=__("In order to use the theme you will need to set your default options first.",'escortwp')?>
					</p>
					<p>
						<a href="<?php echo bloginfo('url').'/?install=yes'; ?>" class="pinkbutton rad3"><?=__("Configure your theme",'escortwp')?></a>
					</p>
					<div class="clear"></div>
				</div> <!-- err -->
			</div> <!-- all -->

			</body>
		</html>
		<?php
		}
		die();
	} // is theme installed?
} // function install_theme_wizard()


function char_to_utf8($string) {
	$array = preg_split("//u", strtolower(trim($string)), -1, PREG_SPLIT_NO_EMPTY);
	$chars = array ("Ä" => 'Ae', 'ä' => 'ae', 'Æ' => 'Ae', 'æ' => 'ae', 'À' => 'A', 'à' => 'a', 'Á' => 'A', 'á' => 'a', 'Â' => 'A', 'â' => 'a', 'Ã' => 'A', 'ã' => 'a', 'Å' => 'A', 'å' => 'a', 'ª' => 'a', 'ₐ' => 'a', 'ā' => 'a', 'Ć' => 'C', 'ć' => 'c', 'Ç' => 'C', 'ç' => 'c', 'Ð' => 'D', 'đ' => 'd', 'È' => 'E', 'è' => 'e', 'É' => 'E', 'é' => 'e', 'Ê' => 'E', 'ê' => 'e', 'Ë' => 'E', 'ë' => 'e', 'ₑ' => 'e', 'ƒ' => 'f', 'ğ' => 'g', 'Ğ' => 'G', 'Ì' => 'I', 'ì' => 'i', 'Í' => 'I', 'í' => 'i', 'Î' => 'I', 'î' => 'i', 'Ï' => 'Ii', 'ï' => 'ii', 'ī' => 'i', 'ı' => 'i', 'I' => 'I', 'Ñ' => 'N', 'ñ' => 'n', 'ⁿ' => 'n', 'Ò' => 'O', 'ò' => 'o', 'Ó' => 'O', 'ó' => 'o', 'Ô' => 'O', 'ô' => 'o', 'Õ' => 'O', 'õ' => 'o', 'Ø' => 'O', 'ø' => 'o', 'ₒ' => 'o', 'Ö' => 'Oe', 'ö' => 'oe', 'Œ' => 'Oe', 'œ' => 'oe', 'ß' => 'ss', 'Š' => 'S', 'š' => 's', 'ş' => 's', 'Ş' => 'S', '™' => 'TM', 'Ù' => 'U', 'ù' => 'u', 'Ú' => 'U', 'ú' => 'u', 'Û' => 'U', 'û' => 'u', 'Ü' => 'Ue', 'ü' => 'ue', 'Ý' => 'Y', 'ý' => 'y', 'ÿ' => 'y', 'Ž' => 'Z', 'ž' => 'z',
	// Russian
	'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'TS', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SCH', 'Ъ' => '', 'Ы' => 'YI', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA', 'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'yi', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya');

	foreach($array as $key=>$s) {
		if($chars[$s]) {
			$array[$key] = $chars[$s];
		}
	}
	return implode($array);
}


function generate_demo_data() {
	if(!get_option('generate_demo_data_alert') && current_user_can('level_10')) {
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('.dont_show_me_this_again').on('click', function() {
			$.ajax({
				type: "GET",
				url: "<?php bloginfo('template_url'); ?>/ajax/settings-saver.php",
				data: "hide_demo_data_alert=yes"
			});
			$('.demo_data_intro').slideUp();
		});
	});
	</script>
	<div class="all demo_data_intro">
		<div class="ok rad5">
			<p>
				<?php _e('It looks like this is your first time viewing the theme','escortwp'); ?>.<br />
				<?php _e('Do you want to generate some test profiles?','escortwp'); ?><br />
				<?php _e('That way you can see better how the theme looks','escortwp'); ?>.
			</p>
			<div class="clear10"></div>
			<div class="col50 center">
				<a href="<?php echo get_permalink(get_option('generate_demo_data_page')); ?>" class="pinkbutton rad25 l"><?php _e('Generate demo data','escortwp'); ?></a>
				<div class="graybutton rad25 r dont_show_me_this_again"><?php _e('Don\'t show me this again','escortwp'); ?></div>
			</div>
			<div class="clear10"></div>
		</div> <!-- ok -->
	</div> <!-- all -->
	<?php
	} // if !get_option('generate_demo_data')
}


function generate_random_name($gender) {
	if(in_array($gender, array('1', '5'))) { //if gender is for female or trans
		$gender = "1";
	} elseif(in_array($gender, array('2', '4'))) { //if gender is for male or gay
		$gender = "2";
	}
	$names = array(
		//female, transsexual
		'1' => array('Aaliyah','Abagail','Abbey','Abbie','Abbigail','Abby','Abigail','Abigayle','Abril','Ada','Adalyn','Adalynn','Addison','Addisyn','Addyson','Adelaide','Adeline','Adelyn','Adison','Adriana','Adrianna','Adrienne','Adyson','Aileen','Aimee','Ainsley','Aisha','Aiyana','Akira','Alaina','Alana','Alani','Alanna','Alannah','Alayna','Aleah','Aleena','Alejandra','Alena','Alessandra','Alexa','Alexandra','Alexandria','Alexia','Alexis','Alexus','Ali','Alia','Aliana','Alice','Alicia','Alina','Alisa','Alisha','Alison','Alissa','Alisson','Alivia','Aliya','Aliyah','Aliza','Allie','Allison','Allisson','Ally','Allyson','Alma','Alondra','Alyson','Alyssa','Alyvia','Amanda','Amani','Amara','Amari','Amaris','Amaya','Amber','Amelia','Amelie','America','Amiah','Amina','Amira','Amirah','Amiya','Amiyah','Amy','Amya','Ana','Anabel','Anabella','Anabelle','Anahi','Anastasia','Anaya','Andrea','Angel','Angela','Angelica','Angelina','Angeline','Angelique','Angie','Anika','Aniya','Aniyah','Ann','Anna','Annabel','Annabella','Annabelle','Annalise','Anne','Annie','Annika','Ansley','Anya','April','Arabella','Araceli','Aracely','Areli','Arely','Aria','Ariana','Arianna','Ariel','Ariella','Arielle','Armani','Aryana','Aryanna','Ashanti','Ashlee','Ashleigh','Ashley','Ashly','Ashlyn','Ashlynn','Ashtyn','Asia','Aspen','Athena','Aubree','Aubrey','Aubrie','Audrey','Audrina','Aurora','Autumn','Ava','Avah','Averi','Averie','Avery','Ayana','Ayanna','Ayla','Aylin','Azaria','Azul','Bailee','Bailey','Barbara','Baylee','Beatrice','Belen','Belinda','Bella','Bethany','Bianca','Braelyn','Breanna','Brenda','Brenna','Bria','Briana','Brianna','Bridget','Brielle','Briley','Brisa','Britney','Brittany','Brooke','Brooklyn','Brooklynn','Bryanna','Brylee','Brynlee','Brynn','Cadence','Cailyn','Caitlin','Caitlyn','Cali','Callie','Cameron','Camila','Camilla','Camille','Campbell','Camryn','Cara','Carina','Carissa','Carla','Carlee','Carleigh','Carley','Carlie','Carly','Carmen','Carolina','Caroline','Carolyn','Casey','Cassandra','Cassidy','Cassie','Catalina','Catherine','Caylee','Cecelia','Cecilia','Celeste','Celia','Chana','Chanel','Charity','Charlee','Charlie','Charlize','Charlotte','Chasity','Chaya','Chelsea','Cherish','Cheyanne','Cheyenne','Chloe','Christina','Christine','Ciara','Cierra','Cindy','Claire','Clara','Clare','Clarissa','Claudia','Cloe','Cora','Corinne','Courtney','Cristal','Cristina','Crystal','Cynthia','Dahlia','Daisy','Dakota','Dalia','Damaris','Dana','Dania','Danica','Daniela','Daniella','Danielle','Danika','Danna','Daphne','Dayami','Dayana','Dayanara','Deanna','Deborah','Deja','Delaney','Delilah','Denise','Denisse','Desirae','Desiree','Destinee','Destiney','Destiny','Diamond','Diana','Dixie','Diya','Dominique','Donna','Dulce','Dylan','Eden','Edith','Eileen','Elaina','Elaine','Eleanor','Elena','Eliana','Elianna','Elisa','Elisabeth','Elise','Eliza','Elizabeth','Ella','Elle','Ellen','Elliana','Ellie','Elsa','Elsie','Elyse','Emelia','Emely','Emerson','Emery','Emilee','Emilia','Emilie','Emily','Emma','Emmalee','Emmy','Erica','Erika','Erin','Esmeralda','Esperanza','Essence','Esther','Estrella','Eva','Evangeline','Eve','Evelin','Evelyn','Evie','Faith','Fatima','Felicity','Fernanda','Finley','Fiona','Frances','Francesca','Frida','Gabriela','Gabriella','Gabrielle','Gemma','Genesis','Genevieve','Georgia','Gia','Giada','Giana','Gianna','Gillian','Gina','Giovanna','Giselle','Gisselle','Giuliana','Gloria','Grace','Gracelyn','Gracie','Greta','Gretchen','Guadalupe','Gwendolyn','Hadassah','Hadley','Hailee','Hailey','Hailie','Haleigh','Haley','Halle','Hallie','Hana','Hanna','Hannah','Harley','Harmony','Harper','Haven','Hayden','Haylee','Hayley','Haylie','Hazel','Heather','Heaven','Heidi','Heidy','Helen','Helena','Hillary','Holly','Hope','Iliana','Imani','India','Ingrid','Ireland','Irene','Iris','Isabel','Isabela','Isabell','Isabella','Isabelle','Isis','Isla','Itzel','Ivy','Iyana','Izabella','Izabelle','Jacey','Jacqueline','Jacquelyn','Jada','Jade','Jaden','Jadyn','Jaelyn','Jaelynn','Jaida','Jaiden','Jaidyn','Jakayla','Jaliyah','Jamie','Jamiya','Jamya','Janae','Jane','Janelle','Janessa','Janet','Janiah','Janiya','Janiyah','Jaqueline','Jaslene','Jaslyn','Jasmin','Jasmine','Jaycee','Jayda','Jayden','Jayla','Jaylah','Jaylee','Jayleen','Jaylen','Jaylene','Jaylin','Jaylyn','Jaylynn','Jazlene','Jazlyn','Jazlynn','Jazmin','Jazmine','Jazmyn','Jenna','Jennifer','Jenny','Jessica','Jessie','Jewel','Jillian','Jimena','Joanna','Jocelyn','Jocelynn','Johanna','Jolie','Jordan','Jordin','Jordyn','Joselyn','Josephine','Josie','Joslyn','Journey','Joy','Joyce','Judith','Julia','Juliana','Julianna','Julianne','Julie','Juliet','Juliette','Julissa','June','Justice','Justine','Kadence','Kaelyn','Kaia','Kaila','Kailee','Kailey','Kailyn','Kaitlin','Kaitlyn','Kaitlynn','Kaiya','Kaleigh','Kaley','Kali','Kaliyah','Kallie','Kamari','Kamila','Kamora','Kamryn','Kara','Karen','Karina','Karissa','Karla','Karlee','Karley','Karli','Karlie','Karly','Karma','Karsyn','Kasey','Kassandra','Kassidy','Kate','Katelyn','Katelynn','Katherine','Kathleen','Kathryn','Kathy','Katie','Katrina','Kaya','Kayden','Kaydence','Kayla','Kaylah','Kaylee','Kayleigh','Kaylen','Kayley','Kaylie','Kaylin','Kaylyn','Kaylynn','Keely','Keira','Kelly','Kelsey','Kelsie','Kendal','Kendall','Kendra','Kenley','Kenna','Kennedi','Kennedy','Kenya','Kenzie','Keyla','Khloe','Kiana','Kianna','Kiara','Kiera','Kierra','Kiersten','Kiley','Kimberly','Kimora','Kinley','Kinsley','Kira','Kirsten','Krista','Kristen','Kristin','Kristina','Krystal','Kyla','Kylee','Kyleigh','Kylie','Kyra','Lacey','Laci','Laila','Lailah','Lainey','Lana','Laney','Lara','Larissa','Laura','Laurel','Lauren','Lauryn','Layla','Laylah','Lea','Leah','Leanna','Leia','Leila','Leilani','Lena','Leslie','Lesly','Leticia','Lexi','Lexie','Leyla','Lia','Liana','Libby','Liberty','Lila','Lilah','Lilia','Lilian','Liliana','Lilianna','Lillian','Lilliana','Lillianna','Lillie','Lilly','Lily','Lilyana','Lina','Linda','Lindsay','Lindsey','Lisa','Litzy','Livia','Lizbeth','Lizeth','Logan','Lola','London','Londyn','Lorelai','Lorelei','Lorena','Lucia','Luciana','Lucille','Lucy','Luna','Luz','Lydia','Lyla','Lyric','Macey','Maci','Macie','Mackenzie','Macy','Madalyn','Madalynn','Maddison','Madeleine','Madeline','Madelyn','Madelynn','Madilyn','Madilynn','Madison','Madisyn','Madyson','Maeve','Magdalena','Maggie','Maia','Makaila','Makayla','Makena','Makenna','Makenzie','Maleah','Malia','Maliyah','Mallory','Mara','Mareli','Marely','Maren','Margaret','Maria','Mariah','Mariam','Mariana','Marianna','Maribel','Marie','Mariela','Marilyn','Marin','Marina','Marisa','Marisol','Marissa','Maritza','Mariyah','Marlee','Marlene','Marley','Marlie','Martha','Mary','Maryjane','Matilda','Mattie','Maya','Mayra','Mckayla','Mckenna','Mckenzie','Mckinley','Meadow','Megan','Meghan','Melanie','Melany','Melina','Melissa','Melody','Mercedes','Meredith','Mia','Miah','Micaela','Micah','Michaela','Michelle','Mikaela','Mikayla','Mila','Milagros','Miley','Mina','Mira','Miracle','Miranda','Mireya','Miriam','Miya','Mollie','Molly','Monica','Monique','Monserrat','Morgan','Moriah','Mya','Myah','Myla','Mylee','Mylie','Nadia','Naima','Nancy','Naomi','Natalee','Natalia','Natalie','Nataly','Natalya','Natasha','Nathalia','Nathalie','Nathaly','Nayeli','Nevaeh','Neveah','Nia','Nicole','Nina','Noelle','Noemi','Nola','Nora','Norah','Nyasia','Nyla','Nylah','Olive','Olivia','Paige','Paisley','Paityn','Paloma','Pamela','Paola','Paris','Parker','Patience','Patricia','Paula','Paulina','Payten','Payton','Penelope','Perla','Peyton','Phoebe','Phoenix','Piper','Precious','Presley','Princess','Priscilla','Quinn','Rachael','Rachel','Raegan','Raelynn','Raina','Raquel','Raven','Rayna','Rayne','Reagan','Rebecca','Rebekah','Reese','Regan','Regina','Reina','Renee','Reyna','Rhianna','Rihanna','Riley','Riya','Rory','Rosa','Rose','Roselyn','Rosemary','Rowan','Rubi','Ruby','Ruth','Ryan','Ryann','Rylee','Ryleigh','Rylie','Sabrina','Sadie','Sage','Saige','Salma','Samantha','Samara','Sanaa','Sanai','Sandra','Saniya','Saniyah','Sara','Sarah','Sarahi','Sarai','Sariah','Sasha','Savanah','Savanna','Savannah','Scarlet','Scarlett','Selah','Selena','Selina','Serena','Serenity','Shania','Shaniya','Shannon','Sharon','Shayla','Shaylee','Shayna','Shea','Shelby','Sherlyn','Shiloh','Shirley','Shyann','Shyanne','Shyla','Sidney','Siena','Sienna','Sierra','Simone','Skye','Skyla','Skylar','Skyler','Sloane','Sofia','Sonia','Sophia','Sophie','Stacy','Stella','Stephanie','Stephany','Summer','Susan','Sydnee','Sydney','Sylvia','Tabitha','Talia','Taliyah','Tamara','Tamia','Tania','Taniya','Taniyah','Tanya','Tara','Taryn','Tatiana','Tatum','Taylor','Teagan','Teresa','Tess','Tessa','Thalia','Theresa','Tia','Tiana','Tianna','Tiara','Tiffany','Tori','Trinity','Valentina','Valeria','Valerie','Valery','Vanessa','Veronica','Victoria','Violet','Virginia','Vivian','Viviana','Wendy','Whitney','Willow','Ximena','Xiomara','Yadira','Yamilet','Yareli','Yaretzi','Yaritza','Yasmin','Yasmine','Yazmin','Yesenia','Yoselin','Yuliana','Zaniyah','Zara','Zaria','Zariah','Zion','Zoe','Zoey','Zoie'),
		//male, gay
		'2' => array('Aaden','Aarav','Aaron','Abdiel','Abdullah','Abel','Abraham','Abram','Ace','Adam','Adan','Addison','Aden','Aditya','Adolfo','Adonis','Adrian','Adriel','Adrien','Aedan','Agustin','Ahmad','Ahmed','Aidan','Aiden','Aidyn','Alan','Albert','Alberto','Alden','Aldo','Alec','Alejandro','Alessandro','Alex','Alexander','Alexis','Alexzander','Alfonso','Alfred','Alfredo','Ali','Alijah','Allan','Allen','Alonso','Alonzo','Alvaro','Alvin','Amare','Amari','Amir','Anderson','Andre','Andreas','Andres','Andrew','Andy','Angel','Angelo','Anthony','Antoine','Anton','Antonio','Antony','Antwan','Ari','Ariel','Arjun','Armando','Armani','Arnav','Aron','Arthur','Arturo','Aryan','Asa','Asher','Ashton','Atticus','August','Augustus','Austin','Avery','Axel','Ayaan','Aydan','Ayden','Aydin','Bailey','Baron','Barrett','Beau','Beckett','Beckham','Ben','Benjamin','Bennett','Bentley','Bernard','Billy','Blaine','Blake','Blaze','Bo','Bobby','Boston','Braden','Bradley','Brady','Bradyn','Braeden','Braedon','Braiden','Branden','Brandon','Branson','Braxton','Brayan','Brayden','Braydon','Braylen','Braylon','Brendan','Brenden','Brendon','Brennan','Brennen','Brent','Brenton','Brett','Brian','Brice','Bridger','Brock','Broderick','Brodie','Brody','Brogan','Bronson','Brooks','Bruce','Bruno','Bryan','Bryant','Bryce','Brycen','Bryson','Byron','Cade','Caden','Cael','Caiden','Cale','Caleb','Callum','Calvin','Camden','Cameron','Camren','Camron','Camryn','Cannon','Carl','Carlo','Carlos','Carmelo','Carsen','Carson','Carter','Case','Casey','Cash','Cason','Cassius','Cayden','Cedric','Cesar','Chace','Chad','Chaim','Chance','Chandler','Charles','Charlie','Chase','Chaz','Chris','Christian','Christopher','Clarence','Clark','Clay','Clayton','Clinton','Coby','Cody','Cohen','Colby','Cole','Coleman','Colin','Collin','Colt','Colten','Colton','Conner','Connor','Conor','Conrad','Cooper','Corbin','Cordell','Corey','Cornelius','Cortez','Cory','Craig','Cristian','Cristofer','Cristopher','Cruz','Cullen','Curtis','Cyrus','Dakota','Dale','Dallas','Dalton','Damari','Damarion','Damian','Damien','Damion','Damon','Dane','Dangelo','Daniel','Danny','Dante','Darian','Darien','Dario','Darion','Darius','Darnell','Darrell','Darren','Darryl','Darwin','Dashawn','Davian','David','Davin','Davion','Davis','Davon','Dawson','Dax','Dayton','Deacon','Dean','Deandre','Deangelo','Declan','Deegan','Demarcus','Demarion','Demetrius','Dennis','Denzel','Deon','Derek','Dereon','Derick','Derrick','Deshawn','Desmond','Devan','Deven','Devin','Devon','Devyn','Dexter','Diego','Dillan','Dillon','Dominic','Dominick','Dominik','Dominique','Donald','Donavan','Donovan','Donte','Dorian','Douglas','Drake','Draven','Drew','Duncan','Dustin','Dwayne','Dylan','Ean','Easton','Eddie','Eden','Edgar','Eduardo','Edward','Edwin','Efrain','Eli','Elian','Elias','Eliezer','Elijah','Elisha','Elliot','Elliott','Ellis','Elvis','Emanuel','Emerson','Emery','Emiliano','Emilio','Emmanuel','Emmett','Enrique','Enzo','Eric','Erick','Erik','Ernest','Ernesto','Esteban','Ethan','Ethen','Eugene','Evan','Everett','Ezekiel','Ezequiel','Ezra','Fabian','Felipe','Felix','Fernando','Finley','Finn','Finnegan','Fisher','Fletcher','Francis','Francisco','Franco','Frank','Frankie','Franklin','Freddy','Frederick','Gabriel','Gael','Gage','Gaige','Garrett','Gary','Gauge','Gaven','Gavin','Gavyn','George','Geovanni','Gerald','Gerardo','German','Giancarlo','Gianni','Gideon','Gilbert','Gilberto','Giovani','Giovanni','Giovanny','Glenn','Gordon','Grady','Graham','Grant','Grayson','Gregory','Greyson','Griffin','Guillermo','Gunnar','Gunner','Gustavo','Haiden','Hamza','Harley','Harold','Harper','Harrison','Harry','Hassan','Hayden','Heath','Hector','Henry','Hezekiah','Holden','Houston','Howard','Hudson','Hugh','Hugo','Humberto','Hunter','Ian','Ibrahim','Ignacio','Immanuel','Irvin','Isaac','Isai','Isaiah','Isaias','Ishaan','Isiah','Ismael','Israel','Issac','Ivan','Izaiah','Izayah','Jabari','Jace','Jack','Jackson','Jacob','Jacoby','Jaden','Jadiel','Jadon','Jadyn','Jaeden','Jagger','Jaiden','Jaidyn','Jaime','Jair','Jairo','Jake','Jakob','Jakobe','Jalen','Jamal','Jamar','Jamarcus','Jamari','Jamarion','James','Jameson','Jamie','Jamir','Jamison','Jan','Jaquan','Jared','Jaron','Jarrett','Jase','Jasiah','Jason','Jasper','Javier','Javion','Javon','Jax','Jaxon','Jaxson','Jay','Jayce','Jaydan','Jayden','Jaydin','Jaydon','Jaylan','Jaylen','Jaylin','Jaylon','Jayson','Jayvion','Jayvon','Jean','Jefferson','Jeffery','Jeffrey','Jensen','Jeramiah','Jeremiah','Jeremy','Jerimiah','Jermaine','Jerome','Jerry','Jesse','Jessie','Jett','Jimmy','Joaquin','Joe','Joel','Joey','Johan','John','Johnathan','Johnathon','Johnny','Jon','Jonah','Jonas','Jonathan','Jonathon','Jordan','Jorden','Jordon','Jordyn','Jorge','Jose','Joseph','Josh','Joshua','Josiah','Josue','Jovan','Jovani','Jovanni','Jovanny','Jovany','Juan','Judah','Jude','Julian','Julien','Julio','Julius','Junior','Justice','Justin','Justus','Kade','Kaden','Kadin','Kadyn','Kaeden','Kael','Kai','Kaiden','Kale','Kaleb','Kamari','Kamden','Kameron','Kamren','Kamron','Kane','Kareem','Karson','Karter','Kasen','Kasey','Kash','Kason','Kayden','Keagan','Keaton','Keegan','Keenan','Keith','Kellen','Kelton','Kelvin','Kendall','Kendrick','Kenneth','Kenny','Kenyon','Keon','Keshawn','Kevin','Keyon','Khalil','Kian','Kieran','Killian','King','Kingston','Kobe','Kody','Koen','Kolby','Kole','Kolten','Kolton','Konner','Konnor','Korbin','Krish','Kristian','Kristopher','Kyan','Kylan','Kyle','Kyler','Kymani','Kyson','Lamar','Lamont','Lance','Landen','Landin','Landon','Landyn','Lane','Larry','Lawrence','Lawson','Layne','Layton','Leandro','Lee','Leland','Lennon','Leo','Leon','Leonard','Leonardo','Leonel','Leonidas','Leroy','Levi','Lewis','Liam','Lincoln','Logan','London','Lorenzo','Louis','Luca','Lucas','Lucian','Luciano','Luis','Luka','Lukas','Luke','Lyric','Madden','Maddox','Makai','Makhi','Malachi','Malakai','Malaki','Malcolm','Malik','Manuel','Marc','Marcel','Marcelo','Marco','Marcos','Marcus','Mario','Mark','Markus','Marley','Marlon','Marques','Marquis','Marquise','Marshall','Martin','Marvin','Mason','Mateo','Mathew','Mathias','Matias','Matteo','Matthew','Matthias','Maurice','Mauricio','Maverick','Max','Maxim','Maximilian','Maximillian','Maximo','Maximus','Maxwell','Mekhi','Melvin','Memphis','Messiah','Micah','Michael','Micheal','Miguel','Mike','Miles','Milo','Milton','Misael','Mitchell','Moises','Morgan','Moses','Moshe','Muhammad','Myles','Nash','Nasir','Nathan','Nathanael','Nathanial','Nathaniel','Nathen','Nehemiah','Neil','Nelson','Nicholas','Nick','Nickolas','Nico','Nicolas','Nigel','Nikhil','Niko','Nikolai','Nikolas','Noah','Noe','Noel','Nolan','Octavio','Odin','Oliver','Omar','Omari','Orion','Orlando','Oscar','Osvaldo','Oswaldo','Owen','Pablo','Parker','Patrick','Paul','Paxton','Payton','Pedro','Peter','Peyton','Philip','Phillip','Phoenix','Pierce','Pierre','Porter','Pranav','Preston','Prince','Quentin','Quincy','Quinn','Quinten','Quintin','Quinton','Rafael','Raiden','Ralph','Ramiro','Ramon','Randall','Randy','Raphael','Rashad','Raul','Ray','Rayan','Raymond','Reagan','Reece','Reed','Reese','Reginald','Reid','Reilly','Remington','Rene','Reuben','Rex','Rey','Reynaldo','Rhett','Rhys','Ricardo','Richard','Ricky','Rigoberto','Riley','Rishi','River','Robert','Roberto','Rocco','Roderick','Rodney','Rodolfo','Rodrigo','Rogelio','Roger','Rohan','Roland','Rolando','Roman','Romeo','Ronald','Ronan','Ronin','Ronnie','Rory','Ross','Rowan','Roy','Royce','Ruben','Rudy','Russell','Ryan','Ryder','Ryker','Rylan','Ryland','Rylee','Sage','Salvador','Salvatore','Sam','Samir','Sammy','Samson','Samuel','Santiago','Santino','Santos','Saul','Savion','Sawyer','Scott','Seamus','Sean','Sebastian','Semaj','Sergio','Seth','Shamar','Shane','Shaun','Shawn','Sheldon','Sidney','Silas','Simeon','Simon','Sincere','Skylar','Skyler','Slade','Solomon','Sonny','Soren','Spencer','Stanley','Stephen','Sterling','Steve','Steven','Sullivan','Talan','Talon','Tanner','Tate','Taylor','Teagan','Terrance','Terrell','Terrence','Terry','Thaddeus','Theodore','Thomas','Timothy','Titus','Tobias','Toby','Todd','Tomas','Tommy','Tony','Trace','Travis','Trent','Trenton','Trevin','Trevon','Trevor','Trey','Tripp','Tristan','Tristen','Tristian','Tristin','Triston','Troy','Trystan','Tucker','Turner','Ty','Tyler','Tyree','Tyrell','Tyrese','Tyrone','Tyshawn','Tyson','Ulises','Uriah','Uriel','Urijah','Valentin','Valentino','Van','Vance','Vaughn','Vicente','Victor','Vincent','Wade','Walker','Walter','Warren','Waylon','Wayne','Wesley','Weston','Will','William','Willie','Wilson','Winston','Wyatt','Xander','Xavier','Xzavier','Yadiel','Yael','Yahir','Yair','Yandel','Yosef','Yurem','Yusuf','Zachariah','Zachary','Zachery','Zack','Zackary','Zackery','Zaid','Zaiden','Zain','Zaire','Zander','Zane','Zavier','Zayden','Zayne','Zechariah','Zion')
	);
	if($gender == "3") { //if gender is 'couple' then return two names
		return $names['1'][rand(0, (count($names['1'])))].' '.__('and','escortwp').' '.$names['2'][rand(0, (count($names['2'])))];
	} else {
		return $names[$gender][rand(0, (count($names[$gender])))];
	}
}

function generate_random_user($type) {
	// type:
	// 1 - independent profile
	// 2 - agency
	// 3 - member
	global $taxonomy_profile_url, $taxonomy_agency_url;
	if($type == "1") {
		$type_name = $taxonomy_profile_url;
	} elseif($type == "2") {
		$type_name = $taxonomy_agency_url;
	} elseif($type == "3") {
		$type_name = 'member';
		$yourname = generate_random_name('1');
	}
	$new_user_name = 'user-'.substr(time(), 5).rand(0,9999);
	$new_user_pass = wp_generate_password('20');
	$new_user_email = $new_user_name."@example.com";
	if(email_exists($new_user_email)) {
		generate_random_user($type);
	} else {
		$new_user_id = wp_create_user( $new_user_name, $new_user_pass, $new_user_email );
		wp_update_user(array('ID' => $new_user_id, 'nickname' => $yourname, 'display_name' => $yourname));
		escortwp_child_update_dynamic_user_option("escortid".$new_user_id, $type_name);
		update_user_meta($new_user_id, 'randomly_generated_data', 'randomly_generated_data');
		return $new_user_id;
	}
}

function generate_random_profile($user_id, $gender) {
	global $taxonomy_agency_url, $taxonomy_profile_url, $taxonomy_location_url, $ethnicity_a, $haircolor_a, $hairlength_a, $bustsize_a, $build_a, $looks_a, $smoker_a, $availability_a, $languagelevel_a, $services_a;
	// gender
	// 1 - female
	// 2 - male
	// 3 - couple
	// 4 - gay
	// 5 - transsexual
	$yourname = generate_random_name($gender);
	$aboutyou = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus in quam pretium, sodales velit id, aliquet dui. Vivamus nunc augue, elementum vel nulla sit amet, scelerisque aliquet sapien. Vestibulum non commodo urna. Sed tempus lacus ac quam pharetra dapibus. Ut sollicitudin odio vitae nisi rhoncus finibus. Nam pharetra rutrum mauris, a ultricies quam varius sed. Sed sodales felis magna, in cursus orci vulputate at. Maecenas semper eros eu eros lacinia, ut pellentesque massa tincidunt.<br /><br />Integer tempus justo at lectus convallis, at vestibulum purus placerat. Ut ultrices enim non elit molestie fringilla ac sed sem. Fusce efficitur nibh nec congue dignissim. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer dictum sed magna eu posuere. Vestibulum facilisis ultricies risus quis venenatis. Fusce sed porttitor sapien. Suspendisse at sapien finibus nulla rhoncus fringilla at non eros.<br /><br />Vestibulum egestas interdum lectus. Donec mollis sodales magna, ac facilisis ligula lacinia eu. Nunc pretium, massa ac efficitur venenatis, tellus nibh faucibus metus, id pharetra sapien eros nec nisl. Maecenas sed fermentum nisl. Cras dui lectus, lobortis finibus odio feugiat, scelerisque vulputate arcu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam ut ex nisi.";
	$post_profile = array(
		'post_title' => $yourname,
		'post_content' => $aboutyou,
		'post_name' => $yourname,
		'post_status' => 'publish',
		'post_author' => $user_id,
		'post_type' => $taxonomy_profile_url,
		'ping_status' => 'closed'
	);
	// Insert the post into the database
	$current_lang = multilang_switch_language();
	$post_profile_id = wp_insert_post($post_profile);

	$country_and_city = get_country_and_city_id(); //returns an array with country id and city id
	$country_id = $country_and_city[0];
	$city_id = $country_and_city[1];

	wp_set_post_terms($post_profile_id, $city_id, $taxonomy_location_url);
	multilang_switch_language($current_lang);
	update_post_meta($post_profile_id, "phone", '555 1234 567');
	update_post_meta($post_profile_id, "website", "https://escortwp.com/");
	update_post_meta($post_profile_id, "country", $country_id);
	update_post_meta($post_profile_id, "city", $city_id);
	update_post_meta($post_profile_id, "gender", $gender);
	update_post_meta($post_profile_id, "birthday", rand('1970', '1989')."-".rand(1,12)."-".rand(1,28));
	update_post_meta($post_profile_id, "ethnicity", rand(1,count($ethnicity_a)));
	update_post_meta($post_profile_id, "haircolor", rand(1,count($haircolor_a)));
	update_post_meta($post_profile_id, "hairlength", rand(1,count($hairlength_a)));
	update_post_meta($post_profile_id, "bustsize", rand(1,count($bustsize_a)));
	update_post_meta($post_profile_id, "height", rand(150,170));
	update_post_meta($post_profile_id, "weight", rand(50,70));
	update_post_meta($post_profile_id, "build", rand(1,count($build_a)));
	update_post_meta($post_profile_id, "looks", rand(1,count($looks_a)));
	update_post_meta($post_profile_id, "smoker", rand(1,count($smoker_a)));
	update_post_meta($post_profile_id, "availability", array((string)rand(1,count($availability_a))));
	update_post_meta($post_profile_id, "education", 'College');
	update_post_meta($post_profile_id, "sports", 'Bicycle, Dance');
	update_post_meta($post_profile_id, "hobbies", 'Photography, Dancing');
	update_post_meta($post_profile_id, "zodiacsign", 'Capricorn');
	update_post_meta($post_profile_id, "sexualorientation", 'rather not say');
	update_post_meta($post_profile_id, "occupation", 'rather not say');
	update_post_meta($post_profile_id, "language1", 'english');
	update_post_meta($post_profile_id, "language1level", rand(1,count($languagelevel_a)));
	update_post_meta($post_profile_id, "language2", 'spanish');
	update_post_meta($post_profile_id, "language2level", rand(1,count($languagelevel_a)));
	update_post_meta($post_profile_id, "language3", 'italian');
	update_post_meta($post_profile_id, "language3level", rand(1,count($languagelevel_a)));
	update_post_meta($post_profile_id, "currency", '22');
	update_post_meta($post_profile_id, "rate30min_incall", '50');
	update_post_meta($post_profile_id, "rate1h_incall", '100');
	update_post_meta($post_profile_id, "rate2h_incall", '200');
	update_post_meta($post_profile_id, "rate3h_incall", '300');
	update_post_meta($post_profile_id, "rate6h_incall", '600');
	update_post_meta($post_profile_id, "rate12h_incall", '1200');
	update_post_meta($post_profile_id, "rate24h_incall", '2400');
	update_post_meta($post_profile_id, "services", array_rand($services_a, floor((count($services_a)/2))));
	update_post_meta($post_profile_id, "randomly_generated_data", 'randomly_generated_data');
	if(rand(1,10) > 7) {
		update_post_meta($post_profile_id, "premium", '1');
		update_post_meta($post_profile_id, "premium_since", time());
	} else {
		update_post_meta($post_profile_id, "premium", '0');
	}
	if(rand(1,10) > 7) {
		update_post_meta($post_profile_id, "featured", '1');
	}
	if(rand(1,10) > 7) {
		update_post_meta($post_profile_id, "verified", '1');
	}
	$secret = md5($yourname.time().rand(1,9999));
	$upload_folder = "demo".time().rand(1,9999);
	update_post_meta($post_profile_id, "secret", $secret);
	update_post_meta($post_profile_id, "upload_folder", $upload_folder);

	if(get_option("escortid".$user_id) == $taxonomy_agency_url) { //only add if the profile belongs to an agency
		update_option("agency".$secret, $post_profile_id);
	} else { //else the profile is independent
		escortwp_child_update_dynamic_user_option("escortid".$post_profile_id, $taxonomy_profile_url);
		escortwp_child_update_dynamic_user_option("escortpostid".$user_id, $post_profile_id);
		update_post_meta($post_profile_id, "independent", "yes");
		update_option($secret, $user_id);
	}

	for ($i=1; $i < 3; $i++) {
		$tempFile = get_template_directory().'/i/demo-profiles-images/'.rand(1,87).".jpg";
		$targetPath = ABSPATH.'wp-content/uploads/'.$upload_folder;
		$targetFile =  time().rand(1000, 9999).".jpg";
		if (!is_dir($targetPath)) { mkdir($targetPath, 0777, true); }
		if (copy($tempFile,$targetPath."/".$targetFile)) {
			$attachment = array(
				'post_mime_type' => 'image/jpeg',
				'guid' => get_bloginfo("url")."/wp-content/uploads/".$upload_folder."/".$targetFile,
				'post_status' => 'inherit',
				'post_parent' => $post_profile_id,
				'post_title' => $targetFile,
				'post_type ' => "attachment"
			);
			wp_insert_attachment($attachment, $targetPath."/".$targetFile, $post_profile_id);
		}
	}
	return $post_profile_id;
}

function generate_random_agency($user_id) {
	global $taxonomy_agency_name, $taxonomy_agency_url, $taxonomy_location_url;
	$post_agency = array(
		'post_title' => generate_random_name('1')." ".ucfirst($taxonomy_agency_name),
		'post_content' => "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus in quam pretium, sodales velit id, aliquet dui. Vivamus nunc augue, elementum vel nulla sit amet, scelerisque aliquet sapien. Vestibulum non commodo urna. Sed tempus lacus ac quam pharetra dapibus. Ut sollicitudin odio vitae nisi rhoncus finibus. Nam pharetra rutrum mauris, a ultricies quam varius sed. Sed sodales felis magna, in cursus orci vulputate at. Maecenas semper eros eu eros lacinia, ut pellentesque massa tincidunt.</p><p>Integer tempus justo at lectus convallis, at vestibulum purus placerat. Ut ultrices enim non elit molestie fringilla ac sed sem. Fusce efficitur nibh nec congue dignissim. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer dictum sed magna eu posuere. Vestibulum facilisis ultricies risus quis venenatis. Fusce sed porttitor sapien. Suspendisse at sapien finibus nulla rhoncus fringilla at non eros.</p><p>Vestibulum egestas interdum lectus. Donec mollis sodales magna, ac facilisis ligula lacinia eu. Nunc pretium, massa ac efficitur venenatis, tellus nibh faucibus metus, id pharetra sapien eros nec nisl. Maecenas sed fermentum nisl. Cras dui lectus, lobortis finibus odio feugiat, scelerisque vulputate arcu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam ut ex nisi.</p>",
		'post_status' => 'publish',
		'post_author' => $user_id,
		'post_type' => $taxonomy_agency_url,
		'ping_status' => 'closed'
	);
	// Insert the post into the database
	$current_lang = multilang_switch_language();
	$post_agency_id = wp_insert_post( $post_agency );
	$country_and_city = get_country_and_city_id(); //returns an array with country id and city id
	$country_id = $country_and_city[0];
	$city_id = $country_and_city[1];
	wp_set_post_terms($post_agency_id, $city_id, $taxonomy_location_url);
	multilang_switch_language($current_lang);
	update_post_meta($post_agency_id, "phone", "555 123 4567");
	update_post_meta($post_agency_id, "website", "https://escortwp.com/");
	update_post_meta($post_agency_id, "country", $country_id);
	update_post_meta($post_agency_id, "city", $city_id);
	update_post_meta($post_agency_id, "randomly_generated_data", 'randomly_generated_data');
	$secret = md5($yourname.time().rand(1,9999));
	update_post_meta($post_agency_id, "secret", $secret);
	$upload_folder = "demo_ag".time().rand(1,9999);
	update_post_meta($post_agency_id, "upload_folder", $upload_folder);
	update_post_meta($post_agency_id, "premium", '0');
	escortwp_child_update_dynamic_user_option("agencypostid".$user_id, $post_agency_id);
	update_option($secret, $user_id);

	for ($i=1; $i <= 1; $i++) {
		$tempFile = get_template_directory().'/i/demo-agencies-images/'.rand(1,8).".png";
		$targetPath = ABSPATH.'wp-content/uploads/'.$upload_folder;
		$targetFile =  time().rand(1000, 9999).".png";
		if (!is_dir($targetPath)) { mkdir($targetPath, 0777, true); }
		if (copy($tempFile,$targetPath."/".$targetFile)) {
			$attachment = array(
				'post_mime_type' => 'image/png',
				'guid' => get_bloginfo("url")."/wp-content/uploads/".$upload_folder."/".$targetFile,
				'post_status' => 'inherit',
				'post_parent' => $post_agency_id,
				'post_title' => $targetFile,
				'post_type ' => "attachment"
			);
			wp_insert_attachment($attachment, $targetPath."/".$targetFile, $post_agency_id);
		}
	}

	return $post_agency_id;
}

function generate_random_review($profile_id) {
	global $taxonomy_profile_url, $taxonomy_agency_url;

	$profile_data = get_post($profile_id);
	$reviews_cat_id = term_exists('Reviews', "category");
	if (!$reviews_cat_id) {
		$arg = array('description' => 'Reviews');
		$current_lang = multilang_switch_language();
		wp_insert_term('Reviews', "category", $arg);
		multilang_switch_language($current_lang);
		$reviews_cat_id = term_exists( 'Reviews', "category" );
	}
	$reviews_cat_id = $reviews_cat_id['term_id'];
	$reviewtext_arr = array(
			'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam at fringilla tellus, sit amet tristique elit. Phasellus in rutrum purus, vitae cursus sem. Vivamus nec est nec quam varius eleifend. Cras tempor, risus non condimentum aliquet, lectus lectus varius felis, sed euismod odio tortor non tortor. Vivamus lorem mi, vehicula et metus ac, sollicitudin feugiat diam. Ut convallis viverra venenatis. Integer dictum ex lacus, consequat faucibus magna euismod vel. Vivamus mollis magna ac est placerat, sit amet ornare neque semper. Mauris imperdiet molestie interdum. Fusce condimentum venenatis sapien, vitae venenatis arcu interdum eget.',
			'Fusce convallis pharetra ante vel maximus. Morbi vestibulum magna tempor cursus accumsan. Nam accumsan feugiat quam sed fringilla. Integer eget lobortis orci. Vivamus finibus lorem nulla, at euismod mauris accumsan ac. Nulla facilisi. Nulla facilisi. Ut augue lorem, commodo a orci sit amet, dictum placerat lectus. Donec arcu tortor, lobortis id sollicitudin non, iaculis non libero. Integer quis blandit massa. Vestibulum ut massa pellentesque, condimentum justo in, mattis metus. Donec imperdiet pulvinar ipsum, tempus ullamcorper mi. Pellentesque eu mi risus. Integer augue lectus, auctor lacinia sollicitudin id, ultricies nec tortor.',
			'Nunc maximus lorem vitae posuere lacinia. Ut iaculis sodales ipsum, sit amet convallis est accumsan sit amet. Proin sed efficitur odio. Cras condimentum commodo erat at finibus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Sed tempus augue nisi, tempus finibus lorem ornare in. Etiam non quam et mauris lobortis ultrices. Donec condimentum sem at condimentum condimentum. Sed consequat justo arcu, vel rhoncus diam suscipit ut. Aliquam erat volutpat. Nulla sit amet porttitor ex, in gravida ex.',
			'Mauris placerat tortor rhoncus metus ullamcorper sollicitudin. Curabitur vehicula sem a rhoncus lacinia. Sed rhoncus pellentesque nisl ac dictum. Proin ultricies hendrerit nulla. Etiam ut nibh molestie, sollicitudin tellus faucibus, tincidunt urna. Aenean feugiat hendrerit fringilla. Praesent dui nulla, aliquam ac augue at, efficitur ornare lectus. Fusce eget pharetra sapien, in euismod turpis. Fusce posuere gravida sagittis.',
			'Pellentesque lobortis aliquet ullamcorper. In vel blandit risus. Pellentesque nec eleifend ex. Nullam dictum elementum egestas. Integer vel fringilla enim, sit amet euismod nisi. Vestibulum egestas, lorem nec auctor varius, risus metus aliquam tortor, eget sagittis ex est a justo. Aliquam erat volutpat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. In congue vulputate mauris, vitae lacinia sapien interdum nec. Vivamus vitae mollis ante. Fusce facilisis, ante a pretium luctus, lacus ipsum condimentum felis, eu laoreet erat urna vel nibh. Suspendisse eget erat non justo feugiat elementum in in urna. Donec auctor vel odio mollis tempor. Sed pulvinar odio id lacinia lobortis.',
			'Phasellus viverra risus at odio egestas, sit amet condimentum dui venenatis. Duis mollis ullamcorper mi eu tempus. Fusce quis enim et lectus fermentum consectetur. Ut quis molestie libero. Aenean eu ultrices leo. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Curabitur aliquam nisl mauris, at porta ipsum elementum quis. Nulla facilisi. Pellentesque mattis dolor sit amet urna sollicitudin consequat. Duis ut libero erat. Praesent lobortis laoreet laoreet. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse pharetra nibh lacus, ac semper tellus tincidunt nec. Duis scelerisque mi libero, nec pulvinar nulla bibendum in. Morbi eget tellus sed turpis maximus ultricies ut non mauris.',
			'Proin aliquam lobortis feugiat. Praesent eu luctus dui. Nunc posuere ac justo eget lacinia. Integer lacinia nisi id nunc tristique lacinia. In hac habitasse platea dictumst. Aenean tellus urna, luctus vel risus non, ornare feugiat felis. Maecenas quis libero nec diam placerat lacinia eget vitae nisl. In auctor arcu et eros fermentum bibendum. Curabitur sed varius urna, eget tristique tellus. In hac habitasse platea dictumst. Donec mollis quis lectus vel pharetra. Vestibulum semper ullamcorper augue sit amet aliquam. Sed at convallis augue, quis euismod dolor.',
			'Donec at ex nisl. Quisque sodales diam sit amet commodo molestie. Suspendisse quis tempus quam. Duis elementum mi nisi. Proin ac urna mollis, lacinia lacus non, volutpat ligula. Integer et mi at elit tristique pellentesque ut sed tortor. Ut eget orci arcu. Aliquam augue ligula, porta sed ullamcorper quis, eleifend at sem. Curabitur tincidunt tincidunt quam, ut aliquam nisi consequat sed. Fusce porta facilisis nisl nec dignissim. Aliquam massa lacus, porta laoreet dictum at, egestas commodo ligula. Ut tempor risus leo, ut sollicitudin erat consequat ac.',
			'Mauris aliquam risus eu dui imperdiet lobortis. Nam ornare a ligula eu accumsan. Nullam nunc orci, volutpat eu orci non, mattis venenatis lectus. Vivamus convallis auctor dictum. Nullam vel molestie libero, nec pulvinar tortor. Aliquam leo risus, vestibulum et rutrum a, fringilla sit amet sapien. Maecenas fermentum iaculis dolor cursus ultrices. Aliquam erat volutpat.',
			'In auctor purus sit amet ipsum egestas, eu sagittis lorem dignissim. Nam pretium, justo in fringilla accumsan, magna nisl fermentum libero, vel ullamcorper libero erat et est. Mauris nulla tortor, pulvinar id tellus varius, luctus rhoncus mi. Aliquam sed elit commodo, luctus enim quis, placerat nunc. Aenean condimentum pretium augue quis pellentesque. Phasellus sed ipsum elit. Duis gravida pretium tellus. In faucibus a leo ut volutpat.',
			'Maecenas ac felis ut ipsum bibendum viverra. Morbi at lorem dolor. In fermentum massa eu accumsan convallis. Praesent facilisis finibus risus, in sollicitudin sem ultricies in. Vestibulum ultrices vehicula faucibus. Proin eu neque quis ex fermentum ultricies. Vivamus aliquet vel tortor id ullamcorper. Curabitur ac eros purus. Vivamus blandit a enim sed porttitor. Integer at finibus felis. Duis a interdum felis. Nullam rutrum velit in tellus ullamcorper lobortis.',
			'Suspendisse potenti. Sed nec nunc at augue condimentum aliquam quis in libero. Mauris in turpis in nunc molestie lacinia vel nec lacus. Vivamus vitae sapien quis libero facilisis imperdiet. Vivamus in mi sed massa imperdiet egestas. Nulla facilisi. Integer semper fringilla quam at facilisis. Sed dapibus, mi in lobortis pellentesque, nisi quam consequat odio, a maximus quam lectus viverra magna. Mauris risus ligula, facilisis quis venenatis non, faucibus vitae libero. Phasellus a neque elementum, sodales justo at, bibendum orci. Sed eu diam elementum, sodales nibh ut, imperdiet dui.',
			'Duis efficitur metus et quam ornare, et sodales massa egestas. Cras nec venenatis erat, quis varius urna. Cras non semper magna. Aenean tincidunt aliquet urna. Aenean a dui et mauris vehicula suscipit. Mauris ut aliquam libero, sed dictum dolor. Praesent fringilla, urna a consectetur congue, sapien lacus pretium tortor, pellentesque ullamcorper orci magna a sapien. In in viverra erat. Praesent egestas auctor ex ut fringilla. Nam interdum nibh nisl, nec molestie quam accumsan ac. Quisque et elit ac purus iaculis dapibus.',
			'Vivamus sed orci nisl. Sed nec congue odio, non semper eros. Aenean vitae gravida elit. Nam in porta libero. Vestibulum iaculis leo ut est consequat, at pellentesque dolor cursus. Nullam semper diam ut ligula sagittis pretium. Praesent placerat congue sapien, ut dapibus nisi luctus eget. Proin varius non lacus a interdum.',
			'Maecenas augue enim, interdum non enim pulvinar, mattis pretium enim. Duis sed tortor ligula. Vestibulum lacinia diam ligula, quis tincidunt risus condimentum eget. Vivamus ac tincidunt nunc, a porta ex. Aenean fermentum accumsan magna non lacinia. Integer non urna sit amet ipsum molestie scelerisque. Phasellus et lacinia felis. Donec varius ante non elit gravida eleifend. Integer suscipit cursus lorem, id consectetur eros vestibulum sed. Nulla varius risus eget scelerisque iaculis.',
			'Fusce et nibh sollicitudin, pretium orci sit amet, imperdiet purus. Nunc ornare massa vel purus egestas pellentesque. Duis ornare scelerisque efficitur. In at elit sit amet arcu egestas lacinia ac at diam. Cras sit amet ipsum ac urna dignissim semper. Integer nec sem commodo eros eleifend ornare nec vel enim. Quisque dolor magna, eleifend at sodales ac, maximus sed nunc. Sed aliquet mollis orci quis tincidunt. Curabitur ut risus ac orci mollis dapibus et sit amet neque.',
			'Fusce sit amet lobortis sem. Maecenas pretium, ex nec lobortis hendrerit, neque elit gravida nisl, quis tempor odio enim eget massa. Proin cursus erat et risus faucibus sodales. Duis sed sapien quam. Duis vitae risus eget ligula dictum consequat. Praesent eget viverra augue, eu commodo lorem. Vestibulum commodo eros a ipsum efficitur mattis. Praesent feugiat consectetur consequat. Aliquam ornare massa consequat malesuada gravida. In ultricies nulla et metus porttitor fermentum. Cras vitae sem vel odio malesuada sodales sit amet vel augue. Proin ultrices, sapien eget malesuada hendrerit, ligula ligula lacinia purus, eu facilisis orci nibh ut tellus. Mauris sollicitudin blandit dolor a sodales.',
			'In pulvinar, nulla eget fringilla pellentesque, diam odio eleifend mi, eget pretium magna augue et turpis. Ut tincidunt posuere ante, a varius nunc fermentum at. Cras sed lacus augue. Curabitur vitae nulla eget ex suscipit lobortis. Praesent luctus, nulla eget laoreet fermentum, ipsum orci molestie est, eu consectetur eros magna a dui. Proin quis dui arcu. Sed eu mattis enim. Pellentesque consectetur eleifend molestie. Aenean vestibulum augue sit amet nisi eleifend, vulputate dictum massa congue. In consequat viverra sapien non dictum. Vestibulum dignissim ut nisi sodales rhoncus. Vivamus fringilla ullamcorper aliquet. Pellentesque ac ornare orci, vel tempus justo. Aliquam facilisis efficitur enim. Praesent a augue in ipsum vestibulum imperdiet. Nam condimentum tristique dignissim.'
		);
    $reviewtext = $reviewtext_arr[mt_rand(0, count($reviewtext_arr) - 1)];
	$add_review = array(
		'post_title' => __('Review for','escortwp')." ".$profile_data->post_title,
		'post_content' => $reviewtext,
		'post_status' => 'publish',
		'post_author' => generate_random_user('3'),
		'post_category' => array($reviews_cat_id),
		'post_type' => 'review',
		'ping_status' => 'closed'
	);
	$current_lang = multilang_switch_language();
	$add_review_id = wp_insert_post( $add_review );
	multilang_switch_language($current_lang);
	update_post_meta($add_review_id, "rateescort", rand(3,5));
	update_post_meta($add_review_id, "escortid", $profile_id);
	update_post_meta($add_review_id, "reviewfor", ($profile_data->post_type == $taxonomy_profile_url ? "profile" : "agency"));
	update_post_meta($add_review_id, "demo", "demo");
}

function get_country_and_city_id() {
	global $taxonomy_location_url;
	$locations = array(
			array(
				'country' => 'Germany', 
				'cities' => array('Berlin', 'Hamburg', 'Munich', 'Cologne', 'Frankfurt', 'Stuttgart', 'Düsseldorf', 'Dortmund')
			),
			array(
				'country' => 'France', 
				'cities' => array('Paris', 'Marseille', 'Rouen', 'Lyon', 'Toulouse', 'Montpellier', 'Bordeaux', 'Lille')
			),
			array(
				'country' => 'Italy', 
				'cities' => array('Rome', 'Milan', 'Turin', 'Genoa', 'Florence', 'Bari', 'Catania', 'Venice', 'Trieste')
			),
			array(
				'country' => 'Netherlands', 
				'cities' => array('Amsterdam', 'Rotterdam', 'The Hague', 'Eindhoven', 'Tilburg', 'Groningen', 'Almere')
			),
			array(
				'country' => 'Spain', 
				'cities' => array('Madrid', 'Barcelona', 'Valencia', 'Seville', 'Bilboa', 'Málaga', 'Gijón', 'Las Palmas')
			),
			array(
				'country' => 'Austria', 
				'cities' => array('Vienna', 'Graz', 'Linz', 'Salzburg', 'Innsbruck', 'Klagenfurt', 'Villach', 'Wels')
			),
			array(
				'country' => 'United States', 
				'cities' => array('New York', 'Los Angeles', 'Chicago', 'Houston', 'Philadelphia', 'Phoenix', 'San Diego')
			),
		);
    $country_data = $locations[mt_rand(0, count($locations) - 1)];
    $country_name = $country_data['country'];
    $city_name = $country_data['cities'][mt_rand(0, count($country_data['cities']) - 1)];
	$current_lang = multilang_switch_language();
	wp_insert_term($country_name, $taxonomy_location_url, array('description' => 'randomly_generated_data'));
	$country_id = get_term_by('name', $country_name, $taxonomy_location_url);
	$country_id = $country_id->term_id;

	wp_insert_term($city_name, $taxonomy_location_url, array('parent' => $country_id, 'description' => 'randomly_generated_data'));
	multilang_switch_language($current_lang);
	$city_id = get_term_by('name', $city_name, $taxonomy_location_url);
	$city_id = $city_id->term_id;

	return array($country_id, $city_id);
}

//helper functions
function pr($t) { echo "<pre style='background-color: #fff; color: #000;'>"; print_r($t); echo "</pre><br />\n"; }
function prd($t) { pr($t); die(); }


function license_check() {
	if(get_option('dolce_check_license') < time() && current_user_can('level_10')) {
		global $license_key;
		$url = "https://escortwp.com/check-license/";
		$vars = "?key=".$license_key."&domain=".urlencode(get_bloginfo('url').'/')."&email=".urlencode(get_bloginfo('admin_email'));
		$args = array(
			'timeout'     => 5,
			'redirection' => 5,
			'user-agent'  => 'WordPress ' . get_bloginfo( 'url' )
		);
		$request = wp_remote_get($url.$vars, $args);
		// if license check fails check again in 1 hour
		if(is_wp_error($request)) {
			update_option('dolce_check_license', time()+3600 ); //add 1 hour
			return false;
		}

		// if license check fails check again in 1 hour
		$result = json_decode($request['body']);
		if(!isset($result->key_check) && !isset($result->allow_domain)) {
			update_option('dolce_check_license', time()+3600 ); //add 1 hour
			return false;
		}

		// if license is ok then check again in 3 days
		if($result->key_check == '1' && $result->allow_domain == "1") {
			update_option('dolce_check_license', time()+259200 ); //add 3 days
			return false;
		}

		// if key does not exist
		if($result->key_check != '1') {
			die('<div class="err rad5" style="line-height: 2em; padding: 20px 0;">Your copy of the theme does not have a valid license key! Because of this you will not be able to install the theme.<br />If you think this is an error then please send us a message with the help of our <a href="https://escortwp.com/contact-us/">contact form</a> and we can fix this for you.</div>');
		}

		// if the domain is not allowed
		if($result->allow_domain != '1') {
			foreach($result->domains_in_use as $domain) {
				$domains .= $domain." <small class='redbutton rad3' id='".$domain."'>delete</small><div class='clear10'></div>";
			} ?>
			<script type = 'text/javascript'>
				jQuery(document).ready(function($) {
					$('.redbutton').on('click', function() {
						$('.redbutton').hide();
						var domain = $(this).attr('id');
						var get_data = "?key=<?php echo $license_key; ?>&allow_this_domain=<?php bloginfo('url') ?>&remove_domain="+domain;
						$.ajax({
							url : "<?php echo $url; ?>"+get_data,
							dataType: 'jsonp',
							success: function(msg) {
								var response = jQuery.parseJSON(msg);
								if(response == "ok") {
									$('.check_license_div').html('<div class="ok rad5">The license has been removed from the old domain.</div>You will be redirected in 3 seconds.');
									setTimeout(function(){
										location.reload();
									}, 3000);
								}
								if(response == "err") {
									$('.redbutton').show();
									alert('Something went wrong. Try again.');
								}
							},
							error: function (msg) {
								var response = jQuery.parseJSON(msg);
								if(msg == "err") {
									$('.redbutton').show();
									alert('Something went wrong. Try again.');
								}
							}
						});
					});
				});
			</script>
			<div class="clear10"></div>
			<div class="check_license_div rad5" style="line-height: 1.5em; padding: 20px; text-align: left; max-width: 500px; margin: 0 auto; background-color: #fff;">
			<div class="err rad5">Your license has been used on too many domains.</div>
			Send us a message with the help of our <a href="https://escortwp.com/contact-us/"><u>contact form</u></a> and ask us how you can upgrade your theme package so you can use this theme on more domains.<br /><br />You can also remove the activation from some of your other domains. That way you will be able to use the theme on this domain instead.<br />Click the delete button for one of the domains bellow if you would like to do that:<div class="clear10"></div>
			<?php echo $domains; ?>
			<small>You will not be able to use the theme anymore on the domain you delete</small></div>
			<?php
			die();
		}
	} // if check_license < time()
} // license_check



function theme_comments( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	?>
	<div class="clear"></div>
	<div <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ) ?>><a name="comment-<?php comment_ID() ?>"></a>
		<div class="commentbox" id="commentbox-<?php comment_ID() ?>">
			<div class="comment-info">
	            <span class="commauthor l"><?php comment_author(); ?></span>
	            <span class="commdate r"><?php comment_date() ?> | <?php comment_reply_link( array_merge( $args, array( 'reply_text' => __('Reply','escortwp'), 'add_below' => 'commentbox', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?><?php edit_comment_link('edit', ' | ', ''); ?></span>
	            <div class="clear"></div>
			</div> <!-- comment-info -->
			<div class="comment-text rad5">
		    	<?php if ($comment->comment_approved == '0') : ?>
					<div class="ok rad5"><?php _e('Your comment has been posted but needs to be approved by an admin first.','escortwp'); ?></div>
				<?php endif; ?>
				<?php comment_text() ?>
		        <div class="clear"></div>
			</div> <!-- comment-text -->
	        <div class="clear"></div>
		</div>
	    <div class="clear"></div>
	<?php
}

function verify_recaptcha() {
	$response = preg_replace("/([^a-zA-Z0-9_-])/", "", $_POST['g-recaptcha-response']);
	$url = "https://www.google.com/recaptcha/api/siteverify?secret=".get_option("recaptcha_secretkey")."&response=".$response."&remoteip=".getenv('REMOTE_ADDR');
	$result = wp_remote_get($url);
	$result = json_decode($result['body']);
	if(!$result->success) {
		return __('Are you a robot?','escortwp')."<br />";
	}
}

// Remove "Private" from the tittle
function title_format($content) {
	return '%s';
}
add_filter('private_title_format', 'title_format');

function is_video_processing_running($PID) {
	exec("ps $PID", $ProcessState);
	return(count($ProcessState) >= 2);
}

// does the user have a tour active in another city?
function get_user_current_tour($profile_id) {
	$tours_args = array(
		'post_type' => 'tour',
		'post_status' => 'publish',
		'posts_per_page' => '1',
		'order' => 'ASC',
		'orderby' => 'meta_value_num',
		'meta_key' => 'start',
		'meta_query' => array(
			array(
				'key' => 'belongstoescortid',
				'value' => $profile_id,
				'compare' => '=',
				'type' => 'NUMERIC'
			),
			array(
				'key' => 'start',
				'value' => mktime(0, 0, 0, date("m"), date("d"), date("Y")),
				'compare' => '<=',
				'type' => 'NUMERIC'
			),
			array(
				'key' => 'end',
				'value' => mktime(23, 59, 59, date("m"), date("d"), date("Y")),
				'compare' => '>=',
				'type' => 'NUMERIC'
			)
		),
		'fields' => 'ids'
	);
	$tours = new WP_Query($tours_args);
	if(count($tours->posts) > 0) {
		$post_id = $tours->posts[0];
	}
	if(isset($post_id) && $post_id > 0) {
		global $taxonomy_location_url;
		$country = get_term_by('id', get_post_meta($post_id, 'country', true), $taxonomy_location_url);
		$city = get_term_by('id', get_post_meta($post_id, 'city', true), $taxonomy_location_url);
		$response = array(
			'country' => $country,
			'city' => $city
			);
		if(get_post_meta($post_id, 'state', true)) {
			$state = get_term_by('id', get_post_meta($post_id, 'state', true), $taxonomy_location_url);
			$response['state'] = $state;
		}
		return $response;
	} else {
		return false;
	}
}


function yes_no_select_form_field($name, $val="") {
	$selected = array("1"=>"","2"=>"");
	if($selected) {
		$selected[$val] = ' selected="selected"';
	}
	$html = '<select name="'.$name.'">
				<option value="1"'.$selected["1"].'>'.__('No','escortwp').'</option>
				<option value="2"'.$selected["2"].'>'.__('Yes','escortwp').'</option>
			</select>';
	return $html;
}


function user_last_online_time() {
	if(is_user_logged_in()) {
		$current_user = wp_get_current_user();
		update_user_meta($current_user->ID, 'last_online', (int)current_time('timestamp'));
	}
}
add_action('init', 'user_last_online_time');



function insert_array_inside_another_array( array $array, $key, array $new ) {
	$keys = array_keys( $array );
	$index = array_search( $key, $keys );
	$pos = false === $index ? count( $array ) : $index + 1;
	return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
}


function esc_page_hit_counter($id) {
	global $taxonomy_profile_url, $taxonomy_agency_url;
	$current_user = wp_get_current_user();
	$current_count = (int)get_post_meta($id, 'visitor_counter', true);
	$post = get_post($id);
	if(!current_user_can('level_10') && $post->post_author != $current_user->ID) {
		$current_count++;
		update_post_meta($id, 'visitor_counter', $current_count);
	}
	switch (get_post_type()) {
		case $taxonomy_profile_url:
			$page_name = __('profile','escortwp');
			break;
		
		case $taxonomy_agency_url:
			$page_name = __('profile','escortwp');
			break;
		
		case 'ad':
			$page_name = __('ad','escortwp');
			break;
		
		default:
			$page_name = __('page','escortwp');
			break;
	}
	$current_count_html = '<span class="count rad25">'.$current_count.'</span>';
	if($current_count == "1") {
		$return_html = sprintf(__('viewed %s time','escortwp'),$current_count_html);
	} else {
		$return_html = sprintf(__('viewed %s times','escortwp'),$current_count_html);
	}
	return '<div class="visitor-counter">'.$page_name.' '.$return_html.'</div>';
}


function multilang_switch_language($current_lang="") {
	if (function_exists('icl_object_id')) {
		global $sitepress;
		if($current_lang) {
			$sitepress->switch_lang($current_lang);
		} else {	
			$current_lang = ICL_LANGUAGE_CODE;
			$sitepress->switch_lang($sitepress->get_default_language());
			return $current_lang;
		}
	}
}


function add_opacity_to_watermark($img, $opacity) {
    if (!isset($opacity)) {
        return false;
    }
    $opacity /= 100;

    //get image width and height
    $w = imagesx($img);
    $h = imagesy($img);

    //turn alpha blending off
    imagealphablending($img, false);

    //find the most opaque pixel in the image (the one with the smallest alpha value)
    $minalpha = 127;
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            $alpha = (imagecolorat($img, $x, $y) >> 24) & 0xFF;
            if ($alpha < $minalpha) {
                $minalpha = $alpha;
            }
        }
    }

    //loop through image pixels and modify alpha for each
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            //get current alpha value (represents the TANSPARENCY!)
            $colorxy = imagecolorat($img, $x, $y);
            $alpha = ($colorxy >> 24) & 0xFF;
            //calculate new alpha
            if ($minalpha !== 127) {
                $alpha = 127 + 127 * $opacity * ($alpha - 127) / (127 - $minalpha);
            } else {
                $alpha += 127 * $opacity;
            }
            //get the color index with new alpha
            $alphacolorxy = imagecolorallocatealpha($img, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
            //set pixel with the new color + opacity
            if (!imagesetpixel($img, $x, $y, $alphacolorxy)) {
                return false;
            }
        }
    }

    return true;
}



function show_report_profile_button($id) {
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('.report-profile-wrapper .report-profile-button').on('click', function(event) {
			$(this).hide();
			$(this).siblings('.report-profile-reason-wrapper').fadeIn('100', function (){
				$('html, body').animate({ scrollTop: $('.report-profile-reason-wrapper').offset().top }, 150);
			})
		});
		$('.report-profile-reason-wrapper .closebtn').on('click', function(event) {
			$(this).parent().hide();
			$(this).parent().siblings('.report-profile-button').show();
		});

		$('.report-profile-wrapper .submit-button').on('click', function(){
			if($(this).hasClass('working')) {
				return false;
			} else {
				$(this).addClass('working');
			}

			$('.report-profile-wrapper .err').remove();
			var form = $('.report-profile-reason-form');
			var profileid = form.find('.report-form-field-id').val();
			var reason = form.find('.report-form-field-reason').val();
			var recaptcha = "";
			<?php if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && !is_user_logged_in() && get_option("recaptcha6")) { ?>
			recaptcha = grecaptcha.getResponse();
			if(!recaptcha) {
				$('.report-profile-wrapper .report-profile-reason-form').prepend('<div class="err rad5"><?=addslashes(__('Are you a robot?', 'escortwp'))?></div>');
				$(this).removeClass('working');
				return false;
			}
			<?php } ?>
			if(!reason) {
				$('.report-profile-wrapper .report-profile-reason-form').prepend('<div class="err rad5"><?=addslashes(__('Please write a reason for your report', 'escortwp'))?></div>');
				$(this).removeClass('working');
				return false;
			}
			$.ajax({
				type: "POST",
				url: "<?php bloginfo('template_url'); ?>/ajax/send-profile-report.php",
				data: { profileid: profileid, reason: reason, 'g-recaptcha-response': recaptcha },
				cache: false,
				timeout: 20000, // in milliseconds
				success: function(raw_data) {
					var data = JSON.parse(raw_data);
					if(data.status == 'ok') {
						$('.report-profile-wrapper').html('<div class="ok rad5">'+data.msg+'</div>');
					} else {
						$('.report-profile-wrapper .report-profile-reason-form').prepend('<div class="err rad5">'+data.msg+'</div>');
					}
					$(this).removeClass('working');
				},
				error: function(request, status, err) {
					$(this).removeClass('working');
				}
			});
		});
	});
	</script>
	<div class="report-profile-wrapper">
		<div class="report-profile-button rad25 redbutton"><span class="icon icon-report"></span><?=__('Report Profile', 'escortwp')?></div>
		<div class="clear"></div>
		<div class="report-profile-reason-wrapper rad5">
			<?php closebtn() ?><div class="clear"></div>
			<div class="report-profile-reason-form form-styling">
		    	<input type="hidden" name="profile_id" class="report-form-field-id" value="<?=$id?>" />
			    <div class="form-label col100">
					<label for="reason"><?php _e('Write a short description for your report:','escortwp'); ?></label>
				</div>
				<div class="form-input col100">
			    	<input type="text" maxlength="300" name="reason" id="reason" class="report-form-field-reason input longinput col100" autocomplete="off" />
				</div> <!-- username -->
			    <?php if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && !is_user_logged_in() && get_option("recaptcha6")) { ?>
				<div class="form-input col100">
					<div class="g-recaptcha center" data-sitekey="<?php echo get_option('recaptcha_sitekey'); ?>"></div>
				</div> <!-- captcha --> <div class="clear10"></div>
			    <?php } ?>
				<button class="submit-button rad25 greenbutton"><span class="icon icon-report"></span> <span class="label-normal"><?=__('Send report', 'escortwp')?></span><span class="label-working"><?=__('Sending', 'escortwp')?></span></button>
			</div><!-- report-profile-reason-form -->
		</div><!-- report-profile-options -->
	</div><!-- report-profile-wrapper -->
	<?php
}


function show_online_label_html($user_id) {
	$last_online = get_user_meta($user_id, 'last_online', true);
	if($last_online >= (current_time('timestamp') - 60*5)) {
		$label = '<div class="online-status">
				<span class="online-label">
					<div class="notification-circle">
						<span class="notification-circle-outside">
							<span class="notification-circle-inside"></span>
						</span>
					</div>
					<span class="text-label">'.__('Online','escortwp').'</span>
				</span>
			</div>';
	} else {
		$label = "";
	}
	return $label;
}


remove_filter('pre_term_description', 'wp_filter_kses');
remove_filter('term_description', 'wp_kses_data');

add_filter( 'wp_insert_post_data', function( $data, $postarr ) {
if ( ! empty( $data['post_content'] ) ) {
    $data['post_content'] = wp_encode_emoji( $data['post_content'] );
}
return $data;
}, 99, 2 );


function add_theme_meta_tags() {
    echo "\n\t".'<!-- This site is built with the premium WordPress theme from EscortWP.com -->' . "\t";
    echo "\n\t".'<meta name="theme-name" content="EscortWP" />' . "\n\t";
    echo "\n\t".'<meta name="theme-creator" content="https://escortwp.com/" />' . "\n";
}
add_action('wp_head', 'add_theme_meta_tags', 1);
