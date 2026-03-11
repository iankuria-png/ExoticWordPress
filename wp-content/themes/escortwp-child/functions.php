<?php
/**
 * EscortWP Child Theme functions
 */


if (!defined('isdolcetheme')) {
	define('isdolcetheme', 1);
}

/** Exit if accessed directly */
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Run legacy theme upgrade routines only from safe admin contexts.
 */
function escortwp_child_maybe_run_legacy_theme_upgrade()
{
	if (!is_admin() || wp_doing_ajax() || wp_doing_cron()) {
		return;
	}

	if (defined('REST_REQUEST') && REST_REQUEST) {
		return;
	}

	if (!current_user_can('manage_options') || !function_exists('upgrade_theme')) {
		return;
	}

	upgrade_theme();
}
add_action('admin_init', 'escortwp_child_maybe_run_legacy_theme_upgrade', 1);

/**
 * Execute the legacy expiration sweep outside normal front-end rendering.
 */
function escortwp_child_run_legacy_expiration_check()
{
	if (!function_exists('time_check_expired')) {
		return;
	}

	time_check_expired();
}

/**
 * Ensure the legacy expiration sweep has a scheduled cron entry.
 */
function escortwp_child_schedule_legacy_expiration_check()
{
	if (wp_next_scheduled('escortwp_child_legacy_expiration_check')) {
		return;
	}

	wp_schedule_event(time() + MINUTE_IN_SECONDS, 'hourly', 'escortwp_child_legacy_expiration_check');
}
add_action('init', 'escortwp_child_schedule_legacy_expiration_check');
add_action('escortwp_child_legacy_expiration_check', 'escortwp_child_run_legacy_expiration_check');

/**
 * Keep expiry maintenance reachable even if WP-Cron is delayed.
 */
function escortwp_child_maybe_run_legacy_expiration_check_from_admin()
{
	if (!is_admin() || wp_doing_ajax() || wp_doing_cron()) {
		return;
	}

	if (defined('REST_REQUEST') && REST_REQUEST) {
		return;
	}

	escortwp_child_run_legacy_expiration_check();
}
add_action('admin_init', 'escortwp_child_maybe_run_legacy_expiration_check_from_admin', 2);

/**
 * Resolve current template slug for analytics payloads.
 */
function escortwp_child_current_template_slug()
{
	if (is_front_page()) {
		return 'front-page';
	}

	if (is_home()) {
		return 'blog-home';
	}

	if (is_page_template()) {
		$template = (string) get_page_template_slug();
		if ($template) {
			return sanitize_title(str_replace('.php', '', basename($template)));
		}
	}

	if (is_singular('post')) {
		return 'single-post';
	}

	if (is_tax()) {
		return 'taxonomy';
	}

	return 'default';
}

/**
 * Cache the theme's first-image helper for the lifetime of the current request.
 */
function escortwp_child_get_cached_first_image($post_id, $size = '1')
{
	static $image_cache = array();

	$post_id = (int) $post_id;
	$size = (string) $size;

	if ($post_id < 1) {
		return '';
	}

	if (!isset($image_cache[$post_id])) {
		$image_cache[$post_id] = array();
	}

	if (!array_key_exists($size, $image_cache[$post_id])) {
		$image_cache[$post_id][$size] = function_exists('get_first_image')
			? (string) get_first_image($post_id, $size)
			: '';
	}

	return $image_cache[$post_id][$size];
}

/**
 * Prime request-local profile card data for a batch of posts.
 */
function escortwp_child_prime_profile_card_context(array $post_ids)
{
	static $primed_post_ids = array();
	global $escortwp_child_profile_card_context;

	$post_ids = array_values(array_unique(array_filter(array_map('intval', $post_ids))));
	if (empty($post_ids)) {
		return;
	}

	$post_ids = array_values(array_diff($post_ids, array_keys($primed_post_ids)));
	if (empty($post_ids)) {
		return;
	}

	update_meta_cache('post', $post_ids);

	$author_ids = array();
	foreach ($post_ids as $post_id) {
		$author_id = (int) get_post_field('post_author', $post_id);
		if ($author_id > 0) {
			$author_ids[$author_id] = $author_id;
		}
	}

	if (!empty($author_ids)) {
		update_meta_cache('user', array_values($author_ids));
	}

	$attachment_posts = get_posts(array(
		'post_type' => 'attachment',
		'post_status' => 'inherit',
		'post_parent__in' => $post_ids,
		'posts_per_page' => -1,
		'orderby' => 'ID',
		'order' => 'ASC',
	));

	$attachment_totals = array();
	foreach ($attachment_posts as $attachment_post) {
		$parent_id = (int) $attachment_post->post_parent;
		$mime_type = (string) $attachment_post->post_mime_type;

		if (!isset($attachment_totals[$parent_id])) {
			$attachment_totals[$parent_id] = array(
				'photo_count' => 0,
				'video_count' => 0,
			);
		}

		if (strpos($mime_type, 'image/') === 0) {
			$attachment_totals[$parent_id]['photo_count']++;
		} elseif (strpos($mime_type, 'video/') === 0) {
			$attachment_totals[$parent_id]['video_count']++;
		}
	}

	foreach ($post_ids as $post_id) {
		$author_id = (int) get_post_field('post_author', $post_id);

		$escortwp_child_profile_card_context[$post_id] = array(
			'phone' => (string) get_post_meta($post_id, 'phone', true),
			'featured' => (string) get_post_meta($post_id, 'featured', true),
			'premium' => (string) get_post_meta($post_id, 'premium', true),
			'verified' => (string) get_post_meta($post_id, 'verified', true),
			'birthday' => (string) get_post_meta($post_id, 'birthday', true),
			'last_online' => $author_id > 0 ? (string) get_user_meta($author_id, 'last_online', true) : '',
			'photo_count' => isset($attachment_totals[$post_id]['photo_count']) ? (int) $attachment_totals[$post_id]['photo_count'] : 0,
			'video_count' => isset($attachment_totals[$post_id]['video_count']) ? (int) $attachment_totals[$post_id]['video_count'] : 0,
			'image_5' => escortwp_child_get_cached_first_image($post_id, '5'),
			'image_default' => escortwp_child_get_cached_first_image($post_id, '1'),
			'image_4' => escortwp_child_get_cached_first_image($post_id, '4'),
		);

		$primed_post_ids[$post_id] = true;
	}
}

/**
 * Retrieve a primed profile card context for the current request.
 */
function escortwp_child_get_profile_card_context($post_id)
{
	global $escortwp_child_profile_card_context;

	$post_id = (int) $post_id;
	if ($post_id < 1 || empty($escortwp_child_profile_card_context[$post_id])) {
		return array();
	}

	return $escortwp_child_profile_card_context[$post_id];
}

/**
 * Resolve a usable card image for blog posts.
 * Falls back from featured image to first inline/attached image.
 */
function escortwp_child_get_post_card_image_url($post_id, $size = 'medium_large')
{
	$post_id = (int) $post_id;
	if ($post_id < 1) {
		return '';
	}

	$thumbnail_url = get_the_post_thumbnail_url($post_id, $size);
	if (!empty($thumbnail_url)) {
		return $thumbnail_url;
	}

	$content = (string) get_post_field('post_content', $post_id);
	if ($content !== '') {
		if (preg_match('/wp-image-([0-9]+)/i', $content, $attachment_match)) {
			$inline_attachment_url = wp_get_attachment_image_url((int) $attachment_match[1], $size);
			if (!empty($inline_attachment_url)) {
				return $inline_attachment_url;
			}
		}

		if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $img_match)) {
			return esc_url_raw($img_match[1]);
		}
	}

	$attachments = get_children(array(
		'post_parent' => $post_id,
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
		'numberposts' => 1,
		'orderby' => 'menu_order ID',
		'order' => 'ASC',
		'fields' => 'ids',
	));

	if (!empty($attachments)) {
		$attachment_id = (int) reset($attachments);
		$attachment_url = wp_get_attachment_image_url($attachment_id, $size);
		if (!empty($attachment_url)) {
			return $attachment_url;
		}
	}

	return '';
}

/**
 * Resolve a video preview image for video cards.
 * Priority: attachment featured image -> sidecar JPG -> parent post card image.
 */
function escortwp_child_get_video_card_poster_url($video_id, $parent_id = 0, $size = 'medium_large')
{
	$video_id = (int) $video_id;
	$parent_id = (int) $parent_id;

	if ($video_id < 1) {
		return '';
	}

	$attachment_thumb = get_the_post_thumbnail_url($video_id, $size);
	if (!empty($attachment_thumb)) {
		return $attachment_thumb;
	}

	$attached_file = get_attached_file($video_id);
	if (!empty($attached_file)) {
		$sidecar_file = $attached_file . '.jpg';
		if (file_exists($sidecar_file)) {
			$uploads = wp_get_upload_dir();
			$basedir = isset($uploads['basedir']) ? wp_normalize_path($uploads['basedir']) : '';
			$baseurl = isset($uploads['baseurl']) ? (string) $uploads['baseurl'] : '';
			$sidecar_normalized = wp_normalize_path($sidecar_file);

			if ($basedir !== '' && $baseurl !== '' && strpos($sidecar_normalized, $basedir) === 0) {
				$relative = ltrim(substr($sidecar_normalized, strlen($basedir)), '/');
				if ($relative !== '') {
					return trailingslashit($baseurl) . str_replace(' ', '%20', $relative);
				}
			}

			$video_url = wp_get_attachment_url($video_id);
			if (!empty($video_url)) {
				return $video_url . '.jpg';
			}
		}
	}

	if ($parent_id > 0) {
		$parent_thumb = escortwp_child_get_post_card_image_url($parent_id, $size);
		if (!empty($parent_thumb)) {
			return $parent_thumb;
		}
	}

	return '';
}

/**
 * Enqueue parent & child styles, plus child custom script.
 */
