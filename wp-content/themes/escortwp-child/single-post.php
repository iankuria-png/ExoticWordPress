<?php
$blog_section = 'yes';
get_header();
?>

<div class="contentwrapper blog-shell">
	<div class="body blog-single">
		<?php while (have_posts()): the_post();
			$word_count = str_word_count(wp_strip_all_tags(get_the_content()));
			$read_time = max(1, (int) ceil($word_count / 220));
			$excerpt = wp_strip_all_tags(get_the_excerpt());
			$takeaways = array_values(array_filter(array_map('trim', preg_split('/(?<=[.!?])\s+/', $excerpt))));
			$takeaways = array_slice($takeaways, 0, 3);
			$categories = get_the_category();
			$related_args = array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => 3,
				'post__not_in' => array(get_the_ID()),
				'ignore_sticky_posts' => true,
			);
			if (!empty($categories)) {
				$related_args['category__in'] = wp_list_pluck($categories, 'term_id');
			}
			$related_posts = new WP_Query($related_args);
			?>
			<article <?php post_class('bodybox blog-article'); ?> id="post-<?php the_ID(); ?>">
				<header class="blog-article__header">
					<h1 class="blog-article__title"><?php the_title(); ?></h1>
					<div class="blog-article__meta">
						<span><?php echo esc_html(get_the_date()); ?></span>
						<span><?php echo esc_html(get_the_author()); ?></span>
						<span><?php echo esc_html(sprintf(_n('%d min read', '%d min read', $read_time, 'escortwp'), $read_time)); ?></span>
					</div>
					<nav class="blog-article__anchors" aria-label="<?php esc_attr_e('Article sections', 'escortwp'); ?>">
						<a href="#key-takeaways"><?php esc_html_e('Takeaways', 'escortwp'); ?></a>
						<a href="#post-content"><?php esc_html_e('Read', 'escortwp'); ?></a>
						<a href="#related-posts"><?php esc_html_e('Related', 'escortwp'); ?></a>
					</nav>
				</header>

				<?php if (!empty($takeaways)): ?>
					<section class="blog-article__takeaways" id="key-takeaways">
						<h2><?php esc_html_e('Key takeaways', 'escortwp'); ?></h2>
						<ul>
							<?php foreach ($takeaways as $takeaway): ?>
								<li><?php echo esc_html($takeaway); ?></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<div class="blog-article__content" id="post-content">
					<?php the_content(); ?>
					<?php edit_post_link(__('Edit', 'escortwp'), '<p class="blog-article__edit">', '</p>'); ?>
				</div>

				<section class="blog-article__related" id="related-posts">
					<h2><?php esc_html_e('Related posts', 'escortwp'); ?></h2>
						<?php if ($related_posts->have_posts()): ?>
							<div class="blog-related-grid">
								<?php while ($related_posts->have_posts()):
									$related_posts->the_post();
									$thumb = escortwp_child_get_post_card_image_url(get_the_ID(), 'medium_large');
									?>
								<article class="blog-related-card">
									<a href="<?php the_permalink(); ?>" class="blog-card-link" data-blog-card-click>
										<?php if ($thumb): ?>
											<img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
										<?php endif; ?>
										<h3><?php the_title(); ?></h3>
										<p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18, '...')); ?></p>
									</a>
								</article>
							<?php endwhile; ?>
						</div>
					<?php else: ?>
						<p><?php esc_html_e('No related posts available yet.', 'escortwp'); ?></p>
					<?php endif; ?>
					<?php wp_reset_postdata(); ?>
				</section>

				<?php comments_template(); ?>
			</article>
		<?php endwhile; ?>
	</div>
</div>

<?php get_sidebar('left'); ?>
<?php get_sidebar('right'); ?>
<div class="clear"></div>
<?php get_footer(); ?>
