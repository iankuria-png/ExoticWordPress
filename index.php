<?php
/**
 * Child‐theme index.php
 */
get_header(); ?>

<div class="contentwrapper">
  <div class="body">

    <!-- VIP / Featured Escorts (from 2022 child theme) -->
    <div class="bodybox bodybox-homepage featured-mobile featured-desktop">
      <h3 class="l"><?php _e('VIP Escorts','escortwp'); ?></h3>
      <div class="clear"></div>
      <div style="display: flex; flex-wrap: wrap;">
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
            // inject ad every 6 items
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
    </div>

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
        <div class="bodybox bodybox-homepage">
          <h3 class="l"><?php printf( esc_html__('Premium %s','escortwp'), ucwords($taxonomy_profile_name_plural) ); ?></h3>
          <a class="see-all-top pinkbutton rad25 r"
             href="<?php echo get_permalink(get_option('all_premium_profiles_page_id')); ?>">
            <?php printf( esc_html__('All premium %s','escortwp'), $taxonomy_profile_name_plural ); ?>
          </a>
          <div class="clear"></div>
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
          <div class="clear"></div>
        </div>
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
          <div class="bodybox bodybox-homepage">
            <h3 class="l"><?php _e('Online now','escortwp'); ?></h3>
            <a class="see-all-top pinkbutton rad25 r"
               href="<?php echo get_permalink(get_option('all_online_profiles_page_id')); ?>">
              <?php printf( esc_html__('All online %s','escortwp'), $taxonomy_profile_name_plural ); ?>
            </a>
            <div class="clear"></div>
            <?php
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
            <div class="clear"></div>
          </div>
        <?php
        endif;
        wp_reset_postdata();
      endif;
    endif;
    ?>

    <?php if ( get_option('frontpageshownormal') == 1 ) : ?>
      <!-- NEWLY ADDED PROFILES (2025) -->
      <div class="bodybox bodybox-homepage">
        <h3 class="l"><?php printf( esc_html__('Newly Added %s','escortwp'), ucwords($taxonomy_profile_name_plural) ); ?></h3>
        <a class="see-all-top pinkbutton rad25 r"
           href="<?php echo get_permalink(get_option('all_new_profiles_page_id')); ?>">
          <?php printf( esc_html__('All newly added %s','escortwp'), $taxonomy_profile_name_plural ); ?>
        </a>
        <div class="clear"></div>
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
        <div class="clear"></div>
      </div>
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
        <div class="bodybox bodybox-homepage">
          <h3 class="l"><?php printf( esc_html__('Latest %s Reviews','escortwp'), ucwords($taxonomy_profile_name) ); ?>:</h3>
          <a class="see-all-top pinkbutton rad25 r"
             href="<?php echo get_permalink(get_option('nav_reviews_page_id')); ?>">
            <?php _e('See all reviews','escortwp'); ?>
          </a>
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
        </div>
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
