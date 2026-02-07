<?php
/**
 * Child‐theme index.php
 */
get_header();
$GLOBALS['PROFILE_GRID_NO_SEPARATORS'] = true;
?>

<div class="contentwrapper">
  <div class="body">

    <!-- VIP Escorts (queries featured=1 meta) -->
    <section class="bodybox bodybox-homepage featured-mobile featured-desktop featured-section">
      <div class="section-header">
        <h2 class="l section-heading"><?php _e('VIP Escorts','escortwp'); ?></h2>
      </div>
      <div class="clear"></div>
      <div class="escort-grid__container">
        <?php
        global $taxonomy_location_url, $taxonomy_profile_url;

        $term_slug    = get_query_var('term');
        $taxonomyName = get_query_var('taxonomy');
        $current_term = get_term_by('slug', $term_slug, $taxonomyName);

        if ($taxonomyName === 'escorts-from') {
          $vip_args = [
            'post_type'  => $taxonomy_profile_url,
            'tax_query'  => [[
              'taxonomy' => $current_term->taxonomy,
              'field'    => 'term_id',
              'terms'    => $current_term->term_id,
            ]],
            'meta_query' => [[
              'key'     => 'featured',
              'value'   => '1',
              'compare' => '=',
              'type'    => 'NUMERIC',
            ]],
            'orderby'        => 'rand',
            'posts_per_page' => get_option('headerslideritems'),
          ];
        } else {
          $vip_args = [
            'post_type'      => $taxonomy_profile_url,
            'orderby'        => 'rand',
            'meta_query'     => [[
              'key'     => 'featured',
              'value'   => '1',
              'compare' => '=',
              'type'    => 'NUMERIC',
            ]],
            'posts_per_page' => get_option('headerslideritems'),
          ];
        }

        $vip_query = new WP_Query($vip_args);
        $i = 1;
        if ($vip_query->have_posts()) :
          while ($vip_query->have_posts()) : $vip_query->the_post();
            include get_theme_file_path( '/loop-show-profile.php' );
            if ($i % 6 === 0) {
          //    echo '<div style="width:100%; text-align:center;">';
          //    dynamic_sidebar('box-ads');
           //   echo '</div>';
            }
            $i++;
          endwhile;
        else:
          echo '<b>'.__('No Escort Available in your Search Criteria','escortwp').'</b>';
        endif;
        wp_reset_postdata();
        ?>
      </div>
      <div class="clear"></div>
    </section>

    <?php
    // PREMIUM PROFILES (2025)
    if ( get_option('frontpageshowpremium') == 1 ) :
      $premium_args = [
        'post_type'      => $taxonomy_profile_url,
        'orderby'        => 'meta_value_num',
        'meta_key'       => 'premium_since',
        'meta_query'     => [[
          'key'     => 'premium',
          'value'   => '1',
          'compare' => '=',
          'type'    => 'NUMERIC',
        ]],
        'posts_per_page' => get_option('frontpageshowpremiumcols') * 5,
      ];
      $premium_query = new WP_Query($premium_args);
      $i = 1;
      if ( $premium_query->have_posts() ) : ?>
        <section class="bodybox bodybox-homepage premium-section">
          <div class="section-header">
            <h2 class="l section-heading"><?php printf( esc_html__('Premium %s','escortwp'), ucwords($taxonomy_profile_name_plural) ); ?></h2>
            <a class="see-all-top section-see-all"
               href="<?php echo get_permalink(get_option('all_premium_profiles_page_id')); ?>">
              <?php printf( esc_html__('All premium %s','escortwp'), $taxonomy_profile_name_plural ); ?> →
            </a>
          </div>
          <div class="clear"></div>
          <div class="escort-grid__container">
            <?php
            while ( $premium_query->have_posts() ) : $premium_query->the_post();
              include get_theme_file_path( '/loop-show-profile.php' );
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

    <?php
    // ONLINE PROFILES (2025)
    if ( get_option('frontpageshowonline') == 1 ) :
      $user_args = [
        'meta_key'     => 'last_online2',
        'meta_value'   => current_time('timestamp') - 60*5,
        'meta_compare' => '>=',
        'fields'       => 'ids',
      ];
      $user_query = new WP_User_Query($user_args);
      $users_arr  = $user_query->get_results();

      if ( count($users_arr) ) :
        $online_args = [
          'author__in'     => $users_arr,
          'post_type'      => $taxonomy_profile_url,
          'posts_per_page' => get_option('frontpageshowonlinecols') * 5,
        ];
        $online_query = new WP_Query($online_args);
        $i = 1;
        if ( $online_query->have_posts() ) : ?>

          <!-- Instagram Stories-style Online Now carousel -->
          <section class="bodybox bodybox-homepage online-stories-section">
            <div class="section-header">
              <h2 class="l section-heading">
                <span class="online-pulse"></span>
                <?php _e('Online Now','escortwp'); ?>
              </h2>
              <a class="see-all-top section-see-all"
                 href="<?php echo get_permalink(get_option('all_online_profiles_page_id')); ?>">
                <?php _e('View All','escortwp'); ?> →
              </a>
            </div>
            <div class="online-stories-carousel">
              <?php while ( $online_query->have_posts() ) : $online_query->the_post();
                $story_id = get_the_ID();
                $story_name = get_the_title();
                if (function_exists('get_first_image')) {
                  $story_img = get_first_image($story_id, '5');
                } else {
                  $story_img = '';
                }
              ?>
              <a href="<?php echo esc_url(get_permalink()); ?>" class="online-story" title="<?php echo esc_attr($story_name); ?>">
                <div class="online-story__avatar">
                  <img src="<?php echo esc_url($story_img); ?>" alt="<?php echo esc_attr($story_name); ?>" loading="lazy" />
                  <span class="online-story__indicator"></span>
                </div>
                <span class="online-story__name"><?php echo esc_html( mb_strimwidth($story_name, 0, 10, '…') ); ?></span>
              </a>
              <?php endwhile; ?>
            </div>
          </section>

          <!-- Online Escorts Grid -->
          <section class="bodybox bodybox-homepage online-section">
            <div class="section-header">
              <h2 class="l section-heading"><?php _e('Online Escorts','escortwp'); ?> <span class="online-indicator"></span></h2>
              <a class="see-all-top section-see-all"
                 href="<?php echo get_permalink(get_option('all_online_profiles_page_id')); ?>">
                <?php printf( esc_html__('All online %s','escortwp'), $taxonomy_profile_name_plural ); ?> →
              </a>
            </div>
            <div class="clear"></div>
            <div class="escort-grid__container">
              <?php
              rewind_posts();
              $online_query->rewind_posts();
              while ( $online_query->have_posts() ) : $online_query->the_post();
                include get_theme_file_path( '/loop-show-profile.php' );
                if ( $i % 6 === 0 ) {
                // echo '<div style="width:100%; text-align:center;">';
                 // dynamic_sidebar('box-ads');
                 // echo '</div>';
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
    endif;
    ?>

    <?php if ( get_option('frontpageshownormal') == 1 ) : ?>
      <!-- NEWLY ADDED PROFILES (2025) -->
      <section class="bodybox bodybox-homepage newlyadded-section">
        <div class="section-header">
          <h2 class="l section-heading"><?php printf( esc_html__('Newly Added %s','escortwp'), ucwords($taxonomy_profile_name_plural) ); ?></h2>
          <a class="see-all-top section-see-all"
             href="<?php echo get_permalink(get_option('all_new_profiles_page_id')); ?>">
            <?php printf( esc_html__('All newly added %s','escortwp'), $taxonomy_profile_name_plural ); ?> →
          </a>
        </div>
        <div class="clear"></div>
        <div class="escort-grid__container">
          <?php
          $normal_args = [
            'post_type'      => $taxonomy_profile_url,
            'meta_query'     => [[
              'key'     => 'premium',
              'value'   => '0',
              'compare' => '=',
              'type'    => 'NUMERIC',
            ]],
            'posts_per_page' => get_option('frontpageshownormalcols') * 5,
          ];
          $normal_query = new WP_Query($normal_args);
          $i = 1;
          if ( $normal_query->have_posts() ) :
            while ( $normal_query->have_posts() ) : $normal_query->the_post();
              include get_theme_file_path( '/loop-show-profile.php' );
              if ( $i % 6 === 0 ) {
              //  echo '<div style="width:100%; text-align:center;">';
              //  dynamic_sidebar('box-ads');
              //  echo '</div>';
              }
              $i++;
            endwhile;
          else:
            printf( esc_html__('No %s here yet','escortwp'), $taxonomy_profile_name_plural );
          endif;
          wp_reset_postdata();
          ?>
        </div>
        <div class="clear"></div>
      </section>
    <?php endif; ?>

    <?php
    // LATEST REVIEWS (2025)
    if ( get_option('frontpageshowrev') == 1 ) :
      $rev_args = [
        'post_type'      => 'review',
        'posts_per_page' => get_option('frontpageshowrevitems'),
        'orderby'        => 'date',
      ];
      $rev_query = new WP_Query($rev_args);
      if ( $rev_query->have_posts() ) : ?>
        <section class="bodybox bodybox-homepage reviews-section">
          <div class="section-header">
            <h2 class="l section-heading"><?php printf( esc_html__('Latest %s Reviews','escortwp'), ucwords($taxonomy_profile_name) ); ?></h2>
            <a class="see-all-top section-see-all"
               href="<?php echo get_permalink(get_option('nav_reviews_page_id')); ?>">
              <?php _e('See all reviews','escortwp'); ?> →
            </a>
          </div>
          <div class="clear"></div>
          <?php while ( $rev_query->have_posts() ) : $rev_query->the_post(); ?>
            <div class="onereviewtext onereviewtext-homepage">
              <div class="author l">
                <span><?php echo substr(get_the_author_meta('display_name'),0,2); ?>…</span>
                <?php _e('wrote','escortwp'); ?>:
              </div>
              <div class="rating r">
                <div class="starrating l">
                  <div class="starrating_stars star<?php echo get_post_meta(get_the_ID(),'rateescort',true); ?>"></div>
                </div>
              </div>
              <div class="clear5"></div>
              <div class="reviewtext">
                <?php
                $excerpt = wp_strip_all_tags( get_the_content() );
                echo mb_strimwidth( $excerpt, 0, get_option('frontpageshowrevchars'), '…');
                ?> <a href="<?php the_permalink(); ?>"><?php _e('see the review','escortwp'); ?></a>
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
get_sidebar('right');
get_footer();
