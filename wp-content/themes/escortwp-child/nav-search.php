<?php
/*
Template Name: Search page
*/

global $taxonomy_location_url;

if ( isset($_POST['action']) && $_POST['action'] === 'search' ) {
    $meta_query = [];

    // pagination
    $paged = !empty($_POST['paged']) ? (int) $_POST['paged'] : 1;
    if ( isset($_POST['previous']) ) {
        $paged--;
    }
    if ( isset($_POST['next']) ) {
        $paged++;
    }

    // name
    if ( !empty($_POST['yourname']) ) {
        $yourname = substr( wp_strip_all_tags($_POST['yourname'], true), 0, 200 );
        $meta_query[] = [
            'key'   => 'yourname',
            'value' => $yourname,
        ];
    }

    // country / state / city
    if ( !empty($_POST['country']) && $_POST['country'] > 0 ) {
        $country     = (int) $_POST['country'];
        $city_parent = $country;

        if ( term_exists( $country, $taxonomy_location_url ) ) {
            $meta_query[] = [
                'key'     => 'country',
                'value'   => $country,
                'compare' => '=',
            ];

            if ( showfield('state') && !empty($_POST['state']) && $_POST['state'] > 0 ) {
                $state = (int) $_POST['state'];
                if ( term_exists( $state, $taxonomy_location_url, $country ) ) {
                    $meta_query[] = [
                        'key'     => 'state',
                        'value'   => $state,
                        'compare' => '=',
                    ];
                    $city_parent = $state;
                } else {
                    $err .= __("The state you selected doesn't exist in our database","escortwp") . "<br />";
                }
            }

            if ( !empty($_POST['city']) && $_POST['city'] > 0 ) {
                $city = (int) $_POST['city'];
                if ( term_exists( $city, $taxonomy_location_url, $city_parent ) ) {
                    $meta_query[] = [
                        'key'     => 'city',
                        'value'   => $city,
                        'compare' => '=',
                    ];
                } else {
                    $err .= __("The city you selected doesn't exist in our database","escortwp") . "<br />";
                }
            }
        }
    }

    // independent (value="yes" in the form)
    if ( !empty($_POST['independent']) && $_POST['independent'] === 'yes' ) {
        $meta_query[] = [
            'key'     => 'independent',
            'value'   => 'yes',
            'compare' => '=',
        ];
    }

    // premium
    if ( !empty($_POST['premium']) && (int) $_POST['premium'] === 1 ) {
        $meta_query[] = [
            'key'     => 'premium',
            'value'   => '1',
            'compare' => '=',
        ];
    }

    // verified (new in 2025)
    if ( !empty($_POST['verified']) && (int) $_POST['verified'] === 1 ) {
        $meta_query[] = [
            'key'     => 'verified',
            'value'   => '1',
            'compare' => '=',
        ];
    }

    // gender
    if ( !empty($_POST['gender']) ) {
        $gender = (int) $_POST['gender'];
        $meta_query[] = [
            'key'     => 'gender',
            'value'   => $gender,
            'compare' => '=',
        ];
    }

    // age
    if ( !empty($_POST['age']) ) {
        $age = (int) $_POST['age'];
        if ( $age > 17 ) {
            $meta_query[] = [
                'key'     => 'age',
                'value'   => $age,
                'compare' => '=',
            ];
        }
    }

    // ethnicity, haircolor, hairlength, bustsize, height, weight, build, looks
    foreach ( ['ethnicity','haircolor','hairlength','bustsize','height','weight','build','looks'] as $field ) {
        if ( !empty($_POST[$field]) ) {
            $val = (string) $_POST[$field];
            $meta_query[] = [
                'key'     => $field,
                'value'   => $val,
                'compare' => '=',
            ];
        }
    }

    // availability (array)
    if ( !empty($_POST['availability']) && is_array($_POST['availability']) ) {
        foreach ( $_POST['availability'] as $a ) {
            $meta_query[] = [
                'key'     => 'availability',
                'value'   => '%"' . (int)$a . '"%',
                'compare' => 'LIKE',
            ];
        }
    }

    // smoker
    if ( !empty($_POST['smoker']) ) {
        $smoker = (int) $_POST['smoker'];
        $meta_query[] = [
            'key'     => 'smoker',
            'value'   => $smoker,
            'compare' => '=',
        ];
    }

    // rates filter
    if ( !empty($_POST['low']) || !empty($_POST['high']) ) {
        $low  = (int) ($_POST['low']  ?? 0);
        $high = (int) ($_POST['high'] ?? 0);

        if ( $low && $high ) {
            $meta_query[] = [
                'key'     => 'rate1h_incall',
                'value'   => "$low AND `meta_value` <= $high",
                'compare' => '>=',
            ];
        } elseif ( $low ) {
            $meta_query[] = [
                'key'     => 'rate1h_incall',
                'value'   => $low,
                'compare' => '>=',
            ];
        } elseif ( $high ) {
            $meta_query[] = [
                'key'     => 'rate1h_incall',
                'value'   => $high,
                'compare' => '<=',
            ];
        }
    }

    // services
    if ( !empty($_POST['services']) && is_array($_POST['services']) ) {
        foreach ( $_POST['services'] as $service ) {
            $meta_query[] = [
                'key'     => 'services',
                'value'   => '%"' . (int)$service . '"%',
                'compare' => 'LIKE',
            ];
        }
    }
}