function escortwp_child_enqueue_assets()
{
	global $taxonomy_location_url;

	// Parent stylesheet
	wp_enqueue_style(
		'escortwp-parent-style',
		get_template_directory_uri() . '/style.css'
	);

	// Child stylesheet (depends on parent)
	wp_enqueue_style(
		'escortwp-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array('escortwp-parent-style'),
		wp_get_theme()->get('Version')
	);

	// Inter font for redesign
	wp_enqueue_style(
		'escortwp-inter-font',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'escortwp-playfair-font',
		'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&display=swap',
		array(),
		null
	);

	// Editorial body font for premium profile copy and review content.
	wp_enqueue_style(
		'escortwp-manrope-font',
		'https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap',
		array(),
		null
	);

	// Merge the parent + child Open Sans requests into a single fetch.
	wp_dequeue_style('open-sans-font');
	wp_deregister_style('open-sans-font');
	wp_enqueue_style(
		'open-sans-font',
		'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap',
		array(),
		null
	);

	// Child custom JavaScript
	$custom_script_file = get_stylesheet_directory() . '/js/custom-script.js';
	wp_enqueue_script(
		'escortwp-child-custom-script',
		get_stylesheet_directory_uri() . '/js/custom-script.js',
		array('jquery'),
		file_exists($custom_script_file) ? filemtime($custom_script_file) : '1.0.0',
		true
	);

	// Filter chips (homepage) config for AJAX
	wp_localize_script(
		'escortwp-child-custom-script',
		'escortwpFilterChips',
		array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('escortwp_filter_home_sections'),
			'emptyText' => __('No matches for this filter.', 'escortwp'),
			'pageTemplate' => escortwp_child_current_template_slug(),
			'fallbackImage' => trailingslashit(get_template_directory_uri()) . 'i/no-image.png',
			'copy' => array(
				'onlineEmptyTitle' => __('Quiet right now', 'escortwp'),
				'onlineEmptyBody' => __('No escorts are live at the moment. Want a wider pulse check or someone nearby?', 'escortwp'),
				'recentEmptyTitle' => __('Still a little quiet', 'escortwp'),
				'recentEmptyBody' => __('No escorts have checked in over the past 24 hours. Try a nearby match instead.', 'escortwp'),
				'recent24Cta' => __('Past 24 Hours', 'escortwp'),
				'liveNowCta' => __('Live now only', 'escortwp'),
				'nearbyCta' => __('Escorts Nearby', 'escortwp'),
				'nearbyHint' => __('Use your location or pick a city to keep browsing.', 'escortwp'),
				'recent24Label' => __('Past 24 Hours', 'escortwp'),
			),
			)
		);

	wp_localize_script(
		'escortwp-child-custom-script',
		'escortwpGeoLocation',
		array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('escortwp_resolve_location_term'),
			'sessionKey' => 'escortwp_geo_location_term',
			'isFrontPage' => is_front_page() ? 1 : 0,
			'locationTaxonomy' => !empty($taxonomy_location_url) ? (string) $taxonomy_location_url : 'escorts-from',
				'copy' => array(
					'idle' => __('Use my location', 'escortwp'),
					'loading' => __('Detecting your location…', 'escortwp'),
					'unsupported' => __('Location is not supported on this browser.', 'escortwp'),
					'insecure' => __('Location needs HTTPS or localhost. This local HTTP URL cannot request it.', 'escortwp'),
					'denied' => __('Location access was denied. Choose a county or city manually.', 'escortwp'),
					'noMatch' => __('We could not match your location yet. Choose a county or city manually.', 'escortwp'),
					'networkError' => __('We could not resolve your location right now. Try again in a moment.', 'escortwp'),
					'applied' => __('Showing escorts near %s', 'escortwp'),
			),
		)
	);
}
add_action('wp_enqueue_scripts', 'escortwp_child_enqueue_assets');

/**
 * Convert the legacy rating CSS value (for example "45") into a display score (4.5).
 */
function escortwp_child_rating_display_value($legacy_rating)
{
	$legacy_rating = trim((string) $legacy_rating);
	if ($legacy_rating === '' || $legacy_rating === '0') {
		return 0.0;
	}

	if (strpos($legacy_rating, '.') !== false) {
		return (float) $legacy_rating;
	}

	if (strlen($legacy_rating) > 1) {
		return ((float) $legacy_rating) / 10;
	}

	return (float) $legacy_rating;
}

/**
 * Translate a numeric rating into a short premium-friendly status label.
 */
function escortwp_child_rating_tone($rating_value, $review_count = 0)
{
	$rating_value = (float) $rating_value;
	$review_count = (int) $review_count;

	if ($review_count < 1 || $rating_value <= 0) {
		return __('New profile', 'escortwp');
	}

	if ($rating_value >= 4.5) {
		return __('Exceptional', 'escortwp');
	}

	if ($rating_value >= 4.0) {
		return __('Highly rated', 'escortwp');
	}

	if ($rating_value >= 3.0) {
		return __('Well reviewed', 'escortwp');
	}

	if ($rating_value >= 2.0) {
		return __('Mixed feedback', 'escortwp');
	}

	return __('Needs improvement', 'escortwp');
}

/**
 * Normalize a location string so geocoder results can be matched to taxonomy terms.
 */
function escortwp_child_normalize_location_label($label)
{
	$label = wp_strip_all_tags((string) $label);
	$label = remove_accents($label);
	$label = strtolower($label);
	$label = str_replace(array('&', '/'), ' ', $label);
	$label = preg_replace('/[^a-z0-9\s-]/', ' ', $label);
	$label = preg_replace('/\s+/', ' ', (string) $label);
	return trim((string) $label);
}

/**
 * Build a cached lookup of location terms grouped by normalized label.
 *
 * @return array{by_label:array<string,array<int,array{term:WP_Term,depth:int}>>,by_slug:array<string,array<int,array{term:WP_Term,depth:int}>>}
 */
function escortwp_child_get_location_term_lookup($taxonomy)
{
	static $lookup_cache = array();

	$taxonomy = sanitize_key((string) $taxonomy);
	if ($taxonomy === '') {
		$taxonomy = 'escorts-from';
	}

	if (isset($lookup_cache[$taxonomy])) {
		return $lookup_cache[$taxonomy];
	}

	$terms = get_terms(array(
		'taxonomy' => $taxonomy,
		'hide_empty' => false,
	));

	$lookup = array(
		'by_label' => array(),
		'by_slug' => array(),
	);

	if (is_wp_error($terms) || empty($terms)) {
		$lookup_cache[$taxonomy] = $lookup;
		return $lookup;
	}

	$depth_cache = array();
	$get_depth = static function ($term_id) use ($taxonomy, &$depth_cache) {
		$term_id = (int) $term_id;
		if (isset($depth_cache[$term_id])) {
			return $depth_cache[$term_id];
		}

		$depth = 0;
		$current = get_term($term_id, $taxonomy);
		while ($current && !is_wp_error($current) && !empty($current->parent)) {
			$depth++;
			$current = get_term((int) $current->parent, $taxonomy);
		}

		$depth_cache[$term_id] = $depth;
		return $depth;
	};

	foreach ($terms as $term) {
		$depth = $get_depth($term->term_id);
		$entry = array(
			'term' => $term,
			'depth' => $depth,
		);

		$label_key = escortwp_child_normalize_location_label($term->name);
		if ($label_key !== '') {
			if (empty($lookup['by_label'][$label_key])) {
				$lookup['by_label'][$label_key] = array();
			}
			$lookup['by_label'][$label_key][] = $entry;
		}

		$slug_key = escortwp_child_normalize_location_label($term->slug);
		if ($slug_key !== '') {
			if (empty($lookup['by_slug'][$slug_key])) {
				$lookup['by_slug'][$slug_key] = array();
			}
			$lookup['by_slug'][$slug_key][] = $entry;
		}
	}

	$lookup_cache[$taxonomy] = $lookup;
	return $lookup;
}

/**
 * Resolve the most appropriate taxonomy term from a prioritized set of geocoder candidates.
 *
 * @param array<string,array<int,string>> $candidate_groups
 * @return array{term_id:int,term_name:string,archive_url:string,status:string}
 */
function escortwp_child_match_location_term_from_candidates($taxonomy, array $candidate_groups)
{
	$lookup = escortwp_child_get_location_term_lookup($taxonomy);
	$target_depths = array(
		'city' => array(2, 3, 1, 0),
		'county' => array(1, 2, 0, 3),
		'country' => array(0, 1, 2, 3),
	);

	foreach (array('city', 'county', 'country') as $group_key) {
		$candidates = $candidate_groups[$group_key] ?? array();
		if (empty($candidates)) {
			continue;
		}

		foreach ($candidates as $candidate) {
			$normalized = escortwp_child_normalize_location_label($candidate);
			if ($normalized === '') {
				continue;
			}

			$matches = $lookup['by_label'][$normalized] ?? array();
			if (empty($matches)) {
				$matches = $lookup['by_slug'][$normalized] ?? array();
			}

			if (empty($matches)) {
				continue;
			}

			usort($matches, static function ($a, $b) use ($group_key, $target_depths) {
				$a_depth_rank = array_search((int) $a['depth'], $target_depths[$group_key], true);
				$b_depth_rank = array_search((int) $b['depth'], $target_depths[$group_key], true);
				$a_depth_rank = $a_depth_rank === false ? 999 : $a_depth_rank;
				$b_depth_rank = $b_depth_rank === false ? 999 : $b_depth_rank;

				if ($a_depth_rank !== $b_depth_rank) {
					return $a_depth_rank <=> $b_depth_rank;
				}

				return strcasecmp((string) $a['term']->name, (string) $b['term']->name);
			});

			$match = $matches[0]['term'];
			$archive_url = get_term_link($match);
			return array(
				'term_id' => (int) $match->term_id,
				'term_name' => (string) $match->name,
				'archive_url' => !is_wp_error($archive_url) ? (string) $archive_url : '',
				'status' => $group_key === 'city' ? 'ok' : 'fallback',
			);
		}
	}

	return array(
		'term_id' => 0,
		'term_name' => '',
		'archive_url' => '',
		'status' => 'no_match',
	);
}

/**
 * Reverse geocode coordinates and map them to the nearest available location term.
 *
 * @return array{term_id:int,term_name:string,archive_url:string,status:string,taxonomy:string}
 */
function escortwp_child_resolve_location_term($latitude, $longitude, $taxonomy)
{
	$latitude = (float) $latitude;
	$longitude = (float) $longitude;
	$taxonomy = sanitize_key((string) $taxonomy);
	if ($taxonomy === '') {
		$taxonomy = 'escorts-from';
	}

	$cache_key = sprintf(
		'escortwp_geo_%s',
		md5($taxonomy . '|' . number_format($latitude, 3, '.', '') . '|' . number_format($longitude, 3, '.', ''))
	);
	$cached = get_transient($cache_key);
	if (is_array($cached) && !empty($cached['term_id'])) {
		$cached['taxonomy'] = $taxonomy;
		return $cached;
	}

	$request_url = add_query_arg(
		array(
			'format' => 'jsonv2',
			'lat' => $latitude,
			'lon' => $longitude,
			'zoom' => 10,
			'addressdetails' => 1,
		),
		'https://nominatim.openstreetmap.org/reverse'
	);

	$response = wp_remote_get(
		$request_url,
		array(
			'timeout' => 8,
			'headers' => array(
				'Accept' => 'application/json',
				'User-Agent' => sprintf('%s Geolocation Resolver (%s)', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES), home_url('/')),
			),
		)
	);

	if (is_wp_error($response)) {
		return array(
			'term_id' => 0,
			'term_name' => '',
			'archive_url' => '',
			'status' => 'error',
			'taxonomy' => $taxonomy,
		);
	}

	$body = wp_remote_retrieve_body($response);
	$data = json_decode($body, true);
	$address = is_array($data) && !empty($data['address']) && is_array($data['address']) ? $data['address'] : array();

	$candidate_groups = array(
		'city' => array_values(array_filter(array(
			$address['city'] ?? '',
			$address['town'] ?? '',
			$address['municipality'] ?? '',
			$address['village'] ?? '',
			$address['suburb'] ?? '',
			$address['city_district'] ?? '',
		))),
		'county' => array_values(array_filter(array(
			$address['county'] ?? '',
			$address['state_district'] ?? '',
			$address['region'] ?? '',
			$address['state'] ?? '',
		))),
		'country' => array_values(array_filter(array(
			$address['country'] ?? '',
		))),
	);

	$resolved = escortwp_child_match_location_term_from_candidates($taxonomy, $candidate_groups);
	$resolved['taxonomy'] = $taxonomy;

	if (!empty($resolved['term_id'])) {
		set_transient($cache_key, $resolved, DAY_IN_SECONDS);
	}

	return $resolved;
}

