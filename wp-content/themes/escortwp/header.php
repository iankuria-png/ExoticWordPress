<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

upgrade_theme();
time_check_expired();

global $settings_theme_genders, $taxonomy_profile_name, $taxonomy_profile_name_plural, $taxonomy_agency_name_plural;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, target-densityDpi=device-dpi, user-scalable=no">
	<title><?php if (is_front_page() ) { bloginfo('name'); } else { wp_title('',true); } ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
license_check();
install_theme_wizard();
generate_demo_data();
if(defined('escortwp_demo_theme') && function_exists('escortwp_theme_options')) escortwp_theme_options();
?>
<header>
	<div class="header-top-bar">
		<div class="logo l">
	        <?php
	        if (get_option("sitelogo")) {
		        $h1 = '<img class="l" src="'.get_option('sitelogo').'" alt="'.get_bloginfo('name').'" />';
			} else {
		        $h1 = get_bloginfo('name');
			}
			?>
	    	<h1 class="l"><?php echo '<a href="'.get_bloginfo("url").'/" title="'.get_bloginfo('name').'">'.$h1.'</a>'; ?></h1>
	    </div> <!-- logo -->

		<nav class="header-nav l">
			<?php
			if ( has_nav_menu("header-menu") ) {
				$menu_args = array(
					'theme_location'  => 'header-menu',
					'container'       => 'ul',
					'container_class' => 'header-menu l',
					'container_id'    => '',
					'menu_class'      => 'header-menu vcenter l',
					'menu_id'         => '',
					'echo'            => true,
					'fallback_cb'     => false,
					'before'          => '',
					'after'           => '',
					'link_before'     => '',
					'link_after'      => '',
					'items_wrap'      => '<ul class="%2$s">%3$s</ul>',
					'depth'           => 0,
					'walker'          => ''
				);
				wp_nav_menu($menu_args);
			} else { ?>
	           	<ul class="header-menu vcenter l">
	               	<li><a href="<?php bloginfo('url'); ?>/" title="<?php bloginfo('name'); ?>"><?php _e('Home','escortwp'); ?></a></li>
	                <li class="<?php if (get_option('all_profiles_page_id') == get_the_ID()) { echo ' current_page_item'; } ?>">
	                	<a href="<?php echo get_permalink(get_option('all_profiles_page_id')); ?>"><?=function_exists('icl_object_id') ? get_the_title(icl_object_id(get_option('all_profiles_page_id'),'page',TRUE,ICL_LANGUAGE_CODE)) : get_the_title(get_option('all_profiles_page_id'))?></a>
	                    <ul>
							<?php
							$gender_page_links = array(
									'1' => '<li><a href="'.get_permalink(get_option('all_female_profiles_page_id')).'">'.__('Female','escortwp').'</a></li>',
									'2' => '<li><a href="'.get_permalink(get_option('all_male_profiles_page_id')).'">'.__('Male','escortwp').'</a></li>',
									'3' => '<li><a href="'.get_permalink(get_option('all_couple_profiles_page_id')).'">'.__('Couple','escortwp').'</a></li>',
									'4' => '<li><a href="'.get_permalink(get_option('all_gay_profiles_page_id')).'">'.__('Gay','escortwp').'</a></li>',
									'5' => '<li><a href="'.get_permalink(get_option('all_trans_profiles_page_id')).'">'.__('Transsexual','escortwp').'</a></li>'
								);

							foreach ($settings_theme_genders as $gender) {
								echo $gender_page_links[$gender];
							}
							?>
	                        <?php if(get_option("hide2") != "1") { ?>
	                        <li><a href="<?php echo get_permalink(get_option('all_independent_profiles_page_id')); ?>"><?php _e('Independent','escortwp'); ?></a></li>
	                        <li><a href="<?php echo get_permalink(get_option('all_verified_profiles_page_id')); ?>"><?php _e('Verified','escortwp'); ?></a></li>
	                        <?php } ?>
	                        <li><a href="<?php echo get_permalink(get_option('all_online_profiles_page_id')); ?>"><?php _e('Online','escortwp'); ?></a></li>
	                    </ul>
	                    <div class="clear"></div>
	                </li>
					<?php if(get_option("hide3") != "1") { ?>
					<li<?php if (get_option('list_agencies_page_id') == get_the_ID()) { echo ' class="current_page_item"'; } ?>><a href="<?php echo get_permalink(get_option('list_agencies_page_id')); ?>"><?=function_exists('icl_object_id') ? get_the_title(icl_object_id(get_option('list_agencies_page_id'),'page',TRUE,ICL_LANGUAGE_CODE)) : get_the_title(get_option('list_agencies_page_id'))?></a></li>
					<?php } ?>
	                <?php if(get_option("hide8") != "1") { ?>
					<li<?php if (get_option('city_tours_page_id') == get_the_ID()) { echo ' class="current_page_item"'; } ?>><a href="<?php echo get_permalink(get_option('city_tours_page_id')); ?>"><?php printf(esc_html__('%s on Tour','escortwp'),ucwords($taxonomy_profile_name_plural)); ?></a></li>
					<?php } ?>
					<?php if(get_option("hide1") != "1") { ?>
					<li<?php if (get_option('nav_reviews_page_id') == get_the_ID()) { echo ' class="current_page_item"'; } ?>><a href="<?php echo get_permalink(get_option('nav_reviews_page_id')); ?>"><?php printf(esc_html__('%s Reviews','escortwp'),ucwords($taxonomy_profile_name)); ?></a></li>
					<?php } ?>
					<?php if(get_option("hide6") != "1") { ?>
					<li<?php if (get_option('see_all_ads_page_id') == get_the_ID()) { echo ' class="current_page_item"'; } ?>><a href="<?php echo get_permalink(get_option('see_all_ads_page_id')); ?>"><?php _e('Classified Ads','escortwp'); ?></a></li>
					<?php } ?>
					<?php if(get_option("hide10") != "1") { ?>
					<li<?php if (get_option('blog_page_id') == get_the_ID()) { echo ' class="current_page_item"'; } ?>><a href="<?php echo get_permalink(get_option('blog_page_id')); ?>"><?php _e('Our Blog','escortwp'); ?></a></li>
					<?php } ?>
					<li<?php if (get_option('contact_page_id') == get_the_ID()) { echo ' class="current_page_item"'; } ?>><a href="<?php echo get_permalink(get_option('contact_page_id')); ?>"><?php _e('Contact Us','escortwp'); ?></a></li>
				</ul>
			<?php }	?>
		</nav> <!-- header-nav -->
		<div class="hamburger-menu rad25"><span class="icon icon-menu"></span><span class="label"><?=__('Menu', 'escortwp')?></span></div>

    	<div class="subnav-menu-wrapper r">
	    	<ul class="subnav-menu vcenter r">
				<?php if (!is_user_logged_in() && !get_option("hide31")) { ?>
					<li class="subnav-menu-btn register-btn"><a href="<?php echo get_permalink(get_option('main_reg_page_id')); ?>"><span class="icon icon-user"></span><?php _e('Register','escortwp'); ?></a></li>
					<li class="subnav-menu-btn login-btn"><a href="<?php echo wp_login_url(get_current_url()); ?>"><span class="icon icon-key-outline"></span><?php _e('Login','escortwp'); ?></a></li>
				<?php } ?>
				<?php if (is_user_logged_in()) { ?>
					<li class="subnav-menu-btn logout-btn"><a href="<?php echo wp_logout_url(home_url()."/"); ?>"><span class="icon icon-logout"></span><?php _e('Log Out','escortwp'); ?></a></li>
				<?php } ?>
					<?php if(is_active_sidebar('header-language-switcher')) { dynamic_sidebar('header-language-switcher'); } ?>
					<li class="subnav-menu-icon"><a href="<?php echo get_permalink(get_option('search_page_id')); ?>" title="<?php _e('Search','escortwp'); ?>"><span class="icon icon-search"></span></a></li>
					<li class="subnav-menu-icon"><a href="<?php echo get_permalink(get_option('contact_page_id')); ?>" title="<?php _e('Contact Us','escortwp'); ?>"><span class="icon icon-mail"></span></a></li>
	        </ul>
        </div> <!-- subnav-menu-wrapper -->
    	<div class="clear"></div>
	</div> <!-- header-top-bar -->

    <?php check_if_user_has_validated_his_email(); //long name right? ?>

	<?php
	if (defined('showslider') && showslider == 1) {
	    include (get_template_directory().'/header-slider.php');
	}
	?>
</header> <!-- header -->

<div class="all all-body">