<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

global $taxonomy_profile_name_plural, $taxonomy_location_url;
?>
<div class="sidebar-left l">
	<div class="countries">
    	<h4><?php printf(esc_html__('%s country list','escortwp'),ucwords($taxonomy_profile_name_plural)); ?><span class="dots">:</span><span class="icon icon-down-dir"></span></h4>
        <ul class="country-list">
			<?php
			// create arguments list to retrieve locations list
			$args = array(
					'show_option_all' => '',
					'show_count' => '0',
					'hide_empty' => '1',
					'title_li' => '',
					'show_option_none' => '',
					'pad_counts' => '0',
					'taxonomy' => $taxonomy_location_url,
				);
			$categories_count_data = get_categories(array_merge($args, array(
				'parent' => 0,
				'number' => '2',
				'fields' => 'ids'
			)));
			$categories_count_data = array_values($categories_count_data);
			if(count($categories_count_data) == "1") {
				$args['parent'] = $categories_count_data[0];
			}
			wp_list_categories($args);
			?>
        </ul>
		<div class="clear"></div>
	</div> <!-- COUNTRIES -->
	<div class="clear"></div>

	<?php if ( is_active_sidebar('widget-sidebar-left') || current_user_can('level_10')) : ?>
    <div class="widgetbox-wrapper">
    	<?php if ( !dynamic_sidebar('Sidebar Left') && current_user_can('level_10')) : ?>
		<?php _e('Go to your','escortwp'); ?> <a href="<?php echo admin_url('widgets.php'); ?>"><?php _e('widgets page','escortwp'); ?></a> <?php _e('to add content here','escortwp'); ?>.
		<?php endif; ?>
	</div> <!-- SIDEBAR BOX -->
	<?php endif; ?>

	<?php dynamic_sidebar('Left Ads'); ?>
</div> <!-- SIDEBAR LEFT -->