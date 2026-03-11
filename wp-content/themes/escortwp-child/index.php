<?php
/**
 * Child‐theme index.php
 */
get_header();
$GLOBALS['PROFILE_GRID_NO_SEPARATORS'] = true;

global $taxonomy_location_url, $taxonomy_profile_url;

$term_slug = get_query_var('term');
$taxonomyName = get_query_var('taxonomy');
$current_term = get_term_by('slug', $term_slug, $taxonomyName);

// Build VIP args once (used for VIP section + Online fallback)
$vip_query = null;
if ($taxonomyName === 'escorts-from') {
  $vip_args = [
    'post_type' => $taxonomy_profile_url,
    'ignore_sticky_posts' => true,
    'no_found_rows' => true,
    'tax_query' => [
      [
        'taxonomy' => $current_term->taxonomy,
        'field' => 'term_id',
        'terms' => $current_term->term_id,
      ]
    ],
    'meta_query' => [
      [
        'key' => 'featured',
        'value' => '1',
        'compare' => '=',
        'type' => 'NUMERIC',
      ]
    ],
    'orderby' => 'rand',
    'posts_per_page' => get_option('headerslideritems'),
  ];
} else {
  $vip_args = [
    'post_type' => $taxonomy_profile_url,
    'ignore_sticky_posts' => true,
    'no_found_rows' => true,
    'orderby' => 'rand',
    'meta_query' => [
      [
        'key' => 'featured',
        'value' => '1',
        'compare' => '=',
        'type' => 'NUMERIC',
      ]
    ],
    'posts_per_page' => get_option('headerslideritems'),
  ];
}
?>

