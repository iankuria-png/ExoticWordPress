<?php
if (!defined('error_reporting')) {
    define('error_reporting', '0');
}
ini_set('display_errors', error_reporting);
if (error_reporting == '1') {
    error_reporting(E_ALL);
}
if (isdolcetheme !== 1) {
    die();
}

global $settings_theme_genders, $taxonomy_profile_name, $taxonomy_profile_name_plural, $taxonomy_agency_name_plural;

$escort_profile_context = array();
$show_escort_profile_cta = false;
if (is_user_logged_in() && function_exists('escortwp_child_get_logged_in_escort_profile_context')) {
    $escort_profile_context = escortwp_child_get_logged_in_escort_profile_context(get_current_user_id());
    $show_escort_profile_cta = !empty($escort_profile_context['is_escort'])
        && !empty($escort_profile_context['has_profile'])
        && !empty($escort_profile_context['profile_url']);
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <!-- Google Tag Manager -->
    <script>(function (w, d, s, l, i) {
            w[l] = w[l] || []; w[l].push({
                'gtm.start':
                    new Date().getTime(), event: 'gtm.js'
            }); var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-M9V76MS');</script>
    <!-- End Google Tag Manager -->

    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title><?php if (is_front_page()) {
        bloginfo('name');
    } else {
        wp_title('', true);
    } ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php
    if (function_exists('wp_body_open')) {
        wp_body_open();
    }
    ?>

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M9V76MS" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <a class="skip-link screen-reader-text" href="#main-content">
        <?php esc_html_e('Skip to main content', 'escortwp'); ?>
    </a>

    <?php
    license_check();
    install_theme_wizard();
    generate_demo_data();
    if (defined('escortwp_demo_theme') && function_exists('escortwp_theme_options'))
        escortwp_theme_options();
    ?>

    <header class="modern-header">
        <div class="header-top-bar modern-header-top">
            <div class="header_top_left modern-header-left">

                <!-- Mobile Account Panel -->
                <div
                    id="mobile-account-dialog"
                    class="mobile-login-div-content"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="mobile-account-title"
                    aria-hidden="true"
                >
                    <div class="mobile-account-panel" tabindex="-1">
                        <div class="mobile-account-panel__header">
                            <p class="mobile-account-panel__eyebrow"><?php esc_html_e('Account', 'escortwp'); ?></p>
                            <button type="button" class="mobile-account-panel__close" aria-label="<?php esc_attr_e('Close account menu', 'escortwp'); ?>">
                                <span class="icon icon-cancel" aria-hidden="true"></span>
                            </button>
                            <?php if (!is_user_logged_in()) { ?>
                                <h4 id="mobile-account-title"><?php esc_html_e('Access your profile faster', 'escortwp'); ?></h4>
                                <p><?php esc_html_e('Register or sign in to manage listings, messages, and preferences.', 'escortwp'); ?></p>
                            <?php } else { ?>
                                <h4 id="mobile-account-title"><?php esc_html_e('Welcome back', 'escortwp'); ?></h4>
                                <p><?php esc_html_e('Open messages or continue managing your account.', 'escortwp'); ?></p>
                            <?php } ?>
                        </div>

                        <div class="mobile-account-panel__actions">
                            <?php if (!is_user_logged_in() && !get_option("hide31")) { ?>
                                <a class="mobile-account-action mobile-account-action--primary"
                                    href="<?php echo get_permalink(get_option('main_reg_page_id')); ?>">
                                    <span class="icon icon-user" aria-hidden="true"></span>
                                    <span>
                                        <strong><?php esc_html_e('Register', 'escortwp'); ?></strong>
                                        <small><?php esc_html_e('Create your account and start posting', 'escortwp'); ?></small>
                                    </span>
                                </a>
                                <a class="mobile-account-action mobile-account-action--secondary"
                                    href="<?php echo wp_login_url(get_current_url()); ?>">
                                    <span class="icon icon-key-outline" aria-hidden="true"></span>
                                    <span>
                                        <strong><?php esc_html_e('Log in', 'escortwp'); ?></strong>
                                        <small><?php esc_html_e('Resume your account instantly', 'escortwp'); ?></small>
                                    </span>
                                </a>
                            <?php } elseif (!is_user_logged_in()) { ?>
                                <a class="mobile-account-action mobile-account-action--secondary"
                                    href="<?php echo wp_login_url(get_current_url()); ?>">
                                    <span class="icon icon-key-outline" aria-hidden="true"></span>
                                    <span>
                                        <strong><?php esc_html_e('Log in', 'escortwp'); ?></strong>
                                        <small><?php esc_html_e('Access your account dashboard', 'escortwp'); ?></small>
                                    </span>
                                </a>
                            <?php } ?>

                            <?php if (is_user_logged_in()) {
                                $user_id = get_current_user_id();
                                global $wpdb;
                                $results = $wpdb->get_results("SELECT conv_id FROM {$wpdb->prefix}messages WHERE r_author_id=$user_id and status=0");
                                $count_unread = count($results);
                                ?>
                                <?php if ($show_escort_profile_cta): ?>
                                <a class="mobile-account-action mobile-account-action--secondary mobile-account-action--profile"
                                    href="<?php echo esc_url($escort_profile_context['profile_url']); ?>">
                                    <span class="icon icon-user" aria-hidden="true"></span>
                                    <span>
                                        <strong><?php esc_html_e('My profile', 'escortwp'); ?></strong>
                                        <small><?php echo esc_html($escort_profile_context['status_label']); ?></small>
                                    </span>
                                </a>
                                <?php endif; ?>
                                <a class="mobile-account-action mobile-account-action--secondary mobile-account-action--messages"
                                    href="<?php echo esc_url(get_site_url() . '/messages/'); ?>">
                                    <span class="icon icon-mail" aria-hidden="true"></span>
                                    <span>
                                        <strong><?php esc_html_e('Messages', 'escortwp'); ?></strong>
                                        <small><?php esc_html_e('Check unread conversations', 'escortwp'); ?></small>
                                    </span>
                                    <span class="mobile-account-action__badge"><?php echo esc_html($count_unread); ?></span>
                                </a>
                                <a class="mobile-account-action mobile-account-action--ghost"
                                    href="<?php echo wp_logout_url(home_url() . "/"); ?>">
                                    <span class="icon icon-logout" aria-hidden="true"></span>
                                    <span>
                                        <strong><?php esc_html_e('Log out', 'escortwp'); ?></strong>
                                        <small><?php esc_html_e('Sign out from this device', 'escortwp'); ?></small>
                                    </span>
                                </a>
                            <?php } ?>
                        </div>

                        <div class="mobile-account-panel__links">
                            <a class="mobile-account-link" href="<?php echo get_permalink(get_option('search_page_id')); ?>">
                                <?php esc_html_e('Search escorts', 'escortwp'); ?>
                            </a>
                            <a class="mobile-account-link" href="<?php echo get_permalink(get_option('contact_page_id')); ?>">
                                <?php esc_html_e('Contact support', 'escortwp'); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Logo (SEO: H1 + alt text preserved) -->
                <div class="modern-logo-container">
                    <?php
                    $h1 = get_option("sitelogo") ? '<img class="l" src="' . get_option('sitelogo') . '" alt="' . get_bloginfo('name') . '" />' : get_bloginfo('name');
                    $logo_tag = is_front_page() ? 'h1' : 'p';
                    ?>
                    <<?php echo esc_attr($logo_tag); ?> class="l">
                        <?php echo '<a href="' . get_bloginfo("url") . '/" title="' . get_bloginfo('name') . '">' . $h1 . '</a>'; ?>
                    </<?php echo esc_attr($logo_tag); ?>>
                </div>

                <div class="modern-mobile-controls modern-mobile-controls--right">
                    <a href="#" class="open-country mobile-location-icon"
                        aria-label="<?php esc_attr_e('Escort Locations', 'escortwp'); ?>">
                        <span class="icon icon-location"></span>
                    </a>
                    <button type="button" class="mobile-login-icon"
                        aria-haspopup="true"
                        aria-controls="mobile-account-dialog"
                        aria-expanded="false"
                        aria-label="<?php esc_attr_e('Open account menu', 'escortwp'); ?>">
                        <span class="fa fa-user" aria-hidden="true"></span>
                    </button>
                    <button type="button" class="mobile-menu-icon"
                        aria-controls="mobile-nav-drawer"
                        aria-expanded="false"
                        aria-label="<?php esc_attr_e('Open navigation menu', 'escortwp'); ?>">
                        <span class="fa fa-bars" aria-hidden="true"></span>
                    </button>
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
                        'container' => false,
                        'menu_class' => 'header-menu vcenter l',
                        'fallback_cb' => false
                    ));
                }
                ?>
            </nav>

            <!-- Desktop Actions (Register/Login) -->
            <div class="subnav-menu-wrapper r modern-header-actions">
                <ul class="subnav-menu vcenter r">
                    <?php if (!is_user_logged_in() && !get_option("hide31")) { ?>
                        <li class="subnav-menu-btn register-btn"><a
                                href="<?php echo get_permalink(get_option('main_reg_page_id')); ?>"><span
                                    class="icon icon-user"></span><?php _e('Register', 'escortwp'); ?></a></li>
                        <li class="subnav-menu-btn login-btn"><a href="<?php echo wp_login_url(get_current_url()); ?>"><span
                                    class="icon icon-key-outline"></span><?php _e('Login', 'escortwp'); ?></a></li>
                    <?php } ?>
                    <?php if (is_user_logged_in()) { ?>
                        <li class="subnav-menu-btn logout-btn"><a href="<?php echo wp_logout_url(home_url() . "/"); ?>"><span
                                    class="icon icon-logout"></span><?php _e('Log Out', 'escortwp'); ?></a></li>
                    <?php } ?>
                    <?php if (is_active_sidebar('header-language-switcher'))
                        dynamic_sidebar('header-language-switcher'); ?>
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
                    <span class="mobile-menu-label"><?php _e('Escort Locations', 'escortwp'); ?></span>
                </a>
            </div>
            <div class="mobile-menu-action open-search-div">
                <a href="#" class="open-search">
                    <span class="icon icon-search"></span>
                    <span class="mobile-menu-label"><?php _e('Search', 'escortwp'); ?></span>
                </a>
            </div>
        </div>

    </header>

    <?php if ($show_escort_profile_cta): ?>
        <section class="escort-profile-status-section" role="region" aria-label="<?php esc_attr_e('Profile status', 'escortwp'); ?>">
            <div class="escort-profile-status-strip" role="status" aria-live="polite">
                <div class="escort-profile-status-strip__inner">
                    <div class="escort-profile-status-strip__meta">
                        <span class="escort-profile-status-strip__label"><?php esc_html_e('My profile', 'escortwp'); ?></span>
                        <strong class="escort-profile-status-strip__state"><?php echo esc_html($escort_profile_context['status_label']); ?></strong>
                    </div>
                    <a class="escort-profile-status-strip__cta"
                        href="<?php echo esc_url($escort_profile_context['profile_url']); ?>">
                        <?php esc_html_e('View my profile', 'escortwp'); ?>
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php
    if (defined('showslider') && showslider == 1) {
        include(get_template_directory() . '/header-slider.php');
    }
    ?>

    <!-- Mobile Menu Drawer (positioned outside header for full-height display) -->
    <div class="mobile-menu-div-content" id="mobile-nav-drawer" aria-hidden="true">
        <div class="mobile-drawer-header">
            <div class="mobile-drawer-heading">
                <p class="mobile-drawer-eyebrow"><?php esc_html_e('Explore', 'escortwp'); ?></p>
                <h4 class="mobile-drawer-title"><?php esc_html_e('Quick Navigation', 'escortwp'); ?></h4>
            </div>
            <button type="button" class="close-menu" aria-label="<?php esc_attr_e('Close navigation menu', 'escortwp'); ?>">
                <span class="icon icon-cancel" aria-hidden="true"></span>
            </button>
        </div>
        <div class="mobile-menu-quick-actions">
            <a class="mobile-menu-quick-action mobile-menu-search"
                href="<?php echo get_permalink(get_option('search_page_id')); ?>">
                <span class="icon icon-search"></span>
                <?php _e('Search', 'escortwp'); ?>
            </a>
            <a href="#" class="mobile-menu-quick-action open-country">
                <span class="icon icon-location"></span>
                <?php _e('Escort Locations', 'escortwp'); ?>
            </a>
        </div>
        <h4 class="h-heading"><?php esc_html_e('Main Navigation', 'escortwp'); ?></h4>
        <div class="mobile-drawer-nav">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'header-menu',
                'container' => false,
                'menu_class' => 'slider-menu',
                'fallback_cb' => 'wp_page_menu'
            ));
            ?>
        </div>
    </div>

    <main id="main-content" class="site-main-content" tabindex="-1">
        <div class="all all-body">
