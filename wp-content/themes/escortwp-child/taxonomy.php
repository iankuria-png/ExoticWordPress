<?php

global $taxonomy_location_url, $taxonomy_profile_name_plural, $taxonomy_agency_url, $taxonomy_profile_url;
get_header();

$term = get_queried_object();
$from_term = ($term && !is_wp_error($term)) ? get_term($term->term_id, $taxonomy_location_url) : null;
$from = ($from_term && !is_wp_error($from_term)) ? $from_term->name : '';
$term_name = $term && !is_wp_error($term) ? $term->name : '';
$child_terms = array();
if ($term && !is_wp_error($term)) {
	$termchildren = get_term_children($term->term_id, $term->taxonomy);
	foreach ($termchildren as $child) {
		$child_term = get_term_by('id', $child, $term->taxonomy);
		if ($child_term && !is_wp_error($child_term)) {
			$child_terms[] = $child_term;
		}
	}
}

$posts_per_page = 40;
$paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));

$featured_count = 0;
$featured_query = null;
if ($term && !is_wp_error($term)) {
	$featured_args = array(
		'post_type' => $taxonomy_profile_url,
		'tax_query' => array(
			array(
				'taxonomy' => $taxonomy_location_url,
				'field' => 'id',
				'terms' => $term->term_id,
			),
		),
		'meta_query' => array(
			array(
				'key' => 'featured',
				'value' => '1',
				'compare' => '=',
				'type' => 'NUMERIC',
			),
		),
		'orderby' => 'rand',
		'posts_per_page' => get_option('headerslideritems'),
	);
	$featured_query = new WP_Query($featured_args);
	$featured_count = (int) $featured_query->found_posts;
}

$premium_count = 0;
$normal_count = 0;
$all = null;

