<?php
if (!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set('display_errors', error_reporting);
if (error_reporting == '1') { error_reporting(E_ALL); }
if (isdolcetheme !== 1) { die(); }

global $taxonomy_profile_name_plural, $taxonomy_location_url;

$location_args = array(
	'show_option_all' => '',
	'show_count' => '0',
	'hide_empty' => '0',
	'title_li' => '',
	'show_option_none' => '',
	'pad_counts' => '0',
	'taxonomy' => $taxonomy_location_url
);

$popular_location_slugs = array('nairobi', 'mombasa', 'kisumu', 'nakuru', 'eldoret', 'thika');
$popular_locations = get_terms(array(
	'taxonomy' => $taxonomy_location_url,
	'hide_empty' => false,
	'slug' => $popular_location_slugs,
));

if (!is_wp_error($popular_locations) && !empty($popular_locations)) {
	$slug_order = array_flip($popular_location_slugs);
	usort(
		$popular_locations,
		static function ($a, $b) use ($slug_order) {
			$a_index = $slug_order[$a->slug] ?? 999;
			$b_index = $slug_order[$b->slug] ?? 999;
			return $a_index <=> $b_index;
		}
	);
} else {
	$popular_locations = array();
}

$is_admin_home_sidebar_context = function_exists('escortwp_child_is_admin_home_sidebar_context')
	&& escortwp_child_is_admin_home_sidebar_context();
$admin_home_quick_links = $is_admin_home_sidebar_context && function_exists('escortwp_child_get_admin_home_quick_links')
	? escortwp_child_get_admin_home_quick_links()
	: array();
?>

<?php if ($is_admin_home_sidebar_context) : ?>
<div class="sidebar-left l sidebar-left--admin-home">
	<div class="admin-home-links">
		<details class="admin-home-links__details">
			<summary class="admin-home-links__summary">
				<span class="admin-home-links__summary-copy">
					<small class="admin-home-links__eyebrow"><?php esc_html_e('Admin Home', 'escortwp'); ?></small>
					<strong class="admin-home-links__title"><?php esc_html_e('Quick links', 'escortwp'); ?></strong>
				</span>
				<span class="admin-home-links__chevron" aria-hidden="true"></span>
			</summary>

			<?php if (!empty($admin_home_quick_links)) : ?>
				<ul class="admin-home-links__list">
					<?php foreach ($admin_home_quick_links as $quick_link) :
						$quick_link_url = isset($quick_link['url']) ? trim((string) $quick_link['url']) : '';
						$quick_link_label = isset($quick_link['label']) ? trim((string) $quick_link['label']) : '';
						$quick_link_icon = sanitize_html_class((string) ($quick_link['icon'] ?? 'icon-right-open-mini'));
						if ($quick_link_url === '' || $quick_link_label === '') {
							continue;
						}
						?>
						<li class="admin-home-links__item">
							<a class="admin-home-links__link" href="<?php echo esc_url($quick_link_url); ?>">
								<span class="icon <?php echo esc_attr($quick_link_icon); ?> admin-home-links__icon" aria-hidden="true"></span>
								<span class="admin-home-links__label"><?php echo esc_html($quick_link_label); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="admin-home-links__empty"><?php esc_html_e('No admin shortcuts available.', 'escortwp'); ?></p>
			<?php endif; ?>
		</details>
	</div>
</div>
<?php return; endif; ?>

<div class="sidebar-left l">

			<div class="countries sidebarcountries location-filter">
				<div class="location-header">
					<p class="location-header__eyebrow"><?php esc_html_e('Location', 'escortwp'); ?></p>
					<h4><?php esc_html_e('Browse by location', 'escortwp'); ?></h4>
					<p class="location-subcopy"><?php esc_html_e('Find escorts by county or city', 'escortwp'); ?></p>
				</div>
				<div class="location-search location-search--prominent">
					<label class="screen-reader-text" for="location-search-desktop"><?php esc_html_e('Search locations', 'escortwp'); ?></label>
					<span class="location-search__icon icon icon-search" aria-hidden="true"></span>
				<input
					id="location-search-desktop"
					class="location-search__input js-location-filter"
					type="search"
					placeholder="<?php esc_attr_e('Search county or city...', 'escortwp'); ?>"
					autocomplete="off"
					data-target="#desktop-location-list"
					/>
					<p class="location-search__empty" data-location-empty hidden><?php esc_html_e('No matching location found.', 'escortwp'); ?></p>
				</div>
				<div class="location-actions">
					<button type="button" class="location-geo-button" data-use-current-location>
						<span class="icon icon-location" aria-hidden="true"></span>
						<span><?php esc_html_e('Use my location', 'escortwp'); ?></span>
					</button>
					<p class="location-geo-status" data-geo-status hidden></p>
				</div>

				<?php if (!empty($popular_locations)) : ?>
					<div class="location-quick-picks">
					<p class="location-quick-picks__label"><?php esc_html_e('Popular counties', 'escortwp'); ?></p>
						<div class="location-quick-picks__chips">
							<?php foreach ($popular_locations as $popular_location) :
								$location_url = get_term_link($popular_location);
								if (is_wp_error($location_url)) {
									continue;
								}
								$location_attrs = function_exists('escortwp_child_get_location_link_data_attributes')
									? escortwp_child_get_location_link_data_attributes($popular_location, (string) $location_url)
									: '';
								?>
								<a class="location-quick-chip" href="<?php echo esc_url($location_url); ?>"<?php echo $location_attrs; ?>>
									<?php echo esc_html($popular_location->name); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

		        <ul class="country-list location-list" id="desktop-location-list">
					<?php
					if (function_exists('escortwp_child_render_location_tree')) {
						echo escortwp_child_render_location_tree($taxonomy_location_url);
					} else {
						wp_list_categories($location_args);
					}
					?>
		        </ul>
				<div class="clear"></div>
			</div>

			<div class="countries slidercountries"
				id="slidercountries"
				style="display:none;"
				role="dialog"
				aria-modal="true"
				aria-hidden="true"
				aria-labelledby="mobile-location-title"
				aria-describedby="mobile-location-subcopy">
				<div class="location-overlay-header">
					<div class="location-overlay-heading">
						<h4 id="mobile-location-title"><?php esc_html_e('Choose a Location', 'escortwp'); ?></h4>
						<p id="mobile-location-subcopy" class="location-overlay-subcopy">
							<?php esc_html_e('Find escorts by county or city', 'escortwp'); ?>
						</p>
					</div>
					<a href="#" class="close-country" aria-label="<?php esc_attr_e('Close location panel', 'escortwp'); ?>">
						<span class="icon icon-cancel" aria-hidden="true"></span>
					</a>
				</div>

				<div class="location-search location-search--mobile">
					<label class="screen-reader-text" for="location-search-mobile"><?php esc_html_e('Search locations', 'escortwp'); ?></label>
					<input
						id="location-search-mobile"
						class="location-search__input js-location-filter"
						type="search"
						placeholder="<?php esc_attr_e('Search county or city...', 'escortwp'); ?>"
						autocomplete="off"
						inputmode="search"
						data-target="#mobile-location-list"
					/>
					<p class="location-search__empty" data-location-empty hidden><?php esc_html_e('No matching location found.', 'escortwp'); ?></p>
				</div>
				<div class="location-actions location-actions--mobile">
					<button type="button" class="location-geo-button location-geo-button--mobile" data-use-current-location>
						<span class="icon icon-location" aria-hidden="true"></span>
						<span><?php esc_html_e('Use my location', 'escortwp'); ?></span>
					</button>
					<p class="location-geo-status" data-geo-status hidden></p>
				</div>

			<?php if (!empty($popular_locations)) : ?>
				<div class="location-quick-picks location-quick-picks--mobile">
					<p class="location-quick-picks__label"><?php esc_html_e('Quick picks', 'escortwp'); ?></p>
						<div class="location-quick-picks__chips">
							<?php foreach ($popular_locations as $popular_location) :
								$location_url = get_term_link($popular_location);
								if (is_wp_error($location_url)) {
									continue;
								}
								$location_attrs = function_exists('escortwp_child_get_location_link_data_attributes')
									? escortwp_child_get_location_link_data_attributes($popular_location, (string) $location_url)
									: '';
								?>
								<a class="location-quick-chip" href="<?php echo esc_url($location_url); ?>"<?php echo $location_attrs; ?>>
									<?php echo esc_html($popular_location->name); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<ul class="country-list location-list" id="mobile-location-list">
					<?php
					if (function_exists('escortwp_child_render_location_tree')) {
						echo escortwp_child_render_location_tree($taxonomy_location_url);
					} else {
						wp_list_categories($location_args);
					}
					?>
				</ul>
			<div class="clear"></div>
		</div>

		<div class="clear"></div>

	<?php if (is_active_sidebar('widget-sidebar-left') || current_user_can('level_10')) : ?>
	<div class="widgetbox-wrapper sidebar-left__resources">
		<?php if (!dynamic_sidebar('Sidebar Left') && current_user_can('level_10')) : ?>
			<?php _e('Go to your','escortwp'); ?> <a href="<?php echo admin_url('widgets.php'); ?>"><?php _e('widgets page','escortwp'); ?></a> <?php _e('to add content here','escortwp'); ?>.
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php // dynamic_sidebar('Left Ads'); ?>
</div>
