<?php
/*
Template Name: Blog
*/

$blog_section = 'yes';
get_header();

$paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$current_cat = 0;
if (get_query_var('cat')) {
	$current_cat = (int) get_query_var('cat');
} elseif (is_category()) {
	$queried_object = get_queried_object();
	$current_cat = $queried_object && !is_wp_error($queried_object) ? (int) $queried_object->term_id : 0;
}

$base_query_args = array(
	'post_type' => 'post',
	'post_status' => 'publish',
	'ignore_sticky_posts' => true,
);
if ($current_cat > 0) {
	$base_query_args['cat'] = $current_cat;
}

$featured_query = new WP_Query(array_merge($base_query_args, array(
	'posts_per_page' => 1,
	'paged' => 1,
)));

$featured_post_id = 0;
if ($featured_query->have_posts()) {
	$featured_query->the_post();
	$featured_post_id = (int) get_the_ID();
	wp_reset_postdata();
}

$recent_query = new WP_Query(array_merge($base_query_args, array(
	'posts_per_page' => 9,
	'paged' => $paged,
	'post__not_in' => $featured_post_id ? array($featured_post_id) : array(),
)));

$categories = get_categories(array(
	'hide_empty' => true,
	'orderby' => 'count',
	'order' => 'DESC',
	'number' => 12,
));

$hero_title = get_the_title();
if (empty($hero_title)) {
	$hero_title = esc_html__('Blog', 'escortwp');
}
$blog_page_id = (int) get_queried_object_id();
$blog_page_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/');

$active_category_name = '';
if ($current_cat > 0) {
	$cat_obj = get_category($current_cat);
	if ($cat_obj && !is_wp_error($cat_obj)) {
		$active_category_name = $cat_obj->name;
	}
}
?>

<div class="contentwrapper blog-shell">
	<div class="body blog-index">
		<div class="bodybox blog-hero">
			<header class="blog-hero__header">
				<h1 class="blog-hero__title"><?php echo esc_html($hero_title); ?></h1>
				<p class="blog-hero__intro"><?php esc_html_e('Practical updates, guides, and local insights in a cleaner reading layout.', 'escortwp'); ?></p>
			</header>
			<div class="listing-summary" data-filter-summary data-filter-context="blog" data-filter-label="<?php echo esc_attr($active_category_name ? $active_category_name : __('All categories', 'escortwp')); ?>" data-total-results="<?php echo esc_attr((int) $recent_query->found_posts + ($featured_post_id ? 1 : 0)); ?>">
				<div class="listing-summary__metric">
					<span class="listing-summary__label"><?php esc_html_e('Posts', 'escortwp'); ?></span>
					<strong class="listing-summary__value"><?php echo esc_html(number_format_i18n((int) $recent_query->found_posts + ($featured_post_id ? 1 : 0))); ?></strong>
				</div>
				<div class="listing-summary__metric">
					<span class="listing-summary__label"><?php esc_html_e('Category', 'escortwp'); ?></span>
					<strong class="listing-summary__value"><?php echo esc_html($active_category_name ? $active_category_name : __('All categories', 'escortwp')); ?></strong>
				</div>
			</div>
		</div>

		<div class="bodybox blog-layout">
			<aside class="blog-taxonomy" aria-label="<?php esc_attr_e('Blog categories', 'escortwp'); ?>">
				<details class="blog-taxonomy__panel" <?php echo wp_is_mobile() ? '' : 'open'; ?>>
					<summary><?php esc_html_e('Categories', 'escortwp'); ?></summary>
					<ul>
						<li>
							<a class="blog-taxonomy__link<?php echo $current_cat < 1 ? ' is-active' : ''; ?>" href="<?php echo esc_url($blog_page_url); ?>">
								<?php esc_html_e('All categories', 'escortwp'); ?>
							</a>
						</li>
						<?php foreach ($categories as $category): ?>
							<li>
								<a class="blog-taxonomy__link<?php echo ((int) $current_cat === (int) $category->term_id) ? ' is-active' : ''; ?>"
									href="<?php echo esc_url(add_query_arg('cat', (int) $category->term_id, $blog_page_url)); ?>">
									<?php echo esc_html($category->name); ?>
									<span><?php echo esc_html((int) $category->count); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</details>
			</aside>

			<div class="blog-content">
				<?php if ($featured_post_id):
					$featured_card_query = new WP_Query(array('p' => $featured_post_id, 'post_type' => 'post'));
					if ($featured_card_query->have_posts()):
						while ($featured_card_query->have_posts()):
							$featured_card_query->the_post();
							$featured_thumb = escortwp_child_get_post_card_image_url(get_the_ID(), 'large');
							?>
							<article class="blog-featured-card">
								<a href="<?php the_permalink(); ?>" class="blog-featured-card__media blog-card-link" data-blog-card-click>
									<?php if ($featured_thumb): ?>
										<img src="<?php echo esc_url($featured_thumb); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
									<?php else: ?>
										<span class="blog-featured-card__placeholder"><?php esc_html_e('Featured article', 'escortwp'); ?></span>
									<?php endif; ?>
								</a>
								<div class="blog-featured-card__body">
									<p class="blog-featured-card__eyebrow"><?php esc_html_e('Featured', 'escortwp'); ?></p>
									<h2><a href="<?php the_permalink(); ?>" class="blog-card-link" data-blog-card-click><?php the_title(); ?></a></h2>
									<p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 28, '...')); ?></p>
									<div class="blog-featured-card__meta">
										<span><?php echo esc_html(get_the_date()); ?></span>
										<span><?php echo esc_html(get_the_author()); ?></span>
									</div>
								</div>
							</article>
						<?php endwhile;
					endif;
					wp_reset_postdata();
				endif; ?>

					<section class="blog-recent-grid" aria-label="<?php esc_attr_e('Recent posts', 'escortwp'); ?>">
						<?php if ($recent_query->have_posts()): ?>
							<?php while ($recent_query->have_posts()):
								$recent_query->the_post();
								$thumb = escortwp_child_get_post_card_image_url(get_the_ID(), 'medium_large');
								?>
							<article class="blog-card">
								<a href="<?php the_permalink(); ?>" class="blog-card__media blog-card-link" data-blog-card-click>
									<?php if ($thumb): ?>
										<img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
									<?php else: ?>
										<span class="blog-card__placeholder"><?php esc_html_e('Read article', 'escortwp'); ?></span>
									<?php endif; ?>
								</a>
								<div class="blog-card__body">
									<h2><a href="<?php the_permalink(); ?>" class="blog-card-link" data-blog-card-click><?php the_title(); ?></a></h2>
									<p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22, '...')); ?></p>
									<div class="blog-card__meta">
										<span><?php echo esc_html(get_the_date()); ?></span>
										<span><?php echo esc_html(get_the_author()); ?></span>
									</div>
								</div>
							</article>
						<?php endwhile; ?>
					<?php else: ?>
						<p class="blog-empty"><?php esc_html_e('No articles found.', 'escortwp'); ?></p>
					<?php endif; ?>
				</section>

				<?php
				echo paginate_links(array(
					'total' => max(1, (int) $recent_query->max_num_pages),
					'current' => $paged,
					'prev_text' => esc_html__('Previous page', 'escortwp'),
					'next_text' => esc_html__('Next page', 'escortwp'),
				));
				wp_reset_postdata();
				?>
			</div>
		</div>
	</div>
</div>

<?php get_sidebar('left'); ?>
<div class="clear"></div>

<?php get_footer(); ?>