/**
 * Build consistent metadata attributes for location links.
 */
function escortwp_child_get_location_link_data_attributes($term, $archive_url = '')
{
	if (!$term instanceof WP_Term) {
		return '';
	}

	if ($archive_url === '') {
		$archive_url = get_term_link($term);
	}

	if (is_wp_error($archive_url) || $archive_url === '') {
		return '';
	}

	$attributes = array(
		'data-location-link' => '1',
		'data-location-term-id' => (string) $term->term_id,
		'data-location-name' => (string) $term->name,
		'data-location-taxonomy' => (string) $term->taxonomy,
		'data-location-archive-url' => (string) $archive_url,
	);

	$html = '';
	foreach ($attributes as $name => $value) {
		$html .= sprintf(' %s="%s"', esc_attr($name), esc_attr($value));
	}

	return $html;
}

/**
 * Recursively render the location tree with metadata needed for homepage filtering.
 *
 * @param array<int,array<int,WP_Term>> $terms_by_parent
 * @param array<int,int> $current_ancestor_ids
 */
function escortwp_child_render_location_tree_items(array $terms_by_parent, $parent_id, $taxonomy, $current_term_id, array $current_ancestor_ids)
{
	$parent_id = (int) $parent_id;
	if (empty($terms_by_parent[$parent_id])) {
		return '';
	}

	$output = '';
	foreach ($terms_by_parent[$parent_id] as $term) {
		$archive_url = get_term_link($term);
		if (is_wp_error($archive_url)) {
			continue;
		}

		$classes = array(
			'cat-item',
			'cat-item-' . (int) $term->term_id,
		);

		if ((int) $term->term_id === (int) $current_term_id) {
			$classes[] = 'current-cat';
		} elseif (in_array((int) $term->term_id, $current_ancestor_ids, true)) {
			$classes[] = 'current-cat-parent';
		}

		$output .= sprintf('<li class="%s">', esc_attr(implode(' ', $classes)));
		$output .= sprintf(
			'<a class="location-item__link" href="%1$s" title="%2$s"%3$s>%4$s</a>',
			esc_url($archive_url),
			esc_attr($term->name),
			escortwp_child_get_location_link_data_attributes($term, (string) $archive_url),
			esc_html($term->name)
		);

		$children_html = escortwp_child_render_location_tree_items(
			$terms_by_parent,
			(int) $term->term_id,
			$taxonomy,
			$current_term_id,
			$current_ancestor_ids
		);

		if ($children_html !== '') {
			$output .= '<ul class="children">' . $children_html . '</ul>';
		}

		$output .= '</li>';
	}

	return $output;
}

/**
 * Render location list markup used by sidebar-left for both desktop and mobile.
 */
function escortwp_child_render_location_tree($taxonomy)
{
	$taxonomy = sanitize_key((string) $taxonomy);
	if ($taxonomy === '') {
		$taxonomy = 'escorts-from';
	}

	$terms = get_terms(array(
		'taxonomy' => $taxonomy,
		'hide_empty' => false,
		'orderby' => 'name',
		'order' => 'ASC',
	));

	if (is_wp_error($terms) || empty($terms)) {
		return '';
	}

	$terms_by_parent = array();
	foreach ($terms as $term) {
		$parent_id = (int) $term->parent;
		if (empty($terms_by_parent[$parent_id])) {
			$terms_by_parent[$parent_id] = array();
		}
		$terms_by_parent[$parent_id][] = $term;
	}

	$current_term_id = 0;
	$current_ancestor_ids = array();
	if (is_tax($taxonomy)) {
		$current_term = get_queried_object();
		if ($current_term instanceof WP_Term && $current_term->taxonomy === $taxonomy) {
			$current_term_id = (int) $current_term->term_id;
			$current_ancestor_ids = array_map('intval', get_ancestors($current_term_id, $taxonomy, 'taxonomy'));
		}
	}

	return escortwp_child_render_location_tree_items(
		$terms_by_parent,
		0,
		$taxonomy,
		$current_term_id,
		$current_ancestor_ids
	);
}

/**
 * Dequeue duplicate child CSS loaded by parent's add_js_css().
 * Parent line 58: wp_enqueue_style('main-css-file', get_bloginfo('stylesheet_url'))
 * This loads child style.css a second time. Run at priority 99 (AFTER parent's add_js_css).
 */
function escortwp_child_dequeue_duplicate_css()
{
	wp_dequeue_style('main-css-file');
}
add_action('wp_enqueue_scripts', 'escortwp_child_dequeue_duplicate_css', 99);

/**
 * Keep UploadiFive only on the legacy upload/edit surfaces that initialize it.
 */
function escortwp_child_should_load_uploadifive()
{
	global $taxonomy_profile_url, $taxonomy_agency_url;

	$page_ids = array_filter(array_map('absint', array(
		get_option('escort_verified_status_page_id'),
		get_option('agency_upload_logo_page_id'),
		get_option('site_settings_page_id'),
		get_option('content_settings_page_id'),
	)));

	if (!empty($page_ids) && is_page($page_ids)) {
		return true;
	}

	$profile_post_type = $taxonomy_profile_url ? $taxonomy_profile_url : (string) get_option('taxonomy_profile_url');
	$agency_post_type = $taxonomy_agency_url ? $taxonomy_agency_url : (string) get_option('taxonomy_agency_url');
	$post_types = array_filter(array(
		$profile_post_type,
		$profile_post_type ? 'b' . $profile_post_type : '',
		$agency_post_type,
		'ad',
	));

	return !empty($post_types) && is_singular($post_types);
}

function escortwp_child_conditionally_dequeue_uploadifive()
{
	if (escortwp_child_should_load_uploadifive()) {
		return;
	}

	wp_dequeue_script('jquery-uploadifive');
}
add_action('wp_enqueue_scripts', 'escortwp_child_conditionally_dequeue_uploadifive', 99);

/**
 * The legacy theme enqueues jquery.mobile.custom globally, but the active theme
 * codebase does not bind any of its swipe/tap/vmouse events.
 */
function escortwp_child_dequeue_unused_mobile_custom()
{
	wp_dequeue_script('jquery-mobile-custom');
}
add_action('wp_enqueue_scripts', 'escortwp_child_dequeue_unused_mobile_custom', 99);

/**
 * Defer the homepage Webpushr bootstrap until idle/load or first interaction.
 * This preserves the same setup arguments while moving the SDK off the critical path.
 */
function escortwp_child_should_defer_front_page_webpushr()
{
	return !is_admin() && is_front_page() && function_exists('insert_webpushr_script');
}

function escortwp_child_disable_immediate_front_page_webpushr()
{
	if (!escortwp_child_should_defer_front_page_webpushr()) {
		return;
	}

	remove_action('wp_footer', 'insert_webpushr_script', 1000);
}
add_action('wp', 'escortwp_child_disable_immediate_front_page_webpushr', 20);

function escortwp_child_get_webpushr_setup_args()
{
	$webpushr_integration = get_option('wpp_disable_prompt_code');
	if ($webpushr_integration && is_array($webpushr_integration) && ($webpushr_integration['disable_integration'] ?? '') !== 'false') {
		return null;
	}

	$public_key = (string) get_option('webpushr_public_key');
	if ($public_key === '') {
		return null;
	}

	$args = array('key' => $public_key);
	$sw_path = is_array($webpushr_integration) ? (string) ($webpushr_integration['sw_path'] ?? '') : '';

	global $webpushr_active_plugins;
	$active_plugins = is_array($webpushr_active_plugins) ? $webpushr_active_plugins : (array) get_option('active_plugins', array());

	if ($sw_path === 'root') {
		return $args;
	}

	$has_pwa_plugin = in_array('super-progressive-web-apps/superpwa.php', $active_plugins, true)
		|| in_array('pwa/pwa.php', $active_plugins, true)
		|| in_array('pwa-for-wp/pwa-for-wp.php', $active_plugins, true);

	if ($has_pwa_plugin) {
		$args['sw'] = 'none';
		return $args;
	}

	$args['sw'] = '/wp-content/plugins/webpushr-web-push-notifications/sdk_files/webpushr-sw.js.php';
	return $args;
}

function escortwp_child_print_deferred_front_page_webpushr()
{
	if (!escortwp_child_should_defer_front_page_webpushr()) {
		return;
	}

	$setup_args = escortwp_child_get_webpushr_setup_args();
	if (empty($setup_args)) {
		return;
	}
	?>
	<script id="webpushr-script">
	(function (w, d) {
		var setupArgs = <?php echo wp_json_encode($setup_args); ?>;
		var sdkUrl = <?php echo wp_json_encode('https://cdn.webpushr.com/app.min.js'); ?>;
		var booted = false;

		function boot() {
			if (booted) {
				return;
			}

			booted = true;
			w.webpushr = w.webpushr || function () {
				(w.webpushr.q = w.webpushr.q || []).push(arguments);
			};

			if (!d.getElementById('webpushr-jssdk')) {
				var script = d.createElement('script');
				script.async = true;
				script.id = 'webpushr-jssdk';
				script.src = sdkUrl;
				(d.body || d.documentElement).appendChild(script);
			}

			w.webpushr('setup', setupArgs);
		}

		if ('requestIdleCallback' in w) {
			w.requestIdleCallback(boot, { timeout: 3000 });
		} else {
			w.addEventListener('load', function () {
				w.setTimeout(boot, 1200);
			}, { once: true });
		}

		['pointerdown', 'keydown', 'touchstart'].forEach(function (eventName) {
			w.addEventListener(eventName, boot, { once: true, passive: true });
		});
	})(window, document);
	</script>
	<?php
}
add_action('wp_footer', 'escortwp_child_print_deferred_front_page_webpushr', 1000);

/**
 * Enqueue override.css LAST with cache busting via filemtime().
 * Depends on child style + responsive so it loads after both.
 */
function escortwp_child_enqueue_override_css()
{
	$override_file = get_stylesheet_directory() . '/css/override.css';
	wp_enqueue_style(
		'escortwp-override-css',
		get_stylesheet_directory_uri() . '/css/override.css',
		array('escortwp-child-style', 'responsive'),
		file_exists($override_file) ? filemtime($override_file) : '1.0.0'
	);
}
add_action('wp_enqueue_scripts', 'escortwp_child_enqueue_override_css', 100);

/**
 * Enqueue auth.css only on registration pages.
 */
