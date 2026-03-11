<?php
/*
Template Name: All profiles
*/

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox">
			<?php
			$all_profiles_titles = array(
					get_option('all_profiles_page_id') => sprintf(esc_html__('All %s','escortwp'),$taxonomy_profile_name_plural),
					get_option('all_female_profiles_page_id') => sprintf(esc_html__('All female %s','escortwp'),$taxonomy_profile_name_plural),
					get_option('all_male_profiles_page_id') => sprintf(esc_html__('All male %s','escortwp'),$taxonomy_profile_name_plural),
					get_option('all_couple_profiles_page_id') => sprintf(esc_html__('All couple %s','escortwp'),$taxonomy_profile_name_plural),
					get_option('all_gay_profiles_page_id') => sprintf(esc_html__('All gay %s','escortwp'),$taxonomy_profile_name_plural),
					get_option('all_trans_profiles_page_id') => sprintf(esc_html__('All transsexual %s','escortwp'),$taxonomy_profile_name_plural),
					get_option('all_independent_profiles_page_id') => sprintf(esc_html__('All independent %s','escortwp'),$taxonomy_profile_name_plural),
					get_option('all_premium_profiles_page_id') => sprintf(esc_html__('All premium %s','escortwp'),$taxonomy_profile_name_plural),
					get_option('all_verified_profiles_page_id') => sprintf(esc_html__('All verified %s','escortwp'),$taxonomy_profile_name_plural),
					get_option('all_new_profiles_page_id') => sprintf(esc_html__('All newly created %s','escortwp'),$taxonomy_profile_name_plural),
					get_option('all_online_profiles_page_id') => sprintf(esc_html__('All online %s','escortwp'),$taxonomy_profile_name_plural),
				);
			?>
        	<h3 class="pagetitle l"><?php echo $all_profiles_titles[get_the_ID()]; ?></h3>
			<div class="r">
				<ul class="pagetitle-menu">
				<?php
				$gender_page_links = array(
						'1' => '<li><a class="pinkbutton rad25" href="'.get_permalink(get_option('all_female_profiles_page_id')).'">'.__('Female','escortwp').'</a></li>',
						'2' => '<li><a class="pinkbutton rad25" href="'.get_permalink(get_option('all_male_profiles_page_id')).'">'.__('Male','escortwp').'</a></li>',
						'3' => '<li><a class="pinkbutton rad25" href="'.get_permalink(get_option('all_couple_profiles_page_id')).'">'.__('Couple','escortwp').'</a></li>',
						'4' => '<li><a class="pinkbutton rad25" href="'.get_permalink(get_option('all_gay_profiles_page_id')).'">'.__('Gay','escortwp').'</a></li>',
						'5' => '<li><a class="pinkbutton rad25" href="'.get_permalink(get_option('all_trans_profiles_page_id')).'">'.__('Transsexual','escortwp').'</a></li>',
					);
				foreach ($settings_theme_genders as $gender) {
					echo $gender_page_links[$gender];
				}
				?>
				<li><a class="pinkbutton rad25" href="<?php echo get_permalink(get_option('all_independent_profiles_page_id')); ?>"><?php _e('Independent','escortwp'); ?></a></li>
				<li><a class="pinkbutton rad25" href="<?php echo get_permalink(get_option('all_premium_profiles_page_id')); ?>"><?php _e('Premium','escortwp'); ?></a></li>
				<li><a class="pinkbutton rad25" href="<?php echo get_permalink(get_option('all_verified_profiles_page_id')); ?>"><?php _e('Verified','escortwp'); ?></a></li>
				<li><a class="pinkbutton rad25" href="<?php echo get_permalink(get_option('all_new_profiles_page_id')); ?>"><?php echo __('New','escortwp'); ?></a></li>
				<li><a class="pinkbutton online-label rad25" href="<?php echo get_permalink(get_option('all_online_profiles_page_id')); ?>"><?=__('Online','escortwp')?></span></a></li>
				</ul>
			</div>
			<?php if (have_posts()) : ?>
				<div class="clear20"></div>
				<?php while (have_posts()) : the_post(); ?>
		                <?php the_content(); ?><?php edit_post_link(__('Add some text here','escortwp'), '<div class="clear"></div>', '<div class="clear10"></div>'); ?>
				<?php endwhile; ?>
			<?php endif; ?>
			<div class="clear"></div>
			<?php
			$posts_per_page = "40";

			$gender_pages = array(
					get_option('all_female_profiles_page_id') => "1",
					get_option('all_male_profiles_page_id') => "2",
					get_option('all_couple_profiles_page_id') => "3",
					get_option('all_gay_profiles_page_id') => "4",
					get_option('all_trans_profiles_page_id') => "5"
				);

			$premium_all_args = array(
				'post_type' => $taxonomy_profile_url, 'posts_per_page' => "1", 'paged' => "1",
				'orderby' => 'meta_value_num', 'meta_key' => 'premium_since',
				'meta_query' => array( array( 'key' => 'premium', 'value' => '1', 'compare' => '=', 'type' => 'NUMERIC' ) )
			);
			if(array_key_exists(get_the_ID(), $gender_pages)) {
				$premium_all_args['meta_query'][] = array( 'key' => 'gender', 'value' => $gender_pages[get_the_ID()], 'compare' => '=', 'type' => 'NUMERIC' );
			}
			if(get_the_ID() == get_option('all_independent_profiles_page_id')) {
				$premium_all_args['meta_query'][] = array( 'key' => 'independent', 'value' => 'yes', 'compare' => '=' );
			}
			if(get_the_ID() == get_option('all_verified_profiles_page_id')) {
				$premium_all_args['meta_query'][] = array( 'key' => 'verified', 'value' => '1', 'compare' => '=' );
			}
			if(get_the_ID() == get_option('all_new_profiles_page_id')) {
				$premium_all_args['date_query'] = array(
						array(
							'after'     => date("Y-m-d H:i:s", strtotime("-".get_option('newlabelperiod')." days")),
							'inclusive' => true,
						)
				);
			}
			if(get_the_ID() == get_option('all_online_profiles_page_id')) {
				$online_users_args = array(
				    'meta_key' => 'last_online', //any custom field name
				    'meta_value' => current_time('timestamp') - 60*5, //the value to compare against
				    'meta_compare' => '>=',
				    'fields' => 'ids',
				);
				$online_users_query = new WP_User_Query($online_users_args);
			    $online_users_arr = $online_users_query->get_results();
				$premium_all_args['author__in'] = $online_users_arr;
			}
			$premium_all = new WP_Query( $premium_all_args ); $premium_found_posts = $premium_all->found_posts;

			$normal_all_args = array(
				'post_type' => $taxonomy_profile_url, 'posts_per_page' => "1", 'paged' => "1",
				'meta_query' => array( array( 'key' => 'premium', 'value' => '0', 'compare' => '=', 'type' => 'NUMERIC' ) )
			);
			if(array_key_exists(get_the_ID(), $gender_pages)) {
				$normal_all_args['meta_query'][] = array( 'key' => 'gender', 'value' => $gender_pages[get_the_ID()], 'compare' => '=', 'type' => 'NUMERIC' );
			}
			if(get_the_ID() == get_option('all_verified_profiles_page_id')) {
				$normal_all_args['meta_query'][] = array( 'key' => 'verified', 'value' => '1', 'compare' => '=' );
			}
			if(get_the_ID() == get_option('all_new_profiles_page_id')) {
				$normal_all_args['date_query'] = array(
						array(
							'after'     => date("Y-m-d H:i:s", strtotime("-".get_option('newlabelperiod')." days")),
							'inclusive' => true,
						)
				);
			}
			if(get_the_ID() == get_option('all_online_profiles_page_id')) {
				$normal_all_args['author__in'] = $online_users_arr;
			}
			if(get_the_ID() != get_option('all_premium_profiles_page_id')) {
				$normal_all = new WP_Query( $normal_all_args ); $normal_found_posts = $normal_all->found_posts;
			} else {
				$normal_found_posts = "0";
			}

			$paged = isset($wp_query->query['paged']) && $wp_query->query['paged'] > 0 ? $wp_query->query['paged'] : $wp_query->query['page'];
			$premium_args = array(
				'post_type' => $taxonomy_profile_url,
				'posts_per_page' => $posts_per_page,
				'paged' => $paged,
				'orderby' => 'meta_value_num', 'meta_key' => 'premium_since',
				'meta_query' => array( array( 'key' => 'premium', 'value' => '1', 'compare' => '=', 'type' => 'NUMERIC' ) )
			);
			if(array_key_exists(get_the_ID(), $gender_pages)) {
				$premium_args['meta_query'][] = array( 'key' => 'gender', 'value' => $gender_pages[get_the_ID()], 'compare' => '=', 'type' => 'NUMERIC' );
			}
			if(get_the_ID() == get_option('all_independent_profiles_page_id')) {
				$premium_args['meta_query'][] = array( 'key' => 'independent', 'value' => 'yes', 'compare' => '=' );
			}
			if(get_the_ID() == get_option('all_verified_profiles_page_id')) {
				$premium_args['meta_query'][] = array( 'key' => 'verified', 'value' => '1', 'compare' => '=' );
			}
			if(get_the_ID() == get_option('all_new_profiles_page_id')) {
				$premium_args['date_query'] = array(
						array(
							'after'     => date("Y-m-d H:i:s", strtotime("-".get_option('newlabelperiod')." days")),
							'inclusive' => true,
						)
				);
			}
			if(get_the_ID() == get_option('all_online_profiles_page_id')) {
				$premium_args['author__in'] = $online_users_arr;
			}
			$premium = new WP_Query( $premium_args );


			if($paged < "2") {
				$normal_offset = "0";
			} else {
				$normal_offset = ($paged-1)*$posts_per_page - $premium_found_posts;
				if($normal_offset < 0) { $normal_offset = "0"; }
			}
			$normal_args = array(
				'offset' => $normal_offset,
				'post_type' => $taxonomy_profile_url,
				'posts_per_page' => $posts_per_page - count($premium->posts),
				'orderby' => 'date',
				'order' => 'DESC',
				'meta_query' => array( array( 'key' => 'premium', 'value' => '0', 'compare' => '=', 'type' => 'NUMERIC' ) )
			);
			if(array_key_exists(get_the_ID(), $gender_pages)) {
				$normal_args['meta_query'][] = array( 'key' => 'gender', 'value' => $gender_pages[get_the_ID()], 'compare' => '=', 'type' => 'NUMERIC' );
			}
			if(get_the_ID() == get_option('all_independent_profiles_page_id')) {
				$normal_args['meta_query'][] = array( 'key' => 'independent', 'value' => 'yes', 'compare' => '=' );
			}
			if(get_the_ID() == get_option('all_verified_profiles_page_id')) {
				$normal_args['meta_query'][] = array( 'key' => 'verified', 'value' => '1', 'compare' => '=' );
			}
			if(get_the_ID() == get_option('all_new_profiles_page_id')) {
				$normal_args['date_query'] = array(
						array(
							'after'     => date("Y-m-d H:i:s", strtotime("-".get_option('newlabelperiod')." days")),
							'inclusive' => true,
						)
				);
			}
			if(get_the_ID() == get_option('all_online_profiles_page_id')) {
				$normal_args['author__in'] = $online_users_arr;
			}


			$all = $premium;
			if(count($premium->posts) < $posts_per_page && get_the_ID() != get_option('all_premium_profiles_page_id')) {
				//only query the normal posts if we need to
				$normal = new WP_Query( $normal_args );

				$q = array_merge($premium->posts, $normal->posts);
				$all->post_count = count($q);
				$all->posts = $q;
			}
			$i = 1;
			if ( $all->have_posts() ) :
				while ( $all->have_posts() ) : $all->the_post();
					include (get_template_directory() . '/loop-show-profile.php');
				endwhile;

				$total = ceil(($premium_found_posts + $normal_found_posts) / $posts_per_page);
				dolce_pagination($total, $paged);
			else:
				printf(esc_html__('No %s here yet','escortwp'),$taxonomy_profile_name_plural);
			endif;
			wp_reset_query();
			//SHOW NORMAL POSTS end
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