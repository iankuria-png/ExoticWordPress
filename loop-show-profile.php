<?php
if (!defined('ABSPATH')) exit;

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
$linktitle      = get_the_title();
$imagealt       = $linktitle ?: '';

// Meta
$phone    = get_post_meta($escort_post_id, 'phone', true);
$featured = get_post_meta($escort_post_id, 'featured', true);
$premium  = get_post_meta($escort_post_id, 'premium', true);

// Location (defensive)
$location   = [];
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

// Videos (to show the little "video" label if any)
$videos = get_children([
    'post_parent'    => $escort_post_id,
    'post_status'    => 'inherit',
    'post_type'      => 'attachment',
    'post_mime_type' => 'video',
    'numberposts'    => 1,
]);

// Premium class
$thumbclass = ($premium === '1') ? ' girlpremium' : '';
?>

<div class="girl" itemscope itemtype="http://schema.org/Person">
    <?php if ($featured === '1'): ?>
        <div class="vip-div in-loop">VIP</div>
    <?php endif; ?>

    <div class="thumb rad3<?php echo esc_attr($thumbclass); ?>">
        <div class="girl-overlay">
            <div class="set-pad">
                <a href="<?php echo esc_url(get_permalink()); ?>">
                    <b>
                        <div class="overlay-text">
                            <span style="color:#fff"><?php the_title(); ?></span>
                        </div>
                    </b>
                    <br>
                    <span style="color:#fff">
                        <?php echo esc_html( wp_strip_all_tags( wp_trim_words(get_the_content(), 30, '...') ) ); ?>
                    </span>
                </a>
            </div>
        </div>

        <div class="thumbwrapper">
            <a href="<?php echo esc_url(get_permalink()); ?>" title="<?php echo esc_attr($linktitle); ?>">
                <?php if (!empty($videos)): ?>
                    <span class="label-video">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/i/video-th-icon.png'); ?>" alt="" />
                    </span>
                <?php endif; ?>

                <div class="model-info">
                    <?php
                    if (function_exists('get_escort_labels')) {
                        echo get_escort_labels($escort_post_id); // expected to be safe HTML
                    }
                    ?>
                    <div class="clear"></div>
                    <div class="desc">
                        <div class="girl-name" title="<?php echo esc_attr($linktitle); ?>" itemprop="name">
                            <?php the_title(); ?>
                        </div>
                        <div class="clear"></div>
                        <?php if (!empty($location)): ?>
                            <span class="girl-desc-location" itemprop="homeLocation">
                                <span class="icon-location"></span>
                                <?php echo esc_html(implode(', ', $location)); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                // Fallbacks to avoid empty src/srcset
                if (function_exists('get_first_image')) {
                    $img_5 = get_first_image($escort_post_id, '5');
                    $img_d = get_first_image($escort_post_id);
                    $img_4 = get_first_image($escort_post_id, '4');
                } else {
                    $img_5 = $img_d = $img_4 = '';
                }
                ?>
                <img
                    class="mobile-ready-img rad3"
                    src="<?php echo esc_url($img_5); ?>"
                    srcset="<?php echo esc_url($img_5); ?> 170w, <?php echo esc_url($img_d); ?> 280w, <?php echo esc_url($img_4); ?> 400w"
                    data-responsive-img-url="<?php echo esc_url($img_5); ?>"
                    alt="<?php echo esc_attr($imagealt); ?>"
                    itemprop="image"
                />

                <?php if ($premium === '1'): ?>
                    <div class="premiumlabel rad3">
                        <span><?php _e('PREMIUM','escortwp'); ?></span>
                    </div>
                <?php endif; ?>
            </a>

            <?php if (!empty($phone) && $featured === '1'): ?>
                <div class="phone-number-box">
                    <?php echo esc_html($phone); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($phone)): ?>
                <a class="call-now-box" href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone)); ?>" itemprop="telephone">
                    <span class="icon icon-phone"></span>
                    <span class="need-login"><?php printf(__('Call %s','escortwp'), get_the_title()); ?></span>
                </a>
            <?php endif; ?>

            <div class="clear"></div>
        </div>

        <?php if (!empty($agency_manage_escort_buttons)) { echo $agency_manage_escort_buttons; } ?>
    </div>

    <div class="profile_shadow"></div>
    <div class="clear"></div>
</div>

<?php
/**
 * Separators: many legacy lists rely on these, but they *break CSS grids*.
 * Set $GLOBALS['PROFILE_GRID_NO_SEPARATORS'] = true; before including this file
 * (e.g., in Recently Viewed or other grid sections) to disable them.
 */
if (empty($GLOBALS['PROFILE_GRID_NO_SEPARATORS'])) {
    if (($i % 5) === 0) echo '<div class="show-separator show5profiles clear"></div>';
    if (($i % 4) === 0) echo '<div class="show-separator show4profiles clear hide"></div>';
    if (($i % 3) === 0) echo '<div class="show-separator show3profiles clear hide"></div>';
    if (($i % 2) === 0) echo '<div class="show-separator show2profiles clear hide"></div>';
}
$i++;
unset($escort_label, $belongstoescortid, $class);
