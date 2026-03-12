<?php
if (!defined('ABSPATH'))
    exit;

/**
 * loop-show-profile.php (hardened)
 * - Guards undefined vars / constants
 * - Plays nice with grid layouts (optional: disable separators)
 * - Avoids PHP notices on pages where $i is not preset
 * - Safer rendering (escapes, checks)
 */

// --- Error toggles (optional) ---
if (!defined('ESCORTWP_DEBUG_ERRORS')) {
    define('ESCORTWP_DEBUG_ERRORS', false);
}
if (ESCORTWP_DEBUG_ERRORS) {
    @ini_set('display_errors', '1');
    @error_reporting(E_ALL);
} else {
    @ini_set('display_errors', '0');
}

// --- Theme safety check, but don’t fatal if constant missing ---
if (defined('isdolcetheme') && isdolcetheme !== 1) {
    exit;
}

// --- Ensure item counter exists (used by separators at bottom) ---
if (!isset($i) || !is_numeric($i)) {
    $i = 1;
}

global $taxonomy_location_url;

// --- Base data ---
$escort_post_id = get_the_ID();
$linktitle = get_the_title();
$imagealt = $linktitle ?: '';
$card_context = function_exists('escortwp_child_get_profile_card_context')
	? escortwp_child_get_profile_card_context($escort_post_id)
	: [];

// Meta
$phone = array_key_exists('phone', $card_context) ? $card_context['phone'] : get_post_meta($escort_post_id, 'phone', true);
$featured = array_key_exists('featured', $card_context) ? $card_context['featured'] : get_post_meta($escort_post_id, 'featured', true);
$premium = array_key_exists('premium', $card_context) ? $card_context['premium'] : get_post_meta($escort_post_id, 'premium', true);
$verified = array_key_exists('verified', $card_context) ? $card_context['verified'] : get_post_meta($escort_post_id, 'verified', true);
$author_id = get_the_author_meta('ID');
$last_online = array_key_exists('last_online', $card_context)
	? $card_context['last_online']
	: ($author_id ? get_user_meta($author_id, 'last_online', true) : '');
$is_online = $last_online ? ((int) $last_online >= (current_time('timestamp') - 60 * 5)) : false;
$birthday = array_key_exists('birthday', $card_context) ? $card_context['birthday'] : get_post_meta($escort_post_id, 'birthday', true);
$age_badge = '';
if (!empty($birthday)) {
	$birthday_ts = strtotime($birthday);
	if ($birthday_ts) {
		$age_years = (int) floor((time() - $birthday_ts) / 31556926);
		if ($age_years >= 18 && $age_years <= 99) {
			$age_badge = (string) $age_years;
		}
	}
}

$status_badge = '';
$status_badge_class = '';
if ($is_online) {
	$status_badge = __('Online', 'escortwp');
	$status_badge_class = 'online';
} elseif ($featured === '1') {
	$status_badge = __('VIP', 'escortwp');
	$status_badge_class = 'vip';
} elseif ($premium === '1') {
	$status_badge = __('Premium', 'escortwp');
	$status_badge_class = 'premium';
} elseif ($verified === '1') {
	$status_badge = __('Verified', 'escortwp');
	$status_badge_class = 'verified';
}

// Location (defensive)
$location = [];
$city_terms = wp_get_post_terms($escort_post_id, $taxonomy_location_url);
if (!is_wp_error($city_terms) && !empty($city_terms)) {
    $city = $city_terms[0];
    if (!empty($city->name)) {
        $location[] = $city->name;
    }
    $state = get_term($city->parent, $taxonomy_location_url);
    if ($state && !is_wp_error($state) && !empty($state->name)) {
        $location[] = $state->name;
        $country = get_term($state->parent, $taxonomy_location_url);
        if ($country && !is_wp_error($country) && !empty($country->name)) {
            $location[] = $country->name;
        }
    }
}

// Media counts
$photo_count = array_key_exists('photo_count', $card_context) ? (int) $card_context['photo_count'] : 0;
$video_count = array_key_exists('video_count', $card_context) ? (int) $card_context['video_count'] : 0;
$img_5 = array_key_exists('image_5', $card_context) ? (string) $card_context['image_5'] : '';
$img_d = array_key_exists('image_default', $card_context) ? (string) $card_context['image_default'] : '';
$img_4 = array_key_exists('image_4', $card_context) ? (string) $card_context['image_4'] : '';
$card_description = esc_html(wp_strip_all_tags(wp_trim_words(get_the_content(), 30, '...')));
$photo_badge_label = $photo_count > 0 ? sprintf(_n('%d photo', '%d photos', $photo_count, 'escortwp'), $photo_count) : '';
$video_badge_label = $video_count > 0 ? sprintf(_n('%d video', '%d videos', $video_count, 'escortwp'), $video_count) : '';