function escortwp_child_enqueue_auth_css()
{
	$auth_file = get_stylesheet_directory() . '/css/auth.css';
	$auth_pages = array_filter(array(
		get_option('main_reg_page_id'),
		get_option('escort_reg_page_id'),
		get_option('agency_reg_page_id'),
		get_option('member_register_page_id'),
		get_option('agency_manage_escorts_page_id'),
	));

	if (is_page($auth_pages) || is_page_template('register-main-page.php')) {
		wp_enqueue_style(
			'escortwp-auth-css',
			get_stylesheet_directory_uri() . '/css/auth.css',
			array('escortwp-override-css'),
			file_exists($auth_file) ? filemtime($auth_file) : '1.0.0'
		);
	}
}
add_action('wp_enqueue_scripts', 'escortwp_child_enqueue_auth_css', 110);

/**
 * Enqueue auth styles on wp-login.php.
 */
function escortwp_child_enqueue_login_css()
{
	$override_file = get_stylesheet_directory() . '/css/override.css';
	$auth_file = get_stylesheet_directory() . '/css/auth.css';

	wp_enqueue_style(
		'escortwp-inter-font',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'escortwp-override-css',
		get_stylesheet_directory_uri() . '/css/override.css',
		array(),
		file_exists($override_file) ? filemtime($override_file) : '1.0.0'
	);

	wp_enqueue_style(
		'escortwp-auth-css-login',
		get_stylesheet_directory_uri() . '/css/auth.css',
		array('escortwp-override-css'),
		file_exists($auth_file) ? filemtime($auth_file) : '1.0.0'
	);

	$login_logo_url = trim((string) get_option('sitelogo'));
	if ($login_logo_url === '') {
		$login_logo_url = get_template_directory_uri() . '/i/logo.png';
	}

	if ($login_logo_url !== '') {
		$inline_login_css = sprintf(
			"body.login h1 a{background-image:url('%s') !important;}",
			esc_url_raw($login_logo_url)
		);
		wp_add_inline_style('escortwp-auth-css-login', $inline_login_css);
	}
}
add_action('login_enqueue_scripts', 'escortwp_child_enqueue_login_css');

/**
 * Register additional widget areas.
 */
function escortwp_child_register_widgets()
{
	register_sidebar(array(
		'name' => __('Footer - Home Only', 'escortwp'),
		'id' => 'footer-home-only',
		'before_widget' => '<div id="%1$s" class="widgetbox rad3 widget %2$s l">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __('Header Ads', 'escortwp'),
		'id' => 'sidebar-id-header-ads',
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '',
		'after_title' => '',
	));

	register_sidebar(array(
		'name' => __('Box Ads', 'escortwp'),
		'id' => 'box-ads',
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '',
		'after_title' => '',
	));
}
add_action('widgets_init', 'escortwp_child_register_widgets');

/**
 * Keep footer headings sequential without changing footer widget behavior.
 */
function escortwp_child_reregister_footer_sidebar()
{
	unregister_sidebar('widget-footer');

	register_sidebar(array(
		'name' => __('Footer', 'escortwp'),
		'id' => 'widget-footer',
		'before_widget' => '<div id="%1$s" class="widgetbox rad3 widget %2$s l">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>',
	));
}
add_action('widgets_init', 'escortwp_child_reregister_footer_sidebar', 20);

/**
 * Dynamic per-user options should not be autoloaded on every request.
 */
function escortwp_child_is_dynamic_user_option_name($option_name)
{
	return (bool) preg_match('/^(escortid|escortpostid|agencypostid)\d+$/', (string) $option_name);
}

function escortwp_child_update_dynamic_user_option($option_name, $value)
{
	if (!escortwp_child_is_dynamic_user_option_name($option_name)) {
		return update_option($option_name, $value);
	}

	return update_option($option_name, $value, false);
}

function escortwp_child_run_dynamic_user_option_autoload_migration()
{
	global $wpdb;

	$option_key = 'escortwp_child_dynamic_user_option_autoload_migration_v1';
	if (get_option($option_key, false)) {
		return;
	}

	$table = $wpdb->options;
	$rows_updated = $wpdb->query(
		"UPDATE {$table}
		SET autoload = 'no'
		WHERE option_name REGEXP '^(escortid|escortpostid|agencypostid)[0-9]+$'
			AND autoload NOT IN ('no', 'off', 'auto-off')"
	);

	update_option(
		$option_key,
		array(
			'ran_at' => time(),
			'rows_updated' => max(0, (int) $rows_updated),
		),
		false
	);
}

function escortwp_child_maybe_run_dynamic_user_option_autoload_migration()
{
	if (!is_admin() && !(defined('WP_CLI') && WP_CLI)) {
		return;
	}

	escortwp_child_run_dynamic_user_option_autoload_migration();
}
add_action('admin_init', 'escortwp_child_maybe_run_dynamic_user_option_autoload_migration', 5);
add_action('init', 'escortwp_child_maybe_run_dynamic_user_option_autoload_migration', 5);

/**
 * Instead of redeclaring get_escort_labels(), use gettext to swap VERIFIED → REAL PIC
 */
add_filter('gettext', function ($translated, $original, $domain) {
	if ('VERIFIED' === $original && 'escortwp' === $domain) {
		return 'REAL PIC';
	}
	return $translated;
}, 20, 3);

// Yoast Sitemap override
function force_empty_escort_categories_in_sitemap($terms, $taxonomy)
{
	if ('escorts-from' === $taxonomy) {
		$all_terms = get_terms(array(
			'taxonomy' => 'escorts-from',
			'hide_empty' => false, // include even empty
		));
		if (!is_wp_error($all_terms)) {
			return $all_terms;
		}
	}
	return $terms;
}
add_filter('wpseo_sitemap_exclude_empty_terms', '__return_false'); // don’t drop empty terms
add_filter('wpseo_sitemap_entries_per_page', function () {
	return 5000; });
add_filter('wpseo_get_terms', 'force_empty_escort_categories_in_sitemap', 10, 2);

// Escort & Uploads 404 Redirects
add_action('template_redirect', function () {
	if (is_404()) {
		$request_uri = $_SERVER['REQUEST_URI'];
		if (preg_match('#^/escort/.*$#', $request_uri) || preg_match('#^/uploads/.*$#', $request_uri)) {
			wp_redirect(home_url(), 301);
			exit;
		}
	}
});

// Trigger update_counts.php on certain profile-visibility changes
add_action('init', 'trigger_update_counts_on_profile_visibility_change');
function trigger_update_counts_on_profile_visibility_change()
{
	if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($_POST['action'])) {
		$action = $_POST['action'];
		if (in_array($action, array('activateprivateprofile', 'settoprivate'), true)) {
			$script_path = '/home/exotickenya/update_counts.php'; // adjust path as needed
			if (file_exists($script_path)) {
				include $script_path;
				error_log("update_counts.php triggered via action: {$action}");
			} else {
				error_log("update_counts.php not found at {$script_path}");
			}
		}
	}
}

add_action('wp_footer', 'pass_user_id_to_js');
function pass_user_id_to_js()
{
	if (is_user_logged_in()) {
		$current_user = wp_get_current_user();
		?>
		<script type="text/javascript">
			var escortwp_user_id = <?php echo json_encode($current_user->ID); ?>;
		</script>
		<?php
	}
}

add_action('wp_enqueue_scripts', 'load_sweetalert2_script');
function load_sweetalert2_script()
{
	wp_enqueue_script(
		'sweetalert2',
		'https://cdn.jsdelivr.net/npm/sweetalert2@11',
		array(), // no dependencies
		null,
		true     // load in footer
	);
}

/**
 * Resolve logged-in escort profile context for quick-access UI.
 */
function escortwp_child_get_logged_in_escort_profile_context($user_id = 0)
{
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	if ($user_id < 1) {
		return array(
			'is_escort' => false,
			'has_profile' => false,
			'profile_id' => 0,
			'profile_url' => '',
			'status_key' => '',
			'status_label' => '',
		);
	}

	global $taxonomy_profile_url;
	$profile_type_slug = !empty($taxonomy_profile_url) ? (string) $taxonomy_profile_url : 'escort';

	$is_escort = (string) get_option('escortid' . $user_id) === $profile_type_slug;
	$profile_id = (int) get_option('escortpostid' . $user_id);

	if ($profile_id < 1 || get_post_type($profile_id) !== $profile_type_slug) {
		$escort_posts = get_posts(array(
			'post_type' => $profile_type_slug,
			'author' => $user_id,
			'posts_per_page' => 1,
			'post_status' => array('publish', 'private', 'draft', 'pending'),
			'fields' => 'ids',
			'orderby' => 'date',
			'order' => 'DESC',
		));
		if (!empty($escort_posts)) {
			$profile_id = (int) $escort_posts[0];
		}
	}

	if ($profile_id < 1) {
		return array(
			'is_escort' => $is_escort,
			'has_profile' => false,
			'profile_id' => 0,
			'profile_url' => '',
			'status_key' => 'missing',
			'status_label' => __('No profile', 'escortwp'),
		);
	}

	$post_status = (string) get_post_status($profile_id);
	$needs_payment = (bool) get_post_meta($profile_id, 'needs_payment', true);
	$notactive = (bool) get_post_meta($profile_id, 'notactive', true);
	$status_key = 'active';
	$status_label = __('Active', 'escortwp');

	if ($needs_payment) {
		$status_key = 'payment';
		$status_label = __('Payment required', 'escortwp');
	} elseif ($post_status === 'private' && $notactive) {
		$status_key = 'paused';
		$status_label = __('Paused', 'escortwp');
	} elseif ($post_status === 'private') {
		$status_key = 'private';
		$status_label = __('Private', 'escortwp');
	} elseif ($post_status === 'draft' || $post_status === 'pending') {
		$status_key = 'draft';
		$status_label = __('Draft', 'escortwp');
	}

	return array(
		'is_escort' => $is_escort,
		'has_profile' => true,
		'profile_id' => $profile_id,
		'profile_url' => get_permalink($profile_id),
		'status_key' => $status_key,
		'status_label' => $status_label,
	);
}

/**
 * Determine if the left sidebar should switch to admin-home shortcuts.
 */
function escortwp_child_is_admin_home_sidebar_context()
{
	if (!is_user_logged_in() || !current_user_can('level_10')) {
		return false;
	}

	return is_front_page() || is_home();
}

/**
 * Build quick admin links for home sidebar replacement.
 */