<div class="contentwrapper">
  <div class="body">

    <!-- Ad Carousel (Homepage placement) -->
    <?php if (is_active_sidebar('Right Ads')): ?>
      <section class="bodybox bodybox-homepage homepage-ad-carousel">
        <div class="sidebar-ad-carousel" aria-label="<?php esc_attr_e('Sponsored', 'escortwp'); ?>">
          <?php dynamic_sidebar('Right Ads'); ?>
        </div>
      </section>
    <?php else: ?>
      <?php
      $campaign_markup = '';
      if (class_exists('Exotic_Campaign_Renderer') && method_exists('Exotic_Campaign_Renderer', 'render_carousel')) {
        $campaign_markup = (string) Exotic_Campaign_Renderer::render_carousel(false);
      }
      ?>
      <?php if ($campaign_markup !== ''): ?>
        <section class="bodybox bodybox-homepage homepage-ad-carousel">
          <?php echo $campaign_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </section>
      <?php else: ?>
        <?php
        $register_url = get_permalink(get_option('member_register_page_id')) ?: home_url('/');
        $premium_url = get_permalink(get_option('all_premium_profiles_page_id')) ?: home_url('/');
        $contact_url = get_permalink(get_option('contact_page_id')) ?: home_url('/contact/');
        ?>
        <section class="bodybox bodybox-homepage homepage-ad-carousel">
          <div class="static-ad-carousel" aria-label="<?php esc_attr_e('Sponsored', 'escortwp'); ?>">

          <!-- Card 1: Adult Spa -->
          <article class="ad-card ad-card--spa">
            <div class="ad-card__bg-icon"><i class="fa fa-diamond"></i></div>
            <div class="ad-card__content">
              <div class="ad-card__header">
                <div class="ad-card__icon"><i class="fa fa-diamond"></i></div>
                <span class="ad-card__badge"><?php _e('Adult Fun', 'escortwp'); ?></span>
              </div>
              <h3 class="ad-card__title"><?php _e('Adult Spa', 'escortwp'); ?></h3>
              <p class="ad-card__copy">
                <?php _e('Ultimate relaxation with a happy ending. Satisfaction guaranteed.', 'escortwp'); ?></p>
              <a class="ad-card__cta ad-card__cta--primary" href="<?php echo esc_url($contact_url); ?>">
                <span><?php _e('Book Session', 'escortwp'); ?></span>
                <i class="fa fa-arrow-right"></i>
              </a>
            </div>
          </article>

          <!-- Card 2: Sex Enhancement -->
          <article class="ad-card ad-card--pills">
            <div class="ad-card__bg-icon"><i class="fa fa-bolt"></i></div>
            <div class="ad-card__content">
              <div class="ad-card__header">
                <div class="ad-card__icon"><i class="fa fa-bolt"></i></div>
                <span class="ad-card__badge"><?php _e('Power Up', 'escortwp'); ?></span>
              </div>
              <h3 class="ad-card__title"><?php _e('Sex Enhancement', 'escortwp'); ?></h3>
              <p class="ad-card__copy">
                <?php _e('Harder, stronger, longer. Pills to keep you going all night.', 'escortwp'); ?></p>
              <a class="ad-card__cta ad-card__cta--secondary" href="<?php echo esc_url($premium_url); ?>">
                <span><?php _e('Buy Now', 'escortwp'); ?></span>
                <i class="fa fa-arrow-right"></i>
              </a>
            </div>
          </article>

          <!-- Card 3: Online Casino -->
          <article class="ad-card ad-card--casino">
            <div class="ad-card__bg-icon"><i class="fa fa-trophy"></i></div>
            <div class="ad-card__content">
              <div class="ad-card__header">
                <div class="ad-card__icon"><i class="fa fa-trophy"></i></div>
                <span class="ad-card__badge"><?php _e('Jackpot', 'escortwp'); ?></span>
              </div>
              <h3 class="ad-card__title"><?php _e('Play & Win', 'escortwp'); ?></h3>
              <p class="ad-card__copy"><?php _e('Feeling lucky? Bet big and win massive jackpots today.', 'escortwp'); ?>
              </p>
              <a class="ad-card__cta ad-card__cta--tertiary" href="<?php echo esc_url($register_url); ?>">
                <span><?php _e('Play Now', 'escortwp'); ?></span>
                <i class="fa fa-arrow-right"></i>
              </a>
            </div>
          </article>

          </div>
        </section>
      <?php endif; ?>
    <?php endif; ?>

    <?php
    // ONLINE PROFILES (2025)
    $online_has_posts = false;
    $online_query = null;
    $show_online_section = (get_option('frontpageshowonline') == 1);

    if ($show_online_section):
      $user_args = [
        'meta_key' => 'last_online2',
        'meta_value' => current_time('timestamp') - 60 * 5,
        'meta_compare' => '>=',
        'fields' => 'ids',
        'count_total' => false,
      ];
      $user_query = new WP_User_Query($user_args);
      $users_arr = $user_query->get_results();

      if (count($users_arr)):
        $online_args = [
          'author__in' => $users_arr,
          'post_type' => $taxonomy_profile_url,
          'ignore_sticky_posts' => true,
          'no_found_rows' => true,
          'posts_per_page' => get_option('frontpageshowonlinecols') * 5,
        ];
        $online_query = new WP_Query($online_args);
        if ($online_query->have_posts()) {
          $online_has_posts = true;
        }
      endif;
    endif;

    if ($show_online_section):
      if ($online_has_posts) {
        $fallback_query = $online_query;
      } else {
        if (!($vip_query instanceof WP_Query)) {
          $vip_query = new WP_Query($vip_args);
        }
        $fallback_query = clone $vip_query;
        $fallback_query->posts = array_reverse($vip_query->posts);
        $fallback_query->post_count = count($fallback_query->posts);
        $fallback_query->current_post = -1;
      }

      if ($fallback_query instanceof WP_Query) {
        if (function_exists('escortwp_child_prime_profile_card_context')) {
          escortwp_child_prime_profile_card_context(wp_list_pluck($fallback_query->posts, 'ID'));
        }
        $fallback_query->rewind_posts();
      }

      if ($fallback_query && $fallback_query->have_posts()): ?>
        <!-- Instagram Stories-style Online Now carousel (VIP fallback if no online) -->
        <section class="bodybox bodybox-homepage online-stories-section" data-online-stories-section>
          <div class="section-header">
            <h2 class="l section-heading">
              <span class="online-pulse"></span>
              <?php _e('Online Now', 'escortwp'); ?>
            </h2>
            <a class="see-all-top section-see-all section-see-all--online"
              href="<?php echo esc_url(get_permalink(get_option('all_online_profiles_page_id'))); ?>"
              data-online-view-all
              data-idle-label="<?php esc_attr_e('View all', 'escortwp'); ?>"
              data-active-label="<?php esc_attr_e('Viewing all', 'escortwp'); ?>">
              <span data-online-view-all-label><?php _e('View all', 'escortwp'); ?></span>
              <span class="section-see-all__icon" aria-hidden="true"></span>
            </a>
          </div>
          <div class="online-stories-carousel">
	            <?php while ($fallback_query->have_posts()):
	              $fallback_query->the_post();
	              $story_id = get_the_ID();
	              $story_name = get_the_title();
	              $story_fallback_img = trailingslashit(get_template_directory_uri()) . 'i/no-image.png';
		              if (function_exists('escortwp_child_get_cached_first_image')) {
		                $story_img = escortwp_child_get_cached_first_image($story_id, '5');
		              } elseif (function_exists('get_first_image')) {
		                $story_img = get_first_image($story_id, '5');
		              } else {
		                $story_img = '';
		              }
	              if (empty($story_img)) {
	                $story_img = $story_fallback_img;
	              }
	              ?>
	              <a href="<?php echo esc_url(get_permalink()); ?>" class="online-story"
	                title="<?php echo esc_attr($story_name); ?>">
	                <div class="online-story__avatar">
	                  <img src="<?php echo esc_url($story_img); ?>" alt="" aria-hidden="true"
	                    data-fallback-src="<?php echo esc_url($story_fallback_img); ?>" loading="lazy" decoding="async" />
	                  <span class="online-story__indicator"></span>
	                </div>
                <?php
                $story_label = function_exists('mb_strimwidth')
                  ? mb_strimwidth($story_name, 0, 10, '…')
                  : (strlen($story_name) > 10 ? substr($story_name, 0, 10) . '…' : $story_name);
                ?>
                <span class="online-story__name"><?php echo esc_html($story_label); ?></span>
              </a>
            <?php endwhile; ?>
          </div>
        </section>
        <?php
        wp_reset_postdata();
      endif;
    endif;
    ?>

	    <?php
	    $filter_taxonomy = '';
	    $filter_term_id = '';
	    $filter_term_name = '';
	    $filter_archive_url = '';
	    if ($taxonomyName === 'escorts-from' && $current_term && !is_wp_error($current_term)) {
	      $filter_taxonomy = $current_term->taxonomy;
	      $filter_term_id = $current_term->term_id;
	      $filter_term_name = $current_term->name;
	      $filter_archive_url = get_term_link($current_term);
	      if (is_wp_error($filter_archive_url)) {
	        $filter_archive_url = '';
	      }
	    }
	    ?>

    <!-- Filter Chips (VIP + Premium + Newly Added) -->
    <section class="bodybox bodybox-homepage filter-chips-section">
	      <div class="filter-chips" role="group" aria-label="<?php esc_attr_e('Filter escorts', 'escortwp'); ?>"
	        data-filter-controls
	        data-taxonomy="<?php echo esc_attr($filter_taxonomy); ?>"
	        data-term-id="<?php echo esc_attr($filter_term_id); ?>"
	        data-term-name="<?php echo esc_attr($filter_term_name); ?>"
	        data-archive-url="<?php echo esc_attr((string) $filter_archive_url); ?>">
        <div class="filter-chip-row filter-chip-row--primary">
	          <button type="button" class="filter-chip is-active" aria-pressed="true" data-filter-type="all"
	            data-filter-value="" data-filter-label="<?php esc_attr_e('All escorts', 'escortwp'); ?>"><?php _e('All', 'escortwp'); ?></button>
          <button type="button" class="filter-chip" aria-pressed="false" data-filter-type="vip" data-filter-value="1"
            data-filter-label="<?php esc_attr_e('VIP', 'escortwp'); ?>"><?php esc_html_e('VIP', 'escortwp'); ?></button>
          <button type="button" class="filter-chip" aria-pressed="false" data-filter-type="premium" data-filter-value="1"
            data-filter-label="<?php esc_attr_e('Premium', 'escortwp'); ?>"><?php esc_html_e('Premium', 'escortwp'); ?></button>
          <button type="button" class="filter-chip" aria-pressed="false" data-filter-type="new" data-filter-value="1"
            data-filter-label="<?php esc_attr_e('New', 'escortwp'); ?>"><?php esc_html_e('New', 'escortwp'); ?></button>
          <button type="button" class="filter-chip filter-chip--status" aria-pressed="false" data-filter-type="online"
            data-filter-value="1" data-filter-label="<?php esc_attr_e('Online', 'escortwp'); ?>">
            <span class="filter-chip__status-dot" aria-hidden="true"></span><?php esc_html_e('Online', 'escortwp'); ?>
          </button>
          <button type="button" class="filter-chip filter-chip--utility" hidden aria-hidden="true" tabindex="-1"
            data-filter-type="recent_24h" data-filter-value="1"
            data-filter-label="<?php esc_attr_e('Past 24 Hours', 'escortwp'); ?>"></button>
          <a class="filter-chip filter-chip--nav" href="<?php echo esc_url(home_url('/videos/')); ?>"
            data-filter-nav data-filter-label="<?php esc_attr_e('Videos', 'escortwp'); ?>">
            <?php esc_html_e('Videos', 'escortwp'); ?>
          </a>
        </div>
        <div class="filter-chip-row filter-chip-row--secondary">
          <button type="button" class="filter-chip" aria-pressed="false" data-filter-type="verified" data-filter-value="1"
            data-filter-label="<?php esc_attr_e('Verified', 'escortwp'); ?>"><?php esc_html_e('Verified', 'escortwp'); ?></button>
          <button type="button" class="filter-chip" aria-pressed="false" data-filter-type="build" data-filter-value="4"
            data-filter-label="<?php esc_attr_e('Curvy', 'escortwp'); ?>"><?php esc_html_e('Curvy', 'escortwp'); ?></button>
          <button type="button" class="filter-chip" aria-pressed="false" data-filter-type="service" data-filter-value="5"
            data-filter-label="<?php esc_attr_e('Massage', 'escortwp'); ?>"><?php esc_html_e('Massage', 'escortwp'); ?></button>
          <button type="button" class="filter-chip" aria-pressed="false" data-filter-type="service" data-filter-value="1"
            data-filter-label="<?php esc_attr_e('BDSM', 'escortwp'); ?>"><?php esc_html_e('BDSM', 'escortwp'); ?></button>
          <button type="button" class="filter-chip" aria-pressed="false" data-filter-type="gender" data-filter-value="4"
            data-filter-label="<?php esc_attr_e('Gay', 'escortwp'); ?>"><?php esc_html_e('Gay', 'escortwp'); ?></button>
          <button type="button" class="filter-chip" aria-pressed="false" data-filter-type="service" data-filter-value="6"
            data-filter-label="<?php esc_attr_e('Fetish', 'escortwp'); ?>"><?php esc_html_e('Fetish', 'escortwp'); ?></button>
          <button type="button" class="filter-chip" aria-pressed="false" data-filter-type="service" data-filter-value="2"
            data-filter-label="<?php esc_attr_e('Couples', 'escortwp'); ?>"><?php esc_html_e('Couples', 'escortwp'); ?></button>
          <button type="button" class="filter-chip" aria-pressed="false" data-filter-type="service" data-filter-value="3"
            data-filter-label="<?php esc_attr_e('Domination', 'escortwp'); ?>"><?php esc_html_e('Domination', 'escortwp'); ?></button>
          <button type="button" class="filter-chip" aria-pressed="false" data-filter-type="looks" data-filter-value="3"
            data-filter-label="<?php esc_attr_e('Sexy', 'escortwp'); ?>"><?php esc_html_e('Sexy', 'escortwp'); ?></button>
        </div>
      </div>
      <div class="filter-results-summary"
        data-filter-summary
        data-filter-context="all"
	        data-filter-label="<?php esc_attr_e('All escorts', 'escortwp'); ?>"
        data-total-results="0">
        <span class="filter-results-summary__count">
          <strong data-summary-count>0</strong> <?php esc_html_e('results', 'escortwp'); ?>
        </span>
        <span class="filter-results-summary__label">
	          <?php esc_html_e('Active', 'escortwp'); ?>: <strong data-summary-label><?php esc_html_e('All escorts', 'escortwp'); ?></strong>
        </span>
        <button type="button" class="filter-results-summary__clear" data-filter-clear>
          <?php esc_html_e('Clear filters', 'escortwp'); ?>
        </button>
      </div>
      <div class="filter-empty-state" data-filter-empty-state hidden></div>
    </section>

    <!-- VIP Escorts (queries featured=1 meta) -->
	    <section class="bodybox bodybox-homepage featured-mobile featured-desktop featured-section">
	        <div class="section-header">
	          <h2 class="l section-heading"
	          data-default-title="<?php echo esc_attr__('VIP Escorts', 'escortwp'); ?>"
	          data-location-title="<?php echo esc_attr__('VIP Escorts', 'escortwp'); ?>"
            data-online-title="<?php echo esc_attr__('VIP Online', 'escortwp'); ?>"
            data-recent-title="<?php echo esc_attr__('VIP Past 24 Hours', 'escortwp'); ?>"
            data-online-fallback-title="<?php echo esc_attr__('VIP Recently Active', 'escortwp'); ?>"><?php _e('VIP Escorts', 'escortwp'); ?></h2>
	      </div>
      <div class="clear"></div>
      <div class="escort-grid__container" data-grid="vip"
        data-skeleton-count="<?php echo esc_attr((int) get_option('headerslideritems')); ?>">
        <?php
        if (!($vip_query instanceof WP_Query)) {
          $vip_query = new WP_Query($vip_args);
        }

        if (function_exists('escortwp_child_prime_profile_card_context')) {
          escortwp_child_prime_profile_card_context(wp_list_pluck($vip_query->posts, 'ID'));
        }
        $vip_query->rewind_posts();
        $i = 1;
        if ($vip_query->have_posts()):
          while ($vip_query->have_posts()):
            $vip_query->the_post();
            include get_theme_file_path('/loop-show-profile.php');
            if ($i % 6 === 0) {
              //    echo '<div style="width:100%; text-align:center;">';
              //    dynamic_sidebar('box-ads');
              //   echo '</div>';
            }
            $i++;
          endwhile;
        else:
          echo '<b>' . __('No Escort Available in your Search Criteria', 'escortwp') . '</b>';
        endif;
        wp_reset_postdata();
        ?>
      </div>
      <div class="clear"></div>
    </section>

    <?php
    // PREMIUM PROFILES (2025)
    if (get_option('frontpageshowpremium') == 1):
      $premium_args = [
        'post_type' => $taxonomy_profile_url,
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
        'orderby' => 'meta_value_num',
        'meta_key' => 'premium_since',
        'meta_query' => [
          [
            'key' => 'premium',
            'value' => '1',
            'compare' => '=',
            'type' => 'NUMERIC',
          ]
        ],
        'posts_per_page' => get_option('frontpageshowpremiumcols') * 5,
      ];
      $premium_query = new WP_Query($premium_args);
      if (function_exists('escortwp_child_prime_profile_card_context')) {
        escortwp_child_prime_profile_card_context(wp_list_pluck($premium_query->posts, 'ID'));
      }
      $i = 1;
      if ($premium_query->have_posts()): ?>
	        <section class="bodybox bodybox-homepage premium-section">
	          <div class="section-header">
	            <h2 class="l section-heading"
	              data-default-title="<?php printf(esc_attr__('Premium %s', 'escortwp'), ucwords($taxonomy_profile_name_plural)); ?>"
	              data-location-title="<?php printf(esc_attr__('Premium %s', 'escortwp'), ucwords($taxonomy_profile_name_plural)); ?>"
                data-online-title="<?php echo esc_attr__('Premium Online', 'escortwp'); ?>"
                data-recent-title="<?php echo esc_attr__('Premium Past 24 Hours', 'escortwp'); ?>"
                data-online-fallback-title="<?php echo esc_attr__('Premium Recently Active', 'escortwp'); ?>">
	              <?php printf(esc_html__('Premium %s', 'escortwp'), ucwords($taxonomy_profile_name_plural)); ?>
	            </h2>
            <a class="see-all-top section-see-all"
              href="<?php echo get_permalink(get_option('all_premium_profiles_page_id')); ?>">
              <?php printf(esc_html__('All premium %s', 'escortwp'), $taxonomy_profile_name_plural); ?> →
            </a>
          </div>
          <div class="clear"></div>
          <div class="escort-grid__container" data-grid="premium"
            data-skeleton-count="<?php echo esc_attr((int) get_option('frontpageshowpremiumcols') * 5); ?>">
            <?php
            while ($premium_query->have_posts()):
              $premium_query->the_post();
              include get_theme_file_path('/loop-show-profile.php');
              if ($i % 6 === 0) {
                //  echo '<div style="width:100%; text-align:center;">';
                //  dynamic_sidebar('box-ads');
                //  echo '</div>';
              }
              $i++;
            endwhile;
            ?>
          </div>
          <div class="clear"></div>
        </section>
        <?php
      endif;
      wp_reset_postdata();
    endif;
    ?>

    <?php if (get_option('frontpageshownormal') == 1): ?>
      <!-- NEWLY ADDED PROFILES (2025) -->
	      <section class="bodybox bodybox-homepage newlyadded-section">
	        <div class="section-header">
	          <h2 class="l section-heading"
	            data-default-title="<?php printf(esc_attr__('Newly Added %s', 'escortwp'), ucwords($taxonomy_profile_name_plural)); ?>"
	            data-location-title="<?php printf(esc_attr__('New %s', 'escortwp'), ucwords($taxonomy_profile_name_plural)); ?>"
              data-online-title="<?php echo esc_attr__('Basic Online', 'escortwp'); ?>"
              data-recent-title="<?php echo esc_attr__('Basic Past 24 Hours', 'escortwp'); ?>"
              data-online-fallback-title="<?php echo esc_attr__('Basic Recently Active', 'escortwp'); ?>">
	            <?php printf(esc_html__('Newly Added %s', 'escortwp'), ucwords($taxonomy_profile_name_plural)); ?>
	          </h2>
          <a class="see-all-top section-see-all"
            href="<?php echo get_permalink(get_option('all_new_profiles_page_id')); ?>">
            <?php printf(esc_html__('All newly added %s', 'escortwp'), $taxonomy_profile_name_plural); ?> →
          </a>
        </div>
        <div class="clear"></div>
        <div class="escort-grid__container" data-grid="new"
          data-skeleton-count="<?php echo esc_attr((int) get_option('frontpageshownormalcols') * 5); ?>">
          <?php
          $normal_args = [
            'post_type' => $taxonomy_profile_url,
            'ignore_sticky_posts' => true,
            'no_found_rows' => true,
            'meta_query' => [
              [
                'key' => 'premium',
                'value' => '0',
                'compare' => '=',
                'type' => 'NUMERIC',
              ]
            ],
            'posts_per_page' => get_option('frontpageshownormalcols') * 5,
          ];
          $normal_query = new WP_Query($normal_args);
          if (function_exists('escortwp_child_prime_profile_card_context')) {
            escortwp_child_prime_profile_card_context(wp_list_pluck($normal_query->posts, 'ID'));
          }
          $i = 1;
          if ($normal_query->have_posts()):
            while ($normal_query->have_posts()):
              $normal_query->the_post();
              include get_theme_file_path('/loop-show-profile.php');
              if ($i % 6 === 0) {
                //  echo '<div style="width:100%; text-align:center;">';
                //  dynamic_sidebar('box-ads');
                //  echo '</div>';
              }
              $i++;
            endwhile;
          else:
            printf(esc_html__('No %s here yet', 'escortwp'), $taxonomy_profile_name_plural);
          endif;
          wp_reset_postdata();
          ?>
        </div>
        <div class="clear"></div>
      </section>
    <?php endif; ?>

    <?php
    // LATEST REVIEWS (2025)
    if (get_option('frontpageshowrev') == 1):
      $rev_args = [
        'post_type' => 'review',
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
        'posts_per_page' => get_option('frontpageshowrevitems'),
        'orderby' => 'date',
      ];
      $rev_query = new WP_Query($rev_args);
      if ($rev_query->have_posts()): ?>
        <section class="bodybox bodybox-homepage reviews-section">
          <div class="section-header">
            <h2 class="l section-heading">
              <?php printf(esc_html__('Latest %s Reviews', 'escortwp'), ucwords($taxonomy_profile_name)); ?>
            </h2>
            <a class="see-all-top section-see-all" href="<?php echo get_permalink(get_option('nav_reviews_page_id')); ?>">
              <?php _e('See all reviews', 'escortwp'); ?> →
            </a>
          </div>
          <div class="clear"></div>
          <?php while ($rev_query->have_posts()):
            $rev_query->the_post(); ?>
            <div class="onereviewtext onereviewtext-homepage">
              <div class="author l">
                <span><?php echo substr(get_the_author_meta('display_name'), 0, 2); ?>…</span>
                <?php _e('wrote', 'escortwp'); ?>:
              </div>
              <div class="rating r">
                <div class="starrating l">
                  <div class="starrating_stars star<?php echo get_post_meta(get_the_ID(), 'rateescort', true); ?>"></div>
                </div>
              </div>
              <div class="clear5"></div>
              <div class="reviewtext">
                <?php
                $excerpt = wp_strip_all_tags(get_the_content());
                echo mb_strimwidth($excerpt, 0, get_option('frontpageshowrevchars'), '…');
                ?> <a href="<?php the_permalink(); ?>"><?php _e('see the review', 'escortwp'); ?></a>
              </div>
            </div>
          <?php endwhile; ?>
          <div class="clear10"></div>
        </section>
        <?php
      endif;
      wp_reset_postdata();
    endif;
    ?>

  </div><!-- .body -->
</div><!-- .contentwrapper -->

<?php
get_sidebar('left');
if (!is_front_page() && !is_home()) {
  get_sidebar('right');
}
get_footer();
