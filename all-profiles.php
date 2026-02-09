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
				get_option('all_profiles_page_id') => sprintf(esc_html__('All %s', 'escortwp'), $taxonomy_profile_name_plural),
				get_option('all_female_profiles_page_id') => sprintf(esc_html__('All female %s', 'escortwp'), $taxonomy_profile_name_plural),
				get_option('all_male_profiles_page_id') => sprintf(esc_html__('All male %s', 'escortwp'), $taxonomy_profile_name_plural),
				get_option('all_couple_profiles_page_id') => sprintf(esc_html__('All couple %s', 'escortwp'), $taxonomy_profile_name_plural),
				get_option('all_gay_profiles_page_id') => sprintf(esc_html__('All gay %s', 'escortwp'), $taxonomy_profile_name_plural),
				get_option('all_trans_profiles_page_id') => sprintf(esc_html__('All transsexual %s', 'escortwp'), $taxonomy_profile_name_plural),
				get_option('all_independent_profiles_page_id') => sprintf(esc_html__('All independent %s', 'escortwp'), $taxonomy_profile_name_plural),
				get_option('all_premium_profiles_page_id') => sprintf(esc_html__('All premium %s', 'escortwp'), $taxonomy_profile_name_plural),
				get_option('all_verified_profiles_page_id') => sprintf(esc_html__('All verified %s', 'escortwp'), $taxonomy_profile_name_plural),
				get_option('all_new_profiles_page_id') => sprintf(esc_html__('All newly created %s', 'escortwp'), $taxonomy_profile_name_plural),
				get_option('all_online_profiles_page_id') => sprintf(esc_html__('All online %s', 'escortwp'), $taxonomy_profile_name_plural),
			);
			?>

			<h3 class="pagetitle l"><?php echo esc_html($all_profiles_titles[get_the_ID()]); ?></h3>

			<div class="r">
				<ul class="pagetitle-menu">
					<?php
					$gender_page_links = array(
						'1' => '<li><a class="pinkbutton rad25" href="' . get_permalink(get_option('all_female_profiles_page_id')) . '">' . esc_html__('Female', 'escortwp') . '</a></li>',
						'2' => '<li><a class="pinkbutton rad25" href="' . get_permalink(get_option('all_male_profiles_page_id')) . '">' . esc_html__('Male', 'escortwp') . '</a></li>',
						'3' => '<li><a class="pinkbutton rad25" href="' . get_permalink(get_option('all_couple_profiles_page_id')) . '">' . esc_html__('Couple', 'escortwp') . '</a></li>',
						'4' => '<li><a class="pinkbutton rad25" href="' . get_permalink(get_option('all_gay_profiles_page_id')) . '">' . esc_html__('Gay', 'escortwp') . '</a></li>',
						'5' => '<li><a class="pinkbutton rad25" href="' . get_permalink(get_option('all_trans_profiles_page_id')) . '">' . esc_html__('Transsexual', 'escortwp') . '</a></li>',
					);
					foreach ((array) $settings_theme_genders as $gender) {
						if (isset($gender_page_links[$gender])) {
							echo $gender_page_links[$gender];
						}
					}
					?>
					<li><a class="pinkbutton rad25"
							href="<?php echo esc_url(get_permalink(get_option('all_independent_profiles_page_id'))); ?>"><?php esc_html_e('Independent', 'escortwp'); ?></a>
					</li>
					<li><a class="pinkbutton rad25"
							href="<?php echo esc_url(get_permalink(get_option('all_premium_profiles_page_id'))); ?>"><?php esc_html_e('Premium', 'escortwp'); ?></a>
					</li>
					<li><a class="pinkbutton rad25"
							href="<?php echo esc_url(get_permalink(get_option('all_verified_profiles_page_id'))); ?>"><?php esc_html_e('Verified', 'escortwp'); ?></a>
					</li>
					<li><a class="pinkbutton rad25"
							href="<?php echo esc_url(get_permalink(get_option('all_new_profiles_page_id'))); ?>"><?php esc_html_e('New', 'escortwp'); ?></a>
					</li>
					<li>
						<a class="pinkbutton online-label rad25"
							href="<?php echo esc_url(get_permalink(get_option('all_online_profiles_page_id'))); ?>">
							<span class="icon icon-circle"></span>
							<span class="text-label"><?php esc_html_e('Online', 'escortwp'); ?></span>
						</a>
					</li>
				</ul>
			</div>

			<?php if (have_posts()): ?>
				<div class="clear20"></div>
				<?php while (have_posts()):
					the_post(); ?>
					<?php the_content(); ?>
					<?php edit_post_link(esc_html__('Add some text here', 'escortwp'), '<div class="clear"></div>', '<div class="clear10"></div>'); ?>
				<?php endwhile; ?>
			<?php endif; ?>
			<div class="clear"></div>

			<?php
			$posts_per_page = 40;

			$gender_pages = array(
				get_option('all_female_profiles_page_id') => 1,
				get_option('all_male_profiles_page_id') => 2,
				get_option('all_couple_profiles_page_id') => 3,
				get_option('all_gay_profiles_page_id') => 4,
				get_option('all_trans_profiles_page_id') => 5,
			);

			// Count total premium & normal
			$premium_all_args = array(
				'post_type' => $taxonomy_profile_url,
				'posts_per_page' => 1,
				'paged' => 1,
				'orderby' => 'meta_value_num',
				'meta_key' => 'premium_since',
				'meta_query' => array(
					array('key' => 'premium', 'value' => '1', 'compare' => '=', 'type' => 'NUMERIC')
				),
			);
			if (isset($gender_pages[get_the_ID()])) {
				$premium_all_args['meta_query'][] = array(
					'key' => 'gender',
					'value' => $gender_pages[get_the_ID()],
					'compare' => '=',
					'type' => 'NUMERIC'
				);
			}
			if (get_the_ID() == get_option('all_independent_profiles_page_id')) {
				$premium_all_args['meta_query'][] = array('key' => 'independent', 'value' => 'yes', 'compare' => '=');
			}
			if (get_the_ID() == get_option('all_new_profiles_page_id')) {
				$premium_all_args['date_query'] = array(
					array(
						'after' => date('Y-m-d H:i:s', strtotime('-' . get_option('newlabelperiod') . ' days')),
						'inclusive' => true,
					),
				);
			}
			if (get_the_ID() == get_option('all_online_profiles_page_id')) {
				$online_users = (new WP_User_Query(array(
					'meta_key' => 'last_online',
					'meta_value' => current_time('timestamp') - 60 * 5,
					'meta_compare' => '>=',
					'fields' => 'ids',
				)))->get_results();
				$premium_all_args['author__in'] = $online_users;
			}
			$premium_all = new WP_Query($premium_all_args);
			$premium_found_posts = $premium_all->found_posts;

			$normal_all_args = array(
				'post_type' => $taxonomy_profile_url,
				'posts_per_page' => 1,
				'paged' => 1,
				'meta_query' => array(
					array('key' => 'premium', 'value' => '0', 'compare' => '=', 'type' => 'NUMERIC')
				),
			);
			if (isset($gender_pages[get_the_ID()])) {
				$normal_all_args['meta_query'][] = array(
					'key' => 'gender',
					'value' => $gender_pages[get_the_ID()],
					'compare' => '=',
					'type' => 'NUMERIC'
				);
			}
			if (get_the_ID() == get_option('all_independent_profiles_page_id')) {
				$normal_all_args['meta_query'][] = array('key' => 'independent', 'value' => 'yes', 'compare' => '=');
			}
			if (get_the_ID() == get_option('all_new_profiles_page_id')) {
				$normal_all_args['date_query'] = array(
					array(
						'after' => date('Y-m-d H:i:s', strtotime('-' . get_option('newlabelperiod') . ' days')),
						'inclusive' => true,
					),
				);
			}
			if (get_the_ID() == get_option('all_online_profiles_page_id')) {
				$normal_all_args['author__in'] = $online_users;
			}
			if (get_the_ID() !== get_option('all_premium_profiles_page_id')) {
				$normal_all = new WP_Query($normal_all_args);
				$normal_found_posts = $normal_all->found_posts;
			} else {
				$normal_found_posts = 0;
			}

			// Pagination
			$paged = isset($wp_query->query['paged']) && $wp_query->query['paged'] > 0
				? $wp_query->query['paged']
				: $wp_query->query['page'];

			// Fetch premium for this page
			$premium_args = $premium_all_args;
			$premium_args['posts_per_page'] = $posts_per_page;
			$premium_args['paged'] = $paged;
			$premium = new WP_Query($premium_args);

			// Calculate offset for normal
			if ($paged < 2) {
				$normal_offset = 0;
			} else {
				$normal_offset = ($paged - 1) * $posts_per_page - $premium_found_posts;
				if ($normal_offset < 0) {
					$normal_offset = 0;
				}
			}
			$normal_args = $normal_all_args;
			$normal_args['offset'] = $normal_offset;
			$normal_args['posts_per_page'] = $posts_per_page - count($premium->posts);
			$normal = new WP_Query($normal_args);

			// Merge and loop
			$all = $premium;
			if (count($premium->posts) < $posts_per_page && get_the_ID() !== get_option('all_premium_profiles_page_id')) {
				$merged = array_merge($premium->posts, $normal->posts);
				$all->posts = $merged;
				$all->post_count = count($merged);
			}

			if ($all->have_posts()):
				echo '<div class="escort-grid__container">';
				while ($all->have_posts()):
					$all->the_post();
					include(get_theme_file_path('/loop-show-profile.php'));
				endwhile;
				echo '</div>'; // Close escort-grid__container
			
				$total = ceil(($premium_found_posts + $normal_found_posts) / $posts_per_page);
				dolce_pagination($total, $paged);
			else:
				printf(esc_html__('No %s here yet', 'escortwp'), esc_html($taxonomy_profile_name_plural));
			endif;
			wp_reset_postdata();
			?>

			<div class="clear"></div>

		</div> <!-- bodybox -->
		<div class="clear"></div>
	</div> <!-- body -->
</div> <!-- contentwrapper -->

<?php get_sidebar('left'); ?>
<?php get_sidebar('right'); ?>
<div class="clear"></div>

<?php get_footer(); ?>