function escortwp_child_get_admin_home_quick_links()
{
	global $taxonomy_location_url;

	$get_page_url = static function ($option_key) {
		$page_id = (int) get_option($option_key);
		if ($page_id < 1) {
			return '';
		}

		$url = get_permalink($page_id);
		return is_string($url) ? $url : '';
	};

	$links = array();
	$push_link = static function (&$target, $label, $url, $icon = 'icon-right-open-mini') {
		$url = trim((string) $url);
		if ($url === '') {
			return;
		}

		$target[] = array(
			'label' => (string) $label,
			'url' => $url,
			'icon' => (string) $icon,
		);
	};

	$push_link($links, sprintf(__('Add %s', 'escortwp'), ucwords((string) get_option('taxonomy_profile_name', 'Escort'))), $get_page_url('escort_reg_page_id'), 'icon-user');
	$push_link($links, sprintf(__('Add %s', 'escortwp'), ucwords((string) get_option('taxonomy_agency_name', 'Agency'))), $get_page_url('agency_reg_page_id'), 'icon-users');

	if ((string) get_option('hide6') !== '1') {
		$push_link($links, __('Classified Ads', 'escortwp'), $get_page_url('manage_ads_page_id'), 'icon-doc-text');
	}
	if ((string) get_option('hide5') !== '1') {
		$push_link($links, __('Blacklisted Clients', 'escortwp'), $get_page_url('escort_blacklist_clients_page_id'), 'icon-block');
	}
	if ((string) get_option('hide4') !== '1') {
		$push_link($links, __('Blacklisted Escorts', 'escortwp'), $get_page_url('blacklisted_escorts_page_id'), 'icon-block');
	}

	$push_link($links, __('Site Settings', 'escortwp'), $get_page_url('site_settings_page_id'), 'icon-cog-alt');
	$push_link($links, __('Content Settings', 'escortwp'), $get_page_url('content_settings_page_id'), 'icon-cog-alt');
	$push_link($links, __('Registration Form', 'escortwp'), $get_page_url('edit_registration_form_escort'), 'icon-cog-alt');
	$push_link($links, __('Payment Settings', 'escortwp'), $get_page_url('edit_payment_settings_page_id'), 'icon-dollar');
	$push_link($links, __('Email Settings', 'escortwp'), $get_page_url('email_settings_page_id'), 'icon-mail');
	$push_link($links, __('Edit User Types', 'escortwp'), $get_page_url('edit_user_types'), 'icon-user');

	$location_tax = !empty($taxonomy_location_url) ? (string) $taxonomy_location_url : 'escorts-from';
	$push_link($links, __('Add Countries', 'escortwp'), admin_url('edit-tags.php?taxonomy=' . rawurlencode($location_tax)), 'icon-plus-circled');

	$push_link($links, __('Generate Demo Data', 'escortwp'), $get_page_url('generate_demo_data_page'), 'icon-plus-circled');
	$push_link($links, __('WordPress Dashboard', 'escortwp'), admin_url(), 'icon-w');
	$push_link($links, __('Log Out', 'escortwp'), wp_logout_url(home_url('/')), 'icon-logout');

	/**
	 * Filters admin home quick links used in left sidebar replacement.
	 *
	 * @param array<int,array{label:string,url:string,icon:string}> $links
	 */
	return apply_filters('escortwp_child_admin_home_quick_links', $links);
}

// Recently Viewed: return cards HTML for given IDs (works for logged-out users too)
add_action('wp_ajax_nopriv_escortwp_recently_viewed', 'escortwp_recently_viewed_cards');
add_action('wp_ajax_escortwp_recently_viewed', 'escortwp_recently_viewed_cards');

function escortwp_recently_viewed_cards()
{
	if (empty($_POST['ids'])) {
		wp_die();
	}

	$ids_raw = preg_split('/\s*,\s*/', (string) $_POST['ids']);
	$ids = array_values(array_unique(array_filter(array_map('intval', $ids_raw))));
	if (empty($ids)) {
		wp_die();
	}

	// IMPORTANT: adjust this if your CPT slug differs
	global $taxonomy_profile_url;
	if (empty($taxonomy_profile_url)) {
		$taxonomy_profile_url = 'escort'; // fallback slug if global not set
	}

	$q = new WP_Query(array(
		'post_type' => $taxonomy_profile_url,
		'post__in' => $ids,
		'orderby' => 'post__in',
		'posts_per_page' => count($ids),
		'post_status' => 'publish',
		'no_found_rows' => true,
	));

	ob_start();
	if ($q->have_posts()) {
		while ($q->have_posts()) {
			$q->the_post();
			// Reuse your existing profile card template
			include get_template_directory() . '-child/loop-show-profile.php';
		}
	}
	wp_reset_postdata();
	echo ob_get_clean();
	wp_die();
}

// Homepage filter chips (VIP + Premium + Newly Added)
add_action('wp_ajax_nopriv_escortwp_filter_home_sections', 'escortwp_filter_home_sections');
add_action('wp_ajax_escortwp_filter_home_sections', 'escortwp_filter_home_sections');
add_action('wp_ajax_nopriv_escortwp_resolve_location_term', 'escortwp_resolve_location_term');
add_action('wp_ajax_escortwp_resolve_location_term', 'escortwp_resolve_location_term');

function escortwp_build_chip_meta_query($filter_type, $filter_value)
{
	switch ($filter_type) {
		case 'service':
			if ($filter_value > 0) {
				return array(
					'relation' => 'OR',
					array(
						'key' => 'services',
						'value' => '%"' . $filter_value . '"%',
						'compare' => 'LIKE',
					),
					array(
						'key' => 'services',
						'value' => 'i:' . $filter_value . ';',
						'compare' => 'LIKE',
					),
				);
			}
			break;
		case 'gender':
			if ($filter_value > 0) {
				return array(
					'key' => 'gender',
					'value' => $filter_value,
					'compare' => '=',
					'type' => 'NUMERIC',
				);
			}
			break;
		case 'looks':
			if ($filter_value > 0) {
				if ($filter_value === 3) {
					return array(
						'relation' => 'OR',
						array(
							'key' => 'looks',
							'value' => 3,
							'compare' => '=',
							'type' => 'NUMERIC',
						),
						array(
							'key' => 'looks',
							'value' => 4,
							'compare' => '=',
							'type' => 'NUMERIC',
						),
					);
				}

				return array(
					'key' => 'looks',
					'value' => $filter_value,
					'compare' => '=',
					'type' => 'NUMERIC',
				);
			}
			break;
		case 'premium':
			return array(
				'key' => 'premium',
				'value' => '1',
				'compare' => '=',
				'type' => 'NUMERIC',
			);
		case 'vip':
			return array(
				'key' => 'featured',
				'value' => '1',
				'compare' => '=',
				'type' => 'NUMERIC',
			);
		case 'verified':
			return array(
				'key' => 'verified',
				'value' => '1',
				'compare' => '=',
				'type' => 'NUMERIC',
			);
		case 'build':
			if ($filter_value > 0) {
				return array(
					'key' => 'build',
					'value' => $filter_value,
					'compare' => '=',
					'type' => 'NUMERIC',
				);
			}
			break;
		default:
			break;
	}

	return null;
}

function escortwp_get_home_filter_label($filter_type, $filter_value)
{
	$filter_type = sanitize_key((string) $filter_type);
	$filter_value = absint($filter_value);

	$static_labels = array(
		'all' => __('All', 'escortwp'),
		'verified' => __('Verified', 'escortwp'),
		'premium' => __('Premium', 'escortwp'),
		'vip' => __('VIP', 'escortwp'),
		'online' => __('Online', 'escortwp'),
		'recent_24h' => __('Past 24 Hours', 'escortwp'),
		'new' => __('New', 'escortwp'),
	);

	if (isset($static_labels[$filter_type])) {
		return $static_labels[$filter_type];
	}

	$value_labels = array(
		'service' => array(
			1 => __('BDSM', 'escortwp'),
			2 => __('Couples', 'escortwp'),
			3 => __('Domination', 'escortwp'),
			5 => __('Massage', 'escortwp'),
			6 => __('Fetish', 'escortwp'),
		),
		'build' => array(
			4 => __('Curvy', 'escortwp'),
		),
		'gender' => array(
			4 => __('Gay', 'escortwp'),
		),
		'looks' => array(
			3 => __('Sexy', 'escortwp'),
		),
	);

	if (!empty($value_labels[$filter_type][$filter_value])) {
		return $value_labels[$filter_type][$filter_value];
	}

	return ucfirst(str_replace('_', ' ', $filter_type));
}

function escortwp_render_profile_cards($query, $empty_text)
{
	$GLOBALS['PROFILE_GRID_NO_SEPARATORS'] = true;
	$i = 1;

	ob_start();
	if ($query && $query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			include get_theme_file_path('/loop-show-profile.php');
		}
	} else {
		echo '<div class="escort-grid__empty">' . esc_html($empty_text) . '</div>';
	}
	wp_reset_postdata();

	return ob_get_clean();
}

