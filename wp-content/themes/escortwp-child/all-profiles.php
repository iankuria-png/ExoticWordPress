<?php
/*
Template Name: All profiles
*/

get_header();

global $settings_theme_genders, $taxonomy_profile_name, $taxonomy_profile_name_plural, $taxonomy_profile_url;

$page_id = get_the_ID();
$all_profiles_page_id = (int) get_option('all_profiles_page_id');
$all_profiles_url = $all_profiles_page_id ? get_permalink($all_profiles_page_id) : home_url('/');
$all_profiles_titles = array(
	$all_profiles_page_id => sprintf(esc_html__('All %s', 'escortwp'), $taxonomy_profile_name_plural),
	(int) get_option('all_female_profiles_page_id') => sprintf(esc_html__('All female %s', 'escortwp'), $taxonomy_profile_name_plural),
	(int) get_option('all_male_profiles_page_id') => sprintf(esc_html__('All male %s', 'escortwp'), $taxonomy_profile_name_plural),
	(int) get_option('all_couple_profiles_page_id') => sprintf(esc_html__('All couple %s', 'escortwp'), $taxonomy_profile_name_plural),
	(int) get_option('all_gay_profiles_page_id') => sprintf(esc_html__('All gay %s', 'escortwp'), $taxonomy_profile_name_plural),
	(int) get_option('all_trans_profiles_page_id') => sprintf(esc_html__('All transsexual %s', 'escortwp'), $taxonomy_profile_name_plural),
	(int) get_option('all_independent_profiles_page_id') => sprintf(esc_html__('All independent %s', 'escortwp'), $taxonomy_profile_name_plural),
	(int) get_option('all_premium_profiles_page_id') => sprintf(esc_html__('All premium %s', 'escortwp'), $taxonomy_profile_name_plural),
	(int) get_option('all_verified_profiles_page_id') => sprintf(esc_html__('All verified %s', 'escortwp'), $taxonomy_profile_name_plural),
	(int) get_option('all_new_profiles_page_id') => sprintf(esc_html__('All newly created %s', 'escortwp'), $taxonomy_profile_name_plural),
	(int) get_option('all_online_profiles_page_id') => sprintf(esc_html__('All online %s', 'escortwp'), $taxonomy_profile_name_plural),
);

$current_title = isset($all_profiles_titles[$page_id]) ? $all_profiles_titles[$page_id] : get_the_title();
$current_slug = (string) get_post_field('post_name', $page_id);
$editorial_mode = preg_match('/kenyaraha|nairobiraha|telegram|kutombana/i', $current_slug) === 1;

$descriptor = sprintf(
	esc_html__('Browse verified, premium, and newly added %s with fast filters and direct contact options.', 'escortwp'),
	strtolower($taxonomy_profile_name_plural)
);

$filter_label = esc_html__('All', 'escortwp');
foreach ($all_profiles_titles as $mapped_page_id => $mapped_title) {
	if ((int) $mapped_page_id === (int) $page_id) {
		$filter_label = wp_strip_all_tags($mapped_title);
		break;
	}
}

$posts_per_page = 40;
$paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));

$gender_pages = array(
	(int) get_option('all_female_profiles_page_id') => 1,
	(int) get_option('all_male_profiles_page_id') => 2,
	(int) get_option('all_couple_profiles_page_id') => 3,
	(int) get_option('all_gay_profiles_page_id') => 4,
	(int) get_option('all_trans_profiles_page_id') => 5,
);

$online_users = array();
if ($page_id === (int) get_option('all_online_profiles_page_id')) {
	$online_users = (new WP_User_Query(array(
		'meta_key' => 'last_online',
		'meta_value' => current_time('timestamp') - 60 * 5,
		'meta_compare' => '>=',
		'fields' => 'ids',
	)))->get_results();
	if (empty($online_users)) {
		$online_users = array(0);
	}
}

$premium_all_args = array(
	'post_type' => $taxonomy_profile_url,
	'posts_per_page' => 1,
	'paged' => 1,
	'orderby' => 'meta_value_num',
	'meta_key' => 'premium_since',
	'meta_query' => array(
		array(
			'key' => 'premium',
			'value' => '1',
			'compare' => '=',
			'type' => 'NUMERIC',
		),
	),
);

if (isset($gender_pages[$page_id])) {
	$premium_all_args['meta_query'][] = array(
		'key' => 'gender',
		'value' => $gender_pages[$page_id],
		'compare' => '=',
		'type' => 'NUMERIC',
	);
}

if ($page_id === (int) get_option('all_independent_profiles_page_id')) {
	$premium_all_args['meta_query'][] = array(
		'key' => 'independent',
		'value' => 'yes',
		'compare' => '=',
	);
}