if ($term && !is_wp_error($term)) {
	$premium_all_args = array(
		'post_type' => array($taxonomy_profile_url, $taxonomy_agency_url),
		'posts_per_page' => 1,
		'paged' => 1,
		'meta_query' => array(
			array('key' => 'premium', 'value' => '1', 'compare' => '=', 'type' => 'NUMERIC'),
		),
		'tax_query' => array(
			array('taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id),
		),
	);
	$premium_all = new WP_Query($premium_all_args);
	$premium_count = (int) $premium_all->found_posts;

	$normal_all_args = array(
		'post_type' => array($taxonomy_profile_url, $taxonomy_agency_url),
		'posts_per_page' => 1,
		'paged' => 1,
		'meta_query' => array(
			array('key' => 'premium', 'value' => '0', 'compare' => '=', 'type' => 'NUMERIC'),
		),
		'tax_query' => array(
			array('taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id),
		),
	);
	$normal_all = new WP_Query($normal_all_args);
	$normal_count = (int) $normal_all->found_posts;

	$premium_args = array(
		'post_type' => array($taxonomy_profile_url, $taxonomy_agency_url),
		'posts_per_page' => $posts_per_page,
		'paged' => $paged,
		'orderby' => 'meta_value_num',
		'meta_key' => 'premium_since',
		'meta_query' => array(
			array('key' => 'premium', 'value' => '1', 'compare' => '=', 'type' => 'NUMERIC'),
		),
		'tax_query' => array(
			array('taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id),
		),
	);
	$premium = new WP_Query($premium_args);

	if ($paged < 2) {
		$normal_offset = 0;
	} else {
		$normal_offset = (($paged - 1) * $posts_per_page) - $premium_count;
		$normal_offset = max(0, $normal_offset);
	}

	$normal_args = array(
		'offset' => $normal_offset,
		'post_type' => array($taxonomy_profile_url, $taxonomy_agency_url),
		'posts_per_page' => max(0, $posts_per_page - count($premium->posts)),
		'orderby' => 'date',
		'order' => 'DESC',
		'meta_query' => array(
			array('key' => 'premium', 'value' => '0', 'compare' => '=', 'type' => 'NUMERIC'),
		),
		'tax_query' => array(
			array('taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id),
		),
	);

	$all = $premium;
	if (count($premium->posts) < $posts_per_page) {
		$normal = new WP_Query($normal_args);
		$all->posts = array_merge($premium->posts, $normal->posts);
		$all->post_count = count($all->posts);
	}
}

$total_results = $featured_count + $premium_count + $normal_count;
$filter_label = $term_name ? sprintf(esc_html__('%s', 'escortwp'), $term_name) : esc_html__('Location', 'escortwp');
$clear_page_id = (int) get_option('all_profiles_page_id');
$clear_link = $clear_page_id ? get_permalink($clear_page_id) : home_url('/');

$tours_found_posts = 0;
$tours = null;
if ($term && !is_wp_error($term)) {
	$tours_args = array(
		'post_type' => 'tour',
		'post_status' => 'publish',
		'meta_key' => 'start',
		'meta_query' => array(
			array('key' => 'start', 'value' => mktime(0, 0, 0), 'compare' => '<=', 'type' => 'NUMERIC'),
			array('key' => 'end', 'value' => mktime(23, 59, 59), 'compare' => '>=', 'type' => 'NUMERIC'),
		),
		'tax_query' => array(
			array('taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id),
		),
		'orderby' => 'meta_value_num',
		'order' => 'rand',
		'posts_per_page' => $posts_per_page,
	);
	$tours = new WP_Query($tours_args);
	$tours_found_posts = (int) $tours->found_posts;
}

$top_text = '';
if (function_exists('get_field') && $term && !is_wp_error($term)) {
	$top_text = (string) get_field('top_text', $term->taxonomy . '_' . $term->term_id);
}
?>

<div class="contentwrapper listing-shell listing-shell--taxonomy">
	<div class="body">
		<div class="bodybox listing-context" id="listing-context">
			<header class="listing-context__header">
				<h1 class="listing-context__title">
					<?php
					printf(
						esc_html__('%1$s from %2$s', 'escortwp'),
						ucfirst($taxonomy_profile_name_plural),
						esc_html($from)
					);
					?>
				</h1>
				<p class="listing-context__description">
					<?php esc_html_e('Explore active, premium, and featured profiles in this location with clearer filters and status context.', 'escortwp'); ?>
				</p>
				<div class="listing-context__active" data-filter-context="<?php echo esc_attr($filter_label); ?>">
					<span class="listing-context__active-label"><?php esc_html_e('Active context', 'escortwp'); ?>:</span>
					<span class="listing-context__active-value"><?php echo esc_html($filter_label); ?></span>
				</div>
			</header>

			<nav class="listing-anchors" aria-label="<?php esc_attr_e('Jump to section', 'escortwp'); ?>">
				<a href="#listing-filters"><?php esc_html_e('Filters', 'escortwp'); ?></a>
				<a href="#listing-summary"><?php esc_html_e('Summary', 'escortwp'); ?></a>
				<a href="#listing-results"><?php esc_html_e('Results', 'escortwp'); ?></a>
			</nav>

			<div class="listing-filter-rows" id="listing-filters" role="group" aria-label="<?php esc_attr_e('Location filters', 'escortwp'); ?>">
				<div class="listing-chip-row listing-chip-row--primary">
					<?php if (!empty($child_terms)): ?>
						<?php foreach ($child_terms as $child_term): ?>
							<a class="listing-chip"
								href="<?php echo esc_url(get_term_link($child_term)); ?>"
								data-filter-label="<?php echo esc_attr($child_term->name); ?>">
								<?php echo esc_html($child_term->name); ?>
								<span class="listing-chip__count"><?php echo esc_html((int) $child_term->count); ?></span>
							</a>
						<?php endforeach; ?>
					<?php else: ?>
						<span class="listing-chip listing-chip--ghost"><?php esc_html_e('No sub-locations', 'escortwp'); ?></span>
					<?php endif; ?>
				</div>

				<div class="listing-chip-row listing-chip-row--secondary">
					<?php
					$quick_links = array(
						array('label' => esc_html__('Online', 'escortwp'), 'url' => get_permalink((int) get_option('all_online_profiles_page_id')), 'status' => true),
						array('label' => esc_html__('Premium', 'escortwp'), 'url' => get_permalink((int) get_option('all_premium_profiles_page_id'))),
						array('label' => esc_html__('New', 'escortwp'), 'url' => get_permalink((int) get_option('all_new_profiles_page_id'))),
						array('label' => esc_html__('All locations', 'escortwp'), 'url' => $clear_link),
					);
					foreach ($quick_links as $quick_link):
						if (empty($quick_link['url'])) {
							continue;
						}
						?>
						<a class="listing-chip<?php echo !empty($quick_link['status']) ? ' listing-chip--status' : ''; ?>" href="<?php echo esc_url($quick_link['url']); ?>">
							<?php if (!empty($quick_link['status'])): ?>
								<span class="listing-chip__status-dot" aria-hidden="true"></span>
							<?php endif; ?>
							<?php echo esc_html($quick_link['label']); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="listing-summary" id="listing-summary"
				data-filter-summary
				data-filter-context="<?php echo esc_attr(strtolower($filter_label)); ?>"
				data-filter-label="<?php echo esc_attr($filter_label); ?>"
				data-total-results="<?php echo esc_attr($total_results); ?>">
				<div class="listing-summary__metric">
					<span class="listing-summary__label"><?php esc_html_e('Total results', 'escortwp'); ?></span>
					<strong class="listing-summary__value"><?php echo esc_html(number_format_i18n($total_results)); ?></strong>
				</div>
				<div class="listing-summary__metric">
					<span class="listing-summary__label"><?php esc_html_e('Active filter', 'escortwp'); ?></span>
					<strong class="listing-summary__value" data-filter-context><?php echo esc_html($filter_label); ?></strong>
				</div>
				<a class="listing-summary__clear" href="<?php echo esc_url($clear_link); ?>">
					<?php esc_html_e('Clear filters', 'escortwp'); ?>
				</a>
			</div>
		</div>

		<?php if ($featured_query && $featured_query->have_posts()): ?>
			<div class="bodybox bodybox-homepage featured-mobile featured-desktop listing-results" id="listing-results-featured">
				<div class="section-header">
					<h2 class="section-heading"><?php esc_html_e('VIP Escorts', 'escortwp'); ?></h2>
				</div>
				<div class="escort-grid__container">
					<?php
					while ($featured_query->have_posts()) {
						$featured_query->the_post();
						include get_theme_file_path('/loop-show-profile.php');
					}
					wp_reset_postdata();
					?>
				</div>
			</div>
		<?php endif; ?>

		<div class="bodybox listing-results" id="listing-results">
			<?php
			if ($all && $all->have_posts()) {
				echo '<div class="escort-grid__container">';
				while ($all->have_posts()) {
					$all->the_post();
					include get_theme_file_path('/loop-show-profile.php');
				}
				echo '</div>';
				$total_pages = max(1, (int) ceil(($premium_count + $normal_count) / $posts_per_page));
				dolce_pagination($total_pages, $paged);
				wp_reset_postdata();
			} elseif (($premium_count + $normal_count + $tours_found_posts) === 0) {
				printf(esc_html__('No %s here yet', 'escortwp'), esc_html($taxonomy_profile_name_plural));
			}
			?>
		</div>

		<?php if ($tours && $tours->have_posts()): ?>
			<div class="bodybox listing-results" id="listing-results-tours">
				<div class="section-header">
					<h2 class="section-heading">
						<?php
						printf(
							esc_html__('Tours happening now in %s', 'escortwp'),
							esc_html($from)
						);
						?>
					</h2>
				</div>
				<?php
				while ($tours->have_posts()) {
					$tours->the_post();
					include get_template_directory() . '/loop-show-tour.php';
				}
				wp_reset_postdata();
				?>
			</div>
		<?php endif; ?>

		<div class="bodybox listing-editorial" id="listing-editorial">
			<?php echo term_description(get_queried_object_id(), $taxonomy_location_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			<?php if (!empty($top_text)): ?>
				<div class="top-mobile-expand listing-editorial__content" data-editorial-intro>
					<div data-editorial-content><?php echo $top_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<button type="button" class="listing-editorial__toggle" data-editorial-toggle aria-expanded="false"><?php esc_html_e('Read more', 'escortwp'); ?></button>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php get_sidebar('left'); ?>
<?php get_sidebar('right'); ?>
<div class="clear"></div>
<?php get_footer(); ?>