function escortwp_filter_home_sections()
{
	check_ajax_referer('escortwp_filter_home_sections', 'nonce');

	global $taxonomy_profile_url, $taxonomy_location_url;
	if (empty($taxonomy_profile_url)) {
		$taxonomy_profile_url = 'escort';
	}
	if (empty($taxonomy_location_url)) {
		$taxonomy_location_url = 'escorts-from';
	}

	$filter_type = isset($_POST['filter_type']) ? sanitize_key(wp_unslash($_POST['filter_type'])) : 'all';
	$filter_value = isset($_POST['filter_value']) ? absint($_POST['filter_value']) : 0;
	$allowed_types = array('all', 'service', 'verified', 'build', 'gender', 'looks', 'premium', 'vip', 'online', 'recent_24h', 'new');
	if (!in_array($filter_type, $allowed_types, true)) {
		$filter_type = 'all';
	}

	$taxonomy = isset($_POST['taxonomy']) ? sanitize_key(wp_unslash($_POST['taxonomy'])) : '';
	$term_id = isset($_POST['term_id']) ? absint($_POST['term_id']) : 0;
	$tax_query = array();
	$resolved_location_term = null;
	if ($taxonomy === $taxonomy_location_url && $term_id > 0) {
		$term = get_term($term_id, $taxonomy);
		if ($term && !is_wp_error($term)) {
			$resolved_location_term = $term;
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field' => 'term_id',
				'terms' => $term_id,
			);
		}
	}

	$chip_meta = ($filter_type === 'all') ? null : escortwp_build_chip_meta_query($filter_type, $filter_value);
	$empty_text = __('No matches for this filter.', 'escortwp');

	$date_query = array();
	if ($filter_type === 'new') {
		$newlabelperiod = (int) get_option('newlabelperiod');
		if ($newlabelperiod < 1) {
			$newlabelperiod = 7;
		}
		$date_query[] = array(
			'after' => date('Y-m-d H:i:s', strtotime('-' . $newlabelperiod . ' days')),
			'inclusive' => true,
		);
	}

	$online_users = array();
	$apply_online_filter = false;
	$online_fallback = false;
	if ($filter_type === 'online') {
		$online_windows = array(180, 1440);
		foreach ($online_windows as $index => $minutes) {
			$online_users = (new WP_User_Query(array(
				'meta_key' => 'last_online',
				'meta_value' => current_time('timestamp') - 60 * $minutes,
				'meta_compare' => '>=',
				'fields' => 'ids',
			)))->get_results();
			if (!empty($online_users)) {
				$apply_online_filter = true;
				if ($index > 0) {
					$online_fallback = true;
				}
				break;
			}
		}
	} elseif ($filter_type === 'recent_24h') {
		$online_users = (new WP_User_Query(array(
			'meta_key' => 'last_online',
			'meta_value' => current_time('timestamp') - 60 * 1440,
			'meta_compare' => '>=',
			'fields' => 'ids',
		)))->get_results();
		$apply_online_filter = !empty($online_users);
	}

	// VIP
	$vip_args = array(
		'post_type' => $taxonomy_profile_url,
		'orderby' => 'rand',
		'posts_per_page' => get_option('headerslideritems'),
		'meta_query' => array(
			array(
				'key' => 'featured',
				'value' => '1',
				'compare' => '=',
				'type' => 'NUMERIC',
			),
		),
	);
	if (!empty($tax_query)) {
		$vip_args['tax_query'] = $tax_query;
	}
	if (!empty($date_query)) {
		$vip_args['date_query'] = $date_query;
	}
	if (($filter_type === 'online' || $filter_type === 'recent_24h') && $apply_online_filter) {
		$vip_args['author__in'] = $online_users;
	}
	if ($chip_meta) {
		$vip_args['meta_query'][] = $chip_meta;
	}
	$vip_query = new WP_Query($vip_args);
	$vip_count = (int) $vip_query->found_posts;
	$vip_html = escortwp_render_profile_cards($vip_query, $empty_text);

	// Premium
	$premium_html = '';
	$premium_count = 0;
	if (get_option('frontpageshowpremium') == 1) {
		$premium_args = array(
			'post_type' => $taxonomy_profile_url,
			'orderby' => 'meta_value_num',
			'meta_key' => 'premium_since',
			'posts_per_page' => get_option('frontpageshowpremiumcols') * 5,
			'meta_query' => array(
				array(
					'key' => 'premium',
					'value' => '1',
					'compare' => '=',
					'type' => 'NUMERIC',
				),
			),
		);
		if (!empty($tax_query)) {
			$premium_args['tax_query'] = $tax_query;
		}
		if (!empty($date_query)) {
			$premium_args['date_query'] = $date_query;
		}
		if (($filter_type === 'online' || $filter_type === 'recent_24h') && $apply_online_filter) {
			$premium_args['author__in'] = $online_users;
		}
		if ($chip_meta) {
			$premium_args['meta_query'][] = $chip_meta;
		}
		$premium_query = new WP_Query($premium_args);
		$premium_count = (int) $premium_query->found_posts;
		$premium_html = escortwp_render_profile_cards($premium_query, $empty_text);
	}

	// Newly Added (basic)
	$new_html = '';
	$new_count = 0;
	if (get_option('frontpageshownormal') == 1) {
		$new_meta_query = array();
		if ($filter_type !== 'premium') {
			$new_meta_query[] = array(
				'key' => 'premium',
				'value' => '0',
				'compare' => '=',
				'type' => 'NUMERIC',
			);
		}
		$new_args = array(
			'post_type' => $taxonomy_profile_url,
			'posts_per_page' => get_option('frontpageshownormalcols') * 5,
			'meta_query' => $new_meta_query,
		);
		if (!empty($tax_query)) {
			$new_args['tax_query'] = $tax_query;
		}
		if (!empty($date_query)) {
			$new_args['date_query'] = $date_query;
		}
		if (($filter_type === 'online' || $filter_type === 'recent_24h') && $apply_online_filter) {
			$new_args['author__in'] = $online_users;
		}
		if ($chip_meta) {
			$new_args['meta_query'][] = $chip_meta;
		}
		$new_query = new WP_Query($new_args);
		$new_count = (int) $new_query->found_posts;
		$new_html = escortwp_render_profile_cards($new_query, $empty_text);
	}

	$active_filter_label = escortwp_get_home_filter_label($filter_type, $filter_value);
	$location_name = $resolved_location_term && !is_wp_error($resolved_location_term) ? (string) $resolved_location_term->name : '';
	$active_context_label = $filter_type === 'all'
		? __('All escorts', 'escortwp')
		: $active_filter_label;
	if ($location_name !== '') {
		$active_context_label = $filter_type === 'all'
			? sprintf(__('All escorts in %s', 'escortwp'), $location_name)
			: sprintf(__('%1$s in %2$s', 'escortwp'), $active_filter_label, $location_name);
	}
	$total_results = (int) ($vip_count + $premium_count + $new_count);

	wp_send_json_success(array(
		'vip_html' => $vip_html,
		'premium_html' => $premium_html,
		'new_html' => $new_html,
		'online_fallback' => $online_fallback,
			'summary' => array(
				'total_results' => $total_results,
				'vip_count' => $vip_count,
				'premium_count' => $premium_count,
				'new_count' => $new_count,
				'active_filter_type' => $filter_type,
				'active_filter_label' => $active_filter_label,
				'active_context_label' => $active_context_label,
				'location_name' => $location_name,
			),
		));
}

function escortwp_resolve_location_term()
{
	check_ajax_referer('escortwp_resolve_location_term', 'nonce');

	global $taxonomy_location_url;
	if (empty($taxonomy_location_url)) {
		$taxonomy_location_url = 'escorts-from';
	}

	$latitude = isset($_POST['lat']) ? (float) wp_unslash($_POST['lat']) : 0.0;
	$longitude = isset($_POST['lng']) ? (float) wp_unslash($_POST['lng']) : 0.0;

	if (abs($latitude) < 0.0001 && abs($longitude) < 0.0001) {
		wp_send_json_error(array(
			'status' => 'invalid',
			'message' => __('Invalid location coordinates.', 'escortwp'),
		), 400);
	}

	$resolved = escortwp_child_resolve_location_term($latitude, $longitude, $taxonomy_location_url);
	$payload = array(
		'status' => $resolved['status'] ?? 'no_match',
		'term_id' => isset($resolved['term_id']) ? (int) $resolved['term_id'] : 0,
		'term_name' => isset($resolved['term_name']) ? (string) $resolved['term_name'] : '',
		'taxonomy' => $taxonomy_location_url,
		'archive_url' => isset($resolved['archive_url']) ? (string) $resolved['archive_url'] : '',
	);

	if ($payload['status'] === 'error') {
		wp_send_json_error($payload, 502);
	}

	wp_send_json_success($payload);
}

/**
 * AJAX handler to check profile status for activation button
 */
add_action('wp_ajax_escortwp_check_profile_status', 'escortwp_check_profile_status');
add_action('wp_ajax_nopriv_escortwp_check_profile_status', 'escortwp_check_profile_status_nopriv');

function escortwp_check_profile_status()
{
	if (!isset($_POST['user_id']) || !is_user_logged_in()) {
		wp_send_json_error('User not logged in');
	}

	$user_id = intval($_POST['user_id']);
	$current_user_id = get_current_user_id();

	// Security check - user can only check their own status
	if ($user_id !== $current_user_id) {
		wp_send_json_error('Invalid user');
	}

	// Get the escort post ID for this user
	$escort_post_id = intval(get_option('escortpostid' . $user_id));

	if (!$escort_post_id) {
		wp_send_json_error('No profile found');
	}

	// Check if profile is private
	$is_private = ('private' === get_post_status($escort_post_id));
	$needs_payment = get_post_meta($escort_post_id, 'needs_payment', true);
	$notactive = get_post_meta($escort_post_id, 'notactive', true);
	$escort_expire = get_post_meta($escort_post_id, 'escort_expire', true);
	$escort_expire_ts = $escort_expire ? intval($escort_expire) : 0;
	$is_expired = ($needs_payment)
		|| ($escort_expire_ts && $escort_expire_ts < time())
		|| ($is_private && $notactive);

	wp_send_json_success(array(
		'is_private' => $is_private,
		'needs_payment' => $needs_payment ? true : false,
		'notactive' => $notactive ? true : false,
		'escort_expire' => $escort_expire_ts,
		'is_expired' => $is_expired ? true : false,
		'post_id' => $escort_post_id,
		'post_status' => get_post_status($escort_post_id),
		'user_id' => $user_id
	));
}

function escortwp_check_profile_status_nopriv()
{
	wp_send_json_error('User not logged in');
}

// Image resizing fix
add_action('wp_footer', function () {
	if (is_admin())
		return; // front-end only
	?>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			// Find any profile images that have sizes="auto" and remove it
			document.querySelectorAll('.girl .thumb img[sizes="auto"], img.mobile-ready-img[sizes="auto"]').forEach(function (img) {
				img.removeAttribute('sizes');
			});
		});
	</script>
	<?php
});

/**
 * Add body class for single escort pages (needed for sidebar CSS scoping).
 */
function escortwp_child_profile_body_class($classes)
{
	if (is_singular('escort')) {
		$classes[] = 'single-escort';
	}
	return $classes;
}
add_filter('body_class', 'escortwp_child_profile_body_class');

/**
 * Ensure attachment images always have an explicit alt fallback.
 */
function escortwp_child_attachment_alt_fallback($attr, $attachment)
{
	if (!empty($attr['alt'])) {
		return $attr;
	}

	$alt = trim((string) get_post_meta($attachment->ID, '_wp_attachment_image_alt', true));
	if ($alt === '') {
		$alt = trim((string) $attachment->post_title);
	}
	if ($alt === '') {
		$alt = get_bloginfo('name');
	}

	$attr['alt'] = $alt;
	return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'escortwp_child_attachment_alt_fallback', 10, 2);

/**
 * Wallet feature flag:
 * Enabled only when the CRM wallet credentials are configured and the synced CRM mode is not disabled.
 * Override via the `escortwp_child_wallet_feature_enabled` filter.
 */
function escortwp_child_wallet_feature_enabled($context = array())
{
	$host = '';

	if (!empty($_SERVER['HTTP_HOST'])) {
		$host = strtolower(trim((string) wp_unslash($_SERVER['HTTP_HOST'])));
		$colon_pos = strpos($host, ':');
		if ($colon_pos !== false) {
			$host = substr($host, 0, $colon_pos);
		}
	}

	$synced_wallet_config = escortwp_child_get_wallet_synced_config();
	$wallet_mode = is_array($synced_wallet_config) ? (string) ($synced_wallet_config['mode'] ?? 'disabled') : 'disabled';
	$enabled = escortwp_child_wallet_credentials_configured() && $wallet_mode !== 'disabled';

	return (bool) apply_filters('escortwp_child_wallet_feature_enabled', $enabled, $context);
}

/**
 * Wallet feature state helper for templates/JS config.
 */
function escortwp_child_wallet_feature_state($context = array())
{
	return escortwp_child_wallet_feature_enabled($context) ? 'enabled' : 'coming_soon';
}

/**
 * Human-readable reason shown when wallet actions are unavailable.
 */
function escortwp_child_wallet_unavailable_message($context = array())
{
	$synced_wallet_config = escortwp_child_get_wallet_synced_config();
	$wallet_mode = is_array($synced_wallet_config) ? (string) ($synced_wallet_config['mode'] ?? 'disabled') : 'disabled';
	$market_name = is_array($synced_wallet_config)
		? trim((string) ($synced_wallet_config['config']['market']['name'] ?? ''))
		: '';

	if ($wallet_mode === 'disabled') {
		if ($market_name !== '') {
			return sprintf(
				/* translators: %s market name */
				__('Wallet payments are currently disabled for %s.', 'escortwp'),
				$market_name
			);
		}

		if (!empty($synced_wallet_config)) {
			return __('Wallet payments are currently disabled for this market.', 'escortwp');
		}

		return __('Wallet payments are waiting for CRM market settings sync.', 'escortwp');
	}

	if (!escortwp_child_wallet_credentials_configured()) {
		return __('Wallet payments are configured in CRM, but this site is still missing wallet credentials.', 'escortwp');
	}

	return __('Wallet payments are temporarily unavailable. Please try again later.', 'escortwp');
}

