<?php
/*
Template Name: Template Videos
*/

global $post, $wpdb;

get_header();

$videos_per_page = 12;
$video_page = isset($_GET['vpage']) ? max(1, (int) $_GET['vpage']) : 1;

$video_query = new WP_Query(array(
	'post_type' => 'attachment',
	'post_status' => 'inherit',
	'post_mime_type' => 'video/mp4',
	'posts_per_page' => $videos_per_page,
	'paged' => $video_page,
	'orderby' => 'date',
	'order' => 'DESC',
));

$total_videos = (int) $video_query->found_posts;
$total_pages = max(1, (int) $video_query->max_num_pages);
?>

<div class="contentwrapper videos-shell">
	<div class="body">
		<div class="bodybox videos-hero">
			<header class="videos-hero__header">
				<h1 class="videos-hero__title"><?php the_title(); ?></h1>
				<p class="videos-hero__intro">
					<?php esc_html_e('Browse recent video uploads in a cleaner grid. Tap play on any card to load that player only when needed.', 'escortwp'); ?>
				</p>
			</header>
			<div class="listing-summary"
				data-filter-summary
				data-filter-context="videos"
				data-filter-label="videos"
				data-total-results="<?php echo esc_attr($total_videos); ?>">
				<div class="listing-summary__metric">
					<span class="listing-summary__label"><?php esc_html_e('Videos', 'escortwp'); ?></span>
					<strong class="listing-summary__value"><?php echo esc_html(number_format_i18n($total_videos)); ?></strong>
				</div>
				<div class="listing-summary__metric">
					<span class="listing-summary__label"><?php esc_html_e('Page', 'escortwp'); ?></span>
					<strong class="listing-summary__value"><?php echo esc_html($video_page . ' / ' . $total_pages); ?></strong>
				</div>
			</div>
		</div>

		<div class="bodybox videos-grid-shell" id="listing-results">
			<?php if ($video_query->have_posts()): ?>
				<div class="videos-grid" data-video-grid>
						<?php while ($video_query->have_posts()):
							$video_query->the_post();
							$video_id = get_the_ID();
							$video_url = wp_get_attachment_url($video_id);
							$parent_id = (int) wp_get_post_parent_id($video_id);
							$video_title = get_the_title($video_id);
							if (empty($video_title)) {
								$video_title = esc_html__('Video Upload', 'escortwp');
							}
							$parent_title = $parent_id ? get_the_title($parent_id) : esc_html__('Independent Upload', 'escortwp');
							$poster = escortwp_child_get_video_card_poster_url($video_id, $parent_id, 'medium_large');
							$duration = get_post_meta($video_id, '_wp_attachment_metadata', true);
							$duration_label = '';
							if (is_array($duration) && !empty($duration['length_formatted'])) {
								$duration_label = $duration['length_formatted'];
							}
						?>
						<article class="video-card" data-video-id="<?php echo esc_attr($video_id); ?>">
								<div class="video-card__media<?php echo empty($poster) ? ' video-card__media--placeholder' : ''; ?>">
									<?php if (!empty($poster)): ?>
										<img src="<?php echo esc_url($poster); ?>"
											alt="<?php echo esc_attr($video_title); ?>"
											loading="lazy" />
									<?php else: ?>
										<span class="video-card__placeholder-label"><?php esc_html_e('Preview unavailable', 'escortwp'); ?></span>
									<?php endif; ?>
									<button type="button"
										class="video-card__play"
										data-video-play
										data-video-src="<?php echo esc_url($video_url); ?>"
										data-video-poster="<?php echo esc_url($poster); ?>"
										data-video-title="<?php echo esc_attr($parent_title); ?>"
										aria-label="<?php esc_attr_e('Play video', 'escortwp'); ?>">
									<span class="video-card__play-icon" aria-hidden="true">▶</span>
									<?php esc_html_e('Play', 'escortwp'); ?>
								</button>
								<?php if (!empty($duration_label)): ?>
									<span class="video-card__duration"><?php echo esc_html($duration_label); ?></span>
								<?php endif; ?>
							</div>
							<div class="video-card__player" data-video-player hidden></div>
							<div class="video-card__meta">
								<h2 class="video-card__title">
									<?php if ($parent_id): ?>
										<a href="<?php echo esc_url(get_permalink($parent_id)); ?>"><?php echo esc_html($parent_title); ?></a>
									<?php else: ?>
										<?php echo esc_html($parent_title); ?>
									<?php endif; ?>
								</h2>
								<div class="video-card__labels">
									<span class="video-card__label"><?php esc_html_e('MP4', 'escortwp'); ?></span>
									<span class="video-card__label"><?php echo esc_html(get_the_date()); ?></span>
								</div>
							</div>
						</article>
					<?php endwhile; ?>
				</div>

				<?php if ($total_pages > 1): ?>
					<nav class="videos-pagination" aria-label="<?php esc_attr_e('Video pages', 'escortwp'); ?>">
						<?php
						echo paginate_links(array(
							'base' => esc_url(add_query_arg('vpage', '%#%')),
							'format' => '',
							'current' => $video_page,
							'total' => $total_pages,
							'prev_text' => esc_html__('Previous', 'escortwp'),
							'next_text' => esc_html__('Next', 'escortwp'),
						));
						?>
					</nav>
				<?php endif; ?>
			<?php else: ?>
				<p class="videos-grid__empty"><?php esc_html_e('Videos not found!', 'escortwp'); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php
wp_reset_postdata();
get_sidebar('left');
get_sidebar('right');
?>
<div class="clear"></div>
<?php get_footer(); ?>
