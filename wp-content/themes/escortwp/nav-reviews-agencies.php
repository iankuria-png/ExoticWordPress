<?php
/*
Template Name: Reviews - Agencies (nav link)
*/
global $taxonomy_location_url, $taxonomy_agency_name, $taxonomy_agency_url;
get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox">
        	<h3 class="l"><?php printf(esc_html__('%s Reviews','escortwp'),ucwords($taxonomy_agency_name)); ?></h3>
        	<a class="pinkbutton rad25 r" href="<?php echo get_permalink(get_option('nav_reviews_page_id')); ?>"><?php printf(esc_html__('%s reviews','escortwp'),ucwords($taxonomy_profile_name)); ?></a>
        	<div class="clear"></div>
			<?php
			$posts_per_page = "20";
			$args = array(
				'post_type' => 'review',
				'posts_per_page' => $posts_per_page,
				'meta_query' => array( array('key' => 'reviewfor', 'value' => 'agency', 'compare' => '=') ),
				'paged' => $paged
			);

			query_posts($args);
			if ( have_posts() ) : ?>
			<div class="clear20"></div>
			<?php
			while ( have_posts() ) : the_post();
				$country = get_term( get_post_meta(get_the_ID(), 'countrymeeting', true), $taxonomy_location_url);
				$city = get_term( get_post_meta(get_the_ID(), 'citymeeting', true), $taxonomy_location_url);
				if (get_post_meta(get_the_ID(), 'reviewfor', true) == 'agency') {
					$escort_or_agency = get_post(get_post_meta(get_the_ID(), 'agencyid', true));
					$rating_number = get_post_meta(get_the_ID(), 'rateagency', true);
				} elseif (get_post_meta(get_the_ID(), 'reviewfor', true) == 'profile') {
					$escort_or_agency = get_post(get_post_meta(get_the_ID(), 'escortid', true));
					$rating_number = get_post_meta(get_the_ID(), 'rateescort', true);
				}
			?>
			<a href="<?php echo get_permalink($escort_or_agency->ID); ?>"><img src="<?php echo get_first_image($escort_or_agency->ID); ?>" alt="" class="l rad5 image-next-to-review" /></a>
			<div class="starrating l"><div class="starrating_stars star<?php echo $rating_number; ?>"></div></div>&nbsp;&nbsp;<i><?php _e('by','escortwp'); ?></i> <b><?php echo substr(get_the_author_meta('display_name'), 0, 2) ?>...</b> <i><?php _e('for','escortwp'); ?></i> <b><a href="<?php echo get_permalink($escort_or_agency->ID); ?>"><?php echo $escort_or_agency->post_title; ?></a></b> <i><?php _e('on','escortwp'); ?></i> <b><?php echo the_time("d F Y"); ?></b>
			<?php the_content(); ?>
			<?php edit_post_link(__('Edit review','escortwp')); ?>
			<div class="clear40"></div>
			<?php
			endwhile;
			?>
			<?php
				$total = ceil($wp_query->found_posts / $posts_per_page);
				dolce_pagination($total, $paged);
			else:
				_e('No reviews yet','escortwp');
			endif;
			wp_reset_query();
			?>
            <div class="clear"></div>
        </div> <!-- BODY BOX -->

        <div class="clear"></div>
    </div> <!-- BODY -->
    </div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>

<?php get_footer(); ?>