/**
 * Resolve the CRM API base URL used by the profile account payment flows.
 */
function escortwp_child_get_crm_api_base_url()
{
	$default = 'https://testing.exotic-ads.com';
	$raw = trim((string) get_option('exotic_crm_api_base_url', $default));
	$url = untrailingslashit($raw);

	if ($url === '' || !wp_http_validate_url($url)) {
		$url = $default;
	}

	return (string) apply_filters('escortwp_child_crm_api_base_url', $url);
}

/**
 * Resolve the CRM API base URL used by the server-side wallet proxy.
 *
 * Wallet AJAX runs from WordPress PHP, so local development can safely target
 * the local CRM instance even when browser-facing activation flows still use a
 * public CRM URL.
 */
function escortwp_child_get_wallet_api_base_url()
{
	$environment = function_exists('wp_get_environment_type')
		? wp_get_environment_type()
		: (defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'production');
	$default = $environment === 'local'
		? 'http://localhost:8000'
		: escortwp_child_get_crm_api_base_url();
	$raw = trim((string) get_option('exotic_crm_wallet_api_base_url', ''));
	$url = untrailingslashit($raw !== '' ? $raw : $default);

	if ($url === '' || !wp_http_validate_url($url)) {
		$url = $default;
	}

	return (string) apply_filters('escortwp_child_wallet_api_base_url', $url);
}

/**
 * Feature flag for the optional STK retry button in the payment modal.
 * Keep disabled until the CRM exposes a supported retry endpoint.
 */
function escortwp_child_stk_retry_enabled($context = array())
{
	$enabled = get_option('exotic_stk_retry_enabled', '0') === '1';

	return (bool) apply_filters('escortwp_child_stk_retry_enabled', $enabled, $context);
}

function escortwp_child_get_wallet_synced_config()
{
	$config = get_option('exotic_crm_wallet_config', array());

	return is_array($config) ? $config : array();
}

function escortwp_child_get_wallet_platform_id($user_id = 0)
{
	$user_id = $user_id ? absint($user_id) : get_current_user_id();
	$platform_id = $user_id ? absint(get_user_meta($user_id, 'exotic_crm_wallet_platform_id', true)) : 0;

	if ($platform_id <= 0) {
		$platform_id = absint(get_option('exotic_crm_wallet_platform_id', 0));
	}

	if ($platform_id <= 0) {
		$config = escortwp_child_get_wallet_synced_config();
		$platform_id = absint($config['platform_id'] ?? 0);
		if ($platform_id <= 0 && !empty($config['config']['market']['platform_id'])) {
			$platform_id = absint($config['config']['market']['platform_id']);
		}
	}

	return $platform_id;
}

function escortwp_child_wallet_credentials_configured()
{
	$api_base_url = escortwp_child_get_wallet_api_base_url();
	$bearer_key = trim((string) get_option('exotic_crm_wallet_bearer_key', ''));
	$hmac_secret = trim((string) get_option('exotic_crm_wallet_hmac_secret', ''));
	$platform_id = escortwp_child_get_wallet_platform_id();

	return $api_base_url !== ''
		&& $bearer_key !== ''
		&& $hmac_secret !== ''
		&& $platform_id > 0;
}

function escortwp_child_wallet_current_profile_id($user_id = 0)
{
	$user_id = $user_id ? absint($user_id) : get_current_user_id();
	if ($user_id <= 0) {
		return 0;
	}

	$profile_id = absint(get_option('escortpostid' . $user_id, 0));
	if ($profile_id > 0) {
		return $profile_id;
	}

	if (is_singular('escort')) {
		$current_post_id = absint(get_the_ID());
		if ($current_post_id > 0 && absint(get_post_field('post_author', $current_post_id)) === $user_id) {
			return $current_post_id;
		}
	}

	return 0;
}

function escortwp_child_wallet_sanitize_value($value)
{
	if (is_array($value)) {
		$sanitized = array();
		foreach ($value as $key => $item) {
			$sanitized_key = is_string($key) ? sanitize_key($key) : $key;
			$sanitized[$sanitized_key] = escortwp_child_wallet_sanitize_value($item);
		}

		return $sanitized;
	}

	if (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
		return $value;
	}

	return sanitize_text_field((string) $value);
}

function escortwp_child_get_wallet_cached_summary($user_id = 0)
{
	$user_id = $user_id ? absint($user_id) : get_current_user_id();
	if ($user_id <= 0) {
		return array();
	}

	$summary = get_user_meta($user_id, 'exotic_crm_wallet_summary', true);
	$summary = is_array($summary) ? $summary : array();
	$config = escortwp_child_get_wallet_synced_config();

	if (empty($summary)) {
		$summary = array(
			'platform_id' => escortwp_child_get_wallet_platform_id($user_id),
			'balance' => (string) get_user_meta($user_id, 'exotic_crm_wallet_balance', true),
			'currency' => (string) get_user_meta($user_id, 'exotic_crm_wallet_currency', true),
			'mode' => (string) get_user_meta($user_id, 'exotic_crm_wallet_mode', true),
			'refreshed_at' => (string) get_user_meta($user_id, 'exotic_crm_wallet_refreshed_at', true),
			'wallet_last_synced_at' => (string) get_user_meta($user_id, 'exotic_crm_wallet_last_synced_at', true),
			'last_topup' => get_user_meta($user_id, 'exotic_crm_wallet_last_topup', true),
			'transactions' => get_user_meta($user_id, 'exotic_crm_wallet_transactions', true),
		);
	}

	$summary['platform_id'] = absint($summary['platform_id'] ?? escortwp_child_get_wallet_platform_id($user_id));
	$summary['balance'] = $summary['balance'] !== '' ? (string) $summary['balance'] : '0.00';
	$summary['currency'] = !empty($summary['currency'])
		? (string) $summary['currency']
		: (string) ($config['config']['market']['currency'] ?? 'KES');
	$summary['mode'] = !empty($summary['mode'])
		? (string) $summary['mode']
		: (string) ($config['mode'] ?? 'disabled');
	$summary['refreshed_at'] = !empty($summary['refreshed_at']) ? (string) $summary['refreshed_at'] : '';
	$summary['wallet_last_synced_at'] = !empty($summary['wallet_last_synced_at']) ? (string) $summary['wallet_last_synced_at'] : '';
	$summary['last_topup'] = is_array($summary['last_topup'] ?? null) ? $summary['last_topup'] : null;
	$summary['transactions'] = is_array($summary['transactions'] ?? null) ? array_values($summary['transactions']) : array();
	$summary['config'] = is_array($summary['config'] ?? null)
		? $summary['config']
		: (is_array($config['config'] ?? null) ? $config['config'] : array());

	return $summary;
}

function escortwp_child_store_wallet_summary($user_id, array $payload)
{
	$user_id = absint($user_id);
	$config_payload = is_array($payload['config'] ?? null) ? $payload['config'] : null;
	$platform_id = absint($payload['client']['platform_id'] ?? 0);

	if ($platform_id <= 0) {
		$platform_id = absint($payload['config']['market']['platform_id'] ?? 0);
	}

	if ($config_payload) {
		$stored_config = array(
			'platform_id' => $platform_id > 0 ? $platform_id : escortwp_child_get_wallet_platform_id($user_id),
			'mode' => sanitize_key((string) ($payload['mode'] ?? ($payload['wallet']['mode'] ?? 'disabled'))),
			'synced_at' => (string) ($payload['wallet_last_synced_at'] ?? gmdate('c')),
			'config' => escortwp_child_wallet_sanitize_value($config_payload),
		);
		update_option('exotic_crm_wallet_config', $stored_config, false);

		if (!empty($stored_config['platform_id'])) {
			update_option('exotic_crm_wallet_platform_id', (int) $stored_config['platform_id'], false);
		}
	}

	$summary = array(
		'platform_id' => $platform_id > 0 ? $platform_id : escortwp_child_get_wallet_platform_id($user_id),
		'balance' => number_format((float) ($payload['balance'] ?? ($payload['wallet']['balance'] ?? 0)), 2, '.', ''),
		'currency' => strtoupper((string) ($payload['currency'] ?? ($payload['wallet']['currency'] ?? 'KES'))),
		'mode' => sanitize_key((string) ($payload['mode'] ?? ($payload['wallet']['mode'] ?? 'disabled'))),
		'refreshed_at' => (string) ($payload['refreshed_at'] ?? gmdate('c')),
		'wallet_last_synced_at' => (string) ($payload['wallet_last_synced_at'] ?? gmdate('c')),
		'last_topup' => is_array($payload['last_topup'] ?? null) ? escortwp_child_wallet_sanitize_value($payload['last_topup']) : null,
		'transactions' => is_array($payload['transactions'] ?? null) ? escortwp_child_wallet_sanitize_value(array_values($payload['transactions'])) : array(),
		'config' => is_array($config_payload) ? escortwp_child_wallet_sanitize_value($config_payload) : (escortwp_child_get_wallet_synced_config()['config'] ?? array()),
	);

	update_user_meta($user_id, 'exotic_crm_wallet_balance', $summary['balance']);
	update_user_meta($user_id, 'exotic_crm_wallet_currency', $summary['currency']);
	update_user_meta($user_id, 'exotic_crm_wallet_mode', $summary['mode']);
	update_user_meta($user_id, 'exotic_crm_wallet_platform_id', $summary['platform_id']);
	update_user_meta($user_id, 'exotic_crm_wallet_refreshed_at', $summary['refreshed_at']);
	update_user_meta($user_id, 'exotic_crm_wallet_last_synced_at', $summary['wallet_last_synced_at']);
	update_user_meta($user_id, 'exotic_crm_wallet_last_topup', $summary['last_topup']);
	update_user_meta($user_id, 'exotic_crm_wallet_transactions', $summary['transactions']);
	update_user_meta($user_id, 'exotic_crm_wallet_summary', $summary);

	return $summary;
}

function escortwp_child_wallet_signature($timestamp, $method, $path, $platform_id, $idempotency_key, $body, $secret)
{
	$payload = implode("\n", array(
		(string) $timestamp,
		strtoupper((string) $method),
		'/' . ltrim((string) $path, '/'),
		(string) absint($platform_id),
		(string) $idempotency_key,
		hash('sha256', (string) $body),
	));

	return hash_hmac('sha256', $payload, (string) $secret);
}

