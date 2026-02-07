<?php
if (!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set('display_errors', error_reporting);
if (error_reporting == '1') { error_reporting(E_ALL); }
if (isdolcetheme !== 1) { die(); }

upgrade_theme();
time_check_expired();

global $settings_theme_genders, $taxonomy_profile_name, $taxonomy_profile_name_plural, $taxonomy_agency_name_plural;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-M9V76MS');</script>
    <!-- End Google Tag Manager -->

    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, target-densityDpi=device-dpi, user-scalable=no">
    <title><?php if (is_front_page()) { bloginfo('name'); } else { wp_title('', true); } ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M9V76MS"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<?php
license_check();
install_theme_wizard();
generate_demo_data();
if (defined('escortwp_demo_theme') && function_exists('escortwp_theme_options')) escortwp_theme_options();
?>

<header class="modern-header">
    <div class="header-top-bar modern-header-top">
        <div class="header_top_left modern-header-left">
            <!-- Mobile Controls (preserved for JS) -->
            <div class="modern-mobile-controls">
                <div class="mobile-menu-icon">
                    <img src="<?php echo get_stylesheet_directory_uri() . '/img/mobile-menu-icon.png'; ?>" />
                </div>
                <div class="mobile-login-icon">
                    <img src="<?php echo get_stylesheet_directory_uri() . '/img/login-icon.png'; ?>" />
                </div>
            </div>

            <!-- Mobile Menu Drawer (preserved for JS) -->
            <div class="mobile-menu-div-content">
                <a href="#" class="close-menu">Close</a>
                <h4 class="h-heading">Main Navigation</h4>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'header-menu',
                    'container' => 'ul',
                    'menu_class' => 'slider-menu',
                    'items_wrap' => '<ul class="%2$s">%3$s</ul>',
                    'fallback_cb' => false
                ));
                ?>
            </div>

            <!-- Mobile Login Overlay (preserved for JS) -->
            <div class="mobile-login-div-content">
                <div class="subnav-menu-wrapper r">
                    <ul class="subnav-menu vcenter r">
                        <?php if (!is_user_logged_in() && !get_option("hide31")) { ?>
                            <li class="subnav-menu-btn register-btn"><a href="<?php echo get_permalink(get_option('main_reg_page_id')); ?>"><span class="icon icon-user"></span><?php _e('Register','escortwp'); ?></a></li>
                            <li class="subnav-menu-btn login-btn"><a href="<?php echo wp_login_url(get_current_url()); ?>"><span class="icon icon-key-outline"></span><?php _e('Login','escortwp'); ?></a></li>
                        <?php } ?>

                        <?php if (is_user_logged_in()) {
                            $user_id = get_current_user_id();
                            global $wpdb;
                            $results = $wpdb->get_results("SELECT conv_id FROM {$wpdb->prefix}messages WHERE r_author_id=$user_id and status=0");
                            $count_unread = count($results);
                        ?>
                            <li class="subnav-menu-btn logout-btn"><a href="<?php echo wp_logout_url(home_url() . "/"); ?>"><span class="icon icon-logout"></span><?php _e('Log Out','escortwp'); ?></a></li>
                            <li class="subnav-menu-icon" style="position:relative;"><a href="<?php echo get_site_url(); ?>/messages/"><span class="icon icon-mail"></span></a><div class="notification-div"><?php echo $count_unread; ?></div></li>
                        <?php } else { ?>
                            <li class="subnav-menu-icon"><a href="#" class="show-popup"><span class="icon icon-mail"></span></a></li>
                        <?php } ?>

                        <?php if (get_option("hidelangdrpdwn") == "1") {
                            $sitelang = preg_replace("/([^a-zA-Z0-9])/", "", $_COOKIE['sitelang'] ?? '');
                            if (!$sitelang) $sitelang = get_option("dolce_sitelang");
                            echo '<li><div class="headerlangselect"><select name="headerlang" class="headerlang vcenter rad25">' . get_langs_list($sitelang) . '</select></div></li>';
                        } ?>

                        <li class="subnav-menu-icon"><a href="<?php echo get_permalink(get_option('search_page_id')); ?>" title="<?php _e('Search','escortwp'); ?>"><span class="icon icon-search2"></span></a></li>
                        <li class="subnav-menu-icon"><a href="<?php echo get_permalink(get_option('contact_page_id')); ?>" title="<?php _e('Contact Us','escortwp'); ?>"><span class="icon icon-mail"></span></a></li>
                    </ul>
                </div>
            </div>

            <!-- Logo (SEO: H1 + alt text preserved) -->
            <div class="modern-logo-container">
                <?php
                $h1 = get_option("sitelogo") ? '<img class="l" src="'.get_option('sitelogo').'" alt="'.get_bloginfo('name').'" />' : get_bloginfo('name');
                ?>
                <h1 class="l"><?php echo '<a href="'.get_bloginfo("url").'/" title="'.get_bloginfo('name').'">'.$h1.'</a>'; ?></h1>
            </div>
        </div>

        <div class="heager_top_right">
            <?php dynamic_sidebar('sidebar-id-header-ads'); ?>
        </div>

        <!-- Desktop Navigation -->
        <nav class="header-nav l modern-nav-wrapper">
            <?php
            if (has_nav_menu("header-menu")) {
                wp_nav_menu(array(
                    'theme_location' => 'header-menu',
                    'container' => 'ul',
                    'menu_class' => 'header-menu vcenter l',
                    'items_wrap' => '<ul class="%2$s">%3$s</ul>',
                    'fallback_cb' => false
                ));
            }
            ?>
        </nav>

        <!-- Desktop Actions (Register/Login/Search/Contact) -->
        <div class="subnav-menu-wrapper r modern-header-actions">
            <ul class="subnav-menu vcenter r">
                <?php if (!is_user_logged_in() && !get_option("hide31")) { ?>
                    <li class="subnav-menu-btn register-btn"><a href="<?php echo get_permalink(get_option('main_reg_page_id')); ?>"><span class="icon icon-user"></span><?php _e('Register','escortwp'); ?></a></li>
                    <li class="subnav-menu-btn login-btn"><a href="<?php echo wp_login_url(get_current_url()); ?>"><span class="icon icon-key-outline"></span><?php _e('Login','escortwp'); ?></a></li>
                <?php } ?>
                <?php if (is_user_logged_in()) { ?>
                    <li class="subnav-menu-btn logout-btn"><a href="<?php echo wp_logout_url(home_url()."/"); ?>"><span class="icon icon-logout"></span><?php _e('Log Out','escortwp'); ?></a></li>
                <?php } ?>
                <?php if (is_active_sidebar('header-language-switcher')) dynamic_sidebar('header-language-switcher'); ?>
                <li class="subnav-menu-icon"><a href="<?php echo get_permalink(get_option('search_page_id')); ?>" title="<?php _e('Search','escortwp'); ?>"><span class="icon icon-search"></span></a></li>
                <li class="subnav-menu-icon"><a href="<?php echo get_permalink(get_option('contact_page_id')); ?>" title="<?php _e('Contact Us','escortwp'); ?>"><span class="icon icon-mail"></span></a></li>
            </ul>
        </div>

        <div class="clear"></div>
    </div>

    <?php check_if_user_has_validated_his_email(); ?>

    <div class="online-escort-counter-div">
        <a class="close-online-escort" href="#">X</a>
        <a href="<?php echo site_url(); ?>/online-escorts/">
            See who is online - <i style="font-size:13px;color:#00e600" class="fa fa-circle" aria-hidden="true"></i>
            <b class="count"></b>
        </a>
    </div>

    <div class="mobile-menu-div">
        <div class="mobile-menu-action">
            <a href="#" class="open-country">
                <span class="icon icon-location"></span>
                <span class="mobile-menu-label"><?php _e('Escort Locations','escortwp'); ?></span>
            </a>
        </div>
        <div class="mobile-menu-action open-search-div">
            <a href="#" class="open-search">
                <span class="icon icon-search"></span>
                <span class="mobile-menu-label"><?php _e('Search','escortwp'); ?></span>
            </a>
        </div>
    </div>

    <?php
    if (defined('showslider') && showslider == 1) {
        include (get_template_directory().'/header-slider.php');
    }
    ?>
</header>

<div class="all all-body">