if ($page_id === (int) get_option('all_new_profiles_page_id')) {
	$newlabelperiod = max(1, (int) get_option('newlabelperiod'));
	$premium_all_args['date_query'] = array(
		array(
			'after' => date('Y-m-d H:i:s', strtotime('-' . $newlabelperiod . ' days')),
			'inclusive' => true,
		),
	);
}

if ($page_id === (int) get_option('all_online_profiles_page_id')) {
	$premium_all_args['author__in'] = $online_users;
}

$premium_all = new WP_Query($premium_all_args);
$premium_found_posts = (int) $premium_all->found_posts;

$normal_all_args = array(
	'post_type' => $taxonomy_profile_url,
	'posts_per_page' => 1,
	'paged' => 1,
	'meta_query' => array(
		array(
			'key' => 'premium',
			'value' => '0',
			'compare' => '=',
			'type' => 'NUMERIC',
		),
	),
);

if (isset($gender_pages[$page_id])) {
	$normal_all_args['meta_query'][] = array(
		'key' => 'gender',
		'value' => $gender_pages[$page_id],
		'compare' => '=',
		'type' => 'NUMERIC',
	);
}

if ($page_id === (int) get_option('all_independent_profiles_page_id')) {
	$normal_all_args['meta_query'][] = array(
		'key' => 'independent',
		'value' => 'yes',
		'compare' => '=',
	);
}

if ($page_id === (int) get_option('all_new_profiles_page_id')) {
	$newlabelperiod = max(1, (int) get_option('newlabelperiod'));
	$normal_all_args['date_query'] = array(
		array(
			'after' => date('Y-m-d H:i:s', strtotime('-' . $newlabelperiod . ' days')),
			'inclusive' => true,
		),
	);
}

if ($page_id === (int) get_option('all_online_profiles_page_id')) {
	$normal_all_args['author__in'] = $online_users;
}

if ($page_id !== (int) get_option('all_premium_profiles_page_id')) {
	$normal_all = new WP_Query($normal_all_args);
	$normal_found_posts = (int) $normal_all->found_posts;
} else {
	$normal_found_posts = 0;
}

$premium_args = $premium_all_args;
$premium_args['posts_per_page'] = $posts_per_page;
$premium_args['paged'] = $paged;
$premium = new WP_Query($premium_args);

if ($paged < 2) {
	$normal_offset = 0;
} else {
	$normal_offset = (($paged - 1) * $posts_per_page) - $premium_found_posts;
	if ($normal_offset < 0) {
		$normal_offset = 0;
	}
}

$normal_args = $normal_all_args;
$normal_args['offset'] = $normal_offset;
$normal_args['posts_per_page'] = max(0, $posts_per_page - (int) count($premium->posts));
$normal = new WP_Query($normal_args);

$all = $premium;
if (count($premium->posts) < $posts_per_page && $page_id !== (int) get_option('all_premium_profiles_page_id')) {
	$merged = array_merge($premium->posts, $normal->posts);
	$all->posts = $merged;
	$all->post_count = count($merged);
}

$total_results = $premium_found_posts + $normal_found_posts;

$section_links_primary = array(
	array(
		'label' => esc_html__('All', 'escortwp'),
		'page_id' => $all_profiles_page_id,
	),
	array(
		'label' => esc_html__('Female', 'escortwp'),
		'page_id' => (int) get_option('all_female_profiles_page_id'),
	),
	array(
		'label' => esc_html__('Male', 'escortwp'),
		'page_id' => (int) get_option('all_male_profiles_page_id'),
	),
	array(
		'label' => esc_html__('Couple', 'escortwp'),
		'page_id' => (int) get_option('all_couple_profiles_page_id'),
	),
	array(
		'label' => esc_html__('Gay', 'escortwp'),
		'page_id' => (int) get_option('all_gay_profiles_page_id'),
	),
	array(
		'label' => esc_html__('Transsexual', 'escortwp'),
		'page_id' => (int) get_option('all_trans_profiles_page_id'),
	),
);

$section_links_secondary = array(
	array(
		'label' => esc_html__('Independent', 'escortwp'),
		'page_id' => (int) get_option('all_independent_profiles_page_id'),
	),
	array(
		'label' => esc_html__('Premium', 'escortwp'),
		'page_id' => (int) get_option('all_premium_profiles_page_id'),
	),
	array(
		'label' => esc_html__('Verified', 'escortwp'),
		'page_id' => (int) get_option('all_verified_profiles_page_id'),
	),
	array(
		'label' => esc_html__('New', 'escortwp'),
		'page_id' => (int) get_option('all_new_profiles_page_id'),
	),
	array(
		'label' => esc_html__('Online', 'escortwp'),
		'page_id' => (int) get_option('all_online_profiles_page_id'),
		'is_status' => true,
	),
);