// Premium class
$thumbclass = ($premium === '1') ? ' girlpremium' : '';
?>

<?php
$labels = function_exists('get_escort_labels') ? get_escort_labels($escort_post_id) : '';
$trust_classes = 'escort-card__trust';
if (empty($labels)) {
	$trust_classes .= ' escort-card__trust--empty';
}
?>

<div class="girl escort-card" itemscope itemtype="http://schema.org/Person">
    <div class="thumb rad3<?php echo esc_attr($thumbclass); ?>">
        <div class="thumbwrapper">
            <?php if ($status_badge): ?>
                <span class="escort-card__status-badge escort-card__status-badge--<?php echo esc_attr($status_badge_class); ?>">
                    <?php echo esc_html($status_badge); ?>
                </span>
            <?php endif; ?>
            <?php if ($photo_count > 0 || $video_count > 0): ?>
                <div class="escort-card__media-badges" aria-label="<?php esc_attr_e('Available media', 'escortwp'); ?>">
                    <?php if ($photo_count > 0): ?>
                        <span class="escort-card__media-badge escort-card__media-badge--photos" aria-label="<?php echo esc_attr($photo_badge_label); ?>">
                            <span class="escort-card__media-icon" aria-hidden="true">
                                <svg viewBox="0 0 16 16" focusable="false" aria-hidden="true">
                                    <path d="M3.25 4.25h2.05l.72-1.2a1 1 0 0 1 .86-.48h2.24a1 1 0 0 1 .86.48l.72 1.2h2.05A1.75 1.75 0 0 1 14.5 6v5.75a1.75 1.75 0 0 1-1.75 1.75h-9.5A1.75 1.75 0 0 1 1.5 11.75V6a1.75 1.75 0 0 1 1.75-1.75Zm0 1A.75.75 0 0 0 2.5 6v5.75c0 .41.34.75.75.75h9.5a.75.75 0 0 0 .75-.75V6a.75.75 0 0 0-.75-.75h-2.33a.5.5 0 0 1-.43-.24L9.2 3.76a.25.25 0 0 0-.22-.13H6.74a.25.25 0 0 0-.22.13L5.7 5.01a.5.5 0 0 1-.43.24H3.25Zm4.75 1.1A2.4 2.4 0 1 1 5.6 8.75 2.4 2.4 0 0 1 8 6.35Zm0 1A1.4 1.4 0 1 0 9.4 8.75 1.4 1.4 0 0 0 8 7.35Z" fill="currentColor" />
                                </svg>
                            </span>
                            <strong><?php echo esc_html($photo_count); ?></strong>
                        </span>
                    <?php endif; ?>
                    <?php if ($video_count > 0): ?>
                        <span class="escort-card__media-badge escort-card__media-badge--videos" aria-label="<?php echo esc_attr($video_badge_label); ?>">
                            <span class="escort-card__media-icon" aria-hidden="true">
                                <svg viewBox="0 0 16 16" focusable="false" aria-hidden="true">
                                    <path d="M3.25 3.25A1.75 1.75 0 0 0 1.5 5v6a1.75 1.75 0 0 0 1.75 1.75h6.5A1.75 1.75 0 0 0 11.5 11V9.66l2.24 1.53a.5.5 0 0 0 .76-.41V5.22a.5.5 0 0 0-.76-.41L11.5 6.34V5a1.75 1.75 0 0 0-1.75-1.75h-6.5Zm0 1h6.5A.75.75 0 0 1 10.5 5v6a.75.75 0 0 1-.75.75h-6.5A.75.75 0 0 1 2.5 11V5c0-.41.34-.75.75-.75Zm8.25 3.29 2-1.37v3.66l-2-1.37V7.54Z" fill="currentColor" />
                                </svg>
                            </span>
                            <strong><?php echo esc_html($video_count); ?></strong>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="girl-overlay">
                <div class="set-pad">
                    <span class="escort-card__description"><?php echo $card_description; ?></span>
                </div>
            </div>
		            <a class="escort-card__media" href="<?php echo esc_url(get_permalink()); ?>"
		                title="<?php echo esc_attr($linktitle); ?>">
		                <?php
	                // Fallbacks to avoid empty src/srcset
		                if ($img_5 === '' && function_exists('escortwp_child_get_cached_first_image')) {
		                    $img_5 = escortwp_child_get_cached_first_image($escort_post_id, '5');
		                    $img_d = escortwp_child_get_cached_first_image($escort_post_id, '1');
		                    $img_4 = escortwp_child_get_cached_first_image($escort_post_id, '4');
		                } elseif ($img_5 === '' && function_exists('get_first_image')) {
		                    $img_5 = get_first_image($escort_post_id, '5');
		                    $img_d = get_first_image($escort_post_id);
		                    $img_4 = get_first_image($escort_post_id, '4');
		                } elseif ($img_5 === '') {
		                    $img_5 = $img_d = $img_4 = '';
		                }

	                $fallback_card_image = trailingslashit(get_template_directory_uri()) . 'i/no-image.png';
	                if (empty($img_5)) {
	                    $img_5 = $fallback_card_image;
	                }
	                if (empty($img_d)) {
	                    $img_d = $img_5;
	                }
	                if (empty($img_4)) {
	                    $img_4 = $img_d;
	                }
	                $is_fallback_image = ($img_5 === $fallback_card_image);
	                ?>
	                <img class="mobile-ready-img rad3<?php echo $is_fallback_image ? ' is-fallback' : ''; ?>"
	                    src="<?php echo esc_url($img_5); ?>"
	                    <?php if (!$is_fallback_image): ?>
	                    srcset="<?php echo esc_url($img_5); ?> 170w, <?php echo esc_url($img_d); ?> 280w, <?php echo esc_url($img_4); ?> 400w"
	                    <?php endif; ?>
	                    data-responsive-img-url="<?php echo esc_url($img_5); ?>" alt="<?php echo esc_attr($imagealt); ?>"
	                    data-fallback-src="<?php echo esc_url($fallback_card_image); ?>"
	                    loading="lazy" decoding="async" itemprop="image" />
	            </a>

        </div>

        <div class="escort-card__body">
            <a class="escort-card__primary-link" href="<?php echo esc_url(get_permalink()); ?>"
                aria-label="<?php echo esc_attr(sprintf(__('View %s profile', 'escortwp'), $linktitle)); ?>">
                <span class="screen-reader-text"><?php echo esc_html($linktitle); ?></span>
            </a>
            <div class="escort-card__meta">
                <div class="escort-card__name-row">
                    <div class="girl-name" title="<?php echo esc_attr($linktitle); ?>" itemprop="name">
                        <?php the_title(); ?>
                    </div>
                    <?php if ($age_badge !== ''): ?>
                        <span class="escort-card__age"><?php echo esc_html($age_badge); ?>y</span>
                    <?php endif; ?>
                </div>
            <?php if (!empty($location)): ?>
                <span class="girl-desc-location" itemprop="homeLocation">
                    <span class="icon-location"></span>
                    <span class="girl-desc-location__text"><?php echo esc_html(implode(', ', $location)); ?></span>
                </span>
            <?php endif; ?>
            <span class="escort-card__last-active" data-last-online="<?php echo esc_attr($last_online ? (int) $last_online : ''); ?>"></span>
        </div>

            <div class="<?php echo esc_attr($trust_classes); ?>">
                <?php echo $labels; ?>
            </div>

            <?php if (!empty($phone)): ?>
                <a class="contact-btn contact-btn--call" href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone)); ?>"
                    itemprop="telephone">
                    <span class="contact-btn__icon-shell" aria-hidden="true">
                        <span class="icon icon-phone"></span>
                    </span>
                    <span class="contact-btn__label"><?php echo esc_html($phone); ?></span>
                </a>
            <?php else: ?>
                <a class="contact-btn contact-btn--profile" href="<?php echo esc_url(get_permalink()); ?>">
                    <span class="contact-btn__icon-shell" aria-hidden="true">
                        <span class="icon icon-user"></span>
                    </span>
                    <span class="contact-btn__label"><?php _e('View Profile', 'escortwp'); ?></span>
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($agency_manage_escort_buttons)) {
            echo $agency_manage_escort_buttons;
        } ?>
    </div>
</div>

<?php
/**
 * Separators: many legacy lists rely on these, but they *break CSS grids*.
 * Set $GLOBALS['PROFILE_GRID_NO_SEPARATORS'] = true; before including this file
 * (e.g., in Recently Viewed or other grid sections) to disable them.
 */
if (empty($GLOBALS['PROFILE_GRID_NO_SEPARATORS'])) {
    if (($i % 5) === 0)
        echo '<div class="show-separator show5profiles clear"></div>';
    if (($i % 4) === 0)
        echo '<div class="show-separator show4profiles clear hide"></div>';
    if (($i % 3) === 0)
        echo '<div class="show-separator show3profiles clear hide"></div>';
    if (($i % 2) === 0)
        echo '<div class="show-separator show2profiles clear hide"></div>';
}
$i++;
unset($escort_label, $belongstoescortid, $class);