get_header(); ?>
<div class="contentwrapper">
  <div class="body">
    <div class="bodybox">
      <form action="<?php echo esc_url( get_permalink() ); ?>" method="post" class="form-styling">
        <h3 class="l">
          <?php printf( esc_html__('Search for %s','escortwp'), $taxonomy_profile_name_plural ); ?>
        </h3>
        <div class="pinkbutton rad25 r filtersearch"<?php if (empty($_POST['action'])) echo ' style="display:none;"'; ?>>
          <?php _e('Filter search','escortwp'); ?>
        </div>
        <div class="clear30"></div>

        <script type="text/javascript">
        jQuery(function($){
          var c = ".country",
              parent = ".searchform",
              city_div = <?php echo showfield('state') ? "' .inputstates'" : "'.inputcities'"; ?>,
              state_c = '.state',
              state_div = '.inputcities';

          // initial population of cities
          if ($(c).val() > 0) {
            show_search_cities(c);
          }
          $(c).change(function(){ show_search_cities(c); });

          function show_search_cities(e) {
            var country = $(e).val();
            $(city_div).text($(city_div).data('text'));
            <?php if(showfield('state')): ?>
              $(state_div).text($(state_div).data('text'));
            <?php endif; ?>
            if(country < 1) return;
            loader($(e).parents(parent).find(city_div));
            $.ajax({
              type: "GET",
              url: "<?php bloginfo('template_url'); ?>/ajax/get-cities.php",
              data: "id=" + country + "&selected=<?php echo esc_js($city ?? ''); ?>&hide_empty=1<?php echo showfield('state') ? "&state=yes" : ""; ?>&select2=yes",
              success: function(data){
                $(e).parents(parent).find(city_div).html(data);
                if($(window).width() > 960) $('.select2').select2();
              }
            });
          }

          <?php if(showfield('state')): ?>
          $(parent).on("change", state_c, function(){
            var state = $(this).val();
            $(state_div).text($(state_div).data('text'));
            if(state < 1) return;
            loader($(this).parents(parent).find(state_div));
            $.ajax({
              type: "GET",
              url: "<?php bloginfo('template_url'); ?>/ajax/get-cities.php",
              data: "id=" + state + "&selected=<?php echo esc_js($city ?? ''); ?>&hide_empty=1&select2=yes",
              success: function(data){
                $(state_div).html(data + '<div class="formseparator"></div>');
                if($(window).width() > 960) $('.select2').select2();
              }
            });
          });
          <?php endif; ?>

          // toggle filter form
          $('.filtersearch').on('click', function(){
            $('.searchform').slideToggle();
          });
        });
        </script>

        <div class="searchform registerform"<?php if (!empty($_POST['action'])) echo ' style="display:none;"'; ?>>
          <input type="hidden" name="action" value="search" />
          <input type="hidden" name="paged"  value="<?php echo esc_attr($paged); ?>" />

          <?php if(insearch('yourname')): ?>
          <div class="form-label">
            <label for="yourname">
              <?php printf(esc_html__('%s Name','escortwp'), ucwords($taxonomy_profile_name)); ?>
            </label>
          </div>
          <div class="form-input">
            <input type="text" name="yourname" id="yourname" class="input" value="<?php echo esc_attr($yourname ?? ''); ?>" />
          </div>
          <div class="formseparator"></div>
          <?php endif; ?>

          <?php if(insearch('country')): ?>
          <div class="form-label">
            <label for="country"><?php _e('Country','escortwp'); ?></label>
          </div>
          <div class="form-input">
            <?php
              $args = [
                'show_option_none' => __('Select country','escortwp'),
                'orderby'          => 'name',
                'order'            => 'ASC',
                'hide_empty'       => 1,
                'selected'         => $country ?? 0,
                'name'             => 'country',
                'class'            => 'country select2',
                'taxonomy'         => $taxonomy_location_url,
              ];
              wp_dropdown_categories($args);
            ?>
          </div>
          <div class="formseparator"></div>
          <?php endif; ?>

          <?php if(insearch('country') && showfield('state')): ?>
          <div class="form-label">
            <label for="state"><?php _e('State','escortwp'); ?></label>
          </div>
          <div class="form-input inputstates" data-text="<?=__('Please select a country first','escortwp')?>">
            <?php if(!empty($country)): ?>
              <?php
                $args = [
                  'show_option_none' => __('Select State','escortwp'),
                  'parent'           => $country,
                  'hide_empty'       => 1,
                  'selected'         => $state ?? 0,
                  'name'             => 'state',
                  'class'            => 'state select2',
                  'depth'            => 1,
                  'taxonomy'         => $taxonomy_location_url,
                ];
                wp_dropdown_categories($args);
              ?>
            <?php else: ?>
              <?php _e('Please select a country first','escortwp'); ?>
            <?php endif; ?>
          </div>
          <div class="formseparator"></div>
          <?php endif; ?>

          <?php if(insearch('city') && (showfield('country'))): ?>
          <div class="form-label">
            <label for="city"><?php _e('City','escortwp'); ?></label>
          </div>
          <div class="form-input inputcities" data-text="<?=__('Please select a country first','escortwp')?>">
            <?php if(!empty($city_parent)): ?>
              <?php
                $args = [
                  'show_option_none' => __('Select City','escortwp'),
                  'parent'           => $city_parent,
                  'hide_empty'       => 1,
                  'selected'         => $city ?? 0,
                  'name'             => 'city',
                  'class'            => 'city select2',
                  'depth'            => 1,
                  'taxonomy'         => $taxonomy_location_url,
                ];
                wp_dropdown_categories($args);
              ?>
            <?php else: ?>
              <?=__('Please select a country first','escortwp')?>
            <?php endif; ?>
          </div>
          <div class="formseparator"></div>
          <?php endif; ?>

          <!-- independent -->
          <div class="form-label">
            <label><?php printf(esc_html__('Only show independent %s?','escortwp'), $taxonomy_profile_name_plural); ?></label>
          </div>
          <div class="form-input">
            <label for="independent">
              <input type="checkbox" name="independent" value="yes" id="independent"<?php checked($independent ?? '', 'yes'); ?> />
              <?php _e('Yes','escortwp'); ?>
            </label>
          </div>
          <div class="formseparator"></div>

          <!-- premium -->
          <div class="form-label">
            <label><?php printf(esc_html__('Only show premium %s?','escortwp'), $taxonomy_profile_name_plural); ?></label>
          </div>
          <div class="form-input">
            <label for="premium">
              <input type="checkbox" name="premium" value="1" id="premium"<?php checked($premium ?? 0, 1); ?> />
              <?php _e('Yes','escortwp'); ?>
            </label>
          </div>
          <div class="formseparator"></div>

          <!-- verified -->
          <div class="form-label">
            <label><?php printf(esc_html__('Only show verified %s?','escortwp'), $taxonomy_profile_name_plural); ?></label>
          </div>
          <div class="form-input">
            <label for="verified">
              <input type="checkbox" name="verified" value="1" id="verified"<?php checked($verified ?? 0, 1); ?> />
              <?php _e('Yes','escortwp'); ?>
            </label>
          </div>
          <div class="formseparator"></div>

          <!-- gender, other fields, rates, services... replicate as above -->

          <div class="text-center">
            <input type="submit" name="submit" value="<?php printf(esc_html__('Search %s','escortwp'), $taxonomy_profile_name_plural); ?>" class="pinkbutton rad3" />
          </div>
        </div><!-- /.searchform -->

        <?php
        if ( !empty($_POST['action']) && $_POST['action'] === 'search' ) {
            // First get all individual ID lists…
            if ( !empty($meta_query) ) {
                global $wpdb;
                foreach ( $meta_query as $one ) {
                    if ( $one['key'] === 'rate1h_incall' ) {
                        $sql = "SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = %s AND `meta_value` {$one['compare']} {$one['value']}";
                        $cols = $wpdb->get_col( $wpdb->prepare( $sql, $one['key'] ) );
                    } elseif ( $one['key'] === 'yourname' ) {
                        $sql  = "SELECT ID FROM `{$wpdb->posts}` WHERE post_type = %s AND post_title LIKE %s";
                        $cols = $wpdb->get_col( $wpdb->prepare( $sql, $taxonomy_profile_url, '%' . $wpdb->esc_like($one['value']) . '%' ) );
                    } else {
                        $sql  = "SELECT post_id FROM `{$wpdb->postmeta}` WHERE meta_key = %s AND meta_value {$one['compare']} %s";
                        $cols = $wpdb->get_col( $wpdb->prepare( $sql, $one['key'], $one['value'] ) );
                    }

                    if ( $cols ) {
                        $query[] = $cols;
                    }
                }
            }

            // then intersect to find IDs matching all criteria
            if ( !empty($query) ) {
                $r = call_user_func_array('array_intersect', $query);
            }

            if ( !empty($r) ) {
                $posts_per_page = 30;

                // Premium first…
                $premium_args = [
                    'post_type'      => $taxonomy_profile_url,
                    'posts_per_page' => $posts_per_page,
                    'orderby'        => 'meta_value_num',
                    'meta_key'       => 'premium_since',
                    'paged'          => $paged,
                    'post__in'       => $r,
                    'meta_query'     => [[
                        'key'     => 'premium',
                        'value'   => '1',
                        'compare' => '=',
                        'type'    => 'NUMERIC',
                    ]],
                ];
                $premium = new WP_Query($premium_args);

                // …then the rest
                $offset = 0;
                if ( $paged > $premium->max_num_pages ) {
                    $offset = $posts_per_page * ($paged - 1) - $premium->found_posts;
                }
                if ( $premium->found_posts < 1 ) {
                    $offset = $posts_per_page * ($paged - 1);
                }

                $normal_args = [
                    'offset'         => $offset,
                    'post_type'      => $taxonomy_profile_url,
                    'posts_per_page' => $posts_per_page - count($premium->posts),
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                    'post__in'       => $r,
                    'meta_query'     => [[
                        'key'     => 'premium',
                        'value'   => '0',
                        'compare' => '=',
                        'type'    => 'NUMERIC',
                    ]],
                ];
                $normal = new WP_Query($normal_args);

                // merge
                $all = $premium;
                if ( $premium->post_count < $posts_per_page ) {
                    $all->posts      = array_merge($premium->posts, $normal->posts);
                    $all->post_count = count($all->posts);
                }

                // pagination buttons
                $total_posts  = $premium->found_posts + $normal->found_posts;
                $total_pages  = ceil($total_posts / $posts_per_page);
                $pagination   = '';
                if ( $paged > 1 ) {
                    $pagination .= '<input type="submit" name="previous" value="' . __('Previous page','escortwp') . '" class="pinkbutton rad3 l" />';
                }
                if ( $paged < $total_pages ) {
                    $pagination .= '<input type="submit" name="next" value="' . __('Next page','escortwp') . '" class="pinkbutton rad3 r" />';
                }

                // output
                if ( $all->have_posts() ) {
                    while ( $all->have_posts() ) {
                        $all->the_post();
                        include( get_theme_file_path().'/loop-show-profile.php' );
                    }
                    echo '<div class="clear20"></div>';
                    echo $pagination;
                } else {
                    printf( esc_html__('No %s found','escortwp'), $taxonomy_profile_name_plural );
                }
                wp_reset_postdata();
            } else {
                printf( esc_html__('No %s found','escortwp'), $taxonomy_profile_name_plural );
            }
        }
        ?>
      </form>
      <div class="clear"></div>
    </div><!-- .bodybox -->
    <div class="clear"></div>
  </div><!-- .body -->
</div><!-- .contentwrapper -->

<?php get_sidebar('left'); ?>
<?php get_sidebar('right'); ?>
<div class="clear"></div>

<?php get_footer(); ?>