$page_content = '';
if (have_posts()) {
	while (have_posts()) {
		the_post();
		ob_start();
		the_content();
		$page_content .= ob_get_clean();
	}
}
?>

<div class="contentwrapper listing-shell listing-shell--profiles">
	<div class="body">
		<div class="bodybox listing-context" id="listing-context">
			<header class="listing-context__header">
				<h1 class="listing-context__title"><?php echo esc_html($current_title); ?></h1>
				<p class="listing-context__description"><?php echo esc_html($descriptor); ?></p>
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

			<div class="listing-filter-rows" id="listing-filters" role="group" aria-label="<?php esc_attr_e('Profile filters', 'escortwp'); ?>">
				<div class="listing-chip-row listing-chip-row--primary">
					<?php foreach ($section_links_primary as $chip):
						$chip_page_id = (int) $chip['page_id'];
						if ($chip_page_id < 1) {
							continue;
						}
						$is_active = $chip_page_id === (int) $page_id;
						?>
						<a class="listing-chip<?php echo $is_active ? ' is-active' : ''; ?>"
							href="<?php echo esc_url(get_permalink($chip_page_id)); ?>"
							aria-current="<?php echo $is_active ? 'page' : 'false'; ?>">
							<?php echo esc_html($chip['label']); ?>
						</a>
					<?php endforeach; ?>
				</div>

				<div class="listing-chip-row listing-chip-row--secondary">
					<?php foreach ($section_links_secondary as $chip):
						$chip_page_id = (int) $chip['page_id'];
						if ($chip_page_id < 1) {
							continue;
						}
						$is_active = $chip_page_id === (int) $page_id;
						?>
						<a class="listing-chip<?php echo !empty($chip['is_status']) ? ' listing-chip--status' : ''; ?><?php echo $is_active ? ' is-active' : ''; ?>"
							href="<?php echo esc_url(get_permalink($chip_page_id)); ?>"
							aria-current="<?php echo $is_active ? 'page' : 'false'; ?>">
							<?php if (!empty($chip['is_status'])): ?>
								<span class="listing-chip__status-dot" aria-hidden="true"></span>
							<?php endif; ?>
							<?php echo esc_html($chip['label']); ?>
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
					<span class="listing-summary__label"><?php esc_html_e('Results', 'escortwp'); ?></span>
					<strong class="listing-summary__value"><?php echo esc_html(number_format_i18n($total_results)); ?></strong>
				</div>
				<div class="listing-summary__metric">
					<span class="listing-summary__label"><?php esc_html_e('Active filter', 'escortwp'); ?></span>
					<strong class="listing-summary__value" data-filter-context><?php echo esc_html($filter_label); ?></strong>
				</div>
				<a class="listing-summary__clear" href="<?php echo esc_url($all_profiles_url); ?>">
					<?php esc_html_e('Clear filters', 'escortwp'); ?>
				</a>
			</div>

			<?php if (!empty($page_content)): ?>
				<div class="listing-editorial<?php echo $editorial_mode ? ' listing-editorial--clamped' : ''; ?>" data-editorial-intro>
					<div class="listing-editorial__content" data-editorial-content>
						<?php echo $page_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<?php if ($editorial_mode): ?>
						<button type="button" class="listing-editorial__toggle" data-editorial-toggle aria-expanded="false">
							<?php esc_html_e('Read more', 'escortwp'); ?>
						</button>
						<div class="listing-editorial__accordion">
							<details>
								<summary><?php esc_html_e('How this directory works', 'escortwp'); ?></summary>
								<p><?php esc_html_e('Use the filter chips above to jump between profile types, then review each card for status and direct contact options.', 'escortwp'); ?></p>
							</details>
							<details>
								<summary><?php esc_html_e('Safety and verification tips', 'escortwp'); ?></summary>
								<p><?php esc_html_e('Prioritize profiles with verification badges and confirm details directly before booking.', 'escortwp'); ?></p>
							</details>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php edit_post_link(esc_html__('Add some text here', 'escortwp'), '<div class="clear"></div>', '<div class="clear10"></div>'); ?>
		</div>

		<div class="bodybox listing-results" id="listing-results">
			<?php
			if ($all->have_posts()):
				echo '<div class="escort-grid__container">';
				while ($all->have_posts()):
					$all->the_post();
					include get_theme_file_path('/loop-show-profile.php');
				endwhile;
				echo '</div>';

				$total = max(1, (int) ceil($total_results / $posts_per_page));
				dolce_pagination($total, $paged);
			else:
				printf(esc_html__('No %s here yet', 'escortwp'), esc_html($taxonomy_profile_name_plural));
			endif;
			wp_reset_postdata();
			?>
		</div>

		<div class="clear"></div>
	</div>
</div>

<?php get_sidebar('left'); ?>
<?php get_sidebar('right'); ?>
<div class="clear"></div>

<?php get_footer(); ?>