function escortwp_child_wallet_request($method, $path, $payload = array(), $write = false, $user_id = 0)
{
	$method = strtoupper(trim((string) $method));
	$path = '/' . ltrim((string) $path, '/');
	$api_base_url = escortwp_child_get_wallet_api_base_url();
	$platform_id = escortwp_child_get_wallet_platform_id($user_id);
	$bearer_key = trim((string) get_option('exotic_crm_wallet_bearer_key', ''));
	$hmac_secret = trim((string) get_option('exotic_crm_wallet_hmac_secret', ''));

	if ($api_base_url === '' || $platform_id <= 0 || $bearer_key === '') {
		return new WP_Error('wallet_not_configured', 'Wallet CRM settings are incomplete.', array('status' => 500));
	}

	$timestamp = (string) time();
	$url = untrailingslashit($api_base_url) . $path;
	$headers = array(
		'Accept' => 'application/json',
		'Authorization' => 'Bearer ' . $bearer_key,
		'X-Exotic-Platform-Id' => (string) $platform_id,
		'X-Exotic-Timestamp' => $timestamp,
	);
	$args = array(
		'method' => $method,
		'timeout' => 20,
		'headers' => $headers,
	);

	if ($method === 'GET' && !empty($payload)) {
		$url = add_query_arg($payload, $url);
	} else {
		$body = wp_json_encode($payload);
		$args['body'] = $body;
		$args['headers']['Content-Type'] = 'application/json';

		if ($write) {
			if ($hmac_secret === '') {
				return new WP_Error('wallet_not_configured', 'Wallet HMAC secret is missing.', array('status' => 500));
			}

			$idempotency_key = 'wp-wallet-' . wp_generate_uuid4();
			$args['headers']['X-Idempotency-Key'] = $idempotency_key;
			$args['headers']['X-Exotic-Signature'] = escortwp_child_wallet_signature(
				$timestamp,
				$method,
				$path,
				$platform_id,
				$idempotency_key,
				$body,
				$hmac_secret
			);
		}
	}

	$response = wp_remote_request($url, $args);
	if (is_wp_error($response)) {
		return $response;
	}

	$status_code = (int) wp_remote_retrieve_response_code($response);
	$body = wp_remote_retrieve_body($response);
	$decoded = json_decode((string) $body, true);
	$decoded = is_array($decoded) ? $decoded : array();

	if ($status_code >= 400) {
		return new WP_Error(
			sanitize_key((string) ($decoded['error_code'] ?? 'wallet_request_failed')),
			(string) ($decoded['message'] ?? 'Wallet request failed.'),
			array(
				'status' => $status_code,
				'response' => $decoded,
			)
		);
	}

	return $decoded;
}

function escortwp_child_wallet_ajax_context()
{
	if (!is_user_logged_in()) {
		return new WP_Error('not_logged_in', 'You must be logged in to use the wallet.', array('status' => 401));
	}

	check_ajax_referer('exotic_wallet_actions', 'nonce');

	$user_id = get_current_user_id();
	$profile_id = escortwp_child_wallet_current_profile_id($user_id);
	if ($profile_id <= 0) {
		return new WP_Error('wallet_profile_missing', 'No wallet-enabled profile was found for this user.', array('status' => 404));
	}

	return array(
		'user_id' => $user_id,
		'profile_id' => $profile_id,
	);
}

function escortwp_child_wallet_send_ajax_error($error)
{
	if (!is_wp_error($error)) {
		wp_send_json_error(array('message' => 'Wallet request failed.'), 500);
	}

	$data = $error->get_error_data();
	$status = absint($data['status'] ?? 0);
	$response = $data['response'] ?? null;
	wp_send_json_error(array(
		'code' => $error->get_error_code(),
		'message' => $error->get_error_message(),
		'response' => is_array($response) ? $response : null,
	), $status > 0 ? $status : 500);
}

function escortwp_child_wallet_refresh_ajax()
{
	$context = escortwp_child_wallet_ajax_context();
	if (is_wp_error($context)) {
		escortwp_child_wallet_send_ajax_error($context);
	}

	$response = escortwp_child_wallet_request('GET', '/api/wallet/balance', array(
		'wp_user_id' => $context['user_id'],
		'wp_post_id' => $context['profile_id'],
	), false, $context['user_id']);

	if (is_wp_error($response)) {
		escortwp_child_wallet_send_ajax_error($response);
	}

	$summary = escortwp_child_store_wallet_summary($context['user_id'], $response);

	wp_send_json_success(array(
		'wallet' => $summary,
		'message' => 'Wallet refreshed.',
	));
}
add_action('wp_ajax_exotic_wallet_refresh', 'escortwp_child_wallet_refresh_ajax');

function escortwp_child_wallet_subscribe_ajax()
{
	$context = escortwp_child_wallet_ajax_context();
	if (is_wp_error($context)) {
		escortwp_child_wallet_send_ajax_error($context);
	}

	$product_id = absint($_POST['product_id'] ?? 0);
	$duration = sanitize_text_field((string) ($_POST['duration'] ?? ''));
	if ($product_id <= 0 || $duration === '') {
		escortwp_child_wallet_send_ajax_error(new WP_Error('wallet_subscribe_invalid', 'Package and duration are required.', array('status' => 422)));
	}

	$response = escortwp_child_wallet_request('POST', '/api/wallet/subscribe', array(
		'wp_user_id' => $context['user_id'],
		'wp_post_id' => $context['profile_id'],
		'product_id' => $product_id,
		'duration' => $duration,
	), true, $context['user_id']);

	if (is_wp_error($response)) {
		escortwp_child_wallet_send_ajax_error($response);
	}

	$refresh = escortwp_child_wallet_request('GET', '/api/wallet/balance', array(
		'wp_user_id' => $context['user_id'],
		'wp_post_id' => $context['profile_id'],
	), false, $context['user_id']);
	$wallet = is_wp_error($refresh) ? escortwp_child_get_wallet_cached_summary($context['user_id']) : escortwp_child_store_wallet_summary($context['user_id'], $refresh);

	wp_send_json_success(array(
		'message' => (string) ($response['message'] ?? 'Subscription paid from wallet.'),
		'replayed' => !empty($response['replayed']),
		'payment' => is_array($response['payment'] ?? null) ? $response['payment'] : null,
		'deal' => is_array($response['deal'] ?? null) ? $response['deal'] : null,
		'wallet' => $wallet,
	));
}
add_action('wp_ajax_exotic_wallet_subscribe_from_wallet', 'escortwp_child_wallet_subscribe_ajax');

function escortwp_child_wallet_initiate_topup_ajax()
{
	$context = escortwp_child_wallet_ajax_context();
	if (is_wp_error($context)) {
		escortwp_child_wallet_send_ajax_error($context);
	}

	$provider = sanitize_text_field((string) ($_POST['provider'] ?? ''));
	$amount = round((float) ($_POST['amount'] ?? 0), 2);
	if ($provider === '' || $amount <= 0) {
		escortwp_child_wallet_send_ajax_error(new WP_Error('wallet_topup_invalid', 'Provider and amount are required.', array('status' => 422)));
	}

	$payload = array(
		'wp_user_id' => $context['user_id'],
		'wp_post_id' => $context['profile_id'],
		'provider' => $provider,
		'amount' => number_format($amount, 2, '.', ''),
	);

	$auto_subscribe_enabled = !empty($_POST['auto_subscribe_enabled']);
	if ($auto_subscribe_enabled) {
		$auto_product_id = absint($_POST['auto_subscribe_product_id'] ?? 0);
		$auto_duration = sanitize_text_field((string) ($_POST['auto_subscribe_duration'] ?? ''));
		if ($auto_product_id > 0 && $auto_duration !== '') {
			$payload['auto_subscribe'] = array(
				'enabled' => true,
				'product_id' => $auto_product_id,
				'duration' => $auto_duration,
			);
		}
	}

	$response = escortwp_child_wallet_request('POST', '/api/billing/initiate', $payload, true, $context['user_id']);
	if (is_wp_error($response)) {
		escortwp_child_wallet_send_ajax_error($response);
	}

	wp_send_json_success(array(
		'message' => (string) ($response['message'] ?? 'Billing initiation created.'),
		'replayed' => !empty($response['replayed']),
		'mode' => (string) ($response['mode'] ?? 'disabled'),
		'provider' => (string) ($response['provider'] ?? $provider),
		'payment' => is_array($response['payment'] ?? null) ? $response['payment'] : null,
		'action' => is_array($response['action'] ?? null) ? $response['action'] : null,
	));
}
add_action('wp_ajax_exotic_wallet_initiate_topup', 'escortwp_child_wallet_initiate_topup_ajax');

function escortwp_child_wallet_retry_stk_ajax()
{
	$context = escortwp_child_wallet_ajax_context();
	if (is_wp_error($context)) {
		escortwp_child_wallet_send_ajax_error($context);
	}

	$payment_id = absint($_POST['payment_id'] ?? 0);
	if ($payment_id <= 0) {
		escortwp_child_wallet_send_ajax_error(new WP_Error('wallet_retry_invalid', 'Payment ID is required.', array('status' => 422)));
	}

	$response = escortwp_child_wallet_request('POST', '/api/billing/retry-stk', array(
		'wp_user_id' => $context['user_id'],
		'wp_post_id' => $context['profile_id'],
		'payment_id' => $payment_id,
	), true, $context['user_id']);

	if (is_wp_error($response)) {
		escortwp_child_wallet_send_ajax_error($response);
	}

	wp_send_json_success(array(
		'message' => (string) ($response['message'] ?? 'STK retry dispatched.'),
		'payment' => is_array($response['payment'] ?? null) ? $response['payment'] : null,
		'action' => is_array($response['action'] ?? null) ? $response['action'] : null,
	));
}
add_action('wp_ajax_exotic_wallet_retry_stk', 'escortwp_child_wallet_retry_stk_ajax');

/**
 * Enqueue profile page CSS + JS on single escort pages.
 * Priority 120 ensures it loads after override.css (100) and auth.css (110).
 */
function escortwp_child_enqueue_profile_assets()
{
	if (!is_singular('escort'))
		return;

	$css_file = get_stylesheet_directory() . '/css/profile.css';
	wp_enqueue_style(
		'escortwp-profile-css',
		get_stylesheet_directory_uri() . '/css/profile.css',
		array('escortwp-override-css'),
		file_exists($css_file) ? filemtime($css_file) : '1.0.0'
	);

	$js_file = get_stylesheet_directory() . '/js/profile-scroll.js';
	wp_enqueue_script(
		'escortwp-profile-scroll',
		get_stylesheet_directory_uri() . '/js/profile-scroll.js',
		array(),
		file_exists($js_file) ? filemtime($js_file) : '1.0.0',
		true
	);
}
add_action('wp_enqueue_scripts', 'escortwp_child_enqueue_profile_assets', 120);

/**
 * Step 8: Auto-capitalise escort display names.
 * Converts "sexy linda" → "Sexy Linda" for consistent typography.
 */
function escortwp_child_capitalise_escort_title($title, $post_id = 0)
{
	if ($post_id && get_post_type($post_id) === 'escort') {
		return ucwords(strtolower($title));
	}
	return $title;
}
add_filter('the_title', 'escortwp_child_capitalise_escort_title', 10, 2);

?>
