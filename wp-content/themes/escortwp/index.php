<?php get_header(); ?>

	<div class="contentwrapper">
		<div class="body">
			<?php
			// PREMIUM PROFILES start
			if (get_option("frontpageshowpremium") == 1) {
				$args = array(
					'post_type' => $taxonomy_profile_url,
					'orderby' => 'meta_value_num', 'meta_key' => 'premium_since',
					'meta_query' => array( array('key' => 'premium', 'value' => '1', 'compare' => '=', 'type' => 'NUMERIC') ),
					'posts_per_page' => get_option("frontpageshowpremiumcols") * 5
				);
				$premium_profiles = new WP_Query( $args );
				$i = "1";
				if ($premium_profiles->have_posts()) :
					?>
			    	<div class="bodybox bodybox-homepage">
			        	<h3 class="l"><?php printf(esc_html__('Premium %s','escortwp'),ucwords($taxonomy_profile_name_plural)); ?></h3>
			        	<a class="see-all-top pinkbutton rad25 r" href="<?php echo get_permalink(get_option( "all_premium_profiles_page_id")); ?>"><?php printf(esc_html__('All premium %s','escortwp'),$taxonomy_profile_name_plural); ?></a>
			        	<div class="clear"></div>
						<?php
							while ( $premium_profiles->have_posts() ) : $premium_profiles->the_post();
								include (get_template_directory() . '/loop-show-profile.php');
							endwhile;
						?>
			            <div class="clear"></div>
			        	<a class="see-all-bottom pinkbutton rad25 text-center hide" href="<?php echo get_permalink(get_option( "all_premium_profiles_page_id")); ?>"><?php printf(esc_html__('All premium %s','escortwp'),$taxonomy_profile_name_plural); ?></a>
			        	<div class="see-more-button pinkbutton rad25 text-center hide"><?php _e('See more','escortwp'); ?></div>
			        </div> <!-- BODY BOX -->
					<?php
				endif;
				wp_reset_postdata();
			} // if $frontpageshowpremium = 1
			// PREMIUM PROFILES end
			?>

			<?php
			// ONLINE PROFILES start
			if (get_option("frontpageshowonline") == 1) {
				$user_args = array(
				    'meta_key' => 'last_online2',
				    'meta_value' => current_time('timestamp') - 60*5,
				    'meta_compare' => '>=',
				    'fields' => 'ids',
				);
				$user_query = new WP_User_Query($user_args);
			    $users_arr = $user_query->get_results();
			    if(count($users_arr) > 0) {
					$args = array(
						'author__in' => $users_arr,
						'post_type' => $taxonomy_profile_url,
						'posts_per_page' => get_option("frontpageshowonlinecols") * 5,
						'max_num_pages' => '1'
					);
					$online_profiles = new WP_Query( $args );
					$i = "1";
					if ($online_profiles->have_posts()) :
						?>
				    	<div class="bodybox bodybox-homepage">
				        	<h3 class="l"><?php printf(esc_html__('Online now','escortwp'),ucwords($taxonomy_profile_name_plural)); ?></h3>
				        	<a class="see-all-top pinkbutton rad25 r" href="<?php echo get_permalink(get_option("all_online_profiles_page_id")); ?>"><?php printf(esc_html__('All online %s','escortwp'),$taxonomy_profile_name_plural); ?></a>
				        	<div class="clear"></div>
							<?php
								while ( $online_profiles->have_posts() ) : $online_profiles->the_post();
									include (get_template_directory() . '/loop-show-profile.php');
								endwhile;
							?>
				            <div class="clear"></div>
				            <a class="see-all-bottom pinkbutton rad25 text-center hide" href="<?php echo get_permalink(get_option("all_new_profiles_page_id")); ?>"><?php printf(esc_html__('All newly added %s','escortwp'),$taxonomy_profile_name_plural); ?></a>
				            <div class="see-more-button pinkbutton rad25 text-center hide"><?php _e('See more','escortwp'); ?></div>
				        </div> <!-- BODY BOX -->
						<?php
					endif;
				} // if(count($users_arr) < 1) {
				wp_reset_postdata();
			} // if $frontpageshowonline = 1
			// ONLINE PROFILES end
			?>

			<?php if (get_option("frontpageshownormal") == 1) { ?>
			<!-- NORMAL PROFILES start -->
	    	<div class="bodybox bodybox-homepage">
	        	<h3 class="l"><?php printf(esc_html__('Newly Added %s','escortwp'),ucwords($taxonomy_profile_name_plural)); ?></h3>
	        	<a class="see-all-top pinkbutton rad25 r" href="<?php echo get_permalink(get_option("all_new_profiles_page_id")); ?>"><?php printf(esc_html__('All newly added %s','escortwp'),$taxonomy_profile_name_plural); ?></a>
	        	<div class="clear"></div>
				<?php
				$args = array(
					'post_type' => $taxonomy_profile_url,
					'meta_query' => array( array('key' => 'premium', 'value' => '0', 'compare' => '=', 'type' => 'NUMERIC') ),
					'posts_per_page' => get_option("frontpageshownormalcols") * 5
				);
				$normal_profiles = new WP_Query( $args );
				$i = "1";
				if ($normal_profiles->have_posts()) :
					while ( $normal_profiles->have_posts() ) : $normal_profiles->the_post();
						include (get_template_directory() . '/loop-show-profile.php');
					endwhile;
				else:
					printf(esc_html__('No %s here yet','escortwp'),$taxonomy_profile_name_plural);
				endif;
				wp_reset_postdata();
				?>
	            <div class="clear"></div>
	            <a class="see-all-bottom pinkbutton rad25 text-center hide" href="<?php echo get_permalink(get_option("all_new_profiles_page_id")); ?>"><?php printf(esc_html__('All newly added %s','escortwp'),$taxonomy_profile_name_plural); ?></a>
	            <div class="see-more-button pinkbutton rad25 text-center hide"><?php _e('See more','escortwp'); ?></div>
	        </div> <!-- BODY BOX -->
			<!-- NORMAL PROFILES end -->
			<?php } // if $frontpageshownormal = 1 ?>


			<?php if (get_option("frontpageshowrev") == 1) { ?>
			<?php
			$args = array(
				'post_type' => 'review',
				'posts_per_page' => get_option("frontpageshowrevitems"),
				'orderby' => 'date'
			);
			$reviews_query = new WP_Query( $args );
			if ( $reviews_query->have_posts() ) : 
			?>
			<!-- REVIEWS start -->
	    	<div class="bodybox bodybox-homepage">
	        	<h3 class="l"><?php printf(esc_html__('Latest %s Reviews','escortwp'),ucwords($taxonomy_profile_name)); ?>:</h3>
	            <a class="see-all-top pinkbutton rad25 r" href="<?php echo get_permalink(get_option('nav_reviews_page_id')); ?>"><?php _e('See all reviews','escortwp'); ?></a>
	            <div class="clear"></div>
			    <?php while ( $reviews_query->have_posts() ) : $reviews_query->the_post(); ?>
				<div class="onereviewtext onereviewtext-homepage">
					<div class="author l"><span><?php echo substr(get_the_author_meta('display_name'), 0, 2) ?>...</span> <?php _e('wrote','escortwp'); ?>:</div>
					<div class="rating r">
						<div class="starrating l"><div class="starrating_stars star<?php echo get_post_meta(get_the_ID(), 'rateescort', true); ?>"></div></div>
					</div>
				    <div class="clear5"></div>
					<div class="reviewtext">
					    <?php
				        echo substr(strip_tags(get_the_content()), 0, get_option("frontpageshowrevchars"));
						if (strlen(get_the_content()) > get_option("frontpageshowrevchars")) {
							echo '...';
						}
						echo ' <a href="'.get_permalink().'">'.__('see the review','escortwp').'</a>';
						?>
				    </div> <!-- REVIEW TEXT -->
				</div> <!-- ONE REVIEW -->
				<?php endwhile; ?>
	            <div class="clear10"></div>
	            <a class="see-all-bottom pinkbutton rad25 center hide" href="<?php echo get_permalink(get_option('nav_reviews_page_id')); ?>"><?php _e('See all reviews','escortwp'); ?></a>
	        </div> <!-- BODY BOX -->
			<!-- REVIEWS end -->
			<?php
			else:
				// _e('No reviews yet','escortwp');
			endif;
			wp_reset_postdata();
			?>
			<?php } // if $frontpageshowrev = 1 ?>
	    </div> <!-- BODY -->
    </div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>

<?php get_footer(); ?>