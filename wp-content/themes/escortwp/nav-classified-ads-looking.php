<?php
/*
Template Name: Classified ads - Looking
*/

if(get_option("hide6") == "1" && !current_user_can('level_10')) { wp_redirect(site_url(), "301"); }

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox">
        	<h3 class="l"><?php _e('Classified ads','escortwp'); ?> - <?php _e('looking','escortwp'); ?></h3>
            <a href="<?php echo get_permalink(get_option('see_all_ads_page_id')); ?>" class="pinkbutton rad25 r"><?php _e('All','escortwp'); ?></a>
            <span class="r">&nbsp;</span>
            <a href="<?php echo get_permalink(get_option('see_looking_ads_page_id')); ?>" class="pinkbutton rad25 r"><?php _e('Looking','escortwp'); ?></a>
            <span class="r">&nbsp;</span>
            <a href="<?php echo get_permalink(get_option('see_offering_ads_page_id')); ?>" class="pinkbutton rad25 r"><?php _e('Offering','escortwp'); ?></a>
            <div class="clear10"></div>
			<?php
			$posts_per_page = "40";
			$args = array(
				'post_type' => 'ad',
				'posts_per_page' => $posts_per_page,
				'paged' => $paged,
				'meta_query' => array( array('key' => 'type', 'value' => 'looking', 'compare' => '=') )
			);
			query_posts($args);
			if ( have_posts() ) : ?>
			<table class="listagencies rad3">
				<tr class="trhead rad3">
					<th class="rad3"><?php _e('Title','escortwp'); ?></th>
			        <th class="rad3"><?php _e('Type','escortwp'); ?></th>
			        <th class="rad3"><?php _e('Date added','escortwp'); ?></th>
				</tr>
			<?php
			while ( have_posts() ) : the_post();
				if (get_post_meta(get_the_ID(),'type', true) == "offering") {
					$classifiedadtype = __('offering','escortwp');
				}
				if (get_post_meta(get_the_ID(),'type', true) == "looking") {
					$classifiedadtype = __('looking','escortwp');
				}
			?>
				<tr class="agencytr">
			    	<td><a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></td>
			        <td><?php echo $classifiedadtype; ?></td>
			        <td><?php the_time("d F Y"); ?></td>
			    </tr>
			<?php endwhile; ?>
			</table>
			<?php
				$total = ceil($wp_query->found_posts / $posts_per_page);
				dolce_pagination($total, $paged);
			else:
				_e('No classified ads yet','escortwp');